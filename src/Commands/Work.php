<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use CodeigniterExt\Queue\Worker;
use CodeigniterExt\Queue\Queue;

class Work extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:work';
	protected $description  = 'Start processing jobs on the queue as a daemon';
	protected $usage        = 'queue:work';
	protected $arguments    = [];
	protected $options 		= [
		'-once'  			=> 'Only process the next job on the queue',
		'-priority'			=> 'The priority name of the queues to work [1,2,3]. (default 0 - all jobs)',
		'-stop_when_empty'	=> 'Stop when the queue is empty',
		'-quiet'			=> 'Do not output any message',
		'-sleep'			=> 'Number of seconds to sleep after job is done (default 1 sec)',
		'-sleep_no_job'		=> 'Number of seconds to sleep when no job is available (default 3 sec)',
	];

	private $queue = null;
	private $worker = null;
	private $task = null;

	private $once = false;

	
	public function run(array $params)
	{

		$this->once  			= (bool)CLI::getOption('once');

		$this->stop_when_empty	= (bool)CLI::getOption('stop_when_empty');

		$this->quiet  			= (bool)CLI::getOption('quiet');

		$this->sleep  			= CLI::getOption('sleep');

		$this->sleep_no_job  	= CLI::getOption('sleep_no_job');

		$this->priority 		= CLI::getOption('priority');

		if (!ctype_digit($this->sleep)){
			$this->sleep = 1;
		}

		if (!ctype_digit($this->sleep_no_job)){
			$this->sleep_no_job = 3;
		}
		
		if (!ctype_digit($this->priority)){
			$this->priority = 0;
		}else{
			if($this->priority > 3 || $this->priority < 1){
				$this->priority = 0;
			}
		}

		
		// echo $this->priority;

		
		if (!$this->quiet) CLI::write('');
		if (!$this->quiet) CLI::write('Start processing jobs on the queue ...', 'white');
		if (!$this->quiet) CLI::write('');

		$this->Loop();

	}

	private function loop()
	{
		//
		// loop to keep cli running
		//
		while (1) {

			try
			{
				if (!$this->queue){
					
					//
					// Init Queue
					//
					$this->queue = new Queue();

					//
					// Init Worker
					//
					$this->worker = new Worker;
					$this->worker
						->setQueue($this->queue)
						->setInterval($this->sleep);
				}

				
				$this->task = null;

				//
				// get next task
				//
				$this->task = $this->worker->getTask();

				//
				// if a task exists, it will be executed
				//
				if ($this->task !== null) {
					$this->worker->run($this->task);
					if (!$this->quiet){
						CLI::write(
							CLI::color('('.$this->task->id.'): ', 'yellow').
							CLI::color('Ran task ', 'green').
							$this->task->getName()
						);
					}
				}else{
					if ($this->stop_when_empty) exit;
					usleep($this->sleep_no_job * 1000000);
				}

				if($this->once) exit;
			}
			
			catch (\CodeigniterExt\Queue\DBConnectionException $ex) {
				$this->queue = null;
				$this->exception_handler($ex);
				usleep(5 * 1000000);
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

			finally{
				usleep($this->sleep * 1000000);
			}

		}
	}

	private function exception_handler($ex)
	{

		if (!$this->quiet){
			
			if(isset($this->task->id)){
				$taskID = '('.$this->task->id.'): ';
			}else{
				$taskID = '';
			}

			CLI::write(
				CLI::color($taskID. $ex->getMessage(), 'red') . ': ' .
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

		if($this->once) exit;

		return;
	}
}
