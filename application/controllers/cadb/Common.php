<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 4:32 PM
 */

/////////////// Constants Definition

/**
 * CADB Authorization Token
 */
Class Auth {
    const CADB_AUTH_BEARER = 'b584f5bb-c637-4fe2-a379-bd74235ecc79';
    const LDB_AUTH_BEARER = 'e161bb0f103f4c6589f97a00bb27eb921933c1e7f7c39a8049fb3e2a53e1eab0'; // var_dump(hash('sha256', '#SGP*2016%ORANGE$CM'));
}

/**
 * Operator related Constants
 */
class Operator {
    const MTN_NETWORK_ID = '01';
    const ORANGE_NETWORK_ID = '02';
    const NEXTTEL_NETWORK_ID = '03';

    const MTN_ROUTING_NUMBER = '1601';
    const ORANGE_ROUTING_NUMBER = '1602';
    const NEXTTEL_ROUTING_NUMBER = '1603';

    const MTN_OPERATOR_NAME = 'MTN Cameroon';
    const ORANGE_OPERATOR_NAME = 'Orange Cameroon';
    const NEXTTEL_OPERATOR_NAME = 'Viettel Cameroon';

    const ORANGE_NETWORK_ID_NUMBER = 2;
}

/**
 * SMS Message types used throughout the system
 */
class SMSType {
    // Porting Operation SMS
    const OPR_PORTING_ORDERED = 'OPR_PORTING_ORDERED';
    const OPD_PORTING_INIT = 'OPD_PORTING_INIT';
    const OPR_PORTING_OK = 'OPR_PORTING_OK';
    const OPR_PORTING_KO = 'OPR_PORTING_KO';
    const OPR_PORTING_CANCELLED = 'OPR_PORTING_CANCELLED';
    const OPD_PORTING_REMINDER = 'OPD_PORTING_REMINDER';
    const OPD_PORTING_ABANDONED = 'OPD_PORTING_ABANDONED';
    const OPR_PORTING_ABANDONED = 'OPR_PORTING_ABANDONED';
    const OPR_PORTING_WELCOME = 'OPR_PORTING_WELCOME';

    // Rollback Operation SMS
    const OPR_ROLLBACK_STARTED = 'OPR_ROLLBACK_STARTED';
    const OPD_ROLLBACK_ACCEPTED = 'OPD_ROLLBACK_ACCEPTED';
    const OPD_ROLLBACK_REJECTED = 'OPD_ROLLBACK_REJECTED';
    const OPD_ROLLBACK_COMPLETED = 'OPD_ROLLBACK_COMPLETED';
    const OPD_ROLLBACK_ABANDONED = 'OPD_ROLLBACK_ABANDONED';
    const OPR_ROLLBACK_ABANDONED = 'OPR_ROLLBACK_ABANDONED';
}

/**
 * Class smsState
 */
class smsState {

    const PENDING = 'PENDING';
    const SENT = 'SENT';
    const TERMINATED = 'TERMINATED';

    // Used for email notifications in which mails are not needed
    const CLOSED = 'CLOSED';

}

/**
 * Class SMSParams
 * SMS Transmission params
 */
class SMSParams{

    const HOST = '172.21.75.48';
    const PORT = 1025;
    const USERNAME = 'user';
    const PASSWORD = 'pwd';
    const FROM = 'Orange';
    const PRIORITY = 3;
    const CHARSET = 'UTF-8';
    const CODING = '2';

}

/**
 * Class KPSAParams
 * KPSA Operation Params
 */
class KPSAParams {

    const HOST = '172.21.75.50'; // PROD
    //const HOST = '172.21.75.52'; // PREPROD
    //const HOST = '172.21.95.6'; // DR
    const PORT = 13023;

}

class params {
    const DENIED_REJECTED_MAX_COUNT = 50;
}

/**
 * Process Types
 */
class processType {
    const PORTING = 'PORTING';
    const ROLLBACK = 'ROLLBACK';
    const _RETURN = 'RETURN';

    const ERROR = 'ERROR';
}

class portingSource {
    const WEB = 'WEB';
    const MOBILE = 'MOBILE';
}

class rioSource {
    const USSD = 'USSD';
    const IVR = 'IVR';
}

// CADB SFTP CONNECTION PARAMS
class sftpParams{
    const HOST = 'cameroonmnp.campost.cm';
    const USERNAME = 'orange';
    const PASSWORD = 'Wai7doh5';
    const PATH = 'csv/';
}

class BscsParams {
    const cmsUserName = 'CMSINT';
    const cmsPassword = 'CMSINT';
    const endUserName = 'CMSINT';

    const HMCODE = '179';
    const MTN_PLCODE = '356';
    const NEXTTEL_PLCODE = '357';

    const PORTING_OUT_STATUS = 4;
    const PORTING_OUT_REASON = 48;
}

class languageParams{
    const FRENCH = 'FR';
    const ENGLISH = 'EN';
}

class EmailParams{
    //const TO = 'DSI.Software_Factory@orange.com';
    const TO = 'christian.nankam@orange.com';
    const NOTIF_TO = 'DSI.Software_Factory@orange.com';
    const FROM = '';
    const CC = '';
}

/////////////// Primitive Types Verification

/**
 * Validates input networkId based on common.xsd file definition
 * @param $networkId
 * @return bool|string
 */
