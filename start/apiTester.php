<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}

$uri = "http://localhost:8080/";
$groupId = "test";
$token = "aaaa";
$thisClientName = "bbbb";

function postToServer(string $endpoint, array $data = array() ) : array {
	global $uri, $groupId, $token, $thisClientName;

	$context = array(
		'http' => array(
			'method'  => 'POST',
			'header'  => 'Content-Type: application/x-www-form-urlencoded',
			'ignore_errors' => true,
			'content' => http_build_query(array(
				'group' => $groupId,
				'token' => $token,
				'client' => $thisClientName,
				'data' => json_encode($data)
			))
	));
	$append = substr($uri, -1) === '/' ? '' : '/';

	if( in_array($endpoint, ['add', 'list', 'get'])){
		$append .= 'api/' . $endpoint . '.php';
		$ret = file_get_contents( $uri . $append, false, stream_context_create($context));

		if( $ret !== false ){
			$json = json_decode( $ret, true);
			if( !is_null($json) && empty($json['error']) ){
				return $json;
			}
			else{
				$msg = is_null($json) ? $ret : $json['error'];
				echo "ERROR: Returned message from server: '". $msg ."'" . PHP_EOL;
			}
		}
	}
	echo "ERROR: Request failed!" . PHP_EOL;
	return array();
}

function listFiles() : array {
	return postToServer('list');
}

function getFile( string $file, string $device ) : array {
	return postToServer(
		'get',
		array(
			'file' => $file,
			'device' => $device
		));
}

function setDayTasks(array $tasks, int $day) : void {
	postToServer('add',
		array( 
			'day' => $day,
			'tasks' => $tasks
		));
}

foreach( listFiles() as $f ){
	print_r(getFile($f['file'], $f['device']));
}

setDayTasks( array(
	array(
		'begin' => 1600609063,
		'end' => 1600609863,
		'name' => 'Test',
		'category' => 'Cate'
	)
), 1600609863);
setDayTasks( array(
	array(
		'begin' => 1600609063,
		'end' => 1600609863,
		'name' => 'Test',
		'category' => 'Cate'
	)
), 1600509863);
?>