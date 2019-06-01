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
     * @param Array Queue\Config\Queue  $queueConnection['params']
     *
     * @return PersistorInterface
     */
    public function setOptions(array $options);

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
      * Get a task with ID
      *
      * @param integer $id Return only tasks with this ID
      * @return \CodeigniterExt\Queue\Task|null
      */


    /**
     * Return only a task with this ID
     * this task can also be executed or faulty
     * 
     *
     * @param integer $id Return only a task with this ID
	 * @param string $ran Return only a executed task with this ID
	 * @param string $faulty Return only executed and faulty tasks with this ID
	 * @return \CodeigniterExt\Queue\Task|null
	 */
	public function getTaskWithID(int $id = null, string $executed = null , string $faulty = null);

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