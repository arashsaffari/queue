<?php

namespace CodeigniterExt\Queue\Persistor\Codeigniter;

use CodeigniterExt\Queue\Persistor\Codeigniter\TaskModel;
use CodeigniterExt\Queue\Persistor\Codeigniter\TaskEntity;

use CodeigniterExt\Queue\Task;
use CodeigniterExt\Queue\Persistor\PersistorInterface;

/**
 * Codeigniter persistor
 *
 */

class Codeigniter implements PersistorInterface
{
	private $_options;
	
	/**
	 *
	 * @var \TaskModel
	 */
	private $_TaskModel;

	/**
	 *
	 * @var \TaskEntity
	 */
	private $_TaskEntity;
	

	/**
	 * 
	 * @param Array Queue\Config\Queue  $queueConnection['params']
	 */
	public function __construct(array $options)
	{
		$this->setOptions($options);
	}

	/**
	 *
	 * @param Array Config\Queue  $queueConnection['params']
	 *
	 * @return $queueConnection['params']
	 */
	public function setOptions(array $options)
	{	
		$this->_TaskModel 	= new TaskModel($options);

		$this->_TaskEntity 	= new  $this->_TaskModel->returnType;

		$this->_testConnection();

		$this->_options = $options;
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

		$TaskEntity = new $this->_TaskEntity([
			"name"          => $task->getName(),
			"method_name"   => $task->getMethodName(),
			"data"          => $task,
			"priority"      => $task->getPriority(),
			"unique_id"     => $task->isUnique() ? $task->getUniqueId() : null,
			"created_at"    => date('Y-m-d H:i:s'),
		]);

		if (! $this->_TaskModel->save($TaskEntity) ){
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
		try {
			$this->_TaskModel
				->where([
					'id' 		=> $task->id,
					'is_taken' 	=> 1,
					'error' 	=> 0
				])
				->delete();
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}

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
		try {
			$this->_TaskModel
				->update($task->id, [
					'error' => 1
				]);
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
			
		return true;
	}


	/**
	 * 
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return boolen
	 */
	public function setTaken(Task $task)
	{
		try {
			$this->_TaskModel->update($task->id, [
				'is_taken' => 1
			]);
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
			
		return true;
	}


	/**
	 * 
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return boolen
	 */
	public function updateTask(Task $task)
	{
		try {

			$this->_TaskModel->update($task->id, [
				"is_taken" 	=> 0,
				"error"     => 0,
			]);

		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
			
		return true;
	}


	/**
	 * 
	 *
	 * @return boolen
	 */
	public function resetFailedTasks()
	{
		try {

			$this->_TaskModel
    			->where(['error'=> 1])
    			->set([
					'is_taken' => 0,
					'error'=> 0
				])
    			->update();

		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
			
		return true;
	}

	
	/**
	 * 
	 *
	 * @return int
	 */
	public function getCountFailedTasks()
	{
		try {

			(int)$counter = $this->_TaskModel
    			->where(['error'=> 1])
				->countAllResults();

		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
			
		return $counter;
	}


	/**
	 * 
	 * @param int $priority
	 *
	 * @return Task|null
	 */
	public function getTask($priority = null)
	{
		try {
			$this->_TaskModel->where('is_taken', 0);
			if ($priority !== null) {
				$this->_TaskModel->where('priority', $priority);
			}
			$this->_TaskModel->orderBy('created_at', 'asc');
			
			$QueueJob = $this->_TaskModel->first();

			if (empty($QueueJob)) {
				return null;
			}

			$QueueJob->is_taken = 1;

		
			$this->_TaskModel->save($QueueJob);
			
			return $QueueJob->data;
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}

	}

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
	public function getTaskWithID(int $id = null, string $executed = null , string $faulty = null)
	{
		
		if ( !is_int($id) || $id === 0 ){
			throw new \Exception('id was not entered');
		}

		if(null !== $executed){
			$executed = ($executed !== "0") ? 1 : 0;
		}

		if(null !== $faulty){
			$faulty = ($faulty !== "0") ? 1 : 0;
		}

		try {

			$whereArray = array('id' => $id );

			if (null !== $executed){
				$whereArray = array_merge($whereArray, [
					'is_taken' => $executed
				]);
			}

			if (null !== $faulty){
				$whereArray = array_merge($whereArray, [
					'error' => $faulty
				]);
			}

			$this->_TaskModel->where($whereArray);
			
			$QueueJob = $this->_TaskModel->first();

			if (empty($QueueJob)) {
				return null;
			}

			$QueueJob->is_taken = 1;

			$this->_TaskModel->save($QueueJob);
			
			return $QueueJob->data;
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
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
			$this->_TaskModel->where('priority', $priority);
		}
		
		$this->_TaskModel->orderBy('created_at', 'asc');

		try {
			$allTasks = $this->_TaskModel->findAll();
		}
		catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}

		return $allTasks;
	}

	/**
	 * Clear all tasks
	 */
	public function clear()
	{
		try{

			// $this->_TaskModel->emptyTable();
			$this->_TaskModel
				->where('is_taken', 0)
				->orWhere('is_taken', 1)
				->delete();

		}catch (\mysqli_sql_exception $ex) {
			$this->_handelMysqliSqlException($ex);
		}
	}

	/**
	 * Test connection, reconnect if needed
	 *
	 *
	 * @throws \CodeigniterExt\Queue\Persistor\Codeigniter\DBConnecException
	 */
	protected function _testConnection()
	{
		try {

			$db = \Config\Database::connect();
			$db->reconnect();

		}
		catch (\mysqli_sql_exception $ex) {
			
			$this->_handelMysqliSqlException($ex);

		}
	}

	/**
	 * 
	 * @param string $uniqueId
	 * 
	 * @return boolean
	 */
	protected function _hasTaskByUniqueId($uniqueId)
	{
		
		$queueAllJobs = $this->_TaskModel
						->where('is_taken', 0)
						->where('unique_id', $uniqueId)
						->findAll();
		return !empty($queueAllJobs);
	}

	/**
	 * Undocumented function
	 *
	 * @param \mysqli_sql_exception $ex
	 */
	protected function _handelMysqliSqlException($ex)
	{
		throw new \CodeigniterExt\Queue\DBConnectionException(
			$ex->getMessage(),
			(int)$ex->getCode(),
			$ex->getPrevious()
		);
	}

}
