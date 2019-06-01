<?php

namespace CodeigniterExt\Queue\Persistor\Codeigniter;

use CodeIgniter\Entity;

class TaskEntity extends Entity
{
	
	protected $id;
    protected $name;
    protected $method_name;
    protected $data;
    protected $priority;
	protected $unique_id;
	protected $created_at;
	protected $is_taken;
	protected $error;

	protected $_options = [
        'dates' => [],
        'casts' => [
			'is_taken'	=> 'boolean',
			'error'		=> 'boolean',
		],
        'datamap' => []
	];
	
	public function setData(object $data)
	{
		$this->data = serialize($data);
	}

	public function getData()
	{
		$data = unserialize($this->data);
		$data->id = $this->id;
		return $data;
	}
}