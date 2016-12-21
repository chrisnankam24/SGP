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
class POSServerFunctionalities extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/PortingOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("POSServerFunctionalities");

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $orderRequest
     * @return Porting\orderResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidPortingDateAndTimeFault
     * @throws invalidRequestFormatFault
     * @throws numberRangesOverlapFault
     * @throws numberReservedByProcessFault
     * @throws numberNotOwnedByOperatorFault
     * @throws unknownNumberFault
     * @throws numberRangeQuantityLimitExceededFault
     * @throws tooNearPortedPeriodFault
     * @throws portingNotAllowedRequestsFault
     * @throws subscriberDataMissingFault
     * @throws rioNotValidFault
     */
    public function order($orderRequest){

        $response = new Porting\orderResponse();

        $response->portingTransaction = new Porting\portingTransactionType();

        //$orderRequest = new Porting\orderRequest();

        $response->portingTransaction->lastChangeDateTime = date('c');
        $response->portingTransaction->cadbOrderDateTime = date('c');
        $response->portingTransaction->donorNrn = $orderRequest->donorNrn;
        $response->portingTransaction->numberRanges = $orderRequest->numberRanges;
        $response->portingTransaction->portingDateTime = $orderRequest->portingDateTime;
        $response->portingTransaction->portingId = '20161208-02-237694975166-345';
        $response->portingTransaction->portingState = Porting\portingStateType::ORDERED;
        $response->portingTransaction->recipientNrn = $orderRequest->recipientNrn;
        $response->portingTransaction->recipientSubmissionDateTime = date('c');
        $response->portingTransaction->rio = $orderRequest->rio;
        $response->portingTransaction->subscriberInfo = $orderRequest->subscriberInfo;

        return $response;

    }

    /**
     * @param $approveRequest
     * @return Porting\approveResponse
     * @throws invalidOperatorFault
     * @throws portingActionNotAvailableFault
     * @throws invalidPortingIdFault
     * @throws invalidRequestFormatFault
     */
    public function approve($approveRequest){

        $response = new Porting\approveResponse();

        return $response;

    }

    /**
     * @param $acceptRequest
     * @return Porting\acceptResponse
     * @throws invalidOperatorFault
     * @throws portingActionNotAvailableFault
     * @throws invalidPortingIdFault
     * @throws invalidRequestFormatFault
     */
    public function accept($acceptRequest){

        $response = new Porting\acceptResponse();

        $response->portingTransaction = new Porting\portingTransactionType();

        //$orderRequest = new Porting\orderRequest();

        $response->portingTransaction->lastChangeDateTime = date('c');
        $response->portingTransaction->cadbOrderDateTime = date('c');
        $response->portingTransaction->portingDateTime = date('c');
        $response->portingTransaction->portingId = '20161208-02-237694975166-345';
        $response->portingTransaction->portingState = Porting\portingStateType::ACCEPTED;

        return $response;

    }

    /**
     * @param $confirmRequest
     * @return Porting\confirmResponse
     * @throws invalidOperatorFault
     * @throws portingActionNotAvailableFault
     * @throws invalidPortingIdFault
     * @throws invalidPortingDateAndTimeFault
     * @throws invalidRequestFormatFault
     */
    public function confirm($confirmRequest){

        $response = new Porting\confirmResponse();

        return $response;

    }

    /**
     * @param $rejectRequest
     * @return Porting\rejectResponse
     * @throws portingActionNotAvailableFault
     * @throws invalidPortingIdFault
     * @throws invalidRequestFormatFault
     * @throws causeMissingFault
     */
    public function reject($rejectRequest){

        $response = new Porting\rejectResponse();

        return $response;
    }

    /**
     * @param $denyRequest
     * @return Porting\denyResponse
     * @throws invalidOperatorFault
     * @throws portingActionNotAvailableFault
     * @throws invalidPortingIdFault
     * @throws invalidRequestFormatFault
     * @throws causeMissingFault
     */
    public function deny($denyRequest){

        $response = new Porting\denyResponse();

        return $response;
    }

    /**
     * @param $getPortingRequest
     * @return Porting\getPortingResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidPortingIdFault
     * @throws invalidRequestFormatFault
     */
    public function getPorting($getPortingRequest){

        $response = new Porting\getPortingResponse();

        return $response;
    }

    /**
     * @param $getOrderedPortingsRequest
     * @return Porting\getOrderedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     */
    public function getOrderedPortings($getOrderedPortingsRequest){

        $response = new Porting\getOrderedPortingsResponse();

        return $response;

    }

    /**
     * @param $getApprovedPortingsRequest
     * @return Porting\getApprovedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     */
    public function getApprovedPortings($getApprovedPortingsRequest){

        $response = new Porting\getApprovedPortingsResponse();

        return $response;

    }

    /**
     * @param $getAcceptedPortingsRequest
     * @return Porting\getAcceptedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     */
    public function getAcceptedPortings($getAcceptedPortingsRequest){

        $response = new Porting\getAcceptedPortingsResponse();

        return $response;

    }

    /**
     * @param $getConfirmedPortingsRequest
     * @return Porting\getConfirmedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     */
    public function getConfirmedPortings($getConfirmedPortingsRequest){

        $response = new Porting\getConfirmedPortingsResponse();

        return $response;

    }

    /**
     * @param $getDeniedPortingsRequest
     * @return Porting\getDeniedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     * @throws countOverMaxCountLimitFault
     */
    public function getDeniedPortings($getDeniedPortingsRequest){

        $response = new Porting\getDeniedPortingsResponse();

        return $response;

    }

    /**
     * @param $getRejectedPortingsRequest
     * @return Porting\getRejectedPortingsResponse
     * @throws invalidOperatorFault
     * @throws actionNotAuthorizedFault
     * @throws invalidRequestFormatFault
     * @throws countOverMaxCountLimitFault
     */
    public function getRejectedPortings($getRejectedPortingsRequest){

        $response = new Porting\getRejectedPortingsResponse();

        return $response;

    }

}