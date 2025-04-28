<?php

require_once ("Person_controller.php");

class Customers extends Person_controller {
    function __construct()
    {
        parent::__construct('customers');
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
            case 5: // Get documents table
                $this->_dt_documents();
        }
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
        $datatable->add_column('last_name', false);
        $datatable->add_column('first_name', false);
        $datatable->add_column('address_1', false);
        $datatable->add_column('address_2', false);
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
            
            $data_row["last_name"] = $person->last_name;
            $data_row["first_name"] = $person->first_name;
            $data_row["address_1"] = $person->address_1;
            $data_row["address_2"] = $person->address_2;
            $data_row["bank_name"] = $person->bank_name;
            $data_row["bank_account_num"] = $person->bank_account_num;
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

        send($data);
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
        
        $data["extra_fields"] = $this->Customer->get_extra_fields();
        
        if ( is_plugin_active('branches') )
        {
            $this->load->model('branches/Branch_model');
            $data["branches"] = $this->Branch_model->get_branches();
        }
        
        $this->set_dt_documents($this->datatablelib->datatable(), $customer_id);
        $data["tbl_documents"] = $this->datatablelib->render();    

        $this->load->view("customers/form", $data);
    }
    
    function set_dt_documents($datatable, $customer_id)
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "type" => 5, 
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
    
     //Funcion tarea 1
     public function address_exists($address_1, $address_2)
     {
         $this->db->where('address_1', $address_1);
         $this->db->where('address_2', $address_2);
         $query = $this->db->get('c19_customers');
     
         return $query->num_rows() > 0; // Retorna verdadero si ya existe
     }
    //Funcion tarea 1     

    function save($customer_id = -1)
    {
        
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
        //tarea1 
        $existing_customer = $this->Customer->get_customer_by_address($person_data['address_1'], $person_data['city'], $person_data['state'], $person_data['zip']);
    
        if ($existing_customer && $existing_customer->customer_id != $customer_id) {
        $return["success"] = false;
        $return["message"] = "El domicilio ya está registrado con otro usuario. ¿Desea continuar de todas formas?";
        send($return);
        return;
        } //

        $int_date_of_birth = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_of_birth'))) : strtotime($this->input->post('date_of_birth'));
        
        $customer_data = array(
            'account_number' => $this->input->post('account_number') == '' ? null : $this->input->post('account_number'),
            'taxable' => $this->input->post('taxable') == '' ? 0 : 1,
            'bank_name' => $this->input->post('bank_name'),
            'bank_account_num' => $this->input->post('bank_account_num'),
            'date_of_birth' => $int_date_of_birth,
        );
        
        $error_msg = "";
        if ( trim($person_data["first_name"]) == '' )
        {
            $error_msg = ktranslate("form_validation_required", "common_first_name");
        }
        
        if ( trim($person_data["last_name"]) == '' )
        {
            $error_msg = ktranslate("form_validation_required", "common_last_name");
        }
        
        if ( $error_msg != '' )
        {
            $return["success"] = false;
            $return["message"] = $error_msg;
            send($return);
        }
        
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

        // Verificar si ya existe un cliente con la misma dirección antes de registrar
        $direccion_1 = trim(strtolower($this->input->post('address_1')));
        $direccion_2 = trim(strtolower($this->input->post('address_2')));
        
        $existe = $this->Customer->address_exists($direccion_1, $direccion_2);
        
        if ($existe) {
            echo json_encode(array('success' => false, 'message' => 'El domicilio ya está registrado por otro cliente.'));
            return;
        }
        //tarea 1

        if ($this->Customer->save($person_data, $customer_data, $customer_id, $financial_data))
        {
            // Save/Update photo URL
            $this->_update_photo_url($customer_data['person_id']);
            
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
}

?>