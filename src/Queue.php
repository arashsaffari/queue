<?php

namespace CodeigniterExt\Queue;

use CodeigniterExt\Queue\Task;

/**
 * Queue
 */
class Queue
{
	const EVENT_ADD_TASK = 'qutee.queue.add_task';
	const EVENT_CLEAR_ALL_TASKS = 'qutee.queue.clear_all_tasks';

	/**
	 *
	 * @var \CodeigniterExt\Queue\Persistor\PersistorInterface
	 */
	protected $_persistor;

	/**
	 *
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $_eventDispatcher;
	
	/**
	 *
	 * @var \CodeigniterExt\Queue\Queue
	 */
	static protected $_instance;

	public function __construct()
	{

		$config = \CodeigniterExt\Queue\Controllers\Queue::getConfig();

		$connectionName = $config->queueConnection;
		
		$conncetion = $config->{$connectionName};
		
		$queuePersistor = new $conncetion['persistor']( $conncetion['params'] );
		
		$this->setPersistor($queuePersistor);

		self::$_instance = $this;
		
	}

	/**
	 * 
	 * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	public function getEventDispatcher()
	{
		if (null === $this->_eventDispatcher) {
			$this->_eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher;
		}
		return $this->_eventDispatcher;
	}

	/**
	 * 
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
	 * 
	 * @return \CodeigniterExt\Queue\Queue
	 */
	public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
	{
		$this->_eventDispatcher = $eventDispatcher;
		return $this;
	}

	/**
	 *
	 * @return \CodeigniterExt\Queue\Persistor\PersistorInterface
	 */
	public function getPersistor()
	{
		return $this->_persistor;
	}

	/**
	 *
	 * @param \CodeigniterExt\Queue\Persistor\PersistorInterface $persistor
	 *
	 * @return \CodeigniterExt\Queue\Queue
	 */
	public function setPersistor(\CodeigniterExt\Queue\Persistor\PersistorInterface $persistor)
	{
		$this->_persistor = $persistor;

		return $this;
	}

	/**
	 *
	 * @param \CodeigniterExt\Queue\Task $task
	 *
	 * @return \CodeigniterExt\Queue\Queue
	 */
	public function addTask(Task $task)
	{
		$this->getPersistor()->addTask($task);

		$event = new Event($this);
		$event->setTask($task);
		
		$this->getEventDispatcher()->dispatch(self::EVENT_ADD_TASK, $event);

		return $this;
	}

	/**
	 *
	 * @param int $priority
	 *
	 * @return \CodeigniterExt\Queue\Task
	 */
	public function getTask($priority = null)
	{
		return $this->getPersistor()->getTask($priority);
	}

	/**
	 *
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function deleteTask(Task $task)
	{
		return $this->getPersistor()->deleteTask($task);
	}


	/**
	 *
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function setTaskError(Task $task)
	{
		return $this->getPersistor()->setTaskAsFailed($task);
	}

	/**
	 * Clear all tasks
	 *
	 * @return boolean
	 */
	public function clear()
	{
		if ($this->getPersistor()->clear()) {

			$event = new Event($this);

			$this->getEventDispatcher()->dispatch(self::EVENT_CLEAR_ALL_TASKS, $event);
			return true;
		}
		
		return false;
	}

	/**
	 * Create queue
	 *
	 * @param array $config:
	 *  persistor: name of the persistor adapter
	 *  options:   array with options for the persistor
	 *
	 * @return \CodeigniterExt\Queue\Queue
	 * @throws \InvalidArgumentException
	 */
	static public function factory($config = array())
	{
		if (isset($config['persistor'])) {
			$persistorClass = 'CodeigniterExt\\Queue\\Persistor\\'. ucfirst($config['persistor']);
			if (class_exists($persistorClass)) {
				$persistor = new $persistorClass;
			} elseif (class_exists($config['persistor'])) {
				$persistor = new $config['persistor'];
			}

			if (!isset($persistor) || !is_object($persistor)) {
				throw new \InvalidArgumentException(sprintf('Persistor "%s" doesn\'t exist', $config['persistor']));
			} elseif (!($persistor instanceof Persistor\PersistorInterface)) {
				throw new \InvalidArgumentException(sprintf('Persistor "%s" does not implement Persistor\PersistorInterface', $config['persistor']));
			}

			if (isset($config['options'])) {
				$persistor->setOptions($config['options']);
			}
		} else {
			// Default persistor
			$persistor = new \CodeigniterExt\Queue\Persistor\Memory;
		}

		$queue = new self;
		$queue->setPersistor($persistor);

		return $queue;
	}

	static public function setInstance($instance)
	{
		self::$_instance = $instance;
	}

	/**
	 *
	 * @return \CodeigniterExt\Queue\Queue
	 */
	static public function get()
	{
		if (null === self::$_instance) {
			throw new Exception('Queue not created');
		}

		return self::$_instance;
	}
}
