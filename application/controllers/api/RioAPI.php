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
        // Webservice File descriptor load
    }

    /**
     * Returns RIO of individual Number
     */
    public function getRioIndividualMSISDN(){

        $response = [];

        if(isset($_POST)) {

            $msisdn = $this->input->post('MSISDN');

            $response = $this->getIndivRIO($msisdn);

        }else{

            $response['success'] = false;
            $response['message'] = 'No MSISDN found';

        }

        $this->send_response($response);
    }

    /**
     * Returns RIOs calculated from MSISDNs in file
     */
    public function getRioFile()
    {
        /*$config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = 100;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('rioFile')) {
            $error = array('error' => $this->upload->display_errors());
        }else
        {
            $data = array('upload_data' => $this->upload->data());

            $file_name = $data['upload_data']['file_name'];

        }*/

        $response = [];

        if(isset($_POST)) {

            $file_name = $this->input->post('fileName');

            if($file_name != ''){
                $row = 1;

                $msisdns = array();

                if (($handle = fopen(FCPATH . 'uploads/' .$file_name, "r")) !== FALSE) {

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($row == 1){
                            // Check if header Ok
                            if(strtolower($data[0]) != 'msisdn'){
                                $response['success'] = false;
                                $response['message'] = 'Invalid file content format. First Column must be name <MSISDN>. 
                                                        If you have difficulties creating file, please contact administrator';
                            }
                            $row++;
                        }else{
                            $msisdns[] = $data[0]; // MSISDN
                        }
                    }

                    fclose($handle);
                }

                $response['success'] = true;
                $response['data'] = RIO::getBulkRio($msisdns);

            }else{
                $response['success'] = false;
                $response['message'] = 'No file name found';
            }

        }else{

            $response['success'] = false;
            $response['message'] = 'No file name found';

        }

        $this->send_response($response);


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