<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 04/05/2016
 * Time: 14:59
 */

/**
 * Class Log_Model
 * Model for logging related processes.
 */
class FileLog_Model extends CI_Model
{
    
    public function __construct()
    {
        parent::__construct();
        // Load file helper class
        $this->load->helper('file');
    }

    
    public function write_log($level, $class, $message){
        
        $timestamp = date('y/m/d  H:i:s');

        $data = $timestamp . '  ' . $level . '  ' . $class . ': ' . $message . "\r\n";

        if ( ! write_file(APPPATH . 'logs/logs.txt', $data, 'ab'))
        {

        }
        else
        {

        }
    }
}