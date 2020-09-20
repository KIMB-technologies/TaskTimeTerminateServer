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

class AddEdit {

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
		if( is_dir(API::getStorageDir($this->login->getGroup())) ){
			$ds = array();
			foreach( scandir(API::getStorageDir($this->login->getGroup())) as $d){
				if( $d !== '.' && $d !== '..' ){
					$ds[] = array("NAME" => $d);
				}
			}
			$this->temp->setMultipleContent('Data', $ds);
		}

		foreach(array( "YEAR" => 'Y', "MON" => 'm', "DAY" => 'd', "HOUR" => 'H', "MIN"  => 'i') as $k => $d){
			$this->temp->setContent($k, date($d));
		}
	}

}
?>