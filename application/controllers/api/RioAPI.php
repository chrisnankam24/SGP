<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/controllers/rio/RIO.php';

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/8/2016
 * Time: 8:06 AM
 */
class RioAPI extends CI_Controller {

    function _construct()
    {
        parent::__construct();

    }


    public function index(){
        $this->load->view('rio');
    }

    /**
     * Returns RIO of individual Number
     */
    public function getRioIndividualMSISDN(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $msisdn = $this->input->post('MSISDN');

            $rio = RIO::get_rio($msisdn);

            if($rio){

                $response['success'] = true;
                $response['rio'] = $rio;

            }else{
                $response['success'] = false;
                $response['message'] = 'Unable to get RIO corresponding to MSISDN';
            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No MSISDN found';

        }

        $this->send_response($response);
    }

    /**
     *
     * @param $response
     */
    private function send_response($response)
    {
        header("Content-type: text/json");
        echo json_encode($response);
    }

}