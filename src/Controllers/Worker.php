<?php namespace CodeigniterExt\Queue\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class Worker extends Controller
{
    private $config;

    public function __construct(){}

    public static function getConfig()
    {
        // $config = config( 'MaintenanceMode' );
        
        // if (empty($config)){
        //     $config = config( 'CodeigniterExt\MaintenanceMode\MaintenanceMode' );
        // }

        // return $config;
    }

    /**
     * 
     */
    public static function Done()
    {
        echo "Done from Queue";
    }
}
