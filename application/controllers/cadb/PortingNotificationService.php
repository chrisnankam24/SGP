<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Fault.php";
require_once "Common.php";
require_once "Porting.php";
require_once "PortingNotification.php";

require_once APPPATH . "controllers/sms/SMS.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/kpsa/KpsaOperationService.php";


use PortingService\PortingNotification as PortingNotification;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 7:47 AM
 */

/**
 * Controller for PortingNotificationService server requests made towards Us operator
 * Class PortingNotificationService
 */
class PortingNotificationService extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load required models
        $this->load->model('Porting_model');
        $this->load->model('FileLog_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/PortingNotificationService.wsdl');

        // Set the class for the soap server
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
     * @param $notifyOrderedRequest
     * @return PortingNotification\notifyOrderedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyOrdered($notifyOrderedRequest){

        isAuthorized();

        $rio = $notifyOrderedRequest->portingTransaction->rio;

        $portingId = $notifyOrderedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ORDER received for ID ' . $portingId);

        $subscriberType = getSubscriberType($rio);

        $startMSISDN = $notifyOrderedRequest->portingTransaction->numberRanges->numberRange->startNumber;
        $endMSISDN = $notifyOrderedRequest->portingTransaction->numberRanges->numberRange->endNumber;

        $this->db->trans_start();

        // Insert Porting table

        $portingParams = array(
            'portingId' => $portingId,
            'recipientNetworkId' => $notifyOrderedRequest->portingTransaction->recipientNrn->networkId,
            'recipientRoutingNumber' => $notifyOrderedRequest->portingTransaction->recipientNrn->routingNumber,
            'donorNetworkId' => $notifyOrderedRequest->portingTransaction->donorNrn->networkId,
            'donorRoutingNumber' => $notifyOrderedRequest->portingTransaction->donorNrn->routingNumber,
            'recipientSubmissionDateTime' => $notifyOrderedRequest->portingTransaction->recipientSubmissionDateTime,
            'portingDateTime' => $notifyOrderedRequest->portingTransaction->portingDateTime,
            'rio' =>  $notifyOrderedRequest->portingTransaction->rio,
            'portingNotificationMailSendStatus' => smsState::PENDING,
            'startMSISDN' =>  $startMSISDN,
            'endMSISDN' =>  $endMSISDN
        );

        $portingParams['cadbOrderDateTime'] = $notifyOrderedRequest->portingTransaction->cadbOrderDateTime;
        $portingParams['lastChangeDateTime'] = $notifyOrderedRequest->portingTransaction->lastChangeDateTime;
        $portingParams['portingState'] = \PortingService\Porting\portingStateType::ORDERED;

        if($subscriberType == 0) {
            $portingParams['physicalPersonFirstName'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->physicalPersonFirstName;
            $portingParams['physicalPersonLastName'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->physicalPersonLastName;
            $portingParams['physicalPersonIdNumber'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->physicalPersonIdNumber;

        }
        else{
            $portingParams['legalPersonName'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->legalPersonName;
            $portingParams['legalPersonTin'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->legalPersonTin;
            $portingParams['contactNumber'] = $notifyOrderedRequest->portingTransaction->subscriberInfo->contactNumber;
        }

        $this->Porting_model->add_porting($portingParams);

        // Insert Porting State evolution table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyOrderedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ORDERED,
            'isAutoReached' => false,
            'portingId' => $notifyOrderedRequest->portingTransaction->portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);


        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting ORDER saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $emailService = new EmailService();
            $emailService->adminErrorReport('ORDERED_PORTING_RECEIVED_BUT_DB_FILLING_ERROR', $portingParams, processType::PORTING);
            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ORDER saving successful for ID ' . $portingId);

            $this->db->trans_complete();

            $response = new PortingNotification\notifyOrderedResponse();

            return $response;

        }

    }

    /**
     * @param $notifyApprovedRequest
     * @return PortingNotification\notifyApprovedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyApproved($notifyApprovedRequest){

        isAuthorized();

        $this->db->trans_start();

        // Fill in portingStateEvolution table with state approved

        $portingId = $notifyApprovedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting APPROVE received for ID ' . $portingId);

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyApprovedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::APPROVED,
            'isAutoReached' => false,
            'portingId' => $portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyApprovedRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyApprovedRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyApprovedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::APPROVED
        );

        $this->Porting_model->update_porting($notifyApprovedRequest->portingTransaction->portingId, $portingParams);

        if ($this->db->trans_status() === false) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting APPROVE saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('PORTING_APPROVED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting APPROVE successful for ID ' . $portingId);

            $this->db->trans_complete();

            $response = new PortingNotification\notifyApprovedResponse();

            return $response;

        }

    }

    /**
     * @param $notifyAutoApproveRequest
     * @return PortingNotification\notifyAutoApproveResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoApprove($notifyAutoApproveRequest){

        isAuthorized();

        $this->db->trans_start();

        // Insert into porting Evolution state table

        $portingId = $notifyAutoApproveRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO APPROVE received for ID ' . $portingId);

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyAutoApproveRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::APPROVED,
            'isAutoReached' => true,
            'portingId' => $portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyAutoApproveRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyAutoApproveRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyAutoApproveRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::APPROVED
        );

        $this->Porting_model->update_porting($notifyAutoApproveRequest->portingTransaction->portingId, $portingParams);

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting AUTO APPROVE saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService->adminErrorReport('PORTING_AUTO_APPROVED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);
            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO APPROVE successful for ID ' . $portingId);

            $this->db->trans_complete();

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService->adminErrorReport('PORTING_REACHED_AUTO_APPROVE', $portingParams, processType::PORTING);

            $response = new PortingNotification\notifyAutoApproveResponse();

            return $response;

        }
    }

    /**
     * @param $notifyAcceptedRequest
     * @return PortingNotification\notifyAcceptedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAccepted($notifyAcceptedRequest){

        isAuthorized();

        $this->db->trans_start();

        $portingId = $notifyAcceptedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ACCEPT received for ID ' . $portingId);

        // Insert into porting Evolution state table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyAcceptedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ACCEPTED,
            'isAutoReached' => false,
            'portingId' => $notifyAcceptedRequest->portingTransaction->portingId,
        );


        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyAcceptedRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyAcceptedRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyAcceptedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ACCEPTED
        );

        $this->Porting_model->update_porting($portingId, $portingParams);

        // Send SMS to Subscriber

        // Get porting Info for language
        $portingInfo = $this->Porting_model->get_porting($portingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyAcceptedRequest->portingTransaction->numberRanges->numberRange->startNumber;

        $portingDateTime = $notifyAcceptedRequest->portingTransaction->portingDateTime;

        $day = date('d/m/Y', strtotime($portingDateTime));
        $start_time = date('H:i:s', strtotime($portingDateTime));
        $end_time = date('H:i:s', strtotime('+2 hours', strtotime($portingDateTime)));

        $smsResponse = SMS::OPR_Subscriber_OK($language, $subscriberMSISDN, $day, $start_time, $end_time);

        if($smsResponse['success']){

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting ACCEPT SMS sent successful for $portingId");

            // Insert Porting SMS Notification
            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_OK,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

        }
        else{

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting ACCEPT SMS sent failed for $portingId");

            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_OK,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting ACCEPT saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('PORTING_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);
            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ACCEPT successful for ID ' . $portingId);

            $this->db->trans_complete();

            $response = new PortingNotification\notifyAcceptedResponse();

            return $response;

        }

    }

    /**
     * TODO: OK
     * @param $notifyAutoAcceptRequest
     * @return PortingNotification\notifyAutoAcceptResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoAccept($notifyAutoAcceptRequest){

        isAuthorized();

        $this->db->trans_start();

        $portingId = $notifyAutoAcceptRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO ACCEPT received for ID ' . $portingId);

        // Insert into porting Evolution state table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyAutoAcceptRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ACCEPTED,
            'isAutoReached' => true,
            'portingId' => $notifyAutoAcceptRequest->portingTransaction->portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyAutoAcceptRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyAutoAcceptRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyAutoAcceptRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ACCEPTED
        );

        $this->Porting_model->update_porting($portingId, $portingParams);

        // Send SMS to Subscriber

        // Get porting Info for language
        $portingInfo = $this->Porting_model->get_porting($portingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyAutoAcceptRequest->portingTransaction->numberRanges->numberRange->startNumber;

        $portingDateTime = $notifyAutoAcceptRequest->portingTransaction->portingDateTime;

        $day = date('d/m/Y', strtotime($portingDateTime));
        $start_time = date('H:i:s', strtotime($portingDateTime));
        $end_time = date('H:i:s', strtotime('+2 hours', strtotime($portingDateTime)));

        $smsResponse = SMS::OPR_Subscriber_OK($language, $subscriberMSISDN, $day, $start_time, $end_time);

        if($smsResponse['success']){

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting AUTO ACCEPT SMS sent successful for $portingId");

            // Insert Porting SMS Notification
            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_OK,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

        }
        else{

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting AUTO ACCEPT SMS sent failed for $portingId");

            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_OK,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting AUTO ACCEPT saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService->adminErrorReport('PORTING_AUTO_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

        }

        $this->db->trans_complete();

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO ACCEPT successful for ID ' . $portingId);

        $portingParams = $this->Porting_model->get_porting($portingId);

        $emailService->adminErrorReport('PORTING_REACHED_AUTO_ACCEPT', $portingParams, processType::PORTING);

        $response = new PortingNotification\notifyAutoAcceptResponse();

        return $response;

    }

    /**
     * @param $notifyAutoConfirmRequest
     * @return PortingNotification\notifyAutoConfirmResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAutoConfirm($notifyAutoConfirmRequest){

        isAuthorized();

        $portingId = $notifyAutoConfirmRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO CONFIRM received for ID ' . $portingId);

        $donorNetworkId = $notifyAutoConfirmRequest->portingTransaction->donorNrn->networkId;

        $subscriberMSISDN = $notifyAutoConfirmRequest->portingTransaction->numberRanges->numberRange->startNumber;

        $emailService = new EmailService();

        $dbPortingParams = $this->Porting_model->get_porting($portingId);

        // Alert admin
        $emailService->adminErrorReport('PORTING_REACHED_AUTO_CONFIRM', $dbPortingParams, processType::PORTING);

        // Start porting process
        $portingStatedResponse = $this->startPortingOPR($subscriberMSISDN, $donorNetworkId);

        if($portingStatedResponse->success){

            $this->db->trans_start();

            // Insert into porting Evolution state table

            $portingEvolutionParams = array(
                'lastChangeDateTime' => $notifyAutoConfirmRequest->portingTransaction->lastChangeDateTime,
                'portingState' => \PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED,
                'isAutoReached' => true,
                'portingId' => $notifyAutoConfirmRequest->portingTransaction->portingId,
            );

            $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

            // Update Porting table

            $portingParams = array(
                'portingDateTime' => $notifyAutoConfirmRequest->portingTransaction->portingDateTime,
                'cadbOrderDateTime' => $notifyAutoConfirmRequest->portingTransaction->cadbOrderDateTime,
                'lastChangeDateTime' => $notifyAutoConfirmRequest->portingTransaction->lastChangeDateTime,
                'portingState' => \PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED
            );

            $this->Porting_model->update_porting($portingId, $portingParams);

            if ($this->db->trans_status() === FALSE) {

                $error = $this->db->error();

                $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting AUTO CONFIRM saving failed for ID ' . $portingId);

                $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

                $portingParams = $this->Porting_model->get_porting($portingId);

                $emailService->adminErrorReport('PORTING_AUTO_CONFIRMED_AND_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);
                $this->db->trans_complete();
                throw new ldbAdministrationServiceFault();

            }else{

                $this->db->trans_complete();

                $this->fileLogAction('8060', 'PortingNotificationService', 'Porting AUTO CONFIRM successful for ID ' . $portingId);

                $response = new PortingNotification\notifyAutoConfirmResponse();

                return $response;

            }

        }
        else {

            $faultCode = $portingStatedResponse->error;

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

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);

            $response = new PortingNotification\notifyAutoConfirmResponse();

            return $response;

        }
    }

    /**
     *
     * @param $notifyDeniedRequest
     * @return PortingNotification\notifyDeniedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyDenied($notifyDeniedRequest){

        isAuthorized();

        $this->db->trans_start();

        $portingId = $notifyDeniedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting DENY received for ID ' . $portingId);

        // Insert into porting Evolution state table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyDeniedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::DENIED,
            'isAutoReached' => false,
            'portingId' => $notifyDeniedRequest->portingTransaction->portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyDeniedRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyDeniedRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyDeniedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::DENIED
        );

        $this->Porting_model->update_porting($portingId, $portingParams);

        // Insert into PortingDenyRejectionAbandoned

        $pdraParams = array(
            'denyRejectionReason' => $notifyDeniedRequest->denialReason,
            'cause' => $notifyDeniedRequest->cause,
            'portingId' => $portingId
        );

        $this->Portingdenyrejectionabandon_model->add_portingdenyrejectionabandon($pdraParams);

        // Send SMS to Subscriber

        // Get porting Info for language
        $portingInfo = $this->Porting_model->get_porting($portingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyDeniedRequest->portingTransaction->numberRanges->numberRange->startNumber;

        $smsResponse = SMS::OPR_Subscriber_KO($language, $subscriberMSISDN);

        if($smsResponse['success']){

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting DENY SMS sent successful for $portingId");

            // Insert Porting SMS Notification
            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_KO,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting DENY SMS sent failed for $portingId");

            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_KO,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );
        }

        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting DENY saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('PORTING_REJECTED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);
            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->db->trans_complete();

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting DENY successful for ID ' . $portingId);

            $response = new PortingNotification\notifyDeniedResponse();

            return $response;

        }

    }

    /**
     * @param $notifyRejectedRequest
     * @return PortingNotification\notifyRejectedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyRejected($notifyRejectedRequest){

        isAuthorized();

        $portingId = $notifyRejectedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting REJECT received for ID ' . $portingId);

        $this->db->trans_start();

        // Insert into porting Evolution state table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyRejectedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::REJECTED,
            'isAutoReached' => false,
            'portingId' => $notifyRejectedRequest->portingTransaction->portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyRejectedRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyRejectedRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyRejectedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::REJECTED
        );

        $this->Porting_model->update_porting($portingId, $portingParams);

        // Insert into PortingDenyRejectionAbandoned

        $pdraParams = array(
            'denyRejectionReason' => $notifyRejectedRequest->rejectionReason,
            'cause' => $notifyRejectedRequest->cause,
            'portingId' => $portingId
        );

        $this->Portingdenyrejectionabandon_model->add_portingdenyrejectionabandon($pdraParams);

        // Send SMS to Subscriber

        // Get porting Info for language
        $portingInfo = $this->Porting_model->get_porting($portingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyRejectedRequest->portingTransaction->numberRanges->numberRange->startNumber;

        if($notifyRejectedRequest->rejectionReason == \PortingService\Porting\rejectionReasonType::SUBSCRIBER_CANCELLED_PORTING){

            $smsResponse = SMS::OPR_Subscriber_Cancellation($language, $subscriberMSISDN);

        }else{

            $smsResponse = SMS::OPR_Subscriber_KO($language, $subscriberMSISDN);

        }

        if($smsResponse['success']){

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting REJECT SMS sent successful for $portingId");

            // Insert Porting SMS Notification
            $smsNotificationparams = array(
                'portingId' => $portingId,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

            if($notifyRejectedRequest->rejectionReason == \PortingService\Porting\rejectionReasonType::SUBSCRIBER_CANCELLED_PORTING){

                $smsNotificationparams['smsType'] = SMSType::OPR_PORTING_CANCELLED;

            }else{

                $smsNotificationparams['smsType'] = SMSType::OPR_PORTING_KO;

            }

        }else{

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting REJECT SMS sent failed for $portingId");

            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_KO,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting REJECT saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('PORTING_REJECTED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);
            $this->db->trans_complete();
            throw new ldbAdministrationServiceFault();

        }else{

            $this->db->trans_complete();

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting REJECT successful for ID ' . $portingId);

            $response = new PortingNotification\notifyRejectedResponse();

            return $response;

        }

    }

    /**
     * @param notifyAbandonedRequest
     * @return PortingNotification\notifyAbandonedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyAbandoned($notifyAbandonedRequest){

        isAuthorized();

        $this->db->trans_start();

        $portingId = $notifyAbandonedRequest->portingTransaction->portingId;

        $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ABANDON received for ID ' . $portingId);

        // Insert into porting Evolution state table

        $portingEvolutionParams = array(
            'lastChangeDateTime' => $notifyAbandonedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ABANDONED,
            'isAutoReached' => false,
            'portingId' => $notifyAbandonedRequest->portingTransaction->portingId,
        );

        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

        // Update Porting table

        $portingParams = array(
            'portingDateTime' => $notifyAbandonedRequest->portingTransaction->portingDateTime,
            'cadbOrderDateTime' => $notifyAbandonedRequest->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $notifyAbandonedRequest->portingTransaction->lastChangeDateTime,
            'portingState' => \PortingService\Porting\portingStateType::ABANDONED
        );

        $this->Porting_model->update_porting($portingId, $portingParams);

        // Insert into PortingDenyRejectionAbandoned

        $pdraParams = array(
            'cause' => $notifyAbandonedRequest->cause,
            'portingId' => $portingId
        );

        $this->Portingdenyrejectionabandon_model->add_portingdenyrejectionabandon($pdraParams);

        // Send SMS to Subscriber
        $portingInfo = $this->Porting_model->get_porting($portingId);

        $language = $portingInfo['language'];

        $subscriberMSISDN = $notifyAbandonedRequest->portingTransaction->numberRanges->numberRange->startNumber;

        $smsResponse =  SMS::Subscriber_CADB_Abandoned($language, $subscriberMSISDN);

        if($smsResponse['success']){

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting ABANDON SMS sent successful for $portingId");

            // Insert Porting SMS Notification
            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_KO,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

        } else{

            $this->fileLogAction('8060', 'PortingNotificationService', "Porting ABANDON SMS sent failed for $portingId");

            $smsNotificationparams = array(
                'portingId' => $portingId,
                'smsType' => SMSType::OPR_PORTING_KO,
                'message' => $smsResponse['message'],
                'msisdn' => $smsResponse['msisdn'],
                'creationDateTime' => date('c'),
                'status' => smsState::PENDING,
                'attemptCount' => 1,
            );

        }

        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

        $emailService = new EmailService();

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();

            $this->fileLogAction($error['code'], 'PortingNotificationService', 'Porting ABANDON saving failed for ID ' . $portingId);

            $this->fileLogAction($error['code'], 'PortingNotificationService', $error['message']);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService->adminErrorReport('PORTING_ABANDONED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

            $this->db->trans_complete();

            throw new ldbAdministrationServiceFault();

        }else{

            $this->db->trans_complete();

            $this->fileLogAction('8060', 'PortingNotificationService', 'Porting ABANDON successful for ID ' . $portingId);

            $portingParams = $this->Porting_model->get_porting($portingId);

            $emailService = new EmailService();
            $emailService->adminErrorReport('Porting ABANDONED BY CADB', $portingParams, processType::PORTING);

            $response = new PortingNotification\notifyAbandonedResponse();

            return $response;

        }

    }

    /**
     * Starts Porting Process for OPR
     * @param $portingNumber
     * @return errorResponse
     */
    private function startPortingOPR($portingNumber, $donorNetworkId){

        // Import MSISDN
        $bscsOperationService = new BscsOperationService();

        $response = $bscsOperationService->importMSISDN($portingNumber, $donorNetworkId);

        return $response;

    }

}