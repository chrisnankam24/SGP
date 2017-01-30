<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:56 AM
 */

namespace BscsService\BscsTypes;

//////////////////////// Data Types

/**
 * Class createContract
 * @package BscsOperationService\BscsTypes
 */
class createContract {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var long
     */
    public $customerId;

    /**
     * @var string
     */
    public $valid_from;

    /**
     * @var long
     */
    public $dealerId;

    /**
     * @var string
     */
    public $serialNumber;

    /**
     * @var  long
     */
    public $i_tmcode;

    /**
     * @var string
     */
    public $i_sn_string;

    /**
     * @var string
     */
    public $i_owner_birthdate;

    /**
     * @var string
     */
    public $imei;

}

/**
 * Class createContractResponse
 * @package BscsOperationService\BscsTypes
 */
class createContractResponse {

    /**
     * @var long
     */
    public $createContractReturn;

}

/**
 * Class updateContractStatus
 * @package BscsOperationService\BscsTypes
 */
class updateContractStatus {
    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var long
     */
    public $i_co_id;

    /**
     * @var int
     */
    public $i_new_status;

    /**
     * @var long
     */
    public $i_reason;
}

/**
 * Class updateContractStatusResponse
 * @package BscsOperationService\BscsTypes
 */
class updateContractStatusResponse {

}

/**
 * Class transfertContract
 * @package BscsOperationService\BscsTypes
 */
class transfertContract {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var long
     */
    public $customerId;

    /**
     * @var long
     */
    public $i_customer_id;

    /**
     * @var long
     */
    public $reason_code;

    /**
     * @var long
     */
    public $tmcode;

    /**
     * @var string
     */
    public $imei;

}

/**
 * Class transfertContractResponse
 * @package BscsOperationService\BscsTypes
 */
class transfertContractResponse {

    /**
     * @var long
     */
    public $transfertContractReturn;

}

/**
 * Class consultContract
 * @package BscsOperationService\BscsTypes
 */
class consultContract {

    /**
    * @var boolean
    */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var string
     */
    public $i_msisdn;

    /**
     * @var string
     */
    public $i_custcode;

}

/**
 * Class consultContractResponse
 * @package BscsOperationService\BscsTypes
 */
class consultContractResponse {

    /**
     * @var anyType
     */
    public $consultContractReturn;

}

/**
 * Class deleteContract
 * @package BscsOperationService\BscsTypes
 */
class deleteContract {
    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var long
     */
    public $contractId;

    /**
     * @var int
     */
    public $coDevRetention;

    /**
     * @var int
     */
    public $coDnRetention;

    /**
     * @var int
     */
    public $coPortRetention;

}

/**
 * Class deleteContractResponse
 * @package BscsOperationService\BscsTypes
 */
class deleteContractResponse {

}

/**
 * Class logon
 * @package BscsOperationService\BscsTypes
 */
class logon {

    /**
     * @var string
     */
    public $cmsUserName;

    /**
     * @var string
     */
    public $cmsPassword;

    /**
     * @var string
     */
    public $endUserName;

}

/**
 * Class logonResponse
 * @package BscsOperationService\BscsTypes
 */
class logonResponse {

}

/**
 * Class logout
 * @package BscsOperationService\BscsTypes
 */
class logout {

}

/**
 * Class logoutResponse
 * @package BscsOperationService\BscsTypes
 */
class logoutResponse {

}

// MSISDN Management End Point
/**
 * Class ImportMSISDN
 * @package BscsOperationService\BscsTypes
 */
class ImportMSISDN {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var string
     */
    public $MSISDN;

    /**
     * @var long
     */
    public $NPCODE;

    /**
     * @var long
     */
    public $SRC_PLCODE;

    /**
     * @var int
     */
    public $HMCODE;

}

/**
 * Class ImportMSISDNResponse
 * @package BscsOperationService\BscsTypes
 */
class ImportMSISDNResponse {

}

/**
 * Class ChangeImportMSISDN
 * @package BscsOperationService\BscsTypes
 */
class ChangeImportMSISDN {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var long
     */
    public $CO_ID;

    /**
     * @var string
     */
    public $MSISDN;

    /**
     * @var string
     */
    public $MSISDN_TMP;

}

/**
 * Class ChangeImportMSISDNResponse
 * @package BscsOperationService\BscsTypes
 */
class ChangeImportMSISDNResponse {

}

/**
 * Class ReturnMSISDN
 * @package BscsOperationService\BscsTypes
 */
class ReturnMSISDN {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var string
     */
    public $PHONE_NUMBER;

    /**
     * @var long
     */
    public $NPCODE;

    /**
     * @var long
     */
    public $SRC_PLCODE;

}

/**
 * Class ReturnMSISDNResponse
 * @package BscsOperationService\BscsTypes
 */
class ReturnMSISDNResponse {

}

/**
 * Class ExportMSISDN
 * @package BscsOperationService\BscsTypes
 */
class ExportMSISDN {

    /**
     * @var boolean
     */
    public $autoCommit;

    /**
     * @var string
     */
    public $endUserName;

    /**
     * @var string
     */
    public $MSISDN;

    /**
     * @var long
     */
    public $NPCODE;

    /**
     * @var long
     */
    public $DEST_PLCODE;

}

/**
 * Class ExportMSISDNResponse
 * @package BscsOperationService\BscsTypes
 */
class ExportMSISDNResponse {

}