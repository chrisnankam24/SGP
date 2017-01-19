<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";
require_once APPPATH . '/controllers/rio/RIO.php';
require_once APPPATH . '/controllers/sms/SMS.php';

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 8:46 AM
 */

/**
 * Base class for all USSD related functionalities
 */
class USSD extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('FileLog_model');
        $this->load->model('Ussdsmsnotification_model');

    }

    /**
     * Called by USSD gateway. Retrieves subscriber MSISDN, generates RIO and sends it back.
     */
    public function index(){

        $msisdn = null;

        $headers = getallheaders();

        $msisdn = substr($headers['User-MSISDN'], 3);

        $language = $headers['User-Language'];

        $template = '';

        $rio = RIO::getPersonalRIO($msisdn);

        if($rio){

            if($language == 'fr'){

                // Load fr template
                $template = file_get_contents(__DIR__ . '/fr_ussd_template_rio.txt');

            }else{

                // Load en template
                $template = file_get_contents(__DIR__ . '/en_ussd_template_rio.txt');
            }

            // Set Subscriber RIO
            $message = str_replace('[rio]', $rio, $template);


        }
        else{

            if($language == 'fr'){

                // Load fr template
                $template = file_get_contents(__DIR__ . '/fr_error_ussd_template_rio.txt.txt');

            }else{

                // Load en template
                $template = file_get_contents(__DIR__ . '/en_error_ussd_template_rio.txt.txt');
            }

            // Set Subscriber RIO
            $message = str_replace('[rio]', $rio, $template);


        }

        // Create USSD response using cellflash specs
        $dom = new DOMDocument('1.0', 'UTF-8');

        $imp = new DOMImplementation;
        // Creates a DOMDocumentType instance
        $dtd = $imp->createDocumentType('pages', '', 'cellflash-1.3.dtd');
        $dom->appendChild($dtd);

        $dom->formatOutput = true;

        $pages = $dom->createElement('pages');
        $page = $dom->createElement('page');
        $pages->appendChild($page);
        $page->setAttribute('nav', 'end');
        $dom->appendChild($pages);

        $frag= $dom->createDocumentFragment();
        $br=$dom->createElement('br');
        $txt=$dom->createTextNode($message);

        $frag->appendChild( $br );
        $frag->appendChild( $txt );
        $page->appendChild($frag);
        $response = $dom->saveXML();

        self::send_response($response);

        // Send USSD SMS and save in DB
        $response = SMS::USSD_SMS($message, $msisdn);

        if($response['success']){
            // Save SMS in USSDNotificationTable in state SENT

            $smsNotificationparams = array(
                'message' => $message,
                'creationDateTime' => date('c'),
                'status' => smsState::SENT,
                'msisdn' => '237' . $msisdn,
                'attemptCount' => 1,
                'sendDateTime' => date('c')
            );

        }else{
            // Save SMS in USSDNotificationTable in state PENDING

            $smsNotificationparams = array(
                'message' => $message,
                'creationDateTime' => date('c'),
                'msisdn' => '237' . $msisdn,
                'status' => smsState::PENDING,
                'attemptCount' => 1
            );

        }

        $this->Ussdsmsnotification_model->add_ussdsmsnotification($smsNotificationparams);

    }

    /**
     * Sends XML response to USSD Gateway
     * @param $response
     */
    private static function send_response($response)
    {
        header("Content-type: text/xml");
        header("Content-Transfer-Encoding: 8bit");
        echo $response;
    }

}