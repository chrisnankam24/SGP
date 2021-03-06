<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once '_Aux.php';
require_once 'Fault.php';

use AuxService\Aux as Aux;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 3:51 PM
 */

class AuxServerFunctionalities extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/AuxService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        $headers = getallheaders();

        $cadbAuth = null;

        if(isset($headers['Authorization'])){
            $cadbAuth = $headers['Authorization'];
        }

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $getOperatorRequest
     * @return Aux\getOperatorResponse
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     */
    public function getOperator($getOperatorRequest){

        $response = new Aux\getOperatorResponse();

        $operator = new Aux\operatorType();

        $operator->networkId = '01';
        $operator->operatorName = 'MTN Cameroon';
        $operator->routingNumber = '1601';

        $response->cadbOperator = $operator;

        return $response;

    }

    /**
     * @param $getOperatorsRequest
     * @return Aux\getOperatorsResponse
     * @throws invalidRequestFormatFault
     */
    public function getOperators($getOperatorsRequest){

        $response = new Aux\getOperatorsResponse();

        return $response;
    }

}

