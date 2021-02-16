<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}

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
define( 'TaskTimeTerminate', 'CLI' );

require_once( '/php-code/core/load.php' );

// group.json for each user empty session array
$g = new JSONReader('groups');
foreach( $g->getArray() as $group => $data ){
	if(!isset($data['sessions']) || !is_array($data['sessions'])){
		$g->setValue([$group, 'sessions'], array());
	}

	if(!isset($data['caltoken']) || !is_string($data['caltoken']) || strlen($data['caltoken']) !== 50){
		$g->setValue([$group, 'caltoken'], Utilities::randomCode(50, Utilities::ID));
	}
}
?>