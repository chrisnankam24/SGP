<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Problem.php";
require_once "Fault.php";

use ProblemService\Problem as Problem;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/9/2016
 * Time: 3:30 PM
 */

class PrOSServerFunctionalities extends CI_Controller  {

    // Declare client
    private $client = null;

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProblemReportOperationService.wsdl');

        // Set the class for the soap server
        $server->setClass("PrOSServerFunctionalities");

        // Handle soap operations
        $server->handle();

    }

    ////////////////////////////////////////// Define Server methods

    /**
     * @param $reportProblemRequest
     * @return Problem\reportProblemResponse
     * @throws unknownNumberFault
     * @throws unknownManagedNumberFault
     * @throws invalidOperatorFault
     * @throws invalidRequestFormatFault
     * @throws actionNotAuthorizedFault
     */
    public function reportProblem($reportProblemRequest){

        $response = new Problem\reportProblemResponse();

        return $response;
    }
}