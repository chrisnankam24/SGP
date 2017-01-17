<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Rollback.php";
require_once APPPATH . "controllers/cadb/RollbackOperationService.php";

class Rollback extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

    }

    /**
     * TODO: OK
     * API for performing open request
     */
    public function openRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $originalPortingId = $this->input->post('originalPortingId');
            $temporalNumber = $this->input->post('temporalNumber');
            $language = $this->input->post('language'); // EN or FR
            $userId = $this->input->post('userId');

            $rollbackOperationService = new RollbackOperationService();

            $response = $rollbackOperationService->makeOpen($originalPortingId, $temporalNumber, $language, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No parameter received';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing bulk open request
     */
    public function openBulkRollback(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');
            $userId = $this->input->post('userId');

            if($file_name != ''){
                $row = 1;

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    $response['success'] = true;

                    $tmpData = [];

                    $rollbackOperationService = new RollbackOperationService();

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
                            if(strtolower($data[2]) != 'language'){
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

                            $originalPortingId = $data[0]; // originalPortingId
                            $temporalNumber = $data[1]; // temporalNumber
                            $language = $data[2]; // language

                            $tempResponse = $rollbackOperationService->makeOpen($originalPortingId, $temporalNumber, $language, $userId);
                            $tempResponse['temporalNumber'] = $temporalNumber;

                            $tmpData[] = $tempResponse;

                        }
                    }

                    $response['data'] = $tmpData;

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
     * TODO: OK
     * API for performing accept request
     */
    public function acceptRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackId = $this->input->post('rollbackId');
            $userId = $this->input->post('userId');

            $rollbackOperationService = new RollbackOperationService();

            $response = $rollbackOperationService->makeAccept($rollbackId, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing bulk accept
     */
    public function acceptBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform accept one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = $this->input->post('rollbackData'); // Array of rollbackIds
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $rollbackOperationService = new RollbackOperationService();

            foreach ($rollbackData as $rollbackId){

                $tmpResponse = $rollbackOperationService->makeAccept($rollbackId, $userId);
                $tmpResponse['rollbackId'] = $rollbackId;
                $response['data'][] = $tmpResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing reject request
     */
    public function rejectRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackId = $this->input->post('rollbackId');
            $rejectionReason = $this->input->post('rejectionReason');
            $cause = $this->input->post('cause');
            $userId = $this->input->post('userId');

            $rollbackOperationService = new RollbackOperationService();

            $response = $rollbackOperationService->makeReject($rollbackId, $rejectionReason, $cause, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No rollback id found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing bulk reject
     */
    public function rejectBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform reject one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = $this->input->post('rollbackData'); // Array of rejection objects i.e (rollbackId, rejectionReason, cause)
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $rollbackOperationService = new RollbackOperationService();

            foreach ($rollbackData as $rollbackDatum){

                $tmpResponse = $rollbackOperationService->makeReject($rollbackDatum['rollbackId'], $rollbackDatum['rejectionReason'], $rollbackDatum['cause'], $userId);
                $tmpResponse['rollbackId'] = $rollbackDatum['rollbackId'];
                $response['data'][] = $tmpResponse;

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

}
