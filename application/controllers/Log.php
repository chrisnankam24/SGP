<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Log extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
    } 

    /*
     * Listing of log
     */
    function index()
    {
        $data['log'] = $this->Log_model->get_all_log();

        $this->load->view('log/index',$data);
    }

    /*
     * Adding a new log
     */
    function add()
    {   
        if(isset($_POST) && count($_POST) > 0)     
        {   
            $params = array(
				'userId' => $this->input->post('userId'),
				'actionPerformed' => $this->input->post('actionPerformed'),
				'actionDateTime' => $this->input->post('actionDateTime'),
            );
            
            $log_id = $this->Log_model->add_log($params);
            redirect('log/index');
        }
        else
        {
            $this->load->view('log/add');
        }
    }  

    /*
     * Editing a log
     */
    function edit($logId)
    {   
        // check if the log exists before trying to edit it
        $log = $this->Log_model->get_log($logId);
        
        if(isset($log['logId']))
        {
            if(isset($_POST) && count($_POST) > 0)     
            {   
                $params = array(
					'userId' => $this->input->post('userId'),
					'actionPerformed' => $this->input->post('actionPerformed'),
					'actionDateTime' => $this->input->post('actionDateTime'),
                );

                $this->Log_model->update_log($logId,$params);            
                redirect('log/index');
            }
            else
            {   
                $data['log'] = $this->Log_model->get_log($logId);
    
                $this->load->view('log/edit',$data);
            }
        }
        else
            show_error('The log you are trying to edit does not exist.');
    } 

    /*
     * Deleting log
     */
    function remove($logId)
    {
        $log = $this->Log_model->get_log($logId);

        // check if the log exists before trying to delete it
        if(isset($log['logId']))
        {
            $this->Log_model->delete_log($logId);
            redirect('log/index');
        }
        else
            show_error('The log you are trying to delete does not exist.');
    }
    
}
