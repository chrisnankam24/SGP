<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/8/2016
 * Time: 8:06 AM
 */
class Rio extends CI_Controller {

    function _construct()
    {
        parent::__construct();

    }


    public function index(){
        $this->load->view('rio');
    }


}