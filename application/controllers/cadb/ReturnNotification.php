<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 9:05 AM
 */

namespace ReturnService\_ReturnNotification;

use ReturnService\_Return as _Return;

////////////////////// Return Types and Functions

/**
 * Class notifyOpenedRequest
 */
class notifyOpenedRequest {

    /**
     * @var _Return\returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class notifyOpenedResponse
 */
class notifyOpenedResponse {

}

/**
 * Class notifyAcceptedRequest
 */
class notifyAcceptedRequest {

    /**
     * @var _Return\returnTransactionType
     */
    public $returnTransaction;

}

/**
 * Class notifyAcceptedResponse
 */
class notifyAcceptedResponse {

}

/**
 * Class notifyRejectedRequest
 */
class notifyRejectedRequest {

    /**
     * @var _Return\returnTransactionType
     */
    public $returnTransaction;

    /**
     * @var string
     */
    public $cause;

}

/**
 * Class notifyRejectedResponse
 */
class notifyRejectedResponse{

}
