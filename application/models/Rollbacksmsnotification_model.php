<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Rollbacksmsnotification_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get rollbacksmsnotification by rollbackSmsNotification
     */
    function get_rollbacksmsnotification($rollbackSmsNotification)
    {
        return $this->db->get_where('rollbacksmsnotification',array('rollbackSmsNotification'=>$rollbackSmsNotification))->row_array();
    }

    /*
    * Get rollbacksmsnotification by status
    */
    function get_rollbacksmsnotificationByStatus($status)
    {
        return $this->db->get_where('rollbacksmsnotification',array('status'=>$status))->result_array();
    }

    /*
     * Get all rollbacksmsnotification
     */
    function get_all_rollbacksmsnotification()
    {
        return $this->db->get('rollbacksmsnotification')->result_array();
    }
    
    /*
     * function to add new rollbacksmsnotification
     */
    function add_rollbacksmsnotification($params)
    {
        $this->db->insert('rollbacksmsnotification',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update rollbacksmsnotification
     */
    function update_rollbacksmsnotification($rollbackSmsNotification,$params)
    {
        $this->db->where('rollbackSmsNotification',$rollbackSmsNotification);
        $response = $this->db->update('rollbacksmsnotification',$params);
        if($response)
        {
            return "rollbacksmsnotification updated successfully";
        }
        else
        {
            return "Error occuring while updating rollbacksmsnotification";
        }
    }
    
    /*
     * function to delete rollbacksmsnotification
     */
    function delete_rollbacksmsnotification($rollbackSmsNotification)
    {
        $response = $this->db->delete('rollbacksmsnotification',array('rollbackSmsNotification'=>$rollbackSmsNotification));
        if($response)
        {
            return "rollbacksmsnotification deleted successfully";
        }
        else
        {
            return "Error occuring while deleting rollbacksmsnotification";
        }
    }
}
