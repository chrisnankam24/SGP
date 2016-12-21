<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 10:34 AM
 */

require_once "Rollback.php";
require_once "Common.php";
require_once "Fault.php";

use RollbackService\Rollback as rollback;

class RollbackOperationService  extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/RollbackOperationService.wsdl', array(
            "trace" => false
        ));

    }

    public function index(){

    }

    /**
     * @param $originalPortingId string porting id of original porting process
     * @param $donorSubmissionDateTime string of submission process
     * @param $preferredRollbackDateTime string of preferred porting process
     * @return errorResponse
     */
    public function open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime) {

        if($this->client) {

            // Make open request
            $request = new rollback\openRequest();

            $request->originalPortingId = $originalPortingId;

            $request->donorSubmissionDateTime = $donorSubmissionDateTime;

            $request->preferredRollbackDateTime = $preferredRollbackDateTime;

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
     * @param $rollbackId string id of rollback to accept
     * @return mixed
     */
    public function accept($rollbackId) {

        if($this->client) {

            // Make accept request
            $request = new rollback\acceptRequest();

            $request->rollbackId = $rollbackId;

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
     * @param $rollbackId string id of rollback process
     * @param $cause string cause of rejection
     * @param $rejectionReason rollback\rejectionReasonType
     * @return errorResponse
     */
    public function reject($rollbackId, $cause, $rejectionReason) {

        if($this->client) {

            // Make reject request
            $request = new rollback\rejectRequest();

            $request->rollbackId = $rollbackId;

            $request->cause = $cause;

            $request->rejectionReason = $rejectionReason;

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
     * @param $rollbackId string id of rollback process
     * @param $rollbackDateAndTime datetime for provisioning system
     * @return errorResponse
     */
    public function confirm($rollbackId, $rollbackDateAndTime) {

        if($this->client) {

            // Make confirm request
            $request = new rollback\confirmRequest();
            $request->rollbackId = $rollbackId;
            $request->rollbackDateAndTime = $rollbackDateAndTime;

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
     * @return mixed
     */
    public function getRollback() {

        if($this->client) {

            // Make getRollback request
            $request = new rollback\getRollbackRequest();

            try {

                $response = $this->client->getRollback($request);
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
    public function getOpenedRollbacks() {

        if($this->client) {

            // Make getOpenedRollbacks request
            $request = new rollback\getOpenedRollbacksRequest();

            try {

                $response = $this->client->getOpenedRollbacks($request);
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
    public function getAcceptedRollbacks() {

        if($this->client) {

            // Make getAcceptedRollbacks request
            $request = new rollback\getAcceptedRollbacksRequest();

            try {

                $response = $this->client->getAcceptedRollbacks($request);
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
    public function getConfirmedRollbacks() {

        if($this->client) {

            // Make getConfirmedRollbacks request
            $request = new rollback\getConfirmedRollbacksRequest();

            try {

                $response = $this->client->getConfirmedRollbacks($request);
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
    public function getRejectedRollbacks() {

        if($this->client) {

            // Make getRejectedRollbacks request
            $request = new rollback\getRejectedRollbacksRequest();

            try {

                $response = $this->client->getRejectedRollbacks($request);
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