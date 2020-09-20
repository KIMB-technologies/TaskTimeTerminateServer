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

abstract class API {

	const FILENAME_PREG = '/^\d{4}-(0|1)\d-[0-3]\d\.json$/';

	const CLIENT_NAME_PREG = InputParser::DEVICE_NAME_PREG;
	const GROUP_NAME_PREG = '/^[A-Za-z0-9]+$/';
	const TOKEN_VALUE_PREG = self::GROUP_NAME_PREG;

	protected ?array $output = null;
	protected Login $login;
	protected array $requestData = array();

	private bool $hasError = false;
	private string $errorMsg = '';

	public function __construct() {
		if( TaskTimeTerminate !== 'API' ){
			die('Only for usage with API-requests!');
		}
	}

	public function request(array $post) : void {
		$this->validatePost($post);
		if( !$this->hasError ){
			$this->login = new Login($post['group'], $post['client'], $post['token']);
			if( $this->login->isLoggedIn()){
				$this->handleAPITask();
			}
			else{
				$this->error('Unable to log in!');
			}
		}
	}

	/**
	 * Use $this->login and $this->requestData
	 * Give error via $this->error() or output into array $this->output
	 */
	abstract protected function handleAPITask() : void;

	private function validatePost(array $post) : void {
		if( !isset( $post['group'] ) || !isset( $post['token'] ) || !isset( $post['client'] ) || !isset( $post['data'] ) ){
			$this->error('Missing parameter!');
			return;
		}
		if( !is_string( $post['group'] ) || !is_string( $post['token'] ) || !is_string( $post['client'] ) || !is_string( $post['data'] ) ){
			$this->error('Invalid parameter type!');
			return;
		}
		if( !$this->checkByRegEx($post['group'], self::GROUP_NAME_PREG) ){
			$this->error('Invalid Group parameter!');
			return;
		}
		if( !$this->checkByRegEx($post['token'], self::TOKEN_VALUE_PREG) ){
			$this->error('Invalid Token parameter!');
			return;
		}
		if( !$this->checkByRegEx($post['client'], self::CLIENT_NAME_PREG) ){
			$this->error('Invalid Client parameter!');
			return;
		}
		if( !empty( $post['data'] ) ){
			$this->requestData = json_decode( $post['data'], true );
			if( ( empty($this->requestData) && $this->requestData !== array() )
				|| !is_array($this->requestData) ) {
					$this->error('Invalid JSON given as Data!');
			}
		}
		else{
			$this->error('Invalid Data given!');
		}
	}

	protected function error(string $msg = 'Unknown Error') {
		$this->hasError = true;
		$this->errorMsg = $msg;
	}

	public function __destruct(){
		if( is_null($this->output) || $this->hasError === true ){
			http_response_code(400);
			$this->output = array('error' => $this->errorMsg );
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($this->output, JSON_PRETTY_PRINT);
	}

	private function checkByRegEx( string $s, string $reg ) : bool {
		return !empty($s) && preg_match($reg, $s) === 1;
	}

	public static function deleteGroupDir(string $group) : bool {
		$groupDir = __DIR__ . '/../../data/' . $group . '/';
		if( is_dir($groupDir) ){
			return Utilities::deleteDirRecursive($groupDir);
		}
		else{
			return true;
		}
	}
}
?>