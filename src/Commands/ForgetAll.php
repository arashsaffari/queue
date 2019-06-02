<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeigniterExt\Queue\Queue;

class ForgetAll extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:forgetall';
	protected $description  = 'Delete all failed queue task';
	protected $usage        = 'queue:forgetall';
	protected $arguments    = [];
	protected $options 		= [
		'-quiet'	=> 'Do not output any message',
	];

	private $queue = null;
	private $task = null;

	
	public function run(array $params)
	{
		//
		// get inputs
		//

		$this->quiet = (bool)CLI::getOption('quiet');

		if (!$this->quiet){
			$forgetConfirm = CLI::prompt('Do you really want to reset all failed tasks', ['y','n']);
		}else{
			$forgetConfirm = "y";
		}

		//
		// if Reset has not been confirmed app should stop running
		//
		if ($forgetConfirm !== "y") exit;


		if (!$this->quiet) CLI::write('delete all failed queue task ...', 'white');


		//
		// Init Queue
		//
		$this->queue = new Queue();

		//
		// delete the task in DB
		//
		$resualt = $this->queue->getPersistor()->clearFailed();

		if ($resualt){

			if (!$this->quiet){
				CLI::write( 
					'Deleted Tasks: ' .
					CLI::color($resualt, 'yellow')
				, 'green');
			}
		
		}else{
		
			if (!$this->quiet) CLI::error('There is no task to delete.');
		
		}

		if (!$this->quiet) CLI::newLine(1);
		

	}

}
