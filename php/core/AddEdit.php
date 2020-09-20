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
	private string $serverDir;

	public function __construct( Template $temp, Login $login ) {
		$this->login = $login;
		$this->temp = $temp;
		$this->serverDir = API::getStorageDir($this->login->getGroup(), 'server');

		/**
		 * TODO
		 */

		if( $_SERVER['REQUEST_METHOD'] === 'POST'){
			$this->addNew();
		}
		if( isset( $_GET['day'] ) ){
			$this->viewDay($_GET['day'] . '.json');
		}

		$this->setUpHtml();
	}

	private function viewDay(string $file) : void {
		if(preg_match(API::FILENAME_PREG, $file) !== 1 ){
			$this->temp->setContent('NOTEDISABLE','');
			$this->temp->setContent('NOTEMSG','Invalid format for day!');
			return;
		}
		$r = new JSONReader(API::getStorageDir($this->login->getGroup(), 'server', true) . substr($file, 0, -5));

		// delete one?
		if( isset( $_GET['delete'] ) && is_numeric($_GET['delete']) ){
			$id = intval($_GET['delete']);
			if( $r->isValue([$id]) ){
				$r->setValue([$id], null);
			}
		}

		$a = array();
		foreach($r->getArray() as $id => $t){
			$a[] = array(
				'BEGIN' => date('H:i', $t['begin']),
				'END' => date('H:i', $t['end']),
				'NAME' => $t['name'],
				'CAT' => $t['category'],
				'ID' => $id
			);
		}
		$this->temp->setMultipleContent('Day', $a);

		$this->temp->setContent('DAYDISABLED', '');
		$this->temp->setContent('SELCTEDDAY', substr($file, 0, -5));

		if(empty($r->getArray())){
			$r->__destruct();
			JSONReader::deleteFile(API::getStorageDir($this->login->getGroup(), 'server', true) . substr($file, 0, -5));
		}
	}

	private function addNew() : void {
		$this->temp->setContent('NOTEDISABLE','');

		$fourDigits = array('begin_Y', 'end_Y');
		$twoDigits = array('begin_m', 'begin_d', 'begin_H', 'begin_i', 'end_m', 'end_d', 'end_H', 'end_i');

		$ok = true;
		$ok &= isset($_POST['task']) && InputParser::checkNameInput($_POST['task']);
		$ok &= isset($_POST['category']) && InputParser::checkCategoryInput($_POST['category']);

		foreach($fourDigits as $k){
			$ok &= isset($_POST[$k]) && preg_match('/^\d\d\d\d$/', $_POST[$k]) === 1;
		}
		foreach($twoDigits as $k){
			if( isset($_POST[$k]) && preg_match('/^\d$/', $_POST[$k]) === 1 ){
				$_POST[$k] = '0' . $_POST[$k];
			}
			$ok &= isset($_POST[$k]) && preg_match('/^\d\d$/', $_POST[$k]) === 1;
		}

		if(!$ok){
			$this->temp->setContent('NOTEMSG','Invalid format for new task!');
		}
		else{
			$begin = $this->generateTimestamp($_POST, 'begin');
			$end = $this->generateTimestamp($_POST, 'end');
			if( $begin < $end ){
				$task = array(
					'name' => $_POST['task'],
					'category' => $_POST['category'],
					'begin' => $begin,
					'end' => $end
				);

				if($this->initDir()){
					$r = new JSONReader(API::getStorageDir($this->login->getGroup(), 'server', true) . date('Y-m-d', $begin));
					if($r->setValue([null], $task)){
						$this->temp->setContent('NOTEMSG','Added task.');
						return;
					}
				}
				$this->temp->setContent('NOTEMSG','Unable to save new task.');
			}
			else{
				$this->temp->setContent('NOTEMSG','End has to be later than begin!');
			}
		}
	}

	private function initDir() : bool{
		if(!is_dir( $this->serverDir )){
			if( !mkdir( $this->serverDir, 0740, true ) ){
				return false;
			}
		}
		return true;
	}

	private function generateTimestamp(array $post, string $prefix) : int{
		$vals = array();
		$format = array('Y', 'm', 'd', 'H', 'i');
		foreach( $format as $key ){
			$vals[] = $_POST[$prefix . '_' . $key];
		}
		$d = DateTime::createFromFormat(implode('-', $format), implode('-', $vals), Config::getTimezone());
		return $d->getTimestamp();
	}

	private function setUpHtml(){
		if( is_dir($this->serverDir) ){
			$ds = array();
			foreach( scandir($this->serverDir) as $d){
				if( preg_match(API::FILENAME_PREG, $d) === 1 ){
					$ds[] = array("DAY" => substr($d, 0, -5));
				}
			}
			$this->temp->setMultipleContent('Days', $ds);
		}

		foreach(array( "YEAR" => 'Y', "MON" => 'm', "DAY" => 'd', "HOUR" => 'H', "MIN"  => 'i') as $k => $d){
			$this->temp->setContent($k, date($d));
		}
	}

}
?>