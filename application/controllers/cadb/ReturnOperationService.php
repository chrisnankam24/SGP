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
class ReturnOperationService {

    // Declare client
    private $client = null;

    private $db = null;
    private $Numberreturn_model = null;
    private $FileLog_model = null;
    private $Returnrejection_model = null;
    private $Numberreturnsubmission_model = null;
    private $Numberreturnstateevolution_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $this->db = $CI->db;

        $CI->load->model('FileLog_model');
        $CI->load->model('Numberreturn_model');
        $CI->load->model('Returnrejection_model');
        $CI->load->model('Numberreturnsubmission_model');
        $CI->load->model('Numberreturnstateevolution_model');

        $this->FileLog_model = $CI->FileLog_model;
        $this->Numberreturn_model = $CI->Numberreturn_model;
        $this->Returnrejection_model = $CI->Returnrejection_model;
        $this->Numberreturnsubmission_model = $CI->Numberreturnsubmission_model;
        $this->Numberreturnstateevolution_model = $CI->Numberreturnstateevolution_model;

        // Disable wsdl_1_4 cache
        ini_set("soap.wsdl_cache_enabled", "0");

        libxml_disable_entity_loader(false);

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ReturnOperationService.wsdl', array(
            "trace" => false,
            'stream_context' => stream_context_create(array(
                'http' => array(
                    'header' => 'Authorization: Bearer ' . Auth::CADB_AUTH_BEARER
                ),
            )),
        ));

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

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
                $response->message = $e->detail->$fault->message;
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
                $response->message = $e->detail->$fault->message;
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
                $response->message = $e->detail->$fault->message;
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
                $response->message = $e->detail->$fault->message;
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
                $response->message = $e->detail->$fault->message;
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

                    $this->fileLogAction($error['code'], 'ReturnOperationService', "Number Return [$returnId] opening failed");

                    $this->fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_OPENED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                }

                $this->db->trans_complete();

                logAction($userId, "Number Return [$returnId] opening Successfully");

                $this->fileLogAction('8010', 'ReturnOperationService', "Number Return [$returnId] opening Successfully");

                $response['message'] = 'Return has been OPENED successfully!';

            }

            else{

                $fault = $openResponse->error;

                $emailService = new EmailService();

                $this->fileLogAction('8010', 'ReturnOperationService', "Number Return OPENING failed with $fault");

                $response['success'] = false;

                switch ($fault) {
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

                    case Fault::INVALID_OPERATOR_FAULT:
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

                logAction($userId, "Number Return OPEN Failed with [$fault] Fault");



            }

        }else{

            $response['success'] = false;

            $response['message'] = "Invalid return MSISDN. Can't return Orange CM attributed number";

        }

        return $response;

    }

    /**
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

                        $this->fileLogAction($error['code'], 'ReturnOperationService', "Number Return [$returnId] ACCEPT failed " . $error['message']);

                        $this->fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService = new EmailService();

                        $emailService->adminErrorReport('RETURN_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Number Return [$returnId] acceptance Successfully");

                    $this->fileLogAction('8010', 'ReturnOperationService', "Number Return [$returnId] ACCEPT Successfully");

                    $response['message'] = 'Return has been ACCEPTED successfully!';

                }

                else{

                    $fault = $acceptResponse->error;

                    $this->fileLogAction('8010', 'ReturnOperationService', "Number Return [$returnId] ACCEPT failed with $fault");

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

                        $this->fileLogAction($error['code'], 'ReturnOperationService', "Number Return [$returnId] ACCEPT failed");

                        $this->fileLogAction($error['code'], 'ReturnOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Number Return [$returnId] rejection Successfully");

                    $this->fileLogAction('8010', 'ReturnOperationService', "Number Return [$returnId] REJECT successful");

                    $response['message'] = 'Return has been REJECTED successfully!';

                }

                else{

                    $fault = $rejectResponse->error;

                    $this->fileLogAction('8010', 'ReturnOperationService', "Number Return [$returnId] REJECT failed with $fault");

                    $emailService = new EmailService();

                    $response['success'] = false;

                    switch ($fault) {
                        // Terminal Error Processes
                        case Fault::RETURN_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_RETURN_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                        case Fault::CAUSE_MISSING:
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
     * Search number return with msisdn
     * @param $msisdn
     * @return array
     */
    public function searchReturn($msisdn, $userId){

        $response = [];

        $response['success'] = true;

        $response['data'] = $this->Numberreturn_model->search_numberreturn($msisdn);

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

        return $response;

    }

    /**
     * TODO: getCADBNumberReturns
     * API to retrieve all NRs from CADB
     */
    public function getCADBNumberReturns(){

        $response = [];

        $response['data'] = [];

        // Load ORDERED Rollbacks

        $currentNRResponse = $this->getCurrentReturningTransactions(Operator::ORANGE_NETWORK_ID);

        if($currentNRResponse->success){

            if(isset($currentNRResponse->returnNumberTransaction)){

                if(is_array($currentNRResponse->returnNumberTransaction)){

                    $response['data'] = array_merge($response['data'], $currentNRResponse->returnNumberTransaction);

                }else{

                    $response['data'][] =  $currentNRResponse->returnNumberTransaction;

                }

            }

        }
        else{

            $fault = $currentNRResponse->error;

            switch ($fault) {

                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_CURRENT_NRS_FROM_CADB", []);
            }

        }

        return $response;

    }

}