<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Log extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
    }
    
}
