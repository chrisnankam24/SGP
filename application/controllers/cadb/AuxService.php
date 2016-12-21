<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once '_Aux.php';
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
class AuxService extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/AuxService.wsdl', array(
            "location" => 'http://localhost/SGP/index.php/cadb/AuxService',
            "trace" => false
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/AuxService.wsdl');

        // Set the class for the soap server
        $server->setClass("ServerFunctionalities");

        // Handle soap operations
        $server->handle();
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

                return $response;

            }catch (SoapFault $e){

                $response = new errorResponse();

                $fault = key($e->detail);

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

                return $response;

            }catch (SoapFault $e){

                $response = new errorResponse();

                $fault = key($e->detail);

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

}

class ServerFunctionalities {

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $getOperatorRequest
     * @return Aux\getOperatorResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     */
    public function getOperator($getOperatorRequest){

        $response = new Aux\getOperatorResponse();

        return $response;
    }

    /**
     * @param $getOperatorsRequest
     * @return Aux\getOperatorsResponse
     * @throws invalidRequestFormatFault
     */
    public function getOperators($getOperatorsRequest){

        $response = new Aux\getOperatorsResponse();

        return $response;
    }

}

