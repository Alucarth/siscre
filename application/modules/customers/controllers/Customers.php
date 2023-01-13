<?php

require_once (APPPATH . "controllers/Person_controller.php");

class Customers extends Person_controller {

    function __construct()
    {
        parent::__construct('customers');
        
        $this->load->model("leads/leads_model");
        $this->load->helper("leads/leads");
        
        $this->load->library('DataTableLib');
    }

    function index()
    {
        $res = $this->Employee->getLowerLevels();
        $data['staffs'] = $res;

        $data['controller_name'] = strtolower(get_class());
        
        $data["extra_fields"] = $this->Customer->get_extra_fields();
        
        if ( is_plugin_active('branches') )
        {
            $this->load->model('branches/Branch_model');
            $data["branches"] = $this->Branch_model->get_branches();
        }
        
        $this->set_dt_borrowers($this->datatablelib->datatable());
        $data["tbl_borrowers"] = $this->datatablelib->render();
        
        $this->load->view('customers/list', $data);
    }
    
    function ajax()
    {
        $type = $this->input->post('type');
        switch ($type)
        {
            case 1: // Get customers table
                $this->_dt_borrowers();
                break;
            case 2: // Archive customer
                $this->delete();
                break;
            case 3: // Add extra fields
                $this->add_extra_fields();
                break;
            case 4: // Add extra fields
                $this->remove_extra_fields();
                break;
            case 5: // Password reset
                $this->handle_password_reset();
                break;
            case 6: // Account statements
                $this->_dt_statements();
                break;
	    case 7: // Get documents table
                $this->_dt_documents();	
                break;
        }
    }
    
    private function handle_password_reset()
    {
        $customer_id = $this->input->post("customer_id");
        $is_notify = $this->input->post("is_notify");
        $password = $this->input->post("password");
        $repassword = $this->input->post("repassword");
        
        $customer = $this->Person->get_info($customer_id);
        
        if ( !$customer_id )
        {
            $return["msg"] = "¡No tienes permiso para actualizar este registro!";
            send($return);
        }
        
        if ( trim($password) == '' )
        {
            $return["msg"] = "¡La contraseña es un campo requerido!";
            send($return);
        }
        
        if ( trim($repassword) == '' )
        {
            $return["msg"] = "¡Confirmar contraseña es un campo obligatorio!";
            send($return);
        }
        
        if ( $password != $repassword )
        {
            $return["msg"] = "¡La contraseña no coincide!";
            send($return);
        }
        
        $update_data = ["password" => md5($password)];
        
//        $this->db->where("customer_id", $customer_id);
//        $this->db->update("leads", $update_data);
        
        $this->db->where("person_id", $customer_id);
        $this->db->update("employees", $update_data);
        
        if ( $is_notify )
        {
            //$this->password_reset_notify($customer, $password);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function password_reset_notify($customer, $password)
    {
        $email = $customer->email;
            
        $html = "
        Querido/a " . $customer->first_name . ",<br/><br/>
        Sus nuevos datos de inicio de sesión en el portal son los siguientes: <br/><br/>

        " . site_url('leads') . " <br/><br/>
        Correo: " . $email . " <br/>
        Contraseña: $password <br/><br/>

        Saludos,<br/><br/>

            ";

        $email_data = [];
        $email_data["from_name"] = 'noreply';
        $email_data["from_email"] = 'noreply@support.com';
        $email_data["to_email"] = $email;
        $email_data["subject"] = "Restablecimiento de contraseña";
        $email_data["html"] = $html;

        custom_send_email($email_data);
    }
    
    function remove_extra_fields()
    {
        $ids = $this->input->post("ids");
        
        $this->db->where_in( "id", $ids );
        $query = $this->db->get("customer_fields");
        
        $column_names = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $column_names[] = "DROP COLUMN `" . $row->name . "`";
                
                $result = $this->db->query("SHOW COLUMNS FROM `c19_customers` LIKE '" . $row->name . "'");
                $exists = ( $result && $result->num_rows() > 0 ? 1 : 0 );
                if ( $exists )
                {
                    $sql = " ALTER TABLE `c19_customers` DROP COLUMN `" . $row->name . "`";
                    $this->db->query( $sql );
                }
            }
        }
        
        foreach($ids as $id)
        {
            $this->db->where( "id", $id );
            $this->db->delete("customer_fields");
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    function add_extra_fields()
    {
        $field_names = $this->input->post("field_names");
        $show_to_list = $this->input->post("show_to_list");
        $label = $this->input->post("label");
        
        $i = 0;
        $insert_data = [];
        foreach( $field_names as $field_name )
        {
            $field_name = str_replace(" ", "_", strtolower($field_name));
            
            $this->db->where("name", $field_name);
            $query = $this->db->get("customer_fields");
            
            if ( $query && $query->num_rows() > 0 )
            {
                
            }
            else
            {
                $insert_data["name"] = $field_name;
                $insert_data["label"] = $label[$i];
                $insert_data["show_to_list"] = $show_to_list[$i];
                $insert_data["data_type"] = "text";
                $this->db->insert("customer_fields", $insert_data);
            }
            
            // Alter customers table and add the column if not exists
            $result = $this->db->query("SHOW COLUMNS FROM `c19_customers` LIKE '" . $field_name . "'");
            $exists = ( $result && $result->num_rows() > 0 ? 1 : 0 );
            if ( !$exists )
            {
                $sql = "ALTER TABLE `c19_customers`
                        ADD COLUMN `" . $field_name . "` VARCHAR(255) NULL DEFAULT NULL";
                $this->db->query( $sql );
            }
            
            $i++;
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    function set_dt_borrowers($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1]);
        $datatable->ajax_url = site_url('customers/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('id', false);
        $datatable->add_column('full_name', false);
        /*$datatable->add_column('first_name', false);*/
        $datatable->add_column('id_no', false);
        $datatable->add_column('address_1', false);
        $datatable->add_column('email', false);
        $datatable->add_column('phone_number', false);
        if ( is_plugin_active('branches') )
        {
            $datatable->add_column('branch_name', false);            
        }
        
        $extra_fields = $this->Customer->get_extra_fields();
        foreach( $extra_fields as $field )
        {
            if ( $field->show_to_list )
            {
                $datatable->add_column($field->name);
            }
        }

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#tbl_borrowers";
        $datatable->add_titles('Borrowers');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_borrowers()
    {
        $selected_user = $this->input->post("employee_id");
        $status = $this->input->post("status");

        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $filters = [];
        if ( is_plugin_active('branches') )
        {
            $filters["branch_id"] = $this->input->post("branch_id");
        }
        $people = $this->Customer->get_all($limit, $offset, $keywords, $order, $selected_user, 0, $filters);
        $count_all = $this->Customer->get_all($limit, $offset, $keywords, $order, $selected_user, 1, $filters);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        
        $extra_fields = $this->Customer->get_extra_fields();

        $tmp = array();

        foreach ($people->result() as $person)
        {
            $actions = "<a href='" . site_url('customers/view/' . $person->person_id) . "' class='btn btn-xs btn-default btn-secondary' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "customers", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-customer-id='" . $person->person_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }

            $data_row = [];
            $data_row["DT_RowId"] = $person->person_id;
            $data_row["actions"] = $actions;
            
            $data_row["id"] = $person->person_id;
            $data_row["full_name"] = $person->first_name." ".$person->last_name;
            /*$data_row["last_name"] = $person->last_name;
            $data_row["first_name"] = $person->first_name;*/
            $data_row["id_no"] = $person->person_id;
            $data_row["address_1"] = $person->address_1;
            $data_row["email"] = $person->email;
            $data_row["phone_number"] = $person->phone_number;
            if ( is_plugin_active('branches') )
            {
                $data_row["branch_name"] = $person->branch_name;                
            }
            
            foreach( $extra_fields as $field )
            {
                if ( $field->show_to_list )
                {
                    $new_field = $field->name;
                    $data_row[$new_field] = $person->$new_field;
                }
            }

            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        if ( $this->input->post("no_json") == '1' )
        {
            return $data;
        }
        else
        {
            send($data);
        }
    }

    /*
      Returns customer table data rows. This will be called with AJAX.
     */

    function search()
    {
        $search = $this->input->post('search');
        $data_rows = get_people_manage_table_data_rows($this->Customer->search($search), $this);
        echo $data_rows;
    }

    /*
      Gives search suggestions based on what is being searched for
     */

    function suggest()
    {
        //$suggestions = $this->Customer->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
        $suggestions = $this->Customer->get_search_suggestions($this->input->post('query'), 30);
        //echo implode("\n", $suggestions);

        $data = $tmp = array();

        foreach ($suggestions as $suggestion):
            $t = explode("|", $suggestion);
            $tmp = array("value" => $t[1], "data" => $t[0]);
            $data[] = $tmp;
        endforeach;

        echo json_encode(array("suggestions" => $data));
        exit;
    }

    /*
      Loads the customer edit form
     */

    function view($customer_id = -1)
    {
        $data['person_info'] = $this->Customer->get_info($customer_id);
        $data["employers"] = $this->leads_model->get_all_employers();

        $financial_infos = "";
        if (isset($data['person_info']->income_sources))
        {
            $financial_infos = json_decode($data['person_info']->income_sources, true);
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

        $data['attachments'] = $file;

        $data['customer_id'] = $customer_id;
        $data['financial_infos'] = $tmp;
        $data['statuses'] = $this->_get_statuses();
        
        $data["extra_fields"] = $this->Customer->get_extra_fields();
        
        if ( is_plugin_active('branches') )
        {
            $this->load->model('branches/Branch_model');
            $data["branches"] = $this->Branch_model->get_branches();
        }
        
        $this->set_dt_statements($this->datatablelib->datatable());
        $data["tbl_account_statements"] = $this->datatablelib->render();
	
	$this->set_dt_documents($this->datatablelib->datatable(), $customer_id);
        $data["tbl_documents"] = $this->datatablelib->render();  

        $this->load->view("customers/form", $data);
    }
    
    private function _get_statuses()
    {
        $tmp = [
            "active" => "Active",
            "pd1_7" => "PD 1 to 7",
            "legal" => "Legal",
            "closed" => "Closed",
            "cancelled" => "Cancelled",
        ];
        
        return $tmp;
    }
    
    function set_dt_documents($datatable, $customer_id)
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "type" => 7, 
            "customer_id" => $customer_id
        ];
        $datatable->add_server_params('', '', $params);
        $datatable->ajax_url = site_url('customers/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('document_name', false);
        $datatable->add_column('descriptions', false);
        $datatable->add_column('modified_date', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#tbl_documents";
        $datatable->add_titles('Documents');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_documents()
    {
        $this->load->model("Document_model");
        
        $customer_id = $this->input->post("customer_id");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        
        $tmp = array();
        $count_all = 0;
        
        $filters = [];
        $filters["customer_id"] = $customer_id;
        $filters["document_type"] = 'customer_document';
        $filters["order_by"] = 'modified_date DESC';
        $documents = $this->Document_model->get_list($filters, $count_all);

        foreach ($documents as $document)
        {
            $actions = "<a href='" . base_url($document->document_path) . "' target='_blank' class='btn btn-xs btn-default btn-secondary' title='View'><span class='fa fa-download'></span></a> ";
            
            if ( check_access($user_info->role_id, "customers", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-document-id='" . $document->document_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }

            $data_row = [];
            $data_row["DT_RowId"] = $document->document_id;
            $data_row["actions"] = $actions;
            
            $data_row["document_name"] = $document->document_name;
            $data_row["descriptions"] = $document->descriptions;
            $data_row["modified_date"] = date($this->config->item('date_format') ." H:i:s", strtotime($document->added_date));
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    private function _get_formatted_file($id, $filename)
    {
        $words = array("doc", "docx", "odt");
        $xls = array("xls", "xlsx", "csv");
        $tmp = explode(".", $filename);
        $ext = $tmp[1];

        if (in_array(strtolower($ext), $words))
        {
            $tmp['icon'] = "images/word-filetype.jpg";
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
        }
        else if (strtolower($ext) === "pdf")
        {
            $tmp['icon'] = "images/pdf-filetype.jpg";
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
        }
        else if (in_array(strtolower($ext), $xls))
        {
            $tmp['icon'] = "images/xls-filetype.jpg";
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
        }
        else
        {
            $tmp['icon'] = "images/image-filetype.jpg";
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
        }

        return $tmp;
    }

    /*
      Inserts/updates a customer
     */

    function save($customer_id = -1)
    {
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
            'comments' => $this->input->post('comments'),
            'role_id' => CUSTOMER_ROLE_ID
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
        
        if ( is_plugin_active('branches') )
        {
            $customer_data["branch_id"] = $this->input->post("branch_id");
        }
        
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
            // Save/Update photo URL
            $this->_update_photo_url($customer_data['person_id']);
            
            // $myfile = fopen("php_error_logs.txt", "w") or die("Unable to open file!");
            // $txt = $customer_data['person_id']."\n";
          

            // $middle_name = $this->input->post("middle_name"); //este campo no esta en el formulario y estaba generando error
            $middle_name = ' ';
            
            $lead_data = [];
            $lead_data["full_name"] = $person_data["first_name"] . " " . $middle_name . " " . $person_data["last_name"];
            $lead_data["first_name"] = $person_data["first_name"];
            $lead_data["middle_name"] = $middle_name;
            $lead_data["last_name"] = $person_data["last_name"];
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
            $lead_data["gcash_num"] = $this->input->post("gcash_num");
            
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

            // fwrite($myfile, $txt);
            // $txt = json_encode($lead_data)."\n";
            // fwrite($myfile, $txt);
            // fclose($myfile);

            $this->leads_model->save_customer($lead_data);
            
            $this->update_user_loggedin($customer_data['person_id'], $lead_data);
            
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
    
    private function update_user_loggedin($customer_id, $lead_data)
    {
        $this->db->where("person_id", $customer_id);
        $query = $this->db->get("employees");
        
        $user_data = [];
        $user_data["username"] = $lead_data["email"];
        
        $password = $this->input->post("password");
        
        if ( trim($password) != '' )
        {
            $user_data["password"] = md5($password);
        }
        
        $user_data["person_id"] = $customer_id;
        $user_data["added_by"] = 1;
        
        if ( $query && $query->num_rows() > 0 )
        {
            $this->db->where("person_id", $customer_id);
            $this->db->update("employees", $user_data);
        }
        else
        {
            $this->db->insert("employees", $user_data);
        }
    }
    
    private function _update_photo_url($customer_id)
    {
        $this->load->model("Document_model");
        
        if ( empty($_FILES['photo_url']['name']) )
        {
            return true;
        }
        
        $path = FCPATH . "/uploads/profile-$customer_id/";
        if ( !is_dir($path) )
        {
            mkdir($path, 0777, true);
        }
        
        $this->load->library('uploader');
        
        $_FILES['file']['name'] = str_replace(' ', '_', $_FILES['photo_url']['name']);
        $_FILES['file']['tmp_name'] = $_FILES['photo_url']['tmp_name'];
        $_FILES['file']['error'] = $_FILES['photo_url']['error'];
        
        $data = $this->uploader->upload($path);
        
        $doc_data = [];
        $doc_data["document_name"] = $data['filename'];
        $doc_data["descriptions"] = "Profile Photo";
        $doc_data["document_path"] = "/uploads/profile-$customer_id/" . $data['filename'];
        $doc_data["document_type"] = "profile_photo";
        $doc_data["foreign_id"] = $customer_id;
        $doc_data["added_date"] = date("Y-m-d H:i:s");
        
        $document_id = $this->Document_model->save( '', $doc_data );
        
        $this->db->where("person_id", $customer_id);
        $this->db->update("people", ["photo_url" => $data['filename']]);
    }

    /*
      This deletes customers from the customers table
     */

    function delete()
    {
        $customers_to_delete = $this->input->post('ids');

        if ($this->Customer->delete_list($customers_to_delete))
        {
            echo json_encode(array('success' => true, 'message' => $this->lang->line('customers_successful_deleted') . ' ' .
                count($customers_to_delete) . ' ' . $this->lang->line('customers_one_or_multiple')));
        }
        else
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('customers_cannot_be_deleted')));
        }
    }

    function excel()
    {
        $data = file_get_contents("import_customers.csv");
        $name = 'import_customers.csv';
        force_download($name, $data);
    }

    function excel_import()
    {
        $this->load->view("customers/excel_import", null);
    }

    function do_excel_import()
    {
        $msg = 'do_excel_import';
        $failCodes = array();
        if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK)
        {
            $msg = $this->lang->line('items_excel_import_failed');
            echo json_encode(array('success' => false, 'message' => $msg));
            return;
        }
        else
        {
            if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
            {
                //Skip first row
                fgetcsv($handle);

                $i = 1;
                while (($data = fgetcsv($handle)) !== FALSE)
                {
                    $person_data = array(
                        'first_name' => $data[0],
                        'last_name' => $data[1],
                        'email' => $data[2],
                        'phone_number' => $data[3],
                        'address_1' => $data[4],
                        'address_2' => $data[5],
                        'city' => $data[6],
                        'state' => $data[7],
                        'zip' => $data[8],
                        'country' => $data[9],
                        'comments' => $data[10]
                    );

                    $customer_data = array(
                        'account_number' => $data[11] == '' ? null : $data[11],
                        'taxable' => $data[12] == '' ? 0 : 1,
                    );

                    if (!$this->Customer->save($person_data, $customer_data))
                    {
                        $failCodes[] = $i;
                    }

                    $i++;
                }
            }
            else
            {
                echo json_encode(array('success' => false, 'message' => 'Your upload file has no data or not in supported format.'));
                return;
            }
        }

        $success = true;
        if (count($failCodes) > 1)
        {
            $msg = "Most customers imported. But some were not, here is list of their CODE (" . count($failCodes) . "): " . implode(", ", $failCodes);
            $success = false;
        }
        else
        {
            $msg = "Import Customers successful";
        }

        echo json_encode(array('success' => $success, 'message' => $msg));
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width()
    {
        return 350;
    }

    function data()
    {
        $sel_user = $this->input->get("employee_id");
        $index = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 1;
        $dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : "asc";
        $order = array("index" => $index, "direction" => $dir);
        $length = isset($_GET['length'])?$_GET['length']:50;
        $start = isset($_GET['start'])?$_GET['start']:0;
        $key = isset($_GET['search']['value'])?$_GET['search']['value']:"";

        $people = $this->Customer->get_all($length, $start, $key, $order, $sel_user);

        $format_result = array();

        foreach ($people->result() as $person)
        {
            $format_result[] = array(
                "<input type='checkbox' name='chk[]' id='person_$person->person_id' value='" . $person->person_id . "'/>",
                $person->last_name,
                $person->first_name,
                $person->email,
                $person->phone_number,
                anchor('customers/view/' . $person->person_id, $this->lang->line('common_view'), array('class' => 'btn btn-success', "title" => "Update Customer"))
            );
        }

        $data = array(
            "recordsTotal" => $this->Customer->count_all(),
            "recordsFiltered" => $this->Customer->count_all(),
            "data" => $format_result
        );

        echo json_encode($data);
        exit;
    }
    
    function upload_profile_pic()
    {
        $directory = FCPATH . 'uploads/profile-' . $_REQUEST["user_id"] . "/";
        $this->load->library('uploader');
        $data = $this->uploader->upload($directory);

        $this->Customer->save_profile_pic($data['params']['user_id'], $data);

        $return = [
            "status" => "OK", 
            "token_hash" => $this->security->get_csrf_hash()
        ];
        echo json_encode($return);
        exit;
    }
    
    function upload_attachment()
    {
        $directory = FCPATH . 'uploads/customer-' . $_REQUEST["customer_id"] . "/";
        $this->load->library('uploader');
        $data = $this->uploader->upload($directory);

        $this->Customer->save_attachments($data['params']['customer_id'], $data);

        $file = $this->_get_formatted_file($data['attachment_id'], $data['filename']);
        $file['customer_id'] = $data['params']['customer_id'];
        $file['token_hash'] = $this->security->get_csrf_hash();

        echo json_encode($file);
        exit;
    }
    
    function remove_file()
    {
        $file_id = $this->input->post("file_id");
        $return = array(
            "status" => $this->Customer->remove_file($file_id),
            "token_hash" => $this->security->get_csrf_hash()
        );
        echo json_encode($return);
        exit;
    }

    function customer_search()
    {
        $suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('query'), 30);
        $data = $tmp = array();

        foreach ($suggestions as $suggestion):
            $t = explode("|", $suggestion);
            $tmp = array("value" => $t[1], "data" => $t[0], "email" => $t[2]);
            $data[] = $tmp;
        endforeach;

        echo json_encode(array("suggestions" => $data));
        exit;
    }
    
    function set_dt_statements($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 6]);
        $datatable->ajax_url = site_url('customers/ajax');

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
    
    public function upload()
    {
        $this->load->model("Document_model");
        
        $customer_id = $this->input->post('customer_id');
        $document_name = $this->input->post('document_name');
        $descriptions = $this->input->post('descriptions');
        
        $path = FCPATH . "/downloads/customers-$customer_id/";
        if ( !is_dir($path) )
        {
            mkdir($path, 0777, true);
        }
        
        $this->load->library('uploader');
        
        $_FILES['file']['name'] = str_replace(' ', '_', $_FILES['file']['name']);

        $data = $this->uploader->upload($path);
        
        if ( empty($data["filename"]) )
        {
            $return["status"] = "FAILED";
            send($return);
        }
        
        $doc_data = [];
        $doc_data["document_name"] = $document_name;
        $doc_data["descriptions"] = $descriptions;
        $doc_data["document_path"] = "/downloads/customers-$customer_id/" . $data['filename'];
        $doc_data["document_type"] = "customer_document";
        $doc_data["foreign_id"] = $customer_id;
        $doc_data["added_date"] = date("Y-m-d H:i:s");
        
        $document_id = $this->Document_model->save( '', $doc_data );

        $return["status"] = "OK";
        $return["document_id"] = $document_id;
        $return["filename"] = $data['filename'];
        $return["path"] = base_url("downloads/customers-$customer_id/" . $data['filename']);
        
        send($return);
    }
    
    public function export_csv()
    {
        $_POST["no_json"] = 1;
        $data = $this->_dt_borrowers();
        
        $delim = ",";
        $newline = "\n";
        $enclosure = '"';

        $out = '';
        
        $aHeadings = [
            "Last Name",
            "First Name",
            "Bank Name",
            "Bank Account #",
            "Email",
            "Phone Number",
        ];
        
        if ( is_plugin_active('branches') )
        {
            $aHeadings[] = "Branch";
        }
        
        $extra_fields = $this->Customer->get_extra_fields();
        foreach( $extra_fields as $field )
        {
            if ( $field->show_to_list )
            {
                $aHeadings[] = $field->name;
            }
        }
        
        foreach ($aHeadings as $heading)
        {
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $heading) . $enclosure . $delim;
        }
        
        $out = rtrim($out);
        $out .= $newline;
        
        foreach ( $data['data'] as $row )
        {
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["last_name"]) . $enclosure . $delim;
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["first_name"]) . $enclosure . $delim;
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["bank_name"]) . $enclosure . $delim;
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["bank_account_num"]) . $enclosure . $delim;
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["email"]) . $enclosure . $delim;
            $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["phone_number"]) . $enclosure . $delim;
            
            if ( is_plugin_active('branches') )
            {
                $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row["branch_name"]) . $enclosure . $delim;
            }
            
            $extra_fields = $this->Customer->get_extra_fields();
            foreach( $extra_fields as $field )
            {
                if ( $field->show_to_list )
                {
                    $out .= $enclosure . str_replace($enclosure, $enclosure . $enclosure, $row[$field->name]) . $enclosure . $delim;
                }
            }
            
            $out = rtrim($out);
            $out .= $newline;
        }
        
        header("Content-type: text/csv");
        header("Content-Disposition: inline; filename=customers-" . date('YmdHis') . ".csv");
        header("Pragma: public");
        header("Expires: 0");
        ini_set('zlib.output_compression', '0');
        //echo $out;
        file_put_contents(FCPATH . 'downloads/reports/customers.csv', $out);
        $return["url"] = base_url('downloads/reports/customers.csv');
        $return["status"] = "OK";
        send($return);
    }
}

?>