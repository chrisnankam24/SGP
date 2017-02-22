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
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use RollbackService\Rollback as rollback;

class RollbackOperationService {

    // Declare client
    private $client = null;

    private $db = null;
    private $Porting_model = null;
    private $FileLog_model = null;
    private $Rollback_model = null;
    private $Rollbacksubmission_model = null;
    private $Rollbackstateevolution_model = null;
    private $Rollbackrejectionabandon_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $this->db = $CI->db;

        $CI->load->model('Porting_model');
        $CI->load->model('FileLog_model');
        $CI->load->model('Rollback_model');
        $CI->load->model('Rollbacksubmission_model');
        $CI->load->model('Rollbackstateevolution_model');
        $CI->load->model('Rollbackrejectionabandon_model');

        $this->Porting_model = $CI->Porting_model;
        $this->FileLog_model = $CI->FileLog_model;
        $this->Rollback_model = $CI->Rollback_model;
        $this->Rollbacksubmission_model = $CI->Rollbacksubmission_model;
        $this->Rollbackstateevolution_model = $CI->Rollbackstateevolution_model;
        $this->Rollbackrejectionabandon_model = $CI->Rollbackrejectionabandon_model;

        // Disable wsdl_1_4 cache
        ini_set("soap.wsdl_cache_enabled", "0");

