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

class DataAccess {

	private Login $login;
	private Share $share;

	private bool $error = false;

	private array $cmd = array();
	private array $cmdSemantical = array();

	private array $shares = array();

	public function __construct( Login $login, Share $share ) {
		$this->login = $login;
		$this->share = $share;
	}

	public function setParams(string $time, string $rFrom, string $rTo, string $cats, string $names, array $devices ) : void {
		$this->cmd = array();
		$this->cmdSemantical = array();

		$times = array("cDay", "cWeek", "cMonth", "cYear", "lDay", "lWeek", "lMonth", "lYear", "range", "all");
		if( isset($time) && in_array($time, $times)){
			$this->cmd[] = $time;
			$this->cmdSemantical['time'] = $time;
		}
		else{
			$this->error = true;
		}
			
		if($time === "range"){
			if(
				!empty($rFrom) && !empty($rTo)
				&&
				preg_match( TTTStatsData::DATE_PREG, $rFrom) === 1 && preg_match( TTTStatsData::DATE_PREG, $rTo) === 1
			){
				$this->cmd[] = $rFrom;
				$this->cmd[] = $rTo;
				$this->cmdSemantical['range'] = array($rFrom, $rTo);
			}
			else if( !empty($rFrom) && preg_match( TTTStatsData::DATE_PREG, $rFrom) === 1 ){
				$this->cmd[] = $rFrom;
				$this->cmdSemantical['range'] = $rFrom;
			}
			else if( !empty($rTo) && preg_match( TTTStatsData::DATE_PREG, $rTo) === 1){
				$this->cmd[] = $rTo;
				$this->cmdSemantical['range'] = $rTo;
			}
			else{
				$this->error = true;
			}
		}
	
		if(!empty($cats) && preg_match('/^[A-Za-z\-\,]+$/', $cats) === 1){
			$this->cmd[] = '-cats';
			$this->cmd[] = $cats;
			$this->cmdSemantical['cats'] = $cats;
		}
	
		if(!empty($names) && preg_match('/^[A-Za-z0-9\_\-\,]+$/', $names) === 1){
			$this->cmd[] = '-names';
			$this->cmd[] = $names;
			$this->cmdSemantical['names'] = $names;
		}
	
		if(!empty($devices) && is_array($devices)){
			$dev = implode(',', $devices);
			if(preg_match('/^[A-Za-z0-9\-\,]+$/', $dev) === 1){
				$this->cmd[] = '-devices';
				$this->cmd[] = $dev;
				$this->cmdSemantical['devices'] = $dev;
			}
			else{
				$this->error = true;
			}
		}
	}

	public function requestShare(array $requests) : void {
		$this->shares = array();
		$this->cmdSemantical['shares'] = array();

		// share
		$withme = $this->share->getSharedWithMe();
		foreach($requests as $sh){
			if(is_string($sh)){
				$sh = explode('::', $sh);
				$gr = preg_replace('/[^A-Za-z0-9]/', '', $sh[0]);
				if(InputParser::checkCategoryInput($sh[1]) && !empty($gr) ){
					if( in_array( array( 
							'category' => $sh[1],
							'group' => $gr
						) , $withme )
					){
						if(!isset($this->shares[$gr])){
							$this->shares[$gr] = array();
						}
						$this->shares[$gr][] = $sh[1];

						$this->cmdSemantical['shares'][] = $gr . '::' . $sh[1];
					}
				}
			}
		}
		$this->cmdSemantical['shares'] = implode(',', $this->cmdSemantical['shares']);
	}
	
	public function getCmd() : array {
		return $this->cmd;
	}

	public function getCmdSemantical() : array {
		return $this->cmdSemantical;
	}

	public function hasError() : bool {
		return $this->error;
	}
	

	public function getData() : array {
		if( !$this->hasError() && !empty($this->cmd) ){
			$data = new TTTStats($this->cmd, API::getStorageDir($this->login->getGroup()));
			$allData = $data->getAllResults();

			if(!empty($this->shares)){
				$this->addShares($allData);
			}

			return $allData;
		}
		else{
			return array();
		}		
	}	

	private function addShares( array &$allData ) : void {
		$cmdC = $this->cmd;

		if(in_array('-devices', $cmdC)){
			$did = array_search('-devices', $cmdC);
			unset($cmdC[$did], $cmdC[$did+1]);
		}
		if(!in_array('-cats', $cmdC)){
			$cmdC[] = '-cats';
			$cmdC[] = '';
		}
		$cid = array_search('-cats', $cmdC) + 1;

		foreach($this->shares as $group => $cats){
			$cmdC[$cid] = implode(',', $cats);
			$sd = new TTTStats($cmdC, API::getStorageDir($group));
			$data = $sd->getAllResults();
			array_walk_recursive( $data, function (&$value, $key) use (&$group) {
				if(in_array($key, ['name', 'category', 'Name', 'Category', 'Other devices', 'device'])){
					$value = $group . '::' . $value;
				}
			});
			foreach(['table','plain','combi','today'] as $key ){
				if(isset($allData[$key]) && isset($data[$key]) ){
					$allData[$key] = array_merge($allData[$key], $data[$key]);
				}
			}
		}
	}

}
?>