<?php
/** 
 * TaskTimeTerminate Sync-Server
 * https://github.com/KIMB-technologies/TaskTimeTerminate
 * 
 * (c) 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'TaskTimeTerminate' ) or die('Invalid Endpoint!');

class Autocomplete {

	const CACHE_TIME = 3600;

	private array $tasks = array();
	private array $categories = array();

	public function __construct(Login $login) {
		$this->loadAnswers($login->getGroup());
	}

	private function loadAnswers(string $group) : void  {
		$r = new JSONReader('cache_' . $group);
		if(
			$r->isValue(['name']) && $r->isValue(['category']) && $r->isValue(['time']) &&
			$r->getValue(['time']) + self::CACHE_TIME > time()
		){
			$this->tasks = $r->getValue(['name']);
			$this->categories = $r->getValue(['category']);
		}
		else{
			$data = $this->refreshCache($group);
			$data['time'] = time();

			$this->tasks = $data['name'];
			$this->categories = $data['category'];

			$r->setArray($data);
		}
	}


	private function refreshCache(string $group) : array  {
		$stats = new TTTStats(['all'], API::getStorageDir($group));
		$combi = $stats->getAllResults()['combi'];

		$data = array();
		foreach(array('name', 'category') as $col){
			$data[$col] = array();
			foreach(array_values(array_unique(array_column($combi, $col))) as $v){
				$data[$col][str_replace(['-', '_'], '', strtolower($v))] = $v;
			}
		}
		return $data;
	}

	private function stripMultiple( string &$prefix ) : string {
		$pos = strrpos($prefix,',');
		if( $pos === false ){
			return "";
		}
		else{
			$static = substr($prefix, 0, $pos+1);
			$prefix = substr($prefix, $pos+1);
			return $static;
		}
	}

	public function completeTask(string $prefix) : array {
		$static = $this->stripMultiple($prefix);
		return array_map( fn($s) => $static . $s, $this->getCompletes( $prefix, $this->tasks ) );
	}

	public function completeCategory(string $prefix) : array {
		$static = $this->stripMultiple($prefix);
		return array_map( fn($s) => $static . $s, $this->getCompletes( $prefix, $this->categories ) );
	}

	private function getCompletes( string $prefix, array $possibilities ) : array {
		$prefix = str_replace(['-', '_'], '', strtolower($prefix));
		$prefLen = strlen($prefix);

		$answers = array();
		$sims = array();

		$count = 0;
		foreach($possibilities as $search => $cand ){
			$percent = 0;
			if( $prefLen > 2 ){
				similar_text( $prefix, $search, $percent );
				if( $percent > 30 ){
					$sims[$cand] = $percent;
				}
			}
			if( substr($search, 0, $prefLen) == $prefix || $percent > 70 ){
				$answers[] = $cand;

				$count++;
				if($count > 10){
					break;
				}
			}
		}
		if( $count < 10){
			arsort($sims, SORT_NUMERIC);
			$answers = array_unique(array_merge($answers, array_slice(array_keys($sims), 0, 10 - $count)));
		}
		return array_values($answers);
	}
}
?>