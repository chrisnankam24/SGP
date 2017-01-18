<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/8/2016
 * Time: 8:06 AM
 */
class Login extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('User_model');
    }


    public function index()
    {
        
        if ($this->session->logged_in == TRUE) {
            // User already logged in so redirect to app view
            if(isset($this->session->acceuil))
                redirect($this->session->acceuil);
            else
                $this->load->view('login');
        }else {
            $this->load->view('login');
        }
        
    }
    
    public function jsonLogIn(){
        $data = array();

        $request = $this->input->post();
        if(isset($request['username']) && isset($request['username']) && $request['username']!="" && isset($request['username'])!=""){
            // client sent info to browser
            $username = $request['username'];
            $password = $request['password'];
            $valeur_authentification = false;
            $valeur_status = false;

            $utilisateur = $this->User_model->get_user($username);
            if($utilisateur['status'] == 1){
                $cuid = urlencode($username);
                $password = urlencode($password);
                $key = urlencode('6834026871de5083fabeb366693a6ec2');
                $url = "http://172.21.55.39/uaconsole/index.php/UserAccessConsole/authentify?cuid=$cuid&password=$password&key=$key";
                $native_response = file_get_contents($url);
                $json_response = json_decode($native_response, true);

                $valeur_authentification = $json_response['AUTH'];
                $valeur_status = true;
            }

            if($valeur_authentification && $valeur_status){
                // Credentials ok
                $data['username'] = explode(" ", $utilisateur['firstName'])[0] . " " . explode(" ", $utilisateur['lastName'])[0];
                $data['logged_in'] = true;
                $data['role'] = $utilisateur['role'];
                $data['userId'] = $username;
                
                $homepage = "";
                if($utilisateur['role'] == 4){
                    $homepage = site_url('Admin/index');
                }else if($utilisateur['role'] != 4){
                    $homepage = site_url('Welcome/test');
                }

                if(isset($this->session->askPage) && !isset($this->session->logged_in)){
                    $data['acceuil'] = site_url($this->session->askPage);
                }else{
                    $data['acceuil'] = $homepage;
                }
                
                $data['currentPage'] = $homepage;
                // Save data to session
                $this->session->set_userdata($data);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }else{
                // Invalid credentials
                $data['logged_in'] = false;
                $data['message'] = 'Le CUID ou le Mot de passe est incorrect';
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            }

        }else{
            $data['logged_in'] = false;
            $data['message'] = 'Saisir le CUID et le Mot de passe';
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
    }
    
    public function signOut(){
        $this->session->sess_destroy();
        redirect('Login/index', 'refresh');
    }

}