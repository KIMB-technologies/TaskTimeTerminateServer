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
		$this->login;
		$this->requestData;
		/* 
		array(
			'day' => 1585075742,
			'tasks' => array(
					array(
						"begin" => 1585075742,
						"end" => 1585076102,
						"name" => "Test",
						"category" => "Huii"
					), ...
				)
			)
		);
		*/
	 
		// $this->error('Message');
		$this->output = array( 'ok' );
	}

}
?>