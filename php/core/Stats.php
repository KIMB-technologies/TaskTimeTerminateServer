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

	// max and default plain data elements for plots
	const MAX_PLAIN_ELEMENTS = 200000;
	const DEFAULT_PLAIN_ELEMENTS = 2000;

	private Template $temp;
	private Login $login;
	private Share $share;
	private DataAccess $dataAccess;
	private Calendar $calendar;

	public function __construct( Template $temp, Login $login ) {
		$this->login = $login;
		$this->temp = $temp;
		$this->share = new Share($this->login);
		$this->dataAccess = new DataAccess($this->login, $this->share);
		$this->calendar = new Calendar($this->login);
		
		$this->setUpHtml();

		if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
			$this->dataAccess->setParams(
				$_POST["time"], $_POST["range-from"] ?? "", $_POST["range-to"] ?? "",
				$_POST["cats"] ?? "", $_POST["names"] ?? "", $_POST["devices"] ?? array()
			);
			if( isset($_POST['shares']) && is_array($_POST['shares']) ){
				$this->dataAccess->requestShare($_POST['shares']);
			}

			if(!$this->dataAccess->hasError()){
				$this->displayContent($this->dataAccess->getData());

				$this->temp->setContent('DATADISABLE','');
				$this->temp->setContent('CMD', 'ttt s ' . implode(' ', $this->dataAccess->getCmd()));
				$this->temp->setContent('CALURL', $this->calendar->getLink( $this->dataAccess ) );
			}
			else{
				$this->temp->setContent('NOTEDISABLE','');
				$this->temp->setContent('NOTEMSG','Invalid input given!');
				return array();
			}
		}
	}

	private function displayContent(array $data) : void {
		if(!empty($_POST['plainlimit']) ){
			$plainLimit = intval($_POST['plainlimit']);
			if( $plainLimit < self::DEFAULT_PLAIN_ELEMENTS ){
				$plainLimit = self::DEFAULT_PLAIN_ELEMENTS;
			}
			else if( $plainLimit > self::MAX_PLAIN_ELEMENTS ){
				$plainLimit = self::MAX_PLAIN_ELEMENTS;
			}
		}
		else {
			$plainLimit = self::DEFAULT_PLAIN_ELEMENTS;
		}

		$this->temp->setContent('COMBIDATA', json_encode($data['combi']));
		$this->temp->setContent('PLAINDATA', json_encode(array_slice($data['plain'], 0, $plainLimit)));
		if(count($data['plain']) > $plainLimit){
			$this->temp->setContent('LESSDATADISABLE', '');
		}
		$this->temp->setContent('TABLEA', $this->arrayToTable($data['table']));
		if(isset($data['today'])){
			$this->temp->setContent('TABLEB', $this->arrayToTable($data['today']) );
			$this->temp->setContent('SINGLEDAYDATA', json_encode($data['today']));
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

		$this->temp->setContent('DEFAULT_PLAIN_ELEMENTS', self::DEFAULT_PLAIN_ELEMENTS );
	}

}
?>