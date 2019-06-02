<?php namespace CodeigniterExt\Queue\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeigniterExt\Queue\Queue;

class Forget extends BaseCommand
{
	protected $group        = 'Queue';
	protected $name         = 'queue:forget';
	protected $description  = 'Delete a failed queue task';
	protected $usage        = 'queue:forget';
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
			$id = CLI::prompt("please enter the task ID");
		}

		if ( !ctype_digit($id) )
		{
			CLI::error('The specified ID is wrong!');
			return;
		}

		$this->task_id = $id;

		if (!$this->quiet) CLI::write('delete the failed queue task ...', 'white');

		if($this->task_id){

			//
			// Init Queue
			//
			$this->queue = new Queue();

			//
			// delete the task in DB
			//
			$this->deletedStatus = $this->queue->getPersistor()->deleteTaskWithID($this->task_id,null,"1");



			if ($this->deletedStatus){

				if (!$this->quiet) CLI::write('the task was deleted successfully!' , 'green');
			
			}else{
			
				if (!$this->quiet) CLI::error('Either the task is not in the database or the task has not been marked as error.');
			
			}

			CLI::newLine(1);
		}else{
			if (!$this->quiet) CLI::error('task id was not entered correctly!');
		}

	}

}
