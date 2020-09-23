<?php
class TTTStats {

	const DAYS_DISPLAY = 'd.m';
	const DAYS_MAXCOUNT = 5;
	const DEVICE_MAXCOUNT = 3;

	private bool $todayview = false;
	private string $path;
	private array $data = array();

	public function __construct(array $commands, string $path) {
		$this->path = $path;
		$this->parseCommands($commands);
	}

	private function parseCommands(array $commands) : void {
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
			return preg_match( TTTStatsData::DATE_PREG, $d) === 1;
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

		$this->backUntil($until,
			array_slice($commands, count($cmd)), // shift number of range days
			$forwardTo); 
	}

	private function backUntil(int $timestamp, array $f, int $forwardTo = TTTStatsData::FORWARD_TO_NOW) : void {
		// Name, Device and Cat filtering
		$isCats = false;
		$isNames = false;
		$isDevices = false;
		$allNames = array();
		$allCats = array();
		$allDevices = array();

		// No syn
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

		$s = new TTTStatsData($timestamp, $forwardTo, $this->path);
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
			$d['Time'] = TTTTime::secToTime($d['Time']);
		}

		$this->printData($table, 'table');
		$this->printData($data, 'plain');
		$this->printData($combi, 'combi');
		

		if( $this->todayview ){
			$this->printTodayView($data);
		}
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
				&& $lastend + 60 > $d['begin'] ){
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
			$d['Time'] = TTTTime::secToTime($d['Time']);
		}

		$this->printData($table, 'today');
	}

	private function printData(array $data, string $what) : void {
		$this->data[$what] = $data;
	}

	public function getAllResults() : array {
		return $this->data;
	}
}
?>
