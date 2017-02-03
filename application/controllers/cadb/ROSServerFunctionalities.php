<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Rollback.php";
require_once "RollbackNotification.php";
require_once "Common.php";
require_once "Fault.php";

use RollbackService\Rollback as rollback;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/9/2016
 * Time: 3:30 PM
 */

class ROSServerFunctionalities extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Porting_model');
        $this->load->model('Rollback_model');

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/RollbackNotificationService.wsdl', array(
            "trace" => false,
            'stream_context' => stream_context_create(array(
                'http' => array(
                    'header' => 'Authorization: Bearer ' . Auth::CADB_AUTH_BEARER
                ),
            )),
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/RollbackOperationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $openRequest
     * @return rollback\openResponse
     * @throws rollbackNotAllowedFault
     * @throws unknownPortingIdFault
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     */
    public function open($openRequest){

        $response = new rollback\openResponse();

        //$openRequest = new rollback\openRequest();

        $portingInfo = $this->Porting_model->get_porting($openRequest->originalPortingId);

        if($portingInfo == null){
            throw new unknownPortingIdFault();
        }

        $rand = mt_rand(100,998);

        $response->rollbackTransaction = new rollback\rollbackTransactionType();
        $response->rollbackTransaction->lastChangeDateTime = date('c');
        $response->rollbackTransaction->donorSubmissionDateTime = date('c');
        $response->rollbackTransaction->rollbackDateTime = $openRequest->rollbackDateTime;
        $response->rollbackTransaction->rollbackId = date('Ymd') . '-'. $portingInfo['donorNetworkId'] .'-' . $portingInfo['startMSISDN'] . '-' . $rand;

        $response->rollbackTransaction->originalPortingId = $openRequest->originalPortingId;

        $notifyOpenRequest = new \RollbackService\RollbackNotification\notifyOpenedRequest();

        $notifyOpenRequest->rollbackTransaction = new rollback\rollbackTransactionType();
        $notifyOpenRequest->rollbackTransaction->cadbOpenDateTime = date('c');
        $notifyOpenRequest->rollbackTransaction->donorSubmissionDateTime = date('c');
        $notifyOpenRequest->rollbackTransaction->lastChangeDateTime = date('c');
        $notifyOpenRequest->rollbackTransaction->rollbackDateTime = $openRequest->rollbackDateTime;

        $notifyOpenRequest->rollbackTransaction->donorNrn = new nrnType();
        $notifyOpenRequest->rollbackTransaction->donorNrn->networkId = $portingInfo['donorNetworkId'];
        $notifyOpenRequest->rollbackTransaction->donorNrn->routingNumber = $portingInfo['donorRoutingNumber'];

        $notifyOpenRequest->rollbackTransaction->recipientNrn = new nrnType();
        $notifyOpenRequest->rollbackTransaction->recipientNrn->networkId = $portingInfo['recipientNetworkId'];
        $notifyOpenRequest->rollbackTransaction->recipientNrn->routingNumber = $portingInfo['recipientRoutingNumber'];

        $notifyOpenRequest->rollbackTransaction->rollbackId = date('Ymd') . '-'. $portingInfo['donorNetworkId'] .'-' .
            $portingInfo['startMSISDN'] . '-' . ($rand + 1);

        $notifyOpenRequest->rollbackTransaction->rollbackState = 'OPENED';
        $notifyOpenRequest->rollbackTransaction->originalPortingId = $openRequest->originalPortingId;

        // numberRange
        $numRange = new numberRangeType();
        $numRange->endNumber = $portingInfo['startMSISDN'];
        $numRange->startNumber = $portingInfo['endMSISDN'];
        $notifyOpenRequest->rollbackTransaction->numberRanges = array($numRange);

        $this->client->notifyOpened($notifyOpenRequest);

        return $response;

        //throw new invalidRequestFormatFault();
    }

    /**
     * @param $acceptRequest
     * @return rollback\acceptResponse
     * @throws invalidOperatorFault
     * @throws rollbackActionNotAvailableFault
     * @throws invalidRollbackIdFault
     * @throws invalidRequestFormatFault
     */
    public function accept($acceptRequest){

        $response = new rollback\acceptResponse();

        $response->rollbackTransaction = new rollback\rollbackTransactionType();
        $response->rollbackTransaction->lastChangeDateTime = date('c');
        $response->rollbackTransaction->donorSubmissionDateTime = date('c');
        $response->rollbackTransaction->rollbackDateTime = date('c');
        $response->rollbackTransaction->rollbackId = $acceptRequest->rollbackId;

        $portingInfo = $this->Rollback_model->get_full_rollback($acceptRequest->rollbackId);
        
        $notifyAcceptedRequest = new \RollbackService\RollbackNotification\notifyAcceptedRequest();
        $notifyAcceptedRequest->rollbackTransaction = new rollback\rollbackTransactionType();

        $notifyAcceptedRequest->rollbackTransaction = new rollback\rollbackTransactionType();
        $notifyAcceptedRequest->rollbackTransaction->cadbOpenDateTime = date('c');
        $notifyAcceptedRequest->rollbackTransaction->donorSubmissionDateTime = date('c');
        $notifyAcceptedRequest->rollbackTransaction->lastChangeDateTime = date('c');
        $notifyAcceptedRequest->rollbackTransaction->rollbackDateTime = $portingInfo['rollbackDateTime'];

        $notifyAcceptedRequest->rollbackTransaction->donorNrn = new nrnType();
        $notifyAcceptedRequest->rollbackTransaction->donorNrn->networkId = $portingInfo['donorNetworkId'];
        $notifyAcceptedRequest->rollbackTransaction->donorNrn->routingNumber = $portingInfo['donorRoutingNumber'];

        $notifyAcceptedRequest->rollbackTransaction->recipientNrn = new nrnType();
        $notifyAcceptedRequest->rollbackTransaction->recipientNrn->networkId = $portingInfo['recipientNetworkId'];
        $notifyAcceptedRequest->rollbackTransaction->recipientNrn->routingNumber = $portingInfo['recipientRoutingNumber'];

        $parts = explode('-', $portingInfo['rollbackId']);
        $parts[3] += 1;
        $notifyAcceptedRequest->rollbackTransaction->rollbackId = implode('-', $parts);

        $notifyAcceptedRequest->rollbackTransaction->rollbackState = 'ACCEPTED';

        // numberRange
        $numRange = new numberRangeType();
        $numRange->endNumber = $portingInfo['startMSISDN'];
        $numRange->startNumber = $portingInfo['endMSISDN'];
        $notifyAcceptedRequest->rollbackTransaction->numberRanges = array($numRange);

        $this->client->notifyAccepted($notifyAcceptedRequest);
        
        return $response;

        //throw new invalidOperatorFault();

    }

    /**
     * @param $rejectRequest
     * @return rollback\rejectResponse
     * @throws invalidOperatorFault
     * @throws rollbackActionNotAvailableFault
     * @throws invalidRollbackIdFault
     * @throws invalidRequestFormatFault
     * @throws causeMissingFault
     */
    public function reject($rejectRequest){

        $response = new rollback\rejectResponse();

        $response->rollbackTransaction = new rollback\rollbackTransactionType();
        $response->rollbackTransaction->lastChangeDateTime = date('c');
        $response->rollbackTransaction->donorSubmissionDateTime = date('c');
        $response->rollbackTransaction->rollbackDateTime = date('c');
        $response->rollbackTransaction->rollbackId = $rejectRequest->rollbackId;

        $portingInfo = $this->Rollback_model->get_full_rollback($rejectRequest->rollbackId);

        $notifyRejectedRequest = new \RollbackService\RollbackNotification\notifyRejectedRequest();
        $notifyRejectedRequest->rollbackTransaction = new rollback\rollbackTransactionType();

        $notifyRejectedRequest->cause = $rejectRequest->cause;
        $notifyRejectedRequest->rejectionReason = $rejectRequest->rejectionReason;

        $notifyRejectedRequest->rollbackTransaction = new rollback\rollbackTransactionType();
        $notifyRejectedRequest->rollbackTransaction->cadbOpenDateTime = date('c');
        $notifyRejectedRequest->rollbackTransaction->donorSubmissionDateTime = date('c');
        $notifyRejectedRequest->rollbackTransaction->lastChangeDateTime = date('c');
        $notifyRejectedRequest->rollbackTransaction->rollbackDateTime = $portingInfo['rollbackDateTime'];

        $notifyRejectedRequest->rollbackTransaction->donorNrn = new nrnType();
        $notifyRejectedRequest->rollbackTransaction->donorNrn->networkId = $portingInfo['donorNetworkId'];
        $notifyRejectedRequest->rollbackTransaction->donorNrn->routingNumber = $portingInfo['donorRoutingNumber'];

        $notifyRejectedRequest->rollbackTransaction->recipientNrn = new nrnType();
        $notifyRejectedRequest->rollbackTransaction->recipientNrn->networkId = $portingInfo['recipientNetworkId'];
        $notifyRejectedRequest->rollbackTransaction->recipientNrn->routingNumber = $portingInfo['recipientRoutingNumber'];

        $parts = explode('-', $portingInfo['rollbackId']);
        $parts[3] += 1;
        $notifyRejectedRequest->rollbackTransaction->rollbackId = implode('-', $parts);

        $notifyRejectedRequest->rollbackTransaction->rollbackState = 'REJECTED';

        // numberRange
        $numRange = new numberRangeType();
        $numRange->endNumber = $portingInfo['startMSISDN'];
        $numRange->startNumber = $portingInfo['endMSISDN'];
        $notifyRejectedRequest->rollbackTransaction->numberRanges = array($numRange);

        $this->client->notifyRejected($notifyRejectedRequest);

        return $response;
        //throw new invalidOperatorFault();

    }

    /**
     * @param $confirmRequest
     * @return rollback\confirmResponse
     * @throws invalidOperatorFault
     * @throws rollbackActionNotAvailableFault
     * @throws invalidRollbackIdFault
     * @throws invalidRequestFormatFault
     */
    public function confirm($confirmRequest) {

        $response = new rollback\confirmResponse();

        return $response;

    }

    /**
     * @param $getRollbackRequest
     * @return rollback\getRollbackResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRollbackIdFault
     */
    public function getRollback($getRollbackRequest) {

        $response = new rollback\getRollbackResponse();

        $rollbackInfo = $this->Rollback_model->get_rollback($getRollbackRequest->rollbackId);

        $response->rollbackTransaction = new rollback\rollbackTransactionType();
        $response->rollbackTransaction->lastChangeDateTime = date('c');
        $response->rollbackTransaction->donorSubmissionDateTime = $rollbackInfo['donorSubmissionDateTime'];
        $response->rollbackTransaction->rollbackDateTime = $rollbackInfo['rollbackDateTime'];
        $response->rollbackTransaction->originalPortingId = $rollbackInfo['originalPortingId'];
        $response->rollbackTransaction->rollbackId = $getRollbackRequest->rollbackId;

        return $response;

    }

    /**
     * @param $getOpenedRollbacksRequest
     * @return rollback\getOpenedRollbacksResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     */
    public function getOpenedRollbacks($getOpenedRollbacksRequest){

        $response = new rollback\getOpenedRollbacksResponse();

        return $response;

    }

    /**
     * @param $getAcceptedRollbacksRequest
     * @return rollback\getAcceptedRollbacksResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     */
    public function getAcceptedRollbacks($getAcceptedRollbacksRequest){

        $response = new rollback\getAcceptedRollbacksResponse();

        return $response;

    }

    /**
     * @param $getConfirmedRollbacksRequest
     * @return rollback\getConfirmedRollbacksResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     */
    public function getConfirmedRollbacks($getConfirmedRollbacksRequest){

        $response = new rollback\getConfirmedRollbacksResponse();

        return $response;

    }

    /**
     * @param $getRejectedRollbacksRequest
     * @return rollback\getRejectedRollbacksResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     * @throws countOverMaxCountLimitFault
     */
    public function getRejectedRollbacks($getRejectedRollbacksRequest){

        $response = new rollback\getRejectedRollbacksResponse();

        return $response;

    }

}