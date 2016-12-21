<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 9:31 AM
 */

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

    /**
     * Reports Error to Admin via mail
     * @param $faultCode
     * @param $params
     */
    public function adminErrorReport($faultCode, $params){

        // Load template
        $template = file_get_contents(__DIR__ . '/templates/process_error_template.html');

        // Set Fault Code
        $template = str_replace('[faultCode]', $faultCode, $template);

        $to = array('chp.testbed@gmail.com');
        $cc = array();
        $subject = [];

        $this->send_mail($to, $cc, $subject, $template);

    }

    public function adminSubmissionReport($faultcode, $params){

    }

    public function adminConfirmReport($faultcode, $params){

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
    public function adminAgentsErrorReport($params){

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

        $this->CI->email->from('chp.testbed@gmail.com', 'Nankam Happi C.');
        $this->CI->email->to($to);
        $this->CI->email->cc($cc);

        $this->CI->email->subject($subject);
        $this->CI->email->message($message);

        $this->CI->email->send();

    }


}