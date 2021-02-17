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

	private const CAL_EOL = "\r\n";
	private string $timezone = "";

	public function __construct( Login $login ) {
		$this->login = $login;
		$this->timezone = Config::getTimezone()->getName();
	}

	public function generateICS(DataAccess $da) : string {
		$result = $da->getData();
		return $this->toICS($result['plain']);
	}

	private function toICS(array $plainData) : string {
		$cal = 'BEGIN:VCALENDAR' . self::CAL_EOL;
		$cal .= 'VERSION:2.0' . self::CAL_EOL;
		$cal .= 'PRODID:https://github.com/KIMB-technologies/TaskTimeTerminateServer' . self::CAL_EOL;
		$cal .= 'CALSCALE:GREGORIAN' . self::CAL_EOL;
		$cal .= 'METHOD:PUBLISH' . self::CAL_EOL;

		// name, category, begin, end
		$current = array("","", 0, 0);
		foreach( $plainData as $dat ){ // will be in order current to past (sees sorting in TTTStats::printDataset())
			if(
				$dat['name'] === $current[0] && $dat['category'] === $current[1] &&
				abs( $current[2] - $dat['end'] ) < 120
			){ // group to one "event"?
				$current[2] = $dat['begin'];
				continue;
			}
			if(!empty($current[0]) && !empty($current[1])){
				$cal .= $this->calEvent(...$current);
			}
			$current = array($dat['name'], $dat['category'], $dat['begin'], $dat['end']);
		}
		if(!empty($current[0]) && !empty($current[1])){
			$cal .= $this->calEvent(...$current);
		}
		
		return $cal . 'END:VCALENDAR' . self::CAL_EOL;
	}

	private function calEvent(string $name, string $category, int $begin, int $end) : string {
		return 'BEGIN:VEVENT' . self::CAL_EOL
			. 'UID:' . sha1($name . $category . $begin . $end) . '@ttt-server' . self::CAL_EOL
			. 'SUMMARY:' . mb_strcut($name . ' - ' . $category, 0, 75 - 8) . self::CAL_EOL // maximal 75 octets per line
			. $this->calTimeRow($begin, 'DTSTART')
			. $this->calTimeRow($end, 'DTEND')
			.'END:VEVENT' . self::CAL_EOL;
	}

	private function calTimeRow(int $time,  string $rowname) : string {
		return $rowname . ';TZID=' . $this->timezone . ':'. date('Ymd\THis', $time ) .'Z' . self::CAL_EOL;
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