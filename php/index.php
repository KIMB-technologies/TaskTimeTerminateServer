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
if( !$login->isLoggedIn() && $param->isLoginPost() ){
	$login->userLogin($param->loginPost('group'), $param->loginPost('password'));
}
$gui = new WebGUI($param, $login);

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
?>