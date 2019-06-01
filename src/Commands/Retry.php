<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeigniterExt\Queue\Queue;

class Retry extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:retry';
	protected $description  = 'Retry failed queue task';
	protected $usage        = 'queue:retry';
	protected $arguments    = [
		'id'	=> 'queue task ID'
	];
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

		if (!$this->quiet) CLI::write('set the task as not running ...', 'white');

		if($this->task_id){

			//
			// Init Queue
			//
			$this->queue = new Queue();

			//
			// find task in DB
			//
			$this->task = $this->queue->getPersistor()->getTaskWithID($this->task_id,null,"1");

			if (!empty($this->task)){

				//
				// update task in DB
				//
				$this->task->error = 0;
				$this->task->is_taken = 0;
				$this->queue->getPersistor()->updateTask($this->task);

				if (!$this->quiet){
					CLI::write(
						CLI::color('('.$this->task->id.'): ', 'yellow').
						CLI::color('Updated task ', 'green').
						$this->task->getName()
					);
				}
				
				$this->call("queue:run", [$this->task->id]);
			
			}else{
			
				if (!$this->quiet) CLI::error('Either the task is not in the database or there is no error flag');
			
			}

			CLI::newLine(1);
		}else{
			if (!$this->quiet) CLI::error('task id was not entered correctly!');
		}

	}

}
