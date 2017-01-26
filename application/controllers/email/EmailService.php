<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 9:31 AM
 */

require_once APPPATH . "third_party/PHPMailer/PHPMailerAutoload.php";
require_once APPPATH . "controllers/cadb/Common.php";


/**
 * Class Email
 * Base class for all email related functionalities
 */
class EmailService {

    // Declare CI instance
    private $CI = null;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function test(){


        // Load template
        //$template = file_get_contents(__DIR__ . '/templates/process_error_template.html');
        $template = file_get_contents(__DIR__ . '/templates/porting-confirm-template.html');

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');
        $cc = array();
        $subject = 'Great';

        $this->send_mail($to, $cc, $subject, $template);

    }

    /**
     * Reports Error to Admin via mail
     * @param $errorCode
     * @param $params
     */
    public function adminErrorReport($errorCode, $params, $processType){

        $subject = '';
        $message = '';

        if($processType == processType::PORTING){

            $subject = 'Error with ' . $params['portingId'];

            $template = file_get_contents(__DIR__ . '/templates/porting-error-template.html');

            // Set Error Text
            $template = str_replace('[errorText]', $errorCode, $template);

            // Set PortingId
            $template = str_replace('[portingId]', $params['portingId'], $template);

            // Set Donor Network

            $donorNetwork = '';

            if($params['donorNetworkId'] == Operator::MTN_NETWORK_ID){
                $donorNetwork = Operator::MTN_OPERATOR_NAME;
            }elseif ($params['donorNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
                $donorNetwork = Operator::NEXTTEL_OPERATOR_NAME;
            }elseif ($params['donorNetworkId'] == Operator::ORANGE_NETWORK_ID){
                $donorNetwork = Operator::ORANGE_OPERATOR_NAME;
            }

            $recipientNetwork = '';

            if($params['recipientNetworkId'] == Operator::MTN_NETWORK_ID){
                $recipientNetwork = Operator::MTN_OPERATOR_NAME;
            }elseif ($params['recipientNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
                $recipientNetwork = Operator::NEXTTEL_OPERATOR_NAME;
            }elseif ($params['recipientNetworkId'] == Operator::ORANGE_NETWORK_ID){
                $recipientNetwork = Operator::ORANGE_OPERATOR_NAME;
            }

            $template = str_replace('[donor_network]', $donorNetwork, $template);

            $template = str_replace('[recipient_network]', $recipientNetwork, $template);

            // Set Porting MSISDN
            $template = str_replace('[portingMSISDN]', $params['startMSISDN'], $template);

            // Set RIO
            $template = str_replace('[rio]', $params['rio'], $template, $count);

            // Set Submission Date
            $submissionDateTime = $params['recipientSubmissionDateTime'];
            if($submissionDateTime != ''){
                $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['recipientSubmissionDateTime']));
            }else{
                $submissionDateTime = '';
            }
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            // Set Last Change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $template = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template);

            // Set Porting State
            $message = str_replace('[portingState]', $params['portingState'], $template);

        }
        elseif($processType == processType::ROLLBACK){

            $subject = 'Error with ' . $params['rollbackId'];

            $template = file_get_contents(__DIR__ . '/templates/rollback-error-template.html');

            // Set Error Text
            $template = str_replace('[errorText]', $errorCode, $template);

            // Set rollbackId
            $template = str_replace('[rollbackId]', $params['rollbackId'], $template);

            // Set Donor Network

            $donorNetwork = '';

            if($params['donorNetworkId'] == Operator::MTN_NETWORK_ID){
                $donorNetwork = Operator::MTN_OPERATOR_NAME;
            }elseif ($params['donorNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
                $donorNetwork = Operator::NEXTTEL_OPERATOR_NAME;
            }elseif ($params['donorNetworkId'] == Operator::ORANGE_NETWORK_ID){
                $donorNetwork = Operator::ORANGE_OPERATOR_NAME;
            }

            $template = str_replace('[owner_network]', $donorNetwork, $template);

            // Set MSISDN
            $template = str_replace('[portingMSISDN]', $params['startMSISDN'], $template);

            // Set RIO
            $template = str_replace('[rio]', $params['rio'], $template);

            // Set Submission Date
            $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['donorSubmissionDateTime']));
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            // Set last change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $template = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template);

            // Set rollbackState
            $message = str_replace('[rollbackState]', $params['rollbackState'], $template);

        }
        elseif($processType == processType::_RETURN){

            $subject = 'Error with ' . $params['returnId'];

            $template = file_get_contents(__DIR__ . '/templates/return-error-template.html');

            // Set Error
            $template = str_replace('[errorText]', $errorCode, $template);

            // Set ReturnId
            $template = str_replace('[returnId]', $params['returnId'], $template);

            // Set Return MSISDN
            $template = str_replace('[returnMSISDN]', $params['returnMSISDN'], $template);

            // Set Return state
            $template = str_replace('[returnState]', $params['returnNumberState'], $template);

            // Set Owner Network

            $ownerNetwork = '';

            if($params['ownerNetworkId'] == Operator::MTN_NETWORK_ID){
                $ownerNetwork = Operator::MTN_OPERATOR_NAME;
            }elseif ($params['ownerNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
                $ownerNetwork = Operator::NEXTTEL_OPERATOR_NAME;
            }elseif ($params['ownerNetworkId'] == Operator::ORANGE_NETWORK_ID){
                $ownerNetwork = Operator::ORANGE_OPERATOR_NAME;
            }

            $message = str_replace('[ownerNetwork]', $ownerNetwork, $template);

        }
        else{

            $subject = 'Error with ' . $params['errorReportId'];

            $template = file_get_contents(__DIR__ . '/templates/error-template.html');

            // Set Error Text
            $template = str_replace('[errorText]', $errorCode, $template);

            // Set errorId
            $template = str_replace('[errorId]', $params['errorReportId'], $template);

            // Set problem
            $template = str_replace('[problem]', $params['problem'], $template);

            // Set Reporter Network

            $reporterNetwork = '';

            if($params['reporterNetworkId'] == Operator::MTN_NETWORK_ID){
                $reporterNetwork = Operator::MTN_OPERATOR_NAME;
            }elseif ($params['reporterNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
                $reporterNetwork = Operator::NEXTTEL_OPERATOR_NAME;
            }elseif ($params['reporterNetworkId'] == Operator::ORANGE_NETWORK_ID){
                $reporterNetwork = Operator::ORANGE_OPERATOR_NAME;
            }

            $template = str_replace('[reporterNetwork]', $reporterNetwork, $template);

            // Set CADB Number
            $template = str_replace('[cadbNumber]', $params['cadbNumber'], $template);

            // Set Submission Date
            $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['submissionDateTime']));
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            // Set Process Type
            $message = str_replace('[processType]', $params['processType'], $template);

        }

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');
        $cc = array();

