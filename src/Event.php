<?php

namespace CodeigniterExt\Queue;

use CodeigniterExt\Queue\Task;

/**
 * Description of Event
 *
 * @author anorgan
 */
class Event extends \Symfony\Component\EventDispatcher\GenericEvent
{

    /**
     *
     * @var Task
     */
    protected $task;

    /**
     * 
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * 
     * @param \CodeigniterExt\Queue\Task $task
     *
     * @return \CodeigniterExt\Queue\Event
     */
    public function setTask(Task $task)
    {
        $this->task = $task;
        
        return $this;
    }

}
