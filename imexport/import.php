<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}
require_once(__DIR__ . '/api.php');

echo "Please make sure to use the right Device name, it can't be changed later!" . PHP_EOL;
$client = createAPIReadline();

echo PHP_EOL . 'Please give a directory where the JSON to import of the device are stored.' . PHP_EOL;
do{
	$sourcePath = readline('Type directory: ');
} while( empty($sourcePath) || !is_dir($sourcePath));

$timezone = 'Europe/Berlin';
echo PHP_EOL . 'Timezone will be "'. $timezone .'"' . PHP_EOL;
$newZone = readline('Type other timezone to change or leave empty to use timezone above: ');
if(!empty($newZone)){
	$timezone = $newZone;
}
date_default_timezone_set( $timezone ); 


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