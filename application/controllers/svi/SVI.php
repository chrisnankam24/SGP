<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/controllers/rio/RIO.php';
require_once APPPATH . "controllers/cadb/Common.php";

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 8:46 AM
 */

/**
 * Base class for all SVI/CTI related functionalities
 */
class SVI extends CI_Controller {

    public function __construct()
    {

        parent::__construct();

        $this->load->model('FileLog_model');
        $this->load->model('Ussdsmsnotification_model');

    }

    /**
     * Called by SVI/CTI gateway. Retrieves subscriber MSISDN, generates RIO and sends it back.
     */
    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/RIOMock_avec_langue.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

    }

    /**
     *
     * @param $getRioRequest
     * @return getRioResponse
     */
    public function getRio($getRioRequest){

        $msisdn = $getRioRequest->phoneNumber;

        if(strlen($msisdn) == 12){

            $msisdn = substr($msisdn, 3);

        }

        $this->fileLogAction('8002', 'SVI', 'SVI Request received from ' . $msisdn);

        $rioWithInfo = RIO::getRIOAndInfo($msisdn);

        $response = new getRioResponse();

        $response->return = new RIOInfo();

        if($rioWithInfo){

            $response->return->returnCode = $rioWithInfo['clientType'];
            $response->return->rioNumber = $rioWithInfo['rio'];

            if($rioWithInfo['language'] != '' ){
                $response->return->langue = $rioWithInfo['language'];
            }

            $smsNotificationparams = array(
                'message' => $rioWithInfo['rio'],
                'creationDateTime' => date('Y-m-d\TH:i:s'),
                'status' => smsState::SENT,
                'msisdn' => '237' . $msisdn,
                'attemptCount' => 1,
                'sendDateTime' => date('Y-m-d\TH:i:s'),
                'source' => rioSource::IVR
            );

            $this->Ussdsmsnotification_model->add_ussdsmsnotification($smsNotificationparams);

        }else{

            $response->return->returnCode = '2';

        }

        $this->fileLogAction('8002', 'SVI', 'SVI Response sent to ' . $msisdn . ' :: ' . json_encode($response));

        return $response;
    }

}

class RIOInfo {

    /**
     * @var string
     */
    public $returnCode;

    /**
     * @var string
     */
    public $rioNumber;

    /**
     * @var string
     */
    public $langue;

}

/**
 * Class getRioRequest
 */
class getRioRequest {

    /**
     * @var string
     */
    public $phoneNumber;

}

/**
 * Class getRioResponse
 */
class getRioResponse {

    /**
     * @var RIOInfo
     */
    public $return;

}