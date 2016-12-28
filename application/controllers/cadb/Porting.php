<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:56 AM
 */

/**
 *  Last modified : 11/29/2016 by Nankam H.C
 *  Changes:
 *      Added denialReasonType
 *      Modified rejectionReasonType
 *      Changed preferredPortingDateTime to portingDateTime in basePortingTransactionType
 *      Removed portingDateTime from portingTransactionType
 */

namespace PortingService\Porting;

//require_once  "Common.php";

/////////////// Constants Definition

/**
 * Class portingStateType
 * @package PortingService\Porting
 */
class portingStateType {

    const ORDERED = 'ORDERED';
    const APPROVED = 'APPROVED';
    const ACCEPTED = 'ACCEPTED';
    const CONFIRMED = 'CONFIRMED';
    const COMPLETED = 'COMPLETED';
    const DENIED = 'DENIED';
    const REJECTED = 'REJECTED';
    const ABANDONED = 'ABANDONED';

    // Orange Defined Porting States

    const MSISDN_IMPORT_CONFIRMED = 'MSISDN_IMPORT_CONFIRMED';
    const MSISDN_EXPORT_CONFIRMED = 'MSISDN_EXPORT_CONFIRMED';
    const CONTRACT_DELETED_CONFIRMED = 'CONTRACT_DELETED_CONFIRMED';
    const MSISDN_CHANGE_IMPORT_CONFIRMED = 'MSISDN_CHANGE_IMPORT_CONFIRMED';

}

/**
 * Class denialReasonType
 * @package PortingService\Porting
 */
class denialReasonType {

    const NUMBER_NOT_OWNED_BY_SUBSCRIBER = 'NUMBER_NOT_OWNED_BY_SUBSCRIBER';
    const SUBSCRIBER_DATA_DISCREPANCY = 'SUBSCRIBER_DATA_DISCREPANCY';
    const NUMBER_IN_INVALID_STATE = 'NUMBER_IN_INVALID_STATE';
    const RIO_NOT_VALID = 'RIO_NOT_VALID';

}

/**
 * Class rejectionReasonType
 * @package PortingService\Porting
 */
class rejectionReasonType {
    const SUBSCRIBER_CHANGED_NUMBER = 'SUBSCRIBER_CHANGED_NUMBER';
    const SUBSCRIBER_CANCELLED_PORTING = 'SUBSCRIBER_CANCELLED_PORTING';
    const OUTSTANDING_OBLIGATIONS_TO_DONOR = 'OUTSTANDING_OBLIGATIONS_TO_DONOR';
}

/**
 * Class portingSubmissionStateType
 * @package PortingService\Porting
 */
class portingSubmissionStateType {
    const STARTED = 'STARTED';
    const ORDERED = 'ORDERED';
    const TERMINATED = 'TERMINATED';
}


//////////////////////// Validation functions

/**
 * Validates input rioType based on porting.xsd file definition
 * @param $rioType
 * @return bool|string
 */
function isValidRioType($rioType) {
    // Convert to String
    $rioType = $rioType . '';

    // Validates for [0-9]{2}[EP][0-9]{9} regex
    $regex = '/[0-9]{2}[EP][0-9]{9}/';

    if(preg_match($regex, $rioType) && (strlen($rioType) == 12)){

        return $rioType;

    }else{

        return false;

    }
}

/**
 * Validates input portingStateType based on porting.xsd file definition
 * @param $portingStateType
 * @return bool
 */
function isValidportingStateType($portingStateType){

    if($portingStateType == portingStateType::ORDERED || $portingStateType == portingStateType::APPROVED || $portingStateType == portingStateType::ACCEPTED
        || $portingStateType == portingStateType::CONFIRMED || $portingStateType == portingStateType::COMPLETED
        || $portingStateType == portingStateType::DENIED || $portingStateType == portingStateType::REJECTED || $portingStateType == portingStateType::ABANDONED){

        return true;

    }else{

        return false;

    }

}


//////////////////////// Data Types

/**
 * Class subscriberInfoType
 * @package PortingService\Porting
 */
class subscriberInfoType{

    /**
     * @var string
     */
    public $physicalPersonFirstName;

    /**
     * @var string
     */
    public $physicalPersonLastName;

