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

        $this->load->model('Error_model');

        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $this->client = new SoapClient(__DIR__ . '/wsdl/ProblemReportOperationService.wsdl', array(
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

    /**
     * TODO: OK
     * @param $reporterNetworkId
     * @param $cadbNumber
     * @param $problem
     * @return array
     */
    public function makeReport($cadbNumber, $problem){

        $response = [];

        $reporterNetworkId = Operator::ORANGE_NETWORK_ID;

        $prResponse = $this->reportProblem($reporterNetworkId, $cadbNumber, $problem);

        // Verify response

        if($prResponse->success){

            $this->db->trans_start();

            // Insert Error table

            $eParams = array(
                'errorReportId' => $prResponse->returnTransaction->errorReportId,
                'cadbNumber' => $prResponse->returnTransaction->cadbNumber,
                'problem' => $prResponse->returnTransaction->problem,
                'reporterNetworkId' => Operator::ORANGE_NETWORK_ID,
                'submissionDateTime' => $prResponse->returnTransaction->submissionDateTime
            );

            $this->Error_model->add_error($eParams);

            $this->db->trans_complete();

            $response['success'] = true;

            if ($this->db->trans_status() === FALSE) {

                $emailService = new EmailService();
                $emailService->adminErrorReport('ERROR_REPORTED_BUT_DB_FILLED_INCOMPLETE', []);

            }

            $response['message'] = 'Error has been REPORTED successfully!';

        }

        else{

            $fault = $prResponse->error;

            $emailService = new EmailService();

            $response['success'] = false;

            switch ($fault) {
                // Errors
                case Fault::UNKNOWN_NUMBER:
                    $response['message'] = 'Number in request is not recognized as number';
                    break;
                case Fault::UNKNOWN_MANAGED_NUMBER:
                    $response['message'] = 'Number in request is not managed by CADB';
                    break;
                case Fault::INVALID_OPERATOR_FAULT:
                    $response['message'] = 'Operator not active';
                    break;

                // Terminal Error Processes
                case Fault::INVALID_REQUEST_FORMAT:
                case Fault::ACTION_NOT_AUTHORIZED:
                    $emailService->adminErrorReport($fault, []);
                    $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
                    break;

                default:
                    $emailService->adminErrorReport($fault, []);
                    $response['message'] = 'Fatal Error Encountered. Please contact Administrator';
            }
        }

        return $response;
    }

}