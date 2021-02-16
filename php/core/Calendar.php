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

class Calendar {

	public function __construct( Login $login ) {
		$this->login = $login;
	}

	public function getLink(DataAccess $da) : string {
		$params = array(
			'time' => 'all'
		);

		if(!$da->hasError() && !empty($da->getCmdSemantical())){
			$params = $da->getCmdSemantical();
		}

		$query = "group=" . $this->login->getGroup() 
			. "&" . "token=" . $this->login->getGroupList()->getValue([$this->login->getGroup(), "caltoken"]);

		foreach($params as $key => $val){
			$query .= "&" . $key . "=" . urlencode($val);
		}

		return $query;
	}
}
?>