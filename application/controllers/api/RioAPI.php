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
        $this->load->helper(array('form', 'url'));
    }


    public function index(){
        $this->load->helper(array('form', 'url'));
        $this->load->view('upload_form', array('error' => ' ' ));
    }

    /**
     * Returns RIO of individual Number
     */
    public function getRioIndividualMSISDN(){
        $response = [];

        if(isset($_POST) && count($_POST) > 0) {

            $msisdn = $this->input->post('MSISDN');

            $rio = RIO::get_rio($msisdn);

            $response = $this->getIndivRIO($msisdn);

        }else{

            $response['success'] = false;
            $response['message'] = 'No MSISDN found';

        }

        $this->send_response($response);
    }

    public function getRioFile()
    {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = 100;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('rioFile')) {
            $error = array('error' => $this->upload->display_errors());
        }else
        {
            $data = array('upload_data' => $this->upload->data());

            $file_name = $data['upload_data']['file_name'];

            $totalResults = array();

            $row = 1;
            if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                $msisdns = array();
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if($row == 1){
                        $row++;
                    }else{
                        $result = array();
                        $msisdns[] = $data[0]; // MSISDN

                    }
                }

                $response = RIO::getBulkRio($msisdns);

                var_dump($response);

                fclose($handle);
            }

        }
    }

    private function getIndivRIO($msisdn){

        $response = [];

        $rio = RIO::get_rio($msisdn);

        if($rio){

            $response['success'] = true;
            $response['rio'] = $rio;

        }else{
            $response['success'] = false;
            $response['message'] = 'Unable to get RIO corresponding to MSISDN';
        }

        return $response;

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