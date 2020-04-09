<?php
class Stats {

	const DAYS_DISPLAY = 'd.m';
	const DAYS_MAXCOUNT = 5;
	const DEVICE_MAXCOUNT = 3;

	private CLIParser $parser;
	private CLIOutput $output;

	private bool $todayview = false;

	public function __construct(CLIParser $parser, CLIOutput $output) {
		$this->parser = $parser;
		$this->output = $output;

		$this->parseCommands($this->parser->getCommands());
	}

	private function parseCommands(array $commands) : void {
		$this->output->print(
			array('Statistics',
				array(
					'Use commands ' .
						CLIOutput::colorString( 'day', CLIOutput::BLUE ) . ', ' .
						CLIOutput::colorString( 'week', CLIOutput::BLUE ) . ', '.
						CLIOutput::colorString( 'month', CLIOutput::BLUE ) . ', '.
						CLIOutput::colorString( 'all', CLIOutput::BLUE ) . ' or '.
						CLIOutput::colorString( 'today', CLIOutput::BLUE ) . ' (=default).',
					'Optional add '.CLIOutput::colorString('-cats Hobby,Home', CLIOutput::BLUE) . 
						', ' . CLIOutput::colorString('-names TTT,Website', CLIOutput::BLUE),
					'and/or ' . CLIOutput::colorString('-devices Laptop,Desktop', CLIOutput::BLUE).
						' to filter for',
					'categories, names and devices (if synced across devices).',
					'Also '.CLIOutput::colorString('-localOnly', CLIOutput::BLUE) . 
						' can be added to ignore external devices from syncs.',
				)
			));
		switch( $commands[0] ) {
			case "day":
				$this->backUntil(time() - 86400, array_slice($commands, 1));
				break;
			case "week":
				$this->backUntil(time() - 604800, array_slice($commands, 1));
				break;
			case "month":
				$this->backUntil(time() - 2628000, array_slice($commands, 1));
				break;
			case "all":
				$this->backUntil(0, array_slice($commands, 1));
				break;
			case "range":
				$this->rangeStats(array_slice($commands, 1));
				break;
			case "today":
				$commands = array_slice($commands, 1);
			default:
				$this->todayview = true;
				$this->backUntil(strtotime("today"), $commands);
		}
	}

	private function rangeStats(array $commands) {
		$cmd = array_values(array_filter(array_slice($commands, 0, 2), function ($d) {
			return preg_match( StatsData::DATE_PREG, $d) === 1;
		})); // create array of range days

		if( count($cmd) === 1){
			$until = strtotime($cmd[0]);
			$forwardTo = $until + 86399;

			$this->todayview = true;
		}
		else if( count($cmd) === 2 ){
			sort( $cmd ); // change s.t. ('start of range', 'end of range')
			$until = strtotime($cmd[0]);
			$forwardTo = strtotime($cmd[1]) + 86399;
		}
		else{
			$this->output->print(
				array('Statistics -> Range',
					array(
						'Please give single day or range of two days',
						array(
							CLIOutput::colorString( 'range 2020-01-11', CLIOutput::BLUE ),
							CLIOutput::colorString( 'range 2020-01-11 2020-01-13', CLIOutput::BLUE ),
						)
					)
				));
			return;
		}

		$this->backUntil($until,
			array_slice($commands, count($cmd)), // shift number of range days
			$forwardTo); 
	}

	private function backUntil(int $timestamp, array $f, int $forwardTo = StatsData::FORWARD_TO_NOW) : void {
		// Name, Device and Cat filtering
		$isCats = false;
		$isNames = false;
		$isDevices = false;
		$allNames = array();
		$allCats = array();
		$allDevices = array();

		// No syn
		$onlyLocal = false;
		foreach( $f as $arg ){
			if( $arg == '-cats' ){
				$isCats = true;
				$isNames = false;
				$isDevices = false;
				continue;
			}
			if( $arg == '-names' ){
				$isCats = false;
				$isNames = true;
				$isDevices = false;
				continue;
			}
			if( $arg == '-devices' ){
				$isCats = false;
				$isNames = false;
				$isDevices = true;
				continue;
			}
			if( $arg == '-localOnly' ){
				$onlyLocal = true;
				continue;
			}

			if( $isNames ){
				$names = explode(',', $arg);
				$names = array_map('trim', $names);
				$names = array_filter( $names, 'InputParser::checkNameInput');
				$allNames = array_merge($allNames, $names);
				$isNames = false;
			}
			if( $isCats ){
				$cats = explode(',', $arg);
				$cats = array_map('trim', $cats);
				$cats = array_filter( $cats, 'InputParser::checkCategoryInput');
				$allCats = array_merge($allCats, $cats);
				$isCats = false;
			}
			if( $isDevices ){
				$devs = explode(',', $arg);
				$devs = array_map('trim', $devs);
				$devs = array_filter( $devs, 'InputParser::checkDeviceName');
				$allDevices = array_merge($allDevices, $devs);
				$isDevices = false;
			}
		}

		$s = new StatsData($timestamp, $forwardTo, $onlyLocal);
		$s->filterData($allNames, $allCats, $allDevices);

		$this->printDataset($s->getAllDatasets());
	}

