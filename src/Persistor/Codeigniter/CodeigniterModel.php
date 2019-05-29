<?php

namespace CodeigniterExt\Queue\Persistor\Codeigniter;

use CodeIgniter\Model;

/**
 * Codeigniter persistor
 *
 */

class CodeigniterModel extends Model
{

    protected $DBGroup;
    
    protected $table;
    
    protected $returnType;


    protected $primaryKey = 'id';
    
    protected $useSoftDeletes = false;
    
    protected $useTimestamps = false;

    // protected $validationMessages = [];

    protected $skipValidation = false;

    protected $allowedFields = [
        'name',
        'method_name',
        'data',
        'priority',
        'unique_id',
        'created_at',
        'is_taken',
        'error',
    ];

    protected $validationRules = [
		'name'			=> 'required|max_length[255]',
        'method_name'	=> 'max_length[255]',
		'data'			=> 'required',
		'priority'		=> 'numeric|required',
		'unique_id'		=> 'max_length[32]',
		'created_at'	=> 'required',
		'is_taken'		=> 'numeric|required',
		'error'			=> 'numeric|max_length[1]',
    ];
    

    public function __construct(array $options)
    {
        if(!empty($options['db_group'])){
			$this->DBGroup		= $options['db_group'];
		}
		$this->table		= $options['table_name'];
        $this->returnType	= $options['entity'];
        
        parent::__construct();
    }



}
