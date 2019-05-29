<?php

namespace CodeigniterExt\Queue\Controllers;

use CodeIgniter\Controller;

class Queue extends Controller
{
    public function __construct(){}

    
    public static function getConfig()
    {
        $config = config( 'Queue' );
        
        if (empty($config)){
            $config = new \CodeigniterExt\Queue\Config\Queue();
        }

        return $config;
    }
}
