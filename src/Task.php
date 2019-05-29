<?php

namespace CodeigniterExt\Queue;

use CodeigniterExt\Queue\Queue;

/**
 * Task
 *
 * @author anorgan
 */
class Task
{
    /**
     * Default name of the method to run the task
     */
    const DEFAULT_METHOD_NAME   = 'run';

    /**
     * Low priority
     */
    const PRIORITY_LOW          = 1;

    /**
     * Normal priority
     */
    const PRIORITY_NORMAL       = 2;

    /**
     * High priority
     */
    const PRIORITY_HIGH         = 3;

    /**
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * @var string
     */
    protected $_methodName;

    /**
     *
     * @var array
     */
    protected $_data;

    /**
     *
     * @var int
     */
    protected $_priority = self::PRIORITY_NORMAL;

    /**
     *
     * @var string
     */
    protected $_uniqueId;

    /**
     *
     * @param string $name
     * @param array $data
     * @param int $priority
     * @param string $unique_id
     * @param string $methodName
     *
     * @param array $data
     */
    public function __construct($name = null, $data = array(), $priority = self::PRIORITY_NORMAL, $unique_id = null, $methodName = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $data) {
            $this->setData($data);
        }

        if (null !== $methodName) {
            $this->setMethodName($methodName);
        }

        if (null !== $priority) {
            $this->setPriority($priority);
        }

        if (null !== $unique_id) {
            $this->setUniqueId($unique_id);
        }
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        // Name can hold method name in it
        if (strpos($name, '::')) {
            list($name, $methodName) = explode('::', $name);
        }

        // Validate name
        if (!preg_match('/^[a-zA-Z0-9\/\\\ _-]+$/', $name)) {
            throw new \InvalidArgumentException('Name can be only alphanumerics, spaces, underscores and dashes');
        }

        if (isset($methodName)) {
            $this->setMethodName($methodName);
        }

        $this->_name = $name;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getMethodName()
    {
        if ($this->_methodName === null) {
            $this->_methodName = self::DEFAULT_METHOD_NAME;
        }

        return $this->_methodName;
    }

    /**
     *
     * @param string $methodName
     * @return \CodeigniterExt\Queue\Task
     *
     * @throws \InvalidArgumentException
     */
    public function setMethodName($methodName)
    {
        // validate name
        if (!preg_match('/^[a-z][a-zA-Z0-9_]+$/', $methodName)) {
            throw new \InvalidArgumentException('Method name can be only alphanumerics and underscores');
        }

        $this->_methodName = $methodName;

        return $this;
    }

    /**
     *
     * @return string
     * @throws Exception
     */
    public function getClassName()
    {
        if ($this->_name === null) {
            throw new Exception('Name not set, can not create class name');
        }

        if (strpos($this->_name, '\\') !== false) {
            // FQCN?
            $className = $this->_name;
        } elseif (strpos($this->_name, '/') !== false) {
            // Forward slash FQCN?
            $className = str_replace('/', '\\', $this->_name);
        } else {
            $className = str_replace(array('-','_'), ' ', strtolower($this->_name));
            $className = str_replace(' ', '', ucwords($className));
        }

        return $className;
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * @param array $data
     *
     * @return Task
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     *
     * @param int $priority
     *
     * @return Task
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;

        return $this;
    }

    /**
     *
     * @return string|boolean
     */
    public function getUniqueId()
    {
        if (!$this->isUnique()) {
            return false;
        }

        return md5($this->getName() . $this->_uniqueId);
    }

    /**
     *
     * @param string $uniqueId
     *
     * @return \CodeigniterExt\Queue\Task
     */
    public function setUniqueId($uniqueId)
    {
        $this->_uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Task is unique if unique identifier is not null
     *
     * @return boolean
     */
    public function isUnique()
    {
        return !is_null($this->_uniqueId);
    }

    /**
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_name', '_data', '_methodName', '_priority', '_uniqueId');
    }

    /**
     *
     * @param string $name
     * @param array $data
     * @param int $priority
     * @param string $unique_id
     * @param string $methodName
     *
     * @return Task
     */
    public static function create($name, $data = array(), $priority = self::PRIORITY_NORMAL, $unique_id = null, $methodName = null)
    {

        $queue  = Queue::get();
        $task   = new self($name, $data, $priority, $unique_id, $methodName);
        $queue->addTask($task);

        return $task;
    }
}