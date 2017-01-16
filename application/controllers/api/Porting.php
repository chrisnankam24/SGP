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

                if(strtolower($donorOperator) == 'mtn'){
                    $donorOperator = 0;
                }elseif (strtolower($donorOperator) == 'nexttel'){
                    $donorOperator = 1;
                }else{
                    $tempResponse['success'] = false;
                    $tempResponse['message'] = "Invalid donor operator. Must be <MTN> or <NEXTTEL>";
                }

                if($donorOperator == 0 || $donorOperator == 1){

                    $portingOperationService = new PortingOperationService();

                    $orderResponse = $portingOperationService->orderPort($donorOperator, $portingMsisdn, $subscriberType, $rio, $documentType, $physicalPersonFirstName,
                        $physicalPersonLastName, $physicalPersonIdNumber, $legalPersonName, $legalPersonTin,
                        $contactNumber, $temporalNumber, $contractId, $language);

                    $response = $orderResponse;
                }

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
                            if(strtolower($data[0]) != 'donoroperator'){
                                $errorFound = true;
                            }
                            if(strtolower($data[1]) != 'portingmsisdn'){
                                $errorFound = true;
                            }
                            if(strtolower($data[2]) != 'rio'){
                                $errorFound = true;
                            }
                            if(strtolower($data[3]) != 'documentType'){
                                $errorFound = true;
                            }
                            if(strtolower($data[4]) != 'firstname'){
                                $errorFound = true;
                            }
                            if(strtolower($data[5]) != 'lastname'){
                                $errorFound = true;
                            }
                            if(strtolower($data[6]) != 'idnumber'){
                                $errorFound = true;
                            }
                            if(strtolower($data[7]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if(strtolower($data[8]) != 'language'){
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
                            $bscsOperationService = new BscsOperationService();
                            $contractId = $bscsOperationService->getContractId($temporalNumber);

                            if($contractId == -1){

                                $tempResponse['success'] = false;
                                $tempResponse['message'] = 'Connection to BSCS Unsuccessful. Please try again later';

                            }elseif($contractId == null){

                                $tempResponse['success'] = false;
                                $tempResponse['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';

                            }else{

                                if(strtolower($donorOperator) == 'mtn'){
                                    $donorOperator = 0;
                                }elseif (strtolower($donorOperator) == 'nexttel'){
                                    $donorOperator = 1;
                                }else{
                                    $tempResponse['success'] = false;
                                    $tempResponse['message'] = "Invalid donor operator. Must be <MTN> or <NEXTTEL>";
                                }

                                if($donorOperator == 0 || $donorOperator == 1){

                                    $tempResponse = $portingOperationService->orderPort($donorOperator, $portingMSISDN, $subscriberType, $rio, $documentType, $physicalPersonFirstName,
                                        $physicalPersonLastName, $physicalPersonIdNumber, null, null,
                                        null, $temporalNumber, $contractId, $language);
                                    $tempResponse['portingMSISDN'] = $portingMSISDN;
                                }

                            }

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
     * API for performing bulk order request for enterprises
     */
    public function orderEnterpriseBulkPorting(){

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');

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
                            if(strtolower($data[0]) != 'donoroperator'){
                                $errorFound = true;
                            }
                            if(strtolower($data[1]) != 'portingmsisdn'){
                                $errorFound = true;
                            }
                            if(strtolower($data[2]) != 'rio'){
                                $errorFound = true;
                            }
                            if(strtolower($data[3]) != 'documentType'){
                                $errorFound = true;
                            }
                            if(strtolower($data[4]) != 'legalname'){
                                $errorFound = true;
                            }
                            if(strtolower($data[5]) != 'legaltin'){
                                $errorFound = true;
                            }
                            if(strtolower($data[6]) != 'contactnumber'){
                                $errorFound = true;
                            }
                            if(strtolower($data[7]) != 'temporalnumber'){
                                $errorFound = true;
                            }
                            if(strtolower($data[8]) != 'language'){
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

                                $tempResponse['success'] = false;
                                $tempResponse['message'] = 'Connection to BSCS Unsuccessful. Please try again later';

                            }elseif($contractId == null){

                                $tempResponse['success'] = false;
                                $tempResponse['message'] = 'Temporal number not found in BSCS. Please verify number has been identified properly and try again';

                            }else{

                                if(strtolower($donorOperator) == 'mtn'){
                                    $donorOperator = 0;
                                }elseif (strtolower($donorOperator) == 'nexttel'){
                                    $donorOperator = 1;
                                }else{
                                    $tempResponse['success'] = false;
                                    $tempResponse['message'] = "Invalid donor operator. Must be <MTN> or <NEXTTEL>";
                                }

                                if($donorOperator == 0 || $donorOperator == 1){

                                    $tempResponse = $portingOperationService->orderPort($donorOperator, $portingMSISDN, $subscriberType, $rio, $documentType, null,
                                        null, null,$legalPersonName, $legalPersonTin, $contactNumber,
                                        $temporalNumber, $contractId, $language);

                                    $tempResponse['portingMSISDN'] = $portingMSISDN;

                                }

                            }

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
    public function acceptPorting(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');

            $portingOperationService = new PortingOperationService();

            $response = $portingOperationService->acceptPort($portingId);

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

            $portingData = $this->input->post('portingData'); // Array of portingIds

            $portingOperationService = new PortingOperationService();

            $response['success'] = true;
            $response['data'] = [];

            foreach ($portingData as $portingId){

                $tmpResponse = $portingOperationService->acceptPort($portingId);
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

            $portingOperationService = new PortingOperationService();

            $response = $portingOperationService->rejectPort($portingId, $rejectionReason, $cause);

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

            $portingData = $this->input->post('portingData'); // Array of rejection objects i.e (portingId, rejectionReason, cause)

            $response['success'] = true;
            $response['data'] = [];

            $portingOperationService = new PortingOperationService();

            foreach ($portingData as $portingDatum){

                $tmpResponse = $portingOperationService->rejectPort($portingDatum['portingId'], $portingDatum['rejectionReason'], $portingDatum['cause']);
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
     * API to retrieve detail on porting
     */
    public function getCADBPorting(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');

            $portingOperationService = new PortingOperationService();
            $getResponse = $portingOperationService->getPorting($portingId);

            // Verify response

            if($getResponse->success){

                $response['success'] = true;

                $response['data'] = $getResponse->portingTransaction;

            }

            else{

                $fault = $getResponse->error;

                $response['success'] = false;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:
                    case Fault::PORTING_ACTION_NOT_AVAILABLE:
                    case Fault::INVALID_PORTING_ID:
                    case Fault::INVALID_REQUEST_FORMAT:
                    default:
                        $response['message'] = 'Error from CADB';

                }


            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);
    }

    /**
     * API to retieve all portings from LDB
     */
    public function getLDBPortings(){

        $response = [];

        $response['data'] = $this->Porting_model->get_all_porting();

        $this->send_response($response);
    }

    /**
     * API to retrieve all portings from CADB
     */
    public function getCADBPortings(){

        $response = [];

        $response['data'] = [];

        $portingOperationService = new PortingOperationService();

        // Load ORDERED Portings

        $orderedResponse = $portingOperationService->getOrderedPortings(Operator::ORANGE_NETWORK_ID);

        if($orderedResponse->success){

            $response['data'] = array_merge($response['data'], $orderedResponse->portingTransactions);

        }
        else{

            $fault = $orderedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_ORDERED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load APPROVED Portings

        $approvedResponse = $portingOperationService->getApprovedPortings(Operator::ORANGE_NETWORK_ID);

        if($approvedResponse->success){

            $response['data'] = array_merge($response['data'], $approvedResponse->portingTransactions);

        }
        else{

            $fault = $approvedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_APPROVED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load ACCEPTED Portings

        $acceptedResponse = $portingOperationService->getAcceptedPortings(Operator::ORANGE_NETWORK_ID);

        if($acceptedResponse->success){

            $response['data'] = array_merge($response['data'], $acceptedResponse->portingTransactions);

        }
        else{

            $fault = $acceptedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_ACCEPTED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load CONFIRMED Portings

        $confirmedResponse = $portingOperationService->getConfirmedPortings(Operator::ORANGE_NETWORK_ID);

        if($confirmedResponse->success){

            $response['data'] = array_merge($response['data'], $confirmedResponse->portingTransactions);

        }
        else{

            $fault = $confirmedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_CONFIRMED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load DENIED Portings

        $deniedResponse = $portingOperationService->getDeniedPortings(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

        if($deniedResponse->success){

            $response['data'] = array_merge($response['data'], $deniedResponse->portingTransactions);

        }
        else{

            $fault = $deniedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                case Fault::COUNT_OVER_MAX_COUNT_LIMIT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_DENIED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load REJECTED Portings

        $rejectedResponse = $portingOperationService->getRejectedPortings(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

        if($rejectedResponse->success){

            $response['data'] = array_merge($response['data'], $rejectedResponse->portingTransactions);

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
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_REJECTED_PORTINGS_FROM_CADB", []);
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
            // TODO: Include contact number and TIN

            $response['data'] = $responseData;

        }else{

            $response['success'] = false;

        }

        return $response;

    }

}
