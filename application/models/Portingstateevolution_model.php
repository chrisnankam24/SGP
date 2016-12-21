<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Portingstateevolution_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get portingstateevolution by portingStateEvolutionId
     */
    function get_portingstateevolution($portingStateEvolutionId)
    {
        return $this->db->get_where('PortingStateEvolution',array('portingStateEvolutionId'=>$portingStateEvolutionId))->row_array();
    }
    
    /*
     * Get all portingstateevolution
     */
    function get_all_portingstateevolution()
    {
        return $this->db->get('PortingStateEvolution')->result_array();
    }
    
    /*
     * function to add new portingstateevolution
     */
    function add_portingstateevolution($params)
    {
        $this->db->insert('PortingStateEvolution',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update portingstateevolution
     */
    function update_portingstateevolution($portingId,$params)
    {
        $this->db->where('portingId',$portingId);
        $response = $this->db->update('PortingStateEvolution',$params);
        if($response)
        {
            return "portingstateevolution updated successfully";
        }
        else
        {
            return "Error occuring while updating portingstateevolution";
        }
    }
    
    /*
     * function to delete portingstateevolution
     */
    function delete_portingstateevolution($portingStateEvolutionId)
    {
        $response = $this->db->delete('PortingStateEvolution',array('portingStateEvolutionId'=>$portingStateEvolutionId));
        if($response)
        {
            return "portingstateevolution deleted successfully";
        }
        else
        {
            return "Error occuring while deleting portingstateevolution";
        }
    }
}
