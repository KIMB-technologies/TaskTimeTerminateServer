<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}
require_once(__DIR__ . '/api.php');

$client = new APIClient(
	"http://localhost:8080/",
	"admin",
	"bbbbb",
	"blmWWwrHIJKRbir5cb6VabtX5arRrlomyfekBPc70FU68KUYk7"
);


print_r( $client->listFiles(0, time()) );
?>