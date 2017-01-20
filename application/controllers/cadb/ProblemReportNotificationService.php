<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/19/2016
 * Time: 8:57 PM
 */

require_once "Fault.php";
require_once "Common.php";
require_once "ProblemNotification.php";
require_once APPPATH . "controllers/email/EmailService.php";

use ProblemService\ProblemNotification as ProblemNotification;

class ProblemReportNotificationService extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Error_model');

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProblemReportNotificationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * TODO: OK
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
                'errorNotificationMailSendStatus' => smsState::PENDING,
                'processType' => $notifyProblemReportedRequest->processType
            );

            $this->Error_model->add_error($params);

        }

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();
            fileLogAction($error['code'], 'ProblemReportNotificationService', $error['message']);

            $params = $this->Error_model->get_error($errorReportId);

            $emailService->adminErrorReport('ERROR_REPORT_RECEIVED_BUT_DB_FILLED_INCOMPLETE', $params, processType::ERROR);

        }

        $this->db->trans_complete();

        $response = new ProblemNotification\notifyProblemReportedResponse();

        return $response;

    }

}