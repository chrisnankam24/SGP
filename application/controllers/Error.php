<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/8/2016
 * Time: 8:15 AM
 */
class Error extends CI_Controller {

    function _construct()
    {
        parent::__construct();

        $this->load->model('Error_model');

    }

    /**
     * API to perform Problem report request
     */
    public function reportProblem(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $reporterNetworkId = Operator::ORANGE_NETWORK_ID;
            $cadbNumber = $this->input->post('problemNumber');
            $problem = $this->input->post('problem');

            // Make report problem request

            $prOperationService = new ProblemReportOperationService();
            $prResponse = $prOperationService->reportProblem($reporterNetworkId, $cadbNumber, $problem);

            // Verify response

            if($prResponse->success){

                $prResponse = new \ProblemService\Problem\reportProblemResponse();

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

        }else{

            $response['success'] = false;
            $response['message'] = 'No/Incomplete parameters';

        }

        $this->send_response($response);

    }

    /**
     * @param $response
     */
    private function send_response($response)
    {
        header("Content-type: text/json");
        echo json_encode($response);
    }

}