<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Fault.php";

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/16/2016
 * Time: 8:10 AM
 */
class KpsaOperationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('FileLog_model');

    }

    public function performKPSAOperation($fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber){
        $response = [];
        $response->success = true;
        $response->message = '';
        // If OPR = OPA, delete MSISDN in KPSA

        // Else if MSISDN not in KPSA, create MSISDN with routing number toOperator

        // Else if MSISDN in KPSA, update MSISDN with routing number toOperator
        return $response;

    }

    public function performKPSAOtherOperation($toOperator, $toRoutingNumber){
        $response = [];
        $response->success = true;
        $response->message = '';
        // If OPR = OPA, delete MSISDN in KPSA

        // Else if MSISDN not in KPSA, create MSISDN with routing number toOperator

        // Else if MSISDN in KPSA, update MSISDN with routing number toOperator
        return $response;

    }

    /**
     * API to create a subscriber in TEKELEC
     * @param $msisdn
     * @param $routingNumber
     * @return array
     */
    private function creationSubscriberTEKELEC($msisdn, $routingNumber){

        $creationResponse = [];
        $creationResponse->success = true;

        //TODO: Verify if MSISDN is full or partial

        $requestId = 1;

        $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
            "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_CREATE_NEW_PORT;MSISDN=$msisdn;MOBILE_NETWORK=$routingNumber}");

       if($response){
           $tmp_responses = explode(';', $response);

           $responses = [];

           foreach ($tmp_responses as $tmp_response){
               $pieces = explode('=', $tmp_response);
               $responses[$pieces[0]] = $pieces[1];
           }

           if($responses['STATUS'] == 'FAILED'){
               $creationResponse->success = false;
               $creationResponse->message = $responses['LIBELLE'];
           }
       }else{
           $creationResponse->success = false;
           $creationResponse->message = 'FAILED GETTING CONTENT FROM API';
       }

        return $creationResponse;

    }

    /**
     * API to delete a subscriber from TEKELEC
     * @param $msisdn
     * @param $routingNumber
     * @return array
     */
    private function deleteSubscriberTEKELEC($msisdn, $routingNumber){

        $deleteResponse = [];
        $deleteResponse->success = true;

        //TODO: Verify if MSISDN is full or partial

        $requestId = 1;

        $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
            "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_DELETE_PORT;MSISDN=$msisdn;MOBILE_NETWORK=$routingNumber}");

        if($response){
            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $deleteResponse->success = false;
                $deleteResponse->message = $responses['LIBELLE'];
            }
        }else{
            $deleteResponse->success = false;
            $deleteResponse->message = 'FAILED GETTING CONTENT FROM API';
        }

        return $deleteResponse;

    }

    /**
     * API to update subscriber in TEKELEC
     * @param $msisdn
     * @param $routingNumber
     * @return array
     */
    private function updateSubscriberTEKELEC($msisdn, $routingNumber){

        $updateResponse = [];
        $updateResponse->success = true;

        //TODO: Verify if MSISDN is full or partial

        $requestId = 1;

        $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
            "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_UPDATE_PORT_NETWORK;MSISDN=$msisdn;MOBILE_NETWORK=$routingNumber}");

        if($response){
            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $updateResponse->success = false;
                $updateResponse->message = $responses['LIBELLE'];
            }
        }else{
            $updateResponse->success = false;
            $updateResponse->message = 'FAILED GETTING CONTENT FROM API';
        }

        return $updateResponse;

    }

    /**
     * API to view subscriber in TEKELEC
     * @param $msisdn
     * @return array
     */
    private function viewSubscriberTEKELEC($msisdn){

        $viewResponse = [];
        $viewResponse->success = true;

        //TODO: Verify if MSISDN is full or partial

        $requestId = 1;

        $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
            "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_QUERY_SUBSCRIBER;MSISDN=$msisdn}");

        if($response){
            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $viewResponse->success = false;
                $viewResponse->message = $responses['LIBELLE'];
            }else{
                $viewResponse->routingNumber = $responses['MOBILE_NETWORK'];
            }
        }else{
            $viewResponse->success = false;
            $viewResponse->message = 'FAILED GETTING CONTENT FROM API';
        }

        return $viewResponse;

    }

}