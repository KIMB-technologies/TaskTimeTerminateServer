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

		$this->setUpHtml();

		if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
			$cmd = $this->paramsToCmd();
			if( !empty($cmd)){
				$data = new TTTStats($cmd, API::getStorageDir($this->login->getGroup()));

				$this->temp->setContent('GRAPH', PHP_EOL .  print_r($data->getAllResults(), true));
			}
		}
	}

	private function paramsToCmd() : array {
		$error = false;
		$cmd = array();

		$times = array("today", "day", "week", "month", "range", "all");
		if( isset($_POST["time"]) && in_array($_POST["time"], $times)){
			$cmd[] = $_POST["time"];
		}
		else{
			$error = true;
		}
		
		if($_POST["time"] === "range"){
			if(
				isset($_POST["range-from"]) && isset($_POST["range-to"])
				&&
				preg_match( TTTStatsData::DATE_PREG, $_POST["range-from"]) === 1 && preg_match( TTTStatsData::DATE_PREG, $_POST["range-to"]) === 1
			){
				$cmd[] = $_POST["range-from"];
				$cmd[] = $_POST["range-to"];
			}
			else{
				$error = true;
			}
		}

		if(!empty($_POST["cats"]) && preg_match('/^[A-Za-z\-\,]+$/', $_POST["cats"]) === 1){
			$cmd[] = '-cats';
			$cmd[] = $_POST["cats"];
		}

		if(!empty($_POST["names"]) && preg_match('/^[A-Za-z0-9\_\-\,]+$/', $_POST["names"]) === 1){
			$cmd[] = '-names';
			$cmd[] = $_POST["names"];
		}

		if(!empty($_POST["devices"]) && is_array($_POST["devices"])){
			$dev = implode(',', $_POST["devices"]);
			if(preg_match('/^[A-Za-z0-9\-\,]+$/', $dev) === 1){
				$cmd[] = '-devices';
				$cmd[] = $dev;
			}
			else{
				$error = true;
			}
		}

		if($error){
			$this->temp->setContent('NOTEDISABLE','');
			$this->temp->setContent('NOTEMSG','Invalid input given!');
			return array();
		}
		else{
			return $cmd;
		}
	}

	private function setUpHtml(){
		$this->temp->setContent('TODAY', date('Y-m-d'));

		if( is_dir(API::getStorageDir($this->login->getGroup())) ){
			$ds = array();
			foreach( scandir(API::getStorageDir($this->login->getGroup())) as $d){
				if( $d !== '.' && $d !== '..' ){
					$ds[] = array(
						"NAME" => $d,
						"VALUE" => $d
					);
				}
			}
			$this->temp->setMultipleContent('Devices', $ds);
		}
	}

}
?>