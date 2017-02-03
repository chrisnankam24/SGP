<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Rollbackrejectionabandon_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get rollbackrejectionabandon by rollbackRejectionAbandoned
     */
    function get_rollbackrejectionabandon($rollbackRejectionAbandoned)
    {
        return $this->db->get_where('rollbackrejectionabandon',array('rollbackRejectionAbandoned'=>$rollbackRejectionAbandoned))->row_array();
    }
    
    /*
     * Get all rollbackrejectionabandon
     */
    function get_all_rollbackrejectionabandon()
    {
        return $this->db->get('rollbackrejectionabandon')->result_array();
    }
    
    /*
     * function to add new rollbackrejectionabandon
     */
    function add_rollbackrejectionabandon($params)
    {
        $this->db->insert('rollbackrejectionabandon',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update rollbackrejectionabandon
     */
    function update_rollbackrejectionabandon($rollbackRejectionAbandonedId,$params)
    {
        $this->db->where('rollbackRejectionAbandonedId',$rollbackRejectionAbandonedId);
        $response = $this->db->update('rollbackrejectionabandon',$params);
        if($response)
        {
            return "rollbackrejectionabandon updated successfully";
        }
        else
        {
            return "Error occuring while updating rollbackrejectionabandon";
        }
    }
    
    /*
     * function to delete rollbackrejectionabandon
     */
    function delete_rollbackrejectionabandon($rollbackRejectionAbandoned)
    {
        $response = $this->db->delete('rollbackrejectionabandon',array('rollbackRejectionAbandoned'=>$rollbackRejectionAbandoned));
        if($response)
        {
            return "rollbackrejectionabandon deleted successfully";
        }
        else
        {
            return "Error occuring while deleting rollbackrejectionabandon";
        }
    }
}
