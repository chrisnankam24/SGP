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

        $this->load->model('Porting_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

    }

    /*
     * Listing of porting
     */
    function index()
    {
        $data['porting'] = $this->Porting_model->get_all_porting();

        $this->load->view('porting/index',$data);
    }

    /**
     * API for retrieving BSCS info linked to temporal number
     */
    public function numberDetails(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $temporalNumber = $this->input->post('temporalNumber');

            // Load temporal number info from BSCS
            $bscsOperationService = new BscsOperationService();
            $data = $bscsOperationService->loadTemporalNumberInfo($temporalNumber);

            $response['success'] = true;
            $response['data'] = $data;

        }else{

            $response['success'] = false;
            $response['message'] = 'No temporal number found';

        }

        $this->send_response($response);
    }

    /**
     * API for performing order request
     */
    public function orderPorting() {

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            // Retrieve POST params

            $donorOperator = $this->input->post('donorOperator'); // 0 == MTN, 1 == Nexttel
            $portingMsisdn = $this->input->post('portingMsisdn');
            $subscriberType = $this->input->post('subscriberType'); // 0 == Person, 1 == Enterprise
            $rio = $this->input->post('rio');
            $physicalPersonFirstName = $this->input->post('physicalPersonFirstName');
            $physicalPersonLastName = $this->input->post('physicalPersonLastName');
            $physicalPersonIdNumber = $this->input->post('physicalPersonIdNumber');
            $legalPersonName = $this->input->post('legalPersonName');
            $legalPersonTin = $this->input->post('legalPersonTin');
            $contactNumber = $this->input->post('contactNumber');
            $portingDateTime = $this->input->post('portingDateTime');
            $temporalNumber = $this->input->post('temporalNumber');
            $contractId = $this->input->post('contractId');

            // Construct subscriber info

            $subscriberInfo = new \PortingService\Porting\subscriberInfoType();

            if($subscriberType == 0){
                $subscriberInfo->physicalPersonFirstName = $physicalPersonFirstName;
                $subscriberInfo->physicalPersonLastName = $physicalPersonLastName;
                $subscriberInfo->physicalPersonIdNumber = $physicalPersonIdNumber;
            }else{
                $subscriberInfo->legalPersonName = $legalPersonName;
                $subscriberInfo->legalPersonTin = $legalPersonTin;
                $subscriberInfo->contactNumber = $contactNumber;
            }

            // Make Order Porting Operation

            $portingOperationService = new PortingOperationService();
            $orderResponse = $portingOperationService->order($donorOperator, $portingDateTime, $portingMsisdn, $rio, $subscriberInfo);

            // Verify response

            if($orderResponse->success){

                $this->db->trans_start();

                // Fill in submission table with submission state ordered
                //$orderResponse = new \PortingService\Porting\orderResponse();

                $submissionParams = array(
                    'donorNetworkId' => $orderResponse->portingTransaction->donorNrn->networkId,
                    'donorRoutingNumber' => $orderResponse->portingTransaction->donorNrn->routingNumber,
                    'subscriberSubmissionDateTime' => date('c'),
                    'portingDateTime' => $portingDateTime,
                    'rio' => $rio,
                    'portingMSISDN' => $portingMsisdn,
                    'physicalPersonIdNumber' => $physicalPersonIdNumber,
                    'physicalPersonFirstName' => $physicalPersonFirstName,
                    'physicalPersonLastName' => $physicalPersonLastName,
                    'legalPersonName' => $legalPersonName,
                    'legalPersonTin' => $legalPersonTin,
                    'contactNumber' => $contactNumber,
                    'contractId' => $contractId,
                    'temporalMSISDN' => $temporalNumber,
                    'submissionState' => \PortingService\Porting\portingSubmissionStateType::ORDERED,
                    'orderedDateTime' => date('c')
                );

                $portingsubmission_id = $this->Portingsubmission_model->add_portingsubmission($submissionParams);

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
                    'subscriberMSISDN' =>  $orderResponse->portingTransaction->numberRanges->numberRange->startNumber,
                    'cadbOrderDateTime' => $orderResponse->portingTransaction->cadbOrderDateTime,
                    'lastChangeDateTime' => $orderResponse->portingTransaction->lastChangeDateTime,
                    'portingState' => \PortingService\Porting\portingStateType::ORDERED,
                    'portingSubmissionId' => $portingsubmission_id,
                );

                if($subscriberType == 0) {
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


                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PORTING_ORDERED_BUT_DB_FILLED_INCOMPLETE', []);

                }else {

                }

                $response['message'] = 'Porting has been ORDERED successfully!';

            }

            else{

                $fault = $orderResponse->error;

                $emailService = new EmailService();

                $response['success'] = false;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:
                        $response['success'] = true;

                        $donorNetworkId = '';
                        $donorRoutingNumber = '';

                        if($donorOperator == 0) {
                            // MTN
                            $donorNetworkId = Operator::MTN_NETWORK_ID;
                            $donorRoutingNumber = Operator::MTN_ROUTING_NUMBER;
                        }else{
                            // Orange
                            $donorNetworkId = Operator::NEXTTEL_NETWORK_ID;
                            $donorRoutingNumber = Operator::NEXTTEL_ROUTING_NUMBER;
                        }

                        $this->db->trans_start();

                        $submissionParams = array(
                            'donorNetworkId' => $donorNetworkId,
                            'donorRoutingNumber' => $donorRoutingNumber,
                            'subscriberSubmissionDateTime' => date('c'),
                            'portingDateTime' => $portingDateTime,
                            'rio' => $rio,
                            'portingMSISDN' => $portingMsisdn,
                            'physicalPersonIdNumber' => $physicalPersonIdNumber,
                            'physicalPersonFirstName' => $physicalPersonFirstName,
                            'physicalPersonLastName' => $physicalPersonLastName,
                            'legalPersonName' => $legalPersonName,
                            'legalPersonTin' => $legalPersonTin,
                            'contactNumber' => $contactNumber,
                            'contractId' => $contractId,
                            'temporalMSISDN' => $temporalNumber,
                            'submissionState' => \PortingService\Porting\portingSubmissionStateType::STARTED,
                            'orderedDateTime' => date('c')
                        );

                        $this->Portingsubmission_model->add_portingsubmission($submissionParams);

                        $this->db->trans_complete();

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING_REQUESTED_OPERATOR_INACTIVE_BUT_STARTED_INCOMPLETE', []);
                            $response['message'] = 'Operator is currently Inactive. We have nonetheless encountered problems saving your request. Please contact Back Office';

                        }else {

                            $response['message'] = 'Operator is currently Inactive. You request has been saved and will be performed as soon as possible';

                        }

                        break;

                    case Fault::NUMBER_NOT_OWNED_BY_OPERATOR:
                        $response['message'] = 'Porting number not owned by donor';
                        break;

                    case Fault::UNKNOWN_NUMBER:
                        $response['message'] = 'Porting number is unknown';
                        break;

                    case Fault::TOO_NEAR_PORTED_PERIOD:
                        $response['message'] = 'Number was already ported within 60 days';
                        break;

                    case Fault::PORTING_NOT_ALLOWED_REQUESTS:
                        $response['message'] = 'Number was already ported two times in period of one year';
                        break;

                    case Fault::RIO_NOT_VALID:
                        $response['message'] = 'RIO format or checksum digits don’t match up';
                        break;

                    case Fault::NUMBER_RESERVED_BY_PROCESS:
                        $response['message'] = 'Number already in transaction';
                        break;

                    case Fault::INVALID_PORTING_DATE_AND_TIME:
                        $response['message'] = 'Invalid porting date and time (out of defined time period)';
                        break;

                    // Terminal Error Processes
                    case Fault::NUMBER_RANGES_OVERLAP:
                    case Fault::NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED:
                    case Fault::INVALID_REQUEST_FORMAT:
                    case Fault::ACTION_NOT_AUTHORIZED:
                    case Fault::SUBSCRIBER_DATA_MISSING:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';
                        break;

                    default:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Back Office';

                }


            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No/Incomplete information submitted';

        }

        $this->send_response($response);

    }

    /**
     * API for performing accept request
     */
    public function acceptPorting(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');

            // Make Accept Porting Operation

            $portingOperationService = new PortingOperationService();
            $acceptResponse = $portingOperationService->accept($portingId);

            // Verify response

            if($acceptResponse->success){

                $this->db->trans_start();

                // Insert into Porting State Evolution table

                $portingEvolutionParams = array(
                    'lastChangeDateTime' => $acceptResponse->portingTransaction->lastChangeDateTime,
                    'portingState' => \PortingService\Porting\portingStateType::ACCEPTED,
                    'isAutoReached' => false,
                    'portingId' => $acceptResponse->portingTransaction->portingId,
                );

                $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                // Update Porting table

                $portingParams = array(
                    'portingDateTime' => $acceptResponse->portingTransaction->portingDateTime,
                    'cadbOrderDateTime' => $acceptResponse->portingTransaction->cadbOrderDateTime,
                    'lastChangeDateTime' => $acceptResponse->portingTransaction->lastChangeDateTime,
                    'portingState' => \PortingService\Porting\portingStateType::ACCEPTED
                );

                $this->Porting_model->update_porting($portingId, $portingParams);


                // Send SMS to Subscriber

                $subscriberMSISDN = $acceptResponse->portingTransaction->numberRanges->numberRange->startNumber;

                $portingDateTime = $acceptResponse->portingTransaction->portingDateTime;

                $day = date('d/m/Y', strtotime($portingDateTime));
                $start_time = date('h:i:s', strtotime($portingDateTime));
                $end_time = date('h:i:s', strtotime('+2 hours', strtotime($portingDateTime)));

                $denom_OPR = $acceptResponse->portingTransaction->recipientNrn->networkId;

                if($acceptResponse->portingTransaction->recipientNrn->networkId == Operator::MTN_NETWORK_ID){
                    $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_MTN;
                }else{
                    $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;
                }

                $smsResponse = SMS::OPD_Subscriber_Reminder($subscriberMSISDN, $denom_OPR, $day, $start_time, $end_time);

                if($smsResponse->success){
                    // Insert Porting SMS Notification
                    $smsNotificationparams = array(
                        'portingId' => $portingId,
                        'smsType' => SMSType::OPD_PORTING_REMINDER,
                        'sendDateTime' => date('c')
                    );

                    $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

                }else{
                    // TODO: Pending SMS.
                }

                $this->db->trans_complete();

                $response['success'] = true;

                if ($this->db->trans_status() === FALSE) {

                    $emailService = new EmailService();
                    $emailService->adminErrorReport('PORTING_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

                }

                $response['message'] = 'Porting has been ACCEPTED successfully!';

            }

            else{

                $fault = $acceptResponse->error;

                $emailService = new EmailService();

                $response['success'] = false;

                switch ($fault) {
                    // Terminal Processes
                    case Fault::INVALID_OPERATOR_FAULT:
                        $response['message'] = 'Operator is not active. Please try again later';
                        break;

                    // Terminal Error Processes
                    case Fault::PORTING_ACTION_NOT_AVAILABLE:
                    case Fault::INVALID_PORTING_ID:
                    case Fault::INVALID_REQUEST_FORMAT:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                        break;

                    default:
                        $emailService->adminErrorReport($fault, []);
                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';

                }


            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

        }

        $this->send_response($response);
    }

    /**
     * API for performing reject request
     */
    public function rejectPorting(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $portingId = $this->input->post('portingId');
            $rejectionReason = $this->input->post('rejectionReason');
            $cause = $this->input->post('cause');

            if($rejectionReason != rejectionReasonType::OUTSTANDING_OBLIGATIONS_TO_DONOR &&
                $rejectionReason != rejectionReasonType::SUBSCRIBER_CANCELLED_PORTING &&
                $rejectionReason != rejectionReasonType::SUBSCRIBER_CHANGED_NUMBER){

                // Make Reject Porting Operation

                $portingOperationService = new PortingOperationService();
                $rejectResponse = $portingOperationService->reject($portingId, $rejectionReason, $cause);

                // Verify response

                if($rejectResponse->success){

                    $this->db->trans_start();

                    $rejectResponse = new \PortingService\Porting\rejectResponse();

                    // Insert into Porting State Evolution table

                    $portingEvolutionParams = array(
                        'lastChangeDateTime' => $rejectResponse->portingTransaction->lastChangeDateTime,
                        'portingState' => \PortingService\Porting\portingStateType::REJECTED,
                        'isAutoReached' => false,
                        'portingId' => $rejectResponse->portingTransaction->portingId,
                    );

                    $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                    // Update Porting table

                    $portingParams = array(
                        'portingDateTime' => $rejectResponse->portingTransaction->portingDateTime,
                        'cadbOrderDateTime' => $rejectResponse->portingTransaction->cadbOrderDateTime,
                        'lastChangeDateTime' => $rejectResponse->portingTransaction->lastChangeDateTime,
                        'portingState' => \PortingService\Porting\portingStateType::REJECTED
                    );

                    $this->Porting_model->update_porting($portingId, $portingParams);

                    // Insert into PortingDenyRejectionAbandoned

                    $pdraParams = array(
                        'denyRejectionReason' => $rejectionReason,
                        'cause' => $cause,
                        'portingId' => $portingId
                    );

                    $this->Portingdenyrejectionabandon_model->add_portingdenyrejectionabandon($pdraParams);


                    $this->db->trans_complete();

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('PORTING_ACCEPTED_BUT_DB_FILLED_INCOMPLETE', []);

                    }else {

                    }

                    $response['message'] = 'Porting has been ACCEPTED successfully!';

                }

                else{

                    $fault = $rejectResponse->error;

                    $emailService = new EmailService();

                    $response['success'] = false;

                    switch ($fault) {
                        // Terminal Processes
                        case Fault::INVALID_OPERATOR_FAULT:
                            $response['message'] = 'Operator is not active. Please try again later';
                            break;

                        // Terminal Error Processes
                        case Fault::PORTING_ACTION_NOT_AVAILABLE:
                        case Fault::INVALID_PORTING_ID:
                        case Fault::INVALID_REQUEST_FORMAT:
                        case Fault::CAUSE_MISSING:
                            $emailService->adminErrorReport($fault, []);
                            $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                            break;

                        default:
                            $emailService->adminErrorReport($fault, []);
                            $response['message'] = 'Fatal Error Encountered. Please contact Administrator';

                    }


                }

            }
            else{

                $response['success'] = false;
                $response['message'] = 'Invalid rejection reason';
            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No porting id found';

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

}
