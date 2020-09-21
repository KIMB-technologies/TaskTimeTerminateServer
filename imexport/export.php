<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}
require_once(__DIR__ . '/api.php');

/**
 * LOGIN
 */
$client = new APIClient(
	"http://localhost:8080/",
	"admin",
	"test",
	"YvCSpXLZxpT0RPLI4yg6JIhSBKwILJ1vFqNILvf6luji7JrtUS"
);
/**
 * SETTINGS
 */
$destPath = __DIR__ . '/exported/';

/**
 * TOOL
 */
if(!is_dir($destPath)){
	if(!mkdir($destPath, 0740, true)){
		die('Error creating export path!');
	}
}
foreach( $client->listFiles() as $f ){
	$devicePath = $destPath . '/' . $f['device'] . '/';
	if(!is_dir($devicePath)){
		if(!mkdir($devicePath, 0740, true)){
			die('Error creating device path!');
		}
	}

	$content = $client->getFile($f['file'], $f['device']);
	$filename = $devicePath . date('Y-m-d', $f['timestamp']) . '.json';
	file_put_contents($filename, json_encode($content, JSON_PRETTY_PRINT));

	echo "Exported " . $f['file'] . " from " . $f['device'] . PHP_EOL;
}
echo "All done" . PHP_EOL;
?>