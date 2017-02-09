<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/18/2016
 * Time: 7:13 PM
 */

class Fault {
    ///////////////////////// Constants Definition

    // System defined faults
    const CLIENT_INIT_FAULT = 'Could not initialize client';

    // WS defined faults

    // Common Fault
    const ACTION_NOT_AVAILABLE = 'actionNotAvailableFault';
    const INVALID_CADB_ID = 'invalidCadbIdFault';
    const INVALID_RETURN_ID = 'invalidReturnIdFault';
    const RETURN_ACTION_NOT_AVAILABLE = 'returnActionNotAvailableFault';
    const MULTIPLE_PRIMARY_OWNER = 'multiplePrimaryOwnersFault';
    const NUMBER_NOT_PORTED = 'numberNotPortedFault';
    const NUMBER_QUANTITY_LIMIT_EXCEEDED = 'numberQuantityLimitExceededFault';
    const UNKNOWN_MANAGED_NUMBER = 'unknownManagedNumberFault';
    const INVALID_ROLLBACK_ID = 'invalidRollbackIdFault';
    const ROLLBACK_ACTION_NOT_AVAILABLE = 'rollbackActionNotAvailableFault';
    const UNKNOWN_PORTING_ID = 'unknownPortingIdFault';
    const ROLLBACK_NOT_ALLOWED = 'rollbackNotAllowedFault';
    const PORTING_ACTION_NOT_AVAILABLE = 'portingActionNotAvailableFault';
    const RIO_NOT_VALID = 'rioNotValidFault';
    const SUBSCRIBER_DATA_MISSING = 'subscriberDataMissingFault';
    const PORTING_NOT_ALLOWED_REQUESTS = 'portingNotAllowedRequestsFault';
    const TOO_NEAR_PORTED_PERIOD = 'tooNearPortedPeriodFault';
    const NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED = 'numberRangeQuantityLimitExceededFault';
    const NUMBER_NOT_OWNED_BY_OPERATOR = 'numberNotOwnedByOperatorFault';
    const NUMBER_RESERVED_BY_PROCESS = 'numberReservedByProcessFault';
    const NUMBER_RANGES_OVERLAP = 'numberRangesOverlapFault';
    const INVALID_PORTING_DATE_AND_TIME = 'invalidPortingDateAndTimeFault';
    const INVALID_PORTING_ID = 'invalidPortingIdFault';
    const COUNT_OVER_MAX_COUNT_LIMIT = 'countOverMaxCountLimitFault';
    const CAUSE_MISSING = 'causeMissingFault';
    const UNKNOWN_NUMBER = 'unknownNumberFault';
    const INVALID_REQUEST_FORMAT = 'invalidRequestFormatFault';
    const ACTION_NOT_AUTHORIZED = 'actionNotAuthorizedFault';
    const INVALID_OPERATOR_FAULT = 'invalidOperatorFault';
    // notification Fault
    const LDB_ADMINISTRATION_SERVICE = 'ldbAdministrationServiceFault';

    // BSCS Fault
    const SIGNATURE_MISMATCH = 'com.orangecaraibe.commonapi.corba.cms.SignatureMismatchException';
    const PARAMETER_LIST = 'com.orangecaraibe.commonapi.corba.cms.ParameterListException';
    const UNKNOWN_COMMAND = 'com.orangecaraibe.commonapi.corba.cms.UnknownCommandFault';
    const SERVICE_BREAK_DOWN = 'com.orangecaraibe.commonapi.corba.cms.ServiceBreakDownFault';
    const CMS_EXECUTION = 'com.orangecaraibe.commonapi.corba.cms.CMSExecutionFault';
    const INVALID_PARAMETER_TYPE = 'com.orangecaraibe.commonapi.corba.cms.InvalidParameterTypeException';
    const DENIED_ACCESS = 'com.orangecaraibe.commonapi.corba.cms.DeniedAccessFault';
    const SERVER_NOT_FOUND = 'com.orangecaraibe.commonapi.corba.cms.ServerNotFoundFault';
    const POST_CONNECTION_INITIALIZATION = 'com.orangecaraibe.commonapi.corba.cms.PostConnectInitializationFault';

    // BSCS Fault names
    const SIGNATURE_MISMATCH_CODE = 'fault';
    const PARAMETER_LIST_CODE = 'fault1';
    const UNKNOWN_COMMAND_CODE = 'fault2';
    const SERVICE_BREAK_DOWN_CODE = 'fault3';
    const CMS_EXECUTION_CODE = 'fault4';
    const INVALID_PARAMETER_TYPE_CODE = 'fault5';
    const DENIED_ACCESS_CODE = 'fault6';
    const SERVER_NOT_FOUND_CODE = 'fault7';
    const POST_CONNECTION_INITIALIZATION_CODE = 'fault8';
}

