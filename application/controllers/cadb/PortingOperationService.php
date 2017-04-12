<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Porting.php";
require_once "Fault.php";
require_once "ProvisionNotification.php";
require_once APPPATH . "controllers/email/EmailService.php";
require_once APPPATH . "controllers/bscs/BscsOperationService.php";
require_once APPPATH . "controllers/sms/SMS.php";

use PortingService\Porting as Porting;
use \ProvisionService\ProvisionNotification\provisionStateType as provisionStateType;

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
class PortingOperationService  {

    // Declare client
    private $client = null;

    private $db = null;
    private $Porting_model = null;
    private $FileLog_model = null;
    private $ProcessNumber_model = null;
    private $Portingstateevolution_model = null;
    private $Portingsmsnotification_model = null;
    private $Portingdenyrejectionabandon_model = null;

    public function __construct()
    {

        $CI =& get_instance();

        $this->db = $CI->db;

        $CI->load->model('Porting_model');
        $CI->load->model('FileLog_model');
        $CI->load->model('ProcessNumber_model');
        $CI->load->model('Portingstateevolution_model');
        $CI->load->model('Portingsmsnotification_model');
        $CI->load->model('Portingdenyrejectionabandon_model');

        $this->Porting_model = $CI->Porting_model;
        $this->FileLog_model = $CI->FileLog_model;
        $this->ProcessNumber_model = $CI->ProcessNumber_model;
        $this->Portingstateevolution_model = $CI->Portingstateevolution_model;
        $this->Portingsmsnotification_model = $CI->Portingsmsnotification_model;
        $this->Portingdenyrejectionabandon_model = $CI->Portingdenyrejectionabandon_model;

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        libxml_disable_entity_loader(false);

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/PortingOperationService.wsdl', array(
            "trace" => true,
            'stream_context' => stream_context_create(array(
                'http' => array(
                    'header' => 'Authorization: Bearer ' . Auth::CADB_AUTH_BEARER
                ),
            )),
        ));

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

    }

    /**
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
                    // Nexttel
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

                $this->logRequestResponse('order');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('order');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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

                $this->logRequestResponse('approve');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('approve');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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

                $this->logRequestResponse('accept');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('accept');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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

                $this->logRequestResponse('confirm');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('confirm');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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

                $this->logRequestResponse('reject');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('reject');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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
     * @param $denialReason string reason of denial
     * @param $cause string description of the denial
     * @return errorResponse
     */
    public function deny($porting_id, $denialReason, $cause) {

        if($this->client) {

            // Make deny request
            $request = new Porting\denyRequest();

            $request->portingId = $porting_id;

            $request->denialReason = $denialReason;

            $request->cause = $cause;

            try {

                $response = $this->client->deny($request);

                $this->logRequestResponse('deny');

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $this->logRequestResponse('deny');

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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

                $response->message = $e->detail->$fault->message;

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
    public function orderPort($donorOperator, $portingMsisdn, $subscriberType, $rio, $documentType, $physicalPersonFirstName,
                               $physicalPersonLastName, $physicalPersonIdNumber, $legalPersonName, $legalPersonTin,
                               $contactNumber, $temporalNumber, $contractId, $language, $portingDateTime, $userId, $source = portingSource::WEB) {

        // Construct subscriber info

        $response = [];

        $subscriberInfo = new \PortingService\Porting\subscriberInfoType();

        $subscriberInfo->contactNumber = $contactNumber;

        if($subscriberType == 0){
            $subscriberInfo->physicalPersonFirstName = $physicalPersonFirstName;
            $subscriberInfo->physicalPersonLastName = $physicalPersonLastName;
            $subscriberInfo->physicalPersonIdNumber = $physicalPersonIdNumber;
        }else{
            $subscriberInfo->legalPersonName = $legalPersonName;
            $subscriberInfo->legalPersonTin = $legalPersonTin;
        }

        // Strips only null values
        $subscriberInfo = (object) array_filter((array) $subscriberInfo, function ($val){
           return !is_null($val);
        });

        // Make Order Porting Operation

        //$portingDateTime = getRecipientPortingDateTime();
        $portingDateTime = date_create($portingDateTime);
        $portingDateTime = date_format($portingDateTime, 'c');

        $orderResponse = $this->order($donorOperator, $portingDateTime, $portingMsisdn, $rio, $subscriberInfo);

        // Verify response

        if($orderResponse->success){

            $this->db->trans_start();

            $portingId = $orderResponse->portingTransaction->portingId;

            // Fill in porting table with state ordered

            $portingParams = array(
                'portingId' => $portingId,
                'recipientNetworkId' => $orderResponse->portingTransaction->recipientNrn->networkId,
                'recipientRoutingNumber' => $orderResponse->portingTransaction->recipientNrn->routingNumber,
                'donorNetworkId' => $orderResponse->portingTransaction->donorNrn->networkId,
                'donorRoutingNumber' => $orderResponse->portingTransaction->donorNrn->routingNumber,
                'recipientSubmissionDateTime' => $orderResponse->portingTransaction->recipientSubmissionDateTime,
                'portingDateTime' => $orderResponse->portingTransaction->portingDateTime,
                'rio' =>  $orderResponse->portingTransaction->rio,
                'cadbOrderDateTime' => $orderResponse->portingTransaction->cadbOrderDateTime,
                'lastChangeDateTime' => $orderResponse->portingTransaction->lastChangeDateTime,
                'portingState' => \PortingService\Porting\portingStateType::ORDERED,
                'language' => $language,
                'contactNumber' => $orderResponse->portingTransaction->subscriberInfo->contactNumber,
                'portingNotificationMailSendStatus' => smsState::CLOSED,
                'portingNotificationMailSendDateTime' => date('c'),
                'source' => $source
            );

            if($subscriberType == 0) {
                $portingParams['physicalPersonFirstName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonFirstName;
                $portingParams['physicalPersonLastName'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonLastName;
                $portingParams['physicalPersonIdNumber'] = $orderResponse->portingTransaction->subscriberInfo->physicalPersonIdNumber;
            }
            else{
                $portingParams['legalPersonName'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonName;
                $portingParams['legalPersonTin'] = $orderResponse->portingTransaction->subscriberInfo->legalPersonTin;
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

            // Fill in Porting process numbers

            $portingNumbers = $this->getPortingNumbers($orderResponse);

            $processNumberParams = [];

            foreach ($portingNumbers as $portingNumber){
                $processNumberParams[] = array(
                    'processId' => $portingId,
                    'msisdn' => $portingNumber,
                    'numberState' => provisionStateType::STARTED,
                    'pLastChangeDateTime' => date('Y-m-d\TH:i:s'),
                    'processType' => processType::PORTING,
                    'contractId' => $contractId,
                    'temporalMsisdn' => $temporalNumber
                );
            }

            $this->db->insert_batch('processnumber', $processNumberParams);

            $response['success'] = true;

            if ($this->db->trans_status() === FALSE) {

                $error = $this->db->error();
                $this->fileLogAction($error['code'], 'PortingOperationService', $error['message']);

                $emailService = new EmailService();
                $emailService->adminErrorReport('PORTING ORDERED BUT DB FILLED INCOMPLETE', $portingParams, processType::PORTING);

            }else {

            }

            $this->db->trans_complete();

            logAction($userId, "Porting [$portingId] Ordered Successfully");

            $response['message'] = 'Porting has been ORDERED successfully!';

        }

        else{

            $fault = $orderResponse->error;

            $emailService = new EmailService();

            $response['success'] = false;

            switch ($fault) {

                // Terminal Processes
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
                case Fault::INVALID_OPERATOR_FAULT:
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
                    'msisdn' =>  [$portingMsisdn],
                    'lastChangeDateTime' => date('c'),
                    'portingState' => 'NONE'
                );

                $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);
                $response['message'] = 'Fatal Error Encountered. Please contact Back Office';

            }

            logAction($userId, "Porting Order Failed with [$fault] Fault");

        }

        return $response;

    }

    /**
     * Make port accept for given portingId
     * @param $portingId
     * @return array
     */
    public function acceptPort($portingId, $userId){

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

                    $portingNumbers = $this->getPortingNumbers($acceptResponse);

                    $sendMsisdn = $acceptResponse->portingTransaction->subscriberInfo->contactNumber;
                    $subscriberMSISDN = implode(', ', $portingNumbers);

                    if(strlen($subscriberMSISDN) > 26){
                        $subscriberMSISDN = substr($subscriberMSISDN, 0, 27) . ' ...';
                    }

                    $portingDateTime = $acceptResponse->portingTransaction->portingDateTime;

                    $day = date('d/m/Y', strtotime($portingDateTime));
                    $start_time = date('H:i:s', strtotime($portingDateTime));
                    $end_time = date('H:i:s', strtotime('+2 hours', strtotime($portingDateTime)));

                    if($acceptResponse->portingTransaction->recipientNrn->networkId == Operator::MTN_NETWORK_ID){
                        $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_MTN;
                    }else{
                        $denom_OPR = SMS::$DENOMINATION_COMMERCIALE_NEXTTEL;
                    }

                    $smsResponse = SMS::OPD_Subscriber_Reminder($language, $subscriberMSISDN, $denom_OPR, $day, $start_time, $end_time, $sendMsisdn);

                    if($smsResponse['success']){
                        // Insert Porting SMS Notification
                        $smsNotificationparams = array(
                            'portingId' => $portingId,
                            'smsType' => SMSType::OPD_PORTING_REMINDER,
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
                            'smsType' => SMSType::OPD_PORTING_REMINDER,
                            'message' => $smsResponse['message'],
                            'msisdn' => $smsResponse['msisdn'],
                            'creationDateTime' => date('c'),
                            'status' => smsState::PENDING,
                            'attemptCount' => 1,
                        );
                    }

                    $this->Portingsmsnotification_model->add_portingsmsnotification($smsNotificationparams);

                    // Update Number state
                    $portingNumberParams = array(
                        'pLastChangeDateTime' => date('c'),
                        'numberState' => \PortingService\Porting\portingStateType::ACCEPTED
                    );

                    $this->ProcessNumber_model->update_processnumber_all($portingId, $portingNumberParams);

                    $response['success'] = true;

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        $this->fileLogAction($error['code'], 'PortingOperationService', $error['message']);

                        $portingParams = $this->Porting_model->get_porting($portingId);

                        $emailService = new EmailService();
                        $emailService->adminErrorReport('PORTING ACCEPTED BUT DB FILLED INCOMPLETE', $portingParams, processType::PORTING);

                    }

                    $this->db->trans_complete();

                    logAction($userId, "Porting [$portingId] Accepted Successfully");

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

                        $portingParams = $this->Porting_model->get_porting($portingId);

                        $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);

                        $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    }

                    logAction($userId, "Porting [$portingId] Acceptance Failed with [$fault] Fault");

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
     * Make port reject
     * @param $portingId
     * @param $rejectionReason
     * @param $cause
     * @return array
     */
    public function rejectPort($portingId, $rejectionReason, $cause, $userId){

        $response = [];

        // Verify if porting currently in APPROVED state in DB
        $dbPort = $this->Porting_model->get_porting($portingId);

        if($dbPort){

            if($dbPort['portingState'] == Porting\portingStateType::APPROVED){

                if($rejectionReason == Porting\rejectionReasonType::SUBSCRIBER_CANCELLED_PORTING ||
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

                        // Update number state

                        $portingNumbers = $this->getPortingNumbers($rejectResponse);

                        foreach ($portingNumbers as $portingNumber){

                            // Update Porting Number table

                            $portingNumberParams = array(
                                'pLastChangeDateTime' => date('c'),
                                'numberState' => provisionStateType::TERMINATED,
                                'terminationReason' => $rejectionReason
                            );

                            $this->ProcessNumber_model->update_processnumber($portingId, $portingNumber, $portingNumberParams);

                        }

                        $response['success'] = true;

                        if ($this->db->trans_status() === FALSE) {

                            $error = $this->db->error();
                            $this->fileLogAction($error['code'], 'PortingOperationService', $error['message']);

                            $portingParams = $this->Porting_model->get_porting($portingId);

                            $emailService = new EmailService();
                            $emailService->adminErrorReport('PORTING REJECTED BUT DB FILLED INCOMPLETE', $portingParams, processType::PORTING);

                        }else {

                        }

                        $this->db->trans_complete();

                        logAction($userId, "Porting [$portingId] Rejected Successfully");

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

                                $portingParams = $this->Porting_model->get_porting($portingId);

                                $emailService->adminErrorReport($fault, $portingParams, processType::PORTING);

                                $response['message'] = 'Fatal Error Encountered. Please contact Administrator';

                        }

                        logAction($userId, "Porting [$portingId] Rejection Failed with [$fault] Fault");

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

    /**
     * Search porting with msisdn
     * @param $msisdn
     * @return array
     */
    public function searchPort($msisdn, $userId){

        $response = [];

        $response['success'] = true;

        $response['data'] = $this->Porting_model->search_porting($msisdn);

        return $response;

    }

    /**
     * API to retrieve detail on porting
     */
    public function getCADBPorting($portingId){

        $response = [];

        $getResponse = $this->getPorting($portingId);

        // Verify response

        if($getResponse->success){

            $response['success'] = true;

            $tmpData = $getResponse->portingTransaction;

            $subscriberType = getSubscriberType($tmpData->rio);

            $data = array();

            $data['portingId'] = $tmpData->portingId;
            $data['recipientNetworkId'] = $tmpData->recipientNrn->networkId;
            $data['recipientRoutingNumber'] = $tmpData->recipientNrn->routingNumber;
            $data['donorNetworkId'] = $tmpData->donorNrn->networkId;
            $data['donorRoutingNumber'] = $tmpData->donorNrn->routingNumber;
            $data['recipientSubmissionDateTime'] = $tmpData->recipientSubmissionDateTime;
            $data['portingDateTime'] = $tmpData->portingDateTime;
            $data['cadbOrderedDateTime'] = $tmpData->cadbOrderDateTime;
            $data['lastChangeDateTime'] = $tmpData->lastChangeDateTime;
            $data['portingState'] = $tmpData->portingState;
            $data['rio'] = $tmpData->rio;
            $data['msisdn'] = $this->getPortingNumbers($getResponse);
            $data['contactNumber'] = $tmpData->subscriberInfo->contactNumber;

            if($subscriberType == 0) {

                $data['physicalPersonFirstName'] = $tmpData->subscriberInfo->physicalPersonFirstName;
                $data['physicalPersonLastName'] = $tmpData->subscriberInfo->physicalPersonLastName;
                $data['physicalPersonIdNumber'] = $tmpData->subscriberInfo->physicalPersonIdNumber;

                $data['legalPersonName'] = null;
                $data['legalPersonTin'] = null;

            }
            else{

                $data['legalPersonName'] = $tmpData->subscriberInfo->legalPersonName;
                $data['legalPersonTin'] = $tmpData->subscriberInfo->legalPersonTin;

                $data['physicalPersonFirstName'] = null;
                $data['physicalPersonLastName'] = null;
                $data['physicalPersonIdNumber'] = null;

            }

            $response['data'] = $data;

        }

        else{

            $fault = $getResponse->error;

            $response['success'] = false;

            switch ($fault) {
                // Terminal Processes
                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::PORTING_ACTION_NOT_AVAILABLE:
                case Fault::INVALID_PORTING_ID:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    $response['message'] = 'Error from CADB';

            }

        }

        return $response;

    }

    /**
     * TODO: getCADBPortings
     * API to retrieve all portings from CADB
     */
    public function getCADBPortings(){

        $response = [];

        $response['success'] = true;
        $response['data'] = [];

        // Load ORDERED Portings

        $orderedResponse = $this->getOrderedPortings(Operator::ORANGE_NETWORK_ID);

        if($orderedResponse->success){

            if(isset($orderedResponse->portingTransaction)){

                if(is_array($orderedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $orderedResponse->portingTransaction);

                }else{

                    $response['data'][] = $orderedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $orderedResponse->error;

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_ORDERED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load APPROVED Portings

        $approvedResponse = $this->getApprovedPortings(Operator::ORANGE_NETWORK_ID);

        if($approvedResponse->success){

            if(isset($approvedResponse->portingTransaction)){

                if(is_array($approvedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $approvedResponse->portingTransaction);

                }else{

                    $response['data'][] = $approvedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $approvedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_APPROVED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load ACCEPTED Portings

        $acceptedResponse = $this->getAcceptedPortings(Operator::ORANGE_NETWORK_ID);

        if($acceptedResponse->success){

            if(isset($acceptedResponse->portingTransaction)){

                if(is_array($acceptedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $acceptedResponse->portingTransaction);

                }else{

                    $response['data'][] = $acceptedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $acceptedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_ACCEPTED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load CONFIRMED Portings

        $confirmedResponse = $this->getConfirmedPortings(Operator::ORANGE_NETWORK_ID);

        if($confirmedResponse->success){

            if(isset($confirmedResponse->portingTransaction)){

                if(is_array($confirmedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $confirmedResponse->portingTransaction);

                }else{

                    $response['data'][] = $confirmedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $confirmedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_CONFIRMED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load DENIED Portings

        $deniedResponse = $this->getDeniedPortings(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

        if($deniedResponse->success){

            if(isset($deniedResponse->portingTransaction)){

                if(is_array($deniedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $deniedResponse->portingTransaction);

                }else{

                    $response['data'][] = $deniedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $deniedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                case Fault::COUNT_OVER_MAX_COUNT_LIMIT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_DENIED_PORTINGS_FROM_CADB", []);
            }

        }

        // Load REJECTED Portings

        $rejectedResponse = $this->getRejectedPortings(Operator::ORANGE_NETWORK_ID, params::DENIED_REJECTED_MAX_COUNT);

        if($rejectedResponse->success){

            if(isset($rejectedResponse->portingTransaction)){

                if(is_array($rejectedResponse->portingTransaction)){

                    $response['data'] = array_merge($response['data'], $rejectedResponse->portingTransaction);

                }else{

                    $response['data'][] = $rejectedResponse->portingTransaction;

                }

            }

        }
        else{

            $fault = $rejectedResponse->error;

            $emailService = new EmailService();

            switch ($fault) {

                case Fault::INVALID_OPERATOR_FAULT:
                case Fault::ACTION_NOT_AUTHORIZED:
                case Fault::INVALID_REQUEST_FORMAT:
                case Fault::COUNT_OVER_MAX_COUNT_LIMIT:
                default:
                    //$emailService->adminErrorReport("ERROR_RETRIEVING_REJECTED_PORTINGS_FROM_CADB", []);
            }

        }

        $tmpData = $response['data'];

        $response['data'] = [];

        foreach ($tmpData as $tmpDatum){

            $res = new stdClass();
            $res->portingTransaction = $tmpDatum;

            $data = array();

            $data['portingId'] = $tmpDatum->portingId;
            $data['recipientNetworkId'] = $tmpDatum->recipientNrn->networkId;
            $data['recipientRoutingNumber'] = $tmpDatum->recipientNrn->routingNumber;
            $data['donorNetworkId'] = $tmpDatum->donorNrn->networkId;
            $data['donorRoutingNumber'] = $tmpDatum->donorNrn->routingNumber;
            $data['recipientSubmissionDateTime'] = $tmpDatum->recipientSubmissionDateTime;
            $data['portingDateTime'] = $tmpDatum->portingDateTime;
            $data['cadbOrderedDateTime'] = $tmpDatum->cadbOrderDateTime;
            $data['lastChangeDateTime'] = $tmpDatum->lastChangeDateTime;
            $data['portingState'] = $tmpDatum->portingState;
            $data['rio'] = $tmpDatum->rio;
            $data['msisdn'] = $this->getPortingNumbers($res);
            $data['contactNumber'] = $tmpDatum->subscriberInfo->contactNumber;

            if(isset($tmpDatum->subscriberInfo->physicalPersonFirstName)) {

                $data['physicalPersonFirstName'] = $tmpDatum->subscriberInfo->physicalPersonFirstName;
                $data['physicalPersonLastName'] = $tmpDatum->subscriberInfo->physicalPersonLastName;
                $data['physicalPersonIdNumber'] = $tmpDatum->subscriberInfo->physicalPersonIdNumber;

                $data['legalPersonName'] = null;
                $data['legalPersonTin'] = null;

            }
            else{

                $data['legalPersonName'] = $tmpDatum->subscriberInfo->legalPersonName;
                $data['legalPersonTin'] = $tmpDatum->subscriberInfo->legalPersonTin;

                $data['physicalPersonFirstName'] = null;
                $data['physicalPersonLastName'] = null;
                $data['physicalPersonIdNumber'] = null;

            }

            array_push($response['data'], $data);

        }

        return $response;

    }

    private function logRequestResponse($action){
        $this->fileLogAction('', 'PortingOperationService', $action . ' Request:: ' . $this->client->__getLastRequest());
        $this->fileLogAction('', 'PortingOperationService', $action . ' Response:: ' . $this->client->__getLastResponse());
    }

    /**
     * Returns porting MSISDN in process
     * @param $request
     * @return array
     */
    private function getPortingNumbers($request){

        $numbers = [];

        if(is_array($request->portingTransaction->numberRanges->numberRange)){

            foreach ($request->portingTransaction->numberRanges->numberRange as $numberRange){

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

            $startMSISDN = $request->portingTransaction->numberRanges->numberRange->startNumber;
            $endMSISDN = $request->portingTransaction->numberRanges->numberRange->endNumber;

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