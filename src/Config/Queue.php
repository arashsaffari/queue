<?php

namespace CodeigniterExt\Queue\Config;

use CodeIgniter\Config\BaseConfig;

class Queue extends BaseConfig
{

    //--------------------------------------------------------------------
    // maintenance mode file path
    //--------------------------------------------------------------------
    // 
    //
    public $queueConnection = 'codeigniter';


    public $codeigniter = [
		'persistor'			=> 'CodeigniterExt\Queue\Persistor\codeigniter\Codeigniter',
		'params'    		=> [
			'db_group'		=> false,
			'table_name'	=> 'queue_tasks',
		],
	];
	
	public $pdo = [
		'persistor'			=> 'CodeigniterExt\Queue\Persistor\Pdo\Pdo',
		'params'    		=> [
			'dsn'       => 'mysql:host=localhost;dbname=ci4_modules;charset=utf8',
			'username'  => 'root',
			'password'  => 'root',
			'table_name'=> 'queue_tasks1'
		],
	];

	public $redis = [
		'persistor'			=> 'CodeigniterExt\Queue\Persistor\Pdo\Pdo',
		'params'    		=> [
			'host'  => '127.0.0.1',
			'port'  => 6379
		],
	];

}
