<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}
require_once(__DIR__ . '/api.php');

$client = createAPIReadline();
$destPath = __DIR__ . '/exported/';
echo PHP_EOL . 'Export will be written to "'. $destPath .'"' . PHP_EOL;
$newPath = readline('Type other path to change or leave empty to use path above: ');
if(!empty($newPath)){
	$destPath = $newPath;
}

if(!is_dir($destPath)){
	if(!mkdir($destPath, 0740, true)){
		die('Error creating export path!');
	}
}
foreach( $client->listFiles(0, time()) as $f ){
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