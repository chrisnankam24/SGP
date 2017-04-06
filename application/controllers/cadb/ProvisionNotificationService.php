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
use \ProvisionService\ProvisionNotification\provisionStateType as provisionStateType;


/**
 * Class ProvisionNotificationService
 */
class ProvisionNotificationService  extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Porting_model');
        $this->load->model('Portingstateevolution_model');
        $this->load->model('Portingsmsnotification_model');
        $this->load->model('Portingdenyrejectionabandon_model');

        $this->load->model('Rollback_model');
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

        $this->fileLogAction('9002', 'ProvisionNotificationService', 'Provision Notification received for ID ' . $processId);

        $provisionNumbers =$this->getProvisionNumbers($notifyRoutingDataRequest);

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
            'routingChangeDateTime' => $notifyRoutingDataRequest->routingData->routingChangeDateTime,
            'processType' => $processType,
            'provisionState' => $provisionState,
        );

        $this->Provisioning_model->add_provisioning($params);

        // Insert into Provision Number table

        $processNumberParams = [];

        foreach ($provisionNumbers as $provisionNumber){
            $processNumberParams[] = array(
                'processId' => $processId,
                'msisdn' => $provisionNumber,
                'numberState' => $provisionState,
                'pLastChangeDateTime' => date('c')
            );
        }

        $this->db->insert_batch('provisionnumber', $processNumberParams);

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

            $this->fileLogAction('9002', 'ProvisionNotificationService', 'Provision Notification saved successfully for ID ' . $processId);

            $this->db->trans_complete();

            $response = new ProvisionNotification\notifyRoutingDataResponse();

            return $response;

        }

    }

    /**
     * Returns provision MSISDN in process
     * @param $request
     * @return array
     */
    private function getProvisionNumbers($request){

        $numbers = [];

        if(is_array($request->numberRanges->numberRange)){

            foreach ($request->numberRanges->numberRange as $numberRange){

                $startMSISDN = $numberRange->startNumber;
                $endMSISDN = $numberRange->endNumber;

                if(strlen($startMSISDN) == 12){
                    $startMSISDN = substr($startMSISDN, 3);
                }
                if(strlen($endMSISDN) == 12){
                    $endMSISDN = substr($endMSISDN, 3);
                }

                $startMSISDN = intval($startMSISDN);
                $endMSISDN = intval($endMSISDN);

                while ($startMSISDN <= $endMSISDN){
                    $numbers[] = '237' . $startMSISDN;
                    $startMSISDN += 1;
                }

            }

        }
        else{

            $startMSISDN = $request->numberRanges->numberRange->startNumber;
            $endMSISDN = $request->numberRanges->numberRange->endNumber;

            if(strlen($startMSISDN) == 12){
                $startMSISDN = substr($startMSISDN, 3);
            }
            if(strlen($endMSISDN) == 12){
                $endMSISDN = substr($endMSISDN, 3);
            }

            $startMSISDN = intval($startMSISDN);
            $endMSISDN = intval($endMSISDN);

            while ($startMSISDN <= $endMSISDN){
                $numbers[] = '237' . $startMSISDN;
                $startMSISDN += 1;
            }

        }

        $numbers = array_values(array_unique($numbers));

        return $numbers;

    }

}