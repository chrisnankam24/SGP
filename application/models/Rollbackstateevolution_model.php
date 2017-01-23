<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Rollbackstateevolution_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get rollbackstateevolution by rollbackId
     */
    function get_rollbackstateevolution($rollbackId)
    {
        return $this->db->get_where('RollbackStateEvolution',array('rollbackId'=>$rollbackId))->row_array();
    }

    /*
     * Get all rollbackstateevolution
     */
    function get_all_rollbackstateevolution()
    {
        return $this->db->get('RollbackStateEvolution')->result_array();
    }

    /*
     * function to add new rollbackstateevolution
     */
    function add_rollbackstateevolution($params)
    {
        $this->db->insert('RollbackStateEvolution',$params);
        return $this->db->insert_id();
    }

    /*
     * function to update rollbackstateevolution
     */
    function update_rollbackstateevolution($rollbackStatevolutionId,$params)
    {
        $this->db->where('rollbackStatevolutionId',$rollbackStatevolutionId);
        $response = $this->db->update('RollbackStateEvolution',$params);
        if($response)
        {
            return "rollbackstateevolution updated successfully";
        }
        else
        {
            return "Error occuring while updating rollbackstateevolution";
        }
    }

    /*
     * function to delete rollbackstateevolution
     */
    function delete_rollbackstateevolution($rollbackStatevolutionId)
    {
        $response = $this->db->delete('RollbackStateEvolution',array('rollbackStatevolutionId'=>$rollbackStatevolutionId));
        if($response)
        {
            return "rollbackstateevolution deleted successfully";
        }
        else
        {
            return "Error occuring while deleting rollbackstateevolution";
        }
    }
}
