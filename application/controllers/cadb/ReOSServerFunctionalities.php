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

        // Set the class for the soap server
        $server->setClass("ReOSServerFunctionalities");

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

        return $response;

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

        return $response;

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