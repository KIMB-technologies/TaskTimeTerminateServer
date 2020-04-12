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

class ParamParser {

	const TASK_NONE = 0, TASK_HOME = 1,
		TASK_ACCOUNT = 2, TASK_DEVICES = 3,
		TASK_RECORD = 4, TASK_STATS = 5;
	private int $task;
	private array $taskIDList = array(
		self::TASK_NONE => '',
		self::TASK_HOME => 'home',
		self::TASK_ACCOUNT => 'account',
		self::TASK_DEVICES => 'devices',
		self::TASK_RECORD => 'record',
		self::TASK_STATS => 'stats'
	);

	public function __construct() {
		if( isset($_GET['task']) && in_array($_GET['task'], $this->taskIDList, true) ){
			$this->task = array_search($_GET['task'], $this->taskIDList, true);
		}
		else{
			$this->task = self::TASK_HOME;
		}
	}

	public function isLoginPost() : bool {
		return $_SERVER['REQUEST_METHOD'] === 'POST' &&
			!empty($_POST['group']) && !empty($_POST['password']); 
	}

	public function loginPost(string $name) : string {
		if( $name === 'group' ){
			return !empty($_POST['group']) ? preg_replace('/[^A-Za-z0-9]/', '', $_POST['group']) : '';
		}
		else if($name === 'password' ){
			if( !empty($pw) && is_string($_POST['password']) ) {
				$pw = preg_replace('/[^ -~]/', '', $_POST['password']);
				if( strlen($pw) < 200 && strlen($pw) > 4 ){
					return $pw;
				}
			}
		}
		return '';
	}

	public function getTask() : int {
		return $this->task;
	}
}
?>