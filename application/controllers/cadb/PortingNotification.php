<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 12:50 PM
 */

namespace PortingService\PortingNotification;

use PortingService\Porting as Porting;

///////////////////// Definition of Request and response messages

/**
 * Class notifyOrderedRequest
 */
class notifyOrderedRequest{

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class notifyOrderedResponse
 */
class notifyOrderedResponse {


}

/**
 * Class notifyApprovedRequest
 */
class notifyApprovedRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class notifyApprovedResponse
 */
class notifyApprovedResponse{

}

/**
 * Class notifyAutoApproveRequest
 */
class notifyAutoApproveRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class notifyAutoApproveResponse
 */
class notifyAutoApproveResponse {

}

/**
 * Class notifyAcceptedRequest
 */
class notifyAcceptedRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

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
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

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
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

}

/**
 * Class notifyAutoConfirmResponse
 */
class notifyAutoConfirmResponse {

}

/**
 * Class notifyDeniedRequest
 */
class notifyDeniedRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

    /**
     * @var Porting\denialReasonType
     */
    public $denialReason;

    /**
     * @var string
     */
    public $cause;

}

/**
 * Class notifyDeniedResponse
 */
class notifyDeniedResponse {

}

/**
 * Class notifyRejectedRequest
 */
class notifyRejectedRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

    /**
     * @var Porting\rejectionReasonType
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
class notifyRejectedResponse{

}

/**
 * Class notifyAbandonedRequest
 */
class notifyAbandonedRequest {

    /**
     * @var Porting\portingTransactionType
     */
    public $portingTransaction;

    /**
     * @var string
     */
    public $cause;

}

/**
 * Class notifyAbandonedResponse
 */
class notifyAbandonedResponse {

}