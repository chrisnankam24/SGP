<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 11:27 PM
 */

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . "controllers/cadb/Fault.php";
require_once APPPATH . "controllers/cadb/PortingOperationService.php";

class Client extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Disable wsdl cache
        ini_set("soap.wsdl_cache_enabled", "0");

        // Define soap client object
        $client = new SoapClient(__DIR__ . '/wsdl/PortingOperationService.wsdl', array(
            "location" => 'http://localhost/SGP/index.php/cadb/PortingOperationService',
            "trace" => true
        ));

//        $request = array(
//            'recipientNrn' => '',
//            'donorNrn' => '',
//            'recipientSubmissionDateTime' => '',
//            'preferredPortingDateTime' => '',
//            'rio' => '',
//            'numberRanges' => array('numberRange' => array('startNumber' => '', 'endNumber' => '')),
//            'subscriberInfo' => '',
//        );

        $request = new orderRequest();

        $numRange = new numberRangeType();
        $numRange->endNumber = '';
        $numRange->startNumber = '';

        $request->donorNrn = '';
        $request->recipientNrn = '';
        $request->recipientSubmissionDateTime = '';
        $request->preferredPortingDateTime = '';
        $request->rio = '';
        $request->numberRanges = array($numRange);

        //$r = json_decode(json_encode($request), true);

        $response = $client->order($request);

        //print_r($response);
        print_r($response);

        echo($client->__getLastRequest());

        //var_dump($client->__getFunctions());

    }

}