<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
}

function createAPIReadline() : APIClient {
	$check = array(
		'URI' => fn(string $u) => @filter_var($u, FILTER_VALIDATE_URL),
		'Group/ Username' => fn(string $g) => preg_match('/^[A-Za-z0-9]+$/', $g) === 1,
		'Device' => fn(string $n) => preg_match( '/^[A-Za-z0-9\-]+$/', $n) === 1,
		'Device Token' => fn(string $t) => preg_match('/^[A-Za-z0-9]+$/', $t) === 1
	);
	$tabs = array(
		'URI' => 3,
		'Group/ Username' => 1,
		'Device' => 3,
		'Device Token' => 2
	);
	$data = array();

	echo "Please create a Device in the Webinterface, then fill in details:" . PHP_EOL;
	foreach($check as $name => $checker ){
		do {
			$input = trim(readline($name. ':' . str_repeat("\t", $tabs[$name])));
			if(empty($input) || !$checker($input) ){
				echo "\tError invalid format!" . PHP_EOL;
			}
			else{
				$data[$name] = $input;
			}
		} while( empty( $data[$name] ) );
	}

	return new APIClient(
		$data['URI'],
		$data['Group/ Username'],
		$data['Device'],
		$data['Device Token']
	);
}

class APIClient {

	private string $uri;
	private string $groupId;
	private string $token;
	private string $thisClientName;

	public function __construct(string $uri, string $group, string $client, string $token){
		$this->uri = $uri;
		$this->groupId = $group;
		$this->token = $token;
		$this->thisClientName = $client;
	}

	private function postToServer(string $endpoint, array $data = array()) : array {
		$context = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'ignore_errors' => true,
				'content' => http_build_query(array(
					'group' => $this->groupId,
					'token' => $this->token,
					'client' => $this->thisClientName,
					'data' => json_encode($data)
				))
		));
		$append = substr($this->uri, -1) === '/' ? '' : '/';

		if( in_array($endpoint, ['add', 'list', 'get'])){
			$append .= 'api/' . $endpoint . '.php';
			$ret = file_get_contents( $this->uri . $append, false, stream_context_create($context));

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

	public function listFiles(int $timeMin, int $timeMax) : array {
		return $this->postToServer(
			'list',
			array(
				'timeMin' => $timeMin,
				'timeMax' => $timeMax
			));
	}

	public function getFile( string $file, string $device ) : array {
		return $this->postToServer(
			'get',
			array(
				'file' => $file,
				'device' => $device
			));
	}

	public function setDayTasks(array $tasks, int $day) : void {
		$this->postToServer('add',
			array( 
				'day' => $day,
				'tasks' => $tasks
			));
	}
}


?>