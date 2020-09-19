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

class Login {

	private bool $loggedIn = false;
	private string $device;
	private string $group;

	private JSONReader $groupList;

	public function __construct( string $group, string $client, string $token ) {
		$this->groupList = new JSONReader('groups');
		if(!empty($group) && !empty($client) && !empty($token)){
			$this->apiClientLogin($group, $client, $token);
		}

		if( TaskTimeTerminate === 'GUI' && session_status() === PHP_SESSION_ACTIVE ){
			$this->userSessionLogin();
		}
	}

	private function apiClientLogin(string $group, string $client, string $token) : void {
		if( $this->groupList->isValue([$group]) ){
			$did = $this->groupList->searchValue([$group, 'devices'], $client, 'name');
			if( $did !== false ){
				if( $this->groupList->getValue([$group, 'devices', $did, 'token']) === $token ){
					$this->logUserIn($group, $client);
					return;
				}
			}
		}
		$this->loggedIn = false;
		$_SESSION['login'] = false;
	}

	private function userSessionLogin() : void {
		$this->loggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true
			&& $_SESSION['login_time'] + 600 > time();
		if( $this->isLoggedIn() ){
			$_SESSION['login_time'] = time();
			$this->group = $_SESSION['group'];
		}
		else{
			$_SESSION['login'] = false;
		}
	}

	public function userLogin(string $group, string $password) : void {
		if( $this->groupList->isValue([$group]) ){
			if(self::checkHashedPassword($password, $this->groupList->getValue([$group, 'passhash']))){
				$this->logUserIn($group);
				return;
			}
		}
		$_SESSION['login_time'] = 0;
		$_SESSION['login'] = false;
	}

	private function logUserIn(string $group, string $device = "") : void {
		$this->loggedIn = true;
		$this->group = $group;
		$this->device = $device;
		$_SESSION['login_time'] = time();
		$_SESSION['login'] = true;
		$_SESSION['group'] = $group;
	}

	public function isLoggedIn() : bool {
		return $this->loggedIn;
	}

	public function getGroup() : string {
		return $this->isLoggedIn() ? $this->group : "";
	}

	public function getDeviceName() : string {
		return $this->isLoggedIn() ? $this->device : "";
	}

	private static function checkHashedPassword(string $pwInput, string $pwDB) : bool {
		$salt = substr($pwDB, strrpos($pwDB, '+')+1);
		return $pwDB === self::genHashedPassword($pwInput, $salt);
	}

	private static function genHashedPassword(string $pwInput, string $salt = '') : string {
		if( empty($salt) || preg_match('/^[a-zA-Z0-9]{50}$/', $salt) !== 1 ){
			$salt = Utilities::randomCode(50, Utilities::ID);
		}
		return 'sha512-salt-prefix+' . hash('sha512', $salt . $pwInput ) . '+' . $salt;
	}
}
?>