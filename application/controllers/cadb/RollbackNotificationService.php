<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 4:18 PM
 */

require_once "Rollback.php";
require_once "PortingNotification.php";
require_once "Common.php";
require_once "Fault.php";

require_once APPPATH . "controllers/sms/SMS.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";

use RollbackService\RollbackNotification as RollbackNotification;

class RollbackNotificationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Load required models
        $this->load->model('FileLog_model');
        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbackstateevolution_model');
        $this->load->model('Rollbacksmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/RollbackNotificationService.wsdl');

        // Set the class for the soap server
        $server->setClass("RollbackNotificationService");

        // Handle soap operations
        $server->handle();

    }

    /**
     * @param $notifyOpenedRequest
     * @return RollbackNotification\notifyOpenedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyOpened($notifyOpenedRequest){

        $rollbackId = $notifyOpenedRequest->rollbackTransaction->rollbackId;

        $subscriberMSISDN = $notifyOpenedRequest->rollbackTransaction->numberRanges->numberRange->startNumber;

        $denom_OPD =  '';

        if($notifyOpenedRequest->rollbackTransaction->donorNrn->networkId == Operator::MTN_NETWORK_ID){
            $denom_OPD = SMS::$DENOMINATION_COMMERCIALE_MTN;
        }else{
            $denom_OPD = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;
        }

        $this->db->trans_start();

        // Insert into Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'originalPortingId' => $notifyOpenedRequest->rollbackTransaction->originalPortingId,
            'donorSubmissionDateTime' => $notifyOpenedRequest->rollbackTransaction->donorSubmissionDateTime,
            'preferredRollbackDateTime' => $notifyOpenedRequest->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateAndTime' => $notifyOpenedRequest->rollbackTransaction->rollbackDateTime,
            'cadbOpenDateTime' => $notifyOpenedRequest->rollbackTransaction->cadbOpenDateTime,
            'lastChangeDateTime' => $notifyOpenedRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED
        );

        $this->Rollback_model->add_rollback($rollbackParams);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
            'lastChangeDateTime' => $notifyOpenedRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => false,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $emailService = new EmailService();
            $emailService->adminErrorReport('OPENED_ROLLBACK_RECEIVED_BUT_DB_FILLING_ERROR', []);
        }

        // Send SMS
        // Send SMS to Subscriber
        $smsResponse = SMS::OPR_Inform_Subscriber($subscriberMSISDN, $denom_OPD, $rollbackId);

        if($smsResponse->success){

            $smsNotificationparams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_STARTED,
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c'),
            );

        }else{

            $smsNotificationparams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_STARTED,
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($smsNotificationparams);

        $response = new RollbackNotification\notifyOpenedResponse();

        return $response;

    }

    /**
     * @param $notifyAcceptedRequest
     * @return RollbackNotification\notifyAcceptedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAccepted($notifyAcceptedRequest) {

        $rollbackId = $notifyAcceptedRequest->rollbackTransaction->rollbackId;

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'preferredRollbackDateTime' => $notifyAcceptedRequest->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateAndTime' => $notifyAcceptedRequest->rollbackTransaction->rollbackDateTime,
            'lastChangeDateTime' => $notifyAcceptedRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
        );

        $this->Rollback_model->update_rollback($rollbackId,$rollbackParams);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED,
            'lastChangeDateTime' => $notifyAcceptedRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => false,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $emailService = new EmailService();
            $emailService->adminErrorReport('ACCEPTED_ROLLBACK_RECEIVED_BUT_DB_FILLING_ERROR', []);
        }

        // Send SMS to Subscriber

        $subscriberMSISDN = $notifyAcceptedRequest->rollbackTransaction->numberRanges->numberRange->startNumber;

        $rollbackDateTime = $notifyAcceptedRequest->rollbackTransaction->rollbackDateTime;

        $day = date('d/m/Y', strtotime($rollbackDateTime));
        $start_time = date('h:i:s', strtotime($rollbackDateTime));
        $end_time = date('h:i:s', strtotime('+2 hours', strtotime($rollbackDateTime)));

        $smsResponse = SMS::OPD_Subscriber_OK($subscriberMSISDN, $day, $start_time, $end_time);

        if($smsResponse->success){

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c'),
            );

        }else{

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($smsParams);

        $response = new RollbackNotification\notifyAcceptedResponse();

        return $response;

    }

    /**
     * @param $notifyAutoAcceptRequest
     * @return RollbackNotification\notifyAutoAcceptResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoAccept($notifyAutoAcceptRequest){

        $rollbackId = $notifyAutoAcceptRequest->rollbackTransaction->rollbackId;

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'preferredRollbackDateTime' => $notifyAutoAcceptRequest->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateAndTime' => $notifyAutoAcceptRequest->rollbackTransaction->rollbackDateTime,
            'lastChangeDateTime' => $notifyAutoAcceptRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
        );

        $this->Rollback_model->update_rollback($rollbackId,$rollbackParams);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED,
            'lastChangeDateTime' => $notifyAutoAcceptRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => true,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        // Mail Admin AUTO state reached

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $emailService->adminErrorReport('ROLLBACK_AUTO_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

        }

        $emailService->adminErrorReport('ROLLBACK_REACHED_AUTO_ACCEPT', []);

        $response = new RollbackNotification\notifyAutoAcceptResponse();

        return $response;

    }

    /**
     * @param $notifyAutoConfirmRequest
     * @return RollbackNotification\notifyAutoConfirmResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoConfirm($notifyAutoConfirmRequest){

        $rollbackId = $notifyAutoConfirmRequest->rollbackTransaction->rollbackId;

        $subscriberMSISDN = $notifyAutoConfirmRequest->rollbackTransaction->numberRanges->numberRange->startNumber;

        $emailService = new EmailService();

        // Alert admin
        $emailService->adminErrorReport('ROLLBACK_REACHED_AUTO_CONFIRM', []);

        // Start porting process
        $rollbackStartedResponse = $this->startRollbackOPD($subscriberMSISDN);

        if($rollbackStartedResponse->success){

            $this->db->trans_start();

            // Update Rollback table

            $rollbackParams = array(
                'rollbackId' => $rollbackId,
                'preferredRollbackDateTime' => $notifyAutoConfirmRequest->rollbackTransaction->preferredRollbackDateTime,
                'rollbackDateAndTime' => $notifyAutoConfirmRequest->rollbackTransaction->rollbackDateTime,
                'lastChangeDateTime' => $notifyAutoConfirmRequest->rollbackTransaction->lastChangeDateTime,
                'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED
            );

            $this->Rollback_model->update_rollback($rollbackId,$rollbackParams);

            // Insert into rollback Evolution state table

            $seParams = array(
                'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED,
                'lastChangeDateTime' => $notifyAutoConfirmRequest->rollbackTransaction->lastChangeDateTime,
                'isAutoReached' => true,
                'rollbackId' => $rollbackId,
            );

            $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {

                $emailService->adminErrorReport('ROLLBACK_AUTO_CONFIRMED_BUT_DB_FILLED_INCOMPLETE', []);

            }

        }
        else {

            $faultCode = $rollbackStartedResponse->error;

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

        $response = new RollbackNotification\notifyAutoConfirmResponse();

        return $response;

    }

    /**
     * @param $notifyRejectedRequest
     * @return RollbackNotification\notifyRejectedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyRejected($notifyRejectedRequest){

        $rollbackId = $notifyRejectedRequest->rollbackTransaction->rollbackId;

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'preferredRollbackDateTime' => $notifyRejectedRequest->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateAndTime' => $notifyRejectedRequest->rollbackTransaction->rollbackDateTime,
            'lastChangeDateTime' => $notifyRejectedRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::REJECTED
        );

        $this->Rollback_model->update_rollback($rollbackId,$rollbackParams);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::REJECTED,
            'lastChangeDateTime' => $notifyRejectedRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => false,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        // Insert into rollback rejectAbandon Table

        $rjParams = array(
            'rejectionReason' => $notifyRejectedRequest->rejectionReason,
            'cause' => $notifyRejectedRequest->cause,
            'rollbackId' => $notifyRejectedRequest->rollbackTransaction->rollbackId,
        );

        $this->Rollbackrejectionabandon_model->add_rollbackrejectionabandon($rjParams);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $emailService = new EmailService();
            $emailService->adminErrorReport('REJECTED_ROLLBACK_RECEIVED_BUT_DB_FILLING_ERROR', []);
        }

        // Send SMS to Subscriber

        $subscriberMSISDN = $notifyRejectedRequest->rollbackTransaction->numberRanges->numberRange->startNumber;

        $smsResponse = SMS::OPD_Subscriber_KO($subscriberMSISDN);

        if($smsResponse->success){
            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_REJECTED,
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c'),
            );

        }else{

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_REJECTED,
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($smsParams);

        $response = new RollbackNotification\notifyRejectedResponse();

        return $response;

    }

    /**
     * @param $notifyAbandonedRequest
     * @return RollbackNotification\notifyAbandonedResponse
     */
    public function notifyAbandoned($notifyAbandonedRequest){

        $this->db->trans_start();

        $rollbackId = $notifyAbandonedRequest->rollbackTransaction->rollbackId;

        // update rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'preferredRollbackDateTime' => $notifyAbandonedRequest->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateAndTime' => $notifyAbandonedRequest->rollbackTransaction->rollbackDateTime,
            'lastChangeDateTime' => $notifyAbandonedRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ABANDONED
        );

        $this->Rollback_model->update_rollback($rollbackId,$rollbackParams);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::ABANDONED,
            'lastChangeDateTime' => $notifyAbandonedRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => false,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        // Insert into Rollback rejection abandon table

        $rraParams = array(
            'cause' => $notifyAbandonedRequest->cause,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackrejectionabandon_model->add_rollbackrejectionabandon($rraParams);

        // Send SMS to Subscriber

        $subscriberMSISDN = $notifyAbandonedRequest->rollbackTransaction->numberRanges->numberRange->startNumber;

        $smsResponse = SMS::Subscriber_CADB_Abandoned_Rollback($subscriberMSISDN);

        if($smsResponse->success){

            // Insert Porting SMS Notification
            $params = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_ABANDONED,
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c'),
            );

        }else{

            $params = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_ABANDONED,
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($params);


        $this->db->trans_complete();

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $emailService->adminErrorReport('ROLLBACK_ABANDONED_BUT_DB_FILLED_INCOMPLETE', []);

        }

        $emailService->adminAgentsPortingAbandoned([]);

        $response = new RollbackNotification\notifyAbandonedResponse();

        return $response;

    }

    private function performRollbackOPD($rollbackNumber){

        // BSCS Ops

        // Import Ported MSISDN (ImportMSISDN)

        // Verify if temporal MSISDN active

        // If not active, activate, else change Import MSISDN (ChangeImportMSISDN)

        // KPSA Ops

        // If OPR = OPA, delete MSISDN in KPSA

        // Else if MSISDN not in KPSA, create MSISDN with routing number Orange

        // Else if MSISND in KPSA, update MSISDN with routing number Orange

    }

    private function performRollbackOPR($rollbackNumber){

        // BSCS Ops

        // Retrieve ContractId from BSCS

        // Export MSISDN from BSCS (ExportMSISDN)

        // KPSA Ops

        // If MSISDN not in KPSA, create MSISDN with routing number OPR

        // Else if MSISND in KPSA, update MSISDN with routing number OPR

    }

    private function performRollbackOther($rollbackNumber) {

        // KPSA Ops

        // If MSISDN not in KPSA, create MSISDN with routing number OPR

        // Else if MSISDN in KPSA, update MSISDN with routing number OPR

    }

    private function startRollbackOPD($rollbackNumber){

        // Import MSISDN
        $bscsOperationService = new BscsOperationService();

        $response = $bscsOperationService->importMSISDN($rollbackNumber);

        return $response;

    }
}

