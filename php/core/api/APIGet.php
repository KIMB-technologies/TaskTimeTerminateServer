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

class APIGet extends API {

	protected function handleAPITask() : void {
		if( !InputParser::checkDeviceName($this->requestData['device']) ||
			preg_match(parent::FILENAME_PREG, $this->requestData['file']) !== 1
		){
			$this->error('Invalid device or filename format.');
			return;
		}
		$device = $this->requestData['device'];
		$file = $this->requestData['file'];
		$groupPath = __DIR__ . '/../../data/' . $this->login->getGroup() . '/' . $device . '/' . $file;

		if( is_file($groupPath) ){
			$c = json_decode(file_get_contents($groupPath), true);
			if( !is_array($c) || is_null($c) ){
				$this->error('Invalid file content in requested file.');
			}
			else{
				$this->output = $c;
			}
		}
		else{
			$this->error('Invalid file requested.');
		}
	}
}
?>