<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Common.php";
require_once "Problem.php";
require_once "ProblemNotification.php";
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

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ProblemReportNotificationService.wsdl', array(
            "trace" => false,
            'stream_context' => stream_context_create(array(
                'http' => array(
                    'header' => 'Authorization: Bearer ' . Auth::CADB_AUTH_BEARER
                ),
            )),
        ));

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/ProblemReportOperationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

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

        $response->returnTransaction = new Problem\problemReportType();

        $rand =  mt_rand(100,998);

        $response->returnTransaction->reporterNetworkId = $reportProblemRequest->reporterNetworkId;
        $response->returnTransaction->submissionDateTime = date('c');
        $response->returnTransaction->errorReportId =  date('Ymd') . '-'. $reportProblemRequest->reporterNetworkId .'-' .
            $reportProblemRequest->cadbNumber . '-' . $rand;
        $response->returnTransaction->cadbNumber = $reportProblemRequest->cadbNumber;
        $response->returnTransaction->problem = $reportProblemRequest->problem;

        $notifyRequest = new \ProblemService\ProblemNotification\notifyProblemReportedRequest();

        $notifyRequest->problemReport = new Problem\problemReportType();
        $notifyRequest->problemReport->cadbNumber = $reportProblemRequest->cadbNumber;
        $notifyRequest->problemReport->problem = $reportProblemRequest->problem;
        $notifyRequest->problemReport->errorReportId = date('Ymd') . '-'. $reportProblemRequest->reporterNetworkId .'-' .
            $reportProblemRequest->cadbNumber . '-' . ($rand+1);
        $notifyRequest->problemReport->submissionDateTime = date('c');
        $notifyRequest->routingChangeDateTime = date('c');
        $notifyRequest->processType = 'PORTING';
        $notifyRequest->nrn = new nrnType();
        $notifyRequest->nrn->networkId = '02';
        $notifyRequest->nrn->routingNumber = '1602';

        //$this->client->notifyProblemReported($notifyRequest);

        return $response;
        //throw new actionNotAuthorizedFault();
    }
}