<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use CodeigniterExt\Queue\Worker;
use CodeigniterExt\Queue\Queue;

class Run extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:run';
	protected $description  = 'Run a queue task with ID';
	protected $usage        = 'queue:retry';
	protected $arguments    = [
		'id'	=> 'queue task ID'
	];
	protected $options 		= [
		'-quiet'	=> 'Do not output any message',
	];

	private $queue = null;
	private $worker = null;
	private $task = null;

	
	public function run(array $params)
	{
		
		//
		// get inputs
		//

		$id = array_shift($params);

		if ( !ctype_digit($id) )
		{
			$id = CLI::prompt("please enter the task ID number");
		}

		if ( !ctype_digit($id) )
		{
			CLI::error('specified ID number is wrong!');
			return;
		}

		$this->task_id = $id;

		$this->quiet = (bool)CLI::getOption('quiet');
		
		// if (!$this->quiet) CLI::write('Run the task...', 'white');


		//
		// Init Queue, Worker
		//
		$this->queue = new Queue();
		$this->worker = new Worker;
		$this->worker->setQueue($this->queue);

		//
		// find not executed task with id in DB 
		//
		$this->task = $this->queue->getPersistor()->getTaskWithID($this->task_id,"0","0");

		if (!empty($this->task)){

			try
			{
				$this->worker->run($this->task);
			}
			
			catch (\CodeigniterExt\Queue\DBConnectionException $ex) {
				$this->exception_handler($ex);
			}

			catch (\InvalidArgumentException $ex) {
				$this->exception_handler($ex);
			}

			catch (\Exception $ex) {
				$this->exception_handler($ex);
			}

			catch (\Error $ex) {
				$this->exception_handler($ex);
			}


			if (!$this->quiet){
				CLI::write(
					CLI::color('('.$this->task->id.'): ', 'yellow').
					CLI::color('Ran task ', 'green').
					$this->task->getName()
				);
			}
		
		}else{
		
			if (!$this->quiet) CLI::error('Either the task is not in the database or has already been executed');
		
		}

		CLI::newLine(1);

	}

	private function exception_handler($ex)
	{

		if (!$this->quiet){
			
			if(isset($this->task->id)){
				$taskID = '('.$this->task->id.'): ';
			}else{
				$taskID = '';
			}

			CLI::error(
				$taskID. $ex->getMessage() . ': ' .
				CLI::color($ex->getFile(), 'yellow') . ':' .
				'' . $ex->getLine() . ''
			);
		}

		log_message('critical', 
			'Queue Error - ' . 
			$ex->getMessage() . ': ' .
			$ex->getFile() .
			' (' . $ex->getLine() . ')'
		);

		if ($this->task !== null) {
			try{
				$this->worker->setError($this->task);
			}
			catch (\Exception $ex) {
				
			}
		}

		CLI::newLine(1);
		die;
	}

}
