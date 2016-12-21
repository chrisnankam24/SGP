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
class ProvisionOperationService extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ProvisionOperationService.wsdl', array(
            "location" => 'http://localhost/SGP/index.php/cadb/ProvisionOperationService',
            "trace" => false
        ));

    }

    public function index(){


    }

    public function test() {
        $provisionOperationService = new ProvisionOperationService();
        $response = $provisionOperationService->confirmRoutingData('zerzer');
        var_dump($response);
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