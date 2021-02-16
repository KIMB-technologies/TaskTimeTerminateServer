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

die("Coming soon!");

require_once( __DIR__ . '/../core/load.php' );

$login = new Login();

if($login->isLoggedIn()){
	$cal = new Calendar($login);

}
?>