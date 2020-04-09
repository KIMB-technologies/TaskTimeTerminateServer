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
defined( 'TaskTimeTerminate' ) or die('Invalid Endpoint!');

class API {

	private array $output = array();

	private bool $hasError = false;
	private string $errorMsg = '';

	public function __construct() {
		if( TaskTimeTerminate !== 'API' ){
			die('Only for usage with API-requests!');
		}
	}

	public function request(array $post) : void {

	}

	private function validatePost(array $post) : void {
		if( empty( $post['group'] ) || empty( $post['token'] ) || empty( $post['client'] ) || empty( $post['data'] ) ){
			$this->error('Missing parameter!');
			return;
		}
		/*
		[group] => tGroup
		[token] => TToken
		[client] => TName
		[data]
		*/
	}

	public function error(string $msg = 'Unknown Error') {
		$this->hasError = true;
		$this->errorMsg = $msg;
	}

	public function __destruct(){
		if( empty($this->output) || $this->hasError === true ){
			http_response_code(400);
			$this->output = array('error' => $this->errorMsg );
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->output, JSON_PRETTY_PRINT);
	}
}
?>