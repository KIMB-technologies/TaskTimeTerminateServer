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

	public function generateICS(DataAccess $da) : string {
		$result = $da->getData();
		return $this->toICS($result['plain']);
	}

	private function toICS(array $plainData) : string {
		$cal = '';
		$eol = "\r\n";

		$cal .= 'BEGIN:VCALENDAR' . $eol;
		$cal .= 'VERSION:2.0' . $eol;
		$cal .= 'PRODID:https://github.com/KIMB-technologies/TaskTimeTerminateServer' . $eol;
		$cal .= 'CALSCALE:GREGORIAN' . $eol;
		$cal .= 'METHOD:PUBLISH' . $eol;

		$tz = date_default_timezone_get();
		
		foreach($plainData as $dat ){
			$cal .= 'BEGIN:VEVENT' . $eol;
			$cal .= 'UID:' . uniqid() . '@ttt-server' . $eol;

			$cal .= 'SUMMARY:' . $dat['category'] . ' - ' . $dat['name'] . $eol;
			$cal .= 'DTSTART;TZID=' . $tz . ':'. date('Ymd', $dat['begin']) .'T'. date('His', $dat['begin']) .'Z' . $eol;
			$cal .= 'DTEND;TZID=' . $tz . ':'. date('Ymd', $dat['end']) .'T'. date('His', $dat['end']) .'Z' . $eol;
			
			$cal .= 'END:VEVENT' . $eol;
		}
		
		$cal .= 'END:VCALENDAR' . $eol;
		return $cal;
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
			if(is_array($val)){
				$val = implode(',', $val);
			}
			$query .= "&" . $key . "=" . urlencode($val);
		}

		return $query;
	}
}
?>