<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use CodeigniterExt\Queue\Worker;
use CodeigniterExt\Queue\Queue;
use CodeigniterExt\Queue\Persistor\Pdo;

class Work extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:work';
	protected $description  = 'Start processing jobs on the queue as a daemon';
	protected $usage        = 'queue:work';
	protected $arguments    = [];
	protected $options 		= [];

	private $queuePersistor;
	private $queue;
	private $worker;
	private $task;

	
	public function run(array $params)
	{

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
			->setInterval(0.5);
		
		CLI::write('');
		CLI::write('Start processing jobs on the queue ...', 'white');
		CLI::write('');

		$this->runLoop();

	}

	private function runLoop()
	{	
		try
		{
			$this->loop();
		}
		finally {
			$this->runLoop();
		}
	}

	private function loop()
	{

		try
		{
			while (1) {
				
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
					CLI::write( CLI::color('Ran task: ', 'green'). $this->task->getName() );
				}

				usleep(0.5 * 1000000);
			}
		}
		catch (\Error $ex) {
			$this->exception_handler($ex);
			return;
		}
		catch (\Exception $ex) {
			$this->exception_handler($ex);
			return;
		}
		catch (\InvalidArgumentException $ex) {
			$this->exception_handler($ex);
			return;
		}
		
	}

	private function exception_handler($ex)
	{

		CLI::write(
			CLI::color($ex->getMessage(), 'red') . ': ' .
			CLI::color($ex->getFile(), 'yellow') .
			' (' . $ex->getLine() . ')'
		);

		log_message('critical', 
			'Queue Error - ' . 
			$ex->getMessage() . ': ' .
			$ex->getFile() .
			' (' . $ex->getLine() . ')'
		);

		if ($this->task !== null) {
			$this->worker->setError($this->task);
		}
	}
}
