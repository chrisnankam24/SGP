<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 12:51 PM
 */

namespace AuxService\Aux;

require_once "Common.php";

// Define Request and Response Messages

/**
 * Type definition
 * Class operatorType
 */
class operatorType {

    /**
     * @var string
     */
    public $networkId;

    /**
     * @var string
     */
    public $operatorName;

    /**
     * @var array routingNumberType
     */
    public $routingNumber;

}

/**
 * Class getOperatorRequest
 */
class getOperatorRequest {

    /**
     * @var string
     */
    public $networkId;

}

/**
 * Class getOperatorResponse
 */
class getOperatorResponse {

    /**
     * @var operatorType
     */
    public $cadbOperator;

}

/**
 * Class getOperatorsRequest
 */
class getOperatorsRequest {

}

/**
 * In essence, system receives array of cadbOperator
 * Class getOperatorsResponse
 */
class getOperatorsResponse {

    /**
     * @var operatorTypeArray
     */
    public $cadbOperators;

}