function isValidNetworkIdType($networkId){

    // Convert to String
    $networkId = $networkId . '';

    // Validates for [0-9]{2} regex
    $regex = '/[0-9]{2}/';

    if(preg_match($regex, $networkId) && (strlen($networkId) == 2)){

        return $networkId;

    }else{

        return false;

    }
}

/**
 * Validates input routingNumber based on common.xsd file definition
 * @param $routingNumber
 * @return bool|string
 */
function isValidRoutingNumberType($routingNumber){

    // Convert to String
    $routingNumber = $routingNumber . '';

    // Validates for [A-F0-9]{4,6} regex
    $regex = '/[A-F0-9]{4,6}/';

    if(preg_match($regex, $routingNumber) && (strlen($routingNumber) >= 4) && (strlen($routingNumber) <= 6)){

        return $routingNumber;

    }else{

        return false;

    }
}

/**
 * Validates input number based on common.xsd file definition
 * @param $number
 * @return bool|string
 */
function isValidNumberType($number){

    // Convert to String
    $number = $number . '';

    // Validates for [1-9][0-9]{10,14} regex
    $regex = '/[1-9][0-9]{10,14}/';

    if(preg_match($regex, $number) && (strlen($number) >= 10)  && (strlen($number) <= 14)){

        return $number;

    }else{

        return false;

    }
}

/**
 * Validates input processType based on common.xsd file definition
 * @param $processType
 * @return bool
 */
function isValidProcessType($processType){

    if($processType == processType::PORTING || $processType == processType::ROLLBACK || $processType == processType::_RETURN){
        return true;
    }else{
        return false;
    }

}

/**
 * Generate preferred porting datetime
 * @return false|string
 */
function getRecipientPortingDateTime(){

    // TODO: Generate 2 days from now which are neither weekends or public holidays as found in the restricted days file

    $date = date('Y-m-d\TH:i:s');

    return $date;

}

/**
 * Verifies if MSISDN has Orange as OPA
 * @param $msisdn
 * @return bool
 */
function isOCMNumber($msisdn){

    if(strlen($msisdn) == 12){
        $msisdn = substr($msisdn, 3);
    }

    if(substr($msisdn, 0, 3) == '655' || substr($msisdn, 0, 3) == '656'){

        return true;

    }elseif($msisdn >= '657000000' && $msisdn <= '657499999'){

        return true;

    }elseif(substr($msisdn, 0, 2) == '69'){

        return true;

    }else{

        return false;

    }

}

/**
 * Retrieves the nature of a subscriber from his RIO
 * @param $rio
 * @return int
 */
function getSubscriberType($rio){

    if($rio[2] == 'P'){
        return 0; // Person
    }else{
        return 1; // Enterprise
    }

}

/**
 * Log User action to DB
 * @param $userId
 * @param $actionPerformed
 */
function logAction($userId, $actionPerformed){

    $logParams = array(
        'userId' => $userId,
        'actionPerformed' => $actionPerformed,
        'actionDateTime' => date('Y-m-d\TH:i:s')
    );

    $CI =& get_instance();

    $CI->load->model('Log_model');

    $CI->Log_model->add_log($logParams);

}

/**
 * Verifies if CADB request is Authenticated. If not, it throws SOAP fault
 * @return bool
 * @throws SoapFault
 */
function isAuthorized(){

    $headers = getallheaders();

    if(isset($headers['Authorization'])){

        $bearerAuth = $headers['Authorization'];

        $bearerAuth = explode(' ', trim($bearerAuth));

        $auth = $bearerAuth[count($bearerAuth)-1];

        if($auth == Auth::LDB_AUTH_BEARER){
            // Authorized
            return true;

        }else{
            // Not Authorized
            //http_response_code(401);
            //throw new ldbAdministrationServiceFault('Incorrect Token');
        }

    }else{
        // Not Authorized
        //http_response_code(401);
        //throw new ldbAdministrationServiceFault('No Token Found');

    }

}

/**
 * Returns difference between two dates
 * @param $startDate
 * @param $endDate
 * @return DateInterval|false
 */
function getDiff($startDate, $endDate){

    $startDate = date('y-d-m', strtotime($startDate));
    $endDate = date('y-d-m', strtotime($endDate));

    $startDate = date_create_from_format('y-d-m', $startDate);
    $endDate = date_create_from_format('y-d-m', $endDate);

    return date_diff($startDate, $endDate);

}

// Turn all errors into exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

/////////////// Complex Types Class Implementations

/**
 * Class numberRangeType
 */
class numberRangeType {

    /**
     * @var string
     */
    public $startNumber;

    /**
     * @var string
     */
    public $endNumber;

}

/**
 * Class numberRangesType
 */
class numberRangesType {

    /**
     * @var \numberRangeType
     */
    public $numberRange;

}

/**
 * Class nrnType
 */
class nrnType {

    /**
     * @var string
     */
    public $networkId;

    /**
     * @var string
     */
    public $routingNumber;

}

/**
 * Class routingDataType
 */
class routingDataType {

    /**
     * @var nrnType
     */
    public $nrn;

    /**
     * @var dateTime
     */
    public $routingChangeDateTime;

    /**
     * @var string
     */
    public $processId;

    /**
     * @var ProcessType
     */
    public $processType;
}

/**
 * Class errorResponse
 */
class errorResponse {

    /**
     * @var bool
     */
    public $success = false;

    /**
     * @var string
     */
    public $error;

    /**
     * @var string
     */
    public $message = '';

}
?>