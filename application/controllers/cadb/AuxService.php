<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once '_Aux.php';
require_once 'Common.php';
require_once 'Fault.php';

use AuxService\Aux as Aux;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 3:51 PM
 */

/**
 * Simulating Controller for AuxService made by CADB
 * Class AuxService
 */
class AuxService {

    // Declare client
    private $client = null;
    private $FileLog_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $CI->load->model('FileLog_model');

        $this->FileLog_model = $CI->FileLog_model;

        // Disable wsdl_1_4 cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/AuxService.wsdl', array(
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
     * @param $networkId string id of network operator
     * @return errorResponse
     */
    public function getOperator($networkId) {

        if($this->client) {

            // Make getOperator request
            $request = new Aux\getOperatorRequest();

            $request->networkId = $networkId;

            try {

                $response = $this->client->getOperator($request);

                $response->success = true;

                $this->logRequestResponse('getOperator');

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('getOperator');

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

    /**
     * @return mixed
     */
    public function getOperators() {

        if($this->client) {

            // Make getOperators request
            $request = new Aux\getOperatorsRequest();

            try {

                $response = $this->client->getOperators($request);

                $this->logRequestResponse('getOperators');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('getOperators');

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
        $this->fileLogAction('8066', 'AuxService', $action . ' Request:: ' . $this->client->__getLastRequest());
        $this->fileLogAction('8066', 'AuxService', $action . ' Response:: ' . $this->client->__getLastResponse());
    }

}

