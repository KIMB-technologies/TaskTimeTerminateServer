<?php
abstract class StatsAccess {

	const FILENAME_PREG = '/^\d{4}-(0|1)\d-[0-3]\d\.json$/';

	/**
	 * return array(array(
	 * 	'timestamp' => '', // day of file at 00:00
	 * 	'file' => '', // filename to pass to getFile()
	 * 	'device' => '' // device to pass to getFile()
	 * ), ...)
	 * 
	 * Must not return stats of the client itself!
	 */
	public function listFiles() : array {
		return $this->filterFileList($this->listFilesUnfiltered());
	}
	abstract protected function listFilesUnfiltered() : array;

	/**
	 * return array like JSON in e.g. "2020-02-12.json"
	 */
	public function getFile( string $file, string $device ) : array {
		return $this->filterFileArray($this->getFileUnfiltered($file, $device));
	}
	abstract protected function getFileUnfiltered( string $file, string $device ) : array;

	/**
	 * Copy initial files to the sync
	 */
	abstract public function initialSync() : bool;

	/**
	 * Set a array of daily task 
	 * (will use last tasks timestamp to calculate day)
	 */
	abstract public function setDayTasks(array $tasks) : void;

	/**
	 * Get a list of all local files to sync on init of a sync
	 */
	protected function filesToSyncInitially() : array {
		return array_values(array_filter(scandir( Config::getStorageDir() ), function ($f) {
			return preg_match(self::FILENAME_PREG, $f) === 1
				&& date('Y-m-d') !== substr($f, 0, -5);
		}));
	}

	/**
	 * Check format of a one days task array from server/ directory
	 * @return the filtered $array, or array() 
	 */
	private function filterFileArray(array $array) : array {
		return array_values(array_filter( $array, function ($a) {
			return isset($a['begin']) && isset($a['end']) && isset($a['name']) && isset($a['category'])
				&& is_int($a['begin']) && $a['begin'] > 0
				&& is_int($a['end']) && $a['end'] > 0
				&& InputParser::checkNameInput( $a['name'] )
				&& InputParser::checkCategoryInput( $a['category'] );
		}));
	}

	/**
	 * Check format of a file list array from server/ directory
	 * @return the filtered $array, or array() 
	 */
	private function filterFileList(array $array) : array {
		return array_values(array_filter( $array, function ($a) {
			return isset($a['timestamp']) && isset($a['file']) && isset($a['device'])
				&& is_int($a['timestamp']) && $a['timestamp'] > 0
				&& preg_match(self::FILENAME_PREG, $a['file']) === 1
				&& ($a['device'] === '' || InputParser::checkDeviceName( $a['device'] ));
		}));
	}
}
?>