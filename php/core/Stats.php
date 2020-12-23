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

	// max plain data elements for plots
	const MAX_PLAIN_ELEMENTS = 2000;

	private Template $temp;
	private Login $login;
	private Share $share;

	public function __construct( Template $temp, Login $login ) {
		$this->login = $login;
		$this->temp = $temp;
		$this->share = new Share($this->login);

		$this->setUpHtml();

		if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
			$cmd = $this->paramsToCmd();
			if( !empty($cmd) ){
				$data = new TTTStats($cmd, API::getStorageDir($this->login->getGroup()));
				$allData = $data->getAllResults();

				// share
				if( isset($_POST['shares']) && is_array($_POST['shares']) ){
					$withme = $this->share->getSharedWithMe();
					$shares = array();
					foreach($_POST['shares'] as $sh ){
						if(is_string($sh)){
							$sh = explode('::', $sh);
							$gr = preg_replace('/[^A-Za-z0-9]/', '', $sh[0]);
							if(InputParser::checkCategoryInput($sh[1]) && !empty($gr) ){
								if( in_array( array( 
										'category' => $sh[1],
										'group' => $gr
								 	) , $withme )
								){
									if(!isset($shares[$gr])){
										$shares[$gr] = array();
									}
									$shares[$gr][] = $sh[1];
								}
							}
						}
					}
					$this->addShares($allData, $cmd, $shares);
				}

				$this->displayContent($allData);
			}
		}
	}

	private function displayContent(array $data) : void {
		$this->temp->setContent('COMBIDATA', json_encode($data['combi']));
		$this->temp->setContent('PLAINDATA', json_encode(array_slice($data['plain'], 0, self::MAX_PLAIN_ELEMENTS)));
		if(count($data['combi']) > self::MAX_PLAIN_ELEMENTS){
			$this->temp->setContent('LESSDATADISABLE', '');
		}
		$this->temp->setContent('TABLEA', $this->arrayToTable($data['table']));
		if(isset($data['today'])){
			$this->temp->setContent('TABLEB', $this->arrayToTable($data['today']) );
			$this->temp->setContent('SINGLEDAYDATA', json_encode($data['today']));
		}
	} 

	private function addShares( array &$allData, array $cmd, array $shares) : void {
		if(in_array('-devices', $cmd)){
			$did = array_search('-devices', $cmd);
			unset($cmd[$did], $cmd[$did+1]);
		}
		if(!in_array('-cats', $cmd)){
			$cmd[] = '-cats';
			$cmd[] = '';
		}
		$cid = array_search('-cats', $cmd) + 1;

		foreach($shares as $group => $cats){
			$cmd[$cid] = implode(',', $cats);
			$sd = new TTTStats($cmd, API::getStorageDir($group));
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

	private function arrayToTable(array $data) : string {
		$table = "<table class=\"table table-striped table-responsive-sm statstable\">";
		$head = false;
		$lastCat = '';
		foreach($data as $row){
			if(!$head){
				$table .= "<tr><thead class=\"thead-dark\">";
				foreach($row as $col => $val){
					$table .= "<th scope=\"col\">". $col ."</th>";
				}
				$table .= "</thead></tr>";
				$head = true;
			}
			$table .= "<tr class=" . ( $row['Category'] !== $lastCat ? "\"table-active\"" : "" ) . ">";
			foreach($row as $key => $val){
				if($key === 'Category'){
					if( $val === $lastCat ){
						$table .= "<td></td>";
					}
					else{
						$table .= "<th scope=\"row\">". $val ."</th>";

					}
					$lastCat = $val;
				}
				else{
					$table .= "<td>". $val ."</td>";
				}
			}
			$table .= "</tr>";
		}
		return $table . "</table>";
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
			else if( isset($_POST["range-from"]) && preg_match( TTTStatsData::DATE_PREG, $_POST["range-from"]) === 1 ){
				$cmd[] = $_POST["range-from"];
			}
			else if( isset($_POST["range-to"]) && preg_match( TTTStatsData::DATE_PREG, $_POST["range-to"]) === 1){
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
			$this->temp->setContent('CMDDISABLE','');
			$this->temp->setContent('CMD', 'ttt s ' . implode(' ', $cmd));
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

		$withme = $this->share->getSharedWithMe();
		if(!empty($withme)){
			$this->temp->setContent('SHARESDIABLE', '');
			$d = array();
			foreach($withme as $w){
				$d[] = array(
					'%%VALUE%%' => $w['group'] . '::' . $w['category'],
					'%%NAME%%' => '"' . $w['category'] . '" from "' . $w['group'] . '"'
				);
			}
			$this->temp->setMultipleContent('Shares', $d);
		}

		$this->temp->setContent('GRAPHES', json_encode(
			array_values(array_map(
				function ($s) {
					return substr($s, 0, -3);
				},
				array_filter(
					scandir(__DIR__ . '/../load/graphs/'),
					function ($s) {
						return strlen($s) > 4 && substr($s, -3) === '.js';
					}
				)
			))
		));
	}

}
?>