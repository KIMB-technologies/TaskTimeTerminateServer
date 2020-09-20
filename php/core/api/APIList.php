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

class APIList extends API {

	protected function handleAPITask() : void{
		$groupDir = __DIR__ . '/../../data/' . $this->login->getGroup() . '/';

		$files = array();
		foreach(array_diff(scandir($groupDir), ['.','..']) as $dir ){
			if( $dir !== $this->login->getDeviceName() && is_dir($groupDir . '/' . $dir) ){
				$files = array_merge(
					$files,
					array_map( function ($f) use (&$dir) {
							return array(
								'timestamp' => strtotime(substr($f, 0, -5)),
	 							'file' => $f, 
	 							'device' => $dir
							);
						},
						array_filter(
							scandir( $groupDir . '/' . $dir ),
							function ($f) {
								return preg_match(parent::FILENAME_PREG, $f) === 1;
							}
						)
					)
				);
			}
		}
		$this->output = $files;
	}
}
?>