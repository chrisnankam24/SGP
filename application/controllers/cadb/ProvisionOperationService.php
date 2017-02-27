<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 8:33 AM
 */

require_once "Provision.php";
require_once "Fault.php";
require_once "Common.php";

use ProvisionService\Provision as Provision;

/**
 * Class ProvisionOperationService
 */
class ProvisionOperationService {

    // Declare client
    private $client = null;
    private $FileLog_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $this->db = $CI->db;

        $CI->load->model('FileLog_model');

        $this->FileLog_model = $CI->FileLog_model;

        // Disable wsdl_1_4 cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ProvisionOperationService.wsdl', array(
            "trace" => true,
            'stream_context' => stream_context_create(array(
                'http' => array(
                    'header' => 'Authorization: Bearer ' . Auth::CADB_AUTH_BEARER
                ),
            )),
        ));

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

    }

    /**
     * @param $processId string process id of porting process to be confirmed
     * @return mixed
     */
    public function confirmRoutingData($processId) {

        if($this->client) {

            // Make confirmRoutingData request
            $request = new Provision\confirmRoutingDataRequest();

            $request->processId = $processId;

            try {

                $response = $this->client->confirmRoutingData($request);

                $this->logRequestResponse('confirmRoutingData');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('confirmRoutingData');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

                $response->error = $fault;

                return $response;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

    private function logRequestResponse($action){
        $this->fileLogAction('', 'ProvisionOperationService', $action . ' Request:: ' . $this->client->__getLastRequest());
        $this->fileLogAction('', 'ProvisionOperationService', $action . ' Response:: ' . $this->client->__getLastResponse());
    }

}