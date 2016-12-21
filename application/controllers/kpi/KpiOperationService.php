<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/14/2016
 * Time: 4:31 PM
 */
class kpiOperationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Load models

        $this->load->model('Porting_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbackstateevolution_model');

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

    }

    /**
     * Returns total port OUT
     * @param $startDateTime
     * @param $endDateTime
     */
    public function totalPortOut($startDateTime, $endDateTime){

    }

    public function totalPortIn($startDateTime, $endDateTime){

    }

    public function totalPortOutDeniedRejected($startDateTime, $endDateTime){

    }

    public function totalPortInDeniedRejected($startDateTime, $endDateTime){

    }

    public function totalPort($startDateTime, $endDateTime){

    }

    public function totalPortOutMistake($startDateTime, $endDateTime){

    }

    public function totalPortInMistake($startDateTime, $endDateTime){

    }

    public function totalPortMistake($startDateTime, $endDateTime){

    }

    public function totalPortOutCancelled($startDateTime, $endDateTime){

    }

    public function totalPortInCancelled($startDateTime, $endDateTime){

    }

    public function totalPortCancelled($startDateTime, $endDateTime){

    }
}