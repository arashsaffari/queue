<?php

namespace CodeigniterExt\Queue;

use CodeigniterExt\Queue\Exception;
use CodeigniterExt\Queue\Queue;
use CodeigniterExt\Queue\Task;
use CodeigniterExt\Queue\TaskInterface;

/**
 * Worker
 *
 * @author anorgan
 */
class Worker
{
    /**
     * Run every 5 seconds by default
     */
    const DEFAULT_INTERVAL = 5;
    
    const EVENT_START_PROCESSING_TASK = 'qutee.worker.start_processing_task';
    const EVENT_END_PROCESSING_TASK = 'qutee.worker.end_processing_task';

    /**
     * Run every X seconds
     *
     * @var int
     */
    protected $_interval = self::DEFAULT_INTERVAL;

    /**
     * Do only tasks with this priority or all if priority is null
     *
     * @var int
     */
    protected $_priority;

    /**
     *
     * @var Queue
     */
    protected $_queue;

    /**
     *
     * @var float
     */
    protected $_startTime;

    /**
     *
     * @var float
     */
    protected $_passedTime;

    /**
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->_interval;
    }

    /**
     *
     * @param int $interval
     *
     * @return Worker
     */
    public function setInterval($interval)
    {
        $this->_interval = $interval;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     *
     * @param int $priority
     *
     * @return Worker
     *
     * @throws \InvalidArgumentException
     */
    public function setPriority($priority)
    {
        if ($priority !== null && !is_int($priority)) {
            throw new \InvalidArgumentException('Priority must be null or an integer');
        }

        $this->_priority = $priority;

        return $this;
    }

    /**
     *
     * @return Queue
     */
    public function getQueue()
    {
        if (null === $this->_queue) {
            $this->_queue = Queue::get();
        }

        return $this->_queue;
    }

    /**
     *
     * @param Queue $queue
     *
     * @return Worker
     */
    public function setQueue(Queue $queue)
    {
        $this->_queue = $queue;

        return $this;
    }


    /**
     * get a Task
     *
     * @return Task|null Task which ran, or null if no task found
     * @throws \Exception
     */
    public function getTask()
    {
        // Start timing
        $this->_startTime();


        $task = $this->getQueue()->getTask($this->getPriority());

        // Get next task with set priority (or any task if priority not set)
        if ($task === null) {
            $this->_sleep();
            return;
        }

        return $task;
    }


    /**
     * Run the worker, run them
     *
     * @return Task
     * @throws \Exception
     */
    public function run($task)
    {
        $event = new Event($this);
        $event->setArgument('startTime', $this->_startTime);
        $event->setTask($task);

        $this->getQueue()->getEventDispatcher()->dispatch(self::EVENT_START_PROCESSING_TASK, $event);
        
        $this->_runTask($task);

        if (!empty($task->id)){
            $this->getQueue()
                ->deleteTask($task);
        }

        // $this->getQueue()->where('id', 12)->delete();
        
        $event = new Event($this);
        $event->setArgument('elapsedTime', $this->_getPassedTime());
        $event->setTask($task);
        
        $this->getQueue()->getEventDispatcher()->dispatch(self::EVENT_END_PROCESSING_TASK, $event);

        // After working, sleep
        $this->_sleep();

        return $task;
    }


    /**
     * cleans up the completed tasks
     *
     * @return null
     */
    public function setTaskAsFailed($task)
    {
        $this->getQueue()
                ->setTaskError($task);
    }

    /**
     * Start timing
     */
    protected function _startTime()
    {
        $this->_startTime = microtime(true);
    }

    /**
     * Get passed time
     *
     * @return float
     */
    protected function _getPassedTime()
    {
        return abs(microtime(true) - $this->_startTime);
    }

    /**
     * Sleep
     *
     * @return null
     */
    protected function _sleep()
    {
        // Time ... enough
        if ($this->_getPassedTime() <= $this->_interval) {
            $remainder = ($this->_interval) - $this->_getPassedTime();
            usleep($remainder * 1000000);
        } // Task took more than the interval, don't sleep
    }

    /**
     * Get class of the task, run it's default method or method specified in
     * task data [method]
     *
     * @param Task $task
     */
    protected function _runTask(Task $task)
    {
        $taskClassName  = $task->getClassName();
        if (!class_exists($taskClassName)) {
            throw new \InvalidArgumentException(sprintf('Error! Task %s not found', $task->getName()));
        }

        $taskObject     = new $taskClassName;

        if ($taskObject instanceof TaskInterface) {
            
            $taskObject->setData($task->getData());

            $taskObject->run();
            
            return $taskObject;

        } else {

            $methodName     = $task->getMethodName();
            $taskObject->$methodName($task->getData());

        }
    }

}
