<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "third_party/vendor/autoload.php";

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Porting.php";
require_once APPPATH . "controllers/cadb/Rollback.php";
require_once APPPATH . "controllers/cadb/ProvisionNotification.php";
require_once APPPATH . "controllers/cadb/Return.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/kpsa/KpsaOperationService.php";
require_once APPPATH . "controllers/cadb/PortingOperationService.php";
require_once APPPATH . "controllers/cadb/RollbackOperationService.php";
require_once APPPATH . "controllers/cadb/ReturnOperationService.php";
require_once APPPATH . "controllers/cadb/ProvisionOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use PortingService\Porting\portingSubmissionStateType as portingSubmissionStateType;
use \RollbackService\Rollback\rollbackSubmissionStateType as rollbackSubmissionStateType;
use ReturnService\_Return\returnSubmissionStateType as returnSubmissionStateType;
use \ProvisionService\ProvisionNotification\provisionStateType as provisionStateType;

use phpseclib\Net\SFTP;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/13/2016
 * Time: 2:37 PM
 */
class BatchOperationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        // Load models

        $this->load->model('Porting_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbacksmsnotification_model');
        $this->load->model('Rollbackstateevolution_model');

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

        $this->load->model('Provisioning_model');
        $this->load->model('FileLog_model');

        $this->load->model('Ussdsmsnotification_model');
        $this->load->model('Error_model');

        set_time_limit(0);

    }

    public function index(){

        echo date('Ymd');

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){
        
        $this->FileLog_model->write_log($code, $class, $message);

    }

    /**
     * TODO: OK
     * Executed as OPR
     * BATCH_002
     * Checks for all ports in Submission table in STARTED state and attempts making Orders for them
     */
    public function portingSubmissionToOrdered(){

        $this->fileLogAction('7001', 'BatchOperationService::portingSubmissionToOrdered', 'portingSubmissionToOrdered STARTED');

        // Load ports in Submission table in STARTED state

        $startedPorts = $this->Portingsubmission_model->get_submissionByState(portingSubmissionStateType::STARTED);

        $this->fileLogAction('7001', 'BatchOperationService::portingSubmissionToOrdered', 'Preparing ORDER of ' . count($startedPorts) . ' submitted ports');

        $portingOperationService = new PortingOperationService();

        $emailService = new EmailService();

        foreach ($startedPorts as $startedPort){

            // Retrieve Port params

            $portingSubmissionId = $startedPort['portingSubmissionId'];

            $this->fileLogAction('7001', 'BatchOperationService::portingSubmissionToOrdered', 'Performing ORDER for ' . $portingSubmissionId);

            $donorNetworkId = $startedPort['donorNetworkId'];
            $portingMsisdn = $startedPort['portingMSISDN'];
            $rio = $startedPort['rio'];
            $physicalPersonFirstName = $startedPort['physicalPersonFirstName'];
            $physicalPersonLastName = $startedPort['physicalPersonLastName'];
            $physicalPersonIdNumber = $startedPort['physicalPersonIdNumber'];
            $legalPersonName = $startedPort['legalPersonName'];
            $legalPersonTin = $startedPort['legalPersonTin'];
            $contactNumber = $startedPort['contactNumber'];
            $portingDateTime = $startedPort['portingDateTime'];
            $subscriberSubmissionDateTime = $startedPort['subscriberSubmissionDateTime'];
            $contractId = $startedPort['contractId'];
            $language = $startedPort['language'];

            // Construct subscriber info

            $subscriberInfo = new \PortingService\Porting\subscriberInfoType();

            if($physicalPersonFirstName){
                $subscriberInfo->physicalPersonFirstName = $physicalPersonFirstName;
                $subscriberInfo->physicalPersonLastName = $physicalPersonLastName;
                $subscriberInfo->physicalPersonIdNumber = $physicalPersonIdNumber;
            }else{
                $subscriberInfo->legalPersonName = $legalPersonName;
                $subscriberInfo->legalPersonTin = $legalPersonTin;
                $subscriberInfo->contactNumber = $contactNumber;
            }

            // Set donor Operator 0 == MTN, 1 == Nexttel

            $donorOperator = null;

            if($donorNetworkId == Operator::MTN_NETWORK_ID){

                $donorOperator = 0;

            }elseif($donorNetworkId == Operator::NEXTTEL_NETWORK_ID){

                $donorOperator = 1;

            }

            // Make Order Porting Operation

            $orderResponse = $portingOperationService->order($donorOperator, $portingDateTime, $portingMsisdn, $rio, $subscriberInfo);

            // Verify response

            if($orderResponse->success){

                $this->fileLogAction('7001', 'BatchOperationService::portingSubmissionToOrdered', 'Successful ORDER of ' . $portingSubmissionId);

                $this->db->trans_start();

                // Update submission table with submission state ordered

                $submissionParams = array(
                    'submissionState' => \PortingService\Porting\portingSubmissionStateType::ORDERED,
                    'orderedDateTime' => date('c')
                );

               $this->Portingsubmission_model->update_portingsubmission($portingSubmissionId, $submissionParams);

                // Fill in porting table with state ordered

                $portingParams = array(
                    'portingId' => $orderResponse->portingTransaction->portingId,
                    'recipientNetworkId' => $orderResponse->portingTransaction->recipientNrn->networkId,
                    'recipientRoutingNumber' => $orderResponse->portingTransaction->recipientNrn->routingNumber,
                    'donorNetworkId' => $orderResponse->portingTransaction->donorNrn->networkId,
                    'donorRoutingNumber' => $orderResponse->portingTransaction->recipientNrn->routingNumber,
                    'recipientSubmissionDateTime' => $orderResponse->portingTransaction->recipientSubmissionDateTime,
                    'portingDateTime' => $orderResponse->portingTransaction->portingDateTime,
                    'rio' =>  $orderResponse->portingTransaction->rio,
                    'startMSISDN' =>  $orderResponse->portingTransaction->numberRanges->numberRange->startNumber,
                    'endMSISDN' =>  $orderResponse->portingTransaction->numberRanges->numberRange->endNumber,
                    'cadbOrderDateTime' => $orderResponse->portingTransaction->cadbOrderDateTime,
                    'lastChangeDateTime' => $orderResponse->portingTransaction->lastChangeDateTime,
                    'portingState' => \PortingService\Porting\portingStateType::ORDERED,
                    'contractId' => $contractId,
                    'language' => $language,
                    'portingNotificationMailSendStatus' => smsState::CLOSED,
                    'portingNotificationMailSendDateTime' => date('c'),
                    'portingSubmissionId' => $portingSubmissionId,
                );

                if($physicalPersonFirstName) {
                    $portingParams['physicalPersonFirstName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonFirstName;
                    $portingParams['physicalPersonLastName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonLastName;
                    $portingParams['physicalPersonIdNumber'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonIdNumber;

                }
                else{
                    $portingParams['legalPersonName'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonName;
                    $portingParams['legalPersonTin'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonTin;
                    $portingParams['contactNumber'] = $orderResponse->portingTransaction->subscriberInfo->contactNumber;
                }

                $this->Porting_model->add_porting($portingParams);


                // Fill in portingStateEvolution table with state ordered

                $portingEvolutionParams = array(
                    'lastChangeDateTime' => $orderResponse->portingTransaction->lastChangeDateTime,
                    'portingState' => \PortingService\Porting\portingStateType::ORDERED,
                    'isAutoReached' => false,
                    'portingId' => $orderResponse->portingTransaction->portingId,
                );

                $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService->adminErrorReport('PORTING_SUBMISSION_ORDERED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

                }else{

                }

                $this->db->trans_complete();

            }

            else{

                $fault = $orderResponse->error;

                $this->fileLogAction('7001', 'BatchOperationService::portingSubmissionToOrdered', 'Failed ORDER of ' . $portingSubmissionId . ' with ' . $fault);

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $currentDateTime = date('c');

                        $start_time = date('y-d-m H:i:s', strtotime($currentDateTime));
                        $end_time = date('y-d-m H:i:s', strtotime($subscriberSubmissionDateTime));

                        $start_time = date_create_from_format('y-d-m H:i:s', $start_time);
                        $end_time = date_create_from_format('y-d-m H:i:s', $end_time);

                        $diff = date_diff($start_time, $end_time);

                        // More than 20 minutes difference
                        if($diff->i > 20){

                            $params = array('errorMessage' => 'Retarded porting submission encountered for ' . $portingMsisdn);

                            $emailService->error('RETARDED PORTING SUBMISSION DETECTED', $params);

                        }

                        break;

                    case Fault::NUMBER_NOT_OWNED_BY_OPERATOR:
                    case Fault::UNKNOWN_NUMBER:
                    case Fault::TOO_NEAR_PORTED_PERIOD:
                    case Fault::PORTING_NOT_ALLOWED_REQUESTS:
                    case Fault::RIO_NOT_VALID:
                    case Fault::NUMBER_RESERVED_BY_PROCESS:
                    case Fault::INVALID_PORTING_DATE_AND_TIME:
                    case Fault::NUMBER_RANGES_OVERLAP:
                    case Fault::NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    case Fault::SUBSCRIBER_DATA_MISSING:
                    default:

                    $portingParams = array(
                        'portingId' => '',
                        'recipientNetworkId' => '',
                        'donorNetworkId' => '',
                        'recipientSubmissionDateTime' => date('c'),
                        'rio' =>  '',
                        'startMSISDN' =>  $portingMsisdn,
                        'lastChangeDateTime' => date('c'),
                        'portingState' => 'NONE'
                    );

                        $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);
                }

            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPD
     * BATCH_001
     * Checks for all ports in ORDERED state, performs actions for Approval / Denial
     */
    public function portingOrderedToApprovedDenied(){

        // Load ports in Porting table in ORDERED state in which we are OPD

        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'portingOrderedToApprovedDenied STARTED');

        $orderedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::ORDERED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Preparing APPROVAL/DENIAL of ' . count($orderedPorts) . ' ORDERED ports');

        $bscsOperationService = new BscsOperationService();

        $portingOperationService = new PortingOperationService();

        $emailService = new EmailService();

        foreach ($orderedPorts as $orderedPort) {

            $portingId = $orderedPort['portingId'];

            $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Performing APPROVE/DENY for ' . $portingId);

            // Load subscriber data from BSCS using MSISDN

            $subscriberMSISDN = $orderedPort['startMSISDN'];

            $recipientNetworkId = $orderedPort['recipientNetworkId'];

            $subscriberInfo = $bscsOperationService->loadNumberInfo($subscriberMSISDN);

            $portingDenialReason = null;
            $cause = null;

            if($subscriberInfo != -1){

                if($subscriberInfo != null){ // Connection to BSCS successful and User found

                    // Update Porting table

                    $portingParams = array(
                        'contractId' => $subscriberInfo['CONTRACT_ID'],
                        'language' => $subscriberInfo['LANGUE']
                    );

                    $this->Porting_model->update_porting($portingId, $portingParams);

                    // Number Owned by Orange

                    $subscriberRIO = RIO::get_rio($subscriberMSISDN);

                    if($subscriberRIO == $orderedPort['rio']){

                        // Subscriber RIO Valid

                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Valid RIO for ' . $portingId);

                        // Send SMS to Subscriber

                        if($recipientNetworkId == Operator::MTN_NETWORK_ID){

                            $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_MTN;

                        }elseif($recipientNetworkId == Operator::NEXTTEL_NETWORK_ID){

                            $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;

                        }

                        $smsResponse = SMS::OPD_Inform_Subcriber($subscriberInfo['LANGUE'], $subscriberMSISDN, $denom_OPR, $portingId);

                        if($smsResponse['success'] == true){

                            // Insert Porting SMS Notification
                            $smsNotificationparams = array(
                                'portingId' => $portingId,
                                'smsType' => SMSType::OPD_PORTING_INIT,
                                'message' => $smsResponse['message'],
                                'msisdn' => $smsResponse['msisdn'],
                                'creationDateTime' => date('c'),
                                'status' => smsState::SENT,
                                'attemptCount' => 1,
                                'sendDateTime' => date('c')
                            );

                        }else{

                            $smsNotificationparams = array(
                                'portingId' => $portingId,
                                'smsType' => SMSType::OPD_PORTING_INIT,
                                'message' => $smsResponse['message'],
                                'msisdn' => $smsResponse['msisdn'],
                                'creationDateTime' => date('c'),
                                'status' => smsState::PENDING,
                                'attemptCount' => 1,
                            );
                        }

                        $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

                        /*// Check subscriber type
                        if($orderedPort['physicalPersonFirstName']){

                            // Physical Person
                            if(strtolower($subscriberInfo['firstName']) == strtolower($orderedPort['physicalPersonFirstName'])){

                                // Valid First Name

                                if(strtolower($subscriberInfo['lastName']) == strtolower($orderedPort['physicalPersonLastName'])){

                                    // Valid Last Name
                                    if(strtolower($subscriberInfo['idNumber']) == strtolower($orderedPort['physicalPersonIdNumber'])){

                                        // Valid ID Number

                                    }else{
                                        // Invalid ID Number
                                        $portingDenialReason = \PortingService\Porting\denialReasonType::SUBSCRIBER_DATA_DISCREPANCY;
                                        $cause = 'Invalid ID Number';
                                    }

                                    }else{
                                    // Invalid Last Name
                                    $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                                    $cause = 'Invalid Last Name';
                                }

                                }else{
                                // Invalid First Name
                                $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                                $cause = 'Invalid First Name';
                            }

                        }
                        else{
                            // Legal Person
                            if(strtolower($subscriberInfo['personName']) == strtolower($orderedPort['legalPersonName'])){

                                // Valid Person Name

                                if(strtolower($subscriberInfo['personTIN']) == strtolower($orderedPort['legalPersonTin'])){

                                    // Valid Person TIN
                                    if(strtolower($subscriberInfo['contactNumber']) == strtolower($orderedPort['contactNumber'])){

                                        // Valid contact Number

                                    }else{
                                        // Invalid contact Number
                                        $portingDenialReason = \PortingService\Porting\denialReasonType::SUBSCRIBER_DATA_DISCREPANCY;
                                        $cause = 'Invalid Contact Number';
                                    }

                                }else{
                                    // Invalid Person TIN
                                    $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                                    $cause = 'Invalid Person TIN';
                                }

                            }else{
                                // Invalid Person Name
                                $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                                $cause = 'Invalid Person Name';
                            }

                        }*/

                    }else{
                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Invalid RIO for ' . $portingId . '. Generated RIO is ' . $subscriberRIO);
                        // Subscriber RIO Invalid
                        $portingDenialReason = \PortingService\Porting\denialReasonType::RIO_NOT_VALID;
                        $cause = 'Invalid RIO';
                    }

                }
                else{ // BSCS returns this in case of in existent user
                    $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Subscriber not found in BSCS for ' . $portingId);
                    // Number not owned by Orange
                    $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                    $cause = 'Unknown Number';
                }

                if($portingDenialReason == null) {
                    // All Checks OK. Approve Port

                    $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Approving porting for ' . $portingId);

                    $approveResponse = $portingOperationService->approve($portingId);

                    if($approveResponse->success){

                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'APPROVE successful for ' . $portingId);

                        // Insert into porting state evolution table

                        $this->db->trans_start();

                        // Insert into porting Evolution state table

                        $portingEvolutionParams = array(
                            'lastChangeDateTime' => $approveResponse->portingTransaction->lastChangeDateTime,
                            'portingState' => \PortingService\Porting\portingStateType::APPROVED,
                            'isAutoReached' => false,
                            'portingId' => $approveResponse->portingTransaction->portingId,
                        );

                        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                        // Update Porting table

                        $portingParams = array(
                            'portingDateTime' => $approveResponse->portingTransaction->portingDateTime,
                            'cadbOrderDateTime' => $approveResponse->portingTransaction->cadbOrderDateTime,
                            'lastChangeDateTime' => $approveResponse->portingTransaction->lastChangeDateTime,
                            'portingState' => \PortingService\Porting\portingStateType::APPROVED
                        );

                        $this->Porting_model->update_porting($portingId, $portingParams);

                        // Notify Agents/Admin

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();
                            $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                            $portingParams = $this->Porting_model->get_porting($portingId);

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING_APPROVED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

                        }else{

                        }

                        $this->db->trans_complete();

                    }
                    else{

                        $fault = $approveResponse->error;

                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'APPROVE failed for ' . $portingId . ' with ' . $fault);

                        switch ($fault) {
                            // Terminal Processes
                            case Fault::INVALID_OPERATOR_FAULT:
                            case Fault::INVALID_REQUEST_FORMAT:
                            case Fault::PORTING_ACTION_NOT_AVAILABLE:
                            case Fault::INVALID_PORTING_ID:
                            default:

                                $portingParams = $this->Porting_model->get_porting($portingId);

                                $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);

                        }

                    }

                }
                else{

                    $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Denying porting for ' . $portingId);

                    // Failed Check. Deny Port
                    $denyResponse = $portingOperationService->deny($portingId, $portingDenialReason, $cause);

                    if($denyResponse->success){

                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'DENY successful for ' . $portingId);

                        // Insert into porting state evolution table

                        $this->db->trans_start();

                        // Insert into porting Evolution state table

                        $portingEvolutionParams = array(
                            'lastChangeDateTime' => $denyResponse->portingTransaction->lastChangeDateTime,
                            'portingState' => \PortingService\Porting\portingStateType::DENIED,
                            'isAutoReached' => false,
                            'portingId' => $denyResponse->portingTransaction->portingId,
                        );

                        $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                        // Update Porting table

                        $portingParams = array(
                            'portingDateTime' => $denyResponse->portingTransaction->portingDateTime,
                            'cadbOrderDateTime' => $denyResponse->portingTransaction->cadbOrderDateTime,
                            'lastChangeDateTime' => $denyResponse->portingTransaction->lastChangeDateTime,
                            'portingState' => \PortingService\Porting\portingStateType::DENIED
                        );

                        $this->Porting_model->update_porting($portingId, $portingParams);

                        // Insert into PortingDenyRejectionAbandoned

                        $pdraParams = array(
                            'denyRejectionReason' => $portingDenialReason,
                            'cause' => $cause,
                            'portingId' => $portingId
                        );

                        $this->Portingdenyrejectionabandon_model->add_portingdenyrejectionabandon($pdraParams);

                        // Notify Agents/Admin

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();
                            $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                            $portingParams = $this->Porting_model->get_porting($portingId);

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING_DENIED_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

                        }else{

                        }

                        $this->db->trans_complete();

                    }
                    else{

                        $fault = $denyResponse->error;

                        $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'DENY failed for ' . $portingId . ' with ' . $fault);

                        switch ($fault) {
                            // Terminal Processes
                            case Fault::INVALID_OPERATOR_FAULT:
                            case Fault::INVALID_REQUEST_FORMAT:
                            case Fault::PORTING_ACTION_NOT_AVAILABLE:
                            case Fault::INVALID_PORTING_ID:
                            case Fault::CAUSE_MISSING:
                            default:

                                $portingParams = $this->Porting_model->get_porting($portingId);

                                $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);

                        }

                    }

                }

            }else{
                // Connection to BSCS failed. Wait and try again later
                $this->fileLogAction('7002', 'BatchOperationService::portingOrderedToApprovedDenied', 'Connection to BSCS failed for ' . $portingId);
            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPD
     * BATCH_003
     * Checks for all individual ports in APPROVED state and sends mail for their Acceptance / Rejection
     */
    public function portingApprovedToAcceptedRejected(){

        $this->fileLogAction('7003', 'BatchOperationService::portingApprovedToAcceptedRejected', 'portingApprovedToAcceptedRejected STARTED');

        // Load ports in Porting table in APPROVED state in which we are OPD AND Personal

        $approvedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::APPROVED, Operator::ORANGE_NETWORK_ID, 0);

        $this->fileLogAction('7003', 'BatchOperationService::portingApprovedToAcceptedRejected', 'Preparing Email for ACCEPTANCE/REJECTION of ' . count($approvedPorts) . ' approved ports');

        $emailService = new EmailService();

        foreach ($approvedPorts as $approvedPort){

            // Verify if mail notification sent
            if($approvedPort['portingNotificationMailSendStatus'] == smsState::PENDING){
                // Send mail to Back Office with Admin in CC for Acceptance / Rejection

                $this->fileLogAction('7003', 'BatchOperationService::portingApprovedToAcceptedRejected', 'Sending Email for ' . $approvedPort['portingId']);

                $response = $emailService->backOfficePortingAcceptReject($approvedPort);

                if($response){

                    $this->fileLogAction('7003', 'BatchOperationService::portingApprovedToAcceptedRejected', 'Successful Email delivery for ' . $approvedPort['portingId']);

                    // Update State in DB

                    $portingParams = array(
                        'portingNotificationMailSendStatus' => smsState::SENT,
                        'portingNotificationMailSendDateTime' =>  date('c')
                    );

                    $this->Porting_model->update_porting($approvedPort['portingId'], $portingParams);

                }
            }
        }
    }

    /**
     * TODO: INCOMPLETE
     * Checks for all enterprise ports in APPROVED state and sends mail for their Acceptance / Rejection
     */
    public function portingApprovedToAcceptedRejectedEnterprise(){

        $this->fileLogAction('7004', 'BatchOperationService::portingApprovedToAcceptedRejectedEnterprise', 'portingApprovedToAcceptedRejectedEnterprise STARTED');

        // Load ports in Porting table in APPROVED state in which we are OPD AND Enterprise

        $approvedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::APPROVED, Operator::ORANGE_NETWORK_ID, 1);

        $this->fileLogAction('7004', 'BatchOperationService::portingApprovedToAcceptedRejectedEnterprise', 'Preparing Email for ACCEPTANCE/REJECTION of ' . count($approvedPorts) . ' approved enterprise ports');

        $emailService = new EmailService();

        $enterpriseGrouping = array(); // Group ports by enterprise

        foreach ($approvedPorts as $approvedPort){

            $is_added = false;

            foreach ($enterpriseGrouping as &$enterprisePort){

                if($enterprisePort['legalPersonTin'] == $approvedPort['legalPersonTin']){
                    $is_added = true;
                    $enterprisePort['portingIds'][] = $approvedPort['portingId'];
                    $enterprisePort['MSISDNs'][] = $approvedPort['startMSISDN'];
                    break;
                }

            }

            if(!$is_added){
                $tmpPort = $approvedPort;
                $tmpPort['portingIds'] = [];
                $tmpPort['portingIds'][] = $approvedPort['portingId'];
                $tmpPort['MSISDNs'][] = $approvedPort['startMSISDN'];
                $enterpriseGrouping[] = $tmpPort;
            }

        }

        foreach ($enterpriseGrouping as $enterprisePort){
            // Send mail to Back Office with Admin in CC for Acceptance / Rejection
            $emailService->backOfficePortingAcceptReject($enterprisePort);
        }

    }

    /**
     * TODO: OK
     * Executed as OPD
     * BATCH_004_{A, B, C}
     * Checks for all ports in ACCEPTED state, if any performs porting to CONTRACT_DELETED_CONFIRMED state
     * Checks for all ports in CONTRACT_DELETED_CONFIRMED state, if any performs porting to MSISDN_EXPORT_CONFIRMED, state
     * Checks for all ports in MSISDN_EXPORT_CONFIRMED state, if any perform porting to CONFIRMED state updating Porting/Provision table
     */
    public function portingOPD(){

        $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'portingOPD STARTED');

        // Load ports in Porting table in ACCEPTED state in which we are OPD

        $acceptedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Preparing CONTRACT_DELETE of ' . count($acceptedPorts) . ' accepted ports');

        // Load ports in Porting table in CONTRACT_DELETED_CONFIRMED state in which we are OPD

        $msisdnContractDeletedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::CONTRACT_DELETED_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Preparing MSISDN_EXPORT of ' . count($msisdnContractDeletedPorts) . ' contract deleted ports');

        // Load ports in Porting table in MSISDN_EXPORT_CONFIRMED state in which we are OPD

        $msisdnExportedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Preparing COMPLETE of ' . count($msisdnExportedPorts) . ' msisdn exported ports');

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedPorts as $acceptedPort){

            $portingId = $acceptedPort['portingId'];

            $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Checking Provisioning of ' . $portingId);

            // Check if port in provision table in state STARTED
            $provisionPort = $this->Provisioning_model->get_provisioning_by_process_state($portingId, processType::PORTING, provisionStateType::STARTED);

            if($provisionPort){

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Process provisioned. Performing CONTRACT_DELETED_CONFIRMED for ' . $portingId);

                // Porting already provisioned. Start porting moving to CONTRACT_DELETED_CONFIRMED state

                $contractId = $acceptedPort['contractId'];

                $deleteResponse = $bscsOperationService->deleteContract($contractId);

                if($deleteResponse->success){

                    $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'CONTRACT_DELETED_CONFIRMED Successful for ' . $portingId);

                    $this->db->trans_start();

                    // Insert into porting Evolution state table

                    $portingEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::CONTRACT_DELETED_CONFIRMED,
                        'isAutoReached' => false,
                        'portingId' => $portingId,
                    );


                    $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                    // Update Porting table

                    $portingParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::CONTRACT_DELETED_CONFIRMED
                    );

                    $this->Porting_model->update_porting($portingId, $portingParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $emailService->adminErrorReport('PORTING_MSISDN_CONTRACT_DELETED_BUT_DB_FILLED_INCOMPLETE', $acceptedPort, processType::PORTING);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    // Notify Admin on failed Export
                    $faultCode = $deleteResponse->error;

                    $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'CONTRACT_DELETED_CONFIRMED failed for ' . $portingId . ' with ' . $faultCode);

                    switch ($faultCode) {
                        // Terminal Processes
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

                    $emailService->adminErrorReport($fault, $acceptedPort, processType::PORTING);

                }

            }else{

                //Port not yet Provisioned. Do nothing, wait till provision

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Process not yet provisioned for ' . $portingId);

            }
        }

        foreach ($msisdnContractDeletedPorts as $msisdnContractDeletedPort){

            $portingId = $msisdnContractDeletedPort['portingId'];

            $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Performing MSISDN_EXPORT_CONFIRMED for ' . $portingId);

            // Porting already provisioned. Start porting moving to MSISDN_EXPORT_CONFIRMED state
            $subscriberMSISDN = $msisdnContractDeletedPort['startMSISDN'];
            $recipientNetworkId = $msisdnContractDeletedPort['recipientNetworkId'];

            $exportResponse = $bscsOperationService->exportMSISDN($subscriberMSISDN, $recipientNetworkId);

            if($exportResponse->success){

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'MSISDN_EXPORT_CONFIRMED Successful for ' . $portingId);

                $this->db->trans_start();

                // Insert into porting Evolution state table

                $portingEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::MSISDN_EXPORT_CONFIRMED,
                    'isAutoReached' => false,
                    'portingId' => $portingId,
                );


                $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                // Update Porting table

                $portingParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::MSISDN_EXPORT_CONFIRMED
                );

                $this->Porting_model->update_porting($portingId, $portingParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService->adminErrorReport('PORTING_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', $msisdnContractDeletedPort, processType::PORTING);

                }else{

                }

                $this->db->trans_complete();

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'MSISDN_EXPORT_CONFIRMED failed for ' . $portingId . ' with ' . $faultCode);


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

                $emailService->adminErrorReport($fault, $msisdnContractDeletedPort, processType::PORTING);

            }
        }

        foreach ($msisdnExportedPorts as $msisdnExportedPort){

            $portingId = $msisdnExportedPort['portingId'];

            $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'Performing KPSA_OPERATION for ' . $portingId);

            $subscriberMSISDN = $msisdnExportedPort['startMSISDN'];

            $fromOperator = $msisdnExportedPort['donorNetworkId'];

            $toOperator = $msisdnExportedPort['recipientNetworkId'];

            $fromRoutingNumber = $msisdnExportedPort['donorRoutingNumber'];

            $toRoutingNumber = $msisdnExportedPort['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->db->trans_start();

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'KPSA_OPERATION Successful for ' . $portingId);

                // Insert into porting Evolution state table

                $portingEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::COMPLETED,
                    'isAutoReached' => false,
                    'portingId' => $portingId,
                );

                $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                // Update Porting table

                $portingParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::COMPLETED
                );

                $this->Porting_model->update_porting($portingId, $portingParams);

                // Update Provisioning table

                $prParams = array(
                    'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED,
                );

                $this->Provisioning_model->update_provisioning($portingId, $prParams);

                // Confirm Routing Data
                $provisionOperationService = new ProvisionOperationService();

                $prResponse = $provisionOperationService->confirmRoutingData($portingId);

                if($prResponse->success){

                    // Process terminated

                }
                else{

                    // Who cares, its auto anyway :)

                }

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService->adminErrorReport('PORTING_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $msisdnExportedPort, processType::PORTING);

                }else{

                }

                $this->db->trans_complete();

            }

            else{

                $this->fileLogAction('7005', 'BatchOperationService::portingOPD', 'KPSA_OPERATION failed for ' . $portingId . ' with ' . $kpsaResponse['message']);

                $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $subscriberMSISDN);

            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPR
     * BATCH_004_{C, D, E}
     * Checks for all ports in ACCEPTED state, if any check porting date and performs porting to MSISDN_IMPORT_CONFIRMED state
     * Checks for all ports in MSISDN_IMPORT_CONFIRMED state, if any, move to MSISDN_CHANGE_IMPORT_CONFIRMED state
     * Checks for all ports in MSISDN_CHANGE_IMPORT_CONFIRMED state, if any, perform porting to CONFIRMED state, sending confirm request and updating Porting table
     */
    public function portingOPR(){

        $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'portingOPR STARTED');

        // Load ports in Porting table in ACCEPTED state in which we are OPR

        $acceptedPorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Preparing MSISDN_IMPORT of ' . count($acceptedPorts) . ' accepted ports');

        // Load ports in Porting table in MSISDN_IMPORT_CONFIRMED state in which we are OPR

        $msisdnConfirmedPorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Preparing MSISDN_CHANGE_IMPORT of ' . count($msisdnConfirmedPorts) . ' imported ports');

        // Load ports in Porting table in MSISDN_CHANGE_IMPORT_CONFIRMED state in which we are OPR

        $msisdnChangePorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::MSISDN_CHANGE_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Preparing CONFIRM of ' . count($msisdnChangePorts) . ' change imported ports');

        $bscsOperationService = new BscsOperationService();

        $portingOperationService = new PortingOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedPorts as $acceptedPort) {

            $portingDateTime = $acceptedPort['portingDateTime'];

            $portingId = $acceptedPort['portingId'];

            $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Checking porting time for ' . $portingId);

            $currentDateTime = date('c');

            $start_time = date('y-d-m H:i:s', strtotime($portingDateTime));
            $end_time = date('y-d-m H:i:s', strtotime($currentDateTime));

            $start_time = date_create_from_format('y-d-m H:i:s', $start_time);
            $end_time = date_create_from_format('y-d-m H:i:s', $end_time);

            $diff = date_diff($start_time, $end_time);

            // End time >= start time, less than 30 minutes difference
            if($diff->i < 30 && $diff->h == 0){

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Time OK. Performing MSISDN_IMPORT for ' . $portingId);

                // Start porting moving to MSISDN_IMPORT_CONFIRMED state. Import Porting MSISDN into BSCS
                $subscriberMSISDN = $acceptedPort['startMSISDN'];

                $donorNetworkId = $acceptedPort['donorNetworkId'];

                $importResponse = $bscsOperationService->importMSISDN($subscriberMSISDN, $donorNetworkId);

                if($importResponse->success){

                    $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'MSISDN_IMPORT Successful for ' . $portingId);

                    $this->db->trans_start();

                    // Insert into porting Evolution state table

                    $portingEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED,
                        'isAutoReached' => false,
                        'portingId' => $portingId,
                    );

                    $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                    // Update Porting table

                    $portingParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED
                    );

                    $this->Porting_model->update_porting($portingId, $portingParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $emailService->adminErrorReport('PORTING_MSISDN_IMPORTED_BUT_DB_FILLED_INCOMPLETE', $acceptedPort, processType::PORTING);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    // Notify Admin on failed Import
                    $faultCode = $importResponse->error;

                    $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'MSISDN_IMPORT failed for ' . $portingId . ' with ' . $faultCode);

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

                    $emailService->adminErrorReport($fault, $acceptedPort, processType::PORTING);

                }

            }
            else if($diff->invert == 1 && $diff->h > 0){

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Process porting time late by more than 1hr for ' . $portingId);

                // More than 1hrs late, alert Admin

                $emailService->adminErrorReport('MORE_THAN_ONE_HOUR_FROM_EXPECTED_PORTING_DATE_TIME', $acceptedPort, processType::PORTING);

            }

        }

        foreach ($msisdnConfirmedPorts as $msisdnConfirmedPort){

            $portingId = $msisdnConfirmedPort['portingId'];

            $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Performing MSISDN_CHANGE_IMPORT for ' . $portingId);

            $subscriberInfo = $this->Portingsubmission_model->get_submissionByPortingId($portingId);

            $subscriberMSISDN = $msisdnConfirmedPort['startMSISDN'];

            $contractId = $msisdnConfirmedPort['contractId'];

            $changeResponse = $bscsOperationService->changeImportMSISDN($subscriberInfo['temporalMSISDN'], $subscriberMSISDN, $contractId);

            if($changeResponse->success){

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'MSISDN_CHANGE_IMPORT Successful for ' . $portingId);

                $this->db->trans_start();

                // Insert into porting Evolution state table

                $portingEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::MSISDN_CHANGE_IMPORT_CONFIRMED,
                    'isAutoReached' => false,
                    'portingId' => $portingId,
                );

                $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                // Update Porting table

                $portingParams = array(
                    'lastChangeDateTime' => date('c'),
                    'portingState' => \PortingService\Porting\portingStateType::MSISDN_CHANGE_IMPORT_CONFIRMED
                );

                $this->Porting_model->update_porting($portingId, $portingParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService->adminErrorReport('PORTING_MSISDN_CHANGED_BUT_DB_FILLED_INCOMPLETE', $msisdnConfirmedPort, processType::PORTING);

                }

                $this->db->trans_complete();

            }
            else{
                // Notify Admin on failed Import
                $faultCode = $changeResponse->error;

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'MSISDN_CHANGE_IMPORT failed for ' . $portingId . ' with ' . $faultCode);

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

                $emailService->adminErrorReport($fault, $msisdnConfirmedPort, processType::PORTING);
            }

        }

        foreach ($msisdnChangePorts as $msisdnChangePort){

            // Move porting to CONFIRMED state
            $fromOperator = $msisdnChangePort['donorNetworkId'];

            $subscriberMSISDN = $msisdnChangePort['startMSISDN'];

            $toOperator = $msisdnChangePort['recipientNetworkId'];

            $fromRoutingNumber = $msisdnChangePort['donorRoutingNumber'];

            $toRoutingNumber = $msisdnChangePort['recipientRoutingNumber'];

            $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'Performing KPSA_OPERATION for ' . $msisdnChangePort['portingId']);

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                // Send confirm request

                $portingId = $msisdnChangePort['portingId'];

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'KPSA_OPERATION Successful for ' . $portingId);

                $portingDateAndTime = date('c', strtotime('+5 minutes', strtotime(date('c'))));

                // Make Confirm Porting Operation

                $confirmResponse = $portingOperationService->confirm($portingId, $portingDateAndTime);

                // Verify response

                if($confirmResponse->success){

                    $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'CONFIRM Successful for ' . $portingId);

                    $this->db->trans_start();

                    // Insert into porting Evolution state table

                    $portingEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::CONFIRMED,
                        'isAutoReached' => false,
                        'portingId' => $portingId,
                    );

                    $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                    // Update Porting table

                    $portingParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::CONFIRMED
                    );

                    $this->Porting_model->update_porting($portingId, $portingParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $emailService->adminErrorReport('PORTING_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $msisdnChangePort, processType::PORTING);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    $fault = $confirmResponse->error;

                    $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'CONFIRM failed for ' . $portingId . ' with ' . $fault);

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                        case Fault::PORTING_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_PORTING_ID:
                        case Fault::INVALID_PORTING_DATE_AND_TIME:
                        default:
                            $emailService->adminConfirmReport($fault, $msisdnChangePort, processType::PORTING);
                    }

                }

            }

            else{

                $this->fileLogAction('7006', 'BatchOperationService::portingOPR', 'KPSA_OPERATION failed for ' . $msisdnChangePort['portingId'] . ' with ' . $kpsaResponse['message']);

                $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $subscriberMSISDN);

            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPD
     * BATCH_005
     * Checks for all rollbacks in Submission table in STARTED state and attempts making open for them
     */
    public function rollbackSubmissionToOpened(){

        $this->fileLogAction('7007', 'BatchOperationService::rollbackSubmissionToOpened', 'rollbackSubmissionToOpened STARTED');

        // Load rollbacks in Submission table in STARTED state

        $startedRollbacks = $this->Rollbacksubmission_model->get_submissionByState(rollbackSubmissionStateType::STARTED);

        $this->fileLogAction('7007', 'BatchOperationService::rollbackSubmissionToOpened', 'Preparing OPEN of ' . count($startedRollbacks) . ' submitted rollbacks');

        $rollbackOperationService = new RollbackOperationService();

        $emailService = new EmailService();

        foreach ($startedRollbacks as $startedRollback){

            $originalPortingId = $startedRollback['originalPortingId'];
            $rollbackSubmissionId = $startedRollback['rollbackSubmissionId'];
            $donorSubmissionDateTime = date('c');
            $preferredRollbackDateTime = $startedRollback['preferredRollbackDateTime'];
            $openedDateTime = $startedRollback['openedDateTime'];

            $this->fileLogAction('7007', 'BatchOperationService::rollbackSubmissionToOpened', 'Performing OPEN for ' . $rollbackSubmissionId);

            // Make Open Rollback Operation

            $openResponse = $rollbackOperationService->open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime);

            // Verify response

            if($openResponse->success){

                $this->fileLogAction('7007', 'BatchOperationService::rollbackSubmissionToOpened', 'Successful OPEN of ' . $rollbackSubmissionId);

                $this->db->trans_start();

                // Update Rollback Submission Table

                $rsParams = array(
                    'submissionState' => rollbackSubmissionStateType::OPENED
                );

                $this->Rollbacksubmission_model->update_rollbacksubmission($rollbackSubmissionId, $rsParams);

                // Insert into Rollback table

                $rollbackParams = array(
                    'rollbackId' => $openResponse->rollbackTransaction->rollbackId,
                    'originalPortingId' => $openResponse->rollbackTransaction->originalPortingId,
                    'donorSubmissionDateTime' => $openResponse->rollbackTransaction->donorSubmissionDateTime,
                    'preferredRollbackDateTime' => $openResponse->rollbackTransaction->preferredRollbackDateTime,
                    'rollbackDateTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                    'cadbOpenDateTime' => $openResponse->rollbackTransaction->cadbOpenDateTime,
                    'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
                    'rollbackNotificationMailSendStatus' => smsState::CLOSED,
                    'rollbackNotificationMailSendDateTime' => date('c'),
                    'rollbackSubmissionId' => $rollbackSubmissionId,
                );

                $this->Rollback_model->add_rollback($rollbackParams);

                // Insert into Rollback State Evolution table

                $seParams = array(
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
                    'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                    'isAutoReached' => false,
                    'rollbackId' => $openResponse->rollbackTransaction->rollbackId,
                );

                $this->Rollbackstateevolution_model->add_rollbackstateevolution($seParams);

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $portingParams = $this->Porting_model->get_porting($originalPortingId);

                    $rollbackParams = array_merge($rollbackParams, $portingParams);

                    $emailService->adminErrorReport('ROLLBACK_SUBMISSION_OPENED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                }else{

                    }

                $this->db->trans_complete();

            }

            else{

                $fault = $openResponse->error;

                $this->fileLogAction('7007', 'BatchOperationService::rollbackSubmissionToOpened', 'Failed OPEN of ' . $rollbackSubmissionId . ' with ' . $fault);

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $currentDateTime = date('c');

                        $start_time = date('y-d-m H:i:s', strtotime($currentDateTime));
                        $end_time = date('y-d-m H:i:s', strtotime($openedDateTime));

                        $start_time = date_create_from_format('y-d-m H:i:s', $start_time);
                        $end_time = date_create_from_format('y-d-m H:i:s', $end_time);

                        $diff = date_diff($start_time, $end_time);

                        // More than 20 minutes difference
                        if($diff->i > 20){

                            $params = array('errorMessage' => 'Retarded rollback submission encountered');

                            $emailService->error('RETARDED ROLLBACK SUBMISSION DETECTED', $params);

                        }

                        break;

                    // Terminal Error Processes
                    case Fault::ROLLBACK_NOT_ALLOWED:
                    case Fault::UNKNOWN_PORTING_ID:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    default:

                        $portingParams = $this->Porting_model->get_porting($originalPortingId);

                        $submissionParams = array(
                            'originalPortingId' => $originalPortingId,
                            'rollbackId' => '',
                            'donorSubmissionDateTime' => date('c'),
                            'rollbackState' => 'NA'
                        );

                        $rollbackParams = array_merge($submissionParams, $portingParams);

                        $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);
                }
            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPR
     * BATCH_007
     * Checks for all rollbacks in OPENED state and sends mail for their Acceptance / Rejection
     */
    public function rollbackOpenedToAcceptedRejected(){

        $this->fileLogAction('7008', 'BatchOperationService::rollbackOpenedToAcceptedRejected', 'rollbackOpenedToAcceptedRejected STARTED');

        // Load rollback in Rollback table in OPENED state in which we are OPR

        $openedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::OPENED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7008', 'BatchOperationService::rollbackOpenedToAcceptedRejected', 'Preparing ACCEPTANCE / REJECTION Email of ' . count($openedRollbacks) . ' opened rollbacks');

        $emailService = new EmailService();

        foreach ($openedRollbacks as $openedRollback){

            // Verify if mail notification sent
            if($openedRollback['rollbackNotificationMailSendStatus'] == smsState::PENDING){
                // Send mail to Back Office with Admin in CC for Acceptance / Rejection

                $this->fileLogAction('7008', 'BatchOperationService::rollbackOpenedToAcceptedRejected', 'Sending ACCEPTANCE / REJECTION Email for ' . $openedRollback['rollbackId']);

                $response = $emailService->backOfficeRollbackAcceptReject($openedRollback);

                if($response){

                    $this->fileLogAction('7008', 'BatchOperationService::rollbackOpenedToAcceptedRejected', 'ACCEPTANCE / REJECTION Email delivery Successful for ' . $openedRollback['rollbackId']);

                    // Update State in DB

                    $rollbackParams = array(
                        'rollbackNotificationMailSendStatus' => smsState::SENT,
                        'rollbackNotificationMailSendDateTime' =>  date('c')
                    );

                    $this->Rollback_model->update_rollback($openedRollback['rollbackId'], $rollbackParams);

                }else{

                    $this->fileLogAction('7008', 'BatchOperationService::rollbackOpenedToAcceptedRejected', 'ACCEPTANCE / REJECTION Email delivery failed for ' . $openedRollback['rollbackId']);

                }
            }
        }

    }

    /**
     * TODO: OK
     * Executed as OPR
     * BATCH_008_{A, B}
     * Checks for all rollbacks in ACCEPTED state, if any performs rollbacks to CONTRACT_DELETED_CONFIRMED state
     * Checks for all rollbacks in CONTRACT_DELETED_CONFIRMED state, if any performs rollbacks to MSISDN_EXPORT_CONFIRMED, state
     * Checks for all rollbacks in MSISDN_EXPORT_CONFIRMED state, if any perform rollbacks to CONFIRMED state updating Porting/Provision table
     */
    public function rollbackOPR(){

        $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'rollbackOPR STARTED');

        // Load rollbacks in Rollback table in ACCEPTED state in which we are OPR

        $acceptedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Preparing CONTRACT_DELETE of ' . count($acceptedRollbacks) . ' accepted rollbacks');

        // Load rollbacks in Rollback table in CONTRACT_DELETED_CONFIRMED state in which we are OPR

        $msisdnContractDeletedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::CONTRACT_DELETED_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Preparing MSISDN_EXPORT of ' . count($msisdnContractDeletedRollbacks) . ' contract deleted rollbacks');

        // Load rollbacks in Rollback table in MSISDN_EXPORT_CONFIRMED state in which we are OPR

        $msisdnExportedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Preparing COMPLETE of ' . count($msisdnExportedRollbacks) . ' msisdn exported rollbacks');

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedRollbacks as $acceptedRollback){

            $rollbackId = $acceptedRollback['rollbackId'];

            $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Verifying Provisioning for ' . $rollbackId);

            // Check if rollback in provision table in state STARTED
            $provisionRollback = $this->Provisioning_model->get_provisioning_by_process_state($rollbackId, processType::ROLLBACK, \ProvisionService\ProvisionNotification\provisionStateType::STARTED);

            if($provisionRollback){

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Proccess provisioned. Performing CONTRACT_DELETE for ' . $rollbackId);

                // Rollback already provisioned. Start rollback moving to CONTRACT_DELETED_CONFIRMED state
                $subscriberMSISDN = $acceptedRollback['startMSISDN'];

                $contractId = $bscsOperationService->getContractId($subscriberMSISDN);

                $deleteResponse = $bscsOperationService->deleteContract($contractId);

                if($deleteResponse->success){

                    $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'CONTRACT_DELETE successful for ' . $rollbackId);

                    $this->db->trans_start();

                    // Insert into Rollback Evolution state table

                    $rollbackEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::CONTRACT_DELETED_CONFIRMED,
                        'isAutoReached' => false,
                        'rollbackId' => $rollbackId,
                    );


                    $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                    // Update Rollback table

                    $rollbackParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::CONTRACT_DELETED_CONFIRMED
                    );

                    $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService->adminErrorReport('ROLLBACK_CONTRACT_DELETED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    // Notify Admin on failed Export
                    $faultCode = $deleteResponse->error;

                    $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'CONTRACT_DELETE failed for ' . $rollbackId . ' with ' . $faultCode);

                    $fault = '';

                    switch ($faultCode) {
                        // Terminal Processes
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

                    $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                    $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);

                }

            }else{

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Provisioning not yet done for ' . $rollbackId);

                //Rollback not yet Provisioned. Do nothing, wait till provision

            }
        }

        foreach ($msisdnContractDeletedRollbacks as $msisdnContractDeletedRollback){

            $rollbackId = $msisdnContractDeletedRollback['rollbackId'];

            $subscriberMSISDN = $msisdnContractDeletedRollback['startMSISDN'];

            $donorNetworkId = $msisdnContractDeletedRollback['donorNetworkId'];

            $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Performing MSISDN_EXPORT for ' . $rollbackId);

            $exportResponse = $bscsOperationService->exportMSISDN($subscriberMSISDN, $donorNetworkId);

            if($exportResponse->success){

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'MSISDN_EXPORT successful for ' . $rollbackId);

                $this->db->trans_start();

                // Insert into Rollback Evolution state table

                $rollbackEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_EXPORT_CONFIRMED,
                    'isAutoReached' => false,
                    'rollbackId' => $rollbackId,
                );


                $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                // Update Rollback table

                $rollbackParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_EXPORT_CONFIRMED
                );

                $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                    $emailService->adminErrorReport('ROLLBACK_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                }else{

                }

                $this->db->trans_complete();

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'MSISDN_EXPORT failed for ' . $rollbackId . ' with ' . $faultCode);

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

                $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);

            }
        }

        foreach ($msisdnExportedRollbacks as $msisdnExportedRollback){

            $rollbackId = $msisdnExportedRollback['rollbackId'];

            $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'Performing KPSA_OPERATION for ' . $rollbackId);

            $fromOperator = $msisdnExportedRollback['donorNetworkId'];

            $subscriberMSISDN = $msisdnExportedRollback['startMSISDN'];

            $toOperator = $msisdnExportedRollback['recipientNetworkId'];

            $fromRoutingNumber = $msisdnExportedRollback['donorRoutingNumber'];

            $toRoutingNumber = $msisdnExportedRollback['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'KPSA_OPERATION successful for ' . $rollbackId);

                $this->db->trans_start();

                // Insert into Rollback Evolution state table

                $rollbackEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::COMPLETED,
                    'isAutoReached' => false,
                    'rollbackId' => $rollbackId,
                );

                $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                // Update Rollback table

                $rollbackParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::COMPLETED
                );

                $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                // Update Provisioning table

                $prParams = array(
                    'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED,
                );

                $this->Provisioning_model->update_provisioning($rollbackId, $prParams);

                // Confirm Routing Data
                $provisionOperationService = new ProvisionOperationService();

                $prResponse = $provisionOperationService->confirmRoutingData($rollbackId);

                if($prResponse->success){

                    // Process terminated

                }
                else{

                    // Who cares, its auto anyway :)

                }

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                    $emailService->adminErrorReport('ROLLBACK_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                }else{

                }

                $this->db->trans_complete();

            }

            else{

                $this->fileLogAction('7009', 'BatchOperationService::rollbackOPR', 'KPSA_OPERATION failed for ' . $rollbackId . ' with ' . $kpsaResponse['message']);

                $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $subscriberMSISDN);

            }

        }

    }

    /**
     * TODO: OK
     * Executed as OPD
     * BATCH_008_{C, D, E}
     * Checks for all rollbacks in ACCEPTED state, if any perform rollback to MSISDN_IMPORT_CONFIRMED state
     * Checks for all rollbacks MSISDN_IMPORT_CONFIRMED state, if any, move to MSISDN_CHANGE_IMPORT_CONFIRMED state
     * Checks for all ports in MSISDN_CHANGE_IMPORT_CONFIRMED state, if any, perform porting to CONFIRMED state, sending confirm request and updating Rollback table
     */
    public function rollbackOPD(){

        $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'rollbackOPD STARTED');

        // Load rollbacks in Rollback table in ACCEPTED state in which we are OPD

        $acceptedRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Preparing MSISDN_IMPORT of ' . count($acceptedRollbacks) . ' accepted rollbacks');

        // Load rollbacks in Rollback table in MSISDN_IMPORT_CONFIRMED state in which we are OPD

        $msisdnConfirmedRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Preparing MSISDN_CHANGE_IMPORT of ' . count($msisdnConfirmedRollbacks) . ' msisdn imported rollbacks');

        // Load rollbacks in Rollback table in MSISDN_CHANGE_IMPORT_CONFIRMED state in which we are OPD

        $msisdnChangeRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::MSISDN_CHANGE_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Preparing CONFIRM of ' . count($msisdnConfirmedRollbacks) . ' msisdn change imported rollbacks');

        $bscsOperationService = new BscsOperationService();

        $rollbackOperationService = new RollbackOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedRollbacks as $acceptedRollback) {

            $rollbackDateTime = $acceptedRollback['preferredRollbackDateTime'];

            $rollbackId = $acceptedRollback['rollbackId'];

            $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Verifying Rollback DateTime for ' . $rollbackId);

            $currentDateTime = date('c');

            $start_time = date('y-d-m H:i:s', strtotime($rollbackDateTime));
            $end_time = date('y-d-m H:i:s', strtotime($currentDateTime));

            $start_time = date_create_from_format('y-d-m H:i:s', $start_time);
            $end_time = date_create_from_format('y-d-m H:i:s', $end_time);

            $diff = date_diff($start_time, $end_time);

            // End time >= start time, less than 15minutes difference
            if(($diff->invert == 0 && $diff->i < 15) || ($diff->invert == 1 && $diff->i < 15)){

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Rollback datetime OK. Performing MSISDN_IMPORT for ' . $rollbackId);

                // Start rollback moving to MSISDN_IMPORT_CONFIRMED state. Import rollback MSISDN into BSCS
                $subscriberMSISDN = $acceptedRollback['startMSISDN'];

                $recipientNetworkId = $acceptedRollback['recipientNetworkId'];

                $importResponse = $bscsOperationService->importMSISDN($subscriberMSISDN, $recipientNetworkId);

                if($importResponse->success){

                    $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'MSISDN_IMPORT successful for ' . $rollbackId);

                    $this->db->trans_start();

                    // Insert into rollback Evolution state table

                    $rollbackEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED,
                        'isAutoReached' => false,
                        'rollbackId' => $rollbackId,
                    );

                    $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                    // Update Rollback table

                    $rollbackParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED
                    );

                    $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService->adminErrorReport('ROLLBACK_MSISDN_IMPORTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    // Notify Admin on failed Import
                    $faultCode = $importResponse->error;

                    $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'MSISDN_IMPORT failed for ' . $rollbackId . ' with ' . $faultCode);

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

                    $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                    $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);

                }

            }
            else if($diff->invert == 1 && $diff->h > 0){

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Rollback more than 1 hour late for ' . $rollbackId);

                // More than 1hrs late, alert Admin

                $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                $emailService->adminErrorReport('MORE_THAN_ONE_HOUR_FROM_EXPECTED_PORTING_DATE_TIME', $rollbackParams, processType::ROLLBACK);

            }

        }

        foreach ($msisdnConfirmedRollbacks as $msisdnConfirmedRollback){

            $rollbackId = $msisdnConfirmedRollback['rollbackId'];

            $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Performing MSISDN_CHANGE_IMPORT for ' . $rollbackId);

            $subscriberInfo = $this->Rollbacksubmission_model->get_submissionByRollbackId($rollbackId);

            $subscriberMSISDN = $msisdnConfirmedRollback['startMSISDN'];

            $contractId = $msisdnConfirmedRollback['contractId'];

            $changeResponse = $bscsOperationService->changeImportMSISDN($subscriberInfo['temporalMSISDN'], $subscriberMSISDN, $contractId);

            if($changeResponse->success){

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'MSISDN_CHANGE_IMPORT successful for ' . $rollbackId);

                $this->db->trans_start();

                // Insert into rollback Evolution state table

                $rollbackEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_CHANGE_IMPORT_CONFIRMED,
                    'isAutoReached' => false,
                    'rollbackId' => $rollbackId,
                );

                $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                // Update Rollback table

                $rollbackParams = array(
                    'lastChangeDateTime' => date('c'),
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::MSISDN_CHANGE_IMPORT_CONFIRMED
                );

                $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                    $emailService->adminErrorReport('ROLLBACK_MSISDN_CHANGE_IMPORTED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                }else{

                }

                $this->db->trans_complete();

            }else{

                // Notify Admin on failed Import
                $faultCode = $changeResponse->error;

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'MSISDN_CHANGE_IMPORT failed for ' . $rollbackId . ' with ' . $faultCode);

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

                $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                $emailService->adminErrorReport($fault, $rollbackParams, processType::ROLLBACK);

            }


        }

        foreach ($msisdnChangeRollbacks as $msisdnChangeRollback){

            // Move Rollback to CONFIRMED state
            $fromOperator = $msisdnChangeRollback['donorNetworkId'];

            $subscriberMSISDN = $msisdnChangeRollback['startMSISDN'];

            $toOperator = $msisdnChangeRollback['recipientNetworkId'];

            $fromRoutingNumber = $msisdnChangeRollback['donorRoutingNumber'];

            $toRoutingNumber = $msisdnChangeRollback['recipientRoutingNumber'];

            $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'Performing KPSA_OPERATION for ' . $msisdnChangeRollback['rollbackId']);

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'KPSA_OPERATION successful for ' . $msisdnChangeRollback['rollbackId']);

                // Send confirm request

                $rollbackId = $msisdnChangeRollback['rollbackId'];

                $rollbackDateAndTime = date('c', strtotime('+5 minutes', strtotime(date('c'))));

                // Make Confirm Rollback Operation

                $confirmResponse = $rollbackOperationService->confirm($rollbackId, $rollbackDateAndTime);

                // Verify response

                if($confirmResponse->success){

                    $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'CONFIRM successful for ' . $msisdnChangeRollback['rollbackId']);

                    $this->db->trans_start();

                    // Insert into rollback Evolution state table

                    $rollbackEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::CONFIRMED,
                        'isAutoReached' => false,
                        'rollbackId' => $rollbackId,
                    );

                    $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                    // Update rollback table

                    $rollbackParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::CONFIRMED
                    );

                    $this->Rollback_model->update_rollback($rollbackId, $rollbackParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);

                        $emailService->adminErrorReport('ROLLBACK_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                    }else{

                    }

                    $this->db->trans_complete();

                }
                else{

                    $fault = $confirmResponse->error;

                    $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'CONFIRM failed for ' . $msisdnChangeRollback['rollbackId'] . ' with ' . $fault);

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                        case Fault::ROLLBACK_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_ROLLBACK_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                        default:

                        $rollbackParams = $this->Rollback_model->get_full_rollback($rollbackId);
                        $emailService->adminConfirmReport($fault, $rollbackParams, processType::ROLLBACK);
                    }

                }

            }

            else{

                $this->fileLogAction('7010', 'BatchOperationService::rollbackOPD', 'KPSA_OPERATION failed for ' . $msisdnChangeRollback['rollbackId'] . ' with ' . $kpsaResponse['message']);

                $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $subscriberMSISDN);

            }

        }

    }

    /**
     * TODO: OK
     * Executed as CO
     * BATCH_009
     * Checks for all NRs in Submission table in STARTED state and attempts making open for them
     */
    public function nrSubmissionToOpened(){

        $this->fileLogAction('7011', 'BatchOperationService::nrSubmissionToOpened', 'nrSubmissionToOpened STARTED');

        // Load number returns in Submission table in STARTED state

        $startedReturns = $this->Numberreturnsubmission_model->get_submissionByState(returnSubmissionStateType::STARTED);

        $this->fileLogAction('7011', 'BatchOperationService::nrSubmissionToOpened', 'Preparing OPEN of ' . count($startedReturns) . ' submitted returns');

        $nrOperationService = new ReturnOperationService();

        $emailService = new EmailService();

        foreach ($startedReturns as $startedReturn){

            $submissionId = $startedReturn['numberReturnSubmissionId'];
            $returnMSISDN = $startedReturn['returnMSISDN'];
            $primaryOwnerNetworkId = $startedReturn['primaryOwnerNetworkId'];
            $returnOperator = null;

            $this->fileLogAction('7011', 'BatchOperationService::nrSubmissionToOpened', 'Performing OPEN for ' . $submissionId);

            if($primaryOwnerNetworkId == Operator::MTN_NETWORK_ID){
                $returnOperator = 0;
            }elseif($primaryOwnerNetworkId == Operator::NEXTTEL_NETWORK_ID){
                $returnOperator = 1;
            }

            // Make Open NR Operation

            $openResponse = $nrOperationService->open($returnOperator, $returnMSISDN);

            // Verify response

            if($openResponse->success){

                $this->fileLogAction('7011', 'BatchOperationService::nrSubmissionToOpened', 'OPEN successful for ' . $submissionId);

                $this->db->trans_start();

                // Update into NR submission table with state OPENED

                $nrsParams = array(
                    'submissionState' => \ReturnService\_Return\returnSubmissionStateType::OPENED,
                );

                $this->Numberreturnsubmission_model->update_numberreturnsubmission($submissionId, $nrsParams);

                // Insert into NR table

                $nrParams = array(
                    'returnId' => $openResponse->returnTransaction->returnId,
                    'openDateTime' => $openResponse->returnTransaction->openDateTime,
                    'ownerNetworkId' => $openResponse->returnTransaction->ownerNrn->networkId,
                    'ownerRoutingNumber' => $openResponse->returnTransaction->ownerNrn->routingNumber,
                    'primaryOwnerNetworkId' => $openResponse->returnTransaction->primaryOwnerNrn->networkId,
                    'primaryOwnerRoutingNumber' => $openResponse->returnTransaction->primaryOwnerNrn->routingNumber,
                    'returnMSISDN' => $returnMSISDN,
                    'returnNumberState' => \ReturnService\_Return\returnSubmissionStateType::OPENED,
                    'returnNotificationMailSendStatus' => smsState::CLOSED,
                    'returnNotificationMailSendDateTime' => date('c'),
                    'numberReturnSubmissionId' => $submissionId,
                );

                $this->Numberreturn_model->add_numberreturn($nrParams);

                // Insert into NR state Evolution table

                $nrsParams = array(
                    'returnNumberState' => \ReturnService\_Return\returnStateType::OPENED,
                    'lastChangeDateTime' => date('c'),
                    'returnId' => $openResponse->returnTransaction->returnId,
                );

                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($nrsParams);

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService->adminErrorReport('NR_SUBMISSION_OPENED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                }else {

                    $start_time = date('y-d-m H:i:s', strtotime($startedReturn['submissionDateTime']));
                    $end_time = date('y-d-m H:i:s', strtotime(date('c')));

                    $start_time = date_create_from_format('y-d-m H:i:s', $start_time);
                    $end_time = date_create_from_format('y-d-m H:i:s', $end_time);

                    $diff = date_diff($start_time, $end_time);

                    // End time >= start time, less than 30 minutes difference
                    if($diff->h > 0){

                        $params = array('errorMessage' => 'Retarded number return submission encountered for ' . $returnMSISDN);

                        $emailService->error('RETARDED NUMBER RETURN SUBMISSION DETECTED', $params);

                    }
                }

                $this->db->trans_complete();

            }

            else{

                $fault = $openResponse->error;

                $this->fileLogAction('7011', 'BatchOperationService::nrSubmissionToOpened', 'OPEN failed for ' . $submissionId . ' with ' . $fault);

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        // Terminal Error Processes
                    case Fault::NUMBER_RESERVED_BY_PROCESS:
                    case Fault::NUMBER_NOT_OWNED_BY_OPERATOR:
                    case Fault::UNKNOWN_MANAGED_NUMBER:
                    case Fault::NUMBER_NOT_PORTED:
                    case Fault::MULTIPLE_PRIMARY_OWNER:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    case Fault::NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::NUMBER_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::NUMBER_RANGES_OVERLAP:
                    default:

                        $nrParams = array(
                            'ownerNetworkId' => Operator::ORANGE_NETWORK_ID,
                            'returnMSISDN' => $returnMSISDN,
                            'returnId' => '',
                            'returnNumberState' => 'N/A'
                        );

                        $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);
                }

            }

        }

    }

    /**
     * TODO: OK
     * Executed as PO
     * BATCH_010
     * Checks for all NRs in OPENED state and sends mail for their Acceptance / Rejection
     */
    public function nrOpenedToAcceptedRejected(){

        $this->fileLogAction('7012', 'BatchOperationService::nrOpenedToAcceptedRejected', 'nrOpenedToAcceptedRejected STARTED');

        // Load NRs in NR table in OPENED state in which we are PO
        $openedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::OPENED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7012', 'BatchOperationService::nrOpenedToAcceptedRejected', 'Preparing ACCEPTANCE / REJECTION Mail of ' . count($openedReturns) . ' opened returns');

        $emailService = new EmailService();

        foreach ($openedReturns as $openedReturn){

            // Verify if mail notification sent
            if($openedReturn['returnNotificationMailSendStatus'] == smsState::PENDING){
                // Send mail to Back Office with Admin in CC for Acceptance / Rejection

                $this->fileLogAction('7012', 'BatchOperationService::nrOpenedToAcceptedRejected', 'Performing ACCEPTANCE / REJECTION Mail delivery for ' . $openedReturn['returnId']);

                $response = $emailService->backOfficeReturnAcceptReject($openedReturn);

                if($response){

                    $this->fileLogAction('7012', 'BatchOperationService::nrOpenedToAcceptedRejected', 'ACCEPTANCE / REJECTION Mail successful for ' . $openedReturn['returnId']);

                    // Update State in DB

                    $returnParams = array(
                        'returnNotificationMailSendStatus' => smsState::SENT,
                        'returnNotificationMailSendDateTime' =>  date('c')
                    );

                    $this->Numberreturn_model->update_numberreturn($openedReturn['returnId'], $returnParams);

                }else{

                    $this->fileLogAction('7012', 'BatchOperationService::nrOpenedToAcceptedRejected', 'ACCEPTANCE / REJECTION Mail failed for ' . $openedReturn['returnId']);

                }
            }

        }

    }

    /**
     * TODO: OK
     * Executed as CO
     * BATCH_011_{A, B}
     * Checks for all NRs in ACCEPTED state, if any, perform NR to MSISDN_EXPORT_CONFIRMED state
     * Checks for all NRs in MSISDN_EXPORT_CONFIRMED state, if any, perform NR to COMPLETED state updating NR / Provision
     */
    public function numberReturnCO(){

        $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'numberReturnCO STARTED');

        // Load NRs in Return table in ACCEPTED state in which we are CO

        $acceptedReturns = $this->Numberreturn_model->get_nr_by_state_and_co(\ReturnService\_Return\returnStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Preparing MSISDN_EXPORT of' . count($acceptedReturns) . ' accepted returns');

        // Load NRs in Return table in MSISDN_EXPORT_CONFIRMED state in which we are CO

        $msisdnConfirmedReturns = $this->Numberreturn_model->get_nr_by_state_and_co(\ReturnService\_Return\returnStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Preparing CONFIRM of' . count($msisdnConfirmedReturns) . ' msisdn exported returns');

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedReturns as $acceptedReturn){

            $returnId = $acceptedReturn['returnId'];

            $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Performing MSISDN_EXPORT for ' . $returnId);

            $returnMSISDN = $acceptedReturn['returnMSISDN'];

            $primaryOwnerNetworkId = $acceptedReturn['primaryOwnerNetworkId'];

            $exportResponse = $bscsOperationService->exportMSISDN($returnMSISDN, $primaryOwnerNetworkId);

            if($exportResponse->success){

                $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'MSISDN_EXPORT successful for ' . $returnId);

                $this->db->trans_start();

                // Insert into Return Evolution state table

                $returnEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_EXPORT_CONFIRMED,
                    'returnId' => $returnId,
                );


                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($returnEvolutionParams);

                // Update Return table

                $returnParams = array(
                    'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_EXPORT_CONFIRMED
                );

                $this->Numberreturn_model->update_numberreturn($returnId, $returnParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                    $emailService->adminErrorReport('RETURN_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                }else{

                }

                $this->db->trans_complete();

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

                $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'MSISDN_EXPORT failed for ' . $returnId . ' with ' . $faultCode);

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

                $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);

            }
        }

        foreach ($msisdnConfirmedReturns as $msisdnConfirmedReturn){

            $returnId = $msisdnConfirmedReturn['returnId'];

            $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Verifying provisioning for ' . $returnId);

            // Check if return in provision table in state STARTED
            $provisionReturn = $this->Provisioning_model->get_provisioning_by_process_state($returnId, processType::_RETURN, \ProvisionService\ProvisionNotification\provisionStateType::STARTED);

            if($provisionReturn && $provisionReturn['endNetworkId'] != Operator::ORANGE_NETWORK_ID) {

                $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Provision OK. Performing KPSA_OPERATION for ' . $returnId);

                $toOperator = $msisdnConfirmedReturn['primaryOwnerNetworkId'];

                $returnMSISDN = $msisdnConfirmedReturn['returnMSISDN'];

                $fromOperator = $msisdnConfirmedReturn['ownerNetworkId'];

                $toRoutingNumber = $msisdnConfirmedReturn['primaryOwnerRoutingNumber'];

                $fromRoutingNumber = $msisdnConfirmedReturn['ownerRoutingNumber'];

                // Perform KPSA Operation
                $kpsaResponse = $kpsaOperationService->performKPSAOperation($returnMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

                if($kpsaResponse['success']){

                    $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'KPSA_OPERATION successful for ' . $returnId);

                    $this->db->trans_start();

                    // Insert into Return Evolution state table

                    $returnEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'returnNumberState' => \ReturnService\_Return\returnStateType::COMPLETED,
                        'returnId' => $returnId,
                    );


                    $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($returnEvolutionParams);

                    // Update Return table

                    $returnParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::COMPLETED
                    );

                    $this->Numberreturn_model->update_numberreturn($returnId, $returnParams);

                    // Update Provisioning table

                    $prParams = array(
                        'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED
                    );

                    $this->Provisioning_model->update_provisioning($returnId, $prParams);

                    // Confirm Routing Data
                    $provisionOperationService = new ProvisionOperationService();

                    $prResponse = $provisionOperationService->confirmRoutingData($returnId);

                    if($prResponse->success){

                        $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'CONFIRM successful for ' . $returnId);

                        // Process terminated

                    }
                    else{

                        $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'CONFIRM failed for ' . $returnId);

                        // Who cares, its auto anyway :)

                    }

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService->adminErrorReport('RETURN_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }else{

                    }

                    $this->db->trans_complete();

                }

                else{

                    $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'KPSA_OPERATION failed for ' . $returnId . ' with ' . $kpsaResponse['message']);

                    $emailService->adminKPSAError($kpsaResponse['message'] . ' :: ' . $returnMSISDN);

                }

            }else{

                $this->fileLogAction('7013', 'BatchOperationService::numberReturnCO', 'Provision not yet performed for ' . $returnId);

            }


        }

    }

    /**
     * TODO: OK
     * Executed as PO
     * BATCH_011_{C, D}
     * Checks for all NRs in ACCEPTED state, if any, perform NR to MSISDN_RETURN_CONFIRMED state
     * Checks for all NRs in MSISDN_RETURN_CONFIRMED state, if any, perform NR to COMPLETED state updating NR / Provision table
     */
    public function numberReturnPO(){

        $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'numberReturnPO STARTED');

        // Load NRs in Return table in ACCEPTED state in which we are PO

        $acceptedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Preparing MSISDN_RETURN of' . count($acceptedReturns) . ' accepted returns');

        // Load NRs in Return table in MSISDN_RETURN_CONFIRMED state in which we are PO

        $msisdnConfirmedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Preparing CONFIRM of' . count($msisdnConfirmedReturns) . ' msisdn returned returns');

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedReturns as $acceptedReturn){

            $returnId = $acceptedReturn['returnId'];

            $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Performing MSISDN_RETURN for ' . $returnId);

            $returnMSISDN = $acceptedReturn['returnMSISDN'];

            $currentOwnerNetworkId = $acceptedReturn['ownerNetworkId'];

            $returnResponse = $bscsOperationService->returnMSISDN($returnMSISDN, $currentOwnerNetworkId);

            if($returnResponse->success){

                $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'MSISDN_RETURN successful for ' . $returnId);

                $this->db->trans_start();

                // Insert into Return Evolution state table

                $returnEvolutionParams = array(
                    'lastChangeDateTime' => date('c'),
                    'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED,
                    'returnId' => $returnId,
                );


                $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($returnEvolutionParams);

                // Update Return table

                $returnParams = array(
                    'returnNumberState' => \ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED
                );

                $this->Numberreturn_model->update_numberreturn($returnId, $returnParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                    $emailService->adminErrorReport('RETURN_MSISDN_RETURNED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                }else{

                }

                $this->db->trans_complete();

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $returnResponse->error;

                $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'MSISDN_RETURN failed for ' . $returnId . ' with ' . $faultCode);

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

                $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                $emailService->adminErrorReport($fault, $nrParams, processType::_RETURN);

            }
        }

        foreach ($msisdnConfirmedReturns as $msisdnConfirmedReturn){

            $returnId = $msisdnConfirmedReturn['returnId'];

            $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Verifying provisioning for ' . $returnId);

            // Check if return in provision table in state STARTED
            $provisionReturn = $this->Provisioning_model->get_provisioning_by_process_state($returnId, processType::_RETURN, \ProvisionService\ProvisionNotification\provisionStateType::STARTED);

            if($provisionReturn && $provisionReturn['endNetworkId'] == Operator::ORANGE_NETWORK_ID) {

                $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Provision OK. Performing KPSA_OPERATION for ' . $returnId);

                $toOperator = $msisdnConfirmedReturn['primaryOwnerNetworkId'];

                $returnMSISDN = $msisdnConfirmedReturn['returnMSISDN'];

                $toRoutingNumber = $msisdnConfirmedReturn['primaryOwnerRoutingNumber'];

                // Perform KPSA Operation
                $kpsaResponse = $kpsaOperationService->performKPSAReturnOperation($returnMSISDN, $toOperator, $toRoutingNumber);

                if($kpsaResponse['success']){

                    $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'KPSA_OPERATION successful for ' . $returnId);

                    $this->db->trans_start();

                    // Insert into Return Evolution state table

                    $returnEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'returnNumberState' => \ReturnService\_Return\returnStateType::COMPLETED,
                        'returnId' => $returnId,
                    );

                    $this->Numberreturnstateevolution_model->add_numberreturnstateevolution($returnEvolutionParams);

                    // Update Return table

                    $returnParams = array(
                        'returnNumberState' => \ReturnService\_Return\returnStateType::COMPLETED
                    );

                    $this->Numberreturn_model->update_numberreturn($returnId, $returnParams);

                    // Update Provisioning table

                    $prParams = array(
                        'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED
                    );

                    $this->Provisioning_model->update_provisioning($returnId, $prParams);

                    // Confirm Routing Data
                    $provisionOperationService = new ProvisionOperationService();

                    $prResponse = $provisionOperationService->confirmRoutingData($returnId);

                    if($prResponse->success){

                        // Process terminated

                    }
                    else{

                        // Who cares, its auto anyway :)

                    }

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                        $nrParams = $this->Numberreturn_model->get_numberreturn($returnId);

                        $emailService->adminErrorReport('RETURN_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $nrParams, processType::_RETURN);

                    }

                    $this->db->trans_complete();

                }

                else{

                    $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'KPSA_OPERATION failed for ' . $returnId . ' with ' . $kpsaResponse['message']);

                    $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $returnMSISDN);

                }

            }else{

                $this->fileLogAction('7014', 'BatchOperationService::numberReturnPO', 'Provision not yet performed for ' . $returnId);

            }

        }

    }

    /**
     * TODO: OK
     * Executed as Other Operator
     * BATCH_012
     * Checks for all Provisions in STARTED state in which we are not end operator nor donor or recipient but
     *  other(by checking existence of processId in corresponding table), perform corresponding action to COMPLETED state
     */
    public function provisionOther(){

        $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'provisionOther STARTED');

        // Load Provisions in Provision table in STARTED state in which we are other

        $startedPortingProvisions = $this->Provisioning_model->get_provisioning_other_porting();

        $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'Preparing OTHER_ACTION of ' . count($startedPortingProvisions) . ' started portings');

        $startedRollbackProvisions = $this->Provisioning_model->get_provisioning_other_rollback();

        $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'Preparing OTHER_ACTION of ' . count($startedRollbackProvisions) . ' started rollbacks');

        $startedReturnProvisions = $this->Provisioning_model->get_provisioning_other_return();

        $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'Preparing OTHER_ACTION of ' . count($startedReturnProvisions) . ' started returns');

        // Merge arrays
        $startedProvisions = array_merge($startedPortingProvisions, $startedRollbackProvisions);
        $startedProvisions = array_merge($startedProvisions, $startedReturnProvisions);

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($startedProvisions as $startedProvision){

            // Perform KPSA Operation

            $processId = $startedProvision['processId'];

            $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'Performing KPSA_OPERATION for ' . $processId);

            $processType = $startedProvision['processType'];

            $toOperator = $startedProvision['endNetworkId'];

            $subscriberMSISDN = $startedProvision['subscriberMSISDN'];

            $toRoutingNumber = $startedProvision['endRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOtherOperation($subscriberMSISDN, $toOperator, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'KPSA_OPERATION successful for ' . $processId);

                $this->db->trans_start();

                // Update Provisioning table

                $prParams = array(
                    'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED
                );

                $this->Provisioning_model->update_provisioning($processId, $prParams);

                // Notify Agents/Admin

                if ($this->db->trans_status() === FALSE) {

                    $error = $this->db->error();
                    $this->fileLogAction($error['code'], 'BatchOperationService', $error['message']);

                    $emailService = new EmailService();

                    $eParams = array(
                        'errorReportId' => $processId,
                        'cadbNumber' => '',
                        'problem' => 'NB: This is a provisioning problem',
                        'reporterNetworkId' => '',
                        'submissionDateTime' => date('c'),
                        'processType' => $processType
                    );

                    $emailService->adminErrorReport('PROVISION_COMPLETED_BUT_DB_FILLED_INCOMPLETE', $eParams, processType::ERROR);

                }

                $this->db->trans_complete();

            }

            else{

                $this->fileLogAction('7015', 'BatchOperationService::provisionOther', 'KPSA_OPERATION failed for ' . $processId . ' with ' . $kpsaResponse['message']);

                $emailService->adminKPSAError($kpsaResponse['message']. ' :: ' . $subscriberMSISDN);

            }

        }

    }

    /**
     * Notify for error Report
     */
    public function errorReportNotification(){

        $this->fileLogAction('7016', 'BatchOperationService::provisionOther', 'provisionOther STARTED');

        // Load errors in Error table in mail sent pending state

        $errorReports = $this->Error_model->get_errorbyStatus(smsState::PENDING);

        $this->fileLogAction('7016', 'BatchOperationService::provisionOther', 'Preparing error report of ' . count($errorReports) . ' error reports');

        $emailService = new EmailService();

        foreach ($errorReports as $errorReport){

            $this->fileLogAction('7016', 'BatchOperationService::provisionOther', 'Performing Error Report Mail delivery for ' . $errorReport['errorId']);

            // Send mail to Back Office with Admin in CC for Acceptance / Rejection

            $response = $emailService->backOfficePortingAcceptReject($errorReport);

            if($response){

                $this->fileLogAction('7016', 'BatchOperationService::provisionOther', 'Error Report Mail delivery successful for ' . $errorReport['errorId']);

                // Update State in DB

                $errorParams = array(
                    'errorNotificationMailSendStatus' => smsState::SENT,
                    'errorNotificationMailSendDateTime' =>  date('c')
                );

                $this->Error_model->update_error($errorReport['errorId'], $errorParams);

            }else{

                $this->fileLogAction('7016', 'BatchOperationService::provisionOther', 'Error Report Mail delivery failed for ' . $errorReport['errorId']);

            }
        }
    }

    /**
     * Executed by all
     * BATCH_014
     * Performs SFTP synchronization of yesterday data
     */
    public function CADBFileSynchronizer(){

        $prevDay = date('Y-m-d', strtotime('-1 days', strtotime(date('c'))));

        $this->synchronizeDateWithCADB($prevDay);

    }

    /**
     * Perform SFTP synchronization of given date data
     * @param $day
     */
    private function synchronizeDateWithCADB($day){

        $syncResponse = $this->syncDateData($day);

        $emailService = new EmailService();

        if($syncResponse['success'] == true){

            // Process downloaded file

            $file_name = $syncResponse['fileName'];

            if($file_name != ''){
                $row = 1;

                if (($handle = fopen(FCPATH . 'uploads/cadb/' .$file_name, "r")) !== FALSE) {

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){
                            $row++;
                        }else{

                            $cadbId = $data[0]; // CADB_ID
                            $isdn = $data[1]; // ISDN
                            $routingNumber = $data[2]; // $routingNumber
                            $applyTime = $data[3]; // ApplyTime

                            $porting = $this->Porting_model->get_porting($cadbId);

                            if($porting && $porting['portingState'] != \PortingService\Porting\portingStateType::COMPLETED) {

                                $eParams = array(
                                    'errorReportId' => 'N/A',
                                    'cadbNumber' => '',
                                    'problem' => 'NB: This is a CADB Synchronization problem',
                                    'reporterNetworkId' => '',
                                    'submissionDateTime' => date('c'),
                                    'processType' => 'CADB Synchronization'
                                );

                                $emailService->adminErrorReport("CADB SYNCHRONIZATIONN OFF FOR " . $porting['portingState'], $eParams, processType::ERROR);

                            }else{

                                $eParams = array(
                                    'errorReportId' => 'N/A',
                                    'cadbNumber' => '',
                                    'problem' => 'NB: This is a CADB Synchronization problem',
                                    'reporterNetworkId' => '',
                                    'submissionDateTime' => date('c'),
                                    'processType' => 'CADB Synchronization'
                                );

                                $emailService->adminErrorReport("NO ENTRY IN LDB FOR " . $cadbId, $eParams, processType::ERROR);


                            }
                        }
                    }

                    fclose($handle);

                }else{

                    $eParams = array(
                        'errorReportId' => 'N/A',
                        'cadbNumber' => '',
                        'problem' => 'NB: This is a CADB Synchronization problem',
                        'reporterNetworkId' => '',
                        'submissionDateTime' => date('c'),
                        'processType' => 'CADB Synchronization'
                    );

                    $emailService->adminErrorReport("SYNCHRONIZATION FILE NOT FOUND", $eParams, processType::ERROR);

                }

            }else{
                $response['success'] = false;
                $response['message'] = 'Failed opening file';
                $eParams = array(
                    'errorReportId' => 'N/A',
                    'cadbNumber' => '',
                    'problem' => 'NB: This is a CADB Synchronization problem',
                    'reporterNetworkId' => '',
                    'submissionDateTime' => date('c'),
                    'processType' => 'CADB Synchronization'
                );

                $emailService->adminErrorReport("FAILED OPENING SYNCHRONIZATION FILE", $eParams, processType::ERROR);
            }

        }else{
            // Mail admin for failure
            $eParams = array(
                'errorReportId' => 'N/A',
                'cadbNumber' => '',
                'problem' => 'NB: This is a CADB Synchronization problem',
                'reporterNetworkId' => '',
                'submissionDateTime' => date('c'),
                'processType' => 'CADB Synchronization'
            );

            $emailService->adminErrorReport("SYNCHRONIZATION PROCESSED FAILED FOR $day", $eParams, processType::ERROR);


        }

    }

    /**
     * Download date data from CADB
     * @param $date
     * @throws Exception
     */
    private function syncDateData($date){

        $response = [];
        $response['success'] = true;

        $sftp = new SFTP(sftpParams::HOST);

        // Authenticate
        if (!$sftp->login(sftpParams::USERNAME, sftpParams::PASSWORD)) {

            $response['success'] = true;
            $response['message'] = 'Login failed';

        }else{

            $file = 'ported_numbers-report-' . $date . '.csv';
            $sftp->get($file, FCPATH . 'uploads/cadb/' . $file);
            $response['fileName'] = $file;

        }

        return $response;

    }

    /**
     * Executed by all
     * BATCH_005
     * Uses helper functions to update LDB
     */
    public function systemAPIUpdater(){

        $emailService = new EmailService();

        /*
         * Porting Updater
         */
        $portingOperationService = new PortingOperationService();

        // Portings
        $cadbPortings = $portingOperationService->getCADBPortings();

        $cadbPortings = $cadbPortings['data'];

        foreach ($cadbPortings as $cadbPorting){

            // Verify if porting in porting table
            $dbPorting = $this->Porting_model->get_porting($cadbPorting['portingId']);

            if($dbPorting && $dbPorting['portingState'] == $cadbPorting['portingState']){
                // Porting found in the DB and in correct state. Perfect :)
            }else{

                $portingParams = $this->getPortingParams($dbPorting);

                $this->fileLogAction('9006', 'BatchOperationService::systemAPIUpdater', 'Porting[' . $cadbPorting['portingId'] . '] is '  . $cadbPorting['portingState'] . ' in CADB but ' . $dbPorting['portingState'] . ' in LDB: ' . json_encode($cadbPorting));

                $emailService->adminErrorReport($cadbPorting['portingId'] . ' PORTING IN CADB BUT ' . $cadbPorting['portingState'] . ' IN LDB', $portingParams, processType::PORTING);

            }

        }

        /*
         * Rollback Updater
         */
        $rollbackOperationService = new RollbackOperationService();

        // Rollbacks
        $cadbRollbacks = $rollbackOperationService->getCADBRollbacks();

        $cadbRollbacks = $cadbRollbacks['data'];

        foreach ($cadbRollbacks as $cadbRollback){

            // Verify if rollback in rollback table
            $dbRollback = $this->Rollback_model->get_rollback($cadbRollback['rollbackId']);

            if($dbRollback && $dbRollback['rollbackState'] == $cadbRollback['rollbackState']){
                // Rollback found in the DB and in correct state. Perfect :)
            }else{

                $rollbackParams = $this->getRollbackParams($dbRollback);

                $this->fileLogAction('9006', 'BatchOperationService::systemAPIUpdater', 'Rollback[' . $cadbRollback['rollbackId'] . '] is '  . $cadbRollback['rollbackState'] . ' in CADB but ' . $dbRollback['rollbackState'] . ' in LDB: ' . json_encode($cadbRollback));

                $emailService->adminErrorReport($cadbRollback['rollbackId'] . ' ROLLBACK IN CADB BUT ' . $cadbRollback['rollbackState'] . ' IN LDB', $rollbackParams, processType::ROLLBACK);

            }

        }

        /*
         * Return Updater
         */
        $returnOperationService = new ReturnOperationService();

        // Current Returning Transactions
        $cadbReturns =$returnOperationService->getCADBNumberReturns();

        $cadbReturns = $cadbReturns['data'];

        foreach ($cadbReturns as $cadbReturn){

            // Verify if return in return table
            $dbReturn = $this->Numberreturn_model->get_numberreturn($cadbReturn['returnId']);

            if($dbReturn && $dbReturn['returnNumberState'] == $cadbReturn['returnNumberState']){
                // Rollback found in the DB and in correct state. Perfect :)
            }else{

                $returnParams = $this->getReturnParams($dbReturn);

                $this->fileLogAction('9006', 'BatchOperationService::systemAPIUpdater', 'Rollback[' . $cadbReturn['returnId'] . '] is '  . $cadbReturn['rollbackState'] . ' in CADB but ' . $dbReturn['returnNumberState'] . ' in LDB: ' . json_encode($cadbReturn));

                $emailService->adminErrorReport($cadbReturn['returnId'] . ' RETURN IN CADB BUT ' . $cadbReturn['returnNumberState'] . ' IN LDB', $returnParams, processType::ROLLBACK);

            }

        }

    }

    /**
     * Executed by all
     * BATCH_013
     * Checks for all processes at SMS levels and verify if SMS has been sent. If not, send
     */
    public function smsUpdater(){

        $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'smsUpdater STARTED');

        // Get all Porting SMS messages in state pending
        $portingMessages = $this->Portingsmsnotification_model->get_portingsmsnotificationByStatus(smsState::PENDING);

        $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Preparing SMS update for ' . count($portingMessages) . ' porting messages');

        foreach ($portingMessages as $portingMessage){

            $portingNotificationId = $portingMessage['portingSmsNotificationId'];
            $message = $portingMessage['message'];
            $msisdn = $portingMessage['msisdn'];

            $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Sending porting SMS for ' . $portingNotificationId);

            $this->db->trans_start();

            // Send SMS and save in DB
            $response = SMS::MESSAGE_SMS($message, $msisdn);

            $smsNotificationparams = [];

            if($response['success']){

                $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Porting SMS successful for ' . $portingNotificationId);

                // Update SMS in PortingSMSNotification table in state SENT

                $smsNotificationparams = array(
                    'status' => smsState::SENT,
                    'attemptCount' => $portingMessage['attemptCount'] + 1,
                    'sendDateTime' => date('c')
                );

            }else{

                $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Porting SMS failed for ' . $portingNotificationId);

                $smsNotificationparams = array(
                    'attemptCount' => $portingMessage['attemptCount'] + 1
                );

            }

            $this->Portingsmsnotification_model->update_portingsmsnotification($portingNotificationId, $smsNotificationparams);

            $this->db->trans_complete();
        }

        // Get all Rollback SMS messages in state pending
        $rollbackMessages = $this->Rollbacksmsnotification_model->get_rollbacksmsnotificationByStatus(smsState::PENDING);

        $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Preparing SMS update for ' . count($rollbackMessages) . ' rollback messages');


        foreach ($rollbackMessages as $rollbackMessage){

            $rollbackNotificationId = $rollbackMessage['rollbackSmsNotificationId'];
            $message = $rollbackMessage['message'];
            $msisdn = $rollbackMessage['msisdn'];

            $this->db->trans_start();

            $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Sending Rollback SMS for ' . $rollbackNotificationId);

            // Send SMS and save in DB
            $response = SMS::MESSAGE_SMS($message, $msisdn);

            $smsNotificationparams = [];

            if($response['success']){

                $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Rollback SMS successful for ' . $rollbackNotificationId);

                // Update SMS in RollbackSMSNotification table in state SENT

                $smsNotificationparams = array(
                    'status' => smsState::SENT,
                    'attemptCount' => $rollbackMessage['attemptCount'] + 1,
                    'sendDateTime' => date('c')
                );

            }else{

                $this->fileLogAction('7017', 'BatchOperationService::smsUpdater', 'Rollback SMS failed for ' . $rollbackNotificationId);

                $smsNotificationparams = array(
                    'attemptCount' => $rollbackMessage['attemptCount'] + 1,
                );

            }

            $this->Rollbacksmsnotification_model->update_rollbacksmsnotification($rollbackNotificationId, $smsNotificationparams);

            $this->db->trans_complete();
        }

    }

    /**
     * Executed by all
     * Checks for all ussd SMS messages for those in pending state and send SMS
     */
    public function ussdSmsUpdater(){

        $this->fileLogAction('7018', 'BatchOperationService::ussdSmsUpdater', 'ussdSmsUpdater STARTED');

        // Get all USSD messages in state pending
        $ussdMessages = $this->Ussdsmsnotification_model->get_ussdsmsnotificationByStatus(smsState::PENDING);

        $this->fileLogAction('7018', 'BatchOperationService::ussdSmsUpdater', 'Preparing SMS update for ' . count($ussdMessages) . ' USSD messages');

        foreach ($ussdMessages as $ussdMessage){

            $ussdNotificationId = $ussdMessage['ussdSmsNotificationId'];
            $message = $ussdMessage['message'];
            $msisdn = $ussdMessage['msisdn'];

            $this->db->trans_start();

            $this->fileLogAction('7018', 'BatchOperationService::ussdSmsUpdater', 'Sending USSD SMS for ' . $ussdNotificationId);

            // Send USSD SMS and save in DB
            $response = SMS::USSD_SMS($message, $msisdn);

            $smsNotificationparams = [];

            if($response['success']){

                $this->fileLogAction('7018', 'BatchOperationService::ussdSmsUpdater', 'USSD SMS successful for ' . $ussdNotificationId);

                // Update SMS in USSDNotificationTable in state SENT

                $smsNotificationparams = array(
                    'status' => smsState::SENT,
                    'attemptCount' => $ussdMessage['attemptCount'] + 1,
                    'sendDateTime' => date('c')
                );

            }else{

                $this->fileLogAction('7018', 'BatchOperationService::ussdSmsUpdater', 'USSD SMS failed for ' . $ussdNotificationId);

                $smsNotificationparams = array(
                    'attemptCount' => $ussdMessage['attemptCount'] + 1,
                );

            }

            $this->Ussdsmsnotification_model->update_ussdsmsnotification($ussdNotificationId, $smsNotificationparams);

            $this->db->trans_complete();
        }

    }

    /**
     * Returns porting params array from CADB porting transsaction
     * @param $porting
     */
    private function getPortingParams($porting){

        $portingParams = array(
            'portingId' => $porting->portingTransaction->portingId,
            'recipientNetworkId' => $porting->portingTransaction->recipientNrn->networkId,
            'recipientRoutingNumber' => $porting->portingTransaction->recipientNrn->routingNumber,
            'donorNetworkId' => $porting->portingTransaction->donorNrn->networkId,
            'donorRoutingNumber' => $porting->portingTransaction->recipientNrn->routingNumber,
            'recipientSubmissionDateTime' => $porting->portingTransaction->recipientSubmissionDateTime,
            'portingDateTime' => $porting->portingTransaction->portingDateTime,
            'rio' =>  $porting->portingTransaction->rio,
            'startMSISDN' =>  $porting->portingTransaction->numberRanges->numberRange->startNumber,
            'endMSISDN' =>  $porting->portingTransaction->numberRanges->numberRange->endNumber,
            'cadbOrderDateTime' => $porting->portingTransaction->cadbOrderDateTime,
            'lastChangeDateTime' => $porting->portingTransaction->lastChangeDateTime,
            'portingState' => $porting->portingTransaction->portingState,
            'contractId' => null,
            'language' => null,
            'portingSubmissionId' => null,
        );

        if(isset($porting->portingTransaction->subscriberInfo->physicalPersonFirstName)) {
            $portingParams['physicalPersonFirstName'] = $porting->portingTransaction->subscriberInfo->physicalPersonFirstName;
            $portingParams['physicalPersonLastName'] = $porting->portingTransaction->subscriberInfo->physicalPersonLastName;
            $portingParams['physicalPersonIdNumber'] = $porting->portingTransaction->subscriberInfo->physicalPersonIdNumber;

        }
        else{
            $portingParams['legalPersonName'] = $porting->portingTransaction->subscriberInfo->legalPersonName;
            $portingParams['legalPersonTin'] = $porting->portingTransaction->subscriberInfo->legalPersonTin;
            $portingParams['contactNumber'] = $porting->portingTransaction->subscriberInfo->contactNumber;
        }

        return $portingParams;

    }

    private function getRollbackParams($rollback){

        $rollbackParams = array(
            'rollbackId' => $rollback->rollbackTransaction->rollbackId,
            'originalPortingId' => $rollback->rollbackTransaction->originalPortingId,
            'donorSubmissionDateTime' => $rollback->rollbackTransaction->donorSubmissionDateTime,
            'preferredRollbackDateTime' => $rollback->rollbackTransaction->preferredRollbackDateTime,
            'rollbackDateTime' => $rollback->rollbackTransaction->rollbackDateTime,
            'cadbOpenDateTime' => $rollback->rollbackTransaction->cadbOpenDateTime,
            'lastChangeDateTime' => $rollback->rollbackTransaction->lastChangeDateTime,
            'rollbackState' => $rollback->rollbackTransaction->rollbackState,
            'rollbackSubmissionId' => null,
        );

        return $rollbackParams;

    }
    
    private function getReturnParams($return){

        $nrParams = array(
            'returnId' => $return->returnTransaction->returnId,
            'openDateTime' => $return->returnTransaction->openDateTime,
            'ownerNetworkId' => $return->returnTransaction->ownerNrn->networkId,
            'ownerRoutingNumber' => $return->returnTransaction->ownerNrn->routingNumber,
            'primaryOwnerNetworkId' => $return->returnTransaction->primaryOwnerNrn->networkId,
            'primaryOwnerRoutingNumber' => $return->returnTransaction->primaryOwnerNrn->routingNumber,
            'returnMSISDN' => null,
            'returnNumberState' => $return->returnTransaction->returnNumberState,
            'numberReturnSubmissionId' => null,
        );
        
        return $nrParams;
    
    }

}

