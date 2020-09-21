<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}
require_once(__DIR__ . '/api.php');

/**
 * LOGIN
 */
$client = new APIClient(
	"http://localhost:8080/", // ADD API URL
	"admin", // API Username/ Group
	"test", // API Device name
	"9iTOiAj0nMnN0yxZvYf2bnt9vSlCPKKBb7U0f8chkcLnVCQPe3" // Token for device
);
/**
 * SETTINGS
 */
$sourcePath = '/Users/me/Documents/TTTData/laptop/'; // data folder to import from
date_default_timezone_set( 'Europe/Berlin' ); // timezone

/**
 * TOOL
 */
foreach( scandir($sourcePath) as $f ){
	if(preg_match('/^\d{4}-(0|1)\d-[0-3]\d\.json$/', $f) === 1){
		$client->setDayTasks(
			json_decode(file_get_contents($sourcePath . '/' . $f ), true),
			strtotime(substr($f, 0, -5))
		);
		echo "Imported " . $f . PHP_EOL;
	}	
}
echo "All done" . PHP_EOL;
?>