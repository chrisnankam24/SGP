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

    }

    /**
     * Switches or creates or deletes number in KPSA to toOperator from fromOperator
     * @param $misisdn
     * @param $fromOperator
     * @param $toOperator
     * @param $fromRoutingNumber
     * @param $toRoutingNumber
     * @return array
     */
    public function performKPSAOperation($msisdn, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber){
        $response = [];
        $response['success'] = true;

        // If OPR = OPA, delete MSISDN in KPSA
        if($toOperator == Operator::ORANGE_NETWORK_ID && isOCMNumber($msisdn)){ // Orange number coming back

            // Delete number from KPSA
            $deleteResponse = $this->deleteSubscriberTEKELEC($msisdn);

            if($deleteResponse['success'] == true){
                // Operation successful
                $response['success'] = true;
            }else{
                // Operation failed
                $response['message'] = $deleteResponse['message'];
                $response['success'] = false;
            }

        }else{
            //create MSISDN with routing number toOperator

            $createResponse = $this->creationSubscriberTEKELEC($msisdn, $toRoutingNumber);

            if($createResponse['success'] == true){
                // Operation successful
                $response['success'] = true;
            }else{
                // Operation failed
                $response['message'] = $createResponse['message'];
                $response['success'] = false;
            }

        }

        return $response;

    }

    /**
     * Returns Number to Operator. Used during number return PO
     * @param $msisdn
     * @param $toOperator
     * @param $toRoutingNumber
     */
    public function performKPSAReturnOperation($msisdn, $returnOperator, $returnRoutingNumber){

        $response = [];
        $response['success'] = true;

        $deleteResponse = $this->deleteSubscriberTEKELEC($msisdn);

        if($deleteResponse['success'] == true){
            // Operation successful
            $response['success'] = true;
        }else{
            // Operation failed
            $response['message'] = $deleteResponse['message'];
            $response['success'] = false;
        }

        return $response;

    }

    /**
     * Switches or creates MSISDN in KPSA. This is called by other operator not directly involved in porting, rollback or return process
     * @param $msisdn
     * @param $toOperator
     * @param $toRoutingNumber
     * @return array
     */
    public function performKPSAOtherOperation($msisdn, $toOperator, $toRoutingNumber){

        $response = [];
        $response['success'] = true;

        $createResponse = $this->creationSubscriberTEKELEC($msisdn, $toRoutingNumber);

        if($createResponse['success'] == true){
            // Operation successful
            $response['success'] = true;
        }else{
            // Operation failed
            $response['message'] = $createResponse['message'];
            $response['success'] = false;
        }

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
        $creationResponse['success'] = -1; // -1 means connection to KPSA failed, false means connection to KPSA ok but STATUS is FAILED, and finally, true means connected to KPSA with STATUS COMPLETED

        $requestId = 1;

        try {

            $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
                "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_CREATE_NEW_PORT;MSISDN=$msisdn;MOBILE_NETWORK=$routingNumber}");

            $response = $this->parseKPSAResponse($response);

            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $creationResponse['success'] = false;
                $creationResponse['message'] = $responses['LIBELLE'];
            }else{
                $creationResponse['success'] = true;
            }

        }catch (Exception $ex){
            $creationResponse['success'] = -1;
            $creationResponse['message'] = 'FAILED CONNECTING TO KPSA';
        }

        return $creationResponse;

    }

    /**
     * API to delete a subscriber from TEKELEC
     * @param $msisdn
     * @return array
     */
    private function deleteSubscriberTEKELEC($msisdn){

        $deleteResponse = [];
        $deleteResponse['success'] = -1; // -1 means connection to KPSA failed, false means connection to KPSA ok but STATUS is FAILED, and finally, true means connected to KPSA with STATUS COMPLETED

        $requestId = 1;

        try {

            $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
                "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_DELETE_PORT;MSISDN=$msisdn}");

            $response = $this->parseKPSAResponse($response);

            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $deleteResponse['success'] = false;
                $deleteResponse['message'] = $responses['LIBELLE'];
            }else{
                $deleteResponse['success'] = true;
            }

        }catch (Exception $ex){
            $deleteResponse['success'] = -1;
            $deleteResponse['message'] = 'FAILED CONNECTING TO KPSA';
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
        $updateResponse['success'] = -1; // -1 means connection to KPSA failed, false means connection to KPSA ok but STATUS is FAILED, and finally, true means connected to KPSA with STATUS COMPLETED

        $requestId = 1;

        try {

            $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
                "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_UPDATE_PORT_NETWORK;MSISDN=$msisdn;MOBILE_NETWORK=$routingNumber}");

            $response = $this->parseKPSAResponse($response);

            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $updateResponse['success'] = false;
                $updateResponse['message'] = $responses['LIBELLE'];
            }else{
                $updateResponse['success'] = true;
            }

        }catch (Exception $ex){
            $updateResponse['success'] = -1;
            $updateResponse['message'] = 'FAILED CONNECTING TO KPSA';
        }

        return $updateResponse;

    }

    /**
     * API to view subscriber in TEKELEC
     * @param $msisdn
     * @return array
     */
    public function viewSubscriberTEKELEC($msisdn){

        $viewResponse = [];
        $viewResponse['success'] = -1; // -1 means connection to KPSA failed, false means connection to KPSA ok but STATUS is FAILED, and finally, true means connected to KPSA with STATUS COMPLETED

        $requestId = 1;

        try {

            $response = file_get_contents("https://" . KPSAParams::HOST . ":" . KPSAParams::PORT .
                "/exec_mcp?MCP={ID=$requestId;ACT=TEKELEC_QUERY_SUBSCRIBER;MSISDN=$msisdn}");

            $response = $this->parseKPSAResponse($response);

            $tmp_responses = explode(';', $response);

            $responses = [];

            foreach ($tmp_responses as $tmp_response){
                $pieces = explode('=', $tmp_response);
                $responses[$pieces[0]] = $pieces[1];
            }

            if($responses['STATUS'] == 'FAILED'){
                $viewResponse['success'] = false;
                $viewResponse['message'] = $responses['LIBELLE'];
            }else{
                $viewResponse['success'] = true;
                $viewResponse['routingNumber'] = $responses['MOBILE_NETWORK'];
            }

        }catch (Exception $ex){
            $viewResponse['success'] = -1;
            $viewResponse['message'] = 'FAILED CONNECTING TO KPSA';
        }

        return $viewResponse;

    }

    private function parseKPSAResponse($response){

        $startPos = strpos($response, '<BLOCKQUOTE>');
        $endPos = strpos($response, '</BLOCKQUOTE>');

        $response = substr($response, $startPos + 12, $endPos - $startPos -12);

        return $response;

    }

}