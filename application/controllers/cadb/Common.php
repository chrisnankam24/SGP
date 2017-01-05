<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 4:32 PM
 */

/////////////// Constants Definition

/**
 * Operator related Constants
 */
class Operator {
    const MTN_NETWORK_ID = '01';
    const ORANGE_NETWORK_ID = '02';
    const NEXTTEL_NETWORK_ID = '04';

    const MTN_ROUTING_NUMBER = 'ABCDEF01';
    const ORANGE_ROUTING_NUMBER = 'ABCDEF02';
    const NEXTTEL_ROUTING_NUMBER = 'ABCDEF03';

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
    const TERMiNATED = 'TERMINATED';

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

    const HOST = '172.21.75.52';
    const PORT = 13023;

}

class params {
    const DENIED_REJECTED_MAX_COUNT = 100;
}

/**
 * Process Types
 */
class processType {
    const PORTING = 'PORTING';
    const ROLLBACK = 'ROLLBACK';
    const _RETURN = 'RETURN';
}

// CADB SFTP CONNECTION PARAMS
class sftpParams{
    const HOST = 'test.rebex.net';
    const USERNAME = 'demo';
    const PASSWORD = 'password';
    const PATH = '/';
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

    // Validates for [A-F0-9]{8} regex
    $regex = '/[A-F0-9]{8}/';

    if(preg_match($regex, $routingNumber) && (strlen($routingNumber) == 8)){

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

    // Validates for [1-9][0-9]{14} regex
    $regex = '/[1-9][0-9]{14}/';

    if(preg_match($regex, $number) && (strlen($number) == 14)){

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

    $date = date('c');

    return $date;

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

}
?>