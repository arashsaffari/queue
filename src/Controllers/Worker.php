<?php namespace CodeigniterExt\Queue\Controllers;

use CodeIgniter\Controller;
use CodeigniterExt\Queue\Queue;
use CodeigniterExt\Queue\Task;
use CodeigniterExt\Queue\Persistor\Codeigniter\Codeigniter;

use CodeigniterExt\Queue\Entities\QueueJob;
use CodeigniterExt\Queue\Models\QueueJobModel;
use Config\Services;

class Worker extends Controller
{
    private $config;

    public function __construct(){}

    public static function getConfig()
    {
        // $config = config( 'MaintenanceMode' );
        
        // if (empty($config)){
        //     $config = config( 'CodeigniterExt\MaintenanceMode\MaintenanceMode' );
        // }

        // return $config;
    }

    /**
     * 
     */
    public static function Done()
    {

		$queue = new Queue();

		$task = new Task;

		for ($i=0; $i < 10; $i++) { 
			
			$task
				->setName('App/Controllers/SendMail')
				->setData(
					array(
						'to'        => 'arash@saffari.com',
						'from'      => 'qutee@nowhere.tld',
						'subject'   => 'Hi!',
						'text'      => 'It\'s your faithful QuTee!'
					)
				)
				->setPriority(Task::PRIORITY_HIGH);
				// ->setUniqueId('send_mail_email'. $i .'@domain.tld');

			// Queue it
			$queue->addTask($task);



			$task
				->setName('App/Controllers/SendMail1')
				->setData(
					array(
						'to'        => 'arash@saffari.com',
						'from'      => 'qutee@nowhere.tld',
						'subject'   => 'Hi!',
						'text'      => 'It\'s your faithful QuTee!'
					)
				)
				->setPriority(Task::PRIORITY_HIGH);
				// ->setUniqueId('send_mail_email'. $i .'@domain.tld');

			// Queue it
			$queue->addTask($task);

			
		}
		// Create a task
		
		

		}
		


		public static function getTask()
    {

		$queue = new Queue();
		$worker = new \CodeigniterExt\Queue\Worker();
		$worker
			->setQueue($queue)
			->setInterval(0.5);
		$task = $worker->getTask();
		if ($task !== null) {
			$worker->run($task);
		}
		

		echo "Done!";

		
		}
}
