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
	 
		// $this->error('Message');
		$this->output = array(
			array(
				'file' => '2020-03-12.json',
				'timestamp' => strtotime('2020-03-12'),
				'device' => 'Test'
			),
			array(
				'file' => '2020-03-22.json',
				'timestamp' => strtotime('2020-03-22'),
				'device' => 'Test'
			)
		);
	}

}
?>