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

    
    public function write_log($code, $class, $message){
        
        $timestamp = date('H:i:s');

        $data = $timestamp . '  ' . $class . '  ' . $code . ': ' . $message . "\r\n";

        if ( ! write_file(APPPATH . 'logs/' . date('d-m-y') . '.txt', $data, 'ab'))
        {

        }
        else
        {

        }
    }

    public function email_list(){

        $file = APPPATH . "/email_list.txt";

        $emailFile = fopen($file, "r");

        $emailList = fread($emailFile, filesize($file));

        $emailList = explode(';', $emailList);

        fclose($emailFile);

        return $emailList;

    }
}