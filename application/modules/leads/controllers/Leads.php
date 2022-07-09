<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Leads extends Secure_area implements iData_controller
{
    public $data;
    public $is_employer = false;
    
    function __construct()
    {
        $controllers = $this->_get_fe_controllers();
        parent::__construct('leads', null, $controllers);
        
        // Restrict access customers to leads front end only
        if ( strtolower($this->role_name) == 'customers' && !in_array($this->router->fetch_method(), ['profile', 'ajax', 'documents', 'success_payment', 'cancel_payment']))
        {
            redirect('leads');
        }
        
        $this->load->model("leads_model");
        $this->load->model("Loan");
        
        $this->data["is_employer"] = false;
        
        $this->data["user_info"] = $this->leads_model->get_info($this->session->userdata("session_leads_id"));
        $this->load->helper("leads");
        $this->load->library('DataTableLib');        
    }
    
    private function _get_fe_controllers()
    {
        $controllers = [
            'index', 
            'assets', 
            'ajax', 
            'success_payment', 
            'cancel_payment', 
            'documents', 
            'application', 
            'steps', 
            'upload', 
            'article',
            'personal_info',
            'address',
            'employment',
            'docs',
            'bank_info',
            'logout',
            'test',
            'my_employees',
            'employee',
            'password_reset',
            'save_profile'
        ];
        
        return $controllers;
    }

    public function index()
    {
        $q = $this->input->get("q");
        $data["title"] = "SoftReliance - leads";
        $data["meta_description"] = "Borrow money fast";
        $data["meta_keywords"] = "K-Loan Plugin, PHP application, easy to install";
        
        $data["widget_title"] = "Fast and Easy Online Loans 24/7";
        
        $data["articles"] = $this->leads_model->get_articles(["published" => 1]);
        $data["loan_products"] = $this->leads_model->get_loan_products();
        
        $filters = [];
        $filters["q"] = $q;
        
        $data["items"] = $this->leads_model->get_all( $filters );
        $data["default_apply_amount"] = 3000;
        $data["default_interest_rate"] = 17;
        $data["default_first_payment_date"] = date("Y-m-d", strtotime("+15 days"));
        
        $this->load->view("leads/front/index", $data);
    }
    
    public function details($id)
    {
        $this->user_info = $this->Employee->get_logged_in_employee_info();
        $user_id = ( $this->user_info ) ? $this->user_info->person_id : -1;
        $item = $this->leads_model->get_details($id);
        
        $my_item = $this->leads_model->check_my_item( $id, $user_id );
        $item->leads_item_id = @$my_item->leads_item_id;
        $item->verified = @$my_item->verified;
        
        $data["item"] = $item;
        $data["meta_description"] = $item->meta_robots;
        $data["meta_keywords"] = $item->tags;
        $data["title"] = "Plugin: " . $item->title;
        
        $this->load->view("leads/front/details", $data);
    }
    
    public function profile()
    {
        $data["meta_description"] = "SoftReliance - User Info";
        $data["meta_keywords"] = "User account";
        $data["title"] = $this->user_info->first_name . ' ' . $this->user_info->last_name;
        $data["my_purchases"] = $this->leads_model->get_purchases($this->user_info->person_id);
        $data["my_ratings"] = $this->leads_model->get_my_ratings($this->user_info->person_id);
        $this->load->view("leads/front/my_account", $data);
    }
    
    public function records()
    {
        
        $this->load->library('DataTableLib');

        $this->set_dt_records($this->datatablelib->datatable());
        $data["tbl_leads_items"] = $this->datatablelib->render();

        $this->load->view("leads/cms/list", $data);
    }
    
    function get_records()
    {
        $action = $this->input->post('ajax_action');
        switch ($action)
        {
            case 'list':
                $this->_dt_records();
                break;            
        }
    }

    function set_dt_records($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash()]);
        $datatable->ajax_url = site_url('leads/get_records');

        $datatable->add_column('actions', false);
        $datatable->add_column('title', false);
        $datatable->add_column('description', false);
        $datatable->add_column('price', false);
        $datatable->add_column('new_price', false);
        $datatable->add_column('author', false);
        $datatable->add_column('sold', false);
        $datatable->add_column('download_link', false);
        $datatable->add_column('formatted_added_date', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];
        
        $datatable->table_id = "#leads_items";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_records()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");
        
        
        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];

        $order = array("index" => $index, "direction" => $dir);
        
        $fields = $this->leads_model->get_fields("leads");

        $filters = [];
        $items = $this->leads_model->get_all($filters, $limit, $offset, "", $order);

        $count_all = 0;
        $tmp = [];
        if ($items)
        {
            foreach ($items->result() as $row)
            {
                $actions = "<a href='" . site_url('leads/view/' . $row->id) . "' class='btn-xs btn-default' title='View'><span class='fa fa-eye'></span></a> ";
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete' data-item-id='". $row->id ."' title='Delete'><span class='fa fa-trash'></span></a>";

                $data_row = [];
                $data_row["DT_RowId"] = $row->id;
                $data_row["actions"] = $actions;
                
                foreach ($fields as $field)
                {
                    $data_row[$field] = $row->$field;
                    if ( $field == 'added_date' )
                    {
                        $data_row[ 'formatted_added_date' ] = date('M d, Y', strtotime($row->$field));
                    }
                    
                    if ( $field == 'description' )
                    {
                        $data_row['description'] = truncate_html(strip_tags($row->$field), 50);
                    }
                }
                        
                $tmp[] = $data_row;
                $count_all++;
            }
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }

    public function assets()
    {
        //---get working directory and map it to your module
        $file = getcwd() . '/application/modules/' . implode('/', $this->uri->segments);
        
        //----get path parts form extension
        $path_parts = pathinfo($file);
        //---set the type for the headers
        $file_type = strtolower($path_parts['extension']);

        if (is_file($file))
        {
            //----write propper headers
            switch ($file_type)
            {
                case 'css':
                    header('Content-type: text/css');
                    break;

                case 'js':
                    header('Content-type: text/javascript');
                    break;

                case 'json':
                    header('Content-type: application/json');
                    break;

                case 'xml':
                    header('Content-type: text/xml');
                    break;

                case 'pdf':
                    header('Content-type: application/pdf');
                    break;
                
                case 'woff2':
                    header('Content-type: application/font-woff2');
                    readfile($file);
                    exit;
                    break;
                
                case 'woff':
                    header('Content-type: application/font-woff');
                    readfile($file);
                    exit;
                    break;
                
                case 'ttf':
                    header('Content-type: applicaton/x-font-ttf');
                    readfile($file);
                    exit;
                    break;

                case 'jpg' || 'jpeg' || 'png' || 'gif':
                    header('Content-type: image/' . $file_type);
                    readfile($file);
                    exit;
                    break;
            }

            include $file;
        }
        else
        {
            show_404();
        }
        exit;
    }

    public function delete()
    {
        
    }

    public function get_form_width()
    {
        
    }

    public function get_row()
    {
        
    }

    public function save($id = -1)
    {
        $id = $this->leads_model->save($id, $_POST);
        if ( $id )
        {
            $return['success'] = true;
            $return['message'] = "Successful!";
            $return['id'] = $id;
            echo json_encode($return);
            exit;
        }
        
        $return['success'] = false;
        $return['message'] = "Failed to save!";
        $return['id'] = $id;
        echo json_encode($return);
        exit;
    }

    public function search()
    {
        
    }

    public function suggest()
    {
        
    }

    public function view($id = -1)
    {
        $data = [];
        if ( $id > 0 )
        {
            $data["leads"] = $this->leads_model->get_details( $id );
            $data["galleries"] = $this->leads_model->get_galleries( $id );
        }
        
        $data["item_categories"] = $this->leads_model->get_item_categories();        

        $this->set_dt_orders($this->datatablelib->datatable(), $id);
        $data["tbl_leads_orders"] = $this->datatablelib->render();
        
        $this->load->view("leads/cms/form", $data);
    }

    public function upload()
    {
        $id = $this->input->post('id');
        $is_front_img = $this->input->post("is_front_img");
        $is_back_img = $this->input->post("is_back_img");
        $is_cc_img = $this->input->post("is_cc_img");
        $is_patent_img = $this->input->post("is_patent_img");
        
        $directory = APPPATH . 'modules/leads/documents/' . $id;
        
        $this->load->library('uploader');
        
        $_FILES['file']['name'] = str_replace(' ', '_', $_FILES['file']['name']);

        $data = $this->uploader->upload($directory);
        
        $update_data = [];
        if ( $is_front_img )
        {
            $update_data["front_side_img"] = $data['filename'];
        }
        else if ( $is_back_img )
        {
            $update_data["back_side_img"] = $data['filename'];
        }
        else if ( $is_cc_img )
        {
            $update_data["cc_img"] = $data['filename'];
        }
        else if ( $is_patent_img )
        {
            $update_data["business_patent_file"] = $data['filename'];
        }
        else
        {
            $update_data["selfie_with_img"] = $data['filename'];            
        }
        
        $document_id = $this->leads_model->save_documents( $id, $update_data );

        $return["status"] = "OK";
        $return["document_id"] = $document_id;
        $return["filename"] = $data['filename'];
        $return["path"] = site_url('leads/documents/' . $id . '/' . $data['filename']);
        echo json_encode($return);

        exit;
    }
    
    private function _handle_save_password()
    {
        $profile_id = $this->user_info->person_id;
        $current_password = $this->input->post("current_password");
        $new_password = $this->input->post("new_password");
        $retype_password = $this->input->post("retype_password");
        
        if ( $new_password != $retype_password )
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Your password and confirmation password do not match!";
            echo json_encode($return);
            exit;
        }
        
        if ( trim($current_password) == '' || !check_user_password($this->user_info, $current_password))
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Your current password is missing or incorrect!";
            echo json_encode($return);
            exit;
        }
        
        $this->leads_model->update_user_password($new_password, $profile_id);
        
        $return["status"] = "OK";
        $return["message"] = "Password successfully changed!";
        echo json_encode($return);
        exit;
    }
    
    private function _handle_save_profile()
    {
        $profile_id = $this->user_info->person_id;
        $username = $this->input->post("username");
        $email = $this->input->post("email");
        
        $test_data = [];
        $test_data["email"] = $email;
        $key_data["person_id"] = $profile_id;
        if ( trim($email) != '' && user_exists('people', $test_data, $key_data) )
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Email already exists!";
            echo json_encode($return);
            exit;
        }
        
        $test_data = [];
        $test_data["username"] = $username;
        $key_data["person_id"] = $profile_id;
        if ( trim($username) != '' && user_exists('employees', $test_data, $key_data) )
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Username already exists!";
            echo json_encode($return);
            exit;
        }
        
        if ( trim($username) == '' || trim($email) == '' )
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Username and email are required fields!";
            echo json_encode($return);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $return["status"] = "OK";
            $return["message"] = "Error: Email is not a valid email address!";
            echo json_encode($return);
            exit;
        }
        
        $person_data["first_name"] = $this->input->post("first_name");
        $person_data["last_name"] = $this->input->post("last_name");
        $person_data["email"] = $email;
        $person_data["country"] = $this->input->post("country");
        $person_data["city"] = $this->input->post("city");
        $person_data["address_1"] = $this->input->post("address1");
        $person_data["address_2"] = $this->input->post("address2");
        
        $this->db->where("person_id", $profile_id);
        $this->db->update("people", $person_data);
        
        $user_data["username"] = trim($username);
        $user_data["email"] = $email;
        $this->db->where("person_id", $profile_id);
        $this->db->update("employees", $user_data);
        
        $return["status"] = "OK";
        $return["message"] = "Profile has been succesfully saved!";
        echo json_encode($return);
        exit;
        
    }
    
    public function documents()
    {
        //---get working directory and map it to your module
        $file = getcwd() . '/application/modules/' . implode('/', $this->uri->segments);
        
        //----get path parts form extension
        $path_parts = pathinfo($file);
        
        //---set the type for the headers
        $file_type = strtolower($path_parts['extension']);
        
        if (is_file($file))
        {
            //----write propper headers
            switch ($file_type)
            {
                case 'css':
                    header('Content-type: text/css');
                    break;

                case 'js':
                    header('Content-type: text/javascript');
                    break;

                case 'json':
                    header('Content-type: application/json');
                    break;

                case 'xml':
                    header('Content-type: text/xml');
                    break;

                case 'pdf':
                    header('Content-type: application/pdf');
                    break;
                
                case 'woff2':
                    header('Content-type: application/font-woff2');
                    readfile($file);
                    exit;
                    break;
                
                case 'woff':
                    header('Content-type: application/font-woff');
                    readfile($file);
                    exit;
                    break;
                
                case 'ttf':
                    header('Content-type: applicaton/x-font-ttf');
                    readfile($file);
                    exit;
                    break;
                case 'zip':
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false);
                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=".basename($file).";" );
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length: ".filesize($file));
                    ob_clean();
                    flush();
                    readfile($file);
                    break;

                case 'jpg' || 'jpeg' || 'png' || 'gif':
                    header('Content-type: image/' . $file_type);
                    readfile($file);
                    exit;
                    break;
            }

            include $file;
        }
        else
        {
            show_404();
        }
        exit;
    }
    
    private function _handle_signup_ajax()
    {
        $username = $this->input->post("signup_username");
        $email = $this->input->post("signup_email");
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $return["message"] = "Error: Email is not a valid email address!";
            echo json_encode($return);
            exit;
        }
        
        if ( trim($username) == '' )
        {
            $return["message"] = "Error: Please enter your username!";
            send($return);
        }
        
        if ( trim($email) == '' )
        {
            $return["message"] = "Error: Please enter your email!";
            send($return);
        }
        
        if ( $this->_is_exists($username, 'username') )
        {
            $return["message"] = "Error: Username has already been taken!";
            send($return);
        }
        
        if ( $this->_is_exists($email, 'email') )
        {
            $return["message"] = "Error: Email has already been taken!";
            send($return);
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
        $data = [];
        $data["username"] = $username;
        $data["temp_password"] = $password;
        
        $html = $this->load->view('leads/email_templates/welcome_email', $data, true);
        
        $email_data['from_email'] = 'signup@softreliance.com';
        $email_data['from_name'] = 'SoftReliance';
        $email_data['to_email'] = $email;
        $email_data['subject'] = 'Welcome to SoftReliance - leads';
        $email_data['html'] = $html;
        
        custom_send_email( $email_data );
        
        $return["status"] = "OK";
        send($return);
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
    
    private function _handle_reset_password_ajax()
    {
        $username_email = $this->input->post("reset_username_email");
        
        if ( trim($username_email) == '' )
        {
            $return["message"] = "Error: Please enter your username or the email address that you used to register!";
            send($return);
        }
        
        $is_email = false;
        if (filter_var($username_email, FILTER_VALIDATE_EMAIL)) 
        {
            $is_email = true;
        }
        
        if ( $is_email )
        {
            $this->db->where("email", $username_email);
        }
        else
        {
            $this->db->where("username", $username_email);            
        }
        
        $query = $this->db->get("employees");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $user = $query->row();
            
            $password = $this->random_password();        

            $this->db->where("person_id", $user->person_id);
            $this->db->update("employees", ["password" => md5($password)]);

            // Send email
            $data = [];
            $data["username"] = $user->username;
            $data["temp_password"] = $password;

            $html = $this->load->view('leads/email_templates/password_reset_email', $data, true);

            $email_data['from_email'] = 'resetpassword@softreliance.com';
            $email_data['from_name'] = 'SoftReliance';
            $email_data['to_email'] = $user->email;
            $email_data['subject'] = 'Password Reset';
            $email_data['html'] = $html;

            custom_send_email( $email_data );
            
            $return["status"] = "OK";
            send($return);
        }
        else
        {
            $return["message"] = "Error: User doesn't exists!";
            send($return);
        }
    }
    
    function dashboard()
    {
        $this->data["title"] = "SoftReliance - leads";
        $this->data["meta_description"] = "Borrow money fast";
        $this->data["meta_keywords"] = "K-Loan Plugin, PHP application, easy to install";
        
        $this->data["articles"] = $this->leads_model->get_articles(["published" => 1]);
        
        $this->data["default_apply_amount"] = 3000;
        $this->data["default_interest_rate"] = 17;
        $this->data["default_first_payment_date"] = date("Y-m-d", strtotime("+15 days"));
        
        $user_info = $this->Employee->get_logged_in_employee_info();
        $id = $user_info->person_id;        
        
        $tmp = $this->get_current_data($user_info->person_id);
        $this->data["current_outstanding_balance"] = $tmp["current_outstanding_balance"];
        $this->data["current_num_payments"] = $tmp["current_num_payments"];
        $this->data["current_approve_loan_amount"] = $tmp["current_approve_loan_amount"];
        
        $this->data["user_info"] = $user_info;
        $this->data["current_loan_transactions"] = $this->_get_current_loan_transactions( $user_info->person_id );
        
        $this->load->view("leads/front/secure/home", $this->data);
    }
    
    private function get_current_data( $customer_id )
    {
        $tmp = [];
        $tmp["current_outstanding_balance"] = 0;
        $tmp["current_num_payments"] = 0;
        $tmp["current_approve_loan_amount"] = 0;
        
        $sql = "
            SELECT SUM(l.loan_balance) outstanding_balance, IF(l.loan_status = 'approved', SUM(l.apply_amount), 0)  approved_loan_amount, 

                    GROUP_CONCAT(l.loan_id) loan_ids

            FROM c19_loans l 
            LEFT JOIN c19_loan_products lp ON lp.id = l.loan_product_id
            WHERE l.customer_id = $customer_id AND l.loan_balance > 0                 
            ";
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $tmp["current_outstanding_balance"] = $row->outstanding_balance;
            $tmp["current_approve_loan_amount"] = $row->approved_loan_amount;
            
            $loan_ids = explode(",", $row->loan_ids);
            if ( is_array($loan_ids) && count($loan_ids) > 0 )
            {
                $this->db->select("COUNT(*) num_payments");
                $this->db->where("customer_id", $customer_id);
                $this->db->where_in("loan_id", $loan_ids);
                $q = $this->db->get("loan_payments");
                
                if ( $q && $q->num_rows() > 0 )
                {
                    $r = $q->row();
                    $tmp["current_num_payments"] = $r->num_payments;
                }
            }
        }
        
        return $tmp;
    }
    
    private function _get_current_loan_transactions( $customer_id )
    {
        $sql = "
            SELECT lp.product_name loan_product_name, l.* FROM c19_loans l 
            LEFT JOIN c19_loan_products lp ON lp.id = l.loan_product_id
            WHERE l.customer_id = $customer_id AND l.loan_balance > 0                
            ";
        $query = $this->db->query( $sql );
        
        $tmp = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $obj = new stdClass();
                $obj->loan_id = $row->loan_id;
                $obj->loan_product_name = $row->loan_product_name;
                $obj->applied_amount = to_currency($row->apply_amount);
                $obj->applied_date = date( $this->config->item("date_format"), $row->loan_applied_date );
                $obj->application_status = $row->loan_status;
                
                $tmp[] = $obj;
            }
        }
        
        return $tmp;
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch ( $type )
        {
            case 1: // submit step1
                $this->_handle_submit_step1();
                break;
            case 2: // submit step2
                $this->_handle_submit_step2();
                break;
            case 3: // submit step3
                $this->_handle_submit_step3();
                break;
            case 4: // submit step4
                $this->_handle_submit_step4();
                break;
            case 5: // submit step5
                $this->_handle_submit_step5();
                break;
            case 6: // Generate passcode
                $this->_handle_generate_passcode();
                break;
            case 7: // validate session
                $this->_handle_validate_session();
                break;
            case 8: // logout session
                $this->_handle_logout_ajax();
                break;
            case 9: // Update bank details
                $this->_handle_update_bank_details();
                break;
            case 10: // Get leads detail
                $this->_get_leads_details();
                break;
            case 11: // Approve leads application
                $this->_handle_approve_leads_application();
                break;
            case 12: // Save article
                $this->_handle_save_article();
                break;
            case 13: // Publish article
                $this->_handle_publish_article();
                break;
            case 14: // Delete article
                $this->_handle_delete_article();
                break;
            case 15: // Delete application
                $this->_handle_delete_application();
                break;
            case 16: // Secure page - save info
                $this->_handle_secure_save_info();
                break;
            case 17: // Get transactions
                $this->_dt_transactions();
                break;
            case 18: // Apply new Loan
                $this->_handle_apply_new_loan();
                break;
            case 19: // Get employee list
                $this->_dt_employees();
                break;
	    case 20:
                $this->_dt_loan_transactions();
                break;	
            case 21: // Account statements
                $this->_dt_statements();
                break;
            case 22: // Assign branch
                $this->_handle_assign_branch();
                break;
        }
    }
    
    private function _handle_assign_branch()
    {
        $customer_id = $this->input->post("customer_id");
        $branch_id = $this->input->post("branch_id");
        
        $this->db->where("person_id", $customer_id);
        $this->db->update("customers", ["branch_id" => $branch_id]);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_apply_new_loan()
    {
        $apply_amount = $this->input->post("apply_amount");
        $loan_product_id = $this->input->post("loan_product_id");
        
        $user_info = $this->Employee->get_logged_in_employee_info();        
        $user_id = $user_info->person_id;        
        $leads = $this->leads_model->get_info( $user_id );
        
        $loan_product = $this->leads_model->get_loan_product_info( $loan_product_id );
        
        $monthly_income = $leads->net_monthly_income > 0 ? $leads->net_monthly_income : 0;
        
        if ( $apply_amount > ($monthly_income/2) )
        {
            $return["status"] = "Failed";
            $return["msg"] = "The amount you entered is not permitted because your monthly income is too low";
            send($return);
        }
        
        $first_payment_date = date($this->config->item('date_format'), strtotime(date('Y-m-d', strtotime("+30 days"))));
        
        $data["InterestType"] = $loan_product->interest_type;
        $data["NoOfPayments"] = $loan_product->term;
        $data["ApplyAmt"] = $apply_amount;
        $data["TotIntRate"] = $loan_product->interest_rate;
        $data["InstallmentStarted"] = $first_payment_date;
        $data["PayTerm"] = $loan_product->term_period;
        $data["exclude_sundays"] = 0;
        $data["penalty_amount"] = 0.00;
        $data["penalty_type"] = 'percentage';        
        $data["grace_period_days"] = ''; 
            
        $result = $this->Loan->get_loan_schedule($data);
        
        $loan_amount = 0;
        if ( $result )
        {
            foreach ( $result as $sched )
            {
                $loan_amount += $sched["payment_amount"];
            }
        }
        
        $agent_id = 1;
        
        $insert_data = [];
        $insert_data["account"] = $user_id;
        $insert_data["description"] = "New Lead application Date: " . date('M d, Y H:i:s');
        $insert_data["customer_id"] = $user_id;
        $insert_data["loan_amount"] = $loan_amount;
        $insert_data["loan_balance"] = $loan_amount;
        $insert_data["loan_status"] = "pending";
        $insert_data["loan_agent_id"] = $agent_id;
        $insert_data["loan_applied_date"] = strtotime(date("Y-m-d"));
        $insert_data["loan_payment_date"] = strtotime(date('Y-m-d', strtotime("+30 days")));
        $insert_data["apply_amount"] = $apply_amount;
        $insert_data["interest_rate"] = $loan_product->interest_rate;
        $insert_data["interest_type"] = $loan_product->interest_type;
        $insert_data["payment_term"] = $loan_product->term;
        $insert_data["payment_start_date"] = strtotime(date('Y-m-d', strtotime("+30 days")));        
        $insert_data["penalty_value"] = 0;
        $insert_data["penalty_type"] = "percentage";
        $insert_data["loan_product_id"] = $loan_product->id;
        $insert_data["periodic_loan_table"] = json_encode($result);
        
        // Now, Let's add an entry to Loan table
        $this->db->insert("loans", $insert_data);
        $loan_id = $this->db->insert_id();
        
        $return["status"] = "OK";
        
        send($return);
    }
    
    private function _handle_secure_save_info()
    {
        $id = $this->session->userdata('user_info') && $this->session->userdata('user_info')->id > 0 ? $this->session->userdata('user_info')->id : $this->session->userdata('session_leads_id');   
        
        unset($_POST["type"]);
        
        foreach ( $_POST as $key => $value )
        {
            if ( $key == 'birth_date' )
            {
                $_POST["birth_date"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('birth_date')) : $this->input->post('birth_date');
            }
            
            if ( $key == 'password' && trim($value) == '' )
            {
                unset($_POST["password"]);                
            }
            
            if ( $key == 'password' && trim($value) != '' )
            {
                $_POST["password"] = md5(trim($value));
            }
        }
        
        $this->db->where("id", $id);
        $this->db->update("leads", $_POST);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_approve_leads_application()
    {
        $leads_id = $this->input->post("leads_id");
        
        // Next, Let's mark the leads application approved
        $this->db->where("id", $leads_id);
        $this->db->update("leads", ["application_status" => "approved"]);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _get_leads_details()
    {
        $this->load->model("Loan");
        $leads_id = $this->input->post("leads_id");        
        $leads = $this->leads_model->get_info( $leads_id );
        $leads_application = $this->leads_model->get_leads_application( $leads_id );
        $loan_info = $this->Loan->get_info($leads_application->loan_id);
        
        if (is_plugin_active("branches") )
        {
            $this->load->model('branches/Branch_model');
            $data["branches"] = $this->Branch_model->get_branches();
        }
        
        $data["leads"] = $leads;
        $data["loan_info"] = $loan_info;
        
        $return["leads_info"] = $this->load->view('leads/cms/tabs/leads_info', $data, true);
        
        $return["leads_full_name"] = $leads->full_name;
        $return["leads_id"] = $leads_id;
        $return["application_status"] = $leads->application_status;
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_update_bank_details()
    {
        $leads_id = $this->input->post("leads_id");
        
        $bank_name = $this->input->post("bank_name");
        $account_number = $this->input->post("account_number");
        $account_holder_name = $this->input->post("account_holder_name");
        
        if ( $bank_name == '' ) send(['message' => 'Bank name is a required field']);
        if ( $account_number == '' ) send(['message' => 'Account Number is a required field']);
        if ( $account_holder_name == '' ) send(['message' => 'Account Holder Name is a required field']);
        
        $data["bank_name"] = $bank_name;
        $data["account_number"] = $account_number;
        $data["account_holder_name"] = $account_holder_name;
        
        if ($this->leads_model->save($leads_id, $data))
        {
            $return["status"] = "OK";
            send($return);
        }
    }
    
    private function _handle_logout_ajax()
    {
        $this->leads_model->logout(0);
        echo json_encode(["status" => "OK"]);
        exit;
    }
    
    public function logout()
    {
        $this->leads_model->logout(0);
        redirect("leads");
    }
    
    private function login_check($email, $pass_code)
    {

        if (!$this->leads_model->login($email, $pass_code))
        {

            $this->form_validation->set_message('login_check', "Invalid email/password");

            return false;
        }

        return true;
    }
    
    private function _handle_validate_session()
    {
        $email = $this->input->post("email");
        $pass_code = $this->input->post("pass_code");
        
        if ( $this->login_check($email, $pass_code) )
        {
            $return["status"] = "OK";
            $return["url"] = site_url('leads/dashboard');
            send($return);
        }
        
        $return["message"] = "Invalid email/password";
        send($return);
    }
    
    private function _handle_generate_passcode()
    {
        $digits = 4;
        $pass_code = rand(pow(10, $digits-1), pow(10, $digits)-1);
        
        $email = $this->input->post("email");
        
        $this->db->where("email", $email);
        $this->db->update("leads", ["pass_code" => $pass_code]);
        
        $html = "
            Hi, <br/>
            <br/>
            This is your generated passcode: $pass_code <br/>
            <br/>
            Regards,
            ";
        
        // TODO: send an email notification to the user about his pass_code
        $email_data["from_email"] = "support@softreliance.com";
        $email_data["to_email"] = $email;
        $email_data["subject"] = "Generated pass code!";
        $email_data["html"] = $html;
        
        custom_send_email($email_data);
        
        $return["status"] = "OK";
        
        send($return);
    }
    
    private function _handle_submit_step1()
    {
        $leads_id = $this->input->post("leads_id");
        $id_type = $this->input->post("type_of_document");
        $id_no = $this->input->post("document_number");
        $gender = $this->input->post("gender");
        $birth_date = $this->input->post("date_of_birth");
        $password = $this->input->post("password");
        $receive_promo_notifications = $this->input->post("receive_promo_notifications");
        
        $first_name = $this->input->post("application_first_name");
        $middle_name = $this->input->post("application_middle_name");
        $last_name = $this->input->post("application_last_name");
        
        if ( $first_name == '' ) send(['message' => 'First Name is a required field']);
        if ( $last_name == '' ) send(['message' => 'Last Name is a required field']);
        if ( $id_type == '' ) send(['message' => 'Type of document is a required field']);
        if ( $id_no == '' ) send(['message' => 'Document number is a required field']);
        if ( $birth_date == '' ) send(['message' => 'Date of birth is a required field']);
        
        $tmp = explode("-", $birth_date);
        
        $data["first_name"] = $first_name;
        $data["middle_name"] = $middle_name;
        $data["last_name"] = $last_name;
        $data["full_name"] = $first_name . " " . $last_name;
        
        $data['id_type'] = $id_type;
        $data['id_no'] = $id_no;
        $data['birth_date'] = $tmp[2] . '-' . $tmp[0] . '-' . $tmp[1];
        $data['gender'] = $gender;
        $data['password'] = $password;
        $data['receive_promo_notifications'] = $receive_promo_notifications;
        $data['nationality'] = $this->input->post("nationality");
        $data['marital_status'] = $this->input->post("marital_status");
        $data['completed_step'] = 1;
        $data['active_step'] = 2;
        if ($this->leads_model->save($leads_id, $data))
        {
            $return["status"] = "OK";
            $return["step"] = site_url('leads/steps/2');
            send($return);
        }
    }
    
    private function _handle_submit_step2()
    {
        $leads_id = $this->input->post("leads_id");
        $city = $this->input->post("city");
        $address1 = $this->input->post("address1");
        $street_no = $this->input->post("street_no");
        
        if ( $city == '' ) send(['message' => 'City is a required field']);
        if ( $address1 == '' ) send(['message' => 'Address is a required field']);
        if ( $street_no == '' ) send(['message' => 'Street is a required field']);
        
        $data['city'] = $city;
        $data['address1'] = $address1;
        $data['street_no'] = $street_no;
        $data['country'] = $this->input->post("country");
        
        $data['completed_step'] = 2;
        $data['active_step'] = 3;
        if ($this->leads_model->save($leads_id, $data))
        {
            $return["status"] = "OK";
            $return["step"] = site_url('leads/steps/3');
            send($return);
        }
    }
    
    private function _handle_submit_step3()
    {
        $leads_id = $this->input->post("leads_id");
        
        $occupation = $this->input->post("occupation");
        $company_name = $this->input->post("company_name");
        $work_term = $this->input->post("work_term");
        $net_monthly_income = $this->input->post("salary");
        $company_phone = $this->input->post("company_phone");
        $guarantor_phone = $this->input->post("guarantor_phone");
        $employer_id = $this->input->post("employer");
        
        $ubo = $this->input->post("ubo");
        $cc_number = $this->input->post("cc_number");
        $vat_number = $this->input->post("vat_number");
        
        if ( $occupation == '' ) send(['message' => 'Occupation is a required field']);
        
        if ( !in_array($occupation, [12,13,14,15]) )
        {
            if ( $company_name == '' ) send(['message' => 'Company name is a required field']);
            if ( $work_term == '' ) send(['message' => 'Work term is a required field']);
            if ( $net_monthly_income == '' ) send(['message' => 'Net monthly income is a required field']);
            if ( $company_phone == '' ) send(['message' => 'Company phone is a required field']);
        }
        
        if ( $guarantor_phone == '' ) send(['message' => 'Guarantor phone is a required field']);
        
        $data['occupation'] = $occupation;
        $data['company_name'] = $company_name;
        $data['work_term'] = $work_term;
        $data['net_monthly_income'] = $net_monthly_income;
        $data['company_phone'] = $company_phone;
        $data['guarantor_phone'] = $guarantor_phone;
        $data['employer_id'] = $employer_id;
        
        $data['ubo'] = $ubo;
        $data['cc_number'] = $cc_number;
        $data['vat_number'] = $vat_number;
        
        $data['completed_step'] = 3;
        $data['active_step'] = 4;
        if ($this->leads_model->save($leads_id, $data))
        {
            $return["status"] = "OK";
            $return["step"] = site_url('leads/steps/4');
            send($return);
        }
    }
    
    private function _handle_submit_step4()
    {
        $leads_id = $this->input->post("leads_id");
        
        $data['completed_step'] = 4;
        $data['active_step'] = 5;
        if ($this->leads_model->save($leads_id, $data))
        {
            $return["status"] = "OK";
            $return["step"] = site_url('leads/steps/5');
            send($return);
        }
    }
    
    private function _handle_submit_step5()
    {
        $leads_id = $this->input->post("leads_id");
        $payment_method = $this->input->post("payment_method");
        
        if ( $payment_method == 'bank' )
        {
            $bank_name = $this->input->post("bank_name");
            $account_number = $this->input->post("account_number");
            $account_holder_name = $this->input->post("account_holder_name");

            if ( $bank_name == '' ) send(['message' => 'Bank name is a required field']);
            if ( $account_number == '' ) send(['message' => 'Account Number is a required field']);
            if ( $account_holder_name == '' ) send(['message' => 'Account Holder Name is a required field']);

            $data["bank_name"] = $bank_name;
            $data["account_number"] = $account_number;
            $data["account_holder_name"] = $account_holder_name;
        }
        else
        {
            $gcash_num = $this->input->post("gcash_num");
            if ( $gcash_num == '' ) send(['message' => 'GCASH Number is a required field']);
            $data["gcash_num"] = $gcash_num;
        }
        
        $data["payment_method"] = $payment_method;
        
        $data['completed_step'] = 5;
        $data['active_step'] = 5;
        $data['signup_complete'] = 1;
        
        unset($_SESSION['sess_user_id']);
        
        if ($this->leads_model->save($leads_id, $data))
        {
            $this->_create_loan_application( $leads_id );
            
            $return["status"] = "OK";
            $return["step"] = site_url('leads/steps/fin');
            send($return);
        }
    }
    
    private function _create_loan_application( $leads_id )
    {
        $leads = $this->leads_model->get_info( $leads_id );
        $leads_application = $this->leads_model->get_leads_application( $leads_id );
        $loan_product = $this->leads_model->get_loan_product_info( $leads_application->loan_product_id );
        
        $password = $leads->password;
        $enc_password = md5($password);
        
        $first_name = $leads->first_name;
        $last_name = $leads->last_name;
        
        // check if email already exists
        $this->db->where("email", $leads->email);
        $query = $this->db->get("people");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $person_id = $row->person_id;
            
            $update_data = [];
            $update_data["role_id"] = CUSTOMER_ROLE_ID;
            $this->db->where("person_id", $person_id);
            $this->db->update("people", $update_data);
        }
        else
        {
            $insert_data = [];
            $insert_data["first_name"] = $first_name;
            $insert_data["last_name"] = $last_name;
            $insert_data["email"] = $leads->email;
            $insert_data["address_1"] = $leads->address1 != '' ? $leads->address1 : '';
            $insert_data["city"] = $leads->city != '' ? $leads->city : '';
            $insert_data["role_id"] = CUSTOMER_ROLE_ID;
            $insert_data["phone_number"] = $leads->guarantor_phone;

            $this->db->insert("people", $insert_data);
            $person_id = $this->db->insert_id();
        }
        
        $agent_id = 1;
        
        // Let's insert to customers table
        $this->db->where("person_id", $person_id);
        $query = $this->db->get("customers");        
        
        $parent_id = $leads->employer_id > 0 ? $leads->employer_id : 0;
            
        $customer_data = [];
        $customer_data["person_id"] = $person_id;
        $customer_data["added_by"] = $agent_id;            
        $customer_data["parent_id"] = $parent_id;
        $customer_data["date_added"] = date("Y-m-d H:i:s");
        $customer_data["date_of_birth"] = strtotime($leads->birth_date);
            
        if ( $query && $query->num_rows() > 0 )
        {
            // Customer already exists
            $this->db->where("person_id", $person_id);
            $this->db->update("customers", $customer_data);
        }
        else
        {
            $this->db->insert("customers", $customer_data);
        }
        
        $this->db->where("person_id", $person_id);
        $query = $this->db->get("employees");        
        
        $user_data = [];
        $user_data["username"] = $leads->email;
        $user_data["password"] = $enc_password;
        $user_data["person_id"] = $person_id;
        $user_data["added_by"] = $agent_id;
            
        if ( $query && $query->num_rows() > 0 )
        {
            // User's logged in already exists
            $this->db->where("person_id", $person_id);
            $this->db->update("employees", $user_data);
        }
        else
        {
            $this->db->insert("employees", $user_data);
        }
        
        $this->db->where("id", $leads_id);
        $this->db->update("leads", ["customer_id" => $person_id, "password" => $enc_password]);
        
        if ( $loan_product )
        {
            $first_payment_date = date($this->config->item('date_format'), strtotime(date('Y-m-d', strtotime("+30 days"))));

            $data["InterestType"] = $loan_product->interest_type;
            $data["NoOfPayments"] = $loan_product->term;
            $data["ApplyAmt"] = $leads_application->apply_amount;
            $data["TotIntRate"] = $loan_product->interest_rate;
            $data["InstallmentStarted"] = $first_payment_date;
            $data["PayTerm"] = $loan_product->term_period;
            $data["exclude_sundays"] = 0;
            $data["penalty_amount"] = 0.00;
            $data["penalty_type"] = 'percentage';        
            $data["grace_period_days"] = ''; 

            $result = $this->Loan->get_loan_schedule($data);

            $loan_amount = 0;
            if ( $result )
            {
                foreach ( $result as $sched )
                {
                    $loan_amount += $sched["payment_amount"];
                }
            }

            $insert_data = [];
            $insert_data["account"] = $person_id;
            $insert_data["description"] = "New Lead application Date: " . date('M d, Y H:i:s');
            $insert_data["customer_id"] = $person_id;
            $insert_data["loan_amount"] = $loan_amount;
            $insert_data["loan_balance"] = $loan_amount;
            $insert_data["loan_status"] = "pending";
            $insert_data["loan_agent_id"] = $agent_id;
            $insert_data["loan_applied_date"] = strtotime($leads_application->applied_date);
            $insert_data["loan_payment_date"] = strtotime(date('Y-m-d', strtotime("+30 days")));
            $insert_data["apply_amount"] = $leads_application->apply_amount;
            $insert_data["interest_rate"] = $loan_product->interest_rate;
            $insert_data["interest_type"] = $loan_product->interest_type;
            $insert_data["payment_term"] = $loan_product->term;
            $insert_data["term_period"] = $loan_product->term_period;
            $insert_data["payment_start_date"] = strtotime(date('Y-m-d', strtotime("+30 days")));        
            $insert_data["penalty_value"] = 0;
            $insert_data["penalty_type"] = "percentage";
            $insert_data["loan_product_id"] = $loan_product->id;
            $insert_data["periodic_loan_table"] = json_encode($result);

            // Check if there's loan_id associated in the application
            if ( $leads_application->loan_id > 0 )
            {
                $loan_id = $leads_application->loan_id;
                $this->db->where("loan_id", $loan_id);
                $this->db->update("loans", $insert_data);
            }
            else
            {
                // Now, Let's add an entry to Loan table
                $this->db->insert("loans", $insert_data);
                $loan_id = $this->db->insert_id();
            }

            // Update the Leads application record
            $this->db->where("id", $leads_application->id);
            $this->db->update("leads_application", ["loan_id" => $loan_id]);
        }
        // Notify user 
        $customer = $this->Person->get_info($leads->customer_id);
        //$this->welcome_notify($customer, $password);
    }
    
    public function application()
    {
        $email = $this->input->post("application_email");
        $data["email"] = $email;
        
        $leads_id = '';
        
        if ( $this->leads_model->email_exists($email, $leads_id) )
        {
            $return['message'] = "Email already exists! Please login to your account";
            send($return);
        }
        
        $leads_id = $this->leads_model->save($leads_id, $data);
        
        $this->session->set_userdata("sess_user_id", $leads_id);
        
        if ( $leads_id > 0 )
        {
            $apply_amount = $this->input->post("application_apply_amount");
            $loan_product_id = $this->input->post("application_loan_product");
            
            $apply_data = [];
            $apply_data["leads_id"] = $leads_id;
            $apply_data["apply_amount"] = $apply_amount;
            $apply_data["loan_product_id"] = $loan_product_id;
            $apply_data["applied_date"] = date("Y-m-d H:i:s");
            
            $this->db->where("leads_id", $leads_id);
            $this->db->where("status", "pending");
            $query = $this->db->get("leads_application");
            
            if ( $query && $query->num_rows() > 0 )
            {
                $this->db->where("id", $query->row()->id);
                $this->db->update("leads_application", $apply_data);
            }
            else
            {
                $this->db->insert("leads_application", $apply_data);
            }
        }
        
        $return["status"] = "OK";
        $return["application_steps"] = site_url('leads/steps/1');
        
        send($return);
    }
    
    public function steps($step)
    {
        $data["no_header"] = true;
        
        $leads_id = $this->session->userdata("sess_user_id");
        
        if ( empty($leads_id) && $step != 'fin' )
        {
            redirect( 'leads' );
        }
        
        $data["employers"] = $this->leads_model->get_all_employers();
        
        $leads_info = $this->leads_model->get_info( $leads_id );
        
        if ( $leads_info )
        {
            $data["leads_id"] = $leads_id;

            for ($i=1; $i<= 5; $i++ )
            {
                $data["step_status"][$i] = 'progress_line__item-disabled';
                $data["step_url"][$i] = '';
            }

            $data["step_status"][1] = 'progress_line__item-active ';
            $data["step_url"][1] = site_url('leads/steps/1'); 

            for ($i=2; $i<= $leads_info->active_step; $i++ )
            {
                $data["step_status"][$i] = 'progress_line__item-active ';
                $data["step_url"][$i] = site_url('leads/steps/' . $i); 
            }

            for ($i=1; $i<= $leads_info->completed_step; $i++ )
            {
                $data["step_status"][$i] .= 'progress_line__item-complete';
            }

            $data["leads_info"] = $leads_info;
        }
        $this->load->view('leads/front/steps/' . $step, $data);
    }
    
    public function members()
    {
        $this->set_dt_orders($this->datatablelib->datatable());
        $data["tbl_leads_orders"] = $this->datatablelib->render();
        $this->load->view("leads/cms/main", $data);
    }
    
    function get_orders()
    {
        $action = $this->input->post('ajax_action');
        switch ($action)
        {
            case 'list':
                $this->_dt_orders();
                break;            
        }
    }

    function set_dt_orders($datatable, $leads_id = '')
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "leads_id" => $leads_id]);
        $datatable->ajax_url = site_url('leads/get_orders');

        $datatable->add_column('actions', false);
        $datatable->add_column('full_name', false);
        $datatable->add_column('email', false);
        $datatable->add_column('occupation', false);       
        $datatable->add_column('formatted_added_date', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[4, 'desc']];
        
        $datatable->allow_search = 1;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#leads_orders";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_orders()
    {
        $leads_id = $this->input->post("leads_id");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");        
        
        $search = $this->input->post("search")["value"];
        
        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];

        $order = array("index" => $index, "direction" => $dir);
        
        $filters = [
            "leads_id" => $leads_id
        ];
        
        $orders = $this->leads_model->get_all( $filters, $limit, $offset, $search, $order );

        $count_all = 0;
        $tmp = [];
        if ($orders)
        {
            foreach ($orders->result() as $row)
            {
                $actions = "<a href='javascript:void(0)' class='btn-xs btn-primary btn-view-details btn btn-secondary' data-leads-id='" . $row->id . "' title='View details application'><span class='fa fa-eye'></span></a> ";
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-leads-id='". $row->id ."' title='Delete'><span class='fa fa-trash'></span></a>";

                $occupations = get_occupations();
                
                $data_row = [];
                $data_row["DT_RowId"] = $row->id;
                $data_row["actions"] = $actions;
                
                $data_row["full_name"] = '<a href="javascript:void(0)" class="btn-view-details" title="View details" data-leads-id="'. $row->id .'">' . $row->full_name . '</a>';
                $data_row["email"] = $row->email;
                $data_row["occupation"] = $row->occupation > 0 ? $occupations[$row->occupation] : '';
                $data_row["apply_amount"] = to_currency($row->apply_amount, 1);
                $data_row["unpaid_amount"] = to_currency($row->unpaid_amount, 1);
                $data_row["application_status"] = ucwords($row->application_status);
                $data_row["formatted_added_date"] = date("M d, Y", strtotime($row->added_date));
                        
                $tmp[] = $data_row;
                $count_all++;
            }
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    public function article($article_id)
    {
        $article = $this->leads_model->get_article($article_id);
        $this->data["title"]= $article->title;
        $this->data["content"]= $article->content;
        $this->data["articles"] = $this->leads_model->get_articles(["published" => 1]);
        $this->load->view("leads/front/article", $this->data);
    }
    
    public function articles()
    {
        $this->set_dt_articles($this->datatablelib->datatable());
        $data["tbl_leads_articles"] = $this->datatablelib->render();
        
        $this->load->view("leads/cms/articles", $data);
    }
    
    function get_articles()
    {
        $action = $this->input->post('ajax_action');
        switch ($action)
        {
            case 'list':
                $this->_dt_articles();
                break;            
        }
    }
    
    function set_dt_articles($datatable, $article_id = '')
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "article_id" => $article_id]);
        $datatable->ajax_url = site_url('leads/get_articles');

        $datatable->add_column('actions', false);
        $datatable->add_column('title', false);
        $datatable->add_column('content', false);
        $datatable->add_column('formatted_added_date', false);
        $datatable->add_column('added_by', false);
        $datatable->add_column('published', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'asc']];
        
        $datatable->allow_search = 1;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#leads_articles";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_articles()
    {
        $article_id = $this->input->post("article_id");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");        
        
        $search = $this->input->post("search")["value"];
        
        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];

        $order = array("index" => $index, "direction" => $dir);
        
        $filters = [
            "article_id" => $article_id
        ];
        
        $orders = $this->leads_model->get_articles( $filters, $limit, $offset, $search, $order );

        $count_all = 0;
        $tmp = [];
        if ($orders)
        {
            foreach ($orders->result() as $row)
            {
                $actions = "<a href='" . site_url('leads/view_article/' . $row->article_id) . "' class='btn-xs btn-primary btn' data-article-id='" . $row->article_id . "' title='Edit Article'><span class='fa fa-pencil'></span></a> ";
                
                if ( $row->published )
                {
                    $actions .= "<a href='javascript:void(0)' class='btn-xs btn-default btn-publish btn btn-secondary' data-published='0' data-article-id='" . $row->article_id . "' title='Un-publish article'><span class='fa fa-times'></span></a> ";
                }
                else
                {
                    $actions .= "<a href='javascript:void(0)' class='btn-xs btn-primary btn-publish btn' data-published='1' data-article-id='" . $row->article_id . "' title='Publish article'><span class='fa fa-check'></span></a> ";
                }
                
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-article-id='". $row->article_id ."' title='Delete article'><span class='fa fa-trash'></span></a>";

                $data_row = [];
                $data_row["DT_RowId"] = $row->article_id;
                $data_row["actions"] = $actions;
                
                $data_row["title"] = '<a href="' . site_url('leads/view_article/' . $row->article_id) . '" data-article-id="'. $row->article_id .'">' . $row->title . '</a>';
                $data_row["content"] = truncate_html($row->content, 150);
                $data_row["formatted_added_date"] = date($this->config->item("date_format"), $row->added_date);
                $data_row["added_by"] = $row->added_by_name;
                $data_row["published"] = $row->published ? 'Yes' : 'No';
                        
                $tmp[] = $data_row;
                $count_all++;
            }
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function view_article($article_id = -1)
    {
        $article_info = new stdClass();
        $article_info->title = '';
        $article_info->content = '';
        $article_info->published = '';
        
        if ( $article_id > 0 )
        {
            $article_info = $this->leads_model->get_article($article_id);
        }
        
        $this->data["article_info"] = $article_info;
        $this->data["article_id"] = $article_id;
        $this->load->view('leads/cms/view_article', $this->data);
    }
    
    function _handle_save_article()
    {
        $article_id = $this->input->post("article_id");
        $title = $this->input->post("title");
        $content = $this->input->post("content");
        
        $added_by = $this->Employee->get_logged_in_employee_info()->person_id;
        $added_date = strtotime(date("Y-m-d H:i:s"));
        $published = $this->input->post("published") > 0;
        
        $data['title'] = $title;
        $data['content'] = $content;
        $data['added_by'] = $added_by;
        $data['added_date'] = $added_date;
        $data['published'] = $published;
        
        $article_id = $this->leads_model->save_article($article_id, $data);
        
        $return["status"] = "OK";
        $return["message"] = "Article '$title' has been save successfully";
        $return["path"] = site_url('leads/view_article/' . $article_id);
        send($return);
    }
    
    function _handle_publish_article()
    {
        $article_id = $this->input->post("article_id");
        $published = $this->input->post("published");
        $this->leads_model->save_article($article_id, ["published" => $published]);
        
        $return["status"] = "OK";
        send($return);
    }
    
    function _handle_delete_article()
    {
        $article_id = $this->input->post("article_id");
        
        $this->db->where("article_id", $article_id);
        $this->db->delete("leads_articles");
        
        $return["status"] = "OK";
        send($return);
    }
    
    function _handle_delete_application()
    {
        $leads_id = $this->input->post("leads_id");
        
        $this->db->where("id", $leads_id);
        $this->db->delete("leads");
        
        $return["status"] = "OK";
        send($return);
    }
    
    function personal_info()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        $customer_id = $user_info->customer_id;
        
        $this->data["user_info"] = $user_info;  
        $this->data['person_info'] = $this->Customer->get_info($customer_id);
        $this->data["customer_id"] = $this->data["person_info"]->id;
        $this->data["employers"] = $this->leads_model->get_all_employers();
        
        $financial_infos = "";
        if (isset($this->data['person_info']->income_sources))
        {
            $financial_infos = json_decode($this->data['person_info']->income_sources, true);
        }

        $tmp = array();

        if (is_array($financial_infos))
        {
            foreach ($financial_infos as $financial_info):
                if ($financial_info !== '=')
                {
                    $tmp[] = explode("=", $financial_info);
                }
            endforeach;
        }

        if (count($tmp) === 0)
        {
            $tmp[] = array("", "");
        }

        $attachments = $this->Customer->get_attachments($customer_id);

        $file = array();
        foreach ($attachments as $attachment)
        {
            $file[] = $this->_get_formatted_file($attachment->attachment_id, $attachment->filename);
        }

        $this->data["extra_fields"] = $this->Customer->get_extra_fields();
        $this->data['attachments'] = $file;

        $this->data['customer_id'] = $customer_id;
        $this->data['financial_infos'] = $tmp;
        
        $this->set_dt_statements($this->datatablelib->datatable());
        $this->data["tbl_account_statements"] = $this->datatablelib->render();

        $this->load->view("leads/front/secure/personal_info", $this->data);
    }
    
    function save_profile($customer_id = -1)
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
     
        $employer_id = $this->input->post("employer");
        
        $person_data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
            'phone_number' => $this->input->post('phone_number'),
            'address_1' => $this->input->post('address_1'),
            'address_2' => $this->input->post('address_2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'zip' => $this->input->post('zip'),
            'country' => $this->input->post('country'),
            'comments' => $this->input->post('comments')
        );
        
        if ( trim($this->input->post("password")) != trim($this->input->post("repassword")))
        {
            echo json_encode(array('success' => false, 'message' => ' Password don\'t match!' .
                $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => -1));
            exit;
        }

        $int_date_of_birth = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_of_birth'))) : strtotime($this->input->post('date_of_birth'));
        
        $customer_data = array(
            'account_number' => $this->input->post('account_number') == '' ? null : $this->input->post('account_number'),
            'taxable' => $this->input->post('taxable') == '' ? 0 : 1,
            'bank_name' => $this->input->post('bank_name'),
            'bank_account_num' => $this->input->post('bank_account_num'),
            'date_of_birth' => $int_date_of_birth,
            'parent_id' => $employer_id
        );
        
        $extra_fields = $this->Customer->get_extra_fields();
        foreach ( $extra_fields as $field )
        {
            $customer_data[$field->name] = $this->input->post($field->name);
        }

        if (is_array($this->input->post("sources")))
        {
            $income_sources = array();
            $i = 0;
            foreach ($this->input->post("sources") as $sources)
            {
                $tmp = $this->input->post("values");
                $income_sources[] = $sources . "=" . $tmp[$i];
                $i++;
            }
        }

        $financial_data = array(
            "financial_status_id" => $this->input->post("financial_status_id") > 0 ? $this->input->post("financial_status_id") : 0,
            "income_sources" => json_encode($income_sources)
        );

        if ($this->Customer->save($person_data, $customer_data, $customer_id, $financial_data))
        {
            $lead_data = [];
            $lead_data["full_name"] = $person_data["first_name"] . " " . $person_data["last_name"];
            $lead_data["email"] = $person_data["email"];
            $lead_data["city"] = $person_data["city"];
            $lead_data["address1"] = $person_data["address_1"];
            $lead_data["signup_complete"] = 1;
            $lead_data["customer_id"] = $customer_data['person_id'];
            $lead_data["id_type"] = $this->input->post("id_type");
            $lead_data["id_no"] = $this->input->post("id_no");
            $lead_data["gender"] = $this->input->post("gender");
            $lead_data["birth_date"] = $this->input->post("date_of_birth");
            $lead_data["street_no"] = $this->input->post("street_no");
            $lead_data["occupation"] = $this->input->post("occupation");
            $lead_data["company_name"] = $this->input->post("company_name");
            $lead_data["work_term"] = $this->input->post("work_term");
            $lead_data["net_monthly_income"] = $this->input->post("net_monthly_income");
            $lead_data["company_phone"] = $this->input->post("company_phone");
            $lead_data["guarantor_phone"] = $this->input->post("guarantor_phone");
            $lead_data["bank_name"] = $this->input->post("bank_name");
            $lead_data["account_holder_name"] = $this->input->post("account_holder_name");
            $lead_data["account_number"] = $this->input->post("account_number");
            $lead_data["ubo"] = $this->input->post("ubo");
            $lead_data["cc_number"] = $this->input->post("cc_number");
            $lead_data["vat_number"] = $this->input->post("vat_number");
            $lead_data["employer_id"] = $employer_id;
            
            $lead_data["business_name"] = $this->input->post("business_name");
            $lead_data["company_name"] = trim($lead_data["business_name"]) != '' ? $lead_data["business_name"] : $lead_data["company_name"];
            $lead_data["business_address"] = $this->input->post("business_address");
            $lead_data["business_type"] = $this->input->post("business_type");
            $lead_data["business_nif"] = $this->input->post("business_nif");
            $lead_data["business_legal_structure"] = $this->input->post("business_legal_structure");
            $lead_data["business_financial_institution"] = $this->input->post("business_financial_institution");
            $lead_data["business_account_name"] = $this->input->post("business_account_name");
            $lead_data["business_phone"] = $this->input->post("business_phone");
            $lead_data["business_payroll_date"] = $this->input->post("business_payroll_date");
            $lead_data["business_total_employees"] = $this->input->post("business_total_employees");
            $lead_data["business_agent_record"] = $this->input->post("business_agent_record");

            if ( trim($this->input->post("password")) != '' )
            {
                $password = $this->input->post("password");
                $lead_data["password"] = md5($password);
            }

            $this->leads_model->save_customer($lead_data);
            
            if ( $this->input->post("notify_customer") == '1' && trim($password) != '' )
            {
                $id = $customer_id == -1 ? $lead_data["customer_id"] : $customer_id;
                $customer = $this->Person->get_info($id);
                //$this->password_reset_notify($customer, $password);
            }
            
            //New customer
            if ($customer_id == -1)
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_adding') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_data['person_id']));
            }
            else //previous customer
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_updating') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_id));
            }
        }
        else//failure
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('customers_error_adding_updating') . ' ' .
                $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => -1));
        }
    }
    
    function address()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["person_info"] = $this->data["user_info"] = $user_info;        
        $this->data["customer_id"] = $this->data["person_info"]->id;
        $this->data["person_info"]->photo_url = '';
        
        $this->load->view("leads/front/secure/address", $this->data);
    }
    
    function employment()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["employers"] = $this->leads_model->get_all_employers();
        $this->data["person_info"] = $this->data["user_info"] = $user_info;        
        $this->data["customer_id"] = $this->data["person_info"]->id;
        $this->data["person_info"]->photo_url = '';
        
        $this->load->view("leads/front/secure/employment", $this->data);
    }
    
    function docs()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["person_info"] = $this->data["user_info"] = $user_info;        
        $this->data["customer_id"] = $this->data["person_info"]->id;
        $this->data["person_info"]->photo_url = '';
        
        $this->load->view("leads/front/secure/documents", $this->data);
    }
    
    function bank_info()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["person_info"] = $this->data["user_info"] = $user_info;        
        $this->data["customer_id"] = $this->data["person_info"]->id;
        $this->data["person_info"]->photo_url = '';
        
        $this->load->view("leads/front/secure/bank_info", $this->data);
    }
    
    function test()
    {
        $this->load->model("Loan");
        
        $data["InterestType"] = 'flat';
        $data["NoOfPayments"] = 6;
        $data["ApplyAmt"] = 50000;
        $data["TotIntRate"] = 6.00;
        $data["InstallmentStarted"] = '15/09/2020';
        $data["PayTerm"] = 'month';
        //$data["ajax_type"] = 1;
        $data["exclude_sundays"] = 0;
        $data["penalty_amount"] = 0.00;
        $data["penalty_type"] = 'percentage';        
        $data["grace_period_days"] = ''; 
            
        $result = $this->Loan->get_loan_schedule($data);
        
        var_dump($result);
    }

    function loan_history()
    {
        $user_info = $this->Employee->get_logged_in_employee_info();
        
        $id = $user_info->person_id;
        
        $this->data["user_info"] = $user_info;
        
        $this->data["loan_products"] = $this->leads_model->get_loan_products();
        
        $this->load->library('DataTableLib');

        $this->set_dt_transactions($this->datatablelib->datatable());
        $this->data["tbl_loan_transactions"] = $this->datatablelib->render();
        $this->load->view('leads/front/secure/reports/loan_history', $this->data);
    }
    
    function set_dt_transactions($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 17]);
        $datatable->ajax_url = site_url('leads/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('id', false);
        $datatable->add_column('net_proceeds', false);
        $datatable->add_column('loan_amount', false);
        $datatable->add_column('loan_balance', false);
        $datatable->add_column('formatted_applied_date', false);
        $datatable->add_column('formatted_loan_approved_date', false);
        $datatable->add_column('formatted_payment_date', false);
        $datatable->add_column('loan_status', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;

        $datatable->table_id = "#tbl_loans_transactions";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_transactions()
    {
        $status = $this->input->post("status");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $loans = $this->leads_model->get_loan_history($limit, $offset, $keywords, $order, $status);
        
        $tmp = array();

        $count_all = 0;
        foreach ($loans->result() as $loan)
        {
            $loan_status = $loan->loan_status;
            if ($loan->loan_balance <= 0)
            {
                $loan_status = "Paid";
            }

            
            $actions = "<a href='" . site_url('loans/fix_breakdown/' . $loan->loan_id) . "' target='_blank' class='btn-xs btn-default btn btn-secondary' title='Print Schedule'><span class='fa fa-file-o'></span></a> ";
            
            
            $fees = json_decode($loan->misc_fees, true);
            
            $total_fees = 0;
            if ( is_array($fees) && count($fees) > 0 )
            {
                foreach ( $fees as $name => $amount )
                {
                    $total_fees += $amount;
                }
            }

            $data_row = [];
            $data_row["DT_RowId"] = $loan->loan_id;
            $data_row["actions"] = $actions;
            $data_row["id"] = $loan->loan_id;
            $data_row["description"] = $loan->description;
            $data_row["net_proceeds"] = $loan->net_proceeds > 0 ? to_currency($loan->net_proceeds) : to_currency($loan->apply_amount - $total_fees);
            $data_row["loan_amount"] = to_currency($loan->loan_amount);
            $data_row["loan_balance"] = to_currency($loan->loan_balance);
            $data_row["customer"] = $loan->customer_name;
            $data_row["agent"] = $loan->agent_name;
            $data_row["approved_by"] = $loan->approver_name;
            $data_row["formatted_applied_date"] = $loan->loan_applied_date > 0 ? date($this->config->item('date_format'), $loan->loan_applied_date) : '';
            $data_row["formatted_loan_approved_date"] = $loan->loan_approved_date > 0 ? date($this->config->item('date_format'), $loan->loan_approved_date) : '';
            $data_row["formatted_payment_date"] = ($loan->loan_payment_date > 0) ? date($this->config->item('date_format'), $loan->loan_payment_date) : '';
            $data_row["loan_status"] = $loan->loan_balance > 0 ? ucwords($loan->loan_status) : 'Paid';


            $tmp[] = $data_row;
            $count_all++;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    public function password_reset()
    {
        $email = $this->input->post("email");
        
        if ( trim($email) == '' )
        {
            $return["msg"] = "Please enter a valid email!";
            send($return);
        }
            
        // Check if email is found the people records
        $this->db->where("email", $email);
        $query = $this->db->get("people");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $customer = $query->row();
            $password = $this->random_password();
            
            $this->db->where("customer_id", $customer->person_id);
            $this->db->update("leads", ["password" => md5($password)]);
            
            $this->password_reset_notify($customer, $password);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function password_reset_notify($customer, $password)
    {
        $email = $customer->email;
            
        $html = "
        Dear " . $customer->first_name . ",<br/><br/>
        Your new login details to the portal are as follows: <br/><br/>

        " . site_url('leads') . " <br/><br/>
        Email: " . $email . " <br/>
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
    }
    
    private function welcome_notify($customer, $password)
    {
        $email = $customer->email;
            
        $html = "
        Dear " . $customer->first_name . ",<br/><br/>
        Your new login details to the portal are as follows: <br/><br/>

        " . site_url('leads') . " <br/><br/>
        Email: " . $email . " <br/>
        Password: $password <br/><br/>

        Regards,<br/><br/>

            ";

        $email_data = [];
        $email_data["from_name"] = 'noreply';
        $email_data["from_email"] = 'noreply@support.com';
        $email_data["to_email"] = $email;
        $email_data["subject"] = "Welcome to Portal";
        $email_data["html"] = $html;
        
        custom_send_email($email_data);
    }
    
    function loan_transactions()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["user_info"] = $user_info;
        
        $this->data["loan_products"] = $this->leads_model->get_loan_products();
        
        $this->load->library('DataTableLib');

        $this->set_dt_loan_transactions($this->datatablelib->datatable());
        $this->data["tbl_loan_transactions"] = $this->datatablelib->render();
        $this->load->view('leads/front/secure/reports/loan_transactions', $this->data);
    }
    
    function set_dt_loan_transactions($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 19]);
        $datatable->ajax_url = site_url('leads/ajax');

        $datatable->add_column('trans_id', false);
        $datatable->add_column('description', false);
        $datatable->add_column('amount', false);
        $datatable->add_column('type', false);
        $datatable->add_column('trans_date', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;

        $datatable->table_id = "#tbl_loans_transactions";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_loan_transactions()
    {
        $status = $this->input->post("status");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $user_info = $this->leads_model->get_info($this->session->userdata("session_leads_id"));
        
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        
        $filters = [];
        $filters["customer_id"] = $user_info->customer_id;
        $filters["from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($from_date)) : strtotime($from_date);
        $filters["to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($to_date)) : strtotime($to_date);
        
        $transactions = $this->leads_model->get_loan_transactions($limit, $offset, $filters);
        
        $tmp = array();

        $count_all = 0;
        foreach ($transactions as $transaction)
        {
            $data_row = [];
            $data_row["DT_RowId"] = $transaction->id;
            $data_row["trans_id"] = $transaction->trans_id;
            $data_row["description"] = $transaction->description;
            $data_row["amount"] = $transaction->amount;
            $data_row["type"] = $transaction->trans_type;
            $data_row["trans_date"] = date($this->config->item('date_format'), strtotime($transaction->added_date));

            $tmp[] = $data_row;
            $count_all++;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function my_employees()
    {
        if (!$this->leads_model->is_logged_in())
        {
            redirect('leads');
        }
        
        $id = $this->session->userdata('session_leads_id');
        
        $user_info = $this->leads_model->get_details($id);
        
        $this->data["user_info"] = $user_info;
        
        $this->data["loan_products"] = $this->leads_model->get_loan_products();
        
        $this->load->library('DataTableLib');

        $this->set_dt_employees($this->datatablelib->datatable());
        $this->data["tbl_employees"] = $this->datatablelib->render();
        $this->load->view('leads/front/secure/employees/list', $this->data);
    }
    
    function set_dt_employees($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 19]);
        $datatable->ajax_url = site_url('leads/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('last_name', false);
        $datatable->add_column('first_name', false);
        $datatable->add_column('email', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;

        $datatable->table_id = "#tbl_employees";
        $datatable->add_titles('leads');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_employees()
    {
        $current_user_id = $this->session->userdata("session_leads_id");
        $user_info = $this->leads_model->get_details($current_user_id);
        
        $status = $this->input->post("status");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $count_all = 0;

        $filters = [];
        $filters["offset"] = $offset;
        $filters["limit"] = $limit;
        $clients = $this->leads_model->get_my_employees($user_info->customer_id, $filters, $count_all);
        
        $tmp = array();

        foreach ($clients as $client)
        {
            $actions = "<a href='" . site_url('leads/employee/' . $client->person_id) . "' class='btn-xs btn-default btn btn-secondary' title='View'><span class='fa fa-eye'></span></a> ";
            
            $data_row = [];
            $data_row["DT_RowId"] = $client->person_id;
            $data_row["actions"] = $actions;
            $data_row["last_name"] = $client->last_name;
            $data_row["first_name"] = $client->first_name;
            $data_row["email"] = $client->email;            

            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function employee($id = -1)
    {
        $this->data['person_info'] = $this->Customer->get_info($id);
        
        if ( !empty($_POST) )
        {
            $this->_handle_post();
        }

        $financial_infos = "";
        if (isset($this->data['person_info']->income_sources))
        {
            $financial_infos = json_decode($this->data['person_info']->income_sources, true);
        }

        $tmp = array();

        if (is_array($financial_infos))
        {
            foreach ($financial_infos as $financial_info):
                if ($financial_info !== '=')
                {
                    $tmp[] = explode("=", $financial_info);
                }
            endforeach;
        }

        if (count($tmp) === 0)
        {
            $tmp[] = array("", "");
        }

        $attachments = $this->Customer->get_attachments($id);

        $file = array();
        foreach ($attachments as $attachment)
        {
            $file[] = $this->_get_formatted_file($attachment->attachment_id, $attachment->filename);
        }

        $this->data['attachments'] = $file;

        $this->data['financial_infos'] = $tmp;
        
        $this->data['customer_id'] = $id;
        
        $this->set_dt_statements($this->datatablelib->datatable());
        $this->data["tbl_account_statements"] = $this->datatablelib->render();
        
        $this->load->view("leads/front/secure/employees/form", $this->data);
    }
    
    private function _handle_post()
    {
        $this->load->model("customers/Customer");
        
        $customer_id = $this->input->post("customer_id");
        
        // Save a copy to leads table with password if possible
        $current_user_id = $this->session->userdata("session_leads_id");
        $current_user = $this->leads_model->get_details($current_user_id);
        
        $person_data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
            'phone_number' => $this->input->post('phone_number'),
            'address_1' => $this->input->post('address_1'),
            'address_2' => $this->input->post('address_2'),
            'city' => $this->input->post('city'),
            'state' => $this->input->post('state'),
            'zip' => $this->input->post('zip'),
            'country' => $this->input->post('country'),
            'comments' => $this->input->post('comments')
        );
        
        if ( trim($this->input->post("password")) != trim($this->input->post("repassword")))
        {
            echo json_encode(array('success' => false, 'message' => ' Password don\'t match!' .
                $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => -1));
            exit;
        }

        $int_date_of_birth = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_of_birth'))) : strtotime($this->input->post('date_of_birth'));
        
        $customer_data = array(
            'account_number' => $this->input->post('account_number') == '' ? null : $this->input->post('account_number'),
            'taxable' => $this->input->post('taxable') == '' ? 0 : 1,
            'bank_name' => $this->input->post('bank_name'),
            'bank_account_num' => $this->input->post('bank_account_num'),
            'date_of_birth' => $int_date_of_birth,
            'parent_id' => $current_user->customer_id
        );
        
        $extra_fields = $this->Customer->get_extra_fields();
        foreach ( $extra_fields as $field )
        {
            $customer_data[$field->name] = $this->input->post($field->name);
        }

        if (is_array($this->input->post("sources")))
        {
            $income_sources = array();
            $i = 0;
            foreach ($this->input->post("sources") as $sources)
            {
                $tmp = $this->input->post("values");
                $income_sources[] = $sources . "=" . $tmp[$i];
                $i++;
            }
        }

        $financial_data = array(
            "financial_status_id" => $this->input->post("financial_status_id") > 0 ? $this->input->post("financial_status_id") : 0,
            "income_sources" => json_encode($income_sources)
        );

        if ($this->Customer->save($person_data, $customer_data, $customer_id, $financial_data))
        {
            $lead_data = [];
            $lead_data["full_name"] = $person_data["first_name"] . " " . $person_data["last_name"];
            $lead_data["email"] = $person_data["email"];
            $lead_data["city"] = $person_data["city"];
            $lead_data["address1"] = $person_data["address_1"];
            $lead_data["signup_complete"] = 1;
            $lead_data["customer_id"] = ($customer_id == -1) ? $customer_data['person_id'] : $customer_id;
            $lead_data["employer_id"] = $current_user->customer_id;
            
            if ( trim($this->input->post("password")) != '' )
            {
                $lead_data["password"] = md5($this->input->post("password"));
            }
            
            $this->leads_model->save_employee($lead_data);
            
            //New customer
            if ($customer_id == -1)
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_adding') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_data['person_id']));
            }
            else //previous customer
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_updating') . ' ' .
                    $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => $customer_id));
            }
        }
        else//failure
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('customers_error_adding_updating') . ' ' .
                $person_data['first_name'] . ' ' . $person_data['last_name'], 'person_id' => -1));
        }
        
        exit;
    }
    
    function set_dt_statements($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 21]);
        $datatable->ajax_url = site_url('leads/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('description', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;

        $datatable->table_id = "#tbl_account_statements";
        $datatable->add_titles('Statements');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_statements()
    {
        $customer_id = $this->input->post("customer_id");
        
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $filters = [
            "customer_id" => $customer_id
        ];
        
        $loans = $this->Loan->get_all($limit, $offset, $keywords, $order, '', '', $filters);
        $count_all = $this->Loan->get_all($limit, $offset, $keywords, $order, '', '', $filters, 1);
        
        $tmp = array();

        foreach ($loans->result() as $loan)
        {
            $loan_status = $loan->loan_status;
            if ($loan->loan_balance <= 0)
            {
                $loan_status = "Paid";
            }
            
            $actions = "<a href='" . site_url('loans/statement/' . $loan->loan_id) . "' target='_blank' class='btn-xs btn-default btn btn-secondary' title='Generate Account Statement'><span class='fa fa-file-pdf-o'></span></a> ";
            
            $data_row = [];
            $data_row["DT_RowId"] = $loan->loan_id;
            $data_row["actions"] = $actions;
            $data_row["description"] = "Loan #:" . $loan->loan_id . ( trim($loan->description)!='' ?  ", " . $loan->description : '');

            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
}
