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

class WebGUI {

	private Template $mainTemp;
	private Login $login;
	private ParamParser $param;

	private array $nameList = array(
			ParamParser::TASK_NONE => 'Home',
			ParamParser::TASK_HOME => 'Home',
			ParamParser::TASK_ACCOUNT => 'Account Management',
			ParamParser::TASK_DEVICES => 'Device Management',
			ParamParser::TASK_RECORD => 'Add Task',
			ParamParser::TASK_STATS => 'Statistics'
		);

	public function __construct( ParamParser $param, Login $login ) {
		$this->login = $login;
		$this->param = $param;

		$this->mainTemp = new Template('main');
		if($this->login->isLoggedIn()){
			$this->mainTemp->setContent('DISPLAYLOGOUTBOX', '');
			$this->mainTemp->setContent('GROUP', $this->login->getGroup());
		}
		else{
			$this->mainTemp->setContent('HOMELINK', '');
		}
	}

	public function accountManage() : void {
		$this->mainTemp->setContent('TITLE', 'Account Management');
		$account = new Template('account');
		$this->mainTemp->includeTemplate($account);

		// handle pw change
		if( !empty($_POST['type']) && $_POST['type'] === 'change'){
			$newPw = $this->param->loginPost('password');
			$account->setContent('NOTEDISABLE','');
			if(!empty($newPw)){
				if(Login::createNewGroup(
					$this->login->getGroupList(),
					$this->login->getGroup(),
					$newPw,
					$this->login->isAdmin()
				)){
					$account->setContent('NOTEMSG','Changed password!');
				}
				else{
					$account->setContent('NOTEMSG','Error changing password!');
				}
			}
			else{
				$account->setContent('NOTEMSG','Invalid password given!');
			}
		}

		if($this->login->isAdmin()){
			$account->setContent('ISADMIN', '');

			// handle pw change
			if( !empty($_POST['type']) && ($_POST['type'] === 'edit' || $_POST['type'] === 'new') ){
				$pw = $this->param->loginPost('password');
				$group = $this->param->loginPost('group');
				$account->setContent('NOTEDISABLE','');

				if(empty($pw) || empty($group)){
					$account->setContent('NOTEMSG','Invalid password or username given!');
				}
				else{
					$ok = true;
					if( $_POST['type'] === 'edit'){
						if(!$this->login->getGroupList()->isValue([$group])){
							$account->setContent('NOTEMSG','Account does not exist!');
							$ok = false;
						}
						$isAdmin = $this->login->getGroupList()->getValue([$group, 'admin']);
					}
					else if( $_POST['type'] === 'new'){
						if($this->login->getGroupList()->isValue([$group])){
							$account->setContent('NOTEMSG','Account already exists!');
							$ok = false;
						}
						$isAdmin = $_POST['admin'] === 'yes';
					}
					if( $ok ){
						if(Login::createNewGroup(
							$this->login->getGroupList(),
							$group, $pw, $isAdmin
						)){
							$account->setContent('NOTEMSG','Account operation successful!');
						}
						else{
							$account->setContent('NOTEMSG','Error while doing account operation!');
						}
					}
				}
			}
			else if(!empty($_GET['delete'])){
				$account->setContent('NOTEDISABLE','');
				$g = preg_replace('/[^A-Za-z0-9]/', '', $_GET['delete']);
				if(!$this->login->getGroupList()->isValue([$g]) || $g === $this->login->getGroup() ){
					$account->setContent('NOTEMSG','Unable to delete unknown account/ your account!');
				}
				else{
					if($this->login->getGroupList()->setValue([$g], null) &&
						API::deleteGroupDir($g)){
						$account->setContent('NOTEMSG','Deleted account and data!');
					}
					else{
						$account->setContent('NOTEMSG','Unable to delete account!');
					}
				}
			}

			$gr = array();
			foreach($this->login->getGroupList()->getArray() as $group => $data){
				$gr[] = array(
					"ANAME" => $group,
					"AADMIN" => $data['admin'] ? 'Yes' : 'No',
					"AID" => $group
				);
			}
			$account->setMultipleContent('Accounts', $gr);
		}
		else{
			$account->setContent('NOTADMIN', '');
		}
	}

	public function deviceManage() : void {
		$this->mainTemp->setContent('TITLE', 'Device Management');
	}

	public function addTaskRecord() : void {
		$this->mainTemp->setContent('TITLE', 'Add Task');
	}

	public function showStats() : void {
		$this->mainTemp->setContent('TITLE', 'Statistics');
	}

	public function home() : void {
		$this->mainTemp->setContent('HOMELINK', '');
		$this->mainTemp->setContent('TITLE', 'Home');
		$home = new Template('home');
		$this->mainTemp->includeTemplate($home);
		$tasks = array();
		foreach($this->param->getTasksList() as $k => $e){
			if(!empty($e)){
				$tasks[] = array(
					'NAME' => $this->nameList[$k],
					'PARAM' => $e
				);
			}
		}
		$home->setMultipleContent('Links', $tasks);
	}

	public function loginForm() : void {
		$this->mainTemp->setContent('TITLE', 'Login');
		$login = new Template('login');
		$this->mainTemp->includeTemplate($login);
	}

	public function __destruct(){
		$this->mainTemp->output();
	}
	
}
?>