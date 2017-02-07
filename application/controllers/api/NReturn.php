<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Return.php";
require_once APPPATH . "controllers/cadb/ReturnOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

require_once APPPATH . "third_party/PHPExcel/Classes/PHPExcel/IOFactory.php";

class NReturn extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * API for performing open request
     */
    public function openNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnMSISDN = $this->input->post('returnMSISDN');
            $returnOperator = $this->input->post('returnOperator'); // 0 == MTN, 1 == Nexttel
            $userId = $this->input->post('userId');

            $nrOperationService = new ReturnOperationService();

            $response = $nrOperationService->openReturn($returnMSISDN, $returnOperator, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No parameter received';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk open
     */
    public function openBulkNumberReturn(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');
            $userId = $this->input->post('userId');

            /*if($file_name != ''){
                $row = 1;

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    $response['success'] = true;

                    $tmpData = [];

                    $nrOperationService = new ReturnOperationService();

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){

                            // Check if header Ok
                            $errorFound = false;
                            if(isset($data[0]) && strtolower($data[0]) != 'returnmsisdn'){
                                $errorFound = true;
                            }
                            if(isset($data[1]) && strtolower($data[1]) != 'returnoperator'){
                                $errorFound = true;
                            }

                            if($errorFound){
                                $response['success'] = false;
                                $response['message'] = 'Invalid file content format. Columns do not match defined template. If you have difficulties creating file, please contact administrator';

                                $this->send_response($response);

                                //unlink(FCPATH . 'uploads/' .$file_name);

                                return;
                            }
                            $row++;
                        }else{

                            $tempResponse = [];

                            $returnMSISDN = $data[0]; // returnMSISDN
                            $returnOperator = $data[1]; // returnOperator

                            if(strtolower($returnOperator) == 'mtn'){
                                $returnOperator = 0;
                            }elseif (strtolower($returnOperator) == 'nexttel'){
                                $returnOperator = 1;
                            }else{
                                $tempResponse['success'] = false;
                                $tempResponse['message'] = "Invalid return operator. Must be <MTN> or <NEXTTEL>";
                                $tempResponse['returnMSISDN'] = $returnMSISDN;
                            }

                            if($returnOperator == 0 || $returnOperator == 1){

                                $tempResponse = $nrOperationService->openReturn($returnMSISDN, $returnOperator, $userId);
                                $tempResponse['returnMSISDN'] = $returnMSISDN;

                                $tmpData[] = $tempResponse;

                            }
                        }
                    }

                    $response['data'] = $tmpData;

                    fclose($handle);

                    //unlink(FCPATH . 'uploads/' .$file_name);
                }

            }
            else{
                $response['success'] = false;
                $response['message'] = 'No file name found';
            }*/

           $fileObject = PHPExcel_IOFactory::load(FCPATH . 'uploads/' .$file_name);

            if($fileObject){

                $sheetData = $fileObject->getActiveSheet()->toArray();

                $row = 1;
                $response['success'] = true;

                $tmpData = [];

                $nrOperationService = new ReturnOperationService();

                foreach ($sheetData as $sheetDatum){

                    if($row == 1){
                        // Check if header Ok
                        $errorFound = false;
                        if(isset($sheetDatum[0]) && strtolower($sheetDatum[0]) != 'returnmsisdn'){
                            $errorFound = true;
                        }
                        if(isset($sheetDatum[1]) && strtolower($sheetDatum[1]) != 'returnoperator'){
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

                    }
                    else{

                        $tempResponse = [];

                        $returnMSISDN = $sheetDatum[0]; // returnMSISDN
                        $returnOperator = $sheetDatum[1]; // returnOperator

                        if(strtolower($returnOperator) == 'mtn'){
                            $returnOperator = 0;
                        }elseif (strtolower($returnOperator) == 'nexttel'){
                            $returnOperator = 1;
                        }else{
                            $tempResponse['success'] = false;
                            $tempResponse['message'] = "Invalid return operator. Must be <MTN> or <NEXTTEL>";
                            $tempResponse['returnMSISDN'] = $returnMSISDN;
                        }

                        if($returnOperator == 0 || $returnOperator == 1){

                            $tempResponse = $nrOperationService->openReturn($returnMSISDN, $returnOperator, $userId);
                            $tempResponse['returnMSISDN'] = $returnMSISDN;

                            $tmpData[] = $tempResponse;
                            $tempResponse['msisdn'] = $returnMSISDN;

                        }

                    }

                }

                $response['data'] = $tmpData;

                unlink(FCPATH . 'uploads/' .$file_name);

            }else{

                $response['success'] = false;
                $response['message'] = 'File not supported or found';

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
    public function acceptNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnId = $this->input->post('returnId');
            $userId = $this->input->post('userId');

            $nrOperationService = new ReturnOperationService();

            $response = $nrOperationService->acceptReturn($returnId, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No ReturnId found';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk accept
     */
    public function acceptBulkNumberReturn(){

        // Receives list of return IDs linked to enterprise and perform accept one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $returnData = json_decode($this->input->post('returnData')); // Array of returnIds
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $nrOperationService = new ReturnOperationService();

            foreach ($returnData as $returnId){

                $tmpResponse = $nrOperationService->acceptReturn($returnId, $userId);
                $tmpResponse['returnId'] = $returnId;
                $response['data'][] = $tmpResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

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
            $userId = $this->input->post('userId');

            $nrOperationService = new ReturnOperationService();

            $response = $nrOperationService->rejectReturn($returnId, $cause, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No/Incomplete parameters';

        }

        $this->send_response($response);

    }

    /**
     * API for performing bulk reject
     */
    public function rejectBulkNumberReturn(){

        // Receives list of reject IDs linked to enterprise and perform reject one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $rejectData = json_decode($this->input->post('rejectData')); // Array of rejection objects i.e (portingId, rejectionReason, cause)
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $nrOperationService = new ReturnOperationService();

            foreach ($rejectData as $rejectDatum){

                $tmpResponse = $nrOperationService->rejectReturn($rejectDatum['returnId'], $rejectDatum['cause'], $userId);
                $tmpResponse['returnId'] = $rejectDatum['returnId'];
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
    public function searchNumberReturn(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $searchMSISDN = $this->input->post('searchMSISDN');
            $userId = $this->input->post('userId');

            $nrOperationService = new ReturnOperationService();

            $response = $nrOperationService->searchReturn($searchMSISDN, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No search msisdn found';

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
