<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Return.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/cadb/ReturnOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";
 
class NReturn extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

    }

    /**
     * API for performing open request
     */
    public function openNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnMSISDN = $this->input->post('returnMSISDN');
            $returnOperator = $this->input->post('returnOperator'); // 0 == MTN, 1 == Nexttel

            // Make Open NR Operation

            $nrOperationService = new ReturnOperationService();
            $openResponse = $nrOperationService->open($returnOperator, $returnMSISDN);

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
                );

                $submissionId = $this->Numberreturnsubmission_model->add_numberreturnsubmission($nrsParams);

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
                    'numberReturnSubmissionId' => $submissionId,
                );

                $this->Numberreturn_model->add_numberreturn($nrParams);

                // Insert into NR state Evolution table

                $nrsParams = array(
                    'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
                    'lastChangeDateTime' => date('c'),
                    'isAutoReached' => false,
                    'returnId' => $openResponse->returnTransaction->returnId,
                );

                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_OPENED_BUT_DB_FILLED_INCOMPLETE', []);

                }

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

                        $primaryOwnerNetworkId = '';
                        $primaryOwnerNetworkNumber = '';

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
                        );

                        $this->Numberreturnsubmission_model->add_numberreturnsubmission($nrsParams);

                        $this->db->trans_complete();

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('RETURN_REQUESTED_OPERATOR_INACTIVE_BUT_STARTED_INCOMPLETE', []);
                            $response['message'] = 'Operator is currently Inactive. We have nonetheless encountered problems saving your request. Please contact Back Office';

                        }else{

                            $response['message'] = 'Operator is currently Inactive. You request has been saved and will be performed as soon as possible';

                        }

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
            $response['message'] = 'No MSISDN found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing accept request
     */
    public function acceptNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnId = $this->input->post('returnId');

            // Make accept NR Operation

            $nrOperationService = new ReturnOperationService();
            $acceptResponse = $nrOperationService->accept($returnId);

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
                    'isAutoReached' => false,
                    'returnId' => $acceptResponse->returnTransaction->returnId,
                );

                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

                }

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
            $response['message'] = 'No ReturnId found';

        }

        $this->send_response($response);

    }

    /**
     * API for preforming reject request
     */
    public function rejectNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnId = $this->input->post('returnId');
            $cause = $this->input->post('cause');

            // Make reject NR Operation

            $nrOperationService = new ReturnOperationService();
            $rejectResponse = $nrOperationService->reject($returnId, $cause);

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
                    'isAutoReached' => false,
                    'returnId' => $rejectResponse->returnTransaction->returnId,
                );

                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                // Insert into Return rejection table

                $rrParams = array(
                    'cause' => $cause,
                    'returnId' => $returnId,
                );

                $this->Returnrejection_model->add_returnrejection($rrParams);

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

                }

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
            $response['message'] = 'No/Incomplete parameters';

        }

        $this->send_response($response);

    }

    /**
     * API to retrieve detail on NR
     */
    public function getCADBNReturn(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnId = $this->input->post('returnId');

            $returnOperationService = new ReturnOperationService();
            $getResponse = $returnOperationService->getReturningTransaction($returnId);

            // Verify response

            if($getResponse->success){

                $response['success'] = true;

                $response['data'] = $getResponse->returnTransaction;

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

        }else{

            $response['success'] = false;
            $response['message'] = 'No return id found';

        }

        $this->send_response($response);
    }

    /**
     * API to retieve all NRs from LDB
     */
    public function getLDBNumberReturns(){
        $response = [];

        $response['data'] = $this->Numberreturn_model->get_all_numberreturn();

        $this->send_response($response);
    }

    /**
     * API to retrieve all NRs from CADB
     */
    public function getCADBNumberReturns(){

        $response = [];

        $response['data'] = [];

        $returnOperationService = new ReturnOperationService();

        // Load ORDERED Rollbacks

        $currentNRResponse = $returnOperationService->getCurrentReturningTransactions(Operator::ORANGE_NETWORK_ID);

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
