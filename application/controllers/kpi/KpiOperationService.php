<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/14/2016
 * Time: 4:31 PM
 */
class kpiOperationService{

    const HOURLY = 0;
    const DAILY = 1;
    const WEEKLY = 2;
    const MONTHLY = 3;
    const YEARLY = 4;
    
    
    private $Porting_model = null;
    private $Rollback_model = null;
    private $Provisioning_model = null;
    private $Numberreturn_model = null;

    public function __construct()
    {

        // Load models
        /*
        parent::__construct();
        $this->load->model('Porting_model');
        $this->load->model('Rollback_model');
        $this->load->model('Provisioning_model');
        $this->load->model('Numberreturn_model');
        */
        $CI =& get_instance();
        $this->db = $CI->db;

        $CI->load->model('Porting_model');
        $CI->load->model('Rollback_model');
        $CI->load->model('Provisioning_model');
        $CI->load->model('Numberreturn_model');
        
        $this->Porting_model = $CI->Porting_model;
        $this->Rollback_model = $CI->Rollback_model;
        $this->Provisioning_model = $CI->Provisioning_model;
        $this->Numberreturn_model = $CI->Numberreturn_model;

    }
    
    public function test(){
        $response = $this->totalPort('2017-03-06 18:10:33', '2017-04-04 19:40:21',  kpiOperationService::MONTHLY);
        echo json_encode($response);
    }
    
