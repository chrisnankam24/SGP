<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Porting.php";
require_once "Fault.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use PortingService\Porting as Porting;

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
class PortingOperationService extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Porting_model');
        $this->load->model('FileLog_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/PortingOperationService.wsdl', array(
            "trace" => false
        ));

    }

    public function index(){

    }

    public function test(){
        $portingOperationService = new PortingOperationService();

        $response = $portingOperationService->order(1, 'df', 'sef', 'sfsd', 'sdf');

        $date = "2016-12-08T14:29:51+01:00";

        $date1 = date('c', strtotime('+4 hours', strtotime(date('c'))));
        $date2 = date('c', strtotime(date('c')));

        var_dump($date1);
        echo '<br>';
        var_dump($date2);
    }

    /**
     * TODO: OK
     * @param $donorOperator
     * @param $portingDateTime
     * @param $msisdn
     * @param $rio
     * @param $subscriberInfo
     * @return errorResponse
     */
    public function order($donorOperator, $portingDateTime, $msisdn, $rio, $subscriberInfo) {

        if($this->client) {

            // Make order request
            $request = new Porting\orderRequest();

            try {

                // RecipientNrn
                $request->recipientNrn = new nrnType();
                $request->recipientNrn->networkId = Operator::ORANGE_NETWORK_ID;
                $request->recipientNrn->routingNumber = Operator::ORANGE_ROUTING_NUMBER;

                // DonorNrn
                $request->donorNrn = new nrnType();

                if($donorOperator == 0) {
                    // MTN
                    $request->donorNrn->networkId = Operator::MTN_NETWORK_ID;
                    $request->donorNrn->routingNumber = Operator::MTN_ROUTING_NUMBER;
                }else{
                    // Orange
                    $request->donorNrn->networkId = Operator::NEXTTEL_NETWORK_ID;
                    $request->donorNrn->routingNumber = Operator::NEXTTEL_ROUTING_NUMBER;
                }

                //recipientSubmissionDateTime
                $request->recipientSubmissionDateTime = date('c');

                // recipient PortingDateTime
                $request->portingDateTime = $portingDateTime;

                // rio
                $request->rio = $rio;

                // numberRange
                $numRange = new numberRangeType();
                $numRange->endNumber = $msisdn;
                $numRange->startNumber = $msisdn;
                $request->numberRanges = array($numRange);

                // subscriberInfo
                $request->subscriberInfo = $subscriberInfo;

                $response = $this->client->order($request);

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

    /**
     * TODO: OK
     * @param $portingId string Id of porting process to approve
     * @return mixed
     */
    public function approve($portingId){
        if($this->client) {

            // Make approve request
            $request = new Porting\approveRequest();

            $request->portingId = $portingId;

            try {

                $response = $this->client->approve($request);

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

    /**
     * TODO: OK
     * @param $portingId string id of porting process to accept
     * @return errorResponse
     */
    public function accept($portingId) {

        if($this->client) {

            // Make accept request
            $request = new Porting\acceptRequest();

            $request->portingId = $portingId;

            try {

                $response = $this->client->accept($request);

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

    /**
     * @param $portingId string porting process to confirm
     * @param $portingDateAndTime string date and time of successful System update
     * @return errorResponse
     */
    public function confirm($portingId, $portingDateAndTime) {

        if($this->client) {

            // Make confirm request
            $request = new Porting\confirmRequest();

            $request->portingId = $portingId;
            $request->portingDateTime = $portingDateAndTime;

            try {

                $response = $this->client->confirm($request);

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

    /**
     * @param $porting_id string porting process to deny
     * @param $rejectionReason string reson of denial
     * @param $cause string description of the denial
     * @return errorResponse
     */
    public function reject($porting_id, $rejectionReason, $cause) {

        if($this->client) {

            // Make reject request
            $request = new Porting\rejectRequest();

            $request->portingId = $porting_id;

            $request->rejectionReason = $rejectionReason;

            $request->cause = $cause;

            try {

                $response = $this->client->reject($request);

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

    /**
     * TODO: OK
     * @param $porting_id string porting process to deny
     * @param $rejectionReason string reson of denial
     * @param $cause string description of the denial
     * @return errorResponse
     */
    public function deny($porting_id, $rejectionReason, $cause) {

        if($this->client) {

            // Make deny request
            $request = new Porting\denyRequest();

            $request->portingId = $porting_id;

            $request->rejectionReason = $rejectionReason;

            $request->cause = $cause;

            try {

                $response = $this->client->deny($request);

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

    /**
     * @param $portingId
     * @return errorResponse
     */
    public function getPorting($portingId) {

        if($this->client) {

            // Make getPorting request
            $request = new Porting\getPortingRequest();
            $request->portingId = $portingId;

            try {

                $response = $this->client->getPorting($request);

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

    /**
     * @param $networkId
     * @return errorResponse
     */
    public function getOrderedPortings($networkId) {

        if($this->client) {

            // Make getOrderedPortings request
            $request = new Porting\getOrderedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getOrderedPortings($request);

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

    /**
     * @param $networkId
     * @return errorResponse
     */
    public function getApprovedPortings($networkId) {

        if($this->client) {

            // Make getApprovedPortings request
            $request = new Porting\getApprovedPortingsRequest();
            $request->networkId =$networkId;

            try {

                $response = $this->client->getApprovedPortings($request);

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

    /**
     * @param $networkId
     * @return errorResponse
     */
    public function getAcceptedPortings($networkId) {

        if($this->client) {

            // Make getAcceptedPortings request
            $request = new Porting\getAcceptedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getAcceptedPortings($request);

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

    /**
     * @param $networkId
     * @return errorResponse
     */
    public function getConfirmedPortings($networkId) {

        if($this->client) {

            // Make getConfirmedPortings request
            $request = new Porting\getConfirmedPortingsRequest();
            $request->networkId = $networkId;

            try {

                $response = $this->client->getConfirmedPortings($request);

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

    /**
     * @param $networkId
     * @param $count
     * @return errorResponse
     */
    public function getDeniedPortings($networkId, $count) {

        if($this->client) {

            // Make getDeniedPortings request
            $request = new Porting\getDeniedPortingsRequest();
            $request->networkId = $networkId;
            $request->count = $count;

            try {

                $response = $this->client->getDeniedPortings($request);

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

    /**
     * @param $networkId
     * @param $count
     * @return errorResponse
     */
    public function getRejectedPortings($networkId, $count) {

        if($this->client) {

            // Make getRejectedPortings request
            $request = new Porting\getRejectedPortingsRequest();
            $request->networkId = $networkId;
            $request->count = $count;

            try {

                $response = $this->client->getRejectedPortings($request);

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

    /**
     * TODO: OK
     * Make port order for given msisdn
     * @param $donorOperator
     * @param $portingMsisdn
     * @param $subscriberType
     * @param $rio
     * @param $physicalPersonFirstName
     * @param $physicalPersonLastName
     * @param $physicalPersonIdNumber
     * @param $legalPersonName
     * @param $legalPersonTin
     * @param $contactNumber
     * @param $portingDateTime
     * @param $temporalNumber
     * @param $contractId
     */
    public function orderPort($donorOperator, $portingMsisdn, $subscriberType, $rio, $physicalPersonFirstName,
                               $physicalPersonLastName, $physicalPersonIdNumber, $legalPersonName, $legalPersonTin,
                               $contactNumber, $temporalNumber, $contractId, $language) {

        // Construct subscriber info

        $response = [];

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

        $portingDateTime = getRecipientPortingDateTime();

        $orderResponse = $this->order($donorOperator, $portingDateTime, $portingMsisdn, $rio, $subscriberInfo);

        // Verify response

        if($orderResponse->success){

            $this->db->trans_start();

            // Fill in submission table with submission state ordered

            $submissionParams = array(
                'donorNetworkId' => $orderResponse->portingTransaction->donorNrn->networkId,
                'donorRoutingNumber' => $orderResponse->portingTransaction->donorNrn->routingNumber,
                'subscriberSubmissionDateTime' => date('c'),
                'portingDateTime' => $orderResponse->portingTransaction->portingDateTime,
                'rio' => $rio,
                'portingMSISDN' => $portingMsisdn,
                'contractId' => $contractId,
                'language' => $language,
                'temporalMSISDN' => $temporalNumber,
                'submissionState' => \PortingService\Porting\portingSubmissionStateType::ORDERED,
                'orderedDateTime' => date('c')
            );

            if($subscriberType == 0) {
                $submissionParams['physicalPersonFirstName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonFirstName;
                $submissionParams['physicalPersonLastName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonLastName;
                $submissionParams['physicalPersonIdNumber'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonIdNumber;
            }
            else{
                $submissionParams['legalPersonName'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonName;
                $submissionParams['legalPersonTin'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonTin;
                $submissionParams['contactNumber'] = $orderResponse->portingTransaction->subscriberInfo->contactNumber;
            }

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
                'startMSISDN' =>  $orderResponse->portingTransaction->numberRanges->numberRange->startNumber,
                'endMSISDN' =>  $orderResponse->portingTransaction->numberRanges->numberRange->endNumber,
                'cadbOrderDateTime' => $orderResponse->portingTransaction->cadbOrderDateTime,
                'lastChangeDateTime' => $orderResponse->portingTransaction->lastChangeDateTime,
                'portingState' => \PortingService\Porting\portingStateType::ORDERED,
                'contractId' => $contractId,
                'language' => $language,
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
                        'contractId' => $contractId,
                        'language' => $language,
                        'temporalMSISDN' => $temporalNumber,
                        'submissionState' => \PortingService\Porting\portingSubmissionStateType::STARTED
                    );

                    if($subscriberType == 0) {
                        $submissionParams['physicalPersonFirstName'] = $physicalPersonFirstName;
                        $submissionParams['physicalPersonLastName'] = $physicalPersonLastName;
                        $submissionParams['physicalPersonIdNumber'] = $physicalPersonIdNumber;
                    }
                    else{
                        $submissionParams['legalPersonName'] = $legalPersonName;
                        $submissionParams['legalPersonTin'] = $legalPersonTin;
                        $submissionParams['contactNumber'] = $contactNumber;
                    }

                    $this->Portingsubmission_model->add_portingsubmission($submissionParams);

                    $this->db->trans_complete();

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $response['success'] = false;
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
                default:
                    $emailService->adminErrorReport($fault, []);
                    $response['message'] = 'Fatal Error Encountered. Please contact Back Office';

            }

        }

        return $response;

    }

    /**
     * TODO: OK
     * Make port accept for given portingId
     * @param $portingId
     * @return array
     */
    public function acceptPort($portingId){

        $response = [];

        // Verify if porting currently in APPROVED state in DB
        $dbPort = $this->Porting_model->get_porting($portingId);

        if($dbPort){

            if($dbPort['portingState'] == Porting\portingStateType::APPROVED){

                // Make Accept Porting Operation

                $acceptResponse = $this->accept($portingId);

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

                    // Get porting Info for language
                    $portingInfo = $this->Porting_model->get_porting($portingId);

                    $language = $portingInfo['language'];

                    $subscriberMSISDN = $acceptResponse->portingTransaction->numberRanges->numberRange->startNumber;

                    $portingDateTime = $acceptResponse->portingTransaction->portingDateTime;

                    $day = date('d/m/Y', strtotime($portingDateTime));
                    $start_time = date('h:i:s', strtotime($portingDateTime));
                    $end_time = date('h:i:s', strtotime('+2 hours', strtotime($portingDateTime)));

                    if($acceptResponse->portingTransaction->recipientNrn->networkId == Operator::MTN_NETWORK_ID){
                        $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_MTN;
                    }else{
                        $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;
                    }

                    $smsResponse = SMS::OPD_Subscriber_Reminder($language, $subscriberMSISDN, $denom_OPR, $day, $start_time, $end_time);

                    if($smsResponse['success']){
                        // Insert Porting SMS Notification
                        $smsNotificationparams = array(
                            'portingId' => $portingId,
                            'smsType' => SMSType::OPD_PORTING_REMINDER,
                            'creationDateTime' => date('c'),
                            'status' => smsState::SENT,
                            'attemptCount' => 1,
                            'sendDateTime' => date('c')
                        );

                    }else{

                        $smsNotificationparams = array(
                            'portingId' => $portingId,
                            'smsType' => SMSType::OPD_PORTING_REMINDER,
                            'creationDateTime' => date('c'),
                            'status' => smsState::PENDING,
                            'attemptCount' => 1,
                        );
                    }

                    $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

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
                        default:
                            $emailService->adminErrorReport($fault, []);
                            $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    }


                }


            }else{

                $response['success'] = false;

                $response['message'] = 'Porting now in ' . $dbPort['portingState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }

        return $response;

    }

    /**
     * TODO: OK
     * Make port reject
     * @param $portingId
     * @param $rejectionReason
     * @param $cause
     * @return array
     */
    public function rejectPort($portingId, $rejectionReason, $cause){

        $response = [];

        // Verify if porting currently in APPROVED state in DB
        $dbPort = $this->Porting_model->get_porting($portingId);

        if($dbPort){

            if($dbPort['portingState'] == Porting\portingStateType::APPROVED){

                if($rejectionReason == Porting\rejectionReasonType::OUTSTANDING_OBLIGATIONS_TO_DONOR ||
                    $rejectionReason == Porting\rejectionReasonType::SUBSCRIBER_CANCELLED_PORTING ||
                    $rejectionReason == Porting\rejectionReasonType::SUBSCRIBER_CHANGED_NUMBER){

                    // Make Reject Porting Operation

                    $rejectResponse = $this->reject($portingId, $rejectionReason, $cause);

                    // Verify response

                    if($rejectResponse->success){

                        $this->db->trans_start();

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
                            $emailService->adminErrorReport('PORTING_REJECTED_BUT_DB_FILLED_INCOMPLETE', []);

                        }else {

                        }

                        $response['message'] = 'Porting has been REJECTED successfully!';

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

                $response['message'] = 'Porting now in ' . $dbPort['portingState'] . ' state. Request not sent.';

            }

        }else{

            $response['success'] = false;

            $response['message'] = 'No process found in LDB with given Id';

        }


        return $response;

    }

}