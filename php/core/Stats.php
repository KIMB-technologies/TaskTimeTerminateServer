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

class Stats {

	private Template $temp;
	private Login $login;

	public function __construct( Template $temp, Login $login ) {
		$this->login = $login;
		$this->temp = $temp;

		/**
		 * TODO
		 */

		$this->setUpHtml();
	}

	private function setUpHtml(){
		
	}

}
?>