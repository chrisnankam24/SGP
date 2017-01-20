<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Porting_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get porting by portingId
     */
    function get_porting($portingId)
    {
        return $this->db->get_where('Porting',array('portingId'=>$portingId))->row_array();
    }

    /*
     * Get porting in particular state and for particular donor.
     * personType = 0 for personal and 1 for enterprise
     */
    function get_porting_by_state_and_donor($portingState, $donorNetworkId, $personType = null)
    {
        $query = "SELECT * FROM Porting WHERE donorNetworkId = ? AND portingState = ?";

        if($personType == null){

        }elseif($personType == 0){ // Personal
            $query .= " AND legalPersonName IS NULL";
        }else if($personType == 1){ // Enterprise
            $query .= " AND physicalPersonLastName IS NULL";
        }else{

        }

        $response = $this->db->query($query, array($donorNetworkId, $portingState))->result_array();

        return $response;

    }

    /*
     * Get porting in particular state and for particular recipient
     */
    function get_porting_by_state_and_recipient($portingState, $recipientNetworkId)
    {
        return $this->db->get_where('Porting',array('portingState'=>$portingState, 'recipientNetworkId' => $recipientNetworkId))->result_array();
    }
    
    /*
     * Get all porting
     */
    function get_all_porting()
    {
        return $this->db->order_by('recipientSubmissionDateTime', 'desc')->get('Porting')->result_array();
    }

    /*
     * Get all waiting
     */
    function get_all_waiting_porting()
    {
        return $this->db->where('portingState', \PortingService\Porting\portingStateType::APPROVED)->where('donorNetworkId', Operator::ORANGE_NETWORK_ID)->order_by('recipientSubmissionDateTime', 'desc')->get_where('Porting')->result_array();
    }
    
    /*
     * function to add new porting
     */
    function add_porting($params)
    {
        $this->db->insert('Porting',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update porting
     */
    function update_porting($portingId,$params)
    {
        $this->db->where('portingId',$portingId);
        $response = $this->db->update('Porting',$params);
        if($response)
        {
            return "porting updated successfully";
        }
        else
        {
            return "Error occuring while updating porting";
        }
    }
    
    /*
     * function to delete porting
     */
    function delete_porting($portingId)
    {
        $response = $this->db->delete('Porting',array('portingId'=>$portingId));
        if($response)
        {
            return "porting deleted successfully";
        }
        else
        {
            return "Error occuring while deleting porting";
        }
    }
}
