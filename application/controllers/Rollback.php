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

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

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

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

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

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

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

    /*
     * Listing of rollback
     */
    function index()
    {
        $data['rollback'] = $this->Rollback_model->get_all_rollback();

        $this->load->view('rollback/index',$data);
    }

    /*
     * Adding a new rollback
     */
    function add()
    {   
        if(isset($_POST) && count($_POST) > 0)     
        {   
            $params = array(
				'originalPortingId' => $this->input->post('originalPortingId'),
				'donorSubmissionDateTime' => $this->input->post('donorSubmissionDateTime'),
				'preferredRollbackDateTime' => $this->input->post('preferredRollbackDateTime'),
				'rollbackDateAndTime' => $this->input->post('rollbackDateAndTime'),
				'cadbOpenDateTime' => $this->input->post('cadbOpenDateTime'),
				'lastChangeDateTime' => $this->input->post('lastChangeDateTime'),
				'rollbackState' => $this->input->post('rollbackState'),
				'rollbackSubmissionId' => $this->input->post('rollbackSubmissionId'),
            );
            
            $rollback_id = $this->Rollback_model->add_rollback($params);
            redirect('rollback/index');
        }
        else
        {
            $this->load->view('rollback/add');
        }
    }  

    /*
     * Editing a rollback
     */
    function edit($rollbackId)
    {   
        // check if the rollback exists before trying to edit it
        $rollback = $this->Rollback_model->get_rollback($rollbackId);
        
        if(isset($rollback['rollbackId']))
        {
            if(isset($_POST) && count($_POST) > 0)     
            {   
                $params = array(
					'originalPortingId' => $this->input->post('originalPortingId'),
					'donorSubmissionDateTime' => $this->input->post('donorSubmissionDateTime'),
					'preferredRollbackDateTime' => $this->input->post('preferredRollbackDateTime'),
					'rollbackDateAndTime' => $this->input->post('rollbackDateAndTime'),
					'cadbOpenDateTime' => $this->input->post('cadbOpenDateTime'),
					'lastChangeDateTime' => $this->input->post('lastChangeDateTime'),
					'rollbackState' => $this->input->post('rollbackState'),
					'rollbackSubmissionId' => $this->input->post('rollbackSubmissionId'),
                );

                $this->Rollback_model->update_rollback($rollbackId,$params);            
                redirect('rollback/index');
            }
            else
            {   
                $data['rollback'] = $this->Rollback_model->get_rollback($rollbackId);
    
                $this->load->view('rollback/edit',$data);
            }
        }
        else
            show_error('The rollback you are trying to edit does not exist.');
    } 

    /*
     * Deleting rollback
     */
    function remove($rollbackId)
    {
        $rollback = $this->Rollback_model->get_rollback($rollbackId);

        // check if the rollback exists before trying to delete it
        if(isset($rollback['rollbackId']))
        {
            $this->Rollback_model->delete_rollback($rollbackId);
            redirect('rollback/index');
        }
        else
            show_error('The rollback you are trying to delete does not exist.');
    }
    
}
