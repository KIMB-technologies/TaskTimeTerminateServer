<?php
class CLIParser {

	private array $args = array();
	private bool $empty = true;

	const TASK_VERSION = 'v', TASK_HELP = 'h', TASK_STATS = 's',
		TASK_SETTINGS = 'p', TASK_RECORD = 'r', TASK_PAUSE = 'e',
		TASK_OVERVIEW = 'o';
	private $tasks = array(
		'v' => array('version', 'v'),
		'h' => array('help', 'h'),
		's' => array('stats', 's'),
		'p' => array('settings', 'preferences', 'p', 'conf', 'c'),
		'r' => array('record', 'r', 'change', 'new'),
		'e' => array('end', 'begin', 'pause', 'stop', 'e', 'start', 't', 'toggle'),
		'o' => array('overview', 'o')
	);

	public function __construct(int $argc, array $argv) {
		if( $argc > 0){
			$this->args = array_slice($argv, 1);
			$this->args = array_map('trim', $this->args);
			$this->args = preg_replace('/[^A-Za-z0-9\_\-,]/', '', $this->args);
			$this->args = array_values(array_filter($this->args, function ($e){
				return !empty($e);
			}));

			if( count($this->args) > 0 ){
				$this->empty = false;
			}
		}
	}

	public function getTask() : string {
		if( !$this->empty ){
			$t = strtolower($this->args[0]);
			foreach($this->tasks as $key => $vals ){
				if( in_array( $t, $vals ) ){
					return $key;
				}
			}
		}
		return '';
	}

	public function getTaskParams() : array {
		$texts = array(
			'v' => 'Show version and information about program',
			'h' => 'Show this help dialog',
			's' => 'Show statistic of collected data',
			'p' => 'Edit settings of program, e.g. categories',
			'r' => 'Start a new task now, will stop the current and open task dialog',
			'e' => 'Switch the program status, between enabled [collect data, ask for tasks] and disabled [do nothing]',
			'o' => 'Get an overview about current task and program status'
		);
		$o = array();
		foreach( $this->tasks as $key => $task ){
			$o[] = CLIOutput::colorString( implode(', ', $task), CLIOutput::GREEN );
			$o[] = array( CLIOutput::colorString( $texts[$key], CLIOutput::BLUE) );
		}
		return $o;
	}

	public function getCommands() : array {
		if( !$this->empty && count($this->args) > 1){
			return array_slice($this->args, 1);
		}
		return array('');
	}
}
?>