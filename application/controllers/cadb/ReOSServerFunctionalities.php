<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Return.php";
require_once "ReturnNotificationService.php";
require_once "Fault.php";

use ReturnService\_Return as _Return;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/9/2016
 * Time: 3:30 PM
 */

class ReOSServerFunctionalities extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Numberreturn_model');

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ReturnNotificationService.wsdl', array(
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
        $server = new SoapServer(__DIR__ . '/wsdl/ReturnOperationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $openRequest
     * @return _Return\openResponse
     * @throws numberRangesOverlapFault
     * @throws numberNotOwnedByOperatorFault
     * @throws unknownManagedNumberFault
     * @throws numberQuantityLimitExceededFault
     * @throws numberRangeQuantityLimitExceededFault
     * @throws invalidOperatorFault
     * @throws numberNotPortedFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     * @throws multiplePrimaryOwnersFault
     * @throws unknownNumberFault
     * @throws numberReservedByProcessFault
     */
    public function open($openRequest){

        $response = new _Return\openResponse();

        $response->returnTransaction = new _Return\returnTransactionType();

        $rand = mt_rand(100,998);

        $numRange = new numberRangeType();
        $numRange->endNumber = $openRequest->numberRanges->numberRange->startNumber;
        $numRange->startNumber = $openRequest->numberRanges->numberRange->endNumber;

        $response->returnTransaction->ownerNrn = $openRequest->ownerNrn;
        $response->returnTransaction->primaryOwnerNrn = $openRequest->primaryOwnerNrn;
        $response->returnTransaction->openDateTime = date('c');
        $response->returnTransaction->numberRanges = array($numRange);
        $response->returnTransaction->returnId = date('Ymd') . '-'. $openRequest->primaryOwnerNrn->networkId .'-' . $openRequest->numberRanges->numberRange->startNumber . '-' . $rand;

        // Notify Return to CO
        $notifyRequest = new \ReturnService\_ReturnNotification\notifyOpenedRequest();
        $notifyRequest->returnTransaction = new _Return\returnTransactionType();
        $notifyRequest->returnTransaction->primaryOwnerNrn = $openRequest->ownerNrn;
        $notifyRequest->returnTransaction->openDateTime = date('c');

        $notifyRequest->returnTransaction->numberRanges = array($numRange);
        $notifyRequest->returnTransaction->ownerNrn = $openRequest->primaryOwnerNrn;
        $notifyRequest->returnTransaction->returnId = date('Ymd') . '-'. $openRequest->primaryOwnerNrn->networkId .'-' . $openRequest->numberRanges->numberRange->startNumber . '-' . ($rand+1);

        $this->client->notifyOpened($notifyRequest);

        return $response;

        //throw new invalidOperatorFault();

    }

    /**
     * @param $acceptRequest
     * @return _Return\acceptResponse
     * @throws returnActionNotAvailableFault
     * @throws invalidReturnIdFault
     * @throws invalidRequestFormatFault
     */
    public function accept($acceptRequest){

        $response = new _Return\acceptResponse();

        $response->returnTransaction = new _Return\returnTransactionType();

        $response->returnTransaction->returnId = $acceptRequest->returnId;

        $return = $this->Numberreturn_model->get_numberreturn($acceptRequest->returnId);

        $notifyAcceptRequest = new \ReturnService\_ReturnNotification\notifyAcceptedRequest();
        $notifyAcceptRequest->returnTransaction = new _Return\returnTransactionType();

        $parts = explode('-', $acceptRequest->returnId);
        $parts[3] += 1;
        $notifyAcceptRequest->returnTransaction->returnId = implode('-', $parts);

        $notifyAcceptRequest->returnTransaction->openDateTime = $return['openDateTime'];
        $notifyAcceptRequest->returnTransaction->returnNumberState = $return['returnNumberState'];

        $numRange = new numberRangeType();
        $numRange->endNumber = $return['returnMSISDN'];
        $numRange->startNumber = $return['returnMSISDN'];

        $notifyAcceptRequest->returnTransaction->ownerNrn = new nrnType();
        $notifyAcceptRequest->returnTransaction->ownerNrn->networkId = $return['ownerNetworkId'];
        $notifyAcceptRequest->returnTransaction->ownerNrn->routingNumber = $return['ownerRoutingNumber'];

        $notifyAcceptRequest->returnTransaction->primaryOwnerNrn = new nrnType();
        $notifyAcceptRequest->returnTransaction->primaryOwnerNrn->networkId = $return['primaryOwnerNetworkId'];
        $notifyAcceptRequest->returnTransaction->primaryOwnerNrn->routingNumber = $return['primaryOwnerRoutingNumber'];

        $notifyAcceptRequest->returnTransaction->numberRanges = array($numRange);

        $this->client->notifyAccepted($notifyAcceptRequest);

        return $response;

        //throw new returnActionNotAvailableFault();

    }

    /**
     * @param $rejectRequest
     * @return _Return\rejectResponse
     * @throws returnActionNotAvailableFault
     * @throws invalidReturnIdFault
     * @throws invalidRequestFormatFault
     * @throws causeMissingFault
     */
    public function reject($rejectRequest){

        $response = new _Return\rejectResponse();

        $response->returnTransaction = new _Return\returnTransactionType();

        $return = $this->Numberreturn_model->get_numberreturn($rejectRequest->returnId);

        $response->returnTransaction->returnId = $rejectRequest->returnId;

        $notifyRejectedRequest = new \ReturnService\_ReturnNotification\notifyRejectedRequest();
        $notifyRejectedRequest->returnTransaction = new _Return\returnTransactionType();

        $notifyRejectedRequest->cause = $rejectRequest->cause;

        $parts = explode('-', $rejectRequest->returnId);
        $parts[3] += 1;
        $notifyRejectedRequest->returnTransaction->returnId = implode('-', $parts);

        $notifyRejectedRequest->returnTransaction->openDateTime = $return['openDateTime'];
        $notifyRejectedRequest->returnTransaction->returnNumberState = $return['returnNumberState'];

        $numRange = new numberRangeType();
        $numRange->endNumber = $return['returnMSISDN'];
        $numRange->startNumber = $return['returnMSISDN'];

        $notifyRejectedRequest->returnTransaction->ownerNrn = new nrnType();
        $notifyRejectedRequest->returnTransaction->ownerNrn->networkId = $return['ownerNetworkId'];
        $notifyRejectedRequest->returnTransaction->ownerNrn->routingNumber = $return['ownerRoutingNumber'];

        $notifyRejectedRequest->returnTransaction->primaryOwnerNrn = new nrnType();
        $notifyRejectedRequest->returnTransaction->primaryOwnerNrn->networkId = $return['primaryOwnerNetworkId'];
        $notifyRejectedRequest->returnTransaction->primaryOwnerNrn->routingNumber = $return['primaryOwnerRoutingNumber'];

        $notifyRejectedRequest->returnTransaction->numberRanges = array($numRange);

        $this->client->notifyRejected($notifyRejectedRequest);

        return $response;

    }

    /**
     * @param $getReturningTransactionRequest
     * @return _Return\getReturningTransactionResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     * @throws invalidReturnIdFault
     */
    public function getReturningTransaction($getReturningTransactionRequest){

        $response = new _Return\getReturningTransactionResponse();

        $return = $this->Numberreturn_model->get_numberreturn($getReturningTransactionRequest->returnId);

        $response->returnTransaction = new _Return\returnTransactionType();

        $response->returnTransaction->returnId = $getReturningTransactionRequest->returnId;
        $response->returnTransaction->openDateTime = $return['openDateTime'];
        $response->returnTransaction->returnNumberState = $return['returnNumberState'];

        $numRange = new numberRangeType();
        $numRange->endNumber = $return['returnMSISDN'];
        $numRange->startNumber = $return['returnMSISDN'];

        $response->returnTransaction->ownerNrn = new nrnType();
        $response->returnTransaction->ownerNrn->networkId = $return['ownerNetworkId'];
        $response->returnTransaction->ownerNrn->routingNumber = $return['ownerRoutingNumber'];

        $response->returnTransaction->primaryOwnerNrn = new nrnType();
        $response->returnTransaction->primaryOwnerNrn->networkId = $return['primaryOwnerNetworkId'];
        $response->returnTransaction->primaryOwnerNrn->routingNumber = $return['primaryOwnerRoutingNumber'];

        $response->returnTransaction->numberRanges = array($numRange);

        return $response;

    }

    /**
     * @param $getCurrentReturningTransactionsRequest
     * @return _Return\getCurrentReturningTransactionsResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     */
    public function getCurrentReturningTransactions($getCurrentReturningTransactionsRequest){

        $response = new _Return\getCurrentReturningTransactionsResponse();

        return $response;

    }

}