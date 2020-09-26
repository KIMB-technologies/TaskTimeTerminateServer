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

	public function __construct( string $group = '', string $token = '', string $client = '' ) {
		$this->groupList = new JSONReader('groups');
		if(!empty($group) && !empty($client) && !empty($token)){
			$this->apiClientLogin($group, $client, $token);
		}
		else if(!empty($group) && !empty($token)){
			$this->sessionLogin($group, $token);
		}
		else if( TaskTimeTerminate === 'GUI' && session_status() === PHP_SESSION_ACTIVE ){
			$this->userSessionLogin();
		}
	}

	private function apiClientLogin(string $group, string $client, string $token) : void {
		if( $this->groupList->isValue([$group]) ){
			$did = $this->groupList->searchValue([$group, 'devices'], $client, 'name');
			if( $did !== false ){
				if( $this->groupList->getValue([$group, 'devices', $did, 'token']) === $token ){
					$this->groupList->setValue([$group, 'devices', $did, 'used'], time()); 
					$this->logUserIn($group, $client);
					return;
				}
			}
		}
		$this->logUserOut();
	}

	public function sessionLogin(string $group, string $token) : void {
		if( $this->groupList->isValue([$group]) ){
			$sid = $this->groupList->searchValue([$group, 'sessions'], $token, 'token');
			if( $sid !== false ){
				$this->logUserIn($group);
				$this->groupList->setValue([$group, 'sessions', $sid, 'used'], time()); 
				return;
			}
		}
		$this->logUserOut();
	}

	private function userSessionLogin() : void {
		$this->loggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true
			&& $_SESSION['login_time'] + 600 > time();
		if( $this->isLoggedIn() ){
			$_SESSION['login_time'] = time();
			$this->group = $_SESSION['group'];
		}
		else{
			$this->logUserOut();
		}
	}

	public function userLogin(string $group, string $password, bool $stayLoggedIn = false) : ?string {
		if( $this->groupList->isValue([$group]) ){
			if(self::checkHashedPassword($password, $this->groupList->getValue([$group, 'passhash']))){
				$this->logUserIn($group);

				if( $stayLoggedIn ) {
					$token = Utilities::randomCode(50, Utilities::ID);
					$this->groupList->setValue([$group, 'sessions', null], array(
						"browseros" => Utilities::getBrowserOS(),
						"used" => 0,
						"token" => $token
					));
					return $token;
				}
				else{
					return null;
				}
			}
		}
		$this->logUserOut();
		return null;
	}

	private function logUserIn(string $group, string $device = "") : void {
		$this->loggedIn = true;
		$this->group = $group;
		$this->device = $device;
		if( session_status() === PHP_SESSION_ACTIVE ){
			$_SESSION['login_time'] = time();
			$_SESSION['login'] = true;
			$_SESSION['group'] = $group;
		}
	}

	public function logUserOut(){
		$this->loggedIn = false;
		$this->group = "";
		$this->device = "";
		if( session_status() === PHP_SESSION_ACTIVE ){
			$_SESSION['login_time'] = time();
			$_SESSION['login'] = false;
			$_SESSION['group'] = "";
		}
	}

	public function isLoggedIn() : bool {
		return $this->loggedIn;
	}

	public function getGroupList() : JSONReader {
		return $this->groupList;
	}

	public function getGroup() : string {
		return $this->isLoggedIn() ? $this->group : "";
	}

	public function getDeviceName() : string {
		return $this->isLoggedIn() ? $this->device : "";
	}

	public function isAdmin(){
		return $this->groupList->getValue([$this->group, "admin"]);
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

	/**
	 * Creates new group if group not exists, else resets only password.
	 */
	public static function createNewGroup(JSONReader $groups, string $group, string $password, bool $admin = false ) : bool {
		if($groups->isValue([$group])){
			$groups->setValue([$group, "admin"], $admin);
			return $groups->setValue([$group, "passhash"], self::genHashedPassword($password));
		}
		else{
			return $groups->setValue([$group], array(
				"passhash"  => self::genHashedPassword($password),
				"admin" => $admin,
				"devices" => array(),
				"sessions" => array()
			));
		}
	}
}
?>