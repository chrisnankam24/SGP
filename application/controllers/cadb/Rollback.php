<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 9:44 AM
 */

namespace RollbackService\Rollback;

use PortingService\Porting as Porting;

/////////////// Constants Definition

/**
 * Class rollbackStateType
 * @package RollbackService\Rollback
 */
class rollbackStateType {

    const OPENED = 'OPENED';
    const ACCEPTED = 'ACCEPTED';
    const CONFIRMED = 'CONFIRMED';
    const COMPLETED = 'COMPLETED';
    const REJECTED = 'REJECTED';
    const ABANDONED = 'ABANDONED';

    // Orange Defined Rollback States

    const MSISDN_EXPORT_CONFIRMED = 'MSISDN_EXPORT_CONFIRMED';
    const CONTRACT_DELETED_CONFIRMED = 'CONTRACT_DELETED_CONFIRMED';

}

class rollbackSubmissionStateType {
    const STARTED = 'STARTED';
    const OPENED = 'OPENED';
    const TERMINATED = 'TERMINATED';
}

/**
 * Class rejectionReasonType
 * @package RollbackService\Rollback
 */
class rejectionReasonType {
    const OTHER_REASONS = 'OTHER_REASONS';
}

////////////////////// Return Types and Functions

/**
 * Validates input rollbackStateType based on rollback.xsd file definition
 * @param $rollbackStateType
 * @return bool
 */
function isValidRollbackStateType($rollbackStateType){

    if($rollbackStateType == rollbackStateType::OPENED || $rollbackStateType == rollbackStateType::ACCEPTED
        || $rollbackStateType == rollbackStateType::CONFIRMED || $rollbackStateType == rollbackStateType::COMPLETED
        || $rollbackStateType == rollbackStateType::REJECTED || $rollbackStateType == rollbackStateType::ABANDONED){

        return true;

    }else{

        return false;

    }

}

/**
 * Class baseRollbackTransactionType
 */
abstract class baseRollbackTransactionType {

    /**
     * @var string
     */
    public $originalPortingId;

    /**
     * @var \DateTime
     */
    public $donorSubmissionDateTime;

    /**
     * @var \DateTime
     */
    public $preferredRollbackDateTime;

}

/**
 * Class rollbackTransactionType
 */
class rollbackTransactionType extends baseRollbackTransactionType {

    /**
     * @var string
     */
    public $rollbackId;

    /**
     * @var \nrnType
     */
    public $recipientNrn;

    /**
     * @var \nrnType
     */
    public $donorNrn;

    /**
     * @var \numberRangesType
     */
    public $numberRanges;

    /**
     * @var Porting\subscriberInfoType
     */
    public $subscriberInfo;

    /**
     * @var \DateTime
     */
    public $cadbOpenDateTime;

    /**
     * @var \DateTime
     */
    public $lastChangeDateTime;

    /**
     * @var RollbackStateType
     */
    public $rollbackState;

    /**
     * @var \DateTime
     */
    public $rollbackDateTime;

}

/**
 * Class openRequest
 */
class openRequest extends baseRollbackTransactionType {

}

/**
 * Class openResponse
 */
class openResponse {

    /**
     * @var rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class acceptRequest
 */
class acceptRequest {

    /**
     * @var string
     */
    public $rollbackId;

}

/**
 * Class acceptResponse
 */
class acceptResponse {

    /**
     * @var rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class confirmRequest
 */
class confirmRequest {

    /**
     * @var string
     */
    public $rollbackId;

    /**
     * @var \DateTime
     */
    public $rollbackDateAndTime;

}

/**
 * Class confirmResponse
 */
class confirmResponse {

    /**
     * @var rollbackTransactionType
     */
    public $rollbackTransaction;

    /**
     * @var \DateTime
     */
    public $rollbackDateTime;

}

/**
 * Class rejectRequest
 */
class rejectRequest {

    /**
     * @var string
     */
    public $rollbackId;

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
     * @var rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class getRollbackRequest
 */
class getRollbackRequest {

    /**
     * @var string
     */
    public $rollbackId;

}

/**
 * Class getRollbackResponse
 */
class getRollbackResponse {

    /**
     * @var rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class getOpenedRollbacksRequest
 */
class getOpenedRollbacksRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getOpenedRollbacksResponse
 */
class getOpenedRollbacksResponse {

    /**
     * @var array rollbackTransactionType
     */
    public $rollbacks;

}

/**
 * Class getAcceptedRollbacksRequest
 */
class getAcceptedRollbacksRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getAcceptedRollbacksResponse
 */
class getAcceptedRollbacksResponse {

    /**
     * @var array rollbackTransactionType
     */
    public $rollbacks;

}

/**
 * Class getConfirmedRollbacksRequest
 */
class getConfirmedRollbacksRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getConfirmedRollbacksResponse
 */
class getConfirmedRollbacksResponse {

    /**
     * @var array rollbackTransactionType
     */
    public $rollbacks;

}

/**
 * Class getRejectedRollbacksRequest
 */
class getRejectedRollbacksRequest {

    /**
     * @var string
     */
    public $networkId;

    /**
     * @var integer
     */
    public $count;

}

/***
 * Class getRejectedRollbacksResponse
 */
class getRejectedRollbacksResponse {

    /**
     * @var array rollbackTransactionType
     */
    public $rollbacks;

}