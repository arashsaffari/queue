<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeigniterExt\Queue\Queue;

class ResetAll extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:resetall';
	protected $description  = 'Reset all failed queue tasks as not executed and they can be run in queue';
	protected $usage        = 'queue:resetall';
	protected $arguments    = [];
	protected $options 		= [
		'-quiet'	=> 'Do not output any message',
	];

	private $queue = null;

	
	public function run(array $params)
	{

		//
		// get inputs
		//
		$this->quiet = (bool)CLI::getOption('quiet');

		if (!$this->quiet) CLI::write('set the task as not running ...', 'white');

		//
		// Init Queue
		//
		$this->queue = new Queue();

		//
		// All failed queue tasks are counted
		//
		$count = $this->queue->getPersistor()->countFailedTasks();

		if($count>0){

			if (!$this->quiet) CLI::newLine(1);
			if (!$this->quiet) {
				CLI::write(
					CLI::color('Failed Task(s): ', 'light_red').
					CLI::color($count, 'yellow')
				);
			}
			if (!$this->quiet) CLI::newLine(1);

			if (!$this->quiet){
				$resetConfirm = CLI::prompt('Do you really want to reset all failed tasks', ['y','n']);
			}else{
				$resetConfirm = "y";
			}

			//
			// if Reset has not been confirmed app should stop running
			//
			if ($resetConfirm !== "y") exit;

			//
			// update task in DB
			//
			$this->queue->getPersistor()->resetAllFailedTasks();

			if (!$this->quiet){
				CLI::write(
					CLI::color('Reset all failed queue tasks ', 'green')
				);
			}
			
			
		}else{
			if (!$this->quiet){
				CLI::write(
					CLI::color('There are no failed tasks', 'green')
				);
			}
		}

	}

}
