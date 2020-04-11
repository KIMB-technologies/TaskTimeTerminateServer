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
		/* array(
			"file" => "2020-03-12.json",
			"device" => "Test"
		) */
	 
		// $this->error('Message');
		$this->output = array(array(
			"begin" => 1585172769,
			"end" => 1585173369,
			"name" => "2020-03-12",
			"category" => "Huii"
		),
		array(
			"begin" => 1585173613,
			"end" => 1585174213,
			"name" => "2020-03-12",
			"category" => "Huii"
		),
		array(
			"begin" => 1585174217,
			"end" => 1585174517,
			"name" => "2020-03-12",
			"category" => "Huii"
		));
	}
}
?>