	private function printDataset(array $data) : void {
		array_multisort( // sort multiple days (s.t. latest days are first)
			array_column( $data, 'begin' ), SORT_DESC,
			$data
		);

		$noExternalDevice = true;
		$combi = array();
		foreach( $data as $d ){
			$key = $d['category'] . '++' . $d['name'];
			$day = date(self::DAYS_DISPLAY, $d['begin']);
			if( !isset($combi[$key])){
				$combi[$key] = array(
					'category' => $d['category'],
					'name' => $d['name'],
					'duration' => 0,
					'times' => 0,
					'days' => array( $day ),
					'devices' => array()
				);
			}
			$combi[$key]['times']++;
			$combi[$key]['duration'] += $d['duration'];

			if( !in_array( $day, $combi[$key]['days'] ) ){
				$combi[$key]['days'][] = $day;
			}
			if( !empty($d['device']) && !in_array( $d['device'], $combi[$key]['devices'] ) ){
				$combi[$key]['devices'][] = $d['device'];
				$noExternalDevice = false;
			}
		}
		$combi = array_values($combi);

		$table = array();
		foreach( $combi as $d ){
			$table[] = array_merge(
				array(
					'Category' => $d['category'],
					'Name' => $d['name'],
					'Time' => $d['duration'],
					'Work Items' => str_pad($d['times'], 4, " ", STR_PAD_LEFT),
					'Days' => implode(', ', array_slice($d['days'], 0, self::DAYS_MAXCOUNT))
						. (count($d['days']) > self::DAYS_MAXCOUNT ? ', ...' : '' )
				),
				$noExternalDevice ? array() : array(
					'Other devices' => implode(', ', array_slice($d['devices'], 0, self::DEVICE_MAXCOUNT))
					. (count($d['devices']) > self::DEVICE_MAXCOUNT ? ', ...' : '' )
				)
			);
		}

		array_multisort(
			array_column( $table, 'Category' ), SORT_ASC,
			array_column( $table, 'Time' ), SORT_DESC,
			array_column( $table, 'Name' ), SORT_ASC,
			$table
		);

		foreach( $table as &$d ){
			$d['Time'] = self::secToTime($d['Time']);
		}

		$this->output->table($table);

		if( $this->todayview ){
			$this->printTodayView($data);
		}
	}

	public static function secToTime(int $t) : string {
		return str_pad(
				($t >= 3600 ? intval($t/3600) . 'h ' : '' ) .
				str_pad(
					intval(($t % 3600) / 60) . 'm',
					3,
					" ",
					STR_PAD_LEFT
				),
				8,
				" ",
				STR_PAD_LEFT
			);
	}

	private function printTodayView(array $data) : void {
		array_multisort(
			array_column( $data, 'begin' ), SORT_ASC,
			$data
		);

		$table = array();
		$i = 0;
		$lastval = null;
		$lastend = 0;
		foreach( $data as $d ){
			if( $lastval === $d['category'] . '++' . $d['name']
				&& $lastend + Config::getSleepTime() > $d['begin'] ){
				$table[$i-1]['Time'] += $d['duration'];
			}
			else{
				$table[$i++] = array(
					'Begin' => date('H:i', $d['begin']),
					'Category' => $d['category'],
					'Name' => $d['name'],
					'Time' => $d['duration']
				);
			}
			$lastend = $d['end'];
			$lastval = $d['category'] . '++' . $d['name'];
		}

		foreach( $table as &$d ){
			$d['Time'] = self::secToTime($d['Time']);
		}

		$this->output->table($table);
	}

}
?>
