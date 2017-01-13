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
        $this->load->model('Rollbackstateevolution_model');

        $this->load->model('Numberreturn_model');
        $this->load->model('Returnrejection_model');
        $this->load->model('Numberreturnsubmission_model');
        $this->load->model('Numberreturnstateevolution_model');

        $this->load->model('Provisioning_model');

    }

    public function index(){

    }

    /**
     * TODO: OK
     * Executed as OPR
     * BATCH_002
     * Checks for all ports in Submission table in STARTED state and attempts making Orders for them
     */
    public function portingSubmissionToOrdered(){

        // Load ports in Submission table in STARTED state

        $startedPorts = $this->Portingsubmission_model->get_submissionByState(portingSubmissionStateType::STARTED);

        $portingOperationService = new PortingOperationService();

        $emailService = new EmailService();

        foreach ($startedPorts as $startedPort){

            // Retrieve Port params

            $portingSubmissionId = $startedPort['portingSubmissionId'];

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
            $orderedDateTime = $startedPort['orderedDateTime'];
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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {

                    $emailService->adminSubmissionReport('PORTING_SUBMISSION_ORDERED_BUT_DB_FILLED_INCOMPLETE', []);

                }else{

                    $emailService->adminAgentsBatchPortingSubmission([]);

                }

            }

            else{

                $fault = $orderResponse->error;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $currentDateTime = date('c');

                        $start_time = date('y-d-m h:i:s', strtotime($currentDateTime));
                        $end_time = date('y-d-m h:i:s', strtotime($orderedDateTime));

                        $start_time = date_create_from_format('y-d-m h:i:s', $start_time);
                        $end_time = date_create_from_format('y-d-m h:i:s', $end_time);

                        $diff = date_diff($start_time, $end_time);

                        // More than 20 minutes difference
                        if($diff->i > 20){

                            $emailService->adminAgentsRetardedSubmission([]);

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
                        $emailService->adminSubmissionReport($fault, []);
                        break;

                    default:
                        $emailService->adminSubmissionReport($fault, []);
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

        $orderedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::ORDERED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $portingOperationService = new PortingOperationService();

        $emailService = new EmailService();

        foreach ($orderedPorts as $orderedPort) {

            $portingId = $orderedPort['portingId'];

            // Load subscriber data from BSCS using MSISDN

            $subscriberMSISDN = $orderedPort['startMSISDN'];

            $subscriberInfo = $bscsOperationService->loadNumberInfo($subscriberMSISDN);

            $portingDenialReason = null;
            $cause = null;

            if($subscriberInfo != -1){

                if($subscriberInfo != null){ // Connection to BSCS successful and User found

                    // Number Owned by Orange

                    $subscriberRIO = RIO::get_rio($subscriberMSISDN);

                    if($subscriberRIO == $orderedPort['rio']){

                        // Subscriber RIO Valid

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
                        // Subscriber RIO Invalid
                        $portingDenialReason = \PortingService\Porting\denialReasonType::RIO_NOT_VALID;
                        $cause = 'Invalid RIO';
                    }

                }
                else{ // BSCS returns this in case of in existent user
                    // Number not owned by Orange
                    $portingDenialReason = \PortingService\Porting\denialReasonType::NUMBER_NOT_OWNED_BY_SUBSCRIBER;
                    $cause = 'In existent Number';
                }

                if($portingDenialReason == null) {
                    // All Checks OK. Approve Port

                    $approveResponse = $portingOperationService->approve($portingId);

                    if($approveResponse->success){

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

                        $this->db->trans_complete();

                        if ($this->db->trans_status() === FALSE) {
                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING_APPROVED_BUT_DB_FILLED_INCOMPLETE', []);
                        }else{
                            $emailService->adminAgentsPortingApprovedDenied([]);
                        }

                    }
                    else{

                        $fault = $approveResponse->error;

                        switch ($fault) {
                            // Terminal Processes
                            case Fault::INVALID_OPERATOR_FAULT:
                            case Fault::INVALID_REQUEST_FORMAT:
                            case Fault::PORTING_ACTION_NOT_AVAILABLE:
                            case Fault::INVALID_PORTING_ID:
                            default:
                                $emailService->adminErrorReport($fault, []);

                        }

                    }

                }
                else{
                    // Failed Check. Deny Port
                    $denyResponse = $portingOperationService->deny($portingId, $portingDenialReason, $cause);

                    if($denyResponse->success){

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

                        $this->db->trans_complete();

                        if ($this->db->trans_status() === FALSE) {
                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING_DENIED_BUT_DB_FILLED_INCOMPLETE', []);
                        }else{
                            $emailService->adminAgentsPortingApprovedDenied([]);
                        }

                    }
                    else{

                        $fault = $denyResponse->error;

                        switch ($fault) {
                            // Terminal Processes
                            case Fault::INVALID_OPERATOR_FAULT:
                            case Fault::INVALID_REQUEST_FORMAT:
                            case Fault::PORTING_ACTION_NOT_AVAILABLE:
                            case Fault::INVALID_PORTING_ID:
                            case Fault::CAUSE_MISSING:
                                $emailService->adminErrorReport($fault, []);
                                break;
                            default:
                                $emailService->adminErrorReport($fault, []);

                        }

                    }

                }

            }else{
                // Connection to BSCS failed. Wait and try again later

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

        // Load ports in Porting table in APPROVED state in which we are OPD AND Personal

        $approvedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::APPROVED, Operator::ORANGE_NETWORK_ID, 0);

        $emailService = new EmailService();

        foreach ($approvedPorts as $approvedPort){

            // Send mail to Back Office with Admin in CC for Acceptance / Rejection
            $emailService->backOfficePortingAcceptReject($approvedPort);

        }

    }

    /**
     * TODO: OK
     * Checks for all enterprise ports in APPROVED state and sends mail for their Acceptance / Rejection
     */
    public function portingApprovedToAcceptedRejectedEnterprise(){

        // Load ports in Porting table in APPROVED state in which we are OPD AND Enterprise

        $approvedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::APPROVED, Operator::ORANGE_NETWORK_ID, 1);

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

        // Load ports in Porting table in ACCEPTED state in which we are OPD

        $acceptedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load ports in Porting table in CONTRACT_DELETED_CONFIRMED state in which we are OPD

        $msisdnContractDeletedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::CONTRACT_DELETED_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        // Load ports in Porting table in MSISDN_EXPORT_CONFIRMED state in which we are OPD

        $msisdnExportedPorts = $this->Porting_model->get_porting_by_state_and_donor(\PortingService\Porting\portingStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedPorts as $acceptedPort){

            $portingId = $acceptedPort['portingId'];

            // Check if port in provision table in state STARTED
            $provisionPort = $this->Provisioning_model->get_provisioning_by_process_state($portingId, processType::PORTING, provisionStateType::STARTED);

            if($provisionPort){

                // Porting already provisioned. Start porting moving to CONTRACT_DELETED_CONFIRMED state

                $contractId = $acceptedPort['contractId'];

                $deleteResponse = $bscsOperationService->deleteContract($contractId);

                if($deleteResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('PORTING_MSISDN_CONTRACT_DELETED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    // Notify Admin on failed Export
                    $faultCode = $deleteResponse->error;

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

                    $emailService->adminErrorReport($fault, []);

                }

            }else{

                //Port not yet Provisioned. Do nothing, wait till provision

            }
        }

        foreach ($msisdnContractDeletedPorts as $msisdnContractDeletedPort){

            $portingId = $msisdnContractDeletedPort['portingId'];

            // Porting already provisioned. Start porting moving to MSISDN_EXPORT_CONFIRMED state
            $subscriberMSISDN = $msisdnContractDeletedPort['startMSISDN'];

            $exportResponse = $bscsOperationService->exportMSISDN($subscriberMSISDN);

            if($exportResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PORTING_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

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
        }

        foreach ($msisdnExportedPorts as $msisdnExportedPort){

            $portingId = $msisdnExportedPort['portingId'];

            $subscriberMSISDN = $msisdnExportedPort['startMSISDN'];

            $fromOperator = $msisdnExportedPort['donorNetworkId'];

            $toOperator = $msisdnExportedPort['recipientNetworkId'];

            $fromRoutingNumber = $msisdnExportedPort['donorRoutingNumber'];

            $toRoutingNumber = $msisdnExportedPort['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->db->trans_start();

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PORTING_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

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

        // Load ports in Porting table in ACCEPTED state in which we are OPR

        $acceptedPorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load ports in Porting table in MSISDN_IMPORT_CONFIRMED state in which we are OPR

        $msisdnConfirmedPorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::MSISDN_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        // Load ports in Porting table in MSISDN_CHANGE_IMPORT_CONFIRMED state in which we are OPR

        $msisdnChangePorts = $this->Porting_model->get_porting_by_state_and_recipient(\PortingService\Porting\portingStateType::MSISDN_CHANGE_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $portingOperationService = new PortingOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedPorts as $acceptedPort) {

            $portingDateTime = $acceptedPort['portingDateTime'];

            $portingId = $acceptedPort['portingId'];

            $currentDateTime = date('c');

            $start_time = date('y-d-m h:i:s', strtotime($portingDateTime));
            $end_time = date('y-d-m h:i:s', strtotime($currentDateTime));

            $start_time = date_create_from_format('y-d-m h:i:s', $start_time);
            $end_time = date_create_from_format('y-d-m h:i:s', $end_time);

            $diff = date_diff($start_time, $end_time);

            // End time >= start time, less than 30 minutes difference
            if($diff->invert == 0 && $diff->i < 30){

                // Start porting moving to MSISDN_IMPORT_CONFIRMED state. Import Porting MSISDN into BSCS
                $subscriberMSISDN = $acceptedPort['startMSISDN'];

                $importResponse = $bscsOperationService->importMSISDN($subscriberMSISDN);

                if($importResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('PORTING_MSISDN_IMPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    // Notify Admin on failed Import
                    $faultCode = $importResponse->error;

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

            }else if($diff->invert == 0 && $diff->h > 0){

                // More than 1hrs late, alert Admin

                $emailService->adminErrorReport('MORE_THAN_ONE_HOUR_FROM_EXPECTED_PORTING_DATE_TIME', []);

            }

        }

        foreach ($msisdnConfirmedPorts as $msisdnConfirmedPort){

            $portingId = $msisdnConfirmedPort['portingId'];

            $subscriberInfo = $this->Portingsubmission_model->get_submissionByPortingId($portingId);

            $subscriberMSISDN = $msisdnConfirmedPort['startMSISDN'];

            $changeResponse = $bscsOperationService->changeImportMSISDN($subscriberInfo['temporalMSISDN'], $subscriberMSISDN);

            if($changeResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PORTING_MSISDN_CHANGED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }else{

            }

        }

        foreach ($msisdnChangePorts as $msisdnChangePort){

            // Move porting to CONFIRMED state
            $fromOperator = $msisdnChangePort['donorNetworkId'];

            $subscriberMSISDN = $msisdnChangePort['startMSISDN'];

            $toOperator = $msisdnChangePort['recipientNetworkId'];

            $fromRoutingNumber = $msisdnChangePort['donorRoutingNumber'];

            $toRoutingNumber = $msisdnChangePort['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                // Send confirm request

                $portingId = $msisdnChangePort['portingId'];

                $portingDateAndTime = date('c', strtotime('+5 minutes', strtotime(date('c'))));

                // Make Confirm Porting Operation

                $confirmResponse = $portingOperationService->confirm($portingId, $portingDateAndTime);

                // Verify response

                if($confirmResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('PORTING_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    $fault = $confirmResponse->error;

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                        case Fault::PORTING_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_PORTING_ID:
                        case Fault::INVALID_PORTING_DATE_AND_TIME:
                            $emailService->adminConfirmReport($fault, []);
                            break;

                        default:
                            $emailService->adminConfirmReport($fault, []);
                    }

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

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

        // Load rollbacks in Submission table in STARTED state

        $startedRollbacks = $this->Rollbacksubmission_model->get_submissionByState(rollbackSubmissionStateType::STARTED);

        $rollbackOperationService = new RollbackOperationService();

        $emailService = new EmailService();

        foreach ($startedRollbacks as $startedRollback){

            $originalPortingId = $startedRollback['originalPortingId'];
            $rollbackSubmissionId = $startedRollback['rollbackSubmissionId'];
            $donorSubmissionDateTime = date('c');
            $preferredRollbackDateTime = $startedRollback['preferredRollbackDateTime'];
            $openedDateTime = $startedRollback['openedDateTime'];

            // Make Open Rollback Operation

            $openResponse = $rollbackOperationService->open($originalPortingId, $donorSubmissionDateTime, $preferredRollbackDateTime);

            // Verify response

            if($openResponse->success){

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
                    'rollbackDateAndTime' => $openResponse->rollbackTransaction->rollbackDateTime,
                    'cadbOpenDateTime' => $openResponse->rollbackTransaction->cadbOpenDateTime,
                    'lastChangeDateTime' => $openResponse->rollbackTransaction->lastChangeDateTime,
                    'rollbackState' => \RollbackService\Rollback\rollbackStateType::OPENED,
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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {

                    $emailService->adminSubmissionReport('ROLLBACK_SUBMISSION_OPENED_BUT_DB_FILLED_INCOMPLETE', []);

                }else{

                    $emailService->adminAgentsBatchRollbackSubmission([]);

                }

            }

            else{

                $fault = $openResponse->error;

                var_dump($fault);

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:

                        $currentDateTime = date('c');

                        $start_time = date('y-d-m h:i:s', strtotime($currentDateTime));
                        $end_time = date('y-d-m h:i:s', strtotime($openedDateTime));

                        $start_time = date_create_from_format('y-d-m h:i:s', $start_time);
                        $end_time = date_create_from_format('y-d-m h:i:s', $end_time);

                        $diff = date_diff($start_time, $end_time);

                        // More than 20 minutes difference
                        if($diff->i > 20){

                            $emailService->adminAgentsRetardedSubmission([]);

                        }

                    // Terminal Error Processes
                    case Fault::ROLLBACK_NOT_ALLOWED:
                    case Fault::UNKNOWN_PORTING_ID:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                        $emailService->adminSubmissionReport($fault, []);
                        break;
                    default:
                        $emailService->adminSubmissionReport($fault, []);
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

        // Load rollback in Rollback table in OPENED state in which we are OPR

        $openedPorts = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::OPENED, Operator::ORANGE_NETWORK_ID);

        $emailService = new EmailService();

        foreach ($openedPorts as $openedPort){

            // Send mail to Back office with Admin in CC for Acceptance / Rejection
            //$emailService->backOfficRollbackAcceptReject([]);

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

        // Load rollbacks in Rollback table in ACCEPTED state in which we are OPR

        $acceptedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load rollbacks in Rollback table in CONTRACT_DELETED_CONFIRMED state in which we are OPR

        $msisdnContractDeletedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::CONTRACT_DELETED_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        // Load rollbacks in Rollback table in MSISDN_EXPORT_CONFIRMED state in which we are OPR

        $msisdnExportedRollbacks = $this->Rollback_model->get_rollback_by_state_and_recipient(\RollbackService\Rollback\rollbackStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedRollbacks as $acceptedRollback){

            $rollbackId = $acceptedRollback['rollbackId'];

            // Check if rollback in provision table in state STARTED
            $provisionRollback = $this->Provisioning_model->get_provisioning_by_process_state($rollbackId, processType::ROLLBACK, \ProvisionService\ProvisionNotification\provisionStateType::STARTED);

            if($provisionRollback){

                // Rollback already provisioned. Start rollback moving to CONTRACT_DELETED_CONFIRMED state
                $subscriberMSISDN = $acceptedRollback['startMSISDN'];

                $contractId = $bscsOperationService->getContractId($subscriberMSISDN);

                $deleteResponse = $bscsOperationService->deleteContract($contractId);

                if($deleteResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('ROLLBACK_CONTRACT_DELETED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    // Notify Admin on failed Export
                    $faultCode = $deleteResponse->error;

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

                    $emailService->adminErrorReport($fault, []);

                }

            }else{

                //Rollback not yet Provisioned. Do nothing, wait till provision

            }
        }

        foreach ($msisdnContractDeletedRollbacks as $msisdnContractDeletedRollback){

            $rollbackId = $msisdnContractDeletedRollback['rollbackId'];

            $subscriberMSISDN = $msisdnContractDeletedRollback['startMSISDN'];

            $exportResponse = $bscsOperationService->exportMSISDN($subscriberMSISDN);

            if($exportResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

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
        }

        foreach ($msisdnExportedRollbacks as $msisdnExportedRollback){

            $rollbackId = $msisdnExportedRollback['rollbackId'];

            $fromOperator = $msisdnExportedRollback['donorNetworkId'];

            $subscriberMSISDN = $msisdnExportedRollback['startMSISDN'];

            $toOperator = $msisdnExportedRollback['recipientNetworkId'];

            $fromRoutingNumber = $msisdnExportedRollback['donorRoutingNumber'];

            $toRoutingNumber = $msisdnExportedRollback['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

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

        // Load rollbacks in Rollback table in ACCEPTED state in which we are OPD

        $acceptedRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load rollbacks in Rollback table in MSISDN_IMPORT_CONFIRMED state in which we are OPD

        $msisdnConfirmedRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::MSISDN_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        // Load rollbacks in Rollback table in MSISDN_CHANGE_IMPORT_CONFIRMED state in which we are OPD

        $msisdnChangeRollbacks = $this->Rollback_model->get_rollback_by_state_and_donor(\RollbackService\Rollback\rollbackStateType::MSISDN_CHANGE_IMPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $rollbackOperationService = new RollbackOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedRollbacks as $acceptedRollback) {

            $rollbackDateTime = $acceptedRollback['preferredRollbackDateTime'];

            $rollbackId = $acceptedRollback['rollbackId'];

            $currentDateTime = date('c');

            $start_time = date('y-d-m h:i:s', strtotime($rollbackDateTime));
            $end_time = date('y-d-m h:i:s', strtotime($currentDateTime));

            $start_time = date_create_from_format('y-d-m h:i:s', $start_time);
            $end_time = date_create_from_format('y-d-m h:i:s', $end_time);

            $diff = date_diff($start_time, $end_time);

            // End time >= start time, less than 15minutes difference
            if(($diff->invert == 0 && $diff->i < 15) || ($diff->invert == 1 && $diff->i < 15)){

                // Start rollback moving to MSISDN_IMPORT_CONFIRMED state. Import rollback MSISDN into BSCS
                $subscriberMSISDN = $acceptedRollback['startMSISDN'];

                $importResponse = $bscsOperationService->importMSISDN($subscriberMSISDN);

                if($importResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('ROLLBACK_MSISDN_IMPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    // Notify Admin on failed Import
                    $faultCode = $importResponse->error;

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

            }
            else if($diff->invert == 1 && $diff->h > 0){

                // More than 1hrs late, alert Admin

                $emailService->adminErrorReport('MORE_THAN_ONE_HOUR_FROM_EXPECTED_PORTING_DATE_TIME', []);

            }

        }

        foreach ($msisdnConfirmedRollbacks as $msisdnConfirmedRollback){

            $rollbackId = $msisdnConfirmedRollback['rollbackId'];

            $subscriberInfo = $this->Rollbacksubmission_model->get_submissionByRollbackId($rollbackId);

            $subscriberMSISDN = $msisdnConfirmedRollback['startMSISDN'];

            $changeResponse = $bscsOperationService->changeImportMSISDN($subscriberInfo['temporalMSISDN'], $subscriberMSISDN);

            if($changeResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('ROLLBACK_MSISDN_CHANGE_IMPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }else{

            }


        }

        foreach ($msisdnChangeRollbacks as $msisdnChangeRollback){

            // Move Rollback to CONFIRMED state
            $fromOperator = $msisdnChangeRollback['donorNetworkId'];

            $subscriberMSISDN = $msisdnChangeRollback['startMSISDN'];

            $toOperator = $msisdnChangeRollback['recipientNetworkId'];

            $fromRoutingNumber = $msisdnChangeRollback['donorRoutingNumber'];

            $toRoutingNumber = $msisdnChangeRollback['recipientRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($subscriberMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

                // Send confirm request

                $rollbackId = $msisdnChangeRollback['rollbackId'];

                $rollbackDateAndTime = date('c', strtotime('+5 minutes', strtotime(date('c'))));

                // Make Confirm Rollback Operation

                $confirmResponse = $rollbackOperationService->confirm($rollbackId, $rollbackDateAndTime);

                // Verify response

                if($confirmResponse->success){

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

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $emailService = new EmailService();
                        $emailService->adminErrorReport('ROLLBACK_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                    }else{

                    }

                }
                else{

                    $fault = $confirmResponse->error;

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                        case Fault::ROLLBACK_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_ROLLBACK_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                            $emailService->adminConfirmReport($fault, []);
                            break;

                        default:
                            $emailService->adminConfirmReport($fault, []);
                    }

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

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

        // Load number returns in Submission table in STARTED state

        $startedReturns = $this->Numberreturnsubmission_model->get_submissionByState(returnSubmissionStateType::STARTED);

        $nrOperationService = new ReturnOperationService();

        $emailService = new EmailService();

        foreach ($startedReturns as $startedReturn){

            $submissionId = $startedReturn['numberReturnSubmissionId'];
            $returnMSISDN = $startedReturn['returnMSISDN'];
            $primaryOwnerNetworkId = $startedReturn['primaryOwnerNetworkId'];
            $returnOperator = null;

            if($primaryOwnerNetworkId == Operator::MTN_NETWORK_ID){
                $returnOperator = 0;
            }elseif($primaryOwnerNetworkId == Operator::NEXTTEL_NETWORK_ID){
                $returnOperator = 1;
            }

            // Make Open NR Operation

            $openResponse = $nrOperationService->open($returnOperator, $returnMSISDN);

            // Verify response

            if($openResponse->success){

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

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminSubmissionReport('NR_SUBMISSION_OPENED_BUT_DB_FILLED_INCOMPLETE', []);

                }else {

                    $emailService->adminAgentsBatchNRSubmission([]);

                }

            }

            else{

                $fault = $openResponse->error;

                $emailService = new EmailService();

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
                        $emailService->adminSubmissionReport($fault, []);
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

        // Load NRs in NR table in OPENED state in which we are PO

        $openedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::OPENED, Operator::ORANGE_NETWORK_ID);

        $emailService = new EmailService();

        foreach ($openedReturns as $openedReturn){

            // Send mail to Back office with Admin in CC for Acceptance / Rejection
            //$emailService->backOfficReturnAcceptReject([]);

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

        // Load NRs in Return table in ACCEPTED state in which we are CO

        $acceptedReturns = $this->Numberreturn_model->get_nr_by_state_and_co(\ReturnService\_Return\returnStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load NRs in Return table in MSISDN_EXPORT_CONFIRMED state in which we are CO

        $msisdnConfirmedReturns = $this->Numberreturn_model->get_nr_by_state_and_co(\ReturnService\_Return\returnStateType::MSISDN_EXPORT_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedReturns as $acceptedReturn){

            $returnId = $acceptedReturn['returnId'];

            $returnMSISDN = $acceptedReturn['returnMSISDN'];

            $exportResponse = $bscsOperationService->exportMSISDN($returnMSISDN);

            if($exportResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_MSISDN_EXPORTED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $exportResponse->error;

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
        }

        foreach ($msisdnConfirmedReturns as $msisdnConfirmedReturn){

            $returnId = $msisdnConfirmedReturn['returnId'];

            $toOperator = $msisdnConfirmedReturn['primaryOwnerNetworkId'];

            $returnMSISDN = $msisdnConfirmedReturn['returnMSISDN'];

            $fromOperator = $msisdnConfirmedReturn['ownerNetworkId'];

            $toRoutingNumber = $msisdnConfirmedReturn['primaryOwnerRoutingNumber'];

            $fromRoutingNumber = $msisdnConfirmedReturn['ownerRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOperation($returnMSISDN, $fromOperator, $toOperator, $fromRoutingNumber, $toRoutingNumber);

            if($kpsaResponse['success']){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

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

        // Load NRs in Return table in ACCEPTED state in which we are PO

        $acceptedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::ACCEPTED, Operator::ORANGE_NETWORK_ID);

        // Load NRs in Return table in MSISDN_RETURN_CONFIRMED state in which we are PO

        $msisdnConfirmedReturns = $this->Numberreturn_model->get_nr_by_state_and_po(\ReturnService\_Return\returnStateType::MSISDN_RETURN_CONFIRMED, Operator::ORANGE_NETWORK_ID);

        $bscsOperationService = new BscsOperationService();

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($acceptedReturns as $acceptedReturn){

            $returnId = $acceptedReturn['returnId'];

            $returnMSISDN = $acceptedReturn['returnMSISDN'];

            $returnResponse = $bscsOperationService->returnMSISDN($returnMSISDN);

            if($returnResponse->success){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_MSISDN_RETURNED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }
            else{

                // Notify Admin on failed Export
                $faultCode = $returnResponse->error;

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
        }

        foreach ($msisdnConfirmedReturns as $msisdnConfirmedReturn){

            $returnId = $msisdnConfirmedReturn['returnId'];

            $toOperator = $msisdnConfirmedReturn['primaryOwnerNetworkId'];

            $returnMSISDN = $msisdnConfirmedReturn['returnMSISDN'];

            $toRoutingNumber = $msisdnConfirmedReturn['primaryOwnerRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAReturnOperation($returnMSISDN, $toOperator, $toRoutingNumber);

            if($kpsaResponse['success']){

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

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('RETURN_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['success'], []);

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

        // Load Provisions in Provision table in STARTED state in which we are other

        $startedPortingProvisions = $this->Provisioning_model->get_provisioning_other_porting();

        $startedRollbackProvisions = $this->Provisioning_model->get_provisioning_other_rollback();

        $startedReturnProvisions = $this->Provisioning_model->get_provisioning_other_return();

        // Merge arrays
        $startedProvisions = array_merge($startedPortingProvisions, $startedRollbackProvisions);
        $startedProvisions = array_merge($startedProvisions, $startedReturnProvisions);

        $kpsaOperationService = new KpsaOperationService();

        $emailService = new EmailService();

        foreach ($startedProvisions as $startedProvision){

            // Perform KPSA Operation

            $processId = $startedProvision['processId'];

            $toOperator = $startedProvision['endNetworkId'];

            $subscriberMSISDN = $startedProvision['subscriberMSISDN'];

            $toRoutingNumber = $startedProvision['endRoutingNumber'];

            // Perform KPSA Operation
            $kpsaResponse = $kpsaOperationService->performKPSAOtherOperation($subscriberMSISDN, $toOperator, $toRoutingNumber);

            if($kpsaResponse['success']){

                $this->db->trans_start();

                // Update Provisioning table

                $prParams = array(
                    'provisionState' => \ProvisionService\ProvisionNotification\provisionStateType::COMPLETED
                );

                $this->Provisioning_model->update_provisioning($processId, $prParams);

                // Notify Agents/Admin

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PROVISION_COMPLETED_BUT_DB_FILLED_INCOMPLETE', []);
                }else{

                }

            }

            else{

                $emailService->adminKPSAError($kpsaResponse['message'], []);

            }

        }

    }

    /**
     * Executed by all
     * BATCH_014
     * Performs SFTP synchronization of yesterday data
     */
    private function CADBFileSynchronizer(){

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

                            if($porting['portingState'] != \PortingService\Porting\portingStateType::COMPLETED) {

                                //$emailService->cadbPortingStateOffCompleted([]);

                            }
                        }
                    }

                    fclose($handle);

                }else{

                    $response['success'] = false;
                    $response['message'] = 'No file name found';

                }

            }else{
                $response['success'] = false;
                $response['message'] = 'Failed opening file';
            }

        }else{
            // Mail admin for failure
            $emailService->cadbSynchronizationFailure([]);
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

    }

    /**
     * Executed by all
     * BATCH_013
     * Checks for all processes at SMS levels and verify if SMS has been sent. If not, send
     */
    public function smsUpdater(){

    }

    /**
     * Executed by all
     * BATCH_015_A
     * Generates email on weekly KPI evolution
     */
    public function weeklyEmailAlerter(){

    }

    /**
     * Executed by all
     * BATCH_015_B
     * Generates email on monthly KPI evolution
     */
    public function monthlyEmailAlerter(){

    }

    /**
     * Executed by all
     * BATCH_015_C
     * Generates email on yearly KPI evolution
     */
    public function yearlyEmailAlerter(){

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