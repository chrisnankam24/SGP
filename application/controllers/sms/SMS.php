<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/controllers/rio/RIO.php';

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 7:10 AM
 */

/**
 * Class SMS
 * Englobes all SMS related functionalities of the SGP
 */
class SMS extends CI_Controller {

    // Denomination of various operators to be used in SMS messages
    public static $DENOMINATION_COMMERCIALE_MTN = 'MTN CM';
    public static $DENOMINATION_COMMERCIALE_ORANGE = 'Orange CM';
    public static $DENOMINATION_COMMERCIALE_NEXTTEL = 'Nexttel';

    public static $CUSTOMER_SERVICE = '901';

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        //SMS::OPD_Inform_Subcriber('237694975166',SMS::$DENOMINATION_COMMERCIALE_NEXTTEL, '2343454564567567');

        //SMS::OPR_Subscriber_OK('237694975166', '21/11/2016', '12:00:00', '15:00:00');

        //SMS::OPR_Subscriber_KO('237694975166');

        //SMS::OPD_Subscriber_Reminder('237694975166', '21/11/2016', '12:00:00', '15:00:00');

        //SMS::OPR_Subscriber_Cancellation('237694975166');

    }

    ///////////////////////////////////// PORTING PROCESS SMS

    /**
     * SMS sent by OPD (Orange CM) to subscriber upon receiving a porting request from OPR on behalf of Subscriber
     * @param $msisidn
     * @param $language
     * @param $porting_num
     */
    public static function OPD_Inform_Subcriber($language, $msisdn, $denom_OPR, $porting_num){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPD_Subscriber_Init.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPD_Subscriber_Init.txt');
        }

        // Set Denomination of OPR
        $template = str_replace('[OPR]', $denom_OPR, $template);

        // Set Porting ID
        $template = str_replace('[porting_id]', $porting_num, $template);

        // Set Customer Service
        $message = str_replace('[customer_service_num]', self::$CUSTOMER_SERVICE, $template);

        $response = self::send_response($msisdn, $message);

        return $response;

    }

    /**
     * SMS sent by OPR (Orange CM) to Subscriber upon receiving ACCEPTED from OPD.
     * @param $language
     * @param $msisdn string subscriber MSISDN
     * @param $day string in JJ/MM/AAAA format
     * @param $start_time
     * @param $end_time
     */
    public static function OPR_Subscriber_OK($language, $msisdn, $day, $start_time, $end_time){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPR_Subscriber_OK.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPR_Subscriber_OK.txt');
        }


        // Set Subscriber MSISDN
        $template = str_replace('[subs_msisdn]', $msisdn, $template);

        // Set Denomination of OPR
        $template = str_replace('[OPR]', SMS::$DENOMINATION_COMMERCIALE_ORANGE, $template);

        // Set Porting Day
        $template = str_replace('[day]', $day, $template);

        // Set Porting Start time
        $count = 2;
        $template = str_replace('[start_time]', $start_time, $template, $count);

        // Set Porting End time
        $message = str_replace('[end_time]', $end_time, $template);

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS sent by OPR (Orange CM) to Subscriber upon receiving DENIED / REJECTED from OPR.
     * @param $language
     * @param $msisdn string subscriber MSISDN
     */
    public static function OPR_Subscriber_KO($language, $msisdn){

        // Load Message
        if(strtolower($language) == 'en'){
            $message = file_get_contents(__DIR__ . '/en_sms_template_OPR_Subscriber_KO.txt');
        }else{
            $message = file_get_contents(__DIR__ . '/fr_sms_template_OPR_Subscriber_KO.txt');
        }

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS Sent by OPD(Orange CM) to Subscriber atleast 4hrs from the porting time
     * @param $language
     * @param $msisdn
     * @param $day
     * @param $start_time
     * @param $end_time
     */
    public static function OPD_Subscriber_Reminder($language, $msisdn, $denom_OPR, $day, $start_time, $end_time){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPD_Subscriber_Reminder.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPD_Subscriber_Reminder.txt');
        }

        // Set Subscriber MSISDN
        $template = str_replace('[subs_msisdn]', $msisdn, $template);

        // Set Denomination of OPR
        $template = str_replace('[OPR]', $denom_OPR, $template);

        // Set Porting Day
        $template = str_replace('[day]', $day, $template);

        // Set Porting Start time
        $count = 2;
        $template = str_replace('[start_time]', $start_time, $template, $count);

        // Set Porting End time
        $message = str_replace('[end_time]', $end_time, $template);

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS sent by OPR(Orange CM) to Subscriber upon Cancellation of his Porting request
     * @param $language
     * @param $msisdn
     */
    public static function OPR_Subscriber_Cancellation($language, $msisdn){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPR_Subscriber_Cancellation.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPR_Subscriber_Cancellation.txt');
        }

        // Set Subscriber MSISDN
        $template = str_replace('[subs_msisdn]', $msisdn, $template);

        // Set Denomination of OPR
        $message = str_replace('[OPR]', SMS::$DENOMINATION_COMMERCIALE_ORANGE, $template);

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS sent by OPR(Orange CM) to Subscriber upon reception of notifyAbandoned reception message
     * @param $language
     * @param $msisdn
     */
    public static function Subscriber_CADB_Abandoned($language, $msisdn){

        $response = self::send_response($msisdn, 'CADB abandoned your port');

        return $response;
    }

    /**
     * SMS sent by OPR(Orange CM) to Subscriber upon reception of confirmRoutingData ACK
     * @param $msisdn
     */
    public static function OPR_Subscriber_Welcome($language, $msisdn){

    }

    ///////////////////////////////////// ROLLBACK PROCESS SMS

    /**
     * SMS sent by OPR(Orange CM) to Subscriber upon reception of rollback request from OPD
     * @param $language
     * @param $msisdn
     * @param $denom_OPD
     * @param $rollback_num
     */
    public static function OPR_Inform_Subscriber($language, $msisdn, $denom_OPD, $rollback_num){
        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPR_Subscriber_Init_Rollback.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPR_Subscriber_Init_Rollback.txt');
        }

        // Set Denomination of OPR
        $template = str_replace('[OPD]', $denom_OPD, $template);

        // Set Porting ID
        $template = str_replace('[rollback_id]', $rollback_num, $template);

        // Set Customer Service
        $message = str_replace('[customer_service_num]', self::$CUSTOMER_SERVICE, $template);

        $response = self::send_response($msisdn, $message);

        return $response;    }

    /**
     * SMS sent by OPD (Orange CM) to Subscriber upon receiving ACCEPTED from OPD.
     * @param $language
     * @param $msisdn string subscriber MSISDN
     * @param $day string in JJ/MM/AAAA format
     * @param $start_time
     * @param $end_time
     */
    public static function OPD_Subscriber_OK($language, $msisdn, $day, $start_time, $end_time){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_OPD_Subscriber_OK_Rollback.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_OPD_Subscriber_OK_Rollback.txt');
        }

        // Set Subscriber MSISDN
        $template = str_replace('[subs_msisdn]', $msisdn, $template);

        // Set Denomination of OPD
        $template = str_replace('[OPD]', SMS::$DENOMINATION_COMMERCIALE_ORANGE, $template);

        // Set Porting Day
        $template = str_replace('[day]', $day, $template);

        // Set Porting Start time
        $count = 2;
        $template = str_replace('[start_time]', $start_time, $template, $count);

        // Set Porting End time
        $message = str_replace('[end_time]', $end_time, $template);

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS sent by OPD (Orange CM) to Subscriber upon receiving REJECTED from OPR.
     * @param $language
     * @param $msisdn string subscriber MSISDN
     */
    public static function OPD_Subscriber_KO($language, $msisdn){

        // Load Message
        if(strtolower($language) == 'en'){
            $message = file_get_contents(__DIR__ . '/en_sms_template_OPR_Subscriber_KO_Rollback.txt');
        }else{
            $message = file_get_contents(__DIR__ . '/fr_sms_template_OPR_Subscriber_KO_Rollback.txt');
        }


        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * @param $msisdn
     */
    public static function Subscriber_CADB_Abandoned_Rollback($language, $msisdn){
        self::send_response($msisdn, 'CADB abandoned your rollback');
    }

    ///////////////////////////////////// RIO SMS

    /**
     * SMS sent by OPD(Orange CM) to Subscriber informing him of his RIO
     * @param $language
     * @param $msisdn
     */
    public static function Subscriber_RIO($language, $msisdn){

        // Load template
        if(strtolower($language) == 'en'){
            $template = file_get_contents(__DIR__ . '/en_sms_template_rio.txt');
        }else{
            $template = file_get_contents(__DIR__ . '/fr_sms_template_rio.txt');
        }

        $rio = RIO::get_rio($msisdn);

        // Set Subscriber RIO
        $message = str_replace('[rio]', $rio, $template);

        $response = self::send_response($msisdn, $message);

        return $response;
    }

    /**
     * SMS sent by USSD Service to Subscriber after Subscriber demand USSD
     * @param $message
     * @param $msisdn
     * @return array
     */
    public static function USSD_SMS($message, $msisdn){

        $response = self::send_response($msisdn, $message);

        return $response;

    }

    /**
     * Sends message to SMS Gateway
     * @param $msisdn
     * @param $message
     */
    private static function send_response($msisdn, $message)
    {

        $sendResponse = array();
        $sendResponse['success'] = true;

        /*try {

            $response = file_get_contents('http://' . SMSParams::HOST . ':' . SMSParams::PORT . '/cgi-bin/sendsms?&username='
                . SMSParams::USERNAME . '&password=' . SMSParams::PASSWORD . '&from=' . SMSParams::FROM . '&to=' . $msisdn . '&text=' . urlencode($message)
                . '&charset=' . SMSParams::CHARSET . '&coding=' . SMSParams::CODING . '&priority=' . SMSParams::PRIORITY);

        }catch (Exception $ex){

            $sendResponse['success'] = false;

        }*/

        return $sendResponse;

    }

}