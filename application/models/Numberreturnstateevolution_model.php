<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Numberreturnstateevolution_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get numberreturnstateevolution by returnId
     */
    function get_numberreturnstateevolution($returnId)
    {
        return $this->db->get_where('numberreturnstateevolution',array('returnId'=>$returnId))->result_array();
    }
    
    /*
     * Get all numberreturnstateevolution
     */
    function get_all_numberreturnstateevolution()
    {
        return $this->db->get('numberreturnstateevolution')->result_array();
    }
    
    /*
     * function to add new numberreturnstateevolution
     */
    function add_numberreturnstateevolution($params)
    {
        $this->db->insert('numberreturnstateevolution',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update numberreturnstateevolution
     */
    function update_numberreturnstateevolution($numberReturnStateEvolutionId,$params)
    {
        $this->db->where('numberReturnStateEvolutionId',$numberReturnStateEvolutionId);
        $response = $this->db->update('numberreturnstateevolution',$params);
        if($response)
        {
            return "numberreturnstateevolution updated successfully";
        }
        else
        {
            return "Error occuring while updating numberreturnstateevolution";
        }
    }
    
    /*
     * function to delete numberreturnstateevolution
     */
    function delete_numberreturnstateevolution($numberReturnStateEvolutionId)
    {
        $response = $this->db->delete('numberreturnstateevolution',array('numberReturnStateEvolutionId'=>$numberReturnStateEvolutionId));
        if($response)
        {
            return "numberreturnstateevolution deleted successfully";
        }
        else
        {
            return "Error occuring while deleting numberreturnstateevolution";
        }
    }
}
