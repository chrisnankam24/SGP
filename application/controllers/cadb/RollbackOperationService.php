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
require_once "ProvisionNotification.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use RollbackService\Rollback as rollback;
use \ProvisionService\ProvisionNotification\provisionStateType as provisionStateType;

class RollbackOperationService {

    // Declare client
    private $client = null;

    private $db = null;
    private $Porting_model = null;
    private $FileLog_model = null;
    private $Rollback_model = null;
    private $ProcessNumber_model = null;
    private $Rollbackstateevolution_model = null;
    private $Rollbackrejectionabandon_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $this->db = $CI->db;

        $CI->load->model('Porting_model');
        $CI->load->model('FileLog_model');
        $CI->load->model('Rollback_model');
        $CI->load->model('ProcessNumber_model');
        $CI->load->model('Rollbackstateevolution_model');
        $CI->load->model('Rollbackrejectionabandon_model');

        $this->Porting_model = $CI->Porting_model;
        $this->FileLog_model = $CI->FileLog_model;
        $this->Rollback_model = $CI->Rollback_model;
        $this->ProcessNumber_model = $CI->ProcessNumber_model;
        $this->Rollbackstateevolution_model = $CI->Rollbackstateevolution_model;
        $this->Rollbackrejectionabandon_model = $CI->Rollbackrejectionabandon_model;

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        libxml_disable_entity_loader(false);

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/RollbackOperationService.wsdl', array(
            "trace" => true,
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

                $this->logRequestResponse('open');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('open');

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

                $this->logRequestResponse('accept');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('accept');

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

                $this->logRequestResponse('reject');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('reject');

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

                $this->logRequestResponse('confirm');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('confirm');

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

        $numberDetails = $bscsOperationService->loadTemporalNumberInfo($temporalNumber);

        $contractId = $numberDetails["CONTRACT_ID"];