///////////////////////// Fault Message Types

/**
 * Class faultMessageType
 */
abstract class faultMessageType extends SoapFault {
    // message variable is implicit to Exception class from which SoapFault is a subclass

    public function __construct($message, $faultname)
    {
        // Hack from stackOverflow question 25710674. Lesson :: Documentation says detail == string, passing object works
        $detail = new StdClass();
        $detail->message = $message;
        parent::SoapFault('Client', $message, "", $detail, $faultname);
    }
}

/**
 * Class ldbAdministrationServiceFaultMessageType
 */
abstract class ldbAdministrationServiceFaultMessageType extends faultMessageType {

    public function __construct($faultname, $faultmessage)
    {
        parent::__construct($faultmessage, $faultname);
    }

}

/**
 * Class operatorNotActiveFaultMessageType
 */
abstract class invalidOperatorFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid Operator', $faultname);
    }
}

/**
 * Class actionNotAuthorizedFaultMessageType
 */
abstract class actionNotAuthorizedFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('WS user not authorized', $faultname);
    }

}

/**
 * Class invalidRequestFormatFaultMessageType
 */
abstract class invalidRequestFormatFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid request format', $faultname);
    }

}

/**
 * Class unknownNumberFaultMessageType
 */
abstract class unknownNumberFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Unknown Number', $faultname);
    }

}

/**
 * Class causeMissingFaultMessageType
 */
abstract class causeMissingFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Cause missing', $faultname);
    }

}

/**
 * Class countOverMaxCountLimitFaultMessageType
 */
abstract class countOverMaxCountLimitFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Count over Max count limit', $faultname);
    }

}

/**
 * Class invalidPortingIdFaultMessageType
 */
abstract class invalidPortingIdFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Porting ID is malformed or does not exist', $faultname);
    }

}

/**
 * Class invalidPortingDateAndTimeFaultMessageType
 */
abstract class invalidPortingDateAndTimeFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid porting Id date time', $faultname);
    }

}

/**
 * Class numberRangesOverlapFaultMessageType
 */
abstract class numberRangesOverlapFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number Ranges Overlap', $faultname);
    }

}

/**
 * Class numberReservedByProcessFaultMessageType
 */
abstract class numberReservedByProcessFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number reserved by Process', $faultname);
    }

}

/**
 * Class numberNotOwnedByOperatorFaultMessageType
 */
abstract class numberNotOwnedByOperatorFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number not owned by operator', $faultname);
    }

}

/**
 * Class numberRangeQuantityLimitExceededFaultMessageType
 */
abstract class numberRangeQuantityLimitExceededFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number range quantity limit exceeded', $faultname);
    }

}

/**
 * Class tooNearPortedPeriodFaultMessageType
 */
abstract class tooNearPortedPeriodFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Too near ported period', $faultname);
    }

}

/**
 * Class portingNotAllowedRequestsFaultMessageType
 */
abstract class portingNotAllowedRequestsFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Porting not allowed', $faultname);
    }

}

/**
 * Class subscriberDataMissingFaultMessageType
 */
abstract class subscriberDataMissingFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Subscriber data missing', $faultname);
    }

}

/**
 * Class rioNotValidFaultMessageType
 */
abstract class rioNotValidFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('RIO not valid', $faultname);
    }

}

/**
 * Class portingActionNotAvailableFaultMessageType
 */
abstract class portingActionNotAvailableFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Porting action not allowed', $faultname);
    }

}

/**
 * Class rollbackNotAllowedFaultMessageType
 */
abstract class rollbackNotAllowedFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Rollback not Allowed', $faultname);
    }

}

/**
 * Class unknownPortingIdFaultMessageType
 */
abstract class unknownPortingIdFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Unknown porting Id', $faultname);
    }

}

/**
 * Class rollbackActionNotAvailableFaultMessageType
 */
abstract class rollbackActionNotAvailableFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Rollback Action not Available', $faultname);
    }

}

/**
 * Class invalidRollbackIdFaultMessageType
 */
abstract class invalidRollbackIdFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid rollback Id', $faultname);
    }

}

/**
 * Class unknownManagedNumberFaultMessageType
 */
abstract class unknownManagedNumberFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Unknown managed number', $faultname);
    }

}

/**
 * Class numberQuantityLimitExceededFaultMessageType
 */
abstract class numberQuantityLimitExceededFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number quantity limit exceeded', $faultname);
    }

}

/**
 * Class numberNotPortedFaultMessageType
 */
abstract class numberNotPortedFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Number not ported', $faultname);
    }

}

/**
 * Class multiplePrimaryOwnersFaultMessageType
 */
abstract class multiplePrimaryOwnersFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Multiple primary owners', $faultname);
    }

}

