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

class APIAdd extends API {

	protected function handleAPITask() : void{
		$array = array_values(array_filter( $this->requestData['tasks'], function ($a) {
			return isset($a['begin']) && isset($a['end']) && isset($a['name']) && isset($a['category'])
				&& is_int($a['begin']) && $a['begin'] > 0
				&& is_int($a['end']) && $a['end'] > 0
				&& InputParser::checkNameInput( $a['name'] )
				&& InputParser::checkCategoryInput( $a['category'] );
		}));
		if(empty($array) || !isset($this->requestData['day'])
			|| !is_int($this->requestData['day']) || $this->requestData['day'] < 0 ){
			$this->error('Invalid data.');
			return;
		}

		$groupDir = __DIR__ . '/../../data/' . $this->login->getGroup() . '/' . $this->login->getDeviceName();
		if(!is_dir( $groupDir )){
			if( !mkdir( $groupDir, 0740, true ) ){
				$this->error('Unable to create storage dir.');
				return;
			}
		}
		
		$filename = $groupDir . '/' . date('Y-m-d', $this->requestData['day']) . '.json';
		if(file_put_contents( $filename, json_encode( $array, JSON_PRETTY_PRINT ))){
			$this->output = array( 'ok' );
		}
		else{
			$this->error('Error saving data.');
		}	
	}
}
?>