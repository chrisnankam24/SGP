<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 9:31 AM
 */

require_once APPPATH . "third_party/PHPMailer/PHPMailerAutoload.php";


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

        $mail = new PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = '172.21.55.12';  // Specify main and backup SMTP servers

        //$mail->setFrom('DTI_SIT@orange.com', 'DTI OSS');
        $mail->setFrom('DTI_SIT@orange.com');
        $mail->addAddress('christian.nankam@orange.com');     // Add a recipient
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }

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
        $subject = 'Great';

        //$this->send_mail($to, $cc, $subject, $template);

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

        $response = $this->CI->email->send();

        return $response;

    }


}