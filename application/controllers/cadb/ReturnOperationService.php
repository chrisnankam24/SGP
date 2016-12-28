<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 9:13 AM
 */

require_once "Common.php";
require_once "Return.php";
require_once "Fault.php";

use ReturnService\_Return as _Return;


/**
 * Class ReturnOperationService
 */
class ReturnOperationService extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {

        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ReturnOperationService.wsdl', array(
            "location" => 'http://localhost/SGP/index.php/cadb/ReturnOperationService',
            "trace" => false
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ReturnOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ReOSServerFunctionalities");

        // Handle soap operations
        $server->handle();

    }

    /**
     * @param $primaryOwner
     * @param $msisdn
     * @return errorResponse
     */
    public function open($primaryOwner, $msisdn) {

        if($this->client) {

            // Make open request
            $request = new _Return\openRequest();

            $request->ownerNrn = new nrnType();
            $request->ownerNrn->networkId = Operator::ORANGE_NETWORK_ID;;
            $request->ownerNrn->routingNumber = Operator::ORANGE_ROUTING_NUMBER;

            $request->primaryOwnerNrn = new nrnType();

            if($primaryOwner == 0) {
                // MTN
                $request->primaryOwnerNrn->networkId = Operator::MTN_NETWORK_ID;
                $request->primaryOwnerNrn->routingNumber = Operator::MTN_ROUTING_NUMBER;
            }else{
                // Orange
                $request->primaryOwnerNrn->networkId = Operator::NEXTTEL_NETWORK_ID;
                $request->primaryOwnerNrn->routingNumber = Operator::NEXTTEL_ROUTING_NUMBER;
            }

            // numberRange
            $numRange = new numberRangeType();
            $numRange->endNumber = $msisdn;
            $numRange->startNumber = $msisdn;
            $request->numberRanges = array($numRange);

            try {

                $response = $this->client->open($request);
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
     * @param $returnId string id of return process to accept
     * @return errorResponse
     */
    public function accept($returnId) {

        if($this->client) {

            // Make accept request
            $request = new _Return\acceptRequest();

            $request->returnId = $returnId;

            try {

                $response = $this->client->accept($request);
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
     * @param $returnId string id of return process to reject
     * @param $cause string cause of rejection
     * @return errorResponse
     */
    public function reject($returnId, $cause) {

        if($this->client) {

            // Make reject request
            $request = new _Return\rejectRequest();

            $request->returnId = $returnId;
            $request->cause = $cause;

            try {

                $response = $this->client->reject($request);
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
     * @param $returnId
     * @return errorResponse
     */
    public function getReturningTransaction($returnId) {

        if($this->client) {

            // Make getReturningTransaction request
            $request = new _Return\getReturningTransactionRequest();
            $request->returnId = $returnId;

            try {

                $response = $this->client->getReturningTransaction($request);
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
     * @param $networkId
     * @return errorResponse
     */
    public function getCurrentReturningTransactions($networkId) {

        if($this->client) {

            // Make getCurrentReturningTransactions request
            $request = new _Return\getCurrentReturningTransactionsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getCurrentReturningTransactions($request);
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

}