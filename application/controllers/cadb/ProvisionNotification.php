<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 8:21 AM
 */

namespace ProvisionService\ProvisionNotification;

////////////////////// Provision Notification Types and Functions

class provisionStateType {
    const STARTED = 'STARTED';
    const COMPLETED = 'COMPLETED';
    const TERMINATED = 'TERMINATED';
}

/**
 * Class notifyRoutingDataRequest
 */
class notifyRoutingDataRequest {

    /**
     * @var \numberRangesType
     */
    public $numberRanges;

    /**
     * @var \routingDataType
     */
    public $routingData;

}

/**
 * Class notifyRoutingDataResponse
 */
class notifyRoutingDataResponse {

}
