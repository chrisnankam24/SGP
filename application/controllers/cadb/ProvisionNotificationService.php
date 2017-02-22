<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 8:27 AM
 */

require_once "Fault.php";
require_once "Common.php";
require_once "Porting.php";
require_once "Rollback.php";
require_once "ProvisionNotification.php";
require_once "ProvisionOperationService.php";
require_once APPPATH . "controllers/email/EmailService.php";

use ProvisionService\ProvisionNotification as ProvisionNotification;

/**
 * Class ProvisionNotificationService
 */
class ProvisionNotificationService  extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Porting_model');
        $this->load->model('Portingsubmission_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

        $this->load->model('Rollback_model');
        $this->load->model('Rollbacksubmission_model');
        $this->load->model('Rollbackstateevolution_model');

        $this->load->model('Provisioning_model');
        
        $this->load->model('FileLog_model');

    }
    
    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer(__DIR__ . '/wsdl/ProvisionNotificationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * Log action/error to file
     */
    private function fileLogAction($code, $class, $message){

        $this->FileLog_model->write_log($code, $class, $message);

    }
    
    /**
     * @param $notifyRoutingDataRequest
     * @return ProvisionNotification\notifyRoutingDataResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyRoutingData($notifyRoutingDataRequest){

        $processType = $notifyRoutingDataRequest->routingData->processType;

        $processId = $notifyRoutingDataRequest->routingData->processId;

        $endNetworkId = $notifyRoutingDataRequest->routingData->nrn->networkId;

        $provisionState = ProvisionNotification\provisionStateType::STARTED;

        if(($processType == processType::PORTING || $processType == processType::ROLLBACK) && $endNetworkId == Operator::ORANGE_NETWORK_ID){

            // OPR in porting, OPD in rollback
            $provisionState = ProvisionNotification\provisionStateType::COMPLETED;

        }

        $this->db->trans_start();

        // Insert into Provision table

        $params = array(
            'processId' => $processId,
            'endNetworkId' => $endNetworkId,
            'endRoutingNumber' => $notifyRoutingDataRequest->routingData->nrn->routingNumber,
            'subscriberMSISDN' => $notifyRoutingDataRequest->numberRanges->numberRange->startNumber,
            'routingChangeDateTime' => $notifyRoutingDataRequest->routingData->routingChangeDateTime,
            'processType' => $processType,
            'provisionState' => $provisionState,
        );

        $this->Provisioning_model->add_provisioning($params);

        if ($this->db->trans_status() === FALSE) {

            $error = $this->db->error();
            $this->fileLogAction($error['code'], 'ProvisionNotificationService', $error['message']);

            $emailService = new EmailService();

            $eParams = array(
                'errorReportId' => $processId,
                'cadbNumber' => '',
                'problem' => 'NB: This is a provisioning problem',
                'reporterNetworkId' => '',
                'submissionDateTime' => date('c'),
                'processType' => $processType
            );

            $emailService->adminErrorReport('PROVISION ROUTING DATA RECEIVED BUT DB FILLED INCOMPLETE', $eParams, processType::ERROR);

            $this->db->trans_complete();

            throw new ldbAdministrationServiceFault();

        }else{

            $this->db->trans_complete();

            $response = new ProvisionNotification\notifyRoutingDataResponse();

            return $response;

        }

    }

}