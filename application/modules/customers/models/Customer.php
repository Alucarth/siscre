<?php

class Customer extends Person {
    /*
      Determines if a given person_id is a customer
     */

    function exists($person_id, $email = false)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where('customers.person_id', $person_id);
        if ($email)
        {
            $this->db->or_where('people.email', $email);
        }
        $query = $this->db->get();

        return ($query->num_rows() > 0);
    }

    /*
      Returns all the customers
     */

    function get_all($limit = 10000, $offset = 0, $search = "", $order = array(), $sel_user = false, $count_only = false, $filters = [])
    {
        $sorter = array("", "p.last_name", "p.first_name", "c.bank_name", "c.bank_account_num", "p.email", "p.phone_number", "p.address_1", "p.address_2");
        
        $extra_fields = $this->Customer->get_extra_fields();
        foreach ( $extra_fields as $field )
        {
            if ( $field->show_to_list )
            {
                $sorter[] = "c." . $field->name;
            }
        }
        
        $select = "c.*, p.*, l.id_no, l.bank_name, l.account_number bank_account_num";
        if ( is_plugin_active('branches') )
        {
            $select .= ", br.*";            
        }
        
        $this->db->select($select);
        $this->db->from('customers c');
        $this->db->join('people p', 'c.person_id=p.person_id');
        $this->db->join('people pa', 'pa.person_id=c.added_by', 'left');
        $this->db->join('leads l', 'l.customer_id=c.person_id', 'left');
        if ( is_plugin_active('branches') )
        {
            $this->db->join('branches br', 'br.id=c.branch_id', 'left');            
        }
        $this->db->where('c.deleted', 0);

        $employee_id = $this->session->userdata('person_id');
        $employee_id = ($sel_user) ? $sel_user : $employee_id;
        
        $sql = "SELECT role_id FROM c19_people WHERE person_id = $employee_id";
        $q = $this->db->query( $sql );
        
        $role_id = false;
        if ( $q && $q->num_rows() > 0 )
        {
            $role_id = $q->row()->role_id;
        }

        if ($employee_id !== "1")
        {
            if ( $role_id && !$sel_user )
            {
                $this->db->where("pa.role_id", $role_id);
            }
            else
            {
                $this->db->where("c.added_by", $employee_id);
            }
        }

        if ($search !== "")
        {
            $or_where = "(
                p.first_name LIKE  '%" . $search . "%' OR
                p.last_name LIKE  '%" . $search . "%' OR
                p.phone_number LIKE  '%" . $search . "%'
            ";
            
            foreach ( $extra_fields as $field )
            {
                if ( $field->show_to_list )
                {
                    $or_where .= " OR c." . $field->name . " LIKE '%$search%'";
                }
            }
            
            $or_where .= ")";
            
            $this->db->where($or_where);
        }
        
        // if (is_plugin_active("branches"))
        // {
        //     $this->db->where("branch_id", $this->session->userdata('branch_id'));        
        // }

        if (isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("p.last_name", "asc");
        }

        if ( $count_only )
        {
            return $this->db->count_all_results();
        }
        else
        {
            $this->db->limit($limit);
            $this->db->offset($offset);
            $query = $this->db->get();
        }
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "Customers", "Viewed customers list");
        }
        
        return $query;
    }

    function count_all()
    {
        $this->db->from('customers');
        $this->db->where('deleted', 0);
        return $this->db->count_all_results();
    }

    /*
      Gets information about a particular customer
     */

    function get_info($customer_id)
    {
        $this->db->from('customers');
        $this->db->select('customers.*');
        $this->db->select('p.*');
        $this->db->select('l.*');
        $this->db->select("IF(p.country='',l.country,p.country) country");
        $this->db->select('financial_status.financial_status_id');
        $this->db->select('financial_status.income_sources');
        $this->db->join('people p', 'p.person_id = customers.person_id', 'left');
        $this->db->join('leads l', 'l.customer_id = customers.person_id', 'left');
        $this->db->join('financial_status', 'financial_status.person_id = p.person_id', 'LEFT');
        $this->db->where('customers.person_id', $customer_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0)
        {
            if (is_plugin_active('activity_log'))
            {
                $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($user_id, "Customers", "Viewed customer detail #" . $customer_id);
            }
        
            return $query->row();
        }
        else
        {
            //Get empty base parent object, as $customer_id is NOT an customer
            $person_obj = parent::get_info(-1);

            //Get all the fields from customer table
            $fields = $this->db->list_fields('customers');

            //append those fields to base parent object, we we have a complete empty object
            foreach ($fields as $field)
            {
                $person_obj->$field = '';
            }
            
            $fields = $this->db->list_fields('leads');

            //append those fields to base parent object, we we have a complete empty object
            foreach ($fields as $field)
            {
                $person_obj->$field = '';
            }

            return $person_obj;
        }
    }

    /*
      Gets information about multiple customers
     */

    function get_multiple_info($customer_ids)
    {
        $this->db->from('customers');
        $this->db->join('people', 'people.person_id = customers.person_id');
        $this->db->where_in('customers.person_id', $customer_ids);
        $this->db->order_by("last_name", "asc");
        return $this->db->get();
    }

    function get_attachments($customer_id)
    {
        $this->db->from('attachments');
        $this->db->where('customer_id', $customer_id);
        $query = $this->db->get();

        return $query->result();
    }
    
    function get_documents($customer_id)
    {
        $this->db->from('documents');
        $this->db->where('customer_id', $customer_id);
        $query = $this->db->get();

        return $query->result();
    }

    /*
      Inserts or updates a customer
     */

    function save(&$person_data, &$customer_data = [], $customer_id = false, &$financial_data = array())
    {
        $success = false;
        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();

        $employee_id = $this->Employee->get_logged_in_employee_info() ? $this->Employee->get_logged_in_employee_info()->person_id : 1;
        
        if (parent::save($person_data, $customer_data, $customer_id))
        {

            if (!$customer_id || ! $this->exists($customer_id))
            {
                $financial_data['person_id'] = $person_data['person_id'];
                $this->db->insert('financial_status', $financial_data);
                $customer_data['person_id'] = $person_data['person_id'];
                $customer_data['added_by'] = $employee_id;
                $customer_data['date_added'] = date('Y-m-d H:i:s');
                $success = $this->db->insert('customers', $customer_data);
                $this->move_attachments($customer_data);
            }
            else
            {
                if ($financial_data['financial_status_id'] > 0)
                {
                    $this->db->where('financial_status_id', $financial_data['financial_status_id']);
                    $this->db->update('financial_status', $financial_data);
                }
                else
                {
                    $financial_data['person_id'] = $customer_id;
                    $this->db->insert('financial_status', $financial_data);
                }

		$customer_data['modified_date'] = date('Y-m-d H:i:s');
                $customer_data['person_id'] = isset($person_data['person_id']) ? $person_data['person_id'] : $customer_id;
                $this->db->where('person_id', $customer_id);
                $success = $this->db->update('customers', $customer_data);
            }
        }
        
        if (is_plugin_active('activity_log'))
        {
            if ( $customer_id > 0 )
            {
                track_action($employee_id, "Customers", "Updated customer details #" . $customer_id );
            }
        }

        $this->db->trans_complete();
        return $success;
    }

    function move_attachments($customer_data)
    {
        $linker = $this->session->userdata('linker');

        $this->db->from('attachments');
        $this->db->where('session_id', $linker);
        $query = $this->db->get();

        $this->db->where('session_id', $linker);
        $this->db->update('attachments', array("customer_id" => $customer_data['person_id']));

        $attachments = $query->result();
        foreach ($attachments as $attachment)
        {
            $tmp_dir = FCPATH . "uploads/customer-/";
            $user_dir = FCPATH . "uploads/customer-" . $customer_data['person_id'] . "/";

            if (!file_exists($user_dir))
            {
                // temporary set to full access
                @mkdir($user_dir);
            }

            $target_dist = $user_dir . $attachment->filename;

            if (file_exists($tmp_dir . $attachment->filename))
            {
                copy($tmp_dir . $attachment->filename, $target_dist);
                unlink($tmp_dir . $attachment->filename);
            }
        }
    }

    /*
      Deletes one customer
     */

    function delete($customer_id)
    {
        $this->db->where('person_id', $customer_id);
        return $this->db->update('customers', array('deleted' => 1));
    }

    /*
      Deletes a list of customers
     */

    function delete_list($customer_ids)
    {
        $this->db->where_in('person_id', $customer_ids);
        $this->db->delete('customers');
        
        // delete this person from employee table as well
        $this->db->where_in('person_id', $customer_ids);
        $this->db->delete('employees');
        
        // delete this person from people table as well
        $this->db->where_in('person_id', $customer_ids);
        $this->db->delete('people');
        
        // Check if customer is also in leads record and delete
        $this->db->where_in('customer_id', $customer_ids);
        $this->db->delete('leads');
        
        if (is_plugin_active('activity_log'))
        {
            $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
            if ( is_array($customer_ids) )
            {
                foreach ( $customer_ids as $to_delete )
                {
                    track_action($employee_id, "Customers", "Deleted customer #" . $to_delete);
                }
            }
            else
            {
                track_action($employee_id, "Customers", "Deleted customer #" . $customer_ids);
            }
        }
        
        return true;
    }

    /*
      Get search suggestions to find customers
     */

    function get_search_suggestions($search, $limit = 25)
    {
        $suggestions = array();

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        $this->db->order_by("last_name", "asc");
        $by_name = $this->db->get();
        foreach ($by_name->result() as $row)
        {
            $suggestions[] = $row->first_name . ' ' . $row->last_name;
        }

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("email", $search);
        $this->db->order_by("email", "asc");
        $by_email = $this->db->get();
        foreach ($by_email->result() as $row)
        {
            $suggestions[] = $row->email;
        }

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("phone_number", $search);
        $this->db->order_by("phone_number", "asc");
        $by_phone = $this->db->get();
        foreach ($by_phone->result() as $row)
        {
            $suggestions[] = $row->phone_number;
        }

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        $this->db->like("account_number", $search);
        $this->db->order_by("account_number", "asc");
        $by_account_number = $this->db->get();
        foreach ($by_account_number->result() as $row)
        {
            $suggestions[] = $row->account_number;
        }

        //only return $limit suggestions
        if (count($suggestions > $limit))
        {
            $suggestions = array_slice($suggestions, 0, $limit);
        }

        return $suggestions;
    }

    /*
      Get search suggestions to find customers
     */

    function get_customer_search_suggestions($search, $limit = 25)
    {
        $suggestions = array();

        $person_info = $this->Employee->get_logged_in_employee_info();
        $user_id = $person_info->person_id;
        
        $user_ids = $this->get_view_user_ids( $person_info->role_id );
        
        $add_where = '';
        if ($user_id > 1)
        {
            $add_where = " and added_by = " . $user_id . " ";
            
            if ( count($user_ids) > 0 )
            {
                $add_where = " and (added_by = " . $user_id . " OR added_by IN (" . implode(",", $user_ids) . "))";
            }
        }

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0
                    " . $add_where . "
                    ");
        
        if (is_plugin_active("branches"))
        {
            $this->db->where("branch_id", $this->session->userdata('branch_id'));        
        }
        
        $this->db->order_by("last_name", "asc");
        $by_name = $this->db->get();
        
        foreach ($by_name->result() as $row)
        {
            $suggestions[] = $row->person_id . '|' . $row->first_name . ' ' . $row->last_name . '|' . $row->email . '|' . $row->account_number;
        }

        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where('deleted', 0);
        
        if (is_plugin_active("branches"))
        {
            $this->db->where("branch_id", $this->session->userdata('branch_id'));        
        }
        
        $this->db->like("account_number", $search);
        $this->db->order_by("account_number", "asc");
        $by_account_number = $this->db->get();
        
        foreach ($by_account_number->result() as $row)
        {
            $suggestions[] = $row->person_id . '|' . $row->account_number . '|' . $row->email . '|' . $row->account_number;
        }

        //only return $limit suggestions
        if (count($suggestions) > $limit)
        {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }
    
    private function get_view_user_ids( $role_id )
    {
        $this->load->model("role");
        $role_info = $this->role->get_info($role_id);
        
        $low_level_ids = json_decode($role_info->low_level, 1);
        
        $user_ids = [];
        if ( count($low_level_ids) > 0 )
        {
            $user_ids = $this->role->get_staff_user_ids(implode(",", $low_level_ids));
        }
        
        return $user_ids;
    }

    /*
      Preform a search on customers
     */

    function search($search)
    {
        $this->db->from('customers');
        $this->db->join('people', 'customers.person_id=people.person_id');
        $this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
        $this->db->order_by("last_name", "asc");

        return $this->db->get();
    }
    
    function save_profile_pic($customer_id, &$data)
    {
        if ($customer_id > 0)
        {
            $save_data["photo_url"] = $data["filename"];
            $this->db->where("person_id", $customer_id);
            $this->db->update("people", $save_data);
            return true;
        }
    }

    function save_attachments($customer_id, &$data)
    {
        if ($customer_id > 0)
        {
            if ($this->db->insert('attachments', array("filename" => $data['filename'], "customer_id" => $customer_id)))
            {
                $data['attachment_id'] = $this->db->insert_id();
                return true;
            }
        }

        $session_id = $data['params']['linker'];
        $this->load->library('session');
        $this->session->set_userdata(array("linker" => $session_id));
        if ($this->db->insert('attachments', array("filename" => $data['filename'], "session_id" => $session_id)))
        {
            $data['attachment_id'] = $this->db->insert_id();
            return true;
        }
    }
    
    function remove_file_bak($file_id)
    {
        $this->db->from('attachments');
        $this->db->where('attachment_id', $file_id);
        $query = $this->db->get();
        $res = $query->row();

        $user_dir = FCPATH . "uploads/customer-/";
        if ($res->loan_id > 0)
        {
            $user_dir = FCPATH . "uploads/customer-" . $res->customer_id . "/";
        }

        if (file_exists($user_dir . $res->filename))
        {
            unlink($user_dir . $res->filename);
        }

        return $this->db->delete('attachments', array('attachment_id' => $file_id));
    }
    
    function save_documents($customer_id, &$data)
    {
        if ($customer_id > 0)
        {
            if ($this->db->insert('documents', array("filename" => $data['filename'], "customer_id" => $customer_id)))
            {
                $data['document_id'] = $this->db->insert_id();
                return true;
            }
        }

        $session_id = $data['params']['linker'];
        $this->load->library('session');
        $this->session->set_userdata(array("linker" => $session_id));
        if ($this->db->insert('documents', array("filename" => $data['filename'], "session_id" => $session_id)))
        {
            $data['document_id'] = $this->db->insert_id();
            return true;
        }
    }
    
    function remove_file($file_id)
    {
        $this->db->from('documents');
        $this->db->where('document_id', $file_id);
        $query = $this->db->get();
        $res = $query->row();

        $user_dir = FCPATH . "uploads/customer-/";
        if ($res->loan_id > 0)
        {
            $user_dir = FCPATH . "uploads/customer-" . $res->customer_id . "/";
        }

        if (file_exists($user_dir . $res->filename))
        {
            unlink($user_dir . $res->filename);
        }

        return $this->db->delete('documents', array('document_id' => $file_id));
    }

    function get_extra_fields()
    {
        $extra_fields = [];
        
        $query = $this->db->get("customer_fields");
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $extra_fields[] = $row;
            }
        }
        
        return $extra_fields;
    }
}

?>
