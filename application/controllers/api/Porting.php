<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Porting.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/cadb/PortingOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use \PortingService\Porting\rejectionReasonType as rejectionReasonType;

class Porting extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        // Load required models

    }

    /**
     * TODO: OK
     * API for retrieving BSCS info linked to temporal number
     */
    public function numberDetails(){

        $response = [];

        if(isset($_POST)) {

            $temporalNumber = $this->input->post('temporalNumber'); // Without 237 prepended

            $response = $this->numberInfo($temporalNumber);

        }else{

            $response['success'] = false;
            $response['message'] = 'No temporal number found';

        }

        $this->send_response($response);
    }

    /**
     * TODO: OK
     * API for performing order request. This is the individual Endpoint
     */
    public function orderPorting() {

        $response = [];

        if(isset($_POST)) {

            // Retrieve POST params

            $donorOperator = $this->input->post('donorOperator'); // 0 == MTN, 1 == Nexttel
            $portingMsisdn = $this->input->post('portingMsisdn');
            $subscriberType = $this->input->post('subscriberType'); // 0 == Person, 1 == Enterprise
            $rio = $this->input->post('rio');
            $documentType = $this->input->post('documentType');
            $physicalPersonFirstName = $this->input->post('physicalPersonFirstName');
            $physicalPersonLastName = $this->input->post('physicalPersonLastName');
            $physicalPersonIdNumber = $this->input->post('physicalPersonIdNumber');
            $legalPersonName = $this->input->post('legalPersonName');
            $legalPersonTin = $this->input->post('legalPersonTin');
            $contactNumber = $this->input->post('contactNumber');
            $temporalNumber = $this->input->post('temporalNumber');
            $language = $this->input->post('language'); // EN or FR
            $userId = $this->input->post('userId'); // EN or FR

            $portingDateTime = $this->input->post('portingDateTime');

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

                $portingOperationService = new PortingOperationService();

                $orderResponse = $portingOperationService->orderPort($donorOperator, $portingMsisdn, $subscriberType, $rio, $documentType, $physicalPersonFirstName,
                    $physicalPersonLastName, $physicalPersonIdNumber, $legalPersonName, $legalPersonTin,
                    $contactNumber, $temporalNumber, $contractId, $language, $portingDateTime, $userId);

                $response = $orderResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No/Incomplete information submitted';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing bulk order request for individuals
     */
    public function orderIndividualBulkPorting(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');
            $portingDateTime = $this->input->post('portingDateTime');
            $userId = $this->input->post('userId');

            if($file_name != ''){
                $row = 1;

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    $response['success'] = true;

                    $tmpData = [];

                    $portingOperationService = new PortingOperationService();

                    $bscsOperationService = new BscsOperationService();

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){
                            // Check if header Ok
                            $errorFound = false;
                            if(isset($data[0]) && strtolower($data[0]) != 'donoroperator'){
                                $errorFound = true;
                            }
                            if(isset($data[1]) && strtolower($data[1]) != 'portingmsisdn'){
                                $errorFound = true;
                            }
                            if(isset($data[2]) && strtolower($data[2]) != 'rio'){
                                $errorFound = true;
                            }
                            if(isset($data[3]) && strtolower($data[3]) != 'documenttype'){
                                $errorFound = true;
                            }
                            if(isset($data[4]) && strtolower($data[4]) != 'firstname'){
                                $errorFound = true;
                            }
                            if(isset($data[5]) && strtolower($data[5]) != 'lastname'){
                                $errorFound = true;
                            }
                            if(isset($data[6]) && strtolower($data[6]) != 'idnumber'){
                                $errorFound = true;
                            }
                            if(isset($data[7]) && strtolower($data[7]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if(isset($data[8]) && strtolower($data[8]) != 'language'){
                                $errorFound = true;
                            }
                            if($errorFound){
                                $response['success'] = false;
                                $response['message'] = 'Invalid file content format. Columns do not match defined template. If you have difficulties creating file, please contact administrator';

                                $this->send_response($response);
                                return;
                            }
                            $row++;
                        }
                        else{

                            $tempResponse = [];

                            $donorOperator = $data[0]; // Donor Operator, either MTN or NEXTTEL
                            $portingMSISDN = $data[1]; // MSISDN to port
                            $rio = $data[2]; // RIO
                            $documentType = $data[3]; // documentType
                            $physicalPersonFirstName = $data[4]; // FirstName
                            $physicalPersonLastName = $data[5]; // lastName
                            $physicalPersonIdNumber = $data[6]; // idNumber
                            $temporalNumber = $data[7]; // temporalNumber
                            $language = $data[8]; // language

                            $subscriberType = 0; // Physical person

                            // Get subscriber contractId from BSCS with temporal MSISDN
                            $contractId = $bscsOperationService->getContractId($temporalNumber);

                            if($contractId == -1){

                                $response['success'] = false;
                                $response['message'] = 'Connection to BSCS Unsuccessful. Please try again later';
                                $response['portingMSISDN'] = $portingMSISDN;

                            }elseif($contractId == null){

                                $response['success'] = false;
                                $response['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';
                                $response['portingMSISDN'] = $portingMSISDN;

                            }else{

                                if(strtolower($donorOperator) == 'mtn'){
                                    $donorOperator = 0;
                                }elseif (strtolower($donorOperator) == 'nexttel'){
                                    $donorOperator = 1;
                                }else{
                                    $response['success'] = false;
                                    $response['message'] = "Invalid donor operator. Must be <MTN> or <NEXTTEL>";
                                }

                                if($donorOperator == 0 || $donorOperator == 1){

                                    $tempResponse = $portingOperationService->orderPort($donorOperator, $portingMSISDN, $subscriberType, $rio, $documentType, $physicalPersonFirstName,
                                        $physicalPersonLastName, $physicalPersonIdNumber, null, null,
                                        null, $temporalNumber, $contractId, $language, $portingDateTime, $userId);
                                    $tempResponse['portingMSISDN'] = $portingMSISDN;
                                }

                            }

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
     * TODO: OK
     * API for performing bulk order request for enterprises
     */
    public function orderEnterpriseBulkPorting(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');
            $portingDateTime = $this->input->post('portingDateTime');
            $userId = $this->input->post('userId');

            if($file_name != ''){
                $row = 1;

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    $response['success'] = true;

                    $tmpData = [];

                    $portingOperationService = new PortingOperationService();

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){
                            // Check if header Ok
                            $errorFound = false;
                            if(isset($data[0]) && strtolower($data[0]) != 'donoroperator'){
                                $errorFound = true;
                            }
                            if(isset($data[1]) && strtolower($data[1]) != 'portingmsisdn'){
                                $errorFound = true;
                            }
                            if(isset($data[2]) && strtolower($data[2]) != 'rio'){
                                $errorFound = true;
                            }
                            if(isset($data[3]) && strtolower($data[3]) != 'documenttype'){
                                $errorFound = true;
                            }
                            if(isset($data[4]) && strtolower($data[4]) != 'legalname'){
                                $errorFound = true;
                            }
                            if(isset($data[5]) && strtolower($data[5]) != 'legaltin'){
                                $errorFound = true;
                            }
                            if(isset($data[6]) && strtolower($data[6]) != 'contactnumber'){
                                $errorFound = true;
                            }
                            if(isset($data[7]) && strtolower($data[7]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if(isset($data[8]) && strtolower($data[8]) != 'language'){
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

                            $donorOperator = $data[0]; // Donor Operator, either MTN or NEXTTEL
                            $portingMSISDN = $data[1]; // MSISDN to port
                            $rio = $data[2]; // RIO
                            $documentType = $data[3]; // Document Type
                            $legalPersonName = $data[4]; // legalName
                            $legalPersonTin = $data[5]; // legalTIN
                            $contactNumber = $data[6]; // contactNumber
                            $temporalNumber = $data[7]; // temporalNumber
                            $language = $data[8]; // language

                            $subscriberType = 1; // legal person

                            // Get subscriber contractId from BSCS with temporal MSISDN
                            $bscsOperationService = new BscsOperationService();
                            $contractId = $bscsOperationService->getContractId($temporalNumber);

                            if($contractId == -1){

                                $response['success'] = false;
                                $response['message'] = 'Connection to BSCS Unsuccessful. Please try again later';
                                $response['portingMSISDN'] = $portingMSISDN;

                            }elseif($contractId == null){

                                $response['success'] = false;
                                $response['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';
                                $response['portingMSISDN'] = $portingMSISDN;

                            }else{

                                if(strtolower($donorOperator) == 'mtn'){
                                    $donorOperator = 0;
                                }elseif (strtolower($donorOperator) == 'nexttel'){
                                    $donorOperator = 1;
                                }else{
                                    $response['success'] = false;
                                    $response['message'] = "Invalid donor operator. Must be <MTN> or <NEXTTEL>";
                                    $response['portingMSISDN'] = $portingMSISDN;
                                }

                                if($donorOperator == 0 || $donorOperator == 1){

                                    $tempResponse = $portingOperationService->orderPort($donorOperator, $portingMSISDN, $subscriberType, $rio, $documentType, null,
                                        null, null,$legalPersonName, $legalPersonTin, $contactNumber,
                                        $temporalNumber, $contractId, $language, $portingDateTime, $userId);

                                    $tempResponse['portingMSISDN'] = $portingMSISDN;

                                }

                            }

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
     * TODO: OK
     * API for performing accept request
     */
    public function acceptPorting(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');
            $userId = $this->input->post('userId');

            $portingOperationService = new PortingOperationService();

            $response = $portingOperationService->acceptPort($portingId, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing accept request for bulk
     */
    public function acceptBulkPorting(){

        // Receives list of porting IDs linked to enterprise and perform accept one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingData = json_decode($this->input->post('portingData')); // Array of portingIds
            $userId = $this->input->post('userId');

            $portingOperationService = new PortingOperationService();

            $response['success'] = true;
            $response['data'] = [];

            foreach ($portingData as $portingId){

                $tmpResponse = $portingOperationService->acceptPort($portingId, $userId);
                $tmpResponse['portingId'] = $portingId;
                $response['data'][] = $tmpResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting data found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing reject request
     */
    public function rejectPorting(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');
            $rejectionReason = $this->input->post('rejectionReason');
            $cause = $this->input->post('cause');
            $userId = $this->input->post('userId');

            $portingOperationService = new PortingOperationService();

            $response = $portingOperationService->rejectPort($portingId, $rejectionReason, $cause, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);
    }

    /**
     * TODO: OK
     * API for performing bulk reject request
     */
    public function rejectBulkPorting(){

        // Receives list of porting IDs linked to enterprise and perform reject one after the other
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingData = json_decode($this->input->post('portingData')); // Array of rejection objects i.e (portingId, rejectionReason, cause)
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $portingOperationService = new PortingOperationService();

            foreach ($portingData as $portingDatum){

                $tmpResponse = $portingOperationService->rejectPort($portingDatum['portingId'], $portingDatum['rejectionReason'], $portingDatum['cause'], $userId);
                $tmpResponse['portingId'] = $portingDatum['portingId'];
                $response['data'][] = $tmpResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting data found';

        }

        $this->send_response($response);

    }

    /**
     * TODO: OK
     * API for performing search
     */
    public function searchPorting(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $searchMSISDN = $this->input->post('searchMSISDN');
            $userId = $this->input->post('userId');

            $portingOperationService = new PortingOperationService();

            $response = $portingOperationService->searchPort($searchMSISDN, $userId);

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

    // Utility Functions

    /**
     * Retrieves info on number from BSCS
     * @param $msisdn
     * @return array
     */
    private function numberInfo($msisdn){

        $response = [];

        // Load temporal number info from BSCS
        $bscsOperationService = new BscsOperationService();
        $data = $bscsOperationService->loadTemporalNumberInfo($msisdn);

        // Number in BSCS
        if($data){

            $response['success'] = true;

            $responseData = [];

            $responseData['msisdn'] = $data['MSISDN'];
            $responseData['contract_id'] = $data['CONTRACT_ID'];
            $responseData['type_client'] = $data['TYPE_CLIENT'];
            $responseData['nom'] = $data['NOM'];
            $responseData['prenom'] = $data['PRENOM'];
            $responseData['id_piece'] = $data['ID_PIECE'];

            $responseData['ste'] = $data['STE'];
            $responseData['num_registre'] = $data['NUM_REGISTRE'];
            // TODO: Include contact number and TIN

            $response['data'] = $responseData;

        }else{

            $response['success'] = false;

        }

        return $response;

    }

}
