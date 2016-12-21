<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 10:21 AM
 */

namespace RollbackService\RollbackNotification;

use RollbackService\Rollback as Rollback;

////////////////////// Return Types and Functions

/**
 * Class notifyOpenedRequest
 */
class notifyOpenedRequest {

    /**
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

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
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class notifyAcceptedResponse
 */
class notifyAcceptedResponse {

}

/**
 * Class notifyAutoAcceptRequest
 */
class notifyAutoAcceptRequest {

    /**
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class notifyAutoAcceptResponse
 */
class notifyAutoAcceptResponse {

}

/**
 * Class notifyAutoConfirmRequest
 */
class notifyAutoConfirmRequest {

    /**
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

}

/**
 * Class notifyAutoConfirmResponse
 */
class notifyAutoConfirmResponse {

}

/**
 * Class notifyRejectedRequest
 */
class notifyRejectedRequest {

    /**
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

    /**
     * @var Rollback\rejectionReasonType
     */
    public $rejectionReason;

    /**
     * @var string
     */
    public $cause;

}

/**
 * Class notifyRejectedResponse
 */
class notifyRejectedResponse {

}

/**
 * Class notifyAbandonedRequest
 */
class notifyAbandonedRequest {

    /**
     * @var Rollback\rollbackTransactionType
     */
    public $rollbackTransaction;

    /**
     * @var string
     */
    public $cause;

}

/**
 * Class notifyAbandonedResponse
 * @package RollbackService\RollbackNotification
 */
class notifyAbandonedResponse {

}