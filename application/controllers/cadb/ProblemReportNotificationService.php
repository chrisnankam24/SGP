<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:57 PM
 */

require_once "ProblemNotification.php";
require_once "Fault.php";

use ProblemService\ProblemNotification as ProblemNotification;

class ProblemReportNotificationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProblemReportNotificationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ProblemReportNotificationService");

        // Handle soap operations
        $server->handle();

    }

    /**
     * @param $notifyProblemReportedRequest
     * @return ProblemNotification\notifyProblemReportedResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyProblemReported($notifyProblemReportedRequest){

        $reporterNetworkId = $notifyProblemReportedRequest->problemReport->reporterNetworkId;

        $errorReportId = $notifyProblemReportedRequest->problemReport->errorReportId;

        $emailService = new EmailService();

        $this->db->trans_start();

        if($reporterNetworkId == Operator::ORANGE_NETWORK_ID){

            // Update Error table

            $params = array(
                'nrnNetworkId' => $notifyProblemReportedRequest->nrn->networkId,
                'nrnRoutingNumber' => $notifyProblemReportedRequest->nrn->routingNumber,
                'routingChangeDateTime' => $notifyProblemReportedRequest->routingChangeDateTime,
                'processType' => $notifyProblemReportedRequest->processType
            );

            $this->Error_model->update_error($errorReportId,$params);

        }else{

            // Insert into Error table

            $params = array(
                'errorReportId' => $errorReportId,
                'cadbNumber' => $notifyProblemReportedRequest->problemReport->cadbNumber,
                'problem' => $notifyProblemReportedRequest->problemReport->problem,
                'reporterNetworkId' => $notifyProblemReportedRequest->problemReport->reporterNetworkId,
                'submissionDateTime' => $notifyProblemReportedRequest->problemReport->submissionDateTime,
                'nrnNetworkId' => $notifyProblemReportedRequest->nrn->networkId,
                'nrnRoutingNumber' => $notifyProblemReportedRequest->nrn->routingNumber,
                'routingChangeDateTime' => $notifyProblemReportedRequest->routingChangeDateTime,
                'processType' => $notifyProblemReportedRequest->processType
            );

            $this->Error_model->add_error($params);

            // Notify Admin/Agents
            $emailService->adminAgentsErrorReport([]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {

            $emailService->adminErrorReport('ERROR_REPORT_RECEIVED_BUT_DB_FILLED_INCOMPLETE', []);

        }

        $response = new ProblemNotification\notifyProblemReportedResponse();

        return $response;

    }

}