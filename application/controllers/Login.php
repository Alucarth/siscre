<?php

class Login extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        
        if ($this->Employee->is_logged_in())
        {
            redirect('home');
        }
        else
        {
            $this->form_validation->set_rules('username', 'lang:login_undername', 'callback_login_check');

            $this->form_validation->set_error_delimiters('<div class="error">', '</div>');

            if ($this->form_validation->run() == FALSE)
            {
                $data = [];
                if (is_plugin_active("branches"))
                {
                    $this->load->model('branches/Branch_model');
                    $data["branches"] = $this->Branch_model->get_branches();
                }

                $this->load->view('login', $data);
            }
            else
            {

                redirect('home');
            }
        }
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch( $type )
        {
            case 1:
                $this->_handle_login_ajax();
                break;
            case 2:
                $this->_handle_logout_ajax();
                break;
            case 3:
                $this->_handle_signup_ajax();
                break;
            case 4:
                $this->_handle_reset_password();
                break;
        }
    }
    
    private function _handle_reset_password()
    {
        $email = $this->input->post("email");
        
        $this->db->where("p.email", $email);
        $this->db->from("people p");
        $this->db->join("employees e", "e.person_id = p.person_id", "LEFT");
        $query = $this->db->get();
        
        $username = $user_id = '';
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $username = $row->username;
            $user_id = $row->person_id;
        }
        
        $password = $this->random_password();
        
        $update_data = [];
        $update_data["password"] = md5($password);
        
        $this->db->where("person_id", $user_id);
        $this->db->update("employees", $update_data);
        
        $html = "
            Dear $username,<br/><br/>
            Your new login details to the portal are as follows: <br/><br/>
            
            " . site_url() . " <br/><br/>
            User Name: $username <br/>
            Password: $password <br/><br/>
            
            Regards,<br/><br/>

                ";
        
        $email_data = [];
        $email_data["from_name"] = 'noreply';
        $email_data["from_email"] = 'noreply@support.com';
        $email_data["to_email"] = $email;
        $email_data["subject"] = "Password Reset";
        $email_data["html"] = $html;
        
        custom_send_email($email_data);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_signup_ajax()
    {
        $this->load->library('email');
        
        $username = $this->input->post("signup_username");
        $email = $this->input->post("signup_email");
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $return["message"] = "Error: Email is not a valid email address!";
            echo json_encode($return);
            exit;
        }
        
        if ( $this->_is_exists($username, 'username') )
        {
            $return["message"] = "Error: Username has already been taken!";
            echo json_encode($return);
            exit;
        }
        
        if ( $this->_is_exists($email, 'email') )
        {
            $return["message"] = "Error: Email has already been taken!";
            echo json_encode($return);
            exit;
        }
        
        $insert_data = [];
        $insert_data["email"] = $email;
        $insert_data["role_id"] = $this->_get_customer_role_id();
        $this->db->insert("people", $insert_data);
        $user_id = $this->db->insert_id();
        
        $password = $this->random_password();
        
        $insert_data = [];
        $insert_data["username"] = $username;
        $insert_data["email"] = $email;
        $insert_data["password"] = md5($password);
        $insert_data["person_id"] = $user_id;
        $this->db->insert("employees", $insert_data);
        
        // Send email
        $email_data["username"] = $username;
        $email_data["temp_password"] = $password;
        $html = $this->load->view('marketplace/email_templates/welcome_email', $email_data, true);
        
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        $this->email->from('support@softreliance.com', 'Support');
        $this->email->to($email);
        $this->email->bcc('jmendez.marino@gmail.com');

        $this->email->subject('SoftReliance - Marketplace, Thanks for signing up!');        
        $this->email->message($html);

        $this->email->send();
        
        $return["status"] = "OK";
        echo json_encode($return);
        exit;
    }
    
    private function _get_customer_role_id()
    {
        $this->db->like('name', 'customer');
        $query = $this->db->get("roles");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            return $row->role_id;
        }
        
        return 0;
    }
    
    private function random_password()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++)
        {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    private function _is_exists($key, $field)
    {
        $this->db->from("people p");
        $this->db->join("employees e", "p.person_id = e.person_id", "LEFT");
        
        switch($field)
        {
            case 'email':
                $this->db->where("p.email", $key);
                break;
            case 'username':
                $this->db->where("e.username", $key);
                break;
        }
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            return true;
        }
        
        return false;
    }
    
    private function _handle_login_ajax()
    {
        $return["status"] = "FAILED";
        if ($this->login_check($this->input->post("email")))
        {
            $return["status"] = "OK";
        }
        else
        {
            $return["message"] = "Invalid Username/password";
        }
        echo json_encode($return);
        exit;
    }
    
    private function _handle_logout_ajax()
    {
        $this->Employee->logout(0);
        echo json_encode(["status" => "OK"]);
        exit;
    }

    function login_check($username)
    {
        $password = $this->input->post("password");
        $branch_id = $this->input->post("branch_id");
        
        if (!$this->Employee->login($username, $password, $branch_id))
        {

            $this->form_validation->set_message('login_check', $this->lang->line('login_invalid_username_and_password'));
            
            if ( $this->session->userdata('branch_id_error') != '' )
            {
                $this->form_validation->set_message('login_check', $this->session->userdata('branch_id_error'));                
            }

            return false;
        }

        return true;
    }

    function signup()
    {

        $username = $this->input->post("reg_username");

        $password = $this->input->post("reg_password");

        $first_name = $this->input->post("reg_first_name");

        $last_name = $this->input->post("reg_last_name");

        $email = $this->input->post("reg_email");

        $password = $this->input->post("reg_password");

        $contact = $this->input->post("reg_contact_number");



        $return["status"] = "OK";

        echo json_encode($return);
    }

}

?>