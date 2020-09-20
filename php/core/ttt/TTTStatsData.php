<?php
class TTTStatsData {

	const FORWARD_TO_NOW = -1;
	const DATE_PREG = '/^\d{4}-(0|1)\d-[0-3]\d$/';

	private int $begin;
	private int $end;
	private string $path;
	private array $dataset = array();

	public function __construct(int $time = 0, int $forwardTo = self::FORWARD_TO_NOW, string $path) {
		$this->begin = $time;
		$this->end = ($forwardTo  === self::FORWARD_TO_NOW ) ? time() : $forwardTo;
		$this->path = $path;

		$this->loadContents();
	}

	private function loadContents() : void {
		$files = array();
		foreach(array_diff(scandir($this->path), ['.','..']) as $device ){
			if( is_dir($this->path . $device) ){
				$fi = array_filter(
						scandir( $this->path . $device ),
						function ($f) {
							return preg_match(API::FILENAME_PREG, $f) === 1;
						}
					);
				foreach($fi as $f){
					$ts = strtotime(substr($f, 0, -5));
					if($ts >= $this->begin && $ts <= $this->end){
						$files[] = array(
							'file' => $this->path . $device . '/' . $f,
							'device' => $device
						);
					}
				}
				
			}
		}

		$dataset = array();
		foreach( $files as $f ){
			$array = json_decode(file_get_contents($f['file']), true);
			foreach( $array as $key => $a ){
				if($a['end'] < $this->begin ){
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
		}

		$this->dataset = $dataset;
	}

	public function filterData(array $names = array(), array $cats = array(), array $devices = array()){
		foreach( $this->dataset as $k => $d ){
			if(
				(!empty($cats) && !in_array($d['category'], $cats))
				||
				(!empty($names) && !in_array($d['name'], $names))
				||
				(!empty($devices) && !in_array($d['device'], $devices))
			){
				unset( $this->dataset[$k] );
			}
		}
	}

	public function getAllDatasets() : array {
		return $this->dataset;
	}

}
?>