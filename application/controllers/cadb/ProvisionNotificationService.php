<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/20/2016
 * Time: 8:27 AM
 */

require_once "ProvisionNotification.php";
require_once "Fault.php";

use ProvisionService\ProvisionNotification as ProvisionNotification;

/**
 * Class ProvisionNotificationService
 */
class ProvisionNotificationService  extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProvisionNotificationService.wsdl');

        // Set the class for the soap server
        $server->setClass("ProvisionNotificationService");

        // Handle soap operations
        $server->handle();

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

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {

            $emailService = new EmailService();
            $emailService->adminErrorReport('PROVISION_ROUTING_DATA_RECEIVED_BUT_DB_FILLED_INCOMPLETE', []);

        }

        $response = new ProvisionNotification\notifyRoutingDataResponse();

        return $response;

    }

}