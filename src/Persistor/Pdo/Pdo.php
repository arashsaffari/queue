<?php

namespace CodeigniterExt\Queue\Persistor\Pdo;

use CodeigniterExt\Queue\Task;
use CodeigniterExt\Queue\Persistor\PersistorInterface;

/**
 * PDO persistor, use table with columns: name, data, priority
 *
 * MySQL 
 CREATE TABLE IF NOT EXISTS `queue_tasks` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `method_name` VARCHAR(255) NULL,
    `data` TEXT NULL,
    `priority` TINYINT NOT NULL,
    `unique_id` VARCHAR(32) NULL,
    `created_at` DATETIME NOT NULL,
    `is_taken` TINYINT(1) NOT NULL DEFAULT 0,
    `error` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`))
  ENGINE = InnoDB;

 *
 * @author anorgan
 */
class Pdo implements PersistorInterface
{
    /**
     *
     * @var array
     */
    private $_options = array();
    
    /**
     *
     * @var \PDO
     */
    private $_pdo;
    
    /**
     *
     * @var int
     */
    private static $_reconnects = 3;

    /**
     * 
     * @param \PDO $pdo
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
    }

    /**
     *
     * @param Array Queue\Config\Queue  $queueConnection['params']
     *
     * @return PersistorInterface
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

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

        $statement = $this->_getPdo()->prepare(sprintf(' 
            INSERT INTO %s
            SET
                name        = :name,
                method_name = :method_name,
                data        = :data,
                priority    = :priority,
                unique_id   = :unique_id,
                created_at  = NOW()
        ', $this->_options['table_name']));

        $statement->execute(array(
            ':name'        => $task->getName(),
            ':method_name' => $task->getMethodName(),
            ':data'        => serialize($task),
            ':priority'    => $task->getPriority(),
            ':unique_id'   => $task->isUnique() ? $task->getUniqueId() : null,
        ));

        return $this;
    }

    /**
     * 
     * @param int $priority Return only tasks with this priority
     *
     * @return \CodeigniterExt\Queue\Task|null
     */
    public function getTask($priority = null)
    {
        try {
            
            $this->_getPdo()->exec('SET @ID = 0;');
            
            // Update first task that is not taken as taken, taking its ID
            $statement = $this->_getPdo()->prepare(sprintf('
                UPDATE
                    %s
                SET
                    id          = @ID := id,
                    is_taken    = 1
                WHERE
                    is_taken    = 0
                    %s
                ORDER BY
                    created_at ASC
                LIMIT 1
            ', $this->_options['table_name'], $priority !== null ? 'AND priority = :priority' : ''));
            $array = null;
            
            if ($priority !== null) {
                $array = array(':priority' => $priority);
            }

            $statement->execute($array);

            if ($statement->rowCount() === 0) {
                // No tasks
                return null;
            }

            // Now, get that task
            $statement  = $this->_getPdo()->prepare(sprintf('SELECT * FROM %s WHERE id = @ID', $this->_options['table_name']));
            $statement->execute();
            $taskData   = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!$taskData) {
                return null;
            }
            
            $taskID =  $this->_getPdo()->query("SELECT @ID")->fetchColumn();

            $fetchedTask = unserialize($taskData['data']);

            $fetchedTask->id = $taskID;

            return $fetchedTask;
            
        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
        
    }

    /**
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
            
            // $this->_getPdo()->exec('SET @ID = 0;');
            
            // Update first task that is not taken as taken, taking its ID
            $statement = $this->_getPdo()->prepare(sprintf('
                UPDATE
                    %s
                SET
                    is_taken    = 1
                WHERE
                    id = :id
                    %s
                    %s
                ORDER BY
                    created_at ASC
                LIMIT 1
            ',  $this->_options['table_name'],
                $executed !== null ? 'AND `is_taken` = :executed' : '',
                $faulty !== null ? 'AND `error` = :faulty' : ''
            ));
            
            $array = null;
            
            $array = array(':id' => $id);

            if ($executed !== null) {
                $array = array_merge($array, [
					':executed' => $executed
				]);
            }

            if ($faulty !== null) {
                $array = array_merge($array, [
					':faulty' => $faulty
				]);
            }

            $statement->execute($array);

            // $statement->debugDumpParams();exit;

            // if ($statement->rowCount() === 0) {
            //     // No tasks
            //     return null;
            // }

            // Now, get that task
            $statement  = $this->_getPdo()->prepare(sprintf('SELECT * FROM %s WHERE id = '.$id.'', $this->_options['table_name']));
            $statement->execute();

            // echo "-".$statement->rowCount()."-";exit;

            $taskData   = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!$taskData) {
                return null;
            }

            $fetchedTask = unserialize($taskData['data']);

            $fetchedTask->id = $id;

            return $fetchedTask;
            
        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
	 * 
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return boolen
	 */
	public function setTaskAsTaken(Task $task)
	{
        try {

            $this->_getPdo()->exec(
                sprintf('
                    UPDATE
                        %s
                    SET
                        is_taken = 1
                    WHERE
                        id = %s
            ', $this->_options['table_name'], $task->id));
                
            return true;

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
	 * 
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return boolen
	 */
	public function setTaskAsNotTakenNotfailed(Task $task)
	{
        try {

            $this->_getPdo()->exec(
                sprintf('
                    UPDATE
                        %s
                    SET
                        `is_taken` = \'0\',
                        `error` = \'0\'
                    WHERE
                        id = %s
            ', $this->_options['table_name'], $task->id));

            return true;

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
     * 
     * 
     *
     * @return boolen
     */
    public function setTaskAsFailed(Task $task)
    {

        try {

            $this->_getPdo()->exec('
                    UPDATE
                        '.$this->_options['table_name'].'
                    SET
                        `error` = \'1\'
                    WHERE
                        `id` = '.$task->id.'
            ');
                
            return true;

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
        
    }

    /**
	 * 
	 * @return boolen
	 */
	public function resetAllFailedTasks()
	{
        try {

            $this->_getPdo()->exec('
                    UPDATE
                        '.$this->_options['table_name'].'
                    SET
                        `is_taken` = \'0\',
                        `error` = \'0\'
                    WHERE
                        `error` = 1
            ');
                
            return true;

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
	 *
	 * @return int
	 */
	public function countFailedTasks()
	{
        try {
            
            $statement = $this->_getPdo()->prepare('
                    SELECT
                        COUNT(*)
                    FROM
                        '.$this->_options['table_name'].'
                    WHERE
                        `error` = \'1\'
            ');

            $statement->execute();
            
            $taskData   = $statement->fetchColumn();

            return $taskData;

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
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
            $this->_getPdo()->exec(sprintf('
                DELETE FROM %s
                    WHERE 
                        id = %s
                        AND is_taken = 1
                        AND error = 0
                ', $this->_options['table_name'], $task->id));

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
	 *
	 * @param integer $id
	 * @param string $executed
	 * @param string $faulty
	 * @return int $affectedRows
	 */
	public function deleteTaskWithID(int $id = null, string $executed = null , string $faulty = null)
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
            
            // $this->_getPdo()->exec('SET @ID = 0;');
            
            // Update first task that is not taken as taken, taking its ID
            $statement = $this->_getPdo()->prepare(sprintf('
                DELETE FROM
                    %s
                WHERE
                    id = :id
                    %s
                    %s
            ',  $this->_options['table_name'],
                $executed !== null ? 'AND `is_taken` = :executed' : '',
                $faulty !== null ? 'AND `error` = :faulty' : ''
            ));

            $array = null;
            
            $array = array(':id' => $id);

            if ($executed !== null) {
                $array = array_merge($array, [
					':executed' => $executed
				]);
            }

            if ($faulty !== null) {
                $array = array_merge($array, [
					':faulty' => $faulty
				]);
            }

            $statement->execute($array);

            return $statement->rowCount();

        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
     * Clear all tasks
     */
    public function clear()
    {
        try {
            $statement = $this->_getPdo()->prepare(sprintf('DELETE FROM %s', $this->_options['table_name']));
            $statement->execute();
            return $statement->rowCount();
        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }


    /**
	 * 
	 * @return int $affectedRows
	 */
	public function clearFailed()
	{
        try {
            $statement = $this->_getPdo()->prepare(sprintf('
                DELETE FROM 
                    %s
                WHERE
                    id = :id
                    %s', $this->_options['table_name']));
            $statement->execute();
            return $statement->rowCount();
        } catch (\Exception $ex) {
            $this->_handelMysqliSqlException($ex);
        }
    }
    

    /**
     * 
     * @param \PDO $pdo
     *
     * @return \Qutee\Persistor\Pdo
     */
    public function setPdo(\PDO $pdo) {
        $this->_pdo = $pdo;
        
        return $this;
    }

    /**
     * 
     * @return \PDO
     */
    protected function _getPdo()
    {
        if (null === $this->_pdo) {
            $dsn        = $this->_options['dsn'];
            $username   = $this->_options['username'];
            $password   = $this->_options['password'];

            $options    = array(
                \PDO::ATTR_EMULATE_PREPARES     => false, 
                \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_ASSOC
            );

            if (isset($this->_options['options'])) {
                $options = $this->_options['options'] + $options;
            }

            try {
                $this->_pdo = new \PDO($dsn, $username, $password, $options);
            } catch (\Exception $ex) {
                $this->_handelMysqliSqlException($ex);
            }
        } else {
            $this->_testConnection($this->_pdo);
        }

        return $this->_pdo;
    }
    
    /**
     * Test connection, reconnect if needed
     *
     * @param \PDO $pdo
     *
     * @throws \CodeigniterExt\Queue\Persistor\Pdo\DBConnecException
     */
    protected function _testConnection(\PDO $pdo)
    {
        try {
            // Dummy query
            $pdo->query('SELECT 1');

        } catch (\PDOException $ex) {
            // Mysql server has gone away or similar error
            self::$_reconnects--;

            if (self::$_reconnects <= 0) {
                // No more tests, throw error, reinstate reconnects
                self::$_reconnects = 3;
                
                $this->_pdo = null;

                $this->_handelMysqliSqlException($ex);
            }

            // $pdo = null;

            // usleep(4 * 1000000);
            
            // $this->_getPdo();
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
        $stmt = $this->_getPdo()
            ->prepare(sprintf('SELECT id FROM %s WHERE is_taken = 0 AND unique_id = ?', $this->_options['table_name']));
        $stmt->execute(array($uniqueId));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return !empty($rows);
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