        libxml_disable_entity_loader(false);

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/RollbackOperationService.wsdl', array(
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
     * @param $originalPortingId string porting id of original porting process
     * @param $donorSubmissionDateTime string of submission process
     * @param $rollbackDateTime string of preferred porting process
     * @return errorResponse
     */
    public function open($originalPortingId, $donorSubmissionDateTime, $rollbackDateTime) {

        if($this->client) {

            // Make open request
            $request = new rollback\openRequest();

            $request->originalPortingId = $originalPortingId;

            $request->donorSubmissionDateTime = $donorSubmissionDateTime;

            $request->rollbackDateTime = $rollbackDateTime;

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
     * @param $rollbackId string id of rollback process
     * @param $rollbackDateAndTime datetime for provisioning system
     * @return errorResponse
     */
    public function confirm($rollbackId, $rollbackDateAndTime) {

        if($this->client) {

            // Make confirm request
            $request = new rollback\confirmRequest();
            $request->rollbackId = $rollbackId;
            $request->rollbackDateTime = $rollbackDateAndTime;

            try {

                $response = $this->client->confirm($request);
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
     * @param $rollbackId
     * @return errorResponse
     */
    public function getRollback($rollbackId) {

        if($this->client) {

            // Make getRollback request
            $request = new rollback\getRollbackRequest();
            $request->rollbackId = $rollbackId;

            try {

                $response = $this->client->getRollback($request);
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
    public function getOpenedRollbacks($networkId) {

        if($this->client) {

            // Make getOpenedRollbacks request
            $request = new rollback\getOpenedRollbacksRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getOpenedRollbacks($request);
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
    public function getAcceptedRollbacks($networkId) {

        if($this->client) {

            // Make getAcceptedRollbacks request
            $request = new rollback\getAcceptedRollbacksRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getAcceptedRollbacks($request);
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
    public function getConfirmedRollbacks($networkId) {

        if($this->client) {

            // Make getConfirmedRollbacks request
            $request = new rollback\getConfirmedRollbacksRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getConfirmedRollbacks($request);
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
     * @param $count
     * @return errorResponse
     */
    public function getRejectedRollbacks($networkId, $count) {

        if($this->client) {

            // Make getRejectedRollbacks request
            $request = new rollback\getRejectedRollbacksRequest();
            $request->networkId = $networkId;
            $request->count = $count;

            try {

                $response = $this->client->getRejectedRollbacks($request);
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
     * Make rollback open for given portingId
     * @param $originalPortingId
     * @param $contractId
     * @param $temporalNumber
     * @param $donorSubmissionDateTime
     * @param $preferredRollbackDateTime
     */
    public function makeOpen($originalPortingId, $temporalNumber, $userId){

        $response = [];

        // Get subscriber contractId from BSCS with temporal MSISDN
        $bscsOperationService = new BscsOperationService();
        $contractId = $bscsOperationService->getContractId($temporalNumber);

        if($contractId == -1){

            $response['success'] = false;
            $response['message'] = 'Connection to BSCS Unsuccessful. Please try again later';

        }elseif($contractId == null){

            $response['success'] = false;
            $response['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';

        }else{

            $donorSubmissionDateTime = date('c');
            $rollbackDateTime = date('c', strtotime('+2 hours', strtotime(date('c'))));

            // Make Open Rollback Operation

            $openResponse = $this->open($originalPortingId, $donorSubmissionDateTime, $rollbackDateTime);

            // Verify response

            if($openResponse->success){

                $this->db->trans_start();

                // Insert into Rollback submission table

                $submissionParams = array(
                    'originalPortingId' => $originalPortingId,
                    'preferredRollbackDateTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                    'submissionState' => \RollbackService\Rollback\rollbackSubmissionStateType::OPENED,
                    'openedDateTime' => date('c'),
                    'contractId' => $contractId,
                    'temporalMSISDN' => $temporalNumber,
                    'userId' => $userId
                );

                $rollbacksubmission_id = $this->Rollbacksubmission_model->add_rollbacksubmission($submissionParams);
                $rollbackId = $openResponse->rollbackTransaction->rollbackId;
                $originalPortingId = $openResponse->rollbackTransaction->originalPortingId;

                // Insert into Rollback table

                 $rollbackParams = array(
                     'rollbackId' => $rollbackId,
                     'originalPortingId' => $originalPortingId,
                     'donorSubmissionDateTime' => $openResponse->rollbackTransaction->donorSubmissionDateTime,
                     'rollbackDateTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                     'cadbOpenDateTime' => $openResponse->rollbackTransaction->cadbOpenDateTime,
                     'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                     'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
                     'rollbackNotificationMailSendStatus' => smsState::CLOSED,
                     'rollbackNotificationMailSendDateTime' => date('c'),
                     'rollbackSubmissionId' => $rollbacksubmission_id,
                 );

                 $this->Rollback_model->add_rollback($rollbackParams);

                 // Insert into Rollback State Evolution table

                 $seParams = array(
                     'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
                     'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                     'isAutoReached' => false,
                     'rollbackId' => $openResponse->rollbackTransaction->rollbackId,
                 );

                 $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();

                    $this->fileLogAction($error['code'], 'RollbackOperationService', "Rollback OPEN save failed for $rollbackId");

                    $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                    $portingParams = $this->Porting_model->get_porting($originalPortingId);

                    $rollbackParams = array_merge($rollbackParams, $portingParams);

                    $emailService = new EmailService();

                    $emailService->adminErrorReport('ROLLBACK_OPENED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                }

                $this->db->trans_complete();

                logAction($userId, "Rollback [$rollbackId] Opened Successfully");

                $this->fileLogAction('8040', 'RollbackOperationService', "Rollback OPEN successful for $rollbackId");

                $response['message'] = 'Rollback has been OPENED successfully!';

            }

            else{

                $fault = $openResponse->error;

                $this->fileLogAction('8040', 'RollbackOperationService', "Rollback OPEN failed with $fault");

                $emailService = new EmailService();

                $response['success'] = false;

                switch ($fault) {

                    // Terminal Error Processes
                    case Fault::ROLLBACK_NOT_ALLOWED:
                        $response['message'] = 'Rollback period of 4 hours has expired';
                        break;

                    case Fault::UNKNOWN_PORTING_ID:
                        $response['message'] = 'Cannot match ID of the original Porting to any transaction';
                        break;

                    case Fault::INVALID_OPERATOR_FAULT:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    default:

                        $portingParams = $this->Porting_model->get_porting($originalPortingId);

                        $submissionParams = array(
                            'originalPortingId' => $originalPortingId,
                            'rollbackId' => '',
                            'donorSubmissionDateTime' => date('c'),
                            'rollbackState' => 'NA'
                        );

                        $rollbackParams = array_merge($submissionParams, $portingParams);

                        $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                }

                logAction($userId, "Rollback Open Failed with [$fault] Fault");

            }

        }

        return $response;

    }

    /**
     * Make rollback accept for given rollbackId
     * @param $rollbackId
     */
    public function makeAccept($rollbackId, $userId) {

        $response = [];

        // Verify if rollback currently in OPENED state in DB
        $dbPort = $this->Rollback_model->get_rollback($rollbackId);

        if($dbPort){

            if($dbPort['rollbackState'] == rollback\rollbackStateType::OPENED){

                // Make Accept Rollback Operation

                $acceptResponse = $this->accept($rollbackId);

                // Verify response

                if($acceptResponse->success){

                    $this->db->trans_start();

                    // Update Rollback table

                    $rollbackParams = array(
                        'rollbackDateTime' => $acceptResponse->rollbackTransaction->rollbackDateTime,
                        'lastChangeDateTime' => $acceptResponse->rollbackTransaction->lastChangeDateTime,
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
                    );

                    $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                    // Insert into Rollback State Evolution table

                    $seParams = array(
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED,
                        'lastChangeDateTime' => $acceptResponse->rollbackTransaction->lastChangeDateTime,
                        'isAutoReached' => false,
                        'rollbackId' => $acceptResponse->rollbackTransaction->rollbackId,
                    );

                    $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();

                        $this->fileLogAction('8040', 'RollbackOperationService', "Rollback ACCEPT save failed for $rollbackId");

                        $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService = new EmailService();

                        $emailService->adminErrorReport('ROLLBACK_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Rollback [$rollbackId] Accepted Successfully");

                    $this->fileLogAction('8040', 'RollbackOperationService', "Rollback ACCEPT successful for $rollbackId");

                    $response['message'] = 'Rollback has been ACCEPTED successfully!';

                }

                else{

                    $fault = $acceptResponse->error;

                    $this->fileLogAction('8040', 'RollbackOperationService', "Rollback OPEN failed with $fault");

                    $emailService = new EmailService();

                    $response['success'] = false;

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                            $response['message'] = 'Operator not active. Please try again later';
                            break;

                        // Terminal Error Processes
                        case Fault::INVALID_REQUEST_FORMAT:
                        case Fault::INVALID_ROLLBACK_ID:
                        case Fault::ROLLBACK_ACTION_NOT_AVAILABLE:
                        default:

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);
                            $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    }

                    logAction($userId, "Rollback Acceptance Failed with [$fault] Fault");

                }

            }else{

                $response['success'] = false;

                $response['message'] = 'Rollback now in ' . $dbPort['rollbackState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }

        return $response;

    }

    /**
     * Make rollback reject
     * @param $rollbackId
     * @param $rejectionReason
     * @param $cause
     */
    public function makeReject($rollbackId, $rejectionReason, $cause, $userId){

        $response = [];

        // Verify if rollback currently in OPENED state in DB
        $dbRollback = $this->Rollback_model->get_rollback($rollbackId);

        if($dbRollback){

            if($dbRollback['rollbackState'] == rollback\rollbackStateType::OPENED){

                if($rejectionReason == \RollbackService\Rollback\rejectionReasonType::OTHER_REASONS) {

                    // Make Reject Rollback Operation

                    $rejectResponse = $this->reject($rollbackId, $cause, $rejectionReason);

                    // Verify response

                    if($rejectResponse->success){

                        $this->db->trans_start();

                        // Update Rollback table

                        $rollbackParams = array(
                            'rollbackDateTime' => $rejectResponse->rollbackTransaction->rollbackDateTime,
                            'lastChangeDateTime' => $rejectResponse->rollbackTransaction->lastChangeDateTime,
                            'rollbackState' => \RollbackService\Rollback\rollbackStateType::REJECTED
                        );

                        $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                        // Insert into Rollback State Evolution table

                        $seParams = array(
                            'rollbackState' => \RollbackService\Rollback\rollbackStateType::REJECTED,
                            'lastChangeDateTime' => $rejectResponse->rollbackTransaction->lastChangeDateTime,
                            'isAutoReached' => false,
                            'rollbackId' => $rejectResponse->rollbackTransaction->rollbackId,
                        );

                        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

                        // Insert into rollback rejectAbandon Table

                        $rjParams = array(
                            'rejectionReason' => $rejectionReason,
                            'cause' => $cause,
                            'rollbackId' => $rollbackId,
                        );

                        $this->Rollbackrejectionabandon_model->add_rollbackrejectionabandon($rjParams);

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();

                            $this->fileLogAction($error['code'], 'RollbackOperationService', "Rollback REJECT save failed for $rollbackId");

                            $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                            $response = $error;

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('ROLLBACK_REJECTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                        }

                        $this->db->trans_complete();

                        logAction($userId, "Rollback [$rollbackId] Rejected Successfully");

                        $this->fileLogAction('8040', 'RollbackOperationService', "Rollback REJECT successful for $rollbackId");

                        $response['message'] = 'Rollback has been REJECTED successfully!';

                    }

                    else{

                        $fault = $rejectResponse->error;

                        $this->fileLogAction('8040', 'RollbackOperationService', "Rollback REJECT failed for $fault");

                        $emailService = new EmailService();

                        $response['success'] = false;

                        switch ($fault) {
                            // Terminal Processes
                            case Fault::INVALID_OPERATOR_FAULT:
                                $response['message'] = 'Operator not active. Please try again later';
                                break;

                            // Terminal Error Processes
                            case Fault::INVALID_REQUEST_FORMAT:
                            case Fault::INVALID_ROLLBACK_ID:
                            case Fault::CAUSE_MISSING:
                            case Fault::ROLLBACK_ACTION_NOT_AVAILABLE:
                            default:

                                $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                                $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);
                                $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                        }

                        logAction($userId, "Rollback Rejection Failed with [$fault] Fault");

                    }

                }

                else{

                    $response['success'] = false;

                    $response['message'] = 'Invalid rejection reason';

                }

            }else{

                $response['success'] = false;

                $response['message'] = 'Rollback now in ' . $dbRollback['rollbackState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }

        return $response;

    }

    /**
     * Search rollback with msisdn
     * @param $msisdn
     * @return array
     */
    public function searchRollback($msisdn, $userId){

        $response = [];

        $response['success'] = true;

        $response['data'] = $this->Rollback_model->search_rollback($msisdn);

        return $response;

    }


    /**
     * API to retrieve detail on rollback
     */
    public function getCADBRollback($rollbackId){
        $response = [];

        $getResponse = $this->getRollback($rollbackId);

        // Verify response

        if($getResponse->success){

            $response['success'] = true;

            $tmpData = $getResponse->rollbackTransaction;

            $portingData = $this->Porting_model->get_porting($tmpData->originalPortingId);

            $data = array();

            $data['rollbackId'] = $tmpData->rollbackId;
            $data['originalPortingId'] = $tmpData->originalPortingId;
            $data['donorSubmissionDateTime'] = $tmpData->donorSubmissionDateTime;
            $data['rollbackDateTime'] = $tmpData->rollbackDateTime;
            $data['lastChangeDateTime'] = $tmpData->lastChangeDateTime;
            $data['cadbOpenDateTime'] = $tmpData->cadbOpenDateTime;
            $data['rollbackState'] = $tmpData->rollbackState;

            if($portingData == null){
                $portingData = [];
            }

            $response['data'] = array_merge($data, $portingData);

        }

        else{

            $fault = $getResponse->error;

            $response['success'] = false;

            switch ($fault) {
                // Terminal Processes
                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::PORTING_ACTION_NOT_AVAILABLE:
                case Fault::INVALID_ROLLBACK_ID:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    $response['message'] = 'Error from CADB';

            }

        }

        return $response;

    }

    /**
     * TODO: getCADBRollbacks
     * API to retrieve all rollbacks from CADB
     */
    public function getCADBRollbacks(){

        $response = [];

        $response['data'] = [];

        // Load ORDERED Rollbacks

        $openedResponse = $this->getOpenedRollbacks(Operator::ORANGE_NETWORK_ID);

        if($openedResponse->success){

            $response['data'] = array_merge($response['data'], $openedResponse->rollbacks);

        }
        else{

            $fault = $openedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_OPENED_ROLLBACKS_FROM_CADB", []);
            }

        }

        // Load ACCEPTED Rollbacks

        $acceptedResponse = $this->getAcceptedRollbacks(Operator::ORANGE_NETWORK_ID);

        if($acceptedResponse->success){

            $response['data'] = array_merge($response['data'], $acceptedResponse->rollbacks);

        }
        else{

            $fault = $acceptedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_ACCEPTED_ROLLBACKS_FROM_CADB", []);
            }

        }

        // Load CONFIRMED Rollbacks

        $confirmedResponse = $this->getConfirmedRollbacks(Operator::ORANGE_NETWORK_ID);

        if($confirmedResponse->success){

            $response['data'] = array_merge($response['data'], $confirmedResponse->rollbacks);

        }
        else{

            $fault = $confirmedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_CONFIRMED_ROLLBACKS_FROM_CADB", []);
            }

        }

        // Load REJECTED Rollbacks

        $rejectedResponse = $this->getRejectedRollbacks(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

        if($rejectedResponse->success){

            $response['data'] = array_merge($response['data'], $rejectedResponse->rollbacks);

        }
        else{

            $fault = $rejectedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                case Fault::COUNT_OVER_MAX_COUNT_LIMIT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_REJECTED_ROLLBACKS_FROM_CADB", []);
            }

        }

        return $response;
    }

}