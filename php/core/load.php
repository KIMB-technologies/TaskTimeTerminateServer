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

// error reporting in dev
error_reporting( !empty($_ENV['DEVMODE']) && $_ENV['DEVMODE'] == 'true' ? E_ALL : 0 );
// Inits
new Config();
Reader::changePath(realpath(__DIR__ . '/../data/'));
if( TaskTimeTerminate === 'GUI' ){
	session_name( 'TaskTimeTerminate' );
	session_start();
}

// Autoloader
spl_autoload_register(function ($class) {
	if( is_string($class) && preg_match( '/^[A-Za-z0-9]+$/', $class ) === 1 ){
		$candidates = array(
			__DIR__ . '/',
			__DIR__ . '/api/',
			__DIR__ . '/sync/'
		);
		foreach( $candidates as $cand ){
			$classfile = $cand . $class . '.php';
			if( is_file($classfile) ){
				require_once( $classfile );
				break;
			}
		}
	}
});
?>