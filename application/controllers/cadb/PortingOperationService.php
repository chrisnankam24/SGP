<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Porting.php";
require_once "Fault.php";

use PortingService\Porting as Porting;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 1:06 PM
 */

/**
 * Simulating Controller for PortingOperationService made by CADB
 * Class PortingOperationService
 */
class PortingOperationService extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/PortingOperationService.wsdl', array(
            "trace" => false
        ));

    }

    public function index(){

    }

    public function test(){
        $portingOperationService = new PortingOperationService();

        $response = $portingOperationService->order(1, 'df', 'sef', 'sfsd', 'sdf');

        $date = "2016-12-08T14:29:51+01:00";

        $date1 = date('c', strtotime('+4 hours', strtotime(date('c'))));
        $date2 = date('c', strtotime(date('c')));

        var_dump($date1);
        echo '<br>';
        var_dump($date2);
    }

    /**
     * @param $donorOperator
     * @param $portingDateTime
     * @param $msisdn
     * @param $rio
     * @param $subscriberInfo
     * @return errorResponse
     */
    public function order($donorOperator, $portingDateTime, $msisdn, $rio, $subscriberInfo) {

        if($this->client) {

            // Make order request
            $request = new Porting\orderRequest();

            try {

                // RecipientNrn
                $request->recipientNrn = new nrnType();
                $request->recipientNrn->networkId = Operator::ORANGE_NETWORK_ID;
                $request->recipientNrn->routingNumber = Operator::ORANGE_ROUTING_NUMBER;

                // DonorNrn
                $request->donorNrn = new nrnType();

                if($donorOperator == 0) {
                    // MTN
                    $request->donorNrn->networkId = Operator::MTN_NETWORK_ID;
                    $request->donorNrn->routingNumber = Operator::MTN_ROUTING_NUMBER;
                }else{
                    // Orange
                    $request->donorNrn->networkId = Operator::NEXTTEL_NETWORK_ID;
                    $request->donorNrn->routingNumber = Operator::NEXTTEL_ROUTING_NUMBER;
                }

                //recipientSubmissionDateTime
                $request->recipientSubmissionDateTime = date('c');

                // recipient PortingDateTime
                $request->portingDateTime = $portingDateTime;

                // rio
                $request->rio = $rio;

                // numberRange
                $numRange = new numberRangeType();
                $numRange->endNumber = $msisdn;
                $numRange->startNumber = $msisdn;
                $request->numberRanges = array($numRange);

                // subscriberInfo
                $request->subscriberInfo = $subscriberInfo;

                $response = $this->client->order($request);

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
     * @param $portingId string Id of porting process to approve
     * @return mixed
     */
    public function approve($portingId){
        if($this->client) {

            // Make approve request
            $request = new Porting\approveRequest();

            $request->portingId = $portingId;

            try {

                $response = $this->client->approve($request);

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
     * @param $portingId string id of porting process to accept
     * @return errorResponse
     */
    public function accept($portingId) {

        if($this->client) {

            // Make accept request
            $request = new Porting\acceptRequest();

            $request->portingId = $portingId;

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
     * @param $portingId string porting process to confirm
     * @param $portingDateAndTime string date and time of successful System update
     * @return errorResponse
     */
    public function confirm($portingId, $portingDateAndTime) {

        if($this->client) {

            // Make confirm request
            $request = new Porting\confirmRequest();

            $request->portingId = $portingId;
            $request->portingDateTime = $portingDateAndTime;

            try {

                $response = $this->client->confirm($request);

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
     * @param $porting_id string porting process to deny
     * @param $rejectionReason string reson of denial
     * @param $cause string description of the denial
     * @return errorResponse
     */
    public function reject($porting_id, $rejectionReason, $cause) {

        if($this->client) {

            // Make reject request
            $request = new Porting\rejectRequest();

            $request->portingId = $porting_id;

            $request->rejectionReason = $rejectionReason;

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
     * @param $porting_id string porting process to deny
     * @param $rejectionReason string reson of denial
     * @param $cause string description of the denial
     * @return errorResponse
     */
    public function deny($porting_id, $rejectionReason, $cause) {

        if($this->client) {

            // Make deny request
            $request = new Porting\denyRequest();

            $request->portingId = $porting_id;

            $request->rejectionReason = $rejectionReason;

            $request->cause = $cause;

            try {

                $response = $this->client->deny($request);

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
     * @param $portingId
     * @return errorResponse
     */
    public function getPorting($portingId) {

        if($this->client) {

            // Make getPorting request
            $request = new Porting\getPortingRequest();
            $request->portingId = $portingId;

            try {

                $response = $this->client->getPorting($request);

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
    public function getOrderedPortings($networkId) {

        if($this->client) {

            // Make getOrderedPortings request
            $request = new Porting\getOrderedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getOrderedPortings($request);

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
    public function getApprovedPortings($networkId) {

        if($this->client) {

            // Make getApprovedPortings request
            $request = new Porting\getApprovedPortingsRequest();
            $request->networkId =$networkId;

            try {

                $response = $this->client->getApprovedPortings($request);

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
    public function getAcceptedPortings($networkId) {

        if($this->client) {

            // Make getAcceptedPortings request
            $request = new Porting\getAcceptedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getAcceptedPortings($request);

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
    public function getConfirmedPortings($networkId) {

        if($this->client) {

            // Make getConfirmedPortings request
            $request = new Porting\getConfirmedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getConfirmedPortings($request);

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
     * @param $count
     * @return errorResponse
     */
    public function getDeniedPortings($networkId, $count) {

        if($this->client) {

            // Make getDeniedPortings request
            $request = new Porting\getDeniedPortingsRequest();
            $request->networkId = $networkId;
            $request->count = $count;

            try {

                $response = $this->client->getDeniedPortings($request);

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
     * @param $count
     * @return errorResponse
     */
    public function getRejectedPortings($networkId, $count) {

        if($this->client) {

            // Make getRejectedPortings request
            $request = new Porting\getRejectedPortingsRequest();
            $request->networkId = $networkId;
            $request->count = $count;

            try {

                $response = $this->client->getRejectedPortings($request);

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