<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Return.php";
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

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ReturnOperationService.wsdl');

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

        $openRequest = new _Return\openRequest();

        $response->returnTransaction->ownerNrn = $openRequest->ownerNrn;
        $response->returnTransaction->primaryOwnerNrn = $openRequest->primaryOwnerNrn;
        $response->returnTransaction->openDateTime = date('c');
        $response->returnTransaction->returnId = date('Ymd') . '-'. $openRequest->primaryOwnerNrn->networkId .'-' . $openRequest->numberRanges->numberRange->startNumber . '-' . mt_rand(100,999);


        return $response;

        //throw new invalidRequestFormatFault();

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

        return $response;

        //throw new returnActionNotAvailableFault();

    }

    /**
     * @param $rejectRequest
     * @return _Return\rejectResponse
     * @throws returnActionNotAvailableFault
     * @throws invalidReturnIdFault
     * @throws invalidRequestFormatFault
     * @throws unknownNumberFault
     */
    public function reject($rejectRequest){

        $response = new _Return\rejectResponse();

        $response->returnTransaction = new _Return\returnTransactionType();

        $response->returnTransaction->returnId = $rejectRequest->returnId;

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