        $this->send_mail($to, $cc, $subject, $message);

    }

    public function adminSubmissionReport($faultcode, $params, $processType){

    }

    public function adminConfirmReport($confirmCode, $params, $processType){

        $subject = '';
        $message = '';

        if($processType == processType::PORTING){

            $subject = 'Confirm PortID: ' . $params['portingId'];

            $template = file_get_contents(__DIR__ . '/templates/porting-confirm-template.html');

            // Set confirmText
            $template = str_replace('[confirmText]', $subject, $template);

            // Set PortingId
            $template = str_replace('[portingId]', $params['portingId'], $template);

            // Set Porting MSISDN
            $template = str_replace('[portingMSISDN]', $params['startMSISDN'], $template);

            // Set RIO
            $template = str_replace('[rio]', $params['rio'], $template, $count);

            // Set Submission Date
            $submissionDateTime = $params['recipientSubmissionDateTime'];
            if($submissionDateTime != ''){
                $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['recipientSubmissionDateTime']));
            }else{
                $submissionDateTime = '';
            }
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            $template = str_replace('[finalState]', $params['portingState'], $template);

            // Set last change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $message = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template);

        }
        elseif($processType == processType::ROLLBACK){

            $subject = 'Confirm RollbackID: ' . $params['rollbackId'];

            $template = file_get_contents(__DIR__ . '/templates/rollback-confirm-template.html');

            // Set confirmText
            $template = str_replace('[confirmText]', $subject, $template);

            // Set rollbackId
            $template = str_replace('[rollbackId]', $params['rollbackId'], $template);

            // Set Porting MSISDN
            $template = str_replace('[portingMSISDN]', $params['startMSISDN'], $template);

            // Set RIO
            $template = str_replace('[rio]', $params['rio'], $template, $count);

            // Set Submission Date
            $submissionDateTime = $params['donorSubmissionDateTime'];
            if($submissionDateTime != ''){
                $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['donorSubmissionDateTime']));
            }else{
                $submissionDateTime = '';
            }
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            $template = str_replace('[finalState]', $params['rollbackState'], $template);

            // Set last change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $message = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template);

        }
        elseif($processType == processType::_RETURN){

            $subject = 'Confirm ReturnID: ' . $params['returnId'];

            $template = file_get_contents(__DIR__ . '/templates/return-confirm-template.html');

            // Set confirmText
            $template = str_replace('[confirmText]', $subject, $template);

            // Set returnId
            $template = str_replace('[returnId]', $params['returnId'], $template);

            // Set return MSISDN
            $template = str_replace('[returnMSISDN]', $params['returnMSISDN'], $template);

            // Set Submission Date
            $submissionDateTime = $params['donorSubmissionDateTime'];
            if($submissionDateTime != ''){
                $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['donorSubmissionDateTime']));
            }else{
                $submissionDateTime = '';
            }
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

            $template = str_replace('[finalState]', $params['rollbackState'], $template);

            // Set last change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $message = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template);

        }

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');
        $cc = array();

        $this->send_mail($to, $cc, $subject, $message);

    }

    public function backOfficePortingAcceptReject($params){

        $subject = 'Accept / Reject PortID: ' . $params['portingId'];

        if($params['physicalPersonFirstName'] != null){

            $template = file_get_contents(__DIR__ . '/templates/indiv-porting-acceptance-rejection-template.html');

            // Set first name
            $template = str_replace('[firstName]', $params['physicalPersonFirstName'], $template);

            // Set last name
            $template = str_replace('[lastName]', $params['physicalPersonLastName'], $template);

            // Set ID number
            $template = str_replace('[idNumber]', $params['physicalPersonIdNumber'], $template);

        }else{

            $template = file_get_contents(__DIR__ . '/templates/indiv-enterprise-porting-acceptance-rejection-template.html');

            // Set legal name
            $template = str_replace('[legalName]', $params['legalPersonName'], $template);

            // Set TIN
            $template = str_replace('[tin]', $params['legalPersonTin'], $template);

            // Set ID number
            $template = str_replace('[contactNumber]', $params['contactNumber'], $template);

        }

        // Set PortingId
        $template = str_replace('[portingId]', $params['portingId'], $template);

        // Set Recipient Network

        $recipientNetwork = '';

        if($params['recipientNetworkId'] == Operator::MTN_NETWORK_ID){
            $recipientNetwork = Operator::MTN_OPERATOR_NAME;
        }elseif ($params['recipientNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
            $recipientNetwork = Operator::NEXTTEL_OPERATOR_NAME;
        }elseif ($params['recipientNetworkId'] == Operator::ORANGE_NETWORK_ID){
            $recipientNetwork = Operator::ORANGE_OPERATOR_NAME;
        }

        $template = str_replace('[recipient_network]', $recipientNetwork, $template);

        // Set Porting MSISDN
        $template = str_replace('[portingMSISDN]', $params['startMSISDN'], $template);

        // Set RIO
        $template = str_replace('[rio]', $params['rio'], $template, $count);

        // Set Submission Date
        $submissionDateTime = $params['recipientSubmissionDateTime'];
        if($submissionDateTime != ''){
            $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['recipientSubmissionDateTime']));
        }else{
            $submissionDateTime = '';
        }
        $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

        // Set Porting Date
        $portingDateTime = date('l, M d Y, H:i:s', strtotime($params['portingDateTime']));
        $message = str_replace('[portingDateTime]', $portingDateTime, $template);

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');
        $cc = array();

        return $this->send_mail($to, $cc, $subject, $message);
    }

    public function backOfficeRollbackAcceptReject($params){

        $subject = 'Accept / Reject RollbackID: ' . $params['rollbackId'];

        if($params['physicalPersonFirstName'] != null){

            $template = file_get_contents(__DIR__ . '/templates/indiv-rollback-acceptance-rejection-template.html');

            // Set first name
            $template = str_replace('[firstName]', $params['physicalPersonFirstName'], $template);

            // Set last name
            $template = str_replace('[lastName]', $params['physicalPersonLastName'], $template);

            // Set ID number
            $template = str_replace('[idNumber]', $params['physicalPersonIdNumber'], $template);

        }else{

            $template = file_get_contents(__DIR__ . '/templates/indiv-enterprise-rollback-acceptance-rejection-template.html');

            // Set legal name
            $template = str_replace('[legalName]', $params['legalPersonName'], $template);

            // Set TIN
            $template = str_replace('[tin]', $params['legalPersonTin'], $template);

            // Set ID number
            $template = str_replace('[contactNumber]', $params['contactNumber'], $template);

        }

        // Set rollbackId
        $template = str_replace('[rollbackId]', $params['rollbackId'], $template);

        // Set Donor Network

        $donorNetwork = '';

        if($params['donorNetworkId'] == Operator::MTN_NETWORK_ID){
            $donorNetwork = Operator::MTN_OPERATOR_NAME;
        }elseif ($params['donorNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
            $donorNetwork = Operator::NEXTTEL_OPERATOR_NAME;
        }elseif ($params['donorNetworkId'] == Operator::ORANGE_NETWORK_ID){
            $donorNetwork = Operator::ORANGE_OPERATOR_NAME;
        }

        $template = str_replace('[donor_network]', $donorNetwork, $template);

        // Set MSISDN
        $template = str_replace('[rollbackMSISDN]', $params['startMSISDN'], $template);

        // Set RIO
        $template = str_replace('[rio]', $params['rio'], $template);

        // Set Submission Date
        $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['donorSubmissionDateTime']));
        $template = str_replace('[submissionDateTime]', $submissionDateTime, $template);

        // Set preferred rollback Date
        $preferredRollbackDateTime = date('l, M d Y, H:i:s', strtotime($params['preferredRollbackDateTime']));
        $message = str_replace('[rollbackDateTime]', $preferredRollbackDateTime, $template);

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');

        $cc = array();

        return $this->send_mail($to, $cc, $subject, $message);

    }

    public function backOfficeReturnAcceptReject($params){

        $subject = 'Accept / Reject ReturnID: ' . $params['returnId'];

        $template = file_get_contents(__DIR__ . '/templates/indiv-return-acceptance-rejection-template.html');

        // Set ReturnId
        $template = str_replace('[returnId]', $params['returnId'], $template);

        // Set Return MSISDN
        $template = str_replace('[returnMSISDN]', $params['returnMSISDN'], $template);

        // Set Owner Network

        $ownerNetwork = '';

        if($params['ownerNetworkId'] == Operator::MTN_NETWORK_ID){
            $ownerNetwork = Operator::MTN_OPERATOR_NAME;
        }elseif ($params['ownerNetworkId'] == Operator::NEXTTEL_NETWORK_ID){
            $ownerNetwork = Operator::NEXTTEL_OPERATOR_NAME;
        }elseif ($params['ownerNetworkId'] == Operator::ORANGE_NETWORK_ID){
            $ownerNetwork = Operator::ORANGE_OPERATOR_NAME;
        }

        $template = str_replace('[owner_network]', $ownerNetwork, $template);

        // Set Submission Date
        $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['openDateTime']));

        $message = str_replace('[submissionDateTime]', $submissionDateTime, $template);

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');

        $cc = array();

        return $this->send_mail($to, $cc, $subject, $message);

    }

    public function adminKPSAError($message){

        $params = array(
            'errorMessage' => $message
        );

        $this->error('KPSA ERROR DETECTED', $params);

    }

    public function error($fault, $params){

        $subject = 'Error Encountered';
        $message = '';

        $template = file_get_contents(__DIR__ . '/templates/generic-error-template.html');

        // Set errorText
        $template = str_replace('[errorText]', $fault, $template);

        // Set errorMessage
        $message = str_replace('[errorMessage]', $params['errorMessage'], $template);

        $to = array('christian.nankam@orange.com', 'chp.testbed@gmail.com');
        $cc = array();

        $this->send_mail($to, $cc, $subject, $message);

    }

    /**
     * Sends message to SMS Gateway
     * @param $msisdn
     * @param $message
     */
    private function send_mail($to, $cc, $subject, $message)
    {

        $this->CI->load->library('email');

        $this->CI->email->from('SGP', 'SGP Notification Center');
        $this->CI->email->to($to);
        $this->CI->email->cc($cc);

        $this->CI->email->subject($subject);
        $this->CI->email->message($message);

        try {

            $response = $this->CI->email->send();

            $this->CI->email->print_debugger();

        }catch (Exception $ex){

            $response = false;

        }

        return $response;

    }


}