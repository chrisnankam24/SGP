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
     * Get portingstateevolution by portingId
     */
    function get_portingstateevolution($portingId)
    {
        return $this->db->get_where('portingstateevolution',array('portingId'=>$portingId))->result_array();
    }

    /*
    * Get portingstateevolution by portingId
    */
    function get_pse($portingId, $state)
    {
        return $this->db->get_where('portingstateevolution',array('portingId'=>$portingId, 'portingState' => $state))->row_array();
    }


    /*
     * Get all portingstateevolution
     */
    function get_all_portingstateevolution()
    {
        return $this->db->get('portingstateevolution')->result_array();
    }
    
    /*
     * function to add new portingstateevolution
     */
    function add_portingstateevolution($params)
    {
        $this->db->insert('portingstateevolution',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update portingstateevolution
     */
    function update_portingstateevolution($portingId,$params)
    {
        $this->db->where('portingId',$portingId);
        $response = $this->db->update('portingstateevolution',$params);
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
        $response = $this->db->delete('portingstateevolution',array('portingStateEvolutionId'=>$portingStateEvolutionId));
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
