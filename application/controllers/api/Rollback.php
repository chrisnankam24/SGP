<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Rollback.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/cadb/RollbackOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";


class Rollback extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Porting_model');
        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbackstateevolution_model');

    }

    /**
     * API for performing open request
     */
    public function openRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $originalPortingId = $this->input->post('originalPortingId');
            $temporalNumber = $this->input->post('temporalNumber');
            $language = $this->input->post('language'); // EN or FR

            $response = $this->makeOpen($originalPortingId, $temporalNumber, $language);

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk open request
     */
    public function openBulkRollback(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');

            if($file_name != ''){
                $row = 1;

                $response['success'] = true;
                $response['data'] = [];

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){
                            // Check if header Ok
                            $errorFound = false;
                            if(strtolower($data[0]) != 'originalportingid'){
                                $errorFound = true;
                            }
                            if(strtolower($data[1]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if(strtolower($data[1]) != 'language'){
                                $errorFound = true;
                            }
                            if($errorFound){
                                $response['success'] = false;
                                $response['message'] = 'Invalid file content format. Columns do not match defined template. If you have difficulties creating file, please contact administrator';

                                $this->send_response($response);
                                return;
                            }
                            $row++;
                        }else{

                            $tempResponse = [];

                            $originalPortingId = $data[0]; // originalPortingId
                            $temporalNumber = $data[1]; // temporalNumber
                            $language = $data[2]; // language

                            $tempResponse = $this->makeOpen($originalPortingId, $temporalNumber, $language);

                            $response['data'][] = $tempResponse;

                        }
                    }

                    fclose($handle);
                }

            }else{
                $response['success'] = false;
                $response['message'] = 'No file name found';
            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No file name found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing accept request
     */
    public function acceptRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackId = $this->input->post('rollbackId');

            $response = $this->makeAccept($rollbackId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk accept
     */
    public function acceptBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform accept one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = $this->input->post('rollbackData'); // Array of rollbackIds

            $response['success'] = true;
            $response['data'] = [];

            foreach ($rollbackData as $rollbackId){

                $response['data'][] = $this->makeAccept($rollbackId);

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing reject request
     */
    public function rejectRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackId = $this->input->post('rollbackId');
            $rejectionReason = $this->input->post('rejectionReason');
            $cause = $this->input->post('cause');

            $response = $this->makeReject($rollbackId, $rejectionReason, $cause);

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk reject
     */
    public function rejectBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform reject one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = $this->input->post('$rollbackData'); // Array of rejection objects i.e (portingId, rejectionReason, cause)

            $response['success'] = true;
            $response['data'] = [];

            foreach ($rollbackData as $rollbackDatum){

                $response['data'][] = $this->makeReject($rollbackDatum['rollbackId'], $rollbackDatum['rejectionReason'], $rollbackDatum['cause']);

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);

    }

    /**
     * API to retrieve detail on rollback
     */
    public function getCADBRollback(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackId = $this->input->post('rollbackId');

            $rollbackOperationService = new RollbackOperationService();
            $getResponse = $rollbackOperationService->getRollback($rollbackId);

            // Verify response

            if($getResponse->success){

                $response['success'] = true;

                $response['data'] = $getResponse->rollbackTransaction;

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

        }else{

            $response['success'] = false;
            $response['message'] = 'No return id found';

        }

        $this->send_response($response);
    }

    /**
     * API to retieve all rollbacks from LDB
     */
    public function getLDBRollbacks(){
        $response = [];

        $response['data'] = $this->Rollback_model->get_all_rollback();

        $this->send_response($response);
    }

    /**
     * API to retrieve all rollbacks from CADB
     */
    public function getCADBRollbacks(){

        $response = [];

        $response['data'] = [];

        $rollbackOperationService = new RollbackOperationService();

        // Load ORDERED Rollbacks

        $openedResponse = $rollbackOperationService->getOpenedRollbacks(Operator::ORANGE_NETWORK_ID);

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

        $acceptedResponse = $rollbackOperationService->getAcceptedRollbacks(Operator::ORANGE_NETWORK_ID);

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

        $confirmedResponse = $rollbackOperationService->getConfirmedRollbacks(Operator::ORANGE_NETWORK_ID);

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

        $rejectedResponse = $rollbackOperationService->getRejectedRollbacks(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

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

        $this->send_response($response);
    }

    /**
     *
     * @param $response
     */
    private function send_response($response)
    {
        header("Content-type: text/json");
        echo json_encode($response);
    }

    /**
     * Make rollback open for given portingId
     * @param $originalPortingId
     * @param $contractId
     * @param $temporalNumber
     * @param $language
     * @param $donorSubmissionDateTime
     * @param $preferredRollbackDateTime
     */
    private function makeOpen($originalPortingId, $temporalNumber, $language){

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

            $rollbackOperationService = new RollbackOperationService();
            $openResponse = $rollbackOperationService->open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime);

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
                    'temporalMSISDN' => $temporalNumber
                );

                $rollbacksubmission_id = $this->Rollbacksubmission_model->add_rollbacksubmission($submissionParams);

                // Insert into Rollback table

                $rollbackParams = array(
                    'rollbackId' => $openResponse->rollbackTransaction->rollbackId,
                    'originalPortingId' => $openResponse->rollbackTransaction->originalPortingId,
                    'donorSubmissionDateTime' => $openResponse->rollbackTransaction->donorSubmissionDateTime,
                    'preferredRollbackDateTime' => $openResponse->rollbackTransaction->preferredRollbackDateTime,
                    'rollbackDateAndTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                    'cadbOpenDateTime' => $openResponse->rollbackTransaction->cadbOpenDateTime,
                    'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
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

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_OPENED_BUT_DB_FILLED_INCOMPLETE', []);

                }

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
                            'temporalMSISDN' => $temporalNumber
                        );

                        $this->Rollbacksubmission_model->add_rollbacksubmission($submissionParams);

                        $this->db->trans_complete();

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('ROLLBACK_REQUESTED_OPERATOR_INACTIVE_BUT_STARTED_INCOMPLETE', []);
                            $response['message'] = 'Operator is currently Inactive. We have nonetheless encountered problems saving your request. Please contact Back Office';

                        }else{

                            $response['message'] = 'Operator is currently Inactive. You request has been saved and will be performed as soon as possible';

                        }

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
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                        break;

                    default:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                }
            }

        }

        return $response;

    }

    /**
     * Make rollback accept for given rollbackId
     * @param $rollbackId
     */
    private function makeAccept($rollbackId) {

        $response = [];

        // Make Accept Rollback Operation

        $rollbackOperationService = new RollbackOperationService();
        $acceptResponse = $rollbackOperationService->accept($rollbackId);

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

            $this->db->trans_complete();

            $response['success'] = true;

            if ($this->db->trans_status() === FALSE) {

                $emailService = new EmailService();
                $emailService->adminErrorReport('ROLLBACK_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

            }

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
                    $emailService->adminErrorReport($fault, []);
                    $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    break;

                default:
                    $emailService->adminErrorReport($fault, []);
                    $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
            }
        }


        return $response;

    }

    /**
     * Make rollback reject
     * @param $rollbackId
     * @param $rejectionReason
     * @param $cause
     */
    private function makeReject($rollbackId, $rejectionReason, $cause){

        $response = [];

        if($rejectionReason != \RollbackService\Rollback\rejectionReasonType::OTHER_REASONS) {

            // Make Reject Rollback Operation

            $rollbackOperationService = new RollbackOperationService();
            $rejectResponse = $rollbackOperationService->reject($rollbackId, $rejectionReason, $cause);

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

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

                }

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
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                        break;

                    default:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                }
            }

        }
        else{

            $response['success'] = false;
            $response['message'] = 'Invalid rejection reason';
        }

        return $response;

    }
}
