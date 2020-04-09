<?php

class ServerStatsAccess extends StatsAccess {

	private string $uri;
	private string $groupId;
	private string $token;
	private string $thisClientName;

	private bool $requestError = false;

	public function __construct(){
		$c = Config::getStorageReader('config');
		$this->uri = $c->getValue(['sync', 'server', 'uri']);
		$this->groupId = $c->getValue(['sync', 'server', 'group']);
		$this->token = $c->getValue(['sync', 'server', 'token']);
		$this->thisClientName = $c->getValue(['sync', 'server', 'thisname']);
	}

	private function postToServer(string $endpoint, array $data = array() ) : array {
		$context = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-Type: application/x-www-form-urlencoded',
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
					if( !is_null($json) ){
						$this->requestError = false;
						return $json;
					}
				}
			}
			
			$this->requestError = true;
			return array();
	}

	protected function listFilesUnfiltered() : array {
		return $this->postToServer('list');
	}

	protected function getFileUnfiltered( string $file, string $device ) : array {
		return $this->postToServer(
			'get',
			array(
				'file' => $file,
				'device' => $device
			));
	}

	public function initialSync() : bool {
		$ok = true;
		foreach( $this->filesToSyncInitially() as $file ){
			$this->setDayTasks(json_decode(
				file_get_contents( Config::getStorageDir() . '/' . $file ),
				true
			));
			$ok &= !$this->requestError;
		}
		return $ok;
	}

	public function setDayTasks(array $tasks) : void {
		$this->postToServer('add', $tasks );
	}

}

?>