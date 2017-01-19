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
        $template = file_get_contents(__DIR__ . '/templates/email.html');

        $to = array('christian.nankam@orange.com');
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
            $submissionDateTime = date('l, M d Y, H:i:s', strtotime($params['recipientSubmissionDateTime']));
            $template = str_replace('[submissionDateTime]', $submissionDateTime, $template, $count);

            // Set Last Change Date
            $lastChangeDateTime = date('l, M d Y, H:i:s', strtotime($params['lastChangeDateTime']));
            $template = str_replace('[lastChangeDateTime]', $lastChangeDateTime, $template, $count);

            // Set Porting State
            $message = str_replace('[portingState]', $params['portingState'], $template, $count);

        }elseif($processType == processType::ROLLBACK){



        }else{



        }

        $to = array('christian.nankam@orange.com');
        $cc = array();

        $this->send_mail($to, $cc, $subject, $message);

    }

    public function adminSubmissionReport($faultcode, $params, $processType){

    }

    public function adminConfirmReport($faultcode, $params, $processType){

    }

    public function adminAgentsRetardedSubmission($params){

    }

    public function adminAgentsPortingApprovedDenied($params){

    }

    public function backOfficePortingAcceptReject($params){

    }

    public function backOfficeRollbackAcceptReject($params){

    }

    public function backOfficeReturnAcceptReject($params){

    }

    public function adminKPSAError($fault, $params){

    }

    public function cadbSynchronizationFailure($params){

    }

    public function cadbPortingStateOffConfirmed($params){

    }

    /**
     * Reports Error to Admin via mail
     * @param $faultCode
     * @param $params
     */
    public function adminAgentsPortingAbandoned($params){

        $template = 'Porting abandoned by CADB';

        $to = array('chp.testbed@gmail.com');
        $cc = array();
        $subject = [];

        $this->send_mail($to, $cc, $subject, $template);

    }

    /**
     * @param $params
     */
    public function adminAgentsErrorReport($params, $processType){

    }

    /**
     * @param $params
     */
    public function adminAgentsBatchPortingSubmission($params){

    }

    /**
     * @param $params
     */
    public function adminAgentsBatchRollbackSubmission($params){

    }

    /**
     * @param $params
     */
    public function adminAgentsBatchNRSubmission($params){

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

        $response = $this->CI->email->send();

        $this->CI->email->print_debugger();

        return $response;

    }


}