/**
 * Class returnActionNotAvailableFaultMessageType
 */
abstract class returnActionNotAvailableFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Return action not available', $faultname);
    }

}

/**
 * Class invalidReturnIdFaultMessageType
 */
abstract class invalidReturnIdFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid return Id', $faultname);
    }

}

/**
 * Class invalidCadbIdFaultMessageType
 */
abstract class invalidCadbIdFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Invalid Cadb Id', $faultname);
    }

}

/**
 * Class actionNotAvailableFaultMessageType
 */
abstract class actionNotAvailableFaultMessageType extends faultMessageType {

    public function __construct($faultname)
    {
        parent::__construct('Action not Available', $faultname);
    }

}

///////////////////////// Fault Types

/**
 * Class ldbAdministrationServiceFault
 */
class ldbAdministrationServiceFault extends ldbAdministrationServiceFaultMessageType {

    public function __construct($faultmessage = 'LDB Internal error')
    {
        parent::__construct(Fault::LDB_ADMINISTRATION_SERVICE, $faultmessage);
    }

}

/**
 * Class invalidOperatorFault
 */
class invalidOperatorFault extends invalidOperatorFaultMessageType  {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_OPERATOR_FAULT);
    }

}

/**
 * Class actionNotAuthorizedFault
 */
class actionNotAuthorizedFault extends actionNotAuthorizedFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::ACTION_NOT_AUTHORIZED);
    }

}

/**
 * Class invalidRequestFormatFault
 */
class invalidRequestFormatFault extends invalidRequestFormatFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_REQUEST_FORMAT);
    }

}

/**
 * Class unknownNumberFault
 */
class unknownNumberFault extends unknownNumberFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::UNKNOWN_NUMBER);
    }

}

/**
 * Class causeMissingFault
 */
class causeMissingFault extends causeMissingFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::CAUSE_MISSING);
    }

}

/**
 * Class countOverMaxCountLimitFault
 */
class countOverMaxCountLimitFault extends countOverMaxCountLimitFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::COUNT_OVER_MAX_COUNT_LIMIT);
    }

}

/**
 * Class invalidPortingIdFault
 */
class invalidPortingIdFault extends invalidPortingIdFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_PORTING_ID);
    }

}

/**
 * Class invalidPortingDateAndTimeFault
 */
class invalidPortingDateAndTimeFault extends invalidPortingDateAndTimeFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_PORTING_DATE_AND_TIME);
    }

}

/**
 * Class numberRangesOverlapFault
 */
class numberRangesOverlapFault extends numberRangesOverlapFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_RANGES_OVERLAP);
    }

}

/**
 * Class numberReservedByProcessFault
 */
class numberReservedByProcessFault extends numberReservedByProcessFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_RESERVED_BY_PROCESS);
    }

}

/**
 * Class numberNotOwnedByOperatorFault
 */
class numberNotOwnedByOperatorFault extends numberNotOwnedByOperatorFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_NOT_OWNED_BY_OPERATOR);
    }

}

/**
 * Class numberRangeQuantityLimitExceededFault
 */
class numberRangeQuantityLimitExceededFault extends numberRangeQuantityLimitExceededFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_RANGE_QUANTITY_LIMIT_EXCEEDED);
    }

}

/**
 * Class tooNearPortedPeriodFault
 */
class tooNearPortedPeriodFault extends tooNearPortedPeriodFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::TOO_NEAR_PORTED_PERIOD);
    }

}

/**
 * Class portingNotAllowedRequestsFault
 */
class portingNotAllowedRequestsFault extends portingNotAllowedRequestsFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::PORTING_NOT_ALLOWED_REQUESTS);
    }

}

/**
 * Class subscriberDataMissingFault
 */
class subscriberDataMissingFault extends subscriberDataMissingFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::SUBSCRIBER_DATA_MISSING);
    }

}

/**
 * Class rioNotValidFault
 */
class rioNotValidFault extends rioNotValidFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::RIO_NOT_VALID);
    }

}

/**
 * Class portingActionNotAvailableFault
 */
class portingActionNotAvailableFault extends portingActionNotAvailableFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::PORTING_ACTION_NOT_AVAILABLE);
    }

}

/**
 * Class rollbackNotAllowedFault
 */
class rollbackNotAllowedFault extends rollbackNotAllowedFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::ROLLBACK_NOT_ALLOWED);
    }

}

/**
 * Class unknownPortingIdFault
 */
class unknownPortingIdFault extends unknownPortingIdFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::UNKNOWN_PORTING_ID);
    }

}

/**
 * Class rollbackActionNotAvailableFault
 */
class rollbackActionNotAvailableFault extends rollbackActionNotAvailableFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::ROLLBACK_ACTION_NOT_AVAILABLE);
    }

}