        if($contractId == -1){

            $response['success'] = false;
            $response['message'] = 'Connection to BSCS Unsuccessful. Please try again later';

        }elseif($contractId == null){

            $response['success'] = false;
            $response['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';

        }
        else{

            $donorSubmissionDateTime = date('c');
            $rollbackDateTime = date('c', strtotime('+2 hours', strtotime(date('c'))));

            $portingDetails = $this->Porting_model->get_porting($originalPortingId);

            if($portingDetails){

                if($portingDetails['physicalPersonFirstName'] != null && $numberDetails['ID_PIECE'] != trim($portingDetails['physicalPersonIdNumber'])){

                    $response['success'] = false;
                    $response['message'] = 'Temporal number does not belong to subscriber';

                }elseif($portingDetails['legalPersonName'] != null && $numberDetails['NUM_REGISTRE'] != trim($portingDetails['legalPersonTin'])){

                    $response['success'] = false;
                    $response['message'] = 'Temporal number does not belong to subscriber';

                }else{

                    // Make Open Rollback Operation

                    $openResponse = $this->open($originalPortingId, $donorSubmissionDateTime, $rollbackDateTime);

                    // Verify response

                    if($openResponse->success){

                        $this->db->trans_start();

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
                            'rollbackNotificationMailSendDateTime' => date('c')
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

                        // Fill in Rollback process numbers

                        $portingNumbers = $this->getRollbackNumbers($openResponse);

                        $processNumberParams = [];

                        foreach ($portingNumbers as $portingNumber){
                            $processNumberParams[] = array(
                                'processId' => $rollbackId,
                                'msisdn' => $portingNumber,
                                'numberState' => provisionStateType::STARTED,
                                'pLastChangeDateTime' => date('c'),
                                'processType' => processType::ROLLBACK,
                                'contractId' => $contractId,
                                'temporalMsisdn' => $temporalNumber
                            );
                        }

                        $this->db->insert_batch('processnumber', $processNumberParams);

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();

                            $this->fileLogAction($error['code'], 'RollbackOperationService', "Rollback OPEN save failed for $rollbackId");

                            $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                            $portingParams = $this->Porting_model->get_porting($originalPortingId);

                            $rollbackParams = array_merge($rollbackParams, $portingParams);

                            $emailService = new EmailService();

                            $emailService->adminErrorReport('ROLLBACK OPENED BUT DB FILLED INCOMPLETE', $rollbackParams, processType::ROLLBACK);

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

            }else{
                // Port not found in DB
                $response['success'] = false;
                $response['message'] = 'Original Porting process not found';
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

                    // Update Number state
                    $portingNumberParams = array(
                        'pLastChangeDateTime' => date('c'),
                        'numberState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
                    );

                    $this->ProcessNumber_model->update_processnumber_all($rollbackId, $portingNumberParams);

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();

                        $this->fileLogAction('8040', 'RollbackOperationService', "Rollback ACCEPT save failed for $rollbackId");

                        $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService = new EmailService();

                        $emailService->adminErrorReport('ROLLBACK ACCEPTED BUT DB FILLED INCOMPLETE', $rollbackParams, processType::ROLLBACK);

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

                        // Update Number state
                        $portingNumberParams = array(
                            'pLastChangeDateTime' => date('c'),
                            'numberState' => provisionStateType::TERMINATED,
                            'terminationReason' => $cause
                        );

                        $this->ProcessNumber_model->update_processnumber_all($rollbackId, $portingNumberParams);

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();

                            $this->fileLogAction($error['code'], 'RollbackOperationService', "Rollback REJECT save failed for $rollbackId");

                            $this->fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                            $response = $error;

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('ROLLBACK REJECTED BUT DB FILLED INCOMPLETE', $rollbackParams, processType::ROLLBACK);

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

            if(isset($openedResponse->rollbackTransaction)){

                if(is_array($openedResponse->rollbackTransaction)){

                    $response['data'] = array_merge($response['data'], $openedResponse->rollbackTransaction);

                }else{

                    $response['data'][] = $openedResponse->rollbackTransaction;

                }

            }

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

            if(isset($acceptedResponse->rollbackTransaction)){

                if(is_array($acceptedResponse->rollbackTransaction)){

                    $response['data'] = array_merge($response['data'], $acceptedResponse->rollbackTransaction);

                }else{

                    $response['data'][] = $acceptedResponse->rollbackTransaction;

                }

            }

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

            if(isset($confirmedResponse->rollbackTransaction)){

                if(is_array($confirmedResponse->rollbackTransaction)){

                    $response['data'] = array_merge($response['data'], $confirmedResponse->rollbackTransaction);

                }else{

                    $response['data'][] = $confirmedResponse->rollbackTransaction;

                }

            }

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

            if(isset($rejectedResponse->rollbackTransaction)){

                if(is_array($rejectedResponse->rollbackTransaction)){

                    $response['data'] = array_merge($response['data'], $rejectedResponse->rollbackTransaction);

                }else{

                    $response['data'][] = $rejectedResponse->rollbackTransaction;

                }

            }

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

        $tmpData = $response['data'];

        $response['data'] = [];

        foreach ($tmpData as $tmpDatum){

            $res = new stdClass();
            $res->rollbackTransaction = $tmpDatum;

            $data = array();

            $data['originalPortingId'] = $tmpDatum->originalPortingId;
            $data['donorSubmissionDateTime'] = $tmpDatum->donorSubmissionDateTime;
            $data['rollbackDateTime'] = $tmpDatum->rollbackDateTime;
            $data['cadbOpenDateTime'] = $tmpDatum->cadbOpenDateTime;
            $data['lastChangeDateTime'] = $tmpDatum->lastChangeDateTime;
            $data['rollbackState'] = $tmpDatum->rollbackState;

            $data['recipientNetworkId'] = $tmpDatum->recipientNrn->networkId;
            $data['recipientRoutingNumber'] = $tmpDatum->recipientNrn->routingNumber;
            $data['donorNetworkId'] = $tmpDatum->donorNrn->networkId;
            $data['donorRoutingNumber'] = $tmpDatum->donorNrn->routingNumber;

            $data['msisdn'] = $this->getRollbackNumbers($res);
            $data['contactNumber'] = $tmpDatum->subscriberInfo->contactNumber;

            if(isset($tmpDatum->subscriberInfo->physicalPersonFirstName)) {

                $data['physicalPersonFirstName'] = $tmpDatum->subscriberInfo->physicalPersonFirstName;
                $data['physicalPersonLastName'] = $tmpDatum->subscriberInfo->physicalPersonLastName;
                $data['physicalPersonIdNumber'] = $tmpDatum->subscriberInfo->physicalPersonIdNumber;

                $data['legalPersonName'] = null;
                $data['legalPersonTin'] = null;

            }
            else{

                $data['legalPersonName'] = $tmpDatum->subscriberInfo->legalPersonName;
                $data['legalPersonTin'] = $tmpDatum->subscriberInfo->legalPersonTin;

                $data['physicalPersonFirstName'] = null;
                $data['physicalPersonLastName'] = null;
                $data['physicalPersonIdNumber'] = null;

            }

            array_push($response['data'], $data);

        }

        return $response;
    }

    private function logRequestResponse($action){
        $this->fileLogAction('', 'RollbackOperationService', $action . ' Request:: ' . $this->client->__getLastRequest());
        $this->fileLogAction('', 'RollbackOperationService', $action . ' Response:: ' . $this->client->__getLastResponse());
    }

    /**
     * Returns rollback MSISDN in process
     * @param $request
     * @return array
     */
    private function getRollbackNumbers($request){

        $numbers = [];

        if(is_array($request->rollbackTransaction->numberRanges->numberRange)){

            foreach ($request->rollbackTransaction->numberRanges->numberRange as $numberRange){

                $startMSISDN = $numberRange->startNumber;
                $endMSISDN = $numberRange->endNumber;

                if(strlen($startMSISDN) == 12){
                    $startMSISDN = substr($startMSISDN, 3);
                }
                if(strlen($endMSISDN) == 12){
                    $endMSISDN = substr($endMSISDN, 3);
                }

                $startMSISDN = intval($startMSISDN);
                $endMSISDN = intval($endMSISDN);

                while ($startMSISDN <= $endMSISDN){
                    $numbers[] = '237' . $startMSISDN;
                    $startMSISDN += 1;
                }

            }

        }
        else{

            $startMSISDN = $request->rollbackTransaction->numberRanges->numberRange->startNumber;
            $endMSISDN = $request->rollbackTransaction->numberRanges->numberRange->endNumber;

            if(strlen($startMSISDN) == 12){
                $startMSISDN = substr($startMSISDN, 3);
            }
            if(strlen($endMSISDN) == 12){
                $endMSISDN = substr($endMSISDN, 3);
            }

            $startMSISDN = intval($startMSISDN);
            $endMSISDN = intval($endMSISDN);

            while ($startMSISDN <= $endMSISDN){
                $numbers[] = '237' . $startMSISDN;
                $startMSISDN += 1;
            }

        }

        $numbers = array_values(array_unique($numbers));

        return $numbers;

    }
}