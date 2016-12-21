<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:43 PM
 */

namespace ProblemService\ProblemNotification;

use Common;
use ProblemService\Problem as Problem;


////////////////////// Problem Notification Types and Functions
/**
 * Class notifyProblemReportedRequest
 * @package ProblemService\ProblemNotification
 */
class notifyProblemReportedRequest {

    /**
     * @var Problem\problemReportType
     */
    public $problemReport;

    /**
     * @var \nrnType
     */
    public $nrn;

    /**
     * @var \DateTime
     */
    public $routingChangeDateTime;

    /**
     * @var \processType
     */
    public $processType;

}

/**
 * Class notifyProblemReportedResponse
 * @package ProblemService\ProblemNotification
 */
class notifyProblemReportedResponse {

}