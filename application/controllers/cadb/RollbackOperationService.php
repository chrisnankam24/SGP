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

class RollbackOperationService  extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Porting_model');
        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbackstateevolution_model');
        $this->load->model('Rollbackrejectionabandon_model');

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/RollbackOperationService.wsdl', array(
            "trace" => false
        ));

    }

    public function index(){

    }

    /**
     * @param $originalPortingId string porting id of original porting process
     * @param $donorSubmissionDateTime string of submission process
     * @param $preferredRollbackDateTime string of preferred porting process
     * @return errorResponse
     */
    public function open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime) {

        if($this->client) {

            // Make open request
            $request = new rollback\openRequest();

            $request->originalPortingId = $originalPortingId;

            $request->donorSubmissionDateTime = $donorSubmissionDateTime;

            $request->preferredRollbackDateTime = $preferredRollbackDateTime;

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
            $request->rollbackDateAndTime = $rollbackDateAndTime;

            try {

                $response = $this->client->confirm($request);
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
     * Make rollback open for given portingId
     * @param $originalPortingId
     * @param $contractId
     * @param $temporalNumber
     * @param $language
     * @param $donorSubmissionDateTime
     * @param $preferredRollbackDateTime
     */
    public function makeOpen($originalPortingId, $temporalNumber, $language, $userId){

        $response = [];

        // Get subscriber contractId from BSCS with temporal MSISDN
        $bscsOperationService = new BscsOperationService();
        $contractId = $bscsOperationService->getContractId($temporalNumber);

        if($contractId == -1){

            $tempResponse['success'] = false;
            $tempResponse['message'] = 'Connection to BSCS Unsuccessful. Please try again later';

        }elseif($contractId == null){

            $tempResponse['success'] = false;
            $tempResponse['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';

        }else{

            $donorSubmissionDateTime = date('c');
            $preferredRollbackDateTime = date('c', strtotime('+4 hours', strtotime(date('c'))));

            // Make Open Rollback Operation

            $openResponse = $this->open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime);

            // Verify response

            if($openResponse->success){

                $this->db->trans_start();

                // Insert into Rollback submission table

                $submissionParams = array(
                    'originalPortingId' => $originalPortingId,
                    'preferredRollbackDateTime' => $openResponse->rollbackTransaction->preferredRollbackDateTime,
                    'submissionState' => \RollbackService\Rollback\rollbackSubmissionStateType::OPENED,
                    'openedDateTime' => date('c'),
                    'contractId' => $contractId,
                    'language' => $language,
                    'temporalMSISDN' => $temporalNumber,
                    'userId' => $userId
                );

                $rollbacksubmission_id = $this->Rollbacksubmission_model->add_rollbacksubmission($submissionParams);
                $rollbackId = $openResponse->rollbackTransaction->rollbackId;

                // Insert into Rollback table

                 $rollbackParams = array(
                     'rollbackId' => $rollbackId,
                     'originalPortingId' => $openResponse->rollbackTransaction->originalPortingId,
                     'donorSubmissionDateTime' => $openResponse->rollbackTransaction->donorSubmissionDateTime,
                     'preferredRollbackDateTime' => $openResponse->rollbackTransaction->preferredRollbackDateTime,
                     'rollbackDateTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                     'cadbOpenDateTime' => $openResponse->rollbackTransaction->cadbOpenDateTime,
                     'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                     'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
                     'notificationMailSendStatus' => smsState::PENDING,
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
                    fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_OPENED_BUT_DB_FILLED_INCOMPLETE', []);

                }

                $this->db->trans_complete();

                logAction($userId, "Rollback [$rollbackId] Opened Successfully");

                $response['message'] = 'Rollback has been OPENED successfully!';

            }

            else{

                $fault = $openResponse->error;

                $emailService = new EmailService();

                $response['success'] = false;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $this->db->trans_start();

                        // Insert into Rollback submission table

                        $submissionParams = array(
                            'originalPortingId' => $originalPortingId,
                            'preferredRollbackDateTime' => $preferredRollbackDateTime,
                            'submissionState' => \RollbackService\Rollback\rollbackSubmissionStateType::STARTED,
                            'openedDateTime' => date('c'),
                            'contractId' => $contractId,
                            'language' => $language,
                            'temporalMSISDN' => $temporalNumber,
                            'userId' => $userId
                        );

                        $this->Rollbacksubmission_model->add_rollbacksubmission($submissionParams);

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();
                            fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                            $emailService->adminErrorReport('ROLLBACK_REQUESTED_OPERATOR_INACTIVE_BUT_STARTED_INCOMPLETE', []);
                            $response['message'] = 'Operator is currently Inactive. We have nonetheless encountered problems saving your request. Please contact Back Office';

                        }else{

                            $response['message'] = 'Operator is currently Inactive. You request has been saved and will be performed as soon as possible';

                        }

                        $this->db->trans_complete();

                        break;

                    // Terminal Error Processes
                    case Fault::ROLLBACK_NOT_ALLOWED:
                        $response['message'] = 'Rollback period of 4 hours has expired';
                        break;

                    case Fault::UNKNOWN_PORTING_ID:
                        $response['message'] = 'Cannot match ID of the original Porting to any transaction';
                        break;

                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    default:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                }

                logAction($userId, "Rollback Open Failed with [$fault] Fault");

            }

        }

        return $response;

    }

    /**
     * TODO: OK
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
                        'preferredRollbackDateTime' => $acceptResponse->rollbackTransaction->preferredRollbackDateTime,
                        'rollbackDateAndTime' => $acceptResponse->rollbackTransaction->rollbackDateTime,
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
                        fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('ROLLBACK_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Rollback [$rollbackId] Accepted Successfully");

                    $response['message'] = 'Rollback has been ACCEPTED successfully!';

                }

                else{

                    $fault = $acceptResponse->error;

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
                            $emailService->adminErrorReport($fault, []);
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
     * TODO: OK
     * Make rollback reject
     * @param $rollbackId
     * @param $rejectionReason
     * @param $cause
     */
    public function makeReject($rollbackId, $rejectionReason, $cause, $userId){

        $response = [];

        // Verify if rollback currently in OPENED state in DB
        $dbPort = $this->Rollback_model->get_rollback($rollbackId);

        if($dbPort){

            if($dbPort['rollbackState'] == rollback\rollbackStateType::OPENED){

                if($rejectionReason == \RollbackService\Rollback\rejectionReasonType::OTHER_REASONS) {

                    // Make Reject Rollback Operation

                    $rejectResponse = $this->reject($rollbackId, $rejectionReason, $cause);

                    // Verify response

                    if($rejectResponse->success){

                        $this->db->trans_start();

                        // Update Rollback table

                        $rollbackParams = array(
                            'preferredRollbackDateTime' => $rejectResponse->rollbackTransaction->preferredRollbackDateTime,
                            'rollbackDateAndTime' => $rejectResponse->rollbackTransaction->rollbackDateTime,
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
                            fileLogAction($error['code'], 'RollbackOperationService', $error['message']);

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('ROLLBACK_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

                        }

                        $this->db->trans_complete();

                        logAction($userId, "Rollback [$rollbackId] Rejected Successfully");

                        $response['message'] = 'Rollback has been REJECTED successfully!';

                    }

                    else{

                        $fault = $rejectResponse->error;

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
                                $emailService->adminErrorReport($fault, []);
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

                $response['message'] = 'Rollback now in ' . $dbPort['rollbackState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }

        return $response;

    }

}