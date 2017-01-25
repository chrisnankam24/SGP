<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 9:35 AM
 */

require_once "Fault.php";
require_once "Return.php";
require_once "Common.php";
require_once "ReturnNotification.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/kpsa/KpsaOperationService.php";

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

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * TODO: OK
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
            'returnNotificationMailSendStatus' => smsState::PENDING,
            'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
        );

        $this->Numberreturn_model->add_numberreturn($nrParams);

        // Insert into NR state evolution table

        $nrsParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
            'lastChangeDateTime' => date('c'),
            'returnId' => $notifyOpenedRequest->returnTransaction->returnId,
        );

        $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();
            fileLogAction($error['code'], 'ReturnNotificationService', $error['message']);

            $emailService = new EmailService();
            $emailService->adminErrorReport('OPENED_RETURN_RECEIVED_BUT_DB_FILLING_ERROR', $nrParams, processType::_RETURN);
        }

        $this->db->trans_complete();

        $response = new _ReturnNotification\notifyOpenedResponse();

        return $response;

    }

    /**
     * TODO: OK
     * @param $notifyAcceptedRequest
     * @return _ReturnNotification\notifyAcceptedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAccepted($notifyAcceptedRequest){

        $returnId = $notifyAcceptedRequest->returnTransaction->returnId;

        // Update NR table

        $nrParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::ACCEPTED,
        );

        $this->Numberreturn_model->update_numberreturn($returnId, $nrParams);

        // Insert into NR state Evolution

        $nrsParams = array(
            'returnNumberState' => \ReturnService\_Return\returnStateType::ACCEPTED,
            'lastChangeDateTime' => date('c'),
            'returnId' => $returnId,
        );

        $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();
            fileLogAction($error['code'], 'ReturnNotificationService', $error['message']);

            $emailService = new EmailService();

            $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

            $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

        }

        $this->db->trans_complete();

        $response = new _ReturnNotification\notifyAcceptedResponse();

        return $response;

    }

    /**
     * TODO: OK
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
            'returnId' => $returnId,
        );

        $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

        // Insert into return rejection

        $rrParams = array(
            'cause' => $notifyRejectedRequest->cause,
            'returnId' => $returnId,
        );

        $this->Returnrejection_model->add_returnrejection($rrParams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();
            fileLogAction($error['code'], 'ReturnNotificationService', $error['message']);

            $emailService = new EmailService();

            $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

            $emailService->adminErrorReport('RETURN_REJECTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

        }

        $this->db->trans_complete();

        $response = new _ReturnNotification\notifyRejectedResponse();

        return $response;

    }

}