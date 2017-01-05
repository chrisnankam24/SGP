<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/controllers/rio/RIO.php';

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

    const SVI_FRENCH_LANGUAGE = 'FR';
    const SVI_ENGLISH_LANGUAGE = 'EN';

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Called by SVI/CTI gateway. Retrieves subscriber MSISDN, generates RIO and sends it back.
     */
    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/RIOMock.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }


    /**
     *
     * @param $getRioRequest
     * @return getRioResponse
     */
    public function getRio($getRioRequest){

        $msisdn = substr($getRioRequest->phoneNumber, 3);;

        $rioWithInfo = RIO::getRIOAndInfo($msisdn);

        $response = new getRioResponse();

        if($rioWithInfo){

            $response->return->returnCode = $rioWithInfo['clientType'];
            $response->return->rioNumber = $rioWithInfo['rio'];

            if($rioWithInfo['language'] == 'ANGLAIS' ){
                $response->return->language = SVI::SVI_ENGLISH_LANGUAGE;
            }else{
                $response->return->language = SVI::SVI_FRENCH_LANGUAGE;
            }

        }else{

            $response->return->returnCode = '2';

        }

        // TODO: Send SMS to Client in appropriate language

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
    public $language;

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