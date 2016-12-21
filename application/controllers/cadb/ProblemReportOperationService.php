<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:49 PM
 */

require_once "Problem.php";
require_once "Fault.php";
require_once "Common.php";

use ProblemService\Problem as Problem;

/**
 * Controller for ProblemReportOperationService CADB server
 * Class ProblemReportOperationService
 */
class ProblemReportOperationService  extends CI_Controller {

    // Declare client
    private $client = null;

    public function __construct()
    {

        parent::__construct();

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ProblemReportOperationService.wsdl', array(
            "location" => 'http://localhost/SGP/index.php/cadb/ProblemReportOperationService',
            "trace" => false
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProblemReportOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ServerFunctionalities");

        // Handle soap operations
        $server->handle();

    }

    /**
     * @param $reporterNetworkId
     * @param $cadbNumber
     * @param $problem
     * @return errorResponse
     */
    public function reportProblem($reporterNetworkId, $cadbNumber, $problem) {

        if($this->client) {

            // Make report Problem request
            $request = new Problem\reportProblemRequest();

            $request->reporterNetworkId = $reporterNetworkId;
            $request->cadbNumber = $cadbNumber;
            $request->problem = $problem;

            try {

                $response = $this->client->reportProblem($request);

                $response->success = true;

                return $response;

            }catch (SoapFault $e){

                $response = new errorResponse();

                $fault = key($e->detail);

                $response->error = $fault;

                return $response;

            }

        }else{
            // Client null

            $response = new errorResponse();

            $response->error = Fault::CLIENT_INIT_FAULT;

            return $response;

        }

    }

}