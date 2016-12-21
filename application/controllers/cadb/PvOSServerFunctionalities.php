<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Provision.php";
require_once "Fault.php";
require_once "Common.php";

use ProvisionService\Provision as Provision;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/9/2016
 * Time: 3:30 PM
 */

class PvOSServerFunctionalities extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProvisionOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("PvOSServerFunctionalities");

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $confirmRoutingDataRequest
     * @return Provision\confirmRoutingDataResponse
     * @throws invalidOperatorFault
     * @throws actionNotAvailableFault
     * @throws invalidCadbIdFault
     * @throws invalidRequestFormatFault
     */
    public function confirmRoutingData($confirmRoutingDataRequest){

        $response = new Provision\confirmRoutingDataResponse();

        return $response;

    }

}