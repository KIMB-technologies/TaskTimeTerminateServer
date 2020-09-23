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

class APIList extends API {

	protected function handleAPITask() : void{
		$groupDir = parent::getStorageDir($this->login->getGroup());

		// send range?
		if( isset($this->requestData['timeMin']) && isset($this->requestData['timeMax']) &&
			is_int($this->requestData['timeMin']) && is_int($this->requestData['timeMax'])
		){
			$timeMin = $this->requestData['timeMin'];
			$timeMax = $this->requestData['timeMax'];
		}
		if(empty($timeMin) || $timeMin < 0 ){
			$timeMin = 0;
		}
		if(empty($timeMax) || $timeMax < 0 ){
			$timeMax = time();
		}

		$files = array();
		if( is_dir($groupDir) ){
			foreach(array_diff(scandir($groupDir), ['.','..']) as $dir ){
				if( $dir !== $this->login->getDeviceName() && is_dir($groupDir . '/' . $dir) ){
					$fi = array_filter(
							scandir( $groupDir . '/' . $dir ),
							function ($f) {
								return preg_match(parent::FILENAME_PREG, $f) === 1;
							}
						);
					foreach($fi as $f){
						$time = strtotime(substr($f, 0, -5));
						if( $time >= $timeMin && $time <= $timeMax){
							$files[] = array(
								'timestamp' => $time,
								'file' => $f, 
								'device' => $dir
							);
						}
					}
				}
			}
		}
		$this->output = $files;
	}
}
?>