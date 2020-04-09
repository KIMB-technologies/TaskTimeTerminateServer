<?php
class StatsData {

	const FORWARD_TO_NOW = -1;
	const DATE_PREG = '/^\d{4}-(0|1)\d-[0-3]\d$/';

	private StatsLoader $loader;
	private bool $localOnly;

	private array $dataset = array();

	public function __construct(int $time = 0, int $forwardTo = self::FORWARD_TO_NOW, bool $localOnly = true) {
		$this->localOnly = $localOnly;
		
		$this->loader = new StatsLoader(
			$time,
			( $forwardTo  === self::FORWARD_TO_NOW ) ? time() : $forwardTo,
			$this->localOnly
		);
	}

	private function loadContents() : void {
		if( $this->dataset === array() ){
			$this->dataset = $this->loader->getContents();
		}
	}

	public function filterData(array $names = array(), array $cats = array(), array $devices = array()){
		$this->loadContents();
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
	
	public function getLocalNames() : array {
		$this->loadContents();

		return array_unique(array_column(
				array_filter( $this->dataset, function ($a) {
					return empty($a['device']); // => no device means local
				}),
				'name'
			));
	}
	
	public function merge($merge, $mergeTo) : bool {
		if( $merge === $mergeTo){
			return true;
		}

		$ret = true;
		foreach( $this->loader->getLocalFilelist() as $f ){
			$r = Config::getStorageReader(substr($f, 0, -5));
			$rUnchanged = true;
			foreach( $r->getArray() as $k => $a ){
				if( $a['name'] === $merge ){
					$ret &= $r->setValue([$k, 'name'], $mergeTo);
					$rUnchanged = false;
				}
			}
			if(!$rUnchanged){
				StatsLoader::saveDayTasks( $r->getArray() );
			}
			unset($r);
		}

		$this->dataset = array(); //force to reload contents on next use
		return $ret;
	}

	public static function getAllCategories() : array {
		$r = Config::getStorageReader('config');
		return $r->isValue(['categories']) ? $r->getValue(['categories']) : array();
	}
}
?>