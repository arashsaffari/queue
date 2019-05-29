<?php

namespace CodeigniterExt\Queue\Persistor\Codeigniter;

use CodeigniterExt\Queue\Persistor\Codeigniter\QueueJobEntity;
use CodeigniterExt\Queue\Persistor\Codeigniter\CodeigniterModel;

use CodeigniterExt\Queue\Task;
use CodeigniterExt\Queue\Persistor\PersistorInterface;

/**
 * Codeigniter persistor
 *
 */

class Codeigniter implements PersistorInterface
{
    
    /**
     *
     * @var \QueueJobModel
     */
    private $_QueueJobs;
    

    /**
     * 
     * @param \PDO $pdo
     */
    public function __construct(array $options)
    {

		$this->config = \CodeigniterExt\Queue\Controllers\Queue::getConfig();
		
		$this->setOptions($options);
    }

    /**
     *
     * @return array
     */
    public function getOptions(){}

    /**
     *
     * @param array $options
     *
     * @return Pdo
     */
    public function setOptions(array $options)
    {

        $this->_QueueJobs = new CodeigniterModel($options);

        return $this;
    }

    /**
     * 
     * @param \CodeigniterExt\Queue\Task $task
     *
     * @return \CodeigniterExt\Queue\Persistor\Pdo
     */
    public function addTask(Task $task)
    {
        // Check if the task is unique and already exists
        if ($task->isUnique() && $this->_hasTaskByUniqueId($task->getUniqueId())) {
            return $this;
        }

        $QueueJob = new QueueJobEntity([
            "name"          => $task->getName(),
            "method_name"   => $task->getMethodName(),
            "data"          => $task,
            "priority"      => $task->getPriority(),
            "unique_id"     => $task->isUnique() ? $task->getUniqueId() : null,
            "created_at"    => date('Y-m-d H:i:s'),
        ]);

        if (! $this->_QueueJobs->save($QueueJob) ){
            return false;
        }

        return $this;
    }

    /**
     * 
     * @param \CodeigniterExt\Queue\Task $task
     *
     * @return boolen
     */
    public function deleteTask(Task $task)
    {
        $this->_QueueJobs
            ->where('id', $task->id)
            ->where('is_taken', 1)
            ->where('error', null)
            ->delete();

        return true;
    }


    /**
     * 
     * @param \CodeigniterExt\Queue\Task $task
     *
     * @return boolen
     */
    public function setError(Task $task)
    {
        $this->_QueueJobs
            ->update($task->id, [
                'error' => 1
            ]);
            
        return true;
    }

    /**
     * 
     * @param int $priority
     *
     * @return Task|null
     */
    public function getTask($priority = null)
    {

        $this->_QueueJobs->where('is_taken', 0);
        if ($priority !== null) {
            $this->_QueueJobs->where('priority', $priority);
		}
        $this->_QueueJobs->orderBy('created_at', 'asc');
        
        $QueueJob = $this->_QueueJobs->first();

        if (empty($QueueJob)) {
            return null;
        }

		$QueueJob->is_taken = 1;
		
        $this->_QueueJobs->save($QueueJob);

        return $QueueJob->data;
    }

    /**
     * 
     * @param int $priority
     *
     * @return Task[]
     */
    public function getTasks($priority = null) 
    {
        if ($priority !== null) {
            $this->_QueueJobs->where('priority', $priority);
        }
        
        $this->_QueueJobs->orderBy('created_at', 'asc');

        $allTasks = $this->_QueueJobs->findAll();

        return $allTasks;
    }

    /**
     * Clear all tasks
     */
    public function clear()
    {
        // $this->_QueueJobs->emptyTable();
        $this->_QueueJobs
            ->where('is_taken', 0)
            ->orWhere('is_taken', 1)
            ->delete();
    }

    /**
     * 
     * @param string $uniqueId
     * 
     * @return boolean
     */
    protected function _hasTaskByUniqueId($uniqueId)
    {
        $queueAllJobs = $this->_QueueJobs
                        ->where('is_taken', 0)
                        ->where('unique_id', $uniqueId)
                        ->findAll();
        return !empty($queueAllJobs);
    }
}