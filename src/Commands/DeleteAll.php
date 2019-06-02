<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeigniterExt\Queue\Queue;

class DeleteAll extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:deleteall';
	protected $description  = 'Delete all queue task';
	protected $usage        = 'queue:deleteall';
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

		if (!$this->quiet) CLI::write('delete all queue task ...', 'white');

		if (!$this->quiet){
			$deleteConfirm = CLI::prompt('Do you really want to delete all tasks', ['y','n']);
		}else{
			$deleteConfirm = "y";
		}

		//
		// if delete has not been confirmed app should stop running
		//
		if ($deleteConfirm !== "y") exit;

		//
		// Init Queue
		//
		$this->queue = new Queue();

		//
		// delete the task in DB
		//
		$resualt = $this->queue->getPersistor()->clear();

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
