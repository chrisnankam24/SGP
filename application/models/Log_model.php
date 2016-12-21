<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Log_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get log by logId
     */
    function get_log($logId)
    {
        return $this->db->get_where('Log',array('logId'=>$logId))->row_array();
    }
    
    /*
     * Get all log
     */
    function get_all_log()
    {
        return $this->db->get('Log')->result_array();
    }
    
    /*
     * function to add new log
     */
    function add_log($params)
    {
        $this->db->insert('Log',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update log
     */
    function update_log($logId,$params)
    {
        $this->db->where('logId',$logId);
        $response = $this->db->update('Log',$params);
        if($response)
        {
            return "log updated successfully";
        }
        else
        {
            return "Error occuring while updating log";
        }
    }
    
    /*
     * function to delete log
     */
    function delete_log($logId)
    {
        $response = $this->db->delete('Log',array('logId'=>$logId));
        if($response)
        {
            return "log deleted successfully";
        }
        else
        {
            return "Error occuring while deleting log";
        }
    }
}
