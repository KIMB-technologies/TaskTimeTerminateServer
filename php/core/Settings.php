<?php
class Settings {

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser, CLIOutput $output) {
		$this->parser = $parser;
		$this->output = $output;

		$this->parseCommands($this->parser->getCommands());
	}

	private function parseCommands(array $commands) : void {
		switch( $commands[0] ) {
			case "cats":
			case "categories":
			case "c":
				$this->categories(array_slice($commands, 1));
				break;
			case "merge":
				$this->merge();
				break;
			case "edit":
			case "e":
				$this->edit(array_slice($commands, 1));
				break;
			case "sync":
				$this->sync(array_slice($commands, 1));
				break;
			default:
				$this->commandList();
		}
	}

	private function categories(array $commands) : void {
		$this->output->print(array(
			'Settings -> Categories'
		));
		if( isset($commands[0])){
			$cmd = $commands[0];
			if( in_array($cmd, ['add', 'del', 'list'])){
				$cats = StatsData::getAllCategories();
				$r = Config::getStorageReader('config');
				if($cmd == 'add'){
					$color = null;
					do {
						if( $color == CLIOutput::RED){
							$this->output->print(array("Please check input, can't create categories twice, check allowed chars!"), $color ,1);
						}
						$newcat = $this->output->readline("Name of category to add [Only A-Z, a-z, 0-9 and -]:", $color, 1);
						$color = CLIOutput::RED;
					} while (!InputParser::checkCategoryInput($newcat) || in_array($newcat, $cats));
					$cats[] = $newcat;
					if($r->setValue(['categories'], $cats)){
						$this->output->print(array("Added '". $newcat ."'"), CLIOutput::GREEN, 1);
					}
					else{
						$this->output->print(array("Unable to add '". $newcat ."'"), CLIOutput::RED, 1);
					}
				}
				else if ($cmd == 'del'){
					$clist = array();
					foreach( $cats as $id => $cat ){
						$clist[] = array(
							'ID' => strval($id), 
							'Category' => $cat
						);
					}
					$this->output->table($clist);
					
					$delid = $this->output->readline("Type ID to delete category, else abort:", CLIOutput::RED, 1);
					if( isset($cats[$delid]) ){
						$delcat = $cats[$delid];
						unset($cats[$delid]);
						if($r->setValue(['categories'], $cats)){
							$this->output->print(array("Deleted '". $delcat ."'"), CLIOutput::GREEN, 1);
						}
						else{
							$this->output->print(array("Unable to delete '". $delcat ."'"), CLIOutput::RED, 1);
						}
					}
					else{
						$this->output->print(array("Abort!"), CLIOutput::YELLOW, 1);
					}
				}
				else {
					$clist = array();
					foreach( $cats as $id => $cat ){
						$clist[] = array(
							'ID' => strval($id), 
							'Category' => $cat
						);
					}
					$this->output->table($clist);
				}
				return;
			}
		}
		$this->output->print(array(
			array(
				'There are more commands for categories',
				array(
					CLIOutput::colorString( "add, del, list", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Add category, delete category or get a list.", CLIOutput::BLUE) )
				)
			)
		));
	}

	private function merge() : void {
		$this->output->print(array(
			'Settings -> Merge'
		));
			
		$s = new StatsData();
		$allnames = $s->getLocalNames();
		$color = null;
		do {
			if( $color == CLIOutput::RED){
				$this->output->print(array("Please check input, only used names are allowed!"), $color ,1);
			}
			$merge = $this->output->readline("Name of data to merge into another (the one to rename):", $color, 1);
			$color = CLIOutput::RED;
		} while ( empty($merge) || !in_array($merge, $allnames));

		$color = null;
		do {
			if( $color == CLIOutput::RED){
				$this->output->print(array("Please check input, only used names are allowed!"), $color ,1);
			}
			$mergeTo = $this->output->readline("Name of data to merge into (the new name):", $color, 1);
			$color = CLIOutput::RED;
		} while ( empty($mergeTo) || !in_array($mergeTo, $allnames));
		
		if($s->merge($merge, $mergeTo)){
			$this->output->print(array("Merged '". $merge ."' into '".$mergeTo."'"), CLIOutput::GREEN, 1);
		}
		else{
			$this->output->print(array("Error merging '". $merge ."' into '".$mergeTo."'"), CLIOutput::RED, 1);
		}
	}

	private function edit(array $commands) : void {
		$this->output->print(array(
			'Settings -> Edit'
		));
		if( isset($commands[0]) && preg_match( StatsData::DATE_PREG, $commands[0]) === 1 ) {
			$finame = $commands[0];
			if( is_file( Config::getStorageDir() . '/' . $finame . '.json' ) ){
				$s = Config::getStorageReader($finame);

				$id = null;
				do {
					if( $id !== null ){
						$this->output->print(array("Edit Task " . CLIOutput::colorString( strval($id), CLIOutput::YELLOW) . ':',
							array(
								'Name:     ' . CLIOutput::colorString( $s->getValue([$id, 'name']), CLIOutput::YELLOW),
								'Category: ' . CLIOutput::colorString( $s->getValue([$id, 'category']), CLIOutput::YELLOW),
								'Duration: ' . CLIOutput::colorString( Stats::secToTime( $s->getValue([$id, 'end']) - $s->getValue([$id, 'begin']) ) , CLIOutput::YELLOW),
							)
						), null, 1);
						$this->editSingle($id, $s);
					}
					
					$list = array();
					foreach($s->getArray() as $id => $d){
						$list[] = array(
							'ID' => strval($id),
							'Name' => $d['name'],
							'Category' => $d['category'],
							'Duration' => Stats::secToTime( $d['end'] - $d['begin'] )
						);
					}
					$this->output->table($list);
					$id = $this->output->readline("Give ID of task to edit, else exit:", null, 1 );
				}
				while( $s->isValue([$id]) );
				$this->output->print(array("Exit!"), CLIOutput::YELLOW, 1);

				StatsLoader::saveDayTasks( $s->getArray() );				
			}
			else{
				$this->output->print(array(array(
					'No tasks found for this day.'
				)), CLIOutput::RED );
			}
		}
		else {
			$this->output->print(array(array(
				'Please give the day containing the tasks to edit.',
				array( 'e.g. ' . CLIOutput::colorString( "ttt conf edit 2020-01-20", CLIOutput::GREEN) )
			)), CLIOutput::RED );
		}
	}

	private function editSingle(int $id, JSONReader $s) : void {
		if( $this->output->readline("Delete task (y/n)?", CLIOutput::RED, 1) === 'y' ){
			$s->setValue([$id], null);
			$this->output->print(array('Deleted ' . $id . '!' ), CLIOutput::RED, 2);			
		}
		else{
			$this->output->print(array("For all upcoming, leave empty to remain unchanged."), CLIOutput::BLUE, 1);
			
			$name = trim($this->output->readline("Give a new name [Only A-Z, a-z, 0-9, _ and -]:", null, 1));
			if( InputParser::checkNameInput($name) ){
				$s->setValue([$id, 'name'], $name);
			}

			$category = trim($this->output->readline("Give a new category [Use a name containing A-Z, a-z, 0-9 and -, no ID]:", null, 1));
			if( InputParser::checkCategoryInput($category) ){
				$s->setValue([$id, 'category'], $category);
			}

			$time = trim($this->output->readline("Give a new duration (will change the end time; format e.g. 1h10m, 10m, 1h):", null, 1));
			if( !empty( $time ) && preg_match('/^(\d+h)?(\d+m)?$/', $time, $matches) === 1 ){
				$hs = 0;
				$mins = 0;
				if(isset($matches[1])){ // Gruppe 3, d.h. Stundenangabe
					$hs = intval(substr($matches[1], 0, -1));
				}
				if(isset($matches[2])){ // Gruppe 3, d.h. Minutenangabe
					$mins = intval(substr($matches[2], 0, -1));
				}
				$s->setValue([$id, 'end'], $s->getValue([$id, 'begin']) + 3600 * $hs + 60 * $mins);
			}
		}
	}

	private function commandList() : void {
		$this->output->print(array(
			'Settings',
			array(
				'List of all commands for settings',
				array(
					CLIOutput::colorString( "cats, categories, c ", CLIOutput::GREEN ) . CLIOutput::colorString( "del| list| add", CLIOutput::YELLOW ),
					array( CLIOutput::colorString( "Edit list of available categories", CLIOutput::BLUE) ),
					CLIOutput::colorString( "merge", CLIOutput::GREEN ),
					array( CLIOutput::colorString( "Merge two tasks into one (rename one)", CLIOutput::BLUE) ),
					CLIOutput::colorString( "edit, e ", CLIOutput::GREEN ) . CLIOutput::colorString( "2020-01-20", CLIOutput::YELLOW ),
					array( CLIOutput::colorString( "Edit the logged tasks for a given day", CLIOutput::BLUE) ),
					CLIOutput::colorString( "sync ", CLIOutput::GREEN ) . CLIOutput::colorString( "server| directory", CLIOutput::YELLOW ),
					array( CLIOutput::colorString( "Enable, disable or edit multi device synchronization", CLIOutput::BLUE) )
				)
			)
		));
	}

	private function sync(array $commands) : void {
		$this->output->print(array(
			'Settings -> Sync'
		));

		$c = Config::getStorageReader('config');
		if( $c->isValue(['sync', 'directory']) ){
			$this->output->print(array(
				'Directory sync:',
				array(
					"Sync path: \t\t" . $c->getValue(['sync', 'directory', 'path']),
					"Name of this client: \t" . $c->getValue(['sync', 'directory', 'thisname'])
			)), null, 1);
		}
		else{
			$this->output->print(array('No directory sync enabled!'), CLIOutput::YELLOW, 1);
		}

		if( $c->isValue(['sync', 'server']) ){
			$this->output->print(array(
				'Server sync:',
				array(
					"Server URI: \t\t" . $c->getValue(['sync', 'server', 'uri']),
					"Sync group: \t\t" . $c->getValue(['sync', 'server', 'group']),
					"Client token: \t\t" . str_repeat( '*', strlen($c->getValue(['sync', 'server', 'token']))),
					"Name of this client: \t" . $c->getValue(['sync', 'server', 'thisname'])
			)), null, 1);
		}
		else{
			$this->output->print(array('No server sync enabled!'), CLIOutput::YELLOW, 1);
		}

		if( isset($commands[0]) && in_array( trim($commands[0]), ['server', 'directory'], true ) ){
			$this->output->print(array(''));
			$this->editSync(trim($commands[0]), $c);
		}
		else {
			$this->output->print(array(array(
				'To edit syncs choose type to edit.',
				array( CLIOutput::colorString( 'ttt conf sync server', CLIOutput::BLUE) .' or '. CLIOutput::colorString( 'ttt conf sync directory', CLIOutput::BLUE) )
			)));
		}
	}

	private function editSync(string $type, JSONReader $c) : void {
		// Datastore and String values
		$default = array(
			['sync', 'server', 'uri'],
			['sync', 'server', 'group'],
			['sync', 'server', 'token'],
			['sync', 'server', 'thisname'],
			['sync', 'directory', 'path'],
			['sync', 'directory', 'thisname']
		);
		$names = array(
			'path' => "Sync path",
			'thisname' => "Name of this client",
			'uri' => "Server URI",
			'group' => "Sync group",
			'token' => "Client token",
		);
		$check = array(
			'path' => fn(string $p) => is_dir($p) && is_writable($p),
			'thisname' => fn(string $n) => InputParser::checkDeviceName($n),
			'uri' => fn(string $u) => @filter_var($u, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_SCHEME_REQUIRED),
			'group' => fn(string $g) => preg_match('/^[A-Za-z0-9]+$/', $g) === 1,
			'token' => fn(string $t) => preg_match('/^[A-Za-z0-9]+$/', $t) === 1,
		);

		// setup array or remove sync
		if( !$c->isValue(['sync']) ){
			$c->setValue(['sync'], array());
		}
		if( !$c->isValue(['sync', $type]) ){
			$c->setValue(['sync', $type], array());
			foreach($default as $v){
				if( $v[1] == $type ){
					$c->setValue($v, '');
				}
			}
		}
		else if( $this->output->readline('Delete '. $type .' sync (y/n)?', CLIOutput::RED, 1) == 'y' ){
			$c->setValue(['sync', $type], null);
			return;
		}
		else{
			$this->output->print(array("For all upcoming, leave empty to remain unchanged."), CLIOutput::BLUE, 1);
		}

		// change sync
		$unchanged = true;
		foreach($c->getValue(['sync', $type]) as $key => $value ){
			do {
				$input = trim($this->output->readline(
						$names[$key] . ':' . ($key != 'thisname' ? "\t\t" : "\t"
					), null, 1));
				if( !empty($input) ){
					if( isset( $check[$key] ) && !$check[$key]($input) ){
						$this->output->print(array("Invalid format! Stays unchanged!"), CLIOutput::RED, 2);
						
					}
					else{
						$unchanged = false;
						$c->setValue(['sync', $type, $key], $input);
					}
				}
			} while( empty( $c->getValue(['sync', $type, $key]) ) );
		}

		if( !$unchanged ){
			$c->__destruct(); // write to disk and release lock
			$acc = ( $type == 'directory' ? new DirectoryStatsAccess() : new ServerStatsAccess() );
			if($acc->initialSync()){ // copy initial files to sync dir
				$this->output->print(array('', 'Initialization of destination successful.'), CLIOutput::GREEN, 1);
			}
			else{
				$this->output->print(array('', 'Error to initialize destination.'), CLIOutput::RED, 1);
			}
		}
	}
}
?>