<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 8:38 AM
 */

namespace ReturnService\_Return;

/////////////// Constants Definition

/**
 * Class returnStateType
 * @package ReturnService\_Return
 */
class returnStateType {

    const OPENED = 'OPENED';
    const ACCEPTED = 'ACCEPTED';
    const COMPLETED = 'COMPLETED';
    const REJECTED = 'REJECTED';

    // Orange Defined Return States

    const MSISDN_EXPORT_CONFIRMED = 'MSISDN_EXPORT_CONFIRMED';
    const MSISDN_RETURN_CONFIRMED = 'MSISDN_RETURN_CONFIRMED';

}

/**
 * Class returnSubmissionStateType
 * @package ReturnService\_Return
 */
class returnSubmissionStateType {
    const STARTED = 'STARTED';
    const OPENED = 'OPENED';
    const TERMINATED = 'TERMINATED';
}


////////////////////// Return Types and Functions

/**
 * Validates input returnStateType based on return.xsd file definition
 * @param $returnStateType
 * @return bool
 */
function isValidReturnStateType($returnStateType){

    if($returnStateType == returnStateType::OPENED || $returnStateType == returnStateType::ACCEPTED
        || $returnStateType == returnStateType::COMPLETED || $returnStateType == returnStateType::REJECTED){

        return true;

    }else{

        return false;

    }

}

/**
 * Class baseReturnTransactionType
 */
abstract class baseReturnTransactionType {

    /**
     * @var \nrnType
     */
    public $ownerNrn;

    /**
     * @var \nrnType
     */
    public $primaryOwnerNrn;

    /**
     * @var \numberRangesType
     */
    public $numberRanges = array();

}

/**
 * Class returnTransactionType
 */
class returnTransactionType extends baseReturnTransactionType{

    /**
     * @var string
     */
    public $returnId;

    /**
     * @var returnStateType
     */
    public $returnNumberState;

    /**
     * @var \DateTime
     */
    public $openDateTime;

}

/**
 * Class openRequest
 */
class openRequest extends baseReturnTransactionType {

}

/**
 * Class openResponse
 */
class openResponse {

    /**
     * @var returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class acceptRequest
 */
class acceptRequest {

    /**
     * @var string
     */
    public $returnId;

}

/**
 * Class acceptResponse
 */
class acceptResponse {

    /**
     * @var returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class rejectRequest
 */
class rejectRequest {

    /**
     * @var string
     */
    public $returnId;

    /**
     * @var string
     *
     */
    public $cause;

}

/**
 * Class rejectResponse
 */
class rejectResponse {

    /**
     * @var returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class getReturningTransactionRequest
 */
class getReturningTransactionRequest {

    /**
     * @var string
     */
    public $returnId;

}

/**
 * Class getReturningTransactionResponse
 */
class getReturningTransactionResponse {

    /**
     * @var returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class getCurrentReturningTransactionsRequest
 */
class getCurrentReturningTransactionsRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getCurrentReturningTransactionsResponse
 */
class getCurrentReturningTransactionsResponse {

    /**
     * @var array returnTransactionType
     */
    public $returnNumberTransactions;

}