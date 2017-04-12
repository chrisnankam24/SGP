<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 4:18 PM
 */

require_once "Fault.php";
require_once "Common.php";
require_once "Rollback.php";
require_once "RollbackNotification.php";
require_once "ProvisionNotification.php";
require_once APPPATH . "controllers/sms/SMS.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/kpsa/KpsaOperationService.php";

use RollbackService\RollbackNotification as RollbackNotification;
use \ProvisionService\ProvisionNotification\provisionStateType as provisionStateType;

class RollbackNotificationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Load required models
        $this->load->model('Porting_model');
        $this->load->model('FileLog_model');
        $this->load->model('Rollback_model');
        $this->load->model('ProcessNumber_model');
        $this->load->model('Rollbackstateevolution_model');
        $this->load->model('Rollbacksmsnotification_model');
        $this->load->model('Rollbackrejectionabandon_model');

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/RollbackNotificationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

    }

    /**
     * @param $notifyOpenedRequest
     * @return RollbackNotification\notifyOpenedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyOpened($notifyOpenedRequest){

        isAuthorized();

        $rollbackId = $notifyOpenedRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyOpenedRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback OPEN received for ID ' . $rollbackId);

        if($notifyOpenedRequest->rollbackTransaction->donorNrn->networkId == Operator::MTN_NETWORK_ID){
            $denom_OPD = SMS::$DENOMINATION_COMMERCIALE_MTN;
        }else{
            $denom_OPD = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;
        }

        $this->db->trans_start();

        $originalPortingId = $notifyOpenedRequest->rollbackTransaction->originalPortingId;

        // Insert into Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'originalPortingId' => $originalPortingId,
            'donorSubmissionDateTime' => $notifyOpenedRequest->rollbackTransaction->donorSubmissionDateTime,
            'rollbackDateTime' => $notifyOpenedRequest->rollbackTransaction->rollbackDateTime,
            'cadbOpenDateTime' => $notifyOpenedRequest->rollbackTransaction->cadbOpenDateTime,
            'lastChangeDateTime' => $notifyOpenedRequest->rollbackTransaction->lastChangeDateTime,
            'rollbackNotificationMailSendStatus' => smsState::PENDING,
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

        // Insert Porting Numbers

        $processNumberParams = [];

        foreach ($rollbackNumbers as $rollbackNumber){
            $processNumberParams[] = array(
                'processId' => $rollbackId,
                'msisdn' => $rollbackNumber,
                'numberState' => provisionStateType::STARTED,
                'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
                'processType' => processType::ROLLBACK
            );
        }

        $this->db->insert_batch('processnumber', $processNumberParams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback OPEN saving failed for $rollbackId");

            $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

            $emailService = new EmailService();

            $emailService->adminErrorReport('OPENED ROLLBACK RECEIVED BUT DB FILLING ERROR', $rollbackParams, processType::ROLLBACK);

        }
        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback OPEN save successful for $rollbackId");

        }

        $this->db->trans_complete();

        // Send SMS to Subscriber

        // Get porting Info for language
        $portingInfo = $this->Porting_model->get_porting($originalPortingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyOpenedRequest->rollbackTransaction->subscriberInfo->contactNumber;

        $smsResponse = SMS::OPR_Inform_Subscriber($language, $subscriberMSISDN, $denom_OPD, $rollbackId);

        if($smsResponse['success']){

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback OPEN SMS sent successful for $rollbackId");

            $smsNotificationparams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_STARTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
            );

        }else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback OPEN SMS sent failed for $rollbackId");

            $smsNotificationparams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_STARTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
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

        isAuthorized();

        $rollbackId = $notifyAcceptedRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyAcceptedRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback ACCEPT received for ID ' . $rollbackId);

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'rollbackDateTime' => $notifyAcceptedRequest->rollbackTransaction->rollbackDateTime,
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

        // Update Number state
        $portingNumberParams = array(
            'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
            'numberState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
        );

        $this->ProcessNumber_model->update_processnumber_all($rollbackId, $portingNumberParams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback ACCEPT saving failed for $rollbackId");

            $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('ACCEPTED ROLLBACK RECEIVED BUT DB FILLING ERROR', $rollbackParams, processType::ROLLBACK);

        }
        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ACCEPT save successful for $rollbackId");

        }

        $this->db->trans_complete();

        // Send SMS to Subscriber

        $originalPortingId = $notifyAcceptedRequest->rollbackTransaction->originalPortingId;

        $portingInfo = $this->Porting_model->get_porting($originalPortingId);

        $language = $portingInfo['language'];

        $rollbackDateTime = $notifyAcceptedRequest->rollbackTransaction->rollbackDateTime;

        $sendMsisdn = $notifyAcceptedRequest->rollbackTransaction->subscriberInfo->contactNumber;
        $subscriberMSISDN = implode(', ', $rollbackNumbers);

        if(strlen($subscriberMSISDN) > 26){
            $subscriberMSISDN = substr($subscriberMSISDN, 0, 27) . ' ...';
        }

        $day = date('d/m/Y', strtotime($rollbackDateTime));
        $start_time = date('H:i:s', strtotime($rollbackDateTime));
        $end_time = date('H:i:s', strtotime('+2 hours', strtotime($rollbackDateTime)));

        $smsResponse = SMS::OPD_Subscriber_OK($language, $subscriberMSISDN, $day, $start_time, $end_time, $sendMsisdn);

        if($smsResponse['success']){

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ACCEPT SMS sent successful for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
            );

        }
        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ACCEPT SMS sent failed for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
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

        isAuthorized();

        $rollbackId = $notifyAutoAcceptRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyAutoAcceptRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback AUTO ACCEPT received for ID ' . $rollbackId);

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'rollbackDateTime' => $notifyAutoAcceptRequest->rollbackTransaction->rollbackDateTime,
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

        // Update Number state
        $portingNumberParams = array(
            'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
            'numberState' => \RollbackService\Rollback\rollbackStateType::ACCEPTED
        );

        $this->ProcessNumber_model->update_processnumber_all($rollbackId, $portingNumberParams);

        // Mail Admin AUTO state reached

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback AUTO ACCEPT saving failed for $rollbackId");

            $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

            $emailService->adminErrorReport('ROLLBACK_AUTO_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

        }else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback AUTO ACCEPT save successful for $rollbackId");

        }

        $this->db->trans_complete();

        // Send SMS to Subscriber

        $originalPortingId = $notifyAutoAcceptRequest->rollbackTransaction->originalPortingId;

        $portingInfo = $this->Porting_model->get_porting($originalPortingId);

        $language = $portingInfo['language'];

        $rollbackDateTime = $notifyAutoAcceptRequest->rollbackTransaction->rollbackDateTime;

        $sendMsisdn = $notifyAutoAcceptRequest->rollbackTransaction->subscriberInfo->contactNumber;
        $subscriberMSISDN = implode(', ', $rollbackNumbers);

        if(strlen($subscriberMSISDN) > 26){
            $subscriberMSISDN = substr($subscriberMSISDN, 0, 27) . ' ...';
        }

        $day = date('d/m/Y', strtotime($rollbackDateTime));
        $start_time = date('H:i:s', strtotime($rollbackDateTime));
        $end_time = date('H:i:s', strtotime('+2 hours', strtotime($rollbackDateTime)));

        $smsResponse = SMS::OPD_Subscriber_OK($language, $subscriberMSISDN, $day, $start_time, $end_time, $sendMsisdn);

        if($smsResponse['success']){

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ACCEPT SMS sent successful for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
            );

        }
        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ACCEPT SMS sent failed for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_ACCEPTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($smsParams);

        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

        $emailService->adminErrorReport('ROLLBACK REACHED AUTO ACCEPT', $rollbackParams, processType::ROLLBACK);

        $response = new RollbackNotification\notifyAutoAcceptResponse();

        return $response;

    }

    /**
     * @param $notifyAutoConfirmRequest
     * @return RollbackNotification\notifyAutoConfirmResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoConfirm($notifyAutoConfirmRequest){

        isAuthorized();

        $rollbackId = $notifyAutoConfirmRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyAutoConfirmRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback AUTO CONFIRM received for ID ' . $rollbackId);

        $recipientNetworkId = $notifyAutoConfirmRequest->rollbackTransaction->recipientNrn->networkId;

        $emailService = new EmailService();

        $nrollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

        // Alert admin
        $emailService->adminErrorReport('ROLLBACK REACHED AUTO CONFIRM', $nrollbackParams, processType::ROLLBACK);

        // Insert into Rollback State Evolution table

        $seParams = array(
            'rollbackState' => \RollbackService\Rollback\rollbackStateType::CONFIRMED,
            'lastChangeDateTime' => $notifyAutoConfirmRequest->rollbackTransaction->lastChangeDateTime,
            'isAutoReached' => true,
            'rollbackId' => $rollbackId,
        );

        $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

        // Start rollback process

        $rollbackErrors = [];

        foreach ($rollbackNumbers as $rollbackNumber) {

            $subscriberMSISDN = $rollbackNumber;

            $rollbackStartedResponse = $this->startRollbackOPD($subscriberMSISDN, $recipientNetworkId);

            if($rollbackStartedResponse->success){

                $this->db->trans_start();

                // Update Rollback Number table

                $rollbackNumberParams = array(
                    'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
                    'numberState' => \RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED
                );

                $this->ProcessNumber_model->update_processnumber($rollbackId, $subscriberMSISDN, $rollbackNumberParams);


                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();

                    $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback AUTO CONFIRM saving failed for $rollbackId");

                    $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

                    $portingErrors[] = $subscriberMSISDN;

                }
                else{

                    $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback AUTO CONFIRM save successful for $rollbackId");

                }

                $this->db->trans_complete();

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

                $emailService->adminErrorReport($fault, $nrollbackParams, processType::ROLLBACK);

            }

        }

        if (count($rollbackErrors) > 0){

            $nrollbackParams['msisdn'] = $rollbackErrors;

            $emailService->adminErrorReport('ROLLBACK AUTO CONFIRMED BUT DB FILLED INCOMPLETE', $nrollbackParams, processType::ROLLBACK);
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

        isAuthorized();

        $rollbackId = $notifyRejectedRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyRejectedRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback REJECT received for ID ' . $rollbackId);

        $this->db->trans_start();

        // Update Rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'rollbackDateTime' => $notifyRejectedRequest->rollbackTransaction->rollbackDateTime,
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

        // Update number state

        foreach ($rollbackNumbers as $rollbackNumber){

            $rollbackNumberParams = array(
                'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
                'numberState' => provisionStateType::TERMINATED,
                'terminationReason' => $notifyRejectedRequest->rejectionReason
            );

            $this->ProcessNumber_model->update_processnumber($rollbackId, $rollbackNumber, $rollbackNumberParams);

        }

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback REJECTED saving failed for $rollbackId");

            $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('REJECTED ROLLBACK RECEIVED BUT DB FILLING ERROR', $rollbackParams, processType::ROLLBACK);
        }
        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback REJECT save successful for $rollbackId");

        }

        $this->db->trans_complete();

        // Send SMS to Subscriber

        $originalPortingId = $notifyRejectedRequest->rollbackTransaction->originalPortingId;

        $portingInfo = $this->Porting_model->get_porting($originalPortingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyRejectedRequest->rollbackTransaction->subscriberInfo->contactNumber;

        $smsResponse = SMS::OPD_Subscriber_KO($language, $subscriberMSISDN);

        if($smsResponse['success']){

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback REJECT SMS sent successful for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_REJECTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
            );

        }else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback REJECT SMS sent failed for $rollbackId");

            $smsParams = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPD_ROLLBACK_REJECTED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
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

        isAuthorized();

        $this->db->trans_start();

        $rollbackId = $notifyAbandonedRequest->rollbackTransaction->rollbackId;

        $rollbackNumbers = $this->getRollbackNumbers($notifyAbandonedRequest);

        $this->fileLogAction('8030', 'RollbackNotificationService', 'Rollback ABANDON received for ID ' . $rollbackId);

        // update rollback table

        $rollbackParams = array(
            'rollbackId' => $rollbackId,
            'rollbackDateTime' => $notifyAbandonedRequest->rollbackTransaction->rollbackDateTime,
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

        // Update number state

        foreach ($rollbackNumbers as $rollbackNumber){

            $rollbackNumberParams = array(
                'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
                'numberState' => provisionStateType::TERMINATED,
                'terminationReason' => $notifyAbandonedRequest->cause
            );

            $this->ProcessNumber_model->update_processnumber($rollbackId, $rollbackNumber, $rollbackNumberParams);

        }

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'RollbackNotificationService', "Rollback ABANDON saving failed for $rollbackId");

            $this->fileLogAction($error['code'], 'RollbackNotificationService', $error['message']);

            $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

            $emailService->adminErrorReport('ROLLBACK ABANDONED BUT DB FILLED INCOMPLETE', $rollbackParams, processType::ROLLBACK);

        }

        else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ABANDON save successful for $rollbackId");

        }

        $this->db->trans_complete();

        // Send SMS to Subscriber

        $originalPortingId = $notifyAbandonedRequest->rollbackTransaction->originalPortingId;

        $portingInfo = $this->Porting_model->get_porting($originalPortingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyAbandonedRequest->rollbackTransaction->subscriberInfo->contactNumber;

        $smsResponse = SMS::Subscriber_CADB_Abandoned_Rollback($language, $subscriberMSISDN);

        if($smsResponse['success']){

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ABANDON SMS sent successful for $rollbackId");

            // Insert Porting SMS Notification
            $params = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_ABANDONED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
            );

        }else{

            $this->fileLogAction('8030', 'RollbackNotificationService', "Rollback ABANDON SMS sent failed for $rollbackId");

            $params = array(
                'rollbackId' => $rollbackId,
                'smsType' => SMSType::OPR_ROLLBACK_ABANDONED,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Rollbacksmsnotification_model->add_rollbacksmsnotification($params);

        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

        $emailService->adminErrorReport('ROLLBACK ABANDONED BY CADB', $rollbackParams, processType::ROLLBACK);

        $response = new RollbackNotification\notifyAbandonedResponse();

        return $response;

    }

    /**
     * Starts Rollback of given number
     * @param $rollbackNumber
     * @return errorResponse
     */
    private function startRollbackOPD($rollbackNumber, $recipientNetworkId){

        // Import MSISDN
        $bscsOperationService = new BscsOperationService();

        $response = $bscsOperationService->importMSISDN($rollbackNumber, $recipientNetworkId);

        return $response;

    }

    /**
     * Returns rollback MSISDN in process
     * @param $request
     * @return array
     */
    private function getRollbackNumbers($request){

        $numbers = [];

        if(is_array($request->rollbackTransaction->numberRanges->numberRange)){

            foreach ($request->rollbackTransaction->numberRanges->numberRange as $numberRange){

                $startMSISDN = $numberRange->startNumber;
                $endMSISDN = $numberRange->endNumber;

                if(strlen($startMSISDN) == 12){
                    $startMSISDN = substr($startMSISDN, 3);
                }
                if(strlen($endMSISDN) == 12){
                    $endMSISDN = substr($endMSISDN, 3);
                }

                $startMSISDN = intval($startMSISDN);
                $endMSISDN = intval($endMSISDN);

                while ($startMSISDN <= $endMSISDN){
                    $numbers[] = '237' . $startMSISDN;
                    $startMSISDN += 1;
                }

            }

        }
        else{

            $startMSISDN = $request->rollbackTransaction->numberRanges->numberRange->startNumber;
            $endMSISDN = $request->rollbackTransaction->numberRanges->numberRange->endNumber;

            if(strlen($startMSISDN) == 12){
                $startMSISDN = substr($startMSISDN, 3);
            }
            if(strlen($endMSISDN) == 12){
                $endMSISDN = substr($endMSISDN, 3);
            }

            $startMSISDN = intval($startMSISDN);
            $endMSISDN = intval($endMSISDN);

            while ($startMSISDN <= $endMSISDN){
                $numbers[] = '237' . $startMSISDN;
                $startMSISDN += 1;
            }

        }

        $numbers = array_values(array_unique($numbers));

        return $numbers;

    }

}

