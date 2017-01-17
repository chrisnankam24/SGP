<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Problem.php";
require_once APPPATH . "controllers/cadb/ProblemReportOperationService.php";

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

    }

    /**
     * API to perform Problem report request
     */
    public function reportProblem(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $cadbNumber = $this->input->post('problemNumber');
            $problem = $this->input->post('problem');
            $userId = $this->input->post('userId');

            // Make report problem request

            $prOperationService = new ProblemReportOperationService();

            $response = $prOperationService->makeReport($cadbNumber, $problem, $userId);

        }else{

            $response['success'] = false;
            $response['message'] = 'No/Incomplete parameters';

        }

        $this->send_response($response);

    }

    /**
     *
     * API for performing bulk report
     */
    public function reportBulkProblem(){

        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $errorData = $this->input->post('errorData'); // Array of error objects i.e (cadbNumber, problem)
            $userId = $this->input->post('userId');

            $response['success'] = true;
            $response['data'] = [];

            $prOperationService = new ProblemReportOperationService();

            foreach ($errorData as $errorDatum){

                $tmpResponse = $prOperationService->makeReport($errorDatum['cadbNumber'], $errorDatum['problem'], $userId);
                $tmpResponse['cadbNumber'] = $errorDatum['cadbNumber'];
                $response['data'][] = $tmpResponse;

            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No parameter found';

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