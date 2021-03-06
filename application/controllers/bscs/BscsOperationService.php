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
 * BscsOperationService
 * Class PortingOperationService
 */
class BscsOperationService {

    // Declare client
    private $contractClient = null;

    private $msisdnClient = null;

    private $BSCS_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $CI->load->model('BSCS_model');

        $this->BSCS_model = $CI->BSCS_model;

        // Disable wsdl_1_4 cache
        ini_set("soap.wsdl_cache_enabled", "0");

        libxml_disable_entity_loader(false);

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

        // Handle soap operations
        $server->handle();

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

        if(strlen($msisdn) == 12){

            $msisdn = substr($msisdn, 3);

        }

        $subscriberInfo = $this->BSCS_model->get_msisdn_info($msisdn);

        if($subscriberInfo != null && $subscriberInfo != -1) {

            $contractId = $subscriberInfo['CONTRACT_ID'];

        }elseif($subscriberInfo == null){

            $contractId = null;

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
    public function changeImportMSISDN($temporalMSISDN, $portingMSISDN, $contractId){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                if(strlen($temporalMSISDN) == 12){
                    $temporalMSISDN = substr($temporalMSISDN, 3);
                }

                if(strlen($portingMSISDN) == 12){
                    $portingMSISDN = substr($portingMSISDN, 3);
                }

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ChangeImportMSISDN();

                $request->autoCommit = true;
                $request->MSISDN = $portingMSISDN;
                $request->MSISDN_TMP = $temporalMSISDN;
                $request->endUserName = BscsParams::endUserName;
                $request->CO_ID = $contractId;

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

                    $response->message = $e->detail->$fault->reason;

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
    public function importMSISDN($portingMSISDN, $sourceOperatorId){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                if(strlen($portingMSISDN) == 12){
                    $portingMSISDN = substr($portingMSISDN, 3);
                }

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ImportMSISDN();

                $request->autoCommit = true;
                $request->MSISDN = $portingMSISDN;
                $request->endUserName = BscsParams::cmsUserName;
                $request->NPCODE = 1;
                $request->HMCODE = BscsParams::HMCODE;

                if($sourceOperatorId == Operator::MTN_NETWORK_ID){
                    $request->SRC_PLCODE = BscsParams::MTN_PLCODE;
                }else{
                    $request->SRC_PLCODE = BscsParams::NEXTTEL_PLCODE;
                }

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

                    $response->message = $e->detail->$fault->reason;

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
    public function ReturnMSISDN($returnMSISDN, $sourceOperatorId){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                if(strlen($returnMSISDN) == 12){
                    $returnMSISDN = substr($returnMSISDN, 3);
                }

                // Make ChangeImportMSISDN request
                $request = new BscsTypes\ReturnMSISDN();

                $request->autoCommit = true;
                $request->endUserName = BscsParams::endUserName;
                $request->PHONE_NUMBER = $returnMSISDN;
                $request->NPCODE = 1;

                if($sourceOperatorId == Operator::MTN_NETWORK_ID){
                    $request->SRC_PLCODE = BscsParams::MTN_PLCODE;
                }else{
                    $request->SRC_PLCODE = BscsParams::NEXTTEL_PLCODE;
                }

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

                    $response->message = $e->detail->$fault->reason;

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
    public function exportMSISDN($MSISDN, $destOperatorId){

        if($this->msisdnClient) {

            $logonResponse = $this->logonMSISDN();

            if($logonResponse->success){

                if(strlen($MSISDN) == 12){
                    $MSISDN = substr($MSISDN, 3);
                }

                // Make ExportMSISDN request
                $request = new BscsTypes\ExportMSISDN();

                $request->autoCommit = true;
                $request->MSISDN = $MSISDN;
                $request->endUserName = BscsParams::endUserName;
                $request->NPCODE = 1;

                if($destOperatorId == Operator::MTN_NETWORK_ID){
                    $request->DEST_PLCODE = BscsParams::MTN_PLCODE;
                }else{
                    $request->DEST_PLCODE = BscsParams::NEXTTEL_PLCODE;
                }

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

                    $response->message = $e->detail->$fault->reason;

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

    public function logonMSISDN(){

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

            $response->message = $e->detail->$fault->reason;

            $response->error = $fault;

            return $response;

        }
    }

    public function logoutMSISDN(){

        // Make logon request
        $request = new BscsTypes\logout();

        try {

            $response = $this->msisdnClient->logout($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->message = $e->detail->$fault->reason;

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
                $request->autoCommit = true;
                $request->endUserName = BscsParams::endUserName;
                $request->coDevRetention = 0;
                $request->coDnRetention = 0;
                $request->coPortRetention = 0;

                try {

                    $response = $this->contractClient->deleteContract($request);

                    $response->success = true;

                    $this->logoutContract();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->message = $e->detail->$fault->reason;

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

    /**
     * Updates contract status in BSCS
     * @param $contractId
     * @return errorResponse
     */
    public function updateContractStatus($contractId){

        if($this->contractClient) {

            $logonResponse = $this->logonContract();

            if($logonResponse->success){

                // Make updateContractStatus request
                $request = new BscsTypes\updateContractStatus();

                $request->i_co_id = $contractId;
                $request->autoCommit = true;
                $request->endUserName = BscsParams::endUserName;
                $request->i_new_status = BscsParams::PORTING_OUT_STATUS;
                $request->i_reason = BscsParams::PORTING_OUT_REASON;

                try {

                    $response = $this->contractClient->updateContractStatus($request);

                    $response->success = true;

                    $this->logoutContract();

                    return $response;

                }
                catch (SoapFault $e){

                    $response = new errorResponse();

                    $fault = key($e->detail);

                    $response->message = $e->detail->$fault->reason;

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


    public function logonContract(){

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

            $response->message = $e->detail->$fault->reason;

            $response->error = $fault;

            return $response;

        }
    }

    public function logoutContract(){

        // Make logon request
        $request = new BscsTypes\logout();

        try {

            $response = $this->contractClient->logout($request);

            $response->success = true;

            return $response;

        }catch (SoapFault $e){

            $response = new errorResponse();

            $fault = key($e->detail);

            $response->message = $e->detail->$fault->reason;

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