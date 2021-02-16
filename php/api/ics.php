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
define( 'TaskTimeTerminate', 'API' );

require_once( __DIR__ . '/../core/load.php' );

if(
	!empty($_GET['group']) && !empty($_GET['token'])
	&&
	is_string($_GET['group']) && is_string($_GET['token'])
	&&
	preg_match(API::GROUP_NAME_PREG, $_GET['group']) === 1 && strlen($_GET['token']) === 50
){
	$login = new Login($_GET['group'], $_GET['token']);

	if($login->isLoggedIn()){
		$dataAccess = new DataAccess($login, new Share($login));

		// parse range
		$rFrom = "";
		$rTo = "";
		if(isset($_GET['range']) && is_string($_GET['range'])){
			if(strpos($_GET['range'], ',') === false ){
				$rFrom = $_GET['range'];
			}
			else{
				$parts = explode(',', $_GET['range']);
				$rFrom = $parts[0];
				$rTo = $parts[1];
			}
		}

		// parse devices
		$devices = array();
		if(isset($_GET['devices']) && is_string($_GET['devices'])){
			$devices = explode(',', $_GET['devices']);
		}

		// set parsed filters
		$dataAccess->setParams(
			$_GET['time'], $rFrom, $rTo,
			$_GET['cats'] ?? "", $_GET['names'] ?? "", $devices
		);

		// add shares
		if( isset($_GET['shares']) && is_string($_GET['shares']) ){
			$dataAccess->requestShare(explode(',', $_GET['shares']));
		}

		if(!$dataAccess->hasError() ){
			// create calendar
			$cal = new Calendar($login);
			$ics = $cal->generateICS($dataAccess);
		
			// output
			header('Content-type: text/calendar; charset=utf-8');
			header('Content-Disposition: inline; filename=ttt.ics');
			die($ics);
		}
		else{
			// error message
			http_response_code(400);
			header('Content-Type: text/plain; charset=utf-8');
			die("Error – Invalid filter!");
		}
	}
}

// error message
http_response_code(403);
header('Content-Type: text/plain; charset=utf-8');
die("Error – Failed to authenticate!");
?>