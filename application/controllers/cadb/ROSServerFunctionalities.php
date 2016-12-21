<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Rollback.php";
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

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/RollbackOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ROSServerFunctionalities");

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

        $response->rollbackTransaction = new rollback\rollbackTransactionType();
        $response->rollbackTransaction->lastChangeDateTime = date('c');
        $response->rollbackTransaction->donorSubmissionDateTime = date('c');
        $response->rollbackTransaction->rollbackDateTime = date('c');
        $response->rollbackTransaction->rollbackId = '20161208-03-237694975166-345';
        $response->rollbackTransaction->originalPortingId = '20161208-01-237694975166-345';

        return $response;

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
        $response->rollbackTransaction->rollbackId = '20161208-03-237694975166-345';
        $response->rollbackTransaction->originalPortingId = '20161208-01-237694975166-345';


        return $response;

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

        return $response;

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