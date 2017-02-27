<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "controllers/bscs/BscsOperationService.php";
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

    /**
     * Returns RIO of given subscriber
     * @param $msisdn string|false
     */
    public static function get_rio($msisdn){

        $bscsOperationService = new BscsOperationService();
        $subsInfo = $bscsOperationService->loadNumberInfo($msisdn);

        if($subsInfo){

            return RIO::calculateRIO($subsInfo, $msisdn);

        }else{
            return false;
        }

    }

    /**
     * Returns RIO for personal number
     * @param $msisdn
     * @return bool|string
     */
    public static function getPersonalRIO($msisdn){

        $bscsOperationService = new BscsOperationService();
        $subsInfo = $bscsOperationService->loadNumberInfo($msisdn);

        if($subsInfo){

            if($subsInfo['TYPE_CLIENT'] == 'C'){

                return RIO::calculateRIO($subsInfo, $msisdn);

            }else{

                return false;

            }

        }else{
            return false;
        }

    }

    /**
     * Performs Bulk RIO Calculations
     * @param $msisdns
     * @return array
     */
    public static function getBulkRio($msisdns){

        $response = [];
        $bscsOperationService = new BscsOperationService();

        foreach ($msisdns as $msisdn){

            $subsInfo = $bscsOperationService->loadNumberInfo($msisdn);

            $result = false;

            if($subsInfo){

                $result = RIO::calculateRIO($subsInfo, $msisdn);

            }else{

            }

            if($result){

                $response[] = array('MSISDN' => $msisdn, 'success' => true, 'rio' => $result);

            }else{

                $response[] = array('MSISDN' => $msisdn, 'success' => false, 'message' => 'Unable to get RIO corresponding to MSISDN');

            }
        }

        return $response;
    }

    /**
     * Called by SVI, returns RIO and info on subscriber
     * @param $msisdn
     * @return array|bool
     */
    public static function getRIOAndInfo($msisdn){

        $response = [];

        $bscsOperationService = new BscsOperationService();
        $subsInfo = $bscsOperationService->loadNumberInfo($msisdn);

        if($subsInfo){

            $response['rio'] = RIO::calculateRIO($subsInfo, $msisdn);

            $response['language'] = $subsInfo['LANGUE'];

            if($subsInfo['TYPE_CLIENT'] == 'C'){

                $response['clientType'] = '0';

            }else{

                $response['clientType'] = '1';

            }

            return $response;

        }else{
            return false;
        }
    }

    private static function calculateRIO($subsInfo, $msisdn){

        $contractId = $subsInfo['CONTRACT_ID'];

        if(strlen($msisdn) == 12){

            $msisdn = substr($msisdn, 3);

        }

        // RIO == OOQRRRRRRCCC

        $OO = Operator::ORANGE_NETWORK_ID; // Operator ID

        $Q = 'P'; // Subscriber Type :: P == Personal / E == Enterprise

        if($subsInfo['TYPE_CLIENT'] == 'B'){

            $Q = 'E';

        }

        $Q_NC = $Q == 'E' ? '0' : '1';

        $RRRRRR = strtoupper(substr(str_pad(base_convert($contractId, 10, 36), 6, '0', STR_PAD_LEFT), 0, 6)); // Generated

        $CCC = strtoupper(substr(base_convert(Operator::ORANGE_NETWORK_ID_NUMBER . $Q_NC . $msisdn, 10, 36), 0, 3)); // Encrypted Check sum

        $rio = $OO . $Q . $RRRRRR . $CCC;

        return $rio;
    }

    private static function calculateRIOv2($subsInfo, $msisdn){

        $account_num = $subsInfo['NUM_COMPTE'];

        if(strlen($msisdn) == 12){

            $msisdn = substr($msisdn, 3);

        }

        // RIO == OOQRRRRRRCCC

        $OO = Operator::ORANGE_NETWORK_ID; // Operator ID

        $Q = 'P'; // Subscriber Type :: P == Personal / E == Enterprise

        if($subsInfo['TYPE_CLIENT'] == 'B'){

            $Q = 'E';

        }

        $Q_NC = $Q == 'E' ? '0' : '1';

        $RRRRRR = strtoupper(substr(str_pad(base_convert(explode($account_num, '.')[1], 10, 36), 6, '0', STR_PAD_LEFT), 0, 6)); // Generated

        $CCC = strtoupper(substr(base_convert(Operator::ORANGE_NETWORK_ID_NUMBER . $Q_NC . $msisdn, 10, 36), 0, 3)); // Encrypted Check sum

        $rio = $OO . $Q . $RRRRRR . $CCC;

        return $rio;
    }

}