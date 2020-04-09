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

class Recorder {

	private Dialog $dialog;

	public function __construct(bool $inTerminal = false) {
		$this->dialog = new WebDialog();
	}

	public function record(bool $forcenew = false) : void {
		if($this->waitForOpenedDialogs()){
			$r = Config::getStorageReader('current');
			if( empty($r->getArray())){ // first start etc.
				$r->setArray(array(
					'name' => '',
					'category' => '',
					'end' => -1,
					'begin' => -1,
					'lastopend' => time(),

				));
				$this->recordNew($r);
			}
			else{
				$wasIncative = time() > $r->getValue(['lastopend']) + Config::getSleepTime() * 3; // pc was shut down (no work!!)
				$end = $r->getValue(['end']);
				if( $end === -1 ){ // short break enabled
					$this->recordNew($r);
				}
				else if(time() < $end && !$forcenew && !$wasIncative ){
					// sleep (no limit reached)
				}
				else{
					$this->saveTaskTime($r);
					$this->recordNew($r);
				}
				$r->setValue(['lastopend'], time());
			}
		}
		$this->unlockDialogs();
	}

	private function saveTaskTime(JSONReader $r) : void {
		$data = Config::getStorageReader(
				date(
					'Y-m-d',
					is_int($r->getValue(['begin'])) ? $r->getValue(['begin']) : time()
				)
			);
		ReaderManager::addReader($data);
		$data->setValue([null], array(
			"begin" => $r->getValue(['begin']),
			"end" => $r->getValue(['lastopend']) + Config::getSleepTime(),
			"name" => $r->getValue(['name']),
			"category" => $r->getValue(['category'])
		));
		// also save to sync
		StatsLoader::saveDayTasks( $data->getArray() );
		
		$this->dialog->setLastTask(
			$r->getValue(['name']),
			in_array($r->getValue(['category']), StatsData::getAllCategories()) ?
				array_search($r->getValue(['category']), StatsData::getAllCategories()) : null
		);
	}

	private function recordNew(JSONReader $r) : void {
		$this->dialog->setCategories(StatsData::getAllCategories());
		$this->dialog->open();

		if( !$this->dialog->doesShortBreak()){
			$r->setValue(['name'], $this->dialog->getChosenName());
			$r->setValue(['category'], StatsData::getAllCategories()[$this->dialog->getChosenCategory()]);
			$r->setValue(['begin'], time());
			$r->setValue(['end'], InputParser::getEndTimestamp( $this->dialog->getChosenTime() ) );
		}
		else{
			$r->setValue(['name'],'');
			$r->setValue(['category'], '');
			$r->setValue(['begin'], -1);
			$r->setValue(['end'], -1 );
		}
	}

}
?>