/**
 * Class invalidRollbackIdFault
 */
class invalidRollbackIdFault extends invalidRollbackIdFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_ROLLBACK_ID);
    }

}

/**
 * Class unknownManagedNumberFault
 */
class unknownManagedNumberFault extends unknownManagedNumberFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::UNKNOWN_MANAGED_NUMBER);
    }

}

/**
 * Class numberQuantityLimitExceededFault
 */
class numberQuantityLimitExceededFault extends numberQuantityLimitExceededFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_QUANTITY_LIMIT_EXCEEDED);
    }

}

/**
 * Class numberNotPortedFault
 */
class numberNotPortedFault extends numberNotPortedFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::NUMBER_NOT_PORTED);
    }

}

/**
 * Class multiplePrimaryOwnersFault
 */
class multiplePrimaryOwnersFault extends multiplePrimaryOwnersFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::MULTIPLE_PRIMARY_OWNER);
    }

}

/**
 * Class returnActionNotAvailableFault
 */
class returnActionNotAvailableFault extends returnActionNotAvailableFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::RETURN_ACTION_NOT_AVAILABLE);
    }

}

/**
 * Class invalidReturnIdFault
 */
class invalidReturnIdFault extends invalidReturnIdFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_RETURN_ID);
    }

}

/**
 * Class invalidCadbIdFault
 */
class invalidCadbIdFault extends invalidCadbIdFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::INVALID_CADB_ID);
    }

}

/**
 * Class actionNotAvailableFault
 */
class actionNotAvailableFault extends actionNotAvailableFaultMessageType {

    public function __construct()
    {
        parent::__construct(Fault::ACTION_NOT_AVAILABLE);
    }

}

// BSCS Faults
/**
 * Class SignatureMismatchException
 */
class SignatureMismatchException extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->message = Fault::SIGNATURE_MISMATCH;
        $detail->parameterName = 'parameterName';
        $detail->problemCode = 1;
        $faultname = Fault::SIGNATURE_MISMATCH;
        parent::SoapFault('Client', $detail->message, "", $detail, $faultname);
    }
}

/**
 * Class ParameterListException
 */
class ParameterListException extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->info = Fault::PARAMETER_LIST;
        $detail->parameterName = 'parameterName';
        $detail->problemCode = 1;
        $faultname = Fault::PARAMETER_LIST;
        parent::SoapFault('Client', $detail->info, "", $detail, $faultname);
    }
}

/**
 * Class UnknownCommandFault
 */
class UnknownCommandFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->reason = Fault::UNKNOWN_COMMAND;
        $faultname = Fault::UNKNOWN_COMMAND;
        parent::SoapFault('Client', $detail->reason, "", $detail, $faultname);
    }
}

/**
 * Class ServiceBreakDownFault
 */
class ServiceBreakDownFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->reason = Fault::SERVICE_BREAK_DOWN;
        $faultname = Fault::SERVICE_BREAK_DOWN;
        parent::SoapFault('Client', $detail->reason, "", $detail, $faultname);
    }
}

/**
 * Class CMSExecutionFault
 */
class CMSExecutionFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->reason = Fault::CMS_EXECUTION;
        $faultname = Fault::CMS_EXECUTION;
        parent::SoapFault('Client', $detail->reason, "", $detail, $faultname);
    }
}

/**
 * Class InvalidParameterTypeException
 */
class InvalidParameterTypeException extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->accessMethodName = Fault::INVALID_PARAMETER_TYPE;
        $detail->parameterName = 'InvalidParameterTypeException';
        $faultname = Fault::INVALID_PARAMETER_TYPE;
        parent::SoapFault('Client', $detail->serverName, "", $detail, $faultname);
    }
}

/**
 * Class DeniedAccessFault
 */
class DeniedAccessFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->reason = Fault::DENIED_ACCESS;
        $faultname = Fault::DENIED_ACCESS;
        parent::SoapFault('Client', $detail->reason, "", $detail, $faultname);
    }
}

/**
 * Class ServerNotFoundFault
 */
class ServerNotFoundFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->serverName = Fault::SERVER_NOT_FOUND;
        $faultname = Fault::SERVER_NOT_FOUND;
        parent::SoapFault('Client', $detail->serverName, "", $detail, $faultname);
    }
}

/**
 * Class PostConnectInitializationFault
 */
class PostConnectInitializationFault extends SoapFault {

    public function __construct()
    {
        $detail = new StdClass();
        $detail->reason = Fault::POST_CONNECTION_INITIALIZATION;
        $faultname = Fault::POST_CONNECTION_INITIALIZATION;
        parent::SoapFault('Client', $detail->reason, "", $detail, $faultname);
    }
}