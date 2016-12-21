<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Numberreturn_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get numberreturn by returnId
     */
    function get_numberreturn($returnId)
    {
        return $this->db->get_where('NumberReturn',array('returnId'=>$returnId))->row_array();
    }
    
    /*
     * Get all numberreturn
     */
    function get_all_numberreturn()
    {
        return $this->db->get('NumberReturn')->result_array();
    }

    /*
     * Get nr in particular state and for particular owner
     */
    function get_nr_by_state_and_co($nrState, $ownerNetworkId)
    {
        return $this->db->get_where('NumberReturn',array('returnNumberState'=>$nrState, 'ownerNetworkId' => $ownerNetworkId))->result_array();
    }

    /*
     * Get nr in particular state and for particular owner
     */
    function get_nr_by_state_and_po($nrState, $primaryOwnerNetworkId)
    {
        return $this->db->get_where('NumberReturn',array('returnNumberState'=>$nrState, 'primaryOwnerNetworkId' => $primaryOwnerNetworkId))->result_array();
    }


    /*
     * function to add new numberreturn
     */
    function add_numberreturn($params)
    {
        $this->db->insert('NumberReturn',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update numberreturn
     */
    function update_numberreturn($returnId,$params)
    {
        $this->db->where('returnId',$returnId);
        $response = $this->db->update('NumberReturn',$params);
        if($response)
        {
            return "numberreturn updated successfully";
        }
        else
        {
            return "Error occuring while updating numberreturn";
        }
    }
    
    /*
     * function to delete numberreturn
     */
    function delete_numberreturn($returnId)
    {
        $response = $this->db->delete('NumberReturn',array('returnId'=>$returnId));
        if($response)
        {
            return "numberreturn deleted successfully";
        }
        else
        {
            return "Error occuring while deleting numberreturn";
        }
    }
}
