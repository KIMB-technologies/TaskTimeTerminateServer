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
			ParamParser::TASK_RECORD => 'Server Task',
			ParamParser::TASK_STATS => 'Statistics'
		);

	public function __construct( ParamParser $param, Login $login ) {
		$this->login = $login;
		$this->param = $param;

		$this->mainTemp = new Template('main');
		$this->fillTemplateWithImprint($this->mainTemp);
	}

	private function fillTemplateWithImprint( Template $t ) : void {
		$imprintData = Config::getImprintData();
		if(!is_null($imprintData)){
			$t->setContent('COOKIEBANNER', '');
			foreach($imprintData as $k => $v){
				$t->setContent($k, $v);
			}
		}
	}
	
	public function accountManage() : void {
		$this->mainTemp->setContent('TITLE', 'Account Management');
		$account = new Template('account');
		$this->mainTemp->includeTemplate($account);

		$share = new Share($this->login);

		// handle pw change, share add
		if( !empty($_POST['type']) ){
			if($_POST['type'] === 'change'){
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
			else if($_POST['type'] === 'share'){
				$account->setContent('NOTEDISABLE','');
				if( !empty($_POST['category']) && !empty($_POST['group']) ){
					$cat = $_POST['category'];
					$gr = $this->param->loginPost('group');
					if( InputParser::checkCategoryInput($cat) ){
						$share->addShare($cat, $gr);
						if($share->hasError()){
							$account->setContent('NOTEMSG', $share->getErrorMessage());
						}
						else{
							$account->setContent('NOTEMSG','Share was added.');
						}
					}
					else{
						$account->setContent('NOTEMSG','Invalid category given.');
					}
				}
				else {
					$account->setContent('NOTEMSG','Please give category and user to share with.');
				} 
			}
		}
		else if(!empty($_GET['remove']) && is_string($_GET['remove'])
			&& !empty($_GET['group']) && is_string($_GET['group'])
		){
			$account->setContent('NOTEDISABLE','');
			$gr = preg_replace('/[^A-Za-z0-9]/', '', $_GET['group']);
			$cat = $_GET['remove'];

			if( InputParser::checkCategoryInput($cat) ){
				$share->removeShare($cat, $gr);
				if($share->hasError()){
					$account->setContent('NOTEMSG', $share->getErrorMessage());
				}
				else{
					$account->setContent('NOTEMSG','Share was deleted.');
				}
			}
			else{
				$account->setContent('NOTEMSG','Invalid category given while deleting share.');
			}
		}

		$d = array();
		foreach($share->getCategoriesAndGroups() as $n => $v){
			$d[$n] = array();
			foreach($v as $e){
				$d[$n][] = array(
					"NAME" => $e,
					"VALUE" => $e
				);
			}
		}
		$account->setMultipleContent('Category', $d['categories']);
		$account->setMultipleContent('Group', $d['groups']);
		$shares = array();
		foreach($share->getShares() as $s){
			$shares[] = array(
				'CATEGORY' => $s['category'],
				'GROUP' => $s['group']
			);
		}
		$account->setMultipleContent('Shares', $shares);

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
			else if(!empty($_GET['delete']) && is_string($_GET['delete'])){
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
					"AADMIN" => $data['admin'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>',
					"AID" => $group
				);
			}
			$account->setMultipleContent('Accounts', $gr);
		}
		else{
			$account->setContent('NOTADMIN', '');
		}
	}

	public function showLoginToken(string $token) : void {
		$this->mainTemp->setContent('MOREHEADER', '<script>localStorage.setItem("loginToken", "'. $token .','. $this->login->getGroup() .'");</script>');
	}

	public function deviceManage() : void {
		$this->mainTemp->setContent('TITLE', 'Device Management');
		$device = new Template('device');
		$this->mainTemp->includeTemplate($device);

		$r = $this->login->getGroupList();
		$myGroup = $this->login->getGroup();
		if( !empty($_POST['device']) || !empty($_GET['regenerate']) || !empty($_GET['delete']) || isset($_GET['remove']) || isset($_GET['newtoken']) ){
			$device->setContent('NOTEDISABLE','');
			if( !empty($_POST['device']) && InputParser::checkDeviceName($_POST['device']) ){
				$name = $_POST['device'];
				$did = $r->searchValue([$myGroup, 'devices'], $name, 'name');
				if( $did === false ){
					$token = Utilities::randomCode(50, Utilities::ID);
					if($r->setValue([$myGroup, 'devices', null], array( 'name' => $name, 'token' => $token, 'used' => 0))){
						$device->setContent('NOTEMSG','Added device "'. $name .'" with token<br>"<code>'. $token .'</code>"!');
					}
					else{
						$device->setContent('NOTEMSG','Error adding device!');
					}
				}
				else{
					$device->setContent('NOTEMSG','Device already exists!');
				}
			}
			else if(!empty($_GET['regenerate']) && InputParser::checkDeviceName($_GET['regenerate'])){
				$name = $_GET['regenerate'];
				$did = $r->searchValue([$myGroup, 'devices'], $name, 'name');
				if( $did !== false ){
					$token = Utilities::randomCode(50, Utilities::ID);
					if($r->setValue([$myGroup, 'devices', $did, 'token'], $token)){
						$device->setContent('NOTEMSG','Generated new token "<code>'. $token .'</code>" for device "'. $name .'"!');
					}
					else{
						$device->setContent('NOTEMSG','Error saving new token!');
					}
				}
				else{
					$device->setContent('NOTEMSG','Device does not exist!');
				}
			}
			else if(!empty($_GET['delete']) && InputParser::checkDeviceName($_GET['delete'])){
				$name = $_GET['delete'];
				$did = $r->searchValue([$myGroup, 'devices'], $name, 'name');
				if( $did !== false ){
					if($r->setValue([$myGroup, 'devices', $did], null)){
						$device->setContent('NOTEMSG','Deleted device "'. $name .'"!');
					}
					else{
						$device->setContent('NOTEMSG','Error deleting device!');
					}
				}
				else{
					$device->setContent('NOTEMSG','Device does not exist!');
				}
			}
			else if( isset($_GET['remove']) && preg_match('/^[0-9]+$/', $_GET['remove'] ) === 1 ){
				$device->setContent(
					'NOTEMSG',
					$r->isValue([$myGroup, 'sessions', $_GET['remove']]) && $r->setValue([$myGroup, 'sessions', $_GET['remove']], null) ?
						'Deleted session!': 'Error deleting session!'
				);
			}
			else if(isset($_GET['newtoken']) && $_GET['newtoken'] === $_SESSION['newTokenCode']){
				$r->setValue([$myGroup, 'caltoken'], Utilities::randomCode(50, Utilities::ID));
				$device->setContent('NOTEMSG', 'Regenerated the calendar token!');
			}
			else{
				$device->setContent('NOTEMSG','Invalid format!');
			}
		}
		
		$dv = array();
		foreach($r->getValue([$this->login->getGroup(), 'devices']) as $d){
			$dv[] = array(
				"NAME" => $d['name'],
				"DID" => $d['name'],
				"LASTUSED" => $d['used'] > 0 ? date('Y-m-d H:i', $d['used']) : 'Never'
			);
		}
		$device->setMultipleContent('Devices', $dv);

		$sv = array();
		foreach($r->getValue([$this->login->getGroup(), 'sessions']) as $k => $d){
			$sv[] = array(
				"BROWSEROS" => $d['browseros'],
				"LASTUSED" => $d['used'] > 0 ? date('Y-m-d H:i', $d['used']) : 'Never',
				"SID" => $k
			);
		}
		$device->setMultipleContent('Sessions', $sv);

		if( is_dir(API::getStorageDir($myGroup)) ){
			$ds = array();
			foreach( scandir(API::getStorageDir($myGroup)) as $d){
				if( $d !== '.' && $d !== '..' ){
					$ds[] = array( "NAME" => $d );
				}
			}
			$device->setMultipleContent('Data', $ds);
		}

		$device->setContent('CALTOKEN', $r->getValue([$this->login->getGroup(), 'caltoken']));
		$_SESSION['newTokenCode'] = Utilities::randomCode(10, Utilities::CODE);
		$device->setContent('NEWTOKENCODE', $_SESSION['newTokenCode']);
	}

	public function addTaskRecord() : void {
		$this->mainTemp->setContent('TITLE', 'Server Task');
		$edit = new Template('edit');
		$this->mainTemp->includeTemplate($edit);

		new AddEdit($edit, $this->login);		
	}

	public function showStats() : void {
		$this->mainTemp->setContent('TITLE', 'Statistics');

		$stats = new Template('stats');
		$this->mainTemp->includeTemplate($stats);

		new Stats($stats, $this->login);	
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
					'PARAM' => $e,
					'ACTIVE' => $k === ParamParser::TASK_HOME ? 'active' : ''
				);
			}
		}
		$home->setMultipleContent('Links', $tasks);
	}

	public function errorPage(int $code) : void  {
		$this->mainTemp->setContent('TITLE', 'Error ' . $code );
		$error = new Template('error');
		$this->mainTemp->includeTemplate($error);
	}

	public function loginForm() : void {
		$this->mainTemp->setContent('TITLE', 'Login');
		$login = new Template('login');
		$this->mainTemp->includeTemplate($login);
		$this->fillTemplateWithImprint($login);
	}

	public function __destruct(){
		if($this->login->isLoggedIn()){
			$this->mainTemp->setContent('DISPLAYLOGOUTBOX', '');
			$this->mainTemp->setContent('GROUP', $this->login->getGroup());
		}
		else{
			$this->mainTemp->setContent('HOMELINK', '');
		}
		
		$this->mainTemp->output();
	}
}
?>