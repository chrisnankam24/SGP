<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:34 PM
 */

namespace ProblemService\Problem;

use Common;

////////////////////// Problem Types and Functions

/**
 * Class baseProblemReportType
 * @package ProblemService\Problem
 */
abstract class baseProblemReportType {

    /**
     * @var string
     */
    public $reporterNetworkId;

    /**
     * @var string
     */
    public $cadbNumber;

    /**
     * @var string
     */
    public $problem;

}

/**
 * Class problemReportType
 * @package ProblemService\Problem
 */
class problemReportType extends baseProblemReportType{

    /**
     * @var string
     */
    public $errorReportId;

    /**
     * @var \DateTime
     */
    public $submissionDateTime;

}

/**
 * Class reportProblemRequest
 * @package ProblemService\Problem
 */
class reportProblemRequest extends baseProblemReportType {

}

/**
 * Class reportProblemResponse
 * @package ProblemService\Problem
 */
class reportProblemResponse {

    /**
     * @var problemReportType
     */
    public $returnTransaction;

}