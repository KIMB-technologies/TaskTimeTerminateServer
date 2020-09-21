<?php
if( php_sapi_name() !== 'cli' ){
	die('Commandline only!');
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
				//'ignore_errors' => true,
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

	public function listFiles() : array {
		return $this->postToServer('list');
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