    /**
     * @var string
     */
    public $physicalPersonIdNumber;

    /**
     * @var string
     */
    public $legalPersonName;

    /**
     * @var string
     */
    public $legalPersonTin;

    /**
     * @var string
     */
    public $contactNumber;

}

/**
 * Class basePortingTransactionType
 * @package PortingService\Porting
 */
abstract class basePortingTransactionType{

    /**
     * @var \nrnType
     */
    public $recipientNrn;

    /**
     * @var \nrnType
     */
    public $donorNrn;

    /**
     * @var \DateTime
     */
    public $recipientSubmissionDateTime;

    /**
     * @var \DateTime
     */
    public $portingDateTime;

    /**
     * @var string
     */
    public $rio;

    /**
     * @var array Common\numberRangeType
     */
    public $numberRanges = array();

    /**
     * @var subscriberInfoType
     */
    public $subscriberInfo;

}

/**
 * Class portingTransactionType
 */
class portingTransactionType extends basePortingTransactionType {
    /**
     * @var string
     */
    public $portingId;

    /**
     * @var \DateTime
     */
    public $cadbOrderDateTime;

    /**
     * @var \DateTime
     */
    public $lastChangeDateTime;

    /**
     * @var string
     */
    public $portingState;

}

//////////////////////// Requests and Responses

/**
 * Class orderRequest
 */
class orderRequest extends basePortingTransactionType {

}

/**
 * Class orderResponse
 */
class orderResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class approveRequest
 */
class approveRequest {

    /**
     * @var string
     */
    public $portingId;

}

/**
 * Class approveResponse
 */
class approveResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class acceptRequest
 */
class acceptRequest {

    /**
     * @var string
     */
    public $portingId;

}

/**
 * Class acceptResponse
 */
class acceptResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class confirmRequest
 */
class confirmRequest {

    /**
     * @var string
     */
    public $portingId;

    /**
     * @var \DateTime
     */
    public $portingDateTime;

}

/**
 * Class confirmResponse
 */
class confirmResponse {
    /**
     * @var portingTransactionType
     */
    public $portingTransaction;
}

/**
 * Class denyRequest
 */
class denyRequest {

    /**
     * @var string
     */
    public $portingId;

    /**
     * @var denialReasonType
     */
    public $denialReason;

    /**
     * @var string
     */
    public $cause;
}

/**
 * Class denyResponse
 */
class denyResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class rejectRequest
 */
class rejectRequest {

    /**
     * @var string
     */
    public $portingId;

    /**
     * @var rejectionReasonType
     */
    public $rejectionReason;

    /**
     * @var string
     */
    public $cause;
}

/**
 * Class rejectResponse
 */
class rejectResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class getPortingRequest
 * Supplementary operation provided by CADB for accessing porting information for some CADB ID. Not Operator must be
 * recipient or donor of the transaction.
 */
class getPortingRequest {

    /**
     * @var string
     */
    public $portingId;

}

/**
 * Class getPortingResponse
 */
class getPortingResponse {

    /**
     * @var portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class getOrderedPortingsRequest
 */
class getOrderedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getOrderedPortingsResponse
 */
class getOrderedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}

/**
 * Class getApprovedPortingsRequest
 */
class getApprovedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getApprovedPortingsResponse
 */
class getApprovedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}

/**
 * Class getAcceptedPortingsRequest
 */
class getAcceptedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getAcceptedPortingsResponse
 */
class getAcceptedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}

/**
 * Class getConfirmedPortingsRequest
 */
class getConfirmedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getConfirmedPortingsResponse
 */
class getConfirmedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}

/**
 * Class getDeniedPortingsRequest
 */
class getDeniedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

    /**
     * @var integer
     */
    public $count;


}

/**
 * Class getDeniedPortingsResponse
 */
class getDeniedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}

/**
 * Class getRejectedPortingsRequest
 */
class getRejectedPortingsRequest {

    /**
     * @var string
     */
    public $networkId;

    /**
     * @var integer
     */
    public $count;

}

/**
 * Class getRejectedPortingsResponse
 */
class getRejectedPortingsResponse {

    /**
     * @var array portingTransactionType
     */
    public $portingTransactions;

}