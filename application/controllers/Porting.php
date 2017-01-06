<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Porting extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

    }

    /*
     * Listing of porting
     */
    function index()
    {
        $data['porting'] = $this->Porting_model->get_all_porting();

        $this->load->view('porting/index',$data);
    }

}
