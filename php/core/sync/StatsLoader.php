<?php

class StatsLoader {

	private array $locations = array();
	private array $filelist = array();

	private int $until = 0;
	private int $untilDay = 0;
	private int $forward = 0;

	public function __construct(int $time, int  $forwardTo, bool $localOnly){
		$this->locations['local'] = new LocalStatsAccess();
		if( !$localOnly ){
			$c = Config::getStorageReader('config');
			if( $c->isValue(['sync', 'directory']) ){
				$this->locations['directory'] = new DirectoryStatsAccess();
			}
			if( $c->isValue(['sync', 'server']) ){
				$this->locations['server'] = new ServerStatsAccess();
			}
		}

		$this->forward = $forwardTo;
		$this->until = $time;
		$this->untilDay = strtotime(date('Y-m-d', $this->until)); // timestamp 00:00 for day of $until 
		$this->selectUntil();
	}

	private function selectUntil() : void {
		foreach( $this->locations as $location => $access ){
			foreach( $access->listFiles() as $file ){
				if( $file['timestamp'] >= $this->untilDay && $file['timestamp'] <= $this->forward){
					$this->filelist[] = array(
						'file' => $file['file'],
						'device' => $file['device'],
						'location' => $location
					);
				}
			}
		}
	}

	public function getLocalFilelist() : array {
		return array_values(array_column(
				array_filter( $this->filelist, function ($a) {
					return $a['location'] === 'local';
				}),
				'file'
			));
	}

	public function getContents() : array {
		$knownDevices = array();

		$dataset = array();
		foreach( $this->filelist as $f ){
			$hashKey = $f['file'] . '++' . $f['device'];
			if( !in_array( $hashKey, $knownDevices ) ){
				$array = $this->locations[$f['location']]->getFile($f['file'], $f['device']);
				foreach( $array as $key => $a ){
					if($a['end'] < $this->until ){
						unset($array[$key]);
					}
					else {
						$array[$key]['duration'] = $a['end'] - $a['begin'];
						$array[$key]['device'] = $f['device'];
					}
				}
				if( !empty($array )){
					$dataset = array_merge($dataset, $array);
				}
				$knownDevices[] = $hashKey;
			}
		}
		return $dataset;
	}

	public static function saveDayTasks(array $array ) : void {
		$c = Config::getStorageReader('config');
		if( $c->isValue(['sync', 'directory']) ){
			(new DirectoryStatsAccess())->setDayTasks($array);
		}
		if( $c->isValue(['sync', 'server']) ){
			(new ServerStatsAccess())->setDayTasks($array);
		}
	}
}