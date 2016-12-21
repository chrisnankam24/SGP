<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 9:35 AM
 */

require_once "ReturnNotification.php";
require_once "Fault.php";
require_once "Common.php";

use ReturnService\_ReturnNotification as _ReturnNotification;

class ReturnNotificationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ReturnNotificationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ReturnNotificationService");

        // Handle soap operations
        $server->handle();

    }

    /**
     * @param $notifyOpenedRequest
     * @return _ReturnNotification\notifyOpenedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyOpened($notifyOpenedRequest){

        $returnId = $notifyOpenedRequest->returnTransaction->returnId;

        $this->db->trans_start();

        // Insert into NR table

        $nrParams = array(
            'returnId' => $returnId,
            'openDateTime' => $notifyOpenedRequest->returnTransaction->openDateTime,
            'ownerNetworkId' => $notifyOpenedRequest->returnTransaction->ownerNrn->networkId,
            'ownerRoutingNumber' => $notifyOpenedRequest->returnTransaction->ownerNrn->routingNumber,
            'primaryOwnerNetworkId' => $notifyOpenedRequest->returnTransaction->primaryOwnerNrn->networkId,
            'primaryOwnerRoutingNumber' => $notifyOpenedRequest->returnTransaction->primaryOwnerNrn->routingNumber,
            'returnMSISDN' => $notifyOpenedRequest->returnTransaction->numberRanges->numberRange->startNumber,
            'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
        );

        $this->Numberreturn_model->add_numberreturn($nrParams);

        // Insert into NR state evolution table

        $nrsParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
            'lastChangeDateTime' => date('c'),
            'isAutoReached' => false,
            'returnId' => $notifyOpenedRequest->returnTransaction->returnId,
        );

        $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $emailService = new EmailService();
            $emailService->adminErrorReport('OPENED_RETURN_RECEIVED_BUT_DB_FILLING_ERROR', []);
        }

        $response = new _ReturnNotification\notifyOpenedResponse();

        return $response;

    }

    /**
     * @param $notifyAcceptedRequest
     * @return _ReturnNotification\notifyAcceptedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAccepted($notifyAcceptedRequest){

        $returnId = $notifyAcceptedRequest->returnTransaction->returnId;

        $returnNumber = $notifyAcceptedRequest->returnTransaction->numberRanges->numberRange->startNumber;

        $emailService = new EmailService();

        // Start Return Current Owner

        $returnStartedResponse = $this->startReturnCO($returnNumber);

        if($returnStartedResponse->success){

            $this->db->trans_start();

            // Update NR table

            $nrParams = array(
                'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED,
            );

            $this->Numberreturn_model->update_numberreturn($returnId, $nrParams);

            // Insert NR state evolution table

            $nrsParams = array(
                'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED,
                'lastChangeDateTime' => date('c'),
                'isAutoReached' => false,
                'returnId' => $returnId,
            );

            $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {

                $emailService = new EmailService();
                $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

            }

        }else{

            $faultCode = $returnStartedResponse->error;

            $fault = '';

            switch ($faultCode) {
                // Terminal Processes
                case Fault::SERVICE_BREAK_DOWN_CODE:
                    $fault = Fault::SERVICE_BREAK_DOWN;
                    break;
                case Fault::SIGNATURE_MISMATCH_CODE:
                    $fault = Fault::SIGNATURE_MISMATCH;
                    break;
                case Fault::DENIED_ACCESS_CODE:
                    $fault = Fault::DENIED_ACCESS;
                    break;
                case Fault::UNKNOWN_COMMAND_CODE:
                    $fault = Fault::UNKNOWN_COMMAND;
                    break;
                case Fault::INVALID_PARAMETER_TYPE_CODE:
                    $fault = Fault::INVALID_PARAMETER_TYPE;
                    break;

                case Fault::PARAMETER_LIST_CODE:
                    $fault = Fault::PARAMETER_LIST;
                    break;

                case Fault::CMS_EXECUTION_CODE:
                    $fault = Fault::CMS_EXECUTION;
                    break;

                default:
                    $fault = $faultCode;
            }

            $emailService->adminErrorReport($fault, []);

        }

        $response = new _ReturnNotification\notifyAcceptedResponse();

        return $response;

    }

    /**
     * @param $notifyRejectedRequest
     * @return _ReturnNotification\notifyRejectedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyRejected($notifyRejectedRequest){

        $returnId = $notifyRejectedRequest->returnTransaction->returnId;

        $this->db->trans_start();

        // Update NR table

        $nrParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::REJECTED,
        );

        $this->Numberreturn_model->update_numberreturn($returnId, $nrParams);

        // Insert into NR state Evolution

        $nrsParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::REJECTED,
            'lastChangeDateTime' => date('c'),
            'isAutoReached' => false,
            'returnId' => $returnId,
        );

        $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

        // Insert into return rejection

        $rrParams = array(
            'cause' => $notifyRejectedRequest->cause,
            'returnId' => $returnId,
        );

        $this->Returnrejection_model->add_returnrejection($rrParams);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {

            $emailService = new EmailService();
            $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

        }

        $response = new _ReturnNotification\notifyRejectedResponse();

        return $response;

    }

    private function performReturnCO($portingNumber){

        // BSCS Ops

        // Return Ported MSISDN (ReturnMSISDN)

        // KPSA Ops

        // If OPR = OPA, delete MSISDN in KPSA

        // Else if MSISDN not in KPSA, create MSISDN with routing number Orange

        // Else if MSISND in KPSA, update MSISDN with routing number Orange

    }

    private function performReturnPO($returnNumber){

        // BSCS Ops

        // Retrieve ContractId from BSCS

        // Export MSISDN from BSCS (ExportMSISDN)

        // KPSA Ops

        // If MSISDN not in KPSA, create MSISDN with routing number OPR

        // Else if MSISND in KPSA, update MSISDN with routing number OPR

    }

    private function performReturnOther($returnNumber) {

        // KPSA Ops

        // If MSISDN not in KPSA, create MSISDN with routing number OPR

        // Else if MSISDN in KPSA, update MSISDN with routing number OPR

    }

    private function startReturnCO($returnNumber){

        // Return MSISDN
        $bscsOperationService = new BscsOperationService();

        $response = $bscsOperationService->ReturnMSISDN($returnNumber);

        // KPSA Ops

        // If OPR = OPA, delete MSISDN in KPSA

        // Else if MSISDN not in KPSA, create MSISDN with routing number Orange

        // Else if MSISND in KPSA, update MSISDN with routing number Orange

        return $response;

    }

}