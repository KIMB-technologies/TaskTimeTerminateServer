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
define( 'TaskTimeTerminate', 'GUI' );

require_once( __DIR__ . '/core/load.php' );

$param = new ParamParser();
$login = new Login();
$gui = new WebGUI($param, $login);

if( $login->isLoggedIn() ){
	if($param->isLogoutGet()){
		$login->logUserOut();
	}
}
else {
	if( $param->isLoginPost() ){
		$token = $login->userLogin(
			$param->loginPost('group'),
			$param->loginPost('password'),
			!empty($_POST['stayloggedin']) && $_POST['stayloggedin'] === 'yes'
		);
		if(!is_null($token)){
			$gui->showLoginToken($token);
		}
	}
	else if( $param->isSessionPost() ) {
		$login->sessionLogin(
			$param->loginPost('group'),
			$param->loginPost('token')
		);
	}
}

if( isset($_GET['err']) && in_array($_GET['err'], array(404, 403)) ){
	$gui->errorPage($_GET['err']);
}
else{
	if( $login->isLoggedIn() ){
		switch ($param->getTask()) {
			case ParamParser::TASK_ACCOUNT:
				$gui->accountManage();
				break;
			case ParamParser::TASK_DEVICES:
				$gui->deviceManage();
				break;
			case ParamParser::TASK_RECORD:
				$gui->addTaskRecord();
				break;
			case ParamParser::TASK_STATS:
				$gui->showStats();
				break;
			case ParamParser::TASK_HOME:
			case ParamParser::TASK_NONE:
			default:
				$gui->home();
		}
	}
	else{
		$gui->loginForm();
	}
}
?>