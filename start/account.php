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

if( !empty( $_ENV['ADMIN_ACCOUNT'] ) && !empty( $_ENV['ADMIN_PASSWORD'] ) ){
	// get username and password
	$password = preg_replace('/[^ -~]/', '', $_ENV['ADMIN_PASSWORD']);
	$name = preg_replace('/[^A-Za-z0-9]/', '', $_ENV['ADMIN_ACCOUNT']) ;

	// check 
	if(empty($name) || empty($password) || strlen($password) >= 200 || strlen($password) <= 4 ){
		echo "ERROR: Password or Username does not match requirements!" . PHP_EOL;
	}
	else{
		//load files
		$g = new JSONReader('groups');

		// create group/ update password
		if(!Login::createNewGroup($g, $name, $password, true)){
			echo "ERROR: Creating grpup/ updating password!" . PHP_EOL;
		}
	}
}
?>