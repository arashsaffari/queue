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
     * Get next task from the queue and update it as taken task
     * update is_taken = 1
     *
     * @param int $priority Return only tasks with this priority
     *
     * @return \CodeigniterExt\Queue\Task|null
     */
    public function getTask($priority = null);


    /**
     * Return only a task with this ID
     * this task can also be executed or faulty
     * 
     *
     * @param integer $id Return only a task with this ID
	 * @param string $ran Return only a executed task with this ID
	 * @param string $faulty Return only a faulty tasks with this ID
	 * @return \CodeigniterExt\Queue\Task|null
	 */
    public function getTaskWithID(int $id = null, string $executed = null , string $faulty = null);


    /**
     * Set a task as taken
     * update is_taken = 1
     *
     * @param \CodeigniterExt\Queue\Task $name
     * 
     * @return boolean
     */
    public function setTaskAsTaken(\CodeigniterExt\Queue\Task $task);


    /**
     * Set task to not done and failed
     * update is_taken = 0, error = 0
	 * 
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return boolen
	 */
    public function setTaskAsNotTakenNotfailed(\CodeigniterExt\Queue\Task $task);


    /**
     * set task as failed
     * update error = 1
     *
     * @param \CodeigniterExt\Queue\Task $name
     * 
     * @return boolen
     */
    public function setTaskAsFailed(\CodeigniterExt\Queue\Task $task);


    /**
     * 
	 * Reset all failed task to not taken and not failed
     * update is_taken = 0, error = 0
	 *
	 * @return boolen
	 */
    public function resetAllFailedTasks();


    /**
	 * Count failed tasks
	 *
	 * @return int
	 */
    public function countFailedTasks();


    /**
     * delete completed task from the queue
     *
     * @param \CodeigniterExt\Queue\Task $name
     *
     * @return boolen
     */
    public function deleteTask(\CodeigniterExt\Queue\Task $task);


    /**
	 * delete a task with ID
	 *
	 * @param integer $id
	 * @param string $executed
	 * @param string $faulty
	 * @return int $affectedRows
	 */
    public function deleteTaskWithID(int $id = null, string $executed = null , string $faulty = null);


    /**
     * Delete all tasks
     *
     * @return int $affectedRows
     */
    public function clear();


    /**
     * Delete all failed tasks
     *
     * @return int $affectedRows
     */
    public function clearFailed();

}