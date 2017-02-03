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
     * API for performing open request
     */
    public function openRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $originalPortingId = $this->input->post('originalPortingId');
            $temporalNumber = $this->input->post('temporalNumber');
            $userId = $this->input->post('userId');

            $rollbackOperationService = new RollbackOperationService();

            $response = $rollbackOperationService->makeOpen($originalPortingId, $temporalNumber, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No parameter received';

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
                            if(isset($data[0]) && strtolower($data[0]) != 'originalportingid'){
                                $errorFound = true;
                            }
                            if(isset($data[1]) && strtolower($data[1]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if($errorFound){
                                $response['success'] = false;
                                $response['message'] = 'Invalid file content format. Columns do not match defined template. If you have difficulties creating file, please contact administrator';

                                $this->send_response($response);

                                unlink(FCPATH . 'uploads/' .$file_name);

                                return;
                            }
                            $row++;
                        }else{

                            $originalPortingId = $data[0]; // originalPortingId
                            $temporalNumber = $data[1]; // temporalNumber

                            $tempResponse = $rollbackOperationService->makeOpen($originalPortingId, $temporalNumber, $userId);
                            $tempResponse['temporalNumber'] = $temporalNumber;

                            $tmpData[] = $tempResponse;

                        }
                    }

                    $response['data'] = $tmpData;

                    fclose($handle);

                    unlink(FCPATH . 'uploads/' .$file_name);

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
     * API for performing bulk accept
     */
    public function acceptBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform accept one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = json_decode($this->input->post('rollbackData')); // Array of rollbackIds
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
     * API for performing bulk reject
     */
    public function rejectBulkRollback(){

        // Receives list of rollback IDs linked to enterprise and perform reject one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rollbackData = json_decode($this->input->post('rollbackData')); // Array of rejection objects i.e (rollbackId, rejectionReason, cause)
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
     * API for performing search
     */
    public function searchRollback(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $searchMSISDN = $this->input->post('searchMSISDN');
            $userId = $this->input->post('userId');

            $rollbackOperationService = new RollbackOperationService();

            $response = $rollbackOperationService->searchRollback($searchMSISDN, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No search msisdn found';

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
