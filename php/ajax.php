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

$output = array();
$login = new Login();
if( $login->isLoggedIn() ){
	if(!empty($_GET['term']) && !empty($_GET['type']) &&
		is_string($_GET['term']) && is_string($_GET['type']) &&
		preg_match('/^[A-Za-z0-9\_\-\,]+$/', $_GET['term']) === 1
	){
		$acs = new Autocomplete($login);
		switch( $_GET['type'] ){
			case 'category':
				$output = $acs->completeCategory($_GET['term']);
				break;
			case 'task':
				$output = $acs->completeTask($_GET['term']);
				break;
			default:
				$output['error'] = "Unknown type!";
		}
	}
	else{
		$output['error'] = "Unknown request!";
	}
}
else {
	$output['error'] = "Not logged in!";
}

header('Content-Type: application/json; charset=utf-8');
if(isset($output['error'])){
	http_response_code(401);
}
echo json_encode($output, JSON_PRETTY_PRINT);
?>