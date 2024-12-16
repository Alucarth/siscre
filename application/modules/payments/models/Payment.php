<?php

class Payment extends CI_Model {
    /*
      Determines if a given loan_payment_id is a payment
     */

    function exists($payment_id)
    {
        $this->db->from('loan_payments');
        $this->db->where('loan_payment_id', $payment_id);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }
    
    function get_payment_list()
    {
        $from_date = $this->input->post("date_from");
        $to_date = $this->input->post("date_to");

        $filters = [];
        $filters["from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($from_date)) : strtotime($from_date);
        $filters["to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($to_date)) : strtotime($to_date);
        $filters["loan_status"] = $this->input->post("loan_status");
        $keywords = $this->input->post("keywords");
        $payments = $this->Payment->get_all(9999999999, 0, $keywords, [], false, $filters);  
        
        $html = "";
        if ( $payments )
        {
            $html .= "<table>";
            $html .= "<tr>";
            $html .= "<td>" . $this->lang->line('common_trans_id') . "</td>";
            $html .= "<td>" . $this->lang->line('loans_customer') . "</td>";
            $html .= "<td>" . $this->lang->line('payments_loan') . "</td>";
            $html .= "<td>Paid amount</td>";
            $html .= "<td>" . $this->lang->line('loans_balance') . "</td>";
            $html .= "<td>" . $this->lang->line('payments_date_paid') . "</td>";
            $html .= "<td>" . $this->lang->line('payments_teller') . "</td>";
            $html .= "</tr>";
            foreach ( $payments->result() as $payment )
            {
                $html .= "<tr>";
                $html .= "<td>" . $payment->loan_payment_id . "</td>";
                $html .= "<td>" . ucwords($payment->customer_name) . "</td>";
                $html .= "<td>" . ((trim($payment->loan_type) !== "" ? $payment->loan_type : "Flexible") . " (" . to_currency($payment->loan_amount) . ")") . "</td>";
                $html .= "<td>" . to_currency($payment->paid_amount) . "</td>";
                $html .= "<td>" . to_currency($payment->balance_amount - $payment->paid_amount) . "</td>";
                $html .= "<td>" . date($this->config->item('date_format'), $payment->date_paid) . "</td>";
                $html .= "<td>" . ucwords($payment->teller_name) . "</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
        }
        
        return $html;
    }

    function get_all($limit = 10000, $offset = 0, $search = "", $order = array(), $sel_user = false, $filters = [], $count_only = false)
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->Employee->getLowerLevels($low_levels);
        
        $sorter = array(
            "loan_payment_id",
            "loan_payment_id",
            "customer.first_name",
            "loan_types.name",
            "balance_amount",
            "paid_amount",
            "date_paid",
            "teller.first_name"
        );

        $select = "loan_payments.*, CONCAT(customer.first_name, ' ', customer.last_name) as customer_name, 
                   CONCAT(teller.first_name, ' ',teller.last_name) as teller_name, 
                   loan_types.name as loan_type,
                   loans.loan_amount,
                   loans.loan_balance";

        $this->db->select($select, FALSE);
        $this->db->from('loan_payments');
        $this->db->join('people as customer', 'customer.person_id = loan_payments.customer_id', 'LEFT');
        $this->db->join('customers as my_customer', 'my_customer.person_id = loan_payments.customer_id', 'LEFT');
        $this->db->join('people as teller', 'teller.person_id = loan_payments.teller_id', 'LEFT');
        $this->db->join('loans', 'loans.loan_id = loan_payments.loan_id', 'LEFT');
        
        $this->db->join('loan_types as loan_types', 'loan_types.loan_type_id = loans.loan_type_id', 'LEFT');

        if ($search !== "")
        {
            $this->db->where("(
                loan_types.name LIKE '%" . $search . "%' OR
                loan_payments.account LIKE '%" . $search . "%' OR
                customer.first_name LIKE '%" . $search . "%' OR
                CONCAT(customer.first_name, ' ', customer.last_name) LIKE '%" . $search . "%' OR
                customer.last_name LIKE '%" . $search . "%' OR
                teller.first_name LIKE '%" . $search . "%' OR
                date_paid LIKE '%" . $search . "%'
                )");
        }
        
        if ( isset($filters["from_date"]) && $filters["from_date"] != '' )
        {
            $this->db->where("date_paid >=", $filters["from_date"]);
        }
        
        if ( isset($filters["to_date"]) && $filters["to_date"] != '' )
        {
            $this->db->where("date_paid <=", $filters["to_date"]);
        }
        
        if ( isset($filters["loan_status"]) && $filters["loan_status"] != '' )
        {
            switch( $filters["loan_status"] )
            {
                case "pending":
                    $this->db->where("loans.loan_status", "pending");
                    break;
                case "active":
                    $this->db->where("loans.loan_status", "approved");
                    $this->db->where("loans.loan_balance >", 0);
                    break;
                case "completed":
                    $this->db->where("loans.loan_status", "approved");
                    $this->db->where("loans.loan_balance", 0);
                    break;
            }
        }

        if (count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("loan_payment_id", "desc");
        }

        $this->db->where('loan_payments.delete_flag', 0);
        $this->db->where('my_customer.deleted', 0);
        
        if ( $sel_user > 0 )
        {
            $user_id = ($sel_user) ? $sel_user : $user_id;
            $this->db->where('my_customer.added_by', $user_id);
        }
        else
        {
            if ( is_array($low_levels) && count($low_levels) > 0 )
            {
                $this->db->where_in('teller.role_id', $low_levels);
            }
            else
            {
                $this->db->where('my_customer.added_by', $user_id);
            }
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
            track_action($user_id, "Payments", "Viewed payments list");
        }
        
        return $query;
    }

    function count_all( $sel_user = '' )
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_id = ($sel_user) ? $sel_user : $user_id;
        
        $this->db->where('my_customer.added_by', $user_id);
        
        $this->db->where("loan_payments.delete_flag", 0);
        $this->db->from('loan_payments');
        $this->db->join('customers as my_customer', 'my_customer.person_id = loan_payments.customer_id', 'LEFT');
        
        return $this->db->count_all_results();
    }

    /*
      Gets information about a particular loan
     */

    function get_info($payment_id)
    {
        $select = "loan_payments.*, CONCAT(customer.first_name, ' ', customer.last_name) as customer_name, 
                   CONCAT(teller.first_name, ' ',teller.last_name) as teller_name, 
                   user.username as user_name,
                   ci.id_no as ci,
                   loan_types.name as loan_type";

        $this->db->select($select, FALSE);
        $this->db->from('loan_payments');
        $this->db->join('people as customer', 'customer.person_id = loan_payments.customer_id', 'LEFT');
        $this->db->join('people as teller', 'teller.person_id = loan_payments.teller_id', 'LEFT');
        $this->db->join('employees as user', 'user.person_id = loan_payments.teller_id', 'LEFT');
        $this->db->join('loans', 'loans.loan_id = loan_payments.loan_id', 'LEFT');
        $this->db->join('loan_types', 'loan_types.loan_type_id = loans.loan_type_id', 'LEFT');
        //campo CI
        $this->db->join('leads as ci', 'ci.customer_id = loan_payments.customer_id', 'LEFT');
        $this->db->where('loan_payment_id', $payment_id);

        $query = $this->db->get();

        if ($query->num_rows() == 1)
        {
            $row = $query->row();
            
            if (is_plugin_active('activity_log'))
            {
                if ( $payment_id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Employees", "Viewed payment: " . $payment_id);
                }
            }
            
            return $row;
        }
        else
        {
            //Get empty base parent object, as $loan_id is NOT a loan
            $payment_obj = new stdClass();

            //Get all the fields from items table
            $fields = $this->db->list_fields('loan_payments');

            foreach ($fields as $field)
            {
                $payment_obj->$field = '';
            }

            $payment_obj->loan_payment_id = -1;
            $payment_obj->customer_name = '';

            return $payment_obj;
        }
    }

    /*
      Gets information about multiple loans
     */

    function get_multiple_info($loans_ids)
    {
        $this->db->from('loans');
        $this->db->where_in('item_kit_id', $loan_ids);
        $this->db->order_by("account", "asc");
        return $this->db->get();
    }

    /*
        Gets branch for loans
    */

     function getBranchByName($branch_name)
     {
     
         $query =  $this->db->query("SELECT * from c19_branches cb where cb.branch_name like '".$branch_name."';");
         $branch = null;

         foreach ($query->result() as $row) {
            # code...
            $branch = $row;
         }

         return $branch;
     }

     function getBranch($branch_id)
     {
     
         $query =  $this->db->query("SELECT * from c19_branches cb where cb.id = ".$branch_id.";");
         $branch = null;

         foreach ($query->result() as $row) {
            # code...
            $branch = $row;
         }

         return $branch;
     }
 
    /*
      Inserts or updates a payment
     */

    function save(&$payment_data, $payment_id = false)
    {
        if (!$payment_id or ! $this->exists($payment_id))
        {
            if ($this->db->insert('loan_payments', $payment_data))
            {
                $payment_data['loan_payment_id'] = $this->db->insert_id();
                return true;
            }
            return false;
        }

        $payment_data['date_modified'] = time();
        $this->db->where('loan_payment_id', $payment_id);
        return $this->db->update('loan_payments', $payment_data);
    }

    /*
      Deletes one payment
     */

    function delete($payment_id)
    {
        $this->db->where('loan_payment_id', $payment_id);
        return $this->db->update('loan_payments', array('delete_flag' => 1));
    }

    /*
      Deletes a list of loans
     */
    function delete_list($payment_ids)
    {
        if ( count($payment_ids) > 0 )
        {
            $this->db->where_in('loan_payment_id', $payment_ids);
            $query = $this->db->get('loan_payments');
            
            if ( $query && $query->num_rows() > 0 )
            {
                foreach ( $query->result() as $row )
                {
                    $sql = "UPDATE c19_loans SET loan_balance=loan_balance+".$row->paid_amount." WHERE loan_id=" . $row->loan_id;
                    $this->db->query( $sql );
                }
            }
        }
        
        $this->db->where_in('loan_payment_id', $payment_ids);
        return $this->db->update('loan_payments', array("delete_flag" => 1));
    }

    /*
      Get search suggestions to find loans
     */

    function get_search_suggestions($search, $limit = 25)
    {
        $suggestions = array();

        $this->db->from('loans');
        $this->db->like('account', $search);
        $this->db->order_by("account", "asc");
        $by_name = $this->db->get();
        foreach ($by_name->result() as $row)
        {
            $suggestions[] = $row->account;
        }

        //only return $limit suggestions
        if (count($suggestions > $limit))
        {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    function get_loan_search_suggestions($search, $limit = 25)
    {
        $suggestions = array();

        $this->db->from('loans');
        $this->db->like('account', $search);
        $this->db->order_by("account", "asc");
        $by_name = $this->db->get();
        foreach ($by_name->result() as $row)
        {
            $suggestions[] = 'LOAN ' . $row->item_kit_id . '|' . $row->name;
        }

        //only return $limit suggestions
        if (count($suggestions > $limit))
        {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }

    /*
      Preform a search on loans
     */

    function search($search)
    {
        $this->db->from('loans');
        $this->db->where("account LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		description LIKE '%" . $this->db->escape_like_str($search) . "%'");
        $this->db->order_by("account", "asc");
        return $this->db->get();
    }

    function get_loans($customer_id)
    {
        $this->db->select('loans.*, loan_types.name as loan_type');
        $this->db->from('loans');
        $this->db->join('loan_types', "loan_types.loan_type_id = loans.loan_type_id", "LEFT");
        $this->db->where("customer_id", $customer_id);
        $this->db->where("customer_id >", 0);
        $this->db->where("delete_flag", 0);
        $this->db->where("loan_balance > ", 0);
        return $this->db->get()->result();
    }

    function get_payments_by($loan_id)
    {
        $this->db->where("loan_id", $loan_id);
        $query = $this->db->get("loan_payments");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function balance_recalc($loan_id)
    {
        $sql = "
            SELECT b.loan_amount, a.* FROM c19_loan_payments a 
            LEFT JOIN c19_loans b ON b.loan_id = a.loan_id
            WHERE a.delete_flag = 0 AND a.loan_id = '$loan_id'
            ORDER BY a.date_paid
            ";
        $query = $this->db->query( $sql );
        
        $paid_amounts = [];
        $balance = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $payment )
            {
                if ( $balance <= 0 )
                {
                    $balance = $payment->loan_amount;
                }
                
                
                $this->db->where("loan_payment_id", $payment->loan_payment_id)
                        ->update("loan_payments", ["balance_amount" => $balance]);
                
                $balance = $balance - $payment->paid_amount;
            }
            
            $this->db->where("loan_id", $loan_id)
                    ->update("loans", ["loan_balance" => $balance]);
        }
    }
}

?>