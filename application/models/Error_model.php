<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class Error_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get error by errorReportId
     */
    function get_error($errorReportId)
    {
        return $this->db->get_where('Error',array('errorReportId'=>$errorReportId))->row_array();
    }
    
    /*
     * Get all error
     */
    function get_all_error()
    {
        return $this->db->get('Error')->result_array();
    }
    
    /*
     * function to add new error
     */
    function add_error($params)
    {
        $this->db->insert('Error',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update error
     */
    function update_error($errorReportId,$params)
    {
        $this->db->where('errorReportId',$errorReportId);
        $response = $this->db->update('Error',$params);
        if($response)
        {
            return "error updated successfully";
        }
        else
        {
            return "Error occuring while updating error";
        }
    }
    
    /*
     * function to delete error
     */
    function delete_error($errorReportId)
    {
        $response = $this->db->delete('Error',array('errorReportId'=>$errorReportId));
        if($response)
        {
            return "error deleted successfully";
        }
        else
        {
            return "Error occuring while deleting error";
        }
    }
}