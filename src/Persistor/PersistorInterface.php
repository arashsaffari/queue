<?php

namespace CodeigniterExt\Queue\Persistor;

/**
 * Persistor interface
 *
 * @author anorgan
 */
interface PersistorInterface
{

    /**
     * Set options
     *
     * @param array $options
     *
     * @return PersistorInterface
     */
    public function setOptions(array $options);

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Add task to the queue
     *
     * @param \CodeigniterExt\Queue\Task $name
     *
     * @return PersistorInterface
     */
    public function addTask(\CodeigniterExt\Queue\Task $task);

    /**
     * Get next task from the queue Update it (is_taken = 1) task
     *
     * @param int $priority Return only tasks with this priority
     *
     * @return \CodeigniterExt\Queue\Task|null
     */
    public function getTask($priority = null);

    /**
     * Get all tasks from the queue
     *
     * @param int $priority Return only tasks with this priority
     *
     * @return array array of tasks
     */
    public function getTasks($priority = null);

    /**
     * Clear all tasks from queue
     *
     * @return boolean
     */
    public function clear();

    /**
     * set error (error = 1) to given task in queue
     *
     * @param \CodeigniterExt\Queue\Task $name
     * 
     * @return boolean
     */
    public function setError(\CodeigniterExt\Queue\Task $task);

    /**
     * delete completed task from the queue
     *
     * @param \CodeigniterExt\Queue\Task $name
     *
     * @return boolean
     */
    public function deleteTask(\CodeigniterExt\Queue\Task $task);


}