<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/cadb/Common.php";

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/22/2016
 * Time: 8:51 AM
 */

/**
 * Base class for all RIO related functionalities
 */
class RIO extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){
        echo self::get_rio('dsf');
    }

    /**
     * Returns RIO of given subscriber
     * @param $msisdn
     */
    public static function get_rio($msisdn){
        $contractId = '8800092';
        $msisdn = '237694975166';

        // TODO: Load ContractId from BSCS

        // RIO == OOQRRRRRRCCC

        $OO = Operator::ORANGE_NETWORK_ID; // Operator ID

        $Q = 'P'; // Subscriber Type :: P == Personal / E == Enterprise

        $Q_NC = $Q == 'E' ? '0' : '1';

        //$RRRRRR = strtoupper(str_pad(base_convert($contractId, 10, 36), 6, '0', STR_PAD_LEFT)); // Generated
        $RRRRRR = strtoupper(substr(str_pad(base_convert($contractId, 10, 36), 6, '0', STR_PAD_LEFT), 0, 6)); // Generated

        $CCC = strtoupper(substr(base_convert(Operator::ORANCE_NETWORK_ID_NUMBER . $Q_NC . $msisdn, 10, 36), 0, 3)); // Encrypted Check sum

        $rio = $OO . $Q . $RRRRRR . $CCC;

        return $rio;

    }

}