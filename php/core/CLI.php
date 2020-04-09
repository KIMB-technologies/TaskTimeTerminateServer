<?php
class CLI {

	const OVERVIEW_TIME = 'd.m. H:i';

	private CLIParser $parser;
	private CLIOutput $output;

	public function __construct(CLIParser $parser) {
		$this->parser = $parser;
		$this->output = new CLIOutput();
	}

	public function checkTask(){
		switch ($this->parser->getTask()) {
			case CLIParser::TASK_VERSION:
				$this->version();
				break;
			case CLIParser::TASK_STATS:
				new Stats($this->parser, $this->output);
				break;
			case CLIParser::TASK_SETTINGS:
				new Settings($this->parser, $this->output);
				break;
			case CLIParser::TASK_RECORD:
				$this->record();
				break;
			case CLIParser::TASK_OVERVIEW:
				$this->overview();
				break;
			case CLIParser::TASK_PAUSE:
				$this->togglePause();
				break;
			case CLIParser::TASK_HELP:
			default:
				$this->help();
			break;
		}
	}

	private function help(){
		$this->output->print(array(
			'Help',
			array(
				'./cli.php TASK [COMMAND, ...]',
				'List of all Tasks, See commands per task when starting task',
				$this->parser->getTaskParams()
			)
		));
		
	}

	private function record(){
		if( isset($this->parser->getCommands()[0]) && $this->parser->getCommands()[0] == 'inTerminalDialog' ){
			(new Recorder(true))->record();
		}
		else {
			$this->output->print(array(
				'Force new record',
				array('Add command '. CLIOutput::colorString('inTerminalDialog', CLIOutput::BLUE) . ' to do a normal record using the InTerminalDialog.')
			));
			(new Recorder())->record(true);
			if( !Config::getRecordStatus(false) ){
				$this->togglePause(); // make sure to enable
			}
		}
	}

	private function overview(){
		$enabled = Config::getRecordStatus(false);
		$current = Config::getStorageReader('current');
		$this->output->print(array(
			'Overview',
			array(
				'TaskTimeTerminate is ' . ( $enabled ?
					CLIOutput::colorString( 'enabled', CLIOutput::GREEN) : CLIOutput::colorString( 'disabled', CLIOutput::RED)
				) . '!'
			)
		));
		$this->output->print(array(''));
		if( $enabled ){
			if( $current->getValue(['end']) !== -1 ){
				$this->output->print(array(
					'Your current Task:'
				), CLIOutput::BLUE);
				$this->output->table(array(
					array(
						'' => 'Category',
						'Value' => $current->getValue(['category'])
					),
					array(
						'' => 'Name',
						'Value' => $current->getValue(['name'])
					),
					array(
						'' => 'Started',
						'Value' => date( self::OVERVIEW_TIME, $current->getValue(['begin']))
					),
					array(
						'' => 'Planned end',
						'Value' => date( self::OVERVIEW_TIME, $current->getValue(['end']))
					),
					array(
						'' => 'Worked until now',
						'Value' => Stats::secToTime($current->getValue(['lastopend']) - $current->getValue(['begin']))
					)
				));
			}
			else{
				$this->output->print(array(
					'Currently you have a break.'
				), CLIOutput::YELLOW, 1);
			}
		}
	}

	private function version(){
		$this->output->print(array(
			'Version',
			array(
				Utilities::VERSION,
				'TaskTimeTerminate',
				'(c) 2020 by KIMB-technologies',
				'https://git.5d7.eu/KIMB-technologies/TaskTimeTerminate',
				array(
					'released under the terms of GNU Public License Version 3',
					'https://www.gnu.org/licenses/gpl-3.0.txt'
				)
			)
		));
	}

	private function togglePause(){
		$enabled = Config::getRecordStatus(false);
		$c = Config::getStorageReader('config');
		$c->setValue(['status'], !$enabled);

		if( $enabled ){ // not enabled
			$is = CLIOutput::colorString( 'disabled', CLIOutput::RED);
			$was = CLIOutput::colorString( 'enabled', CLIOutput::GREEN);
		}
		else{ // enabled
			$was = CLIOutput::colorString( 'disabled', CLIOutput::RED);
			$is = CLIOutput::colorString( 'enabled', CLIOutput::GREEN);
		}

		$this->output->print(array(
			'Toggle Pause',
			array(
				'Status was: ' . $was,
				'Status is: ' . $is
			)
		));
	}
}
?>