class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port=22)
    {
        $this->connection = ssh2_connect($host, $port);
        if (! $this->connection)
            throw new Exception("Could not connect to $host on port $port.");
    }

    public function login($username, $password)
    {
        if (! ssh2_auth_password($this->connection, $username, $password))
            throw new Exception("Could not authenticate with username $username " .
                "and password $password.");

        $this->sftp = ssh2_sftp($this->connection);
        if (! $this->sftp)
            throw new Exception("Could not initialize SFTP subsystem.");
    }

    public function uploadFile($local_file, $remote_file) {

        $sftp = $this->sftp;

        $stream = fopen("ssh2.sftp://$sftp$remote_file", 'w');

        if (! $stream)
            throw new Exception("Could not open file: $remote_file");

        $data_to_send = file_get_contents($local_file);

        if ($data_to_send === false)
            throw new Exception("Could not open local file: $local_file.");

        if (fwrite($stream, $data_to_send) === false)
            throw new Exception("Could not send data from file: $local_file.");

        fclose($stream);

    }

    public function downloadFile($remote_file, $local_file)
    {

        $sftp = $this->sftp;

        $stream = fopen("ssh2.sftp://$sftp$remote_file", 'r');

        if (! $stream)
            throw new Exception("Could not open file: $remote_file");

        $contents = fread($stream, filesize("ssh2.sftp://$sftp$remote_file"));

        file_put_contents ($local_file, $contents);

        fclose($stream);
    }

    function scanFilesystem($remote_file) {

        $sftp = $this->sftp;
        $dir = "ssh2.sftp://$sftp$remote_file";
        $tempArray = array();
        $handle = opendir($dir);

        // List all the files
        while (false !== ($file = readdir($handle))) {
            if (substr("$file", 0, 1) != "."){
                if(is_dir($file)){
//                $tempArray[$file] = $this->scanFilesystem("$dir/$file");
                } else {
                    $tempArray[]=$file;
                }
            }
        }

        closedir($handle);
        return $tempArray;
    }

    public function deleteFile($remote_file){
        $sftp = $this->sftp;
        unlink("ssh2.sftp://$sftp$remote_file");
    }

}