    /*
    public function in(){
        $response = $this->totalPortIn('2017-03-06 18:10:33', '2017-04-04 19:40:21',  kpiOperationService::MONTHLY);
        echo json_encode($response);
    }
    public function out(){
        $response = $this->totalPortOut('2017-03-06 18:10:33', '2017-04-04 19:40:21',  kpiOperationService::MONTHLY);
        echo json_encode($response);
    }
    public function total(){
        $response = $this->totalPort('2017-03-06 18:10:33', '2017-04-04 19:40:21',  kpiOperationService::MONTHLY);
        echo json_encode($response);
    }*/

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     * @return mixed
     */
    public function totalPortOut($startDateTime, $endDateTime, $granularity){

        
        $totalPortsOut = $this->Porting_model->get_total_ports_out($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortIn($startDateTime, $endDateTime, $granularity){

        $totalPortsIn = $this->Porting_model->get_total_ports_in($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortOutDeniedRejected($startDateTime, $endDateTime, $granularity){

        $totalPortsOutDR = $this->Porting_model->get_total_ports_out_denied_rejected($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsOutDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsOutDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsOutDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsOutDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsOutDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortInDeniedRejected($startDateTime, $endDateTime, $granularity){

        $totalPortsInDR = $this->Porting_model->get_total_ports_in_denied_rejected($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsInDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsInDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsInDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsInDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsInDR as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPort($startDateTime, $endDateTime, $granularity){

        $totalPorts = $this->Provisioning_model->get_total_ports($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortOutMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacksOut = $this->Rollback_model->get_total_rollbacks_out($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortInMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacksIn = $this->Rollback_model->get_total_rollbacks_in($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacks = $this->Provisioning_model->get_total_rollbacks($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y');
                        if (isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] += 1;
                        }else{
                            $timeInterval[$routingChangeDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;


    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortOutCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsOutCancelled = $this->Porting_model->get_total_ports_out_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortInCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsInCancelled = $this->Porting_model->get_total_ports_in_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    /**
     * @param $startDateTime
     * @param $endDateTime
     * @param $granularity
     */
    public function totalPortCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsCancelled = $this->Porting_model->get_total_ports_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] += 1;
                        }else{
                            $timeInterval[$portingDateTime] = 1;
                        }
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    public function msisdnPortOut($startDateTime, $endDateTime, $granularity){

        $totalmsisdnPortsOut = $this->Porting_model->get_total_ports_out($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalmsisdnPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalmsisdnPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalmsisdnPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalmsisdnPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalmsisdnPortsOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
            }


        }

        return $timeInterval;
    }

    public function msisdnPortIn($startDateTime, $endDateTime, $granularity){

        $totalmsisdnPortsIn = $this->Porting_model->get_total_ports_in($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalmsisdnPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalmsisdnPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalmsisdnPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalmsisdnPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalmsisdnPortsIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPort($startDateTime, $endDateTime, $granularity){

        $totalmsisdnPorts = $this->Provisioning_model->get_total_ports($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalmsisdnPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalmsisdnPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalmsisdnPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalmsisdnPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalmsisdnPorts as $port){
                        $routingChangeDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPortOutMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacksOut = $this->Rollback_model->get_total_rollbacks_out($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacksOut as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPortInMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacksIn = $this->Rollback_model->get_total_rollbacks_in($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacksIn as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $port['msisdn']);
                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPortMistake($startDateTime, $endDateTime, $granularity){

        $totalRollbacks = $this->Provisioning_model->get_total_rollbacks($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y-m');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalRollbacks as $port){
                        $routingChangeDateTime =  date_format(date_create($port['routingChangeDateTime']), 'Y');
                        if (!isset($timeInterval[$routingChangeDateTime])){
                            $timeInterval[$routingChangeDateTime] = [];
                        }

                        $timeInterval[$routingChangeDateTime] = array_merge($timeInterval[$routingChangeDateTime], $port['msisdn']);
                    }

                    break;
            }

        }

        return $timeInterval;

    }

    public function msisdnPortOutCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsOutCancelled = $this->Porting_model->get_total_ports_out_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsOutCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPortInCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsInCancelled = $this->Porting_model->get_total_ports_in_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
            }


        }

        return $timeInterval;

    }

    public function msisdnPortCancelled($startDateTime, $endDateTime, $granularity){

        $totalPortsInCancelled = $this->Porting_model->get_total_ports_cancelled($startDateTime,$endDateTime);

        $timeInterval = $this->getInterval($startDateTime, $endDateTime, $granularity, true);

        if ($timeInterval){

            switch ($granularity){
                case kpiOperationService::HOURLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d H:00:00');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);

                    }

                    break;
                case kpiOperationService::DAILY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::WEEKLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'W Y-m-d');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::MONTHLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y-m');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
                case kpiOperationService::YEARLY:

                    foreach ($totalPortsInCancelled as $port){
                        $portingDateTime =  date_format(date_create($port['portingDateTime']), 'Y');
                        if (!isset($timeInterval[$portingDateTime])){
                            $timeInterval[$portingDateTime] = [];
                        }

                        $tmpRes = [];

                        foreach ($port['msisdn'] as $msisdn){

                            $tmpRes[] = array(
                                'msisdn' => $msisdn,
                                'reason' => $port['denyRejectionReason']
                            );

                        }

                        $timeInterval[$portingDateTime] = array_merge($timeInterval[$portingDateTime], $tmpRes);                    }

                    break;
            }


        }

        return $timeInterval;

    }

    private function getInterval($startDateTime, $endDateTime, $granularity, $isMSISDN = false){

        $timeInterval = [];

        switch ($granularity){
            case kpiOperationService::HOURLY:
                $timeInterval = $this->getHoursInInterval($startDateTime, $endDateTime);
                break;
            case kpiOperationService::DAILY:
                $timeInterval = $this->getDaysInInterval($startDateTime, $endDateTime);
                break;
            case kpiOperationService::WEEKLY:
                $timeInterval = $this->getWeeksInInterval($startDateTime, $endDateTime);
                break;
            case kpiOperationService::MONTHLY:
                $timeInterval = $this->getMonthsInInterval($startDateTime, $endDateTime);
                break;
            case kpiOperationService::YEARLY:
                $timeInterval = $this->getYearsInInterval($startDateTime, $endDateTime);
                break;
            default:
                $timeInterval = false;
        }

        $response = [];

        if ($timeInterval){

            foreach ($timeInterval as $time){

                if($isMSISDN){

                    $response[$time] = [];

                }else{

                    $response[$time] = 0;
                }

            }

        }

        return $response;

    }

    /**
     * Returns array of hours in time interval provided
     * @param $startDateTime
     * @param $endDateTime
     */
    private function getHoursInInterval($startDateTime, $endDateTime){

        $startTime = new DateTime($startDateTime);
        $endTime = new DateTime($endDateTime);

        $interval = new DateInterval('PT1H');
        $dateRange = new DatePeriod($startTime, $interval, $endTime);

        $timeInterval = [];

        foreach($dateRange as $date){
            $timeInterval[] = $date->format('Y-m-d H:00:00');
        }

        return $timeInterval;
    }

    /**
     * Returns array of days in time interval provided
     * @param $startDateTime
     * @param $endDateTime
     */
    private function getDaysInInterval($startDateTime, $endDateTime){

        $startTime = new DateTime($startDateTime);
        $endTime = new DateTime($endDateTime);

        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startTime, $interval, $endTime);

        $timeInterval = [];

        foreach($dateRange as $date){
            $timeInterval[] = $date->format('Y-m-d');
        }

        return $timeInterval;

    }

    /**
     * Returns array of weeks in time interval provided
     * @param $startDateTime
     * @param $endDateTime
     */
    private function getWeeksInInterval($startDateTime, $endDateTime){

        $startTime = new DateTime($startDateTime);
        $endTime = new DateTime($endDateTime);

        $interval = new DateInterval('P1W');
        $dateRange = new DatePeriod($startTime, $interval, $endTime);

        $timeInterval = [];

        foreach($dateRange as $date){
            $timeInterval[] = $date->format('W Y-m-d');
        }

        return $timeInterval;

    }

    /**
     * Returns array of months in time interval provided
     * @param $startDateTime
     * @param $endDateTime
     */
    private function getMonthsInInterval($startDateTime, $endDateTime){

        $startTime = new DateTime($startDateTime);
        $endTime = new DateTime($endDateTime);

        $interval = new DateInterval('P1M');
        $dateRange = new DatePeriod($startTime, $interval, $endTime);

        $timeInterval = [];

        foreach($dateRange as $date){
            $timeInterval[] = $date->format('Y-m');
        }

        return $timeInterval;

    }

    /**
     * Returns array of years in time interval provided
     * @param $startDateTime
     * @param $endDateTime
     */
    private function getYearsInInterval($startDateTime, $endDateTime){

        $startTime = new DateTime($startDateTime);
        $endTime = new DateTime($endDateTime);

        $interval = new DateInterval('P1Y');
        $dateRange = new DatePeriod($startTime, $interval, $endTime);

        $timeInterval = [];

        foreach($dateRange as $date){
            $timeInterval[] = $date->format('Y');
        }

        return $timeInterval;

    }

}