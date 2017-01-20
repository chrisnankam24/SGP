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

    }

    public function index(){

        // Create a new soap server in WSDL mode
        $server = new SoapServer( __DIR__ . '/wsdl/ProvisionNotificationService.wsdl');

        // Set the object for the soap server
        $server->setObject($this);

        // Handle soap operations
        $server->handle();

    }

    /**
     * TODO: OK
     * @param $notifyRoutingDataRequest
     * @return ProvisionNotification\notifyRoutingDataResponse
     * @throws ldbAdministrationServiceFault
     */
    public function notifyRoutingData($notifyRoutingDataRequest){

        $processType = $notifyRoutingDataRequest->routingData->processType;

        $processId = $notifyRoutingDataRequest->routingData->processId;

        $endNetworkId = $notifyRoutingDataRequest->routingData->nrn->networkId;

        $provisionState = ProvisionNotification\provisionStateType::STARTED;

        $emailService = new EmailService();

        if(($processType == processType::PORTING || $processType == processType::ROLLBACK) && $endNetworkId == Operator::ORANGE_NETWORK_ID){

            // OPR in porting, OPD in rollback
            $provisionState = ProvisionNotification\provisionStateType::COMPLETED;

            // Update process state from CONFIRMED to COMPLETED or Error if not in CONFIRMED state
            if($processType == processType::PORTING){
                $porting = $this->Porting_model->get_porting($processId);

                if($porting['portingState'] == \PortingService\Porting\portingStateType::CONFIRMED) {

                    $this->db->trans_start();

                    // Insert into porting Evolution state table

                    $portingEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::COMPLETED,
                        'isAutoReached' => false,
                        'portingId' => $processId,
                    );

                    $this->Portingstateevolution_model->add_portingstateevolution($portingEvolutionParams);

                    // Update Porting table

                    $portingParams = array(
                        'lastChangeDateTime' => date('c'),
                        'portingState' => \PortingService\Porting\portingStateType::COMPLETED
                    );

                    $this->Porting_model->update_porting($processId, $portingParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        fileLogAction($error['code'], 'ProvisionNotificationService', $error['message']);

                        $portingParams = $this->Porting_model->get_porting($processId);

                        $emailService->adminErrorReport('PORTING_COMPLETED_FROM_PROVISIONING_BUT_DB_FILLED_INCOMPLETE', $portingParams, processType::PORTING);

                    }else{

                    }

                    $this->db->trans_complete();

                }else{

                    $emailService->cadbPortingStateOffConfirmed([]);

                }
            }elseif($processType == processType::ROLLBACK){

                $rollback = $this->Rollback_model->get_full_rollback($processId);

                if($rollback['rollbackState'] == \RollbackService\Rollback\rollbackStateType::CONFIRMED) {

                    $this->db->trans_start();

                    // Insert into Rollback Evolution state table

                    $rollbackEvolutionParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::COMPLETED,
                        'isAutoReached' => false,
                        'rollbackId' => $processId,
                    );

                    $this->Rollbackstateevolution_model->add_rollbackstateevolution($rollbackEvolutionParams);

                    // Update Rollback table

                    $rollbackParams = array(
                        'lastChangeDateTime' => date('c'),
                        'rollbackState' => \RollbackService\Rollback\rollbackStateType::COMPLETED
                    );

                    $this->Rollback_model->update_rollback($processId, $rollbackParams);

                    // Notify Agents/Admin

                    if ($this->db->trans_status() === FALSE) {

                        $error = $this->db->error();
                        fileLogAction($error['code'], 'ProvisionNotificationService', $error['message']);

                        $rollbackParams = $this->Rollback_model->get_full_rollback($processId);

                        $emailService->adminErrorReport('ROLLBACK_COMPLETED_FROM_PROVISIONING_BUT_DB_FILLED_INCOMPLETE', $rollbackParams, processType::ROLLBACK);

                    }else{

                    }

                    $this->db->trans_complete();

                }else{

                    $emailService->cadbPortingStateOffConfirmed([]);

                }

            }

            // Confirm Routing Data
            $provisionOperationService = new ProvisionOperationService();

            $prResponse = $provisionOperationService->confirmRoutingData($processId);

            if($prResponse->success){

                // Process terminated

            }
            else{

                // Who cares, its auto anyway :)

            }


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
            fileLogAction($error['code'], 'ProvisionNotificationService', $error['message']);

            $emailService = new EmailService();

            $eParams = array(
                'errorReportId' => $processId,
                'cadbNumber' => '',
                'problem' => 'NB: This is a provisioning problem',
                'reporterNetworkId' => '',
                'submissionDateTime' => date('c'),
                'processType' => $processType
            );

            $emailService->adminErrorReport('PROVISION_ROUTING_DATA_RECEIVED_BUT_DB_FILLED_INCOMPLETE', $eParams, processType::ERROR);

            $this->db->trans_complete();

            throw new ldbAdministrationServiceFault();

        }else{

            $this->db->trans_complete();

            $response = new ProvisionNotification\notifyRoutingDataResponse();

            return $response;

        }

    }

}