<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Fault.php";
require_once "BscsTypes.php";

use BscsService\BscsTypes as BscsTypes;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 1:06 PM
 */

/**
 * Simulating Controller for PortingOperationService made by CADB
 * Class PortingOperationService
 */
class BscsOperationService extends CI_Controller  {

    // Declare client
    private $contractClient = null;

    private $msisdnClient = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('FileLog_model');
        $this->load->model('BSCS_model');

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->contractClient = new SoapClient(__DIR__ . '/ContractManagementEndPoint.xml', array(
            "trace" => false
        ));

        // Define soap client object
        $this->msisdnClient = new SoapClient(__DIR__ . '/MSISDNManagementEndPoint.xml', array(
            "trace" => true
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/MSISDNManagementEndPoint.xml');

        // Set the class for the soap server
        $server->setClass("ServerFunctionalities");

        //$postdata = file_get_contents("php://input");

        //$this->FileLog_model->write_log('BSCS', 'BSCS', 'XML Received: ' . json_encode($postdata));

        // Handle soap operations
        $server->handle();

    }

    public function test() {
        $bscsOperationService = new BscsOperationService();
        //$temporalMSISDN = '694975166';
        //$response = $this->loadNumberInfo($temporalMSISDN);
        //var_dump($response);
    }

    /**
     * Loads BSCS info on MSISDN
     * @param $temporalNumber string msisdn of temporal number in BSCS
     * @return string
     */
    public function loadTemporalNumberInfo($temporalNumber){

        $subscriberInfo = $this->loadNumberInfo($temporalNumber);

        return $subscriberInfo;

    }

    /**
     * Loads BSCS info on MSISDN
     * @param $msisdn
     * @return string
     */
    public function loadNumberInfo($msisdn){

        $subscriberInfo = false;

        $subscriberInfo = $this->BSCS_model->get_msisdn_info($msisdn);

        return $subscriberInfo;
    }

    /**
     * Verifies if an MSISDN (particular Temporal MSISDN) is already active
     * @param $msisdn
     * @return bool
     */
    public function verifyActive($msisdn){
        return true;
    }

    /**
     * Returns contractId linked with given MSISDN
     * @param $msisdn
     */
    public function getContractId($msisdn){

        $contractId = -1;

        $subscriberInfo = $this->BSCS_model->get_msisdn_info($msisdn);

        if($subscriberInfo != null && $subscriberInfo != -1) {
            $contractId = $subscriberInfo['CONTRACT_ID'];
        }

        return $contractId;

    }

    /**
     * Activates MSISDN in BSCS
     * @param $msisdn
     * @return bool
     */
    public function activeMSISDN($msisdn){
        return true;
    }

    /**
     * Change temporalNumber to portedNumber in BSCS
     * @param $temporalMSISDN
     * @param $portingMSISDN
     * @return errorResponse
     */
    public function changeImportMSISDN($temporalMSISDN, $portingMSISDN){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ChangeImportMSISDN();

                $request->autoCommit = true;
                $request->MSISDN = $portingMSISDN;
                $request->MSISDN_TMP = $temporalMSISDN;
                $request->endUserName = '?';
                $request->CO_ID = '?';

                try {

                    $response = $this->msisdnClient->ChangeImportMSISDN($request);

                    $response->success = true;

                    $this->logoutMSISDN();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->error = $fault;

                    $this->logoutMSISDN();

                    return $response;

                }

            }else{

                return $logonResponse;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

    /**
     * Import portingNumber in BSCS
     * @param $portingMSISDN
     * @return errorResponse
     */
    public function importMSISDN($portingMSISDN){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ImportMSISDN();

                $request->autoCommit = true;
                $request->MSISDN = $portingMSISDN;
                $request->endUserName = '?';
                $request->SRC_PLCODE = '?';
                $request->CO_ID = '?';

                try {

                    $response = $this->msisdnClient->ImportMSISDN($request);

                    $response->success = true;

                    $this->logoutMSISDN();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->error = $fault;

                    $this->logoutMSISDN();

                    return $response;

                }

            }else{

                return $logonResponse;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

    /**
     * Return number to BSCS
     * @param $returnMSISDN
     * @return errorResponse
     */
    public function ReturnMSISDN($returnMSISDN){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ReturnMSISDN();

                $request->autoCommit = true;
                $request->endUserName = '?';
                $request->PHONE_NUMBER = $returnMSISDN;
                $request->NPCODE = '?';
                $request->SRC_PLCODE = '?';

                try {

                    $response = $this->msisdnClient->ReturnMSISDN($request);

                    $response->success = true;

                    $this->logoutMSISDN();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->error = $fault;

                    $this->logoutMSISDN();

                    return $response;

                }

            }else{

                return $logonResponse;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }


    }

    /**
     * Export MSISDN from BSCS
     * @param $MSISDN
     * @return errorResponse
     */
    public function exportMSISDN($MSISDN){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                // Make ExportMSISDN request
                $request = new BscsTypes\ExportMSISDN();

                $request->autoCommit = true;
                $request->PHONE_NUMBER = $MSISDN;
                $request->endUserName = '?';
                $request->DEST_PLCODE = '?';
                $request->NPCODE = '?';

                try {

                    $response = $this->msisdnClient->ExportMSISDN($request);

                    $response->success = true;

                    $this->logoutMSISDN();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->error = $fault;

                    $this->logoutMSISDN();

                    return $response;

                }

            }else{

                return $logonResponse;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

    private function logonMSISDN(){

        // Make logon request
        $request = new BscsTypes\logon();

        $request->cmsUserName = BscsParams::cmsUserName;
        $request->cmsPassword = BscsParams::cmsPassword;
        $request->endUserName = BscsParams::endUserName;

        try {

            $response = $this->msisdnClient->logon($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->error = $fault;

            return $response;

        }
    }

    private function logoutMSISDN(){

        // Make logon request
        $request = new BscsTypes\logout();

        try {

            $response = $this->msisdnClient->logout($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->error = $fault;

            return $response;

        }

    }

    /*public function createContract(){

        if($this->contractClient) {

            // Make createContract request
            $request = new BscsTypes\createContract();

            try {

                $response = $this->contractClient->createContract($request);

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

    public function updateContractStatus(){

        if($this->contractClient) {

            // Make updateContractStatus request
            $request = new BscsTypes\updateContractStatus();

            try {

                $response = $this->contractClient->updateContractStatus($request);

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

    public function transfertContract(){

        if($this->contractClient) {

            // Make transfertContract request
            $request = new BscsTypes\transfertContract();

            try {

                $response = $this->contractClient->transfertContract($request);

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

    public function consultContract(){

        if($this->contractClient) {

            // Make consultContract request
            $request = new BscsTypes\consultContract();

            try {

                $response = $this->contractClient->consultContract($request);

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

    }*/

    /**
     * Deletes contract from BSCS
     * @param $contractId
     * @return errorResponse
     */
    public function deleteContract($contractId){

        if($this->contractClient) {

            $logonResponse = $this->logonContract();

            if($logonResponse->success){

                // Make deleteContract request
                $request = new BscsTypes\deleteContract();

                $request->contractId = $contractId;

                try {

                    $response = $this->contractClient->deleteContract($request);

                    $response->success = true;

                    $this->logoutContract();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->error = $fault;

                    $this->logoutContract();

                    return $response;

                }

            }else{

                return $logonResponse;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

    private function logonContract(){

        // Make logon request
        $request = new BscsTypes\logon();

        $request->cmsUserName = BscsParams::cmsUserName;
        $request->cmsPassword = BscsParams::cmsPassword;
        $request->endUserName = BscsParams::endUserName;

        try {

            $response = $this->contractClient->logon($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->error = $fault;

            return $response;

        }
    }

    private function logoutContract(){

        // Make logon request
        $request = new BscsTypes\logout();

        try {

            $response = $this->contractClient->logout($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->error = $fault;

            return $response;

        }

    }

}

class ServerFunctionalities {

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $ImportMSISDNRequest
     * @return BscsTypes\ImportMSISDNResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function ImportMSISDN($ImportMSISDNRequest){
        $response = new BscsTypes\ImportMSISDNResponse();
        return $response;
    }

    /**
     * @param $ChangeImportMSISDNRequest
     * @return BscsTypes\ChangeImportMSISDNResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function ChangeImportMSISDN($ChangeImportMSISDNRequest){
        $response = new BscsTypes\ChangeImportMSISDNResponse();
        return $response;
    }

    /**
     * @param $ReturnMSISDNRequest
     * @return BscsTypes\ReturnMSISDNResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function ReturnMSISDN($ReturnMSISDNRequest){
        $response = new BscsTypes\ReturnMSISDNResponse();
        return $response;
    }

    /**
     * @param $ExportMSISDNRequest
     * @return BscsTypes\ExportMSISDNResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function ExportMSISDN($ExportMSISDNRequest) {
        $response = new BscsTypes\ExportMSISDNResponse();
        return $response;
    }

    /**
     * @param $logonRequest
     * @return BscsTypes\logonResponse
     * @throws ServiceBreakDownFault
     * @throws PostConnectInitializationFault
     * @throws DeniedAccessFault
     * @throws ServerNotFoundFault
     */
    public function logon($logonRequest){
        $response = new BscsTypes\logonResponse();
        return $response;
    }

    /**
     * @param $logoutRequest
     * @return BscsTypes\logoutResponse
     * @throws ServiceBreakDownFault
     * @throws DeniedAccessFault
     */
    public function logout($logoutRequest){
        $response = new BscsTypes\logoutResponse();
        return $response;
    }

    /**
     * @param $createContractRequest
     * @return BscsTypes\createContractResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function createContract($createContractRequest){
        $response  = new BscsTypes\createContractResponse();
        return $response;
    }

    /**
     * @param $updateContractStatusRequest
     * @return BscsTypes\updateContractStatusResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function updateContractStatus($updateContractStatusRequest){
        $response = new BscsTypes\updateContractStatusResponse();
        return $response;
    }

    /**
     * @param $transfertContractRequest
     * @return BscsTypes\transfertContractResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function transfertContract($transfertContractRequest){
        $response = new BscsTypes\transfertContractResponse();
        return $response;
    }

    /**
     * @param $consultContractRequest
     * @return BscsTypes\consultContractResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function consultContract($consultContractRequest){
        $response = new BscsTypes\consultContractResponse();
        return $response;
    }

    /**
     * @param $deleteContractRequest
     * @return BscsTypes\deleteContractResponse
     * @throws ServiceBreakDownFault
     * @throws SignatureMismatchException
     * @throws DeniedAccessFault
     * @throws UnknownCommandFault
     * @throws InvalidParameterTypeException
     * @throws ParameterListException
     * @throws CMSExecutionFault
     */
    public function deleteContract($deleteContractRequest){
        $response = new BscsTypes\deleteContractResponse();
        return $response;
    }

}