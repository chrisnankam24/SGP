<?php
/* 
 * Generated by CRUDigniter v2.3 Beta 
 * www.crudigniter.com
 */
 
class User_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    /*
     * Get user by userId
     */
    function get_user($userId)
    {
        return $this->db->get_where('Users',array('userId'=>$userId))->row_array();
    }
    
    /*
     * Get all users
     */
    function get_all_users()
    {
        return $this->db->get('Users')->result_array();
    }
    
    /*
     * function to add new user
     */
    function add_user($params)
    {
        $this->db->insert('Users',$params);
        return $this->db->insert_id();
    }
    
    /*
     * function to update user
     */
    function update_user($userId,$params)
    {
        $this->db->where('userId',$userId);
        $response = $this->db->update('Users',$params);
        if($response)
        {
            return "user updated successfully";
        }
        else
        {
            return "Error occuring while updating user";
        }
    }
    
    /*
     * function to delete user
     */
    function delete_user($userId)
    {
        $response = $this->db->delete('Users',array('userId'=>$userId));
        if($response)
        {
            return "user deleted successfully";
        }
        else
        {
            return "Error occuring while deleting user";
        }
    }
}