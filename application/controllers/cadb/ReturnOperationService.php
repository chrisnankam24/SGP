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
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";

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

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ReturnOperationService.wsdl', array(
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

    /**
     * TODO: OK
     * Open Return for given number
     * @param $returnMSISDN
     * @param $returnOperator
     * @return array
     */
    public function openReturn($returnMSISDN, $returnOperator, $userId){

        $response = [];

        // Check if number not Orange #
        if(!isOCMNumber($returnMSISDN)){
            // Make Open NR Operation

            $openResponse = $this->open($returnOperator, $returnMSISDN);

            // Verify response

            if($openResponse->success){

                $this->db->trans_start();

                // Insert into NR submission table with state OPENED

                $nrsParams = array(
                    'primaryOwnerNetworkId' => $openResponse->returnTransaction->primaryOwnerNrn->networkId,
                    'primaryOwnerNetworkNumber' => $openResponse->returnTransaction->primaryOwnerNrn->routingNumber,
                    'returnMSISDN' => $returnMSISDN,
                    'submissionState' => \ReturnService\_Return\returnSubmissionStateType::OPENED,
                    'submissionDateTime' => date('c'),
                    'userId' => $userId
                );

                $submissionId = $this->Numberreturnsubmission_model->add_numberreturnsubmission($nrsParams);
                $returnId = $openResponse->returnTransaction->returnId;

                // Insert into NR table

                $nrParams = array(
                    'returnId' => $openResponse->returnTransaction->returnId,
                    'openDateTime' => $openResponse->returnTransaction->openDateTime,
                    'ownerNetworkId' => $openResponse->returnTransaction->ownerNrn->networkId,
                    'ownerRoutingNumber' => $openResponse->returnTransaction->ownerNrn->routingNumber,
                    'primaryOwnerNetworkId' => $openResponse->returnTransaction->primaryOwnerNrn->networkId,
                    'primaryOwnerRoutingNumber' => $openResponse->returnTransaction->primaryOwnerNrn->routingNumber,
                    'returnMSISDN' => $returnMSISDN,
                    'returnNumberState' => \ReturnService\_Return\returnSubmissionStateType::OPENED,
                    'returnNotificationMailSendStatus' => smsState::CLOSED,
                    'returnNotificationMailSendDateTime' => date('c'),
                    'numberReturnSubmissionId' => $submissionId,
                );

                $this->Numberreturn_model->add_numberreturn($nrParams);

                // Insert into NR state Evolution table

                $nrsParams = array(
                    'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
                    'lastChangeDateTime' => date('c'),
                    'returnId' => $openResponse->returnTransaction->returnId,
                );

                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_OPENED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                }

                $this->db->trans_complete();

                logAction($userId, "Number Return [$returnId] opening Successfully");

                $response['message'] = 'Return has been OPENED successfully!';

            }

            else{

                $fault = $openResponse->error;

                $emailService = new EmailService();

                $response['success'] = false;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $this->db->trans_start();

                        // Insert into Return submission table with state STARTED

                        if($returnOperator == 0) {
                            // MTN
                            $primaryOwnerNetworkId = Operator::MTN_NETWORK_ID;
                            $primaryOwnerNetworkNumber = Operator::MTN_ROUTING_NUMBER;
                        }else{
                            // Orange
                            $primaryOwnerNetworkId = Operator::NEXTTEL_NETWORK_ID;
                            $primaryOwnerNetworkNumber = Operator::NEXTTEL_ROUTING_NUMBER;
                        }

                        $nrsParams = array(
                            'primaryOwnerNetworkId' => $primaryOwnerNetworkId,
                            'primaryOwnerNetworkNumber' => $primaryOwnerNetworkNumber,
                            'returnMSISDN' => $returnMSISDN,
                            'submissionState' => \ReturnService\_Return\returnSubmissionStateType::STARTED,
                            'submissionDateTime' => date('c'),
                            'userId' => $userId
                        );

                        $this->Numberreturnsubmission_model->add_numberreturnsubmission($nrsParams);

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();
                            fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                            $nrParams = array(
                                'ownerNetworkId' => Operator::ORANGE_NETWORK_ID,
                                'returnMSISDN' => $returnMSISDN,
                                'returnId' => '',
                                'returnNumberState' => 'N/A'
                            );

                            $emailService->adminErrorReport('RETURN_REQUESTED_OPERATOR_INACTIVE_BUT_STARTED_INCOMPLETE', $nrParams, processType::_RETURN);
                            $response['message'] = 'Operator is currently Inactive. We have nonetheless encountered problems saving your request. Please contact Back Office';

                        }else{

                            $response['message'] = 'Operator is currently Inactive. You request has been saved and will be performed as soon as possible';

                        }

                        $this->db->trans_complete();

                        break;

                    // Terminal Error Processes
                    case Fault::NUMBER_RESERVED_BY_PROCESS:
                        $response['message'] = 'Number already in transaction';
                        break;
                    case Fault::NUMBER_NOT_OWNED_BY_OPERATOR:
                        $response['message'] = 'Number does not match Donors numeration plan';
                        break;
                    case Fault::UNKNOWN_MANAGED_NUMBER:
                        $response['message'] = 'Number is not managed by CADB';
                        break;
                    case Fault::NUMBER_NOT_PORTED:
                        $response['message'] = 'Number is not ported in the first place';
                        break;
                    case Fault::MULTIPLE_PRIMARY_OWNER:
                        $response['message'] = 'Primary Owner cannot be resolved';
                        break;

                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    case Fault::NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::NUMBER_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::NUMBER_RANGES_OVERLAP:
                    default:
                        $nrParams = array(
                            'ownerNetworkId' => Operator::ORANGE_NETWORK_ID,
                            'returnMSISDN' => $returnMSISDN,
                            'returnId' => '',
                            'returnNumberState' => 'N/A'
                        );
                        $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                }

                logAction($userId, "Number Return Open Failed with [$fault] Fault");

            }

        }else{

            $response['success'] = false;

            $response['message'] = "Invalid return MSISDN. Can't return Orange CM attributed number";

        }

        return $response;

    }

    /**
     * TODO: OK
     * Accept Return for given number
     * @param $returnId
     * @return array
     */
    public function acceptReturn($returnId, $userId){

        $response = [];

        // Verify if return currently in OPENED state in DB
        $dbPort = $this->Numberreturn_model->get_numberreturn($returnId);

        if($dbPort){

            if($dbPort['returnNumberState'] == _Return\returnStateType::OPENED){

                // Make accept NR Operation

                $acceptResponse = $this->accept($returnId);

                // Verify response

                if($acceptResponse->success){

                    $this->db->trans_start();

                    // Update NR table

                    $nrParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::ACCEPTED,
                    );

                    $this->Numberreturn_model->update_numberreturn($returnId, $nrParams);

                    // Insert into NR state Evolution table

                    $nrsParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::ACCEPTED,
                        'lastChangeDateTime' => date('c'),
                        'returnId' => $acceptResponse->returnTransaction->returnId,
                    );

                    $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('RETURN_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Number Return [$returnId] acceptance Successfully");

                    $response['message'] = 'Return has been ACCEPTED successfully!';

                }

                else{

                    $fault = $acceptResponse->error;

                    $emailService = new EmailService();

                    $response['success'] = false;

                    switch ($fault) {
                        // Terminal Error Processes
                        case Fault::RETURN_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_RETURN_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                        default:

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);
                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';

                    }

                    logAction($userId, "Number Return Acceptance Failed with [$fault] Fault");

                }

            }else{

                $response['success'] = false;

                $response['message'] = 'Return now in ' . $dbPort['returnNumberState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }


        return $response;

    }

    /**
     * TODO: OK
     * Reject Return for given number
     * @param $returnId
     * @param $cause
     * @return array
     */
    public function rejectReturn($returnId, $cause, $userId){

        $response = [];

        // Verify if return currently in OPENED state in DB
        $dbPort = $this->Numberreturn_model->get_numberreturn($returnId);

        if($dbPort){

            if($dbPort['returnNumberState'] == _Return\returnStateType::OPENED){

                // Make reject NR Operation

                $rejectResponse = $this->reject($returnId, $cause);

                // Verify response

                if($rejectResponse->success){

                    $this->db->trans_start();

                    // Update NR table

                    $nrParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::REJECTED,
                    );

                    $this->Numberreturn_model->update_numberreturn($returnId, $nrParams);

                    // Insert into NR state Evolution table

                    $nrsParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::REJECTED,
                        'lastChangeDateTime' => date('c'),
                        'returnId' => $rejectResponse->returnTransaction->returnId,
                    );

                    $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                    // Insert into Return rejection table

                    $rrParams = array(
                        'cause' => $cause,
                        'returnId' => $returnId,
                    );

                    $this->Returnrejection_model->add_returnrejection($rrParams);

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Number Return [$returnId] rejection Successfully");

                    $response['message'] = 'Return has been REJECTED successfully!';

                }

                else{

                    $fault = $rejectResponse->error;

                    $emailService = new EmailService();

                    $response['success'] = false;

                    switch ($fault) {
                        // Terminal Error Processes
                        case Fault::RETURN_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_RETURN_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                        case Fault::UNKNOWN_NUMBER:
                        default:

                            $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                            $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);

                            $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    }

                    logAction($userId, "Number Return Rejection Failed with [$fault] Fault");

                }

            }else{

                $response['success'] = false;

                $response['message'] = 'Return now in ' . $dbPort['returnNumberState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }

        return $response;

    }

    /**
     * API to retrieve detail on NR
     */
    public function getCADBNReturn($returnId){
        $response = [];

        $getResponse = $this->getReturningTransaction($returnId);

        // Verify response

        if($getResponse->success){

            $response['success'] = true;

            $tmpData = $getResponse->returnTransaction;

            $data = array();

            $data['returnId'] = $tmpData->returnId;
            $data['openDateTime'] = $tmpData->openDateTime;
            $data['ownerNetworkId'] = $tmpData->ownerNrn->networkId;
            $data['ownerRoutingNumber'] = $tmpData->ownerNrn->routingNumber;
            $data['primaryOwnerNetworkId'] = $tmpData->primaryOwnerNrn->networkId;
            $data['primaryOwnerRoutingNumber'] = $tmpData->primaryOwnerNrn->routingNumber;
            $data['returnMSISDN'] = $tmpData->numberRanges->numberRange->startNumber;
            $data['returnNumberState'] = $tmpData->returnNumberState;

            $response['data'] = $tmpData;

        }

        else{

            $fault = $getResponse->error;

            $response['success'] = false;

            switch ($fault) {
                // Terminal Processes
                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::PORTING_ACTION_NOT_AVAILABLE:
                case Fault::INVALID_RETURN_ID:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    $response['message'] = 'Error from CADB';

            }


        }

        $this->send_response($response);
    }

    /**
     * TODO: getCADBNumberReturns
     * API to retrieve all NRs from CADB
     */
    private function getCADBNumberReturns(){

        $response = [];

        $response['data'] = [];

        // Load ORDERED Rollbacks

        $currentNRResponse = $this->getCurrentReturningTransactions(Operator::ORANGE_NETWORK_ID);

        if($currentNRResponse->success){

            $response['data'] = array_merge($response['data'], $currentNRResponse->returnNumberTransactions);

        }
        else{

            $fault = $currentNRResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_CURRENT_NRS_FROM_CADB", []);
            }

        }

        $this->send_response($response);
    }

    /**
     * @param $response
     */
    private function send_response($response)
    {
        header("Content-type: text/json");
        echo json_encode($response);
    }


}