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
 * Base class for all USSD related functionalities
 */
class USSD extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Called by USSD gateway. Retrieves subscriber MSISDN, generates RIO and sends it back.
     */
    public function index(){

        $msisdn = null;

        $headers = getallheaders();

        $msisdn = $headers['User-MSISDN'];

        $language = $headers['User-Language'];

        $template = '';

        if($language == 'fr'){

            // Load fr template
            $template = file_get_contents(__DIR__ . '/fr_ussd_template_rio.txt');

        }else{

            // Load en template
            $template = file_get_contents(__DIR__ . '/en_ussd_template_rio.txt');
        }

        $rio = RIO::get_rio($msisdn);

        // Set Subscriber RIO
        $message = str_replace('[rio]', $rio, $template);

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