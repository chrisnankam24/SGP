<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/19/2016
 * Time: 8:57 PM
 */
class BSCS_model extends CI_Model{

    private $db = null;

    function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('BSCS', TRUE);

        //$this->db_b->query('YOUR QUERY');

    }



}
