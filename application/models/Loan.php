<?php



class Loan extends CI_Model {

    /*

      Determines if a given loan_id is a loan

     */



    function exists($loan_id)

    {

        $this->db->from('loans');

        $this->db->where('loan_id', $loan_id);

        $query = $this->db->get();



        return ($query->num_rows() == 1);

    }



    function get_all($limit = 10000, $offset = 0, $search = "", $order = array(), $status = "", $sel_user = false, $filters = [], $count_only = false)
    {
        $user_id = $this->Employee->get_logged_in_employee_info() ? $this->Employee->get_logged_in_employee_info()->person_id : 0;
        $this->Employee->getLowerLevels($low_levels);
        $sorter = array(
            "",
            "loan_id",
            "loan_type",
            "description",
            "loan_amount",
            "loan_balance",
            "customer.first_name",
            "agent.first_name",
            "approver.first_name",
            "loan_applied_date",
            "loan_payment_date",
            "loan_status",
        );
        if ( isset($filters["sorter"]) && is_array($filters["sorter"]) )
        {
            $sorter = $filters["sorter"];
        }
        $select = " l.*, 
                    IF( (SELECT (l.loan_amount - sum(paid_amount)) loan_balance FROM c19_loan_payments a WHERE a.loan_id = l.loan_id AND a.delete_flag = 0 ORDER BY a.balance_amount LIMIT 1) > 0, (SELECT (l.loan_amount - sum(paid_amount)) loan_balance FROM c19_loan_payments a WHERE a.loan_id = l.loan_id AND a.delete_flag = 0 ORDER BY a.balance_amount LIMIT 1), l.loan_balance ) AS loan_balance,
                    (SELECT a.date_paid FROM c19_loan_payments a WHERE a.loan_id = l.loan_id AND a.delete_flag = 0 ORDER BY a.date_paid DESC LIMIT 1) due_paid, customer.phone_number as customer_phone,
                    CONCAT(customer.first_name, ' ', customer.last_name) as customer_name, 
                    CONCAT(agent.first_name, ' ',agent.last_name) as agent_name, 
                    CONCAT(approver.first_name, ' ', approver.last_name) as approver_name";
        if (is_plugin_active('loan_products'))
        {
            $select .= ",                   
                    IF (
                         l.loan_product_id = 0,
                         'Flexible',
                         lt.product_name
                    ) AS loan_type";
        }
        else
        {
            $select .= ",                   
                    IF (
                         l.loan_type_id = 0,
                         'Flexible',
                         lt.name
                    ) AS loan_type";
        }
        $this->db->select($select, FALSE);
        $this->db->from('loans l');
        $this->db->join('people as customer', 'customer.person_id = l.customer_id', 'LEFT');
        $this->db->join('people as agent', 'agent.person_id = l.loan_agent_id', 'LEFT');
        $this->db->join('people as approver', 'approver.person_id = l.loan_approved_by_id', 'LEFT');
        if (is_plugin_active('loan_products'))
        {
            $this->db->join('loan_products lt', 'lt.id = l.loan_product_id', 'LEFT');
        }
        else
        {
            $this->db->join('loan_types lt', 'lt.loan_type_id = l.loan_type_id', 'LEFT');
        }
        $this->db->join('customers as my_customer', 'my_customer.person_id = l.customer_id', 'LEFT');
        if ( $sel_user > 0 )
        {
            $user_id = ($sel_user) ? $sel_user : $user_id;
            $this->db->where('loan_agent_id', $user_id);
        }
        else
        {
            if ( is_array($low_levels) && count($low_levels) > 0 )
            {
                $low_levels[] = $user_id;
                $this->db->where('( agent.role_id IN (' . implode(",", $low_levels) . ') OR loan_agent_id = \'' . $user_id . '\' )');
            }
            else
            {
                if ( $user_id > 0 )
                {
                    $this->db->where('loan_agent_id', $user_id);
                }
            }
        }
        if (is_plugin_active("branches"))
        {
            if ( isset($filters["branch_id"]) && $filters["branch_id"] > 0 )
            {
                $this->db->where("my_customer.branch_id", $filters["branch_id"]);
            }
            $this->db->where("my_customer.branch_id", $this->session->userdata('branch_id'));        
        }
        if ( isset($filters["applied_from_date"]) && trim($filters["applied_from_date"]) != '' )
        {
            $this->db->where("loan_applied_date >=", $filters["applied_from_date"]);
        }
        if ( isset($filters["applied_to_date"]) && trim($filters["applied_to_date"]) != '' )
        {
            $this->db->where("loan_applied_date <=", $filters["applied_to_date"]);
        }
        if ( isset($filters["approved_from_date"]) && trim($filters["approved_from_date"]) != '' )
        {
            $this->db->where("loan_approved_date >=", $filters["approved_from_date"]);
        }
        if ( isset($filters["approved_to_date"]) && trim($filters["approved_to_date"]) != '' )
        {
            $this->db->where("loan_approved_date <=", $filters["approved_to_date"]);
        }
        if ( isset($filters["due_from_date"]) && trim($filters["due_from_date"]) != '' )
        {
            $this->db->where("loan_payment_date >=", $filters["due_from_date"]);
        }
        if ( isset($filters["due_to_date"]) && trim($filters["due_to_date"]) != '' )
        {
            $this->db->where("loan_payment_date <=", $filters["due_to_date"]);
        }
        if ( isset($filters["customer_id"]) && trim($filters["customer_id"]) > 0 )
        {
            $this->db->where("customer.person_id =", $filters["customer_id"]);
        }
        if ($search !== "")
        {
            if (is_plugin_active('loan_products'))
            {
                $this->db->where("(
                    account LIKE '%" . $search . "%' OR
                    l.description LIKE '%" . $search . "%' OR
                    customer.first_name LIKE '%" . $search . "%' OR
                    customer.last_name LIKE '%" . $search . "%' OR
                    CONCAT(customer.first_name,' ', customer.last_name) LIKE '%" . $search . "%' OR
                    lt.product_name LIKE '%" . $search . "%' OR        
                    agent.first_name LIKE '%" . $search . "%' OR
                    agent.last_name LIKE '%" . $search . "%' OR 
                    CONCAT(agent.first_name, ' ', agent.last_name) LIKE '%" . $search . "%'
                    )");
            }
            else
            {
                $this->db->where("(
                    account LIKE '%" . $search . "%' OR
                    l.description LIKE '%" . $search . "%' OR
                    customer.first_name LIKE '%" . $search . "%' OR
                    customer.last_name LIKE '%" . $search . "%' OR
                    CONCAT(customer.first_name,' ', customer.last_name) LIKE '%" . $search . "%' OR
                    lt.name LIKE '%" . $search . "%' OR        
                    agent.first_name LIKE '%" . $search . "%' OR
                    agent.last_name LIKE '%" . $search . "%' OR 
                    CONCAT(agent.first_name, ' ', agent.last_name) LIKE '%" . $search . "%'
                    )");
            }
        }
        if ( isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("loan_id", "desc");
        }
        $this->db->where('delete_flag', 0);
        $this->db->where('my_customer.deleted', 0);
        if ($status !== "")
        {
            if ($status === "paid")
            {
                $this->db->where("loan_balance", 0);
            }
            else if ($status === "unpaid")
            {
                $this->db->where("loan_balance >", 0);
            }
            else if ($status === "overdue")
            {
                if ( (isset($filters["due_from_date"]) && trim($filters["due_from_date"]) != '') || 
                        ( isset($filters["due_to_date"]) && trim($filters["due_to_date"]) != '' ) )
                {
                }
                else
                {
                    $this->db->where("loan_payment_date < UNIX_TIMESTAMP()");
                }
                $this->db->where("loan_status <>", 'pending');
                $this->db->where("loan_balance > ", 0);
            }
            else
            {
                if ( $status != 'all' )
                {
                    $this->db->where("loan_status", $status);
                }
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
            $user_id = $this->Employee->get_logged_in_employee_info() ? $this->Employee->get_logged_in_employee_info()->person_id : 1;
            track_action($user_id, "loans", "Viewed loan transactions");
        }
        return $query;
    }

    function count_all()
    {
        $this->db->from('loans');
        $this->db->where("delete_flag", 0);
        return $this->db->count_all_results();
    }

    function count_overdues()
    {
        $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->db->where("loan_payment_date < UNIX_TIMESTAMP()");
        $this->db->from('loans');
        $this->db->where("delete_flag", 0);
        if ($employee_id > 1)
        {
            $this->db->where("loan_agent_id", $employee_id);
        }
        return $this->db->count_all_results();
    }



    /*

      Gets information about a particular loan

     */



    function get_info($loan_id)
    {
        if (is_plugin_active('activity_log'))
        {
            if ( $loan_id > 0 )
            {
                $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($employee_id, "loans", "Viewed loan details #" . $loan_id );
            }
        }
        $select = "loans.*, customer.phone_number as customer_phone,
                   CONCAT(customer.first_name, ' ', customer.last_name) as customer_name, 
                   CONCAT(agent.first_name, ' ',agent.last_name) as agent_name, 
                   CONCAT(approver.first_name, ' ', approver.last_name) as approver_name";
        $this->db->select($select, FALSE);
        $this->db->from('loans');
        $this->db->join('people as customer', 'customer.person_id = loans.customer_id', 'LEFT');
        $this->db->join('people as agent', 'agent.person_id = loans.loan_agent_id', 'LEFT');
        $this->db->join('people as approver', 'approver.person_id = loans.loan_approved_by_id', 'LEFT');
        $this->db->where('loan_id', $loan_id);
        $query = $this->db->get();
        if ($query->num_rows() == 1)
        {
            return $query->row();
        }
        else
        {

            //Get empty base parent object, as $loan_id is NOT a loan

            $loan_obj = new stdClass();



            //Get all the fields from items table

            $fields = $this->db->list_fields('loans');



            foreach ($fields as $field)

            {

                $loan_obj->$field = '';

            }



            $loan_obj->loan_id = -1;

            $loan_obj->customer_name = '';

            $loan_obj->customer_phone = '';//telefono cliente

            $loan_obj->loan_status = 'pending';



            return $loan_obj;

        }

    }



    /*

      Gets information about multiple loans

     */



    function get_multiple_info($loans_ids)

    {

        $this->db->from('loans');

        $this->db->where_in('item_kit_id', $loans_ids);

        $this->db->order_by("account", "asc");

        return $this->db->get();

    }



    /*

      Inserts or updates a loan

     */



    function save(&$loan_data, $loan_id = false, $has_payment = false)
    {
        if ($loan_data["loan_type_id"] > 0)
        {
            $loan_data['loan_payment_date'] = $this->_get_loan_payment_date($loan_data);
        }
        if (!$loan_id or ! $this->exists($loan_id))
        {
            $loan_data['loan_balance'] = $loan_data["loan_amount"];
            if ($this->db->insert('loans', $loan_data))
            {
                $loan_data['loan_id'] = $this->db->insert_id();
                $this->move_attachments($loan_data);
                if (is_plugin_active('activity_log'))
                {
                    $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($employee_id, "loans", "Added new loan #" . $loan_data['loan_id'] );
                }
                return true;
            }
            return false;
        }

        // Check if there's a change in loan_amount

        $loan_info = $this->get_info($loan_id);
        if ( $loan_info->loan_amount != $loan_data["loan_amount"] )
        {
            $loan_data["loan_balance"] = $loan_data["loan_amount"];
        }
        $this->db->where('loan_id', $loan_id);
        $ret = $this->db->update('loans', $loan_data);
        if (is_plugin_active('activity_log'))
        {
            $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($employee_id, "loans", "Updated loan #" . $loan_id );
        }
        return $ret;

    }



    private function _get_loan_balance($loan_data)

    {

        $loan_type = $this->Loan_type->get_info($loan_data['loan_type_id']);



        $num_weeks = ($loan_type->term_period_type === "year") ? 52 : 4;

        $num_months = ($loan_type->term_period_type === "year") ? 12 : 1;



        if ($loan_type->term_period_type === "year")

        {

            switch ($loan_type->period_type1)

            {

                case "Week": // 52 weeks in 1yr

                    $apr = (52 / $loan_type->period_charge1) * $loan_type->percent_charge1;

                    $interest_rate = $apr / $num_weeks;

                    break;

                case "Month": // 12 months in 1yr

                    $apr = (12 / $loan_type->period_charge1) * $loan_type->percent_charge1;

                    $interest_rate = $apr / $num_months;

                    break;

                case "Year": // 1yr in 1yr

                    $apr = (1 / $loan_type->period_charge1) * $loan_type->percent_charge1;

                    $interest_rate = $apr / 1;

                    break;

            }

        }

        else if($loan_type->term_period_type === "month")

        {

            $interest_rate = $loan_type->percent_charge1;

        }

        else

        {

            $interest_rate = 0;

        }





        return $loan_data['loan_amount'] + ($loan_data['loan_amount'] * ($interest_rate / 100) );

    }



    private function _get_loan_payment_date_obsolete($loan_data)

    {

        // 52 - Weekly

        // 26 - Biweekly

        // 12 - Monthly

        //  6 - Bimonthly

        // 365- Daily

        $time = 0;

        $loan_type = $this->Loan_type->get_info($loan_data['loan_type_id']);

        switch ($loan_type->payment_schedule)

        {

            case "weekly":

                $time = strtotime("+7 day", $loan_data['loan_applied_date']);

                break;

            case "biweekly":

                $time = strtotime("+14 day", $loan_data['loan_applied_date']);

                break;

            case "monthly":

                $time = strtotime("+1 month", $loan_data['loan_applied_date']);

                break;

            case "bimonthly":

                $time = strtotime("+2 month", $loan_data['loan_applied_date']);

                break;

            case "daily":

                $time = strtotime("+1 day", $loan_data['loan_applied_date']);

                break;

        }

        return $time;

    }



    /*

     * Move attachment to the right location

     */



    function move_attachments($loan_data)

    {

        $linker = $this->session->userdata('linker');



        $this->db->from('attachments');

        $this->db->where('session_id', $linker);

        $query = $this->db->get();



        $this->db->where('session_id', $linker);

        $this->db->update('attachments', array("loan_id" => $loan_data['loan_id']));



        $attachments = $query->result();

        foreach ($attachments as $attachment)

        {

            $tmp_dir = FCPATH . "uploads/loan--1/";

            $user_dir = FCPATH . "uploads/loan-" . $loan_data['loan_id'] . "/";



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

      Deletes one loan

     */



    function delete($loan_id)

    {

        $this->db->where('loan_id', $loan_id);

        $this->db->delete('loans', array('delete_flag' => 1));

        

        if (is_plugin_active('activity_log'))

        {

            $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

            track_action($employee_id, "loans", "Deleted loan transaction #" . $loan_id);

        }

        

        return true;

    }



    /*

      Deletes a list of loans

     */



    function delete_list($loan_ids)

    {

        $this->db->where_in('loan_id', $loan_ids);

        $this->db->update('loans', array("delete_flag" => 1));

        

        if (is_plugin_active('activity_log'))

        {

            $employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

            if ( is_array($loan_ids) )

            {

                foreach ( $loan_ids as $loan_id )

                {

                    track_action($employee_id, "loans", "Deleted loan transaction #" . $loan_id);

                }

            }

            else

            {

                track_action($employee_id, "loans", "Deleted loan transaction #" . $loan_id);

            }

        }

        

        return true;

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

    

    function _get_count_payments($loan_id)

    {

        $this->db->where("loan_id", $loan_id);

        $cnt = $this->db->count_all_results("loan_payments");

        return $cnt;

    }

    

    private function _get_next_payment_date($loan_id, $paid_due_date)

    {

        $this->db->where("loan_id", $loan_id);

        $query = $this->db->get("loans");



        if ( $query && $query->num_rows() > 0 )

        {

            $row = $query->row();

            

            $scheds = json_decode($row->periodic_loan_table);

            

            $next = false;

            foreach ( $scheds as $key => $value )

            {

                $payment_date = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate(strip_tags($value->payment_date)) : strip_tags($value->payment_date);

                

                if ( $next )

                {

                    return strtotime($payment_date);

                }

                

                if ( strtotime($payment_date) == $paid_due_date )

                {

                    $next = true;

                }

            }

        }

        

        return false;

    }



    /*

     * Perform update/insert balance

     */



    function update_balance($loan_id)

    {

        $sql = "

            SELECT 

                ( SELECT SUM(lp.paid_amount) paid_amount FROM c19_loan_payments lp WHERE lp.loan_id = a.loan_id AND lp.delete_flag = 0 ) paid_amount,

                a.loan_amount,

                a.periodic_loan_table,

                (SELECT lp.payment_due FROM c19_loan_payments lp WHERE lp.loan_id = a.loan_id AND lp.delete_flag = 0 ORDER BY lp.payment_due DESC LIMIT 1) due_paid

            FROM c19_loans a 

            WHERE a.loan_id = '$loan_id'

            ";

        $query = $this->db->query($sql);

        if ( $query && $query->num_rows() > 0 )

        {

            $row = $query->row();

            

            $scheds = json_decode($row->periodic_loan_table);

            $next_payment_date = sync_payment_date( $row->due_paid, $scheds); // norman

            

            $balance = $row->loan_amount - $row->paid_amount;

            $this->db->where("loan_id", $loan_id);

            $this->db->update("loans", ["loan_balance" => $balance, "loan_payment_date" => $next_payment_date]);

        }

    }



    function save_attachments($loan_id, &$data)

    {

        if ($loan_id > 0)

        {

            if ($this->db->insert('attachments', array("filename" => $data['filename'], "loan_id" => $loan_id)))

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

    

    function save_attach_desc($id, $desc)

    {

        $this->db->where('attachment_id', $id);

        return $this->db->update('attachments', array("descriptions" => $desc));

    }



    function get_attachments($loan_id)

    {

        $this->db->from('attachments');

        $this->db->where('loan_id', $loan_id);

        $query = $this->db->get();



        return $query->result();

    }



    function remove_file($file_id)

    {

        $this->db->from('attachments');

        $this->db->where('attachment_id', $file_id);

        $query = $this->db->get();

        $res = $query->row();



        $user_dir = FCPATH . "uploads/loan--1/";

        if ($res->loan_id > 0)

        {

            $user_dir = FCPATH . "uploads/loan-" . $res->loan_id . "/";

        }



        if (file_exists($user_dir . $res->filename))

        {

            unlink($user_dir . $res->filename);

        }



        return $this->db->delete('attachments', array('attachment_id' => $file_id));

    }



    function get_total_loans()

    {

        $this->db->select("SUM(loan_amount) as total_loans");

        $this->db->from("loans l");

        $this->db->join("customers my_customer", "my_customer.person_id=l.customer_id", "LEFT");

        $this->db->where("delete_flag", "0");

        $this->db->where("loan_status", "approved");

        

        if (is_plugin_active("branches"))

        {

            $this->db->where("my_customer.branch_id", $this->session->userdata('branch_id'));  

        }

        

        $query = $this->db->get();

        $res = $query->row();



        return to_currency($res->total_loans, TRUE, 0);

    }



    function get_loan_interest_types()
    {
        $interest_types = [];
        //$interest_types["flat"] = "Tarifa plana";
        //$interest_types["fixed"] = "Interés fijo";
        //$interest_types["interest_only"] = "Solo interés";
        //$interest_types["outstanding_interest"] = "Interés pendiente";
        //$interest_types["one_time"] = "Pago único";
        //$interest_types["mortgage"] = "Amortización de hipoteca";
        $interest_types["mortgage2"] = "Amortización de hipoteca 2";
        //$interest_types["loan_deduction"] = "Deducción de préstamo";
        //$interest_types["loan_interest_reduction"] = "Reducción de interés";
        //$interest_types["annual_rate"] = "Tarifa anual";
        //$interest_types["pago_excel"] = "Pago excel";
        $interest_types["fixed_fee"] = "Cuota fija";

        return $interest_types;
    }

    function get_loan_schedule($post_var)
    {
        switch ($post_var["InterestType"])
        {
            case 'fixed':

                $data_scheds = $this->calculate_fixed_rate($post_var);

                break;

            case 'flat':

                $data_scheds = $this->calculate_standard_rate($post_var);

                break;

            case 'interest_only':

                $data_scheds = $this->calculate_interest_only($post_var);

                break;

            case 'outstanding_interest':

                $data_scheds = $this->calculate_outstanding_interest($post_var);

                break;

            case 'one_time':

                $data_scheds = $this->calculate_one_time_payment($post_var);

                break;

            case 'mortgage':

                $data_scheds = $this->calculate_mortgage($post_var);

                break;

            case 'mortgage2':

                $data_scheds = $this->calculate_mortgage2($post_var);

                break;

            case 'loan_deduction':

                $data_scheds = $this->calculate_loan_deduction($post_var);

                break;

	        case 'loan_interest_reduction':

                $data_scheds = $this->calculate_loan_interest_reduction($post_var);

                break;	

            case 'annual_rate':

                $data_scheds = $this->calculate_annual_rate($post_var);

                break;

            case 'pago_excel':

                $data_scheds = $this->calculate_loan_pago_excel($post_var);
        
                break;

            case 'fixed_fee':

                $data_scheds = $this->calculate_fixed_fee($post_var);
            
                break;

            default:

                $data_scheds = $this->calculate_percentage($post_var);

                break;

        }



        return $data_scheds;

    }

    

    function calculate_loan_interest_reduction($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $penalty_amount = $post_var["penalty_amount"];

        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        
        
        if ( $pay_term_name == 'biweekly' )
        {
            $pay_term *= 2;
        }
        if ( $pay_term_name == 'month_weekly' )
        {
            $pay_term *= 4;
        }
        
        $loan_amount = $apply_amount;
        $interest_amount = ($loan_amount * ($interest_rate / 100));
        $payment_amount = ($loan_amount / $pay_term) + $interest_amount;
        $divided_amount = ($loan_amount / $pay_term);

        $data_scheds = [];
        $no_of_days = 0;

        for ($i = 1; $i <= $pay_term; $i++)
        {
            $balance_owed = $loan_amount - $divided_amount;
            $color = $title = $grace_period_days = '';

            if (isset($grace_period[$i]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$i] . ' days';
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount / $pay_term;
            $tmp["interest"] = $interest_amount;
            $tmp["payment_amount"] = $payment_amount;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            $apply_amount = $balance_owed;

            $data_scheds[] = $tmp;

            $loan_amount = $balance_owed;
            $interest_amount = ($loan_amount * ($interest_rate / 100));
            $payment_amount = $divided_amount + $interest_amount;

            $no_of_days++;
        }

        return $data_scheds;
    }

    function calculate_annual_rate($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $penalty_amount = $post_var["penalty_amount"];
        $interest_rate = $post_var["TotIntRate"];

        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $total_years_to_pay = $pay_term;
        if ($pay_term_name == 'year')
        {
            $pay_term = $pay_term * 12;
        }

        if ($pay_term_name == 'month')
        {
            $total_years_to_pay = $pay_term / 12;
        }

        $yearly_interest_amount = ($apply_amount * ($interest_rate / 100)) * $total_years_to_pay;
        $loan_amount = $apply_amount + $yearly_interest_amount;
        $payment_amount = $loan_amount / $pay_term;
        $principal_amount = $apply_amount / $pay_term;

        $data_scheds = [];
        $no_of_days = 0;

        for ($i = 1; $i <= $pay_term; $i++)
        {
            $balance_owed = $loan_amount - $payment_amount;
            $color = $title = $grace_period_days = '';

            if (isset($grace_period[$i]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$i] . ' days';
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount / $pay_term;
            $tmp["interest"] = $payment_amount - $principal_amount;
            $tmp["payment_amount"] = $payment_amount;

            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 month');
            
            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;

            $loan_amount = $balance_owed;
            $no_of_days++;
        }

        return $data_scheds;
    }

    function calculate_loan_deduction($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $penalty_amount = $post_var["penalty_amount"];

        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $loan_amount = $apply_amount;
        $payment_amount = $loan_amount / $pay_term;

        $data_scheds = [];
        $no_of_days = 0;
        
        if ( $pay_term_name == 'biweekly' )
        {
            $pay_term *= 2;
            $payment_amount /= 2;
        }
        if ( $pay_term_name == 'month_weekly' )
        {
            $pay_term *= 4;
            $payment_amount /= 4;
        }

        for ($i = 1; $i <= $pay_term; $i++)
        {
            $balance_owed = $loan_amount - $payment_amount;
            $color = $title = $grace_period_days = '';

            if (isset($grace_period[$i]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$i] . ' days';
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount / $pay_term;
            $tmp["interest"] = 0;
            $tmp["payment_amount"] = $payment_amount;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;
            $loan_amount = $balance_owed;

            $no_of_days++;
        }

        return $data_scheds;
    }

    function calculate_mortgage($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $penalty_amount = $post_var["penalty_amount"];

        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $data_scheds = [];
        $interest_rate = ($interest_rate / 100) / 12;

        $balance_owed = $apply_amount;
        $term = (int) $post_var["NoOfPayments"];
        
        if ( $pay_term_name == 'biweekly' )
        {
            $term *= 2;
            $pay_term *= 2;
        }
        
        if ( $pay_term_name == 'month_weekly' )
        {
            $term *= 4;
            $pay_term *= 4;
        }
        
        $i = 0;
        $y = 1;

        while ($balance_owed > 0 && $i < $term)
        {
            $pay_term = $pay_term > 0 ? $pay_term : 1;
            $deno = 1 - 1 / pow((1 + $interest_rate), $pay_term);
            $deno = $deno > 0 ? $deno : 1;

            $term_pay = ($apply_amount * $interest_rate) / $deno;
            $interest = $apply_amount * $interest_rate;

            $principal_amount = $term_pay - $interest;
            $balance_owed = $apply_amount - $principal_amount;

            $color = $title = $grace_period_days = '';
            if (isset($grace_period[$y]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$y] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$y] . ' days';
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount;
            $tmp["interest"] = $interest;
            $tmp["payment_amount"] = $term_pay;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            $apply_amount = $balance_owed;

            $data_scheds[] = $tmp;

            $pay_term = $pay_term - 1;
            $i++;
            $y++;
        }

        return $data_scheds;
    }

    function calculate_standard_rate($post_var)

    {

        $grace_period = $post_var["grace_period_days"];

                

        $term = $post_var["NoOfPayments"];



        $apply_amount = $post_var["ApplyAmt"];

        

        $fixed_rate = $post_var["TotIntRate"];

        

        $pay_term = $post_var["PayTerm"];



        $penalty_amount = $post_var["penalty_amount"];

        

	$exclude_sundays = $post_var["exclude_sundays"];

        $exclude_schedules = isset($post_var["exclude_schedules"]) ? $post_var["exclude_schedules"] : [];



        $data_scheds = [];



        $fixed_amount = ($apply_amount * ($fixed_rate / 100));

        $payment_amount = ($apply_amount + $fixed_amount) / $term;

        

        $principal_amount = $apply_amount / $term;

        

        $loan_amount = ($apply_amount + $fixed_amount);



        $total_amount = 0;

        $no_of_days = 0;

        $total_interest = 0;

        $total_principal = 0;

        

        if ($this->config->item('date_format') == 'd/m/Y')

        {

            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));

        }

        else

        {

            $payment_date = strtotime($post_var["InstallmentStarted"]);

        }



        for ($i = 1; $i <= $term; $i++)

        {

            $compound_interest = $payment_amount - $principal_amount;

            

            $balance_owed = $loan_amount - $payment_amount;



            $total_amount += $payment_amount;

            

            $color = $title = $grace_period_days = '';

            if ( isset($grace_period[$i]) )

            {

                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');

                $color = 'red';

                $title = 'In grace period';

                $grace_period_days = $grace_period[$i] . ' day';

            }

            

            if (is_plugin_active("holidays") )

            {

                $payment_date = get_excluded_days($payment_date, $exclude_schedules);

            }

	    else

	    {

	    	if ($exclude_sundays)

	            {

	                $in_day = date("l", $payment_date);

	                $in_day = strtolower($in_day);



	                if ($in_day == "sunday")

	                {

	                    $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');

	                }

	            }

	    }

         

            $tmp = [];

            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);

            

            $tmp["payment_balance"] = $balance_owed; 

            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';

            

            $tmp["penalty_amount"] = $penalty_amount;

            

            $tmp["interest"] = $compound_interest;

            

            $tmp["payment_amount"] = $payment_amount;

            

            switch($pay_term)

            {

                case "biweekly":

                    $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');

                    break;

                case "month_weekly":

                    $payment_date = strtotime(date('Y-m-d', $payment_date) . '+4 week');

                    break;

                default:

                    $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term);

                    break;

            }



            $data_scheds[] = $tmp;



            $loan_amount = $balance_owed;

            $no_of_days++;

            $total_interest += $compound_interest;

            $total_principal += $principal_amount;

        }



        return $data_scheds;

    }



    function calculate_one_time_payment($post_var)

    {

        $grace_period = $post_var["grace_period_days"];

        $term = 1;



        $loan_amount = $post_var["ApplyAmt"];

        $fixed_amount = $post_var["TotIntRate"];

        $exclude_sundays = $post_var["exclude_sundays"];

        $exclude_schedules = $post_var["exclude_schedules"];



        $penalty_amount = $post_var["penalty_amount"];



        $pay_term = $post_var["PayTerm"];





        $data_scheds = [];



        $fixed_amount = ($loan_amount * ($fixed_amount / 100)) * $term;

        $payment_amount = ($loan_amount + $fixed_amount) / $term;

        $fixed_rate = $fixed_amount / $term;



        $total_amount = 0;

        $no_of_days = 0;

        $total_interest = 0;

        $total_principal = 0;



        if ($this->config->item('date_format') == 'd/m/Y')

        {

            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));

        }

        else

        {

            $payment_date = strtotime($post_var["InstallmentStarted"]);

        }



        for ($i = 1; $i <= $term; $i++)

        {



            $compound_interest = $fixed_rate;

            $principal_amount = $payment_amount - $compound_interest;

            $balance_owed = $loan_amount - $principal_amount;



            $total_amount += $payment_amount;

            

            $color = $title = $grace_period_days = '';

            if ( isset($grace_period[$i]) )

            {

                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');

                $color = 'red';

                $title = 'In grace period';

                $grace_period_days = $grace_period[$i] . ' days';

            }



            $tmp = [];

            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);

            $tmp["payment_balance"] = $balance_owed;

            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';

            $tmp["penalty_amount"] = $penalty_amount;

            $tmp["interest"] = $compound_interest;

            $tmp["payment_amount"] = $payment_amount;



            $data_scheds[] = $tmp;



            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term);



            if (is_plugin_active("holidays") )

            {

                $payment_date = get_excluded_days($payment_date, $exclude_schedules);

            }

            else

            {

                if ($exclude_sundays)

                {

                    $in_day = date("l", $payment_date);

                    $in_day = strtolower($in_day);



                    if ($in_day == "sunday")

                    {

                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');

                    }

                }

            }



            $loan_amount = $balance_owed;

            $no_of_days++;

            $total_interest += $compound_interest;

            $total_principal += $principal_amount;

        }



        return $data_scheds;

    }



    function calculate_fixed_rate($post_var)

    {

        $grace_period = $post_var["grace_period_days"];

        $term = $post_var["NoOfPayments"];



        $loan_amount = $post_var["ApplyAmt"];

        $fixed_amount = $post_var["TotIntRate"];

        $exclude_sundays = $post_var["exclude_sundays"];

        $exclude_schedules = $post_var["exclude_schedules"];



        $penalty_amount = $post_var["penalty_amount"];



        $pay_term = $post_var["PayTerm"];



        if ($pay_term == 'biweekly')

        {

            $fixed_amount = ($loan_amount * ($fixed_amount / 100)) * ($term);

            $payment_amount = ($loan_amount + $fixed_amount) / ($term * 2);

            $fixed_rate = $fixed_amount / ($term);



            $total_amount = 0;

            $no_of_days = 0;

            $total_interest = 0;

            $total_principal = 0;



            if ($this->config->item('date_format') == 'd/m/Y')

            {

                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));

            }

            else

            {

                $payment_date = strtotime($post_var["InstallmentStarted"]);

            }



            $data_scheds = [];

            for ($i = 1; $i <= ($term * 2); $i++)

            {

                $compound_interest = $fixed_rate / 2;

                $principal_amount = $payment_amount - $compound_interest;

                $balance_owed = $loan_amount - $principal_amount;



                $total_amount += $payment_amount;



                $color = $title = $grace_period_days = '';

                if ( isset($grace_period[$i]) )

                {

                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');

                    $color = 'red';

                    $title = 'In grace period';

                    $grace_period_days = $grace_period[$i] . ' days';

                }

                

                $tmp = [];

                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);

                $tmp["payment_balance"] = $balance_owed;

                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';



                $tmp["penalty_amount"] = $penalty_amount;



                $tmp["interest"] = $compound_interest;

                $tmp["payment_amount"] = $payment_amount;



                $data_scheds[] = $tmp;



                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+15 days');

                

                if (is_plugin_active("holidays") )

                {

                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);

                }

                else

                {

                    if ($exclude_sundays)

                    {

                        $in_day = date("l", $payment_date);

                        $in_day = strtolower($in_day);



                        if ($in_day == "sunday")

                        {

                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');

                        }

                    }

                }



                $loan_amount = $balance_owed;

                $no_of_days++;

                $total_interest += $compound_interest;

                $total_principal += $principal_amount;

            }

        }

        else if ( $pay_term == 'month_weekly' )

        {

            $fixed_amount = ($loan_amount * ($fixed_amount / 100)) * ($term);

            $payment_amount = ($loan_amount + $fixed_amount) / ($term * 4);

            $fixed_rate = $fixed_amount / ($term);



            $total_amount = 0;

            $no_of_days = 0;

            $total_interest = 0;

            $total_principal = 0;



            if ($this->config->item('date_format') == 'd/m/Y')

            {

                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));

            }

            else

            {

                $payment_date = strtotime($post_var["InstallmentStarted"]);

            }



            $data_scheds = [];

            for ($i = 1; $i <= ($term * 4); $i++)

            {

                $compound_interest = $fixed_rate / 4;

                $principal_amount = $payment_amount - $compound_interest;

                $balance_owed = $loan_amount - $principal_amount;



                $total_amount += $payment_amount;



                $color = $title = $grace_period_days = '';

                if ( isset($grace_period[$i]) )

                {

                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');

                    $color = 'red';

                    $title = 'In grace period';

                    $grace_period_days = $grace_period[$i] . ' days';

                }

                

                $tmp = [];

                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);

                $tmp["payment_balance"] = $balance_owed;

                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';



                $tmp["penalty_amount"] = $penalty_amount;



                $tmp["interest"] = $compound_interest;

                $tmp["payment_amount"] = $payment_amount;



                $data_scheds[] = $tmp;



                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+7 days');



                if (is_plugin_active("holidays") )

                {

                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);

                }

                else

                {

                    if ($exclude_sundays)

                    {

                        $in_day = date("l", $payment_date);

                        $in_day = strtolower($in_day);



                        if ($in_day == "sunday")

                        {

                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');

                        }

                    }

                }



                $loan_amount = $balance_owed;

                $no_of_days++;

                $total_interest += $compound_interest;

                $total_principal += $principal_amount;

            }

        }

        else

        {

            $data_scheds = [];



            $fixed_amount = ($loan_amount * ($fixed_amount / 100)) * $term;

            $payment_amount = ($loan_amount + $fixed_amount) / $term;

            $fixed_rate = $fixed_amount / $term;



            $total_amount = 0;

            $no_of_days = 0;

            $total_interest = 0;

            $total_principal = 0;



            if ($this->config->item('date_format') == 'd/m/Y')

            {

                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));

            }

            else

            {

                $payment_date = strtotime($post_var["InstallmentStarted"]);

            }



            for ($i = 1; $i <= $term; $i++)

            {



                $compound_interest = $fixed_rate;

                $principal_amount = $payment_amount - $compound_interest;

                $balance_owed = $loan_amount - $principal_amount;



                $total_amount += $payment_amount;

                

                $color = $title = $grace_period_days = '';

                if ( isset($grace_period[$i]) )

                {

                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');

                    $color = 'red';

                    $title = 'In grace period';

                    $grace_period_days = $grace_period[$i] . ' days';

                }

                

                $tmp = [];

                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);

                $tmp["payment_balance"] = $balance_owed;

                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';

                $tmp["penalty_amount"] = $penalty_amount;

                $tmp["interest"] = $compound_interest;

                $tmp["payment_amount"] = $payment_amount;



                $data_scheds[] = $tmp;



                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term);



                if (is_plugin_active("holidays") )

                {

                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);

                }

                else

                {

                    if ($exclude_sundays)

                    {

                        $in_day = date("l", $payment_date);

                        $in_day = strtolower($in_day);



                        if ($in_day == "sunday")

                        {

                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');

                        }

                    }

                }



                $loan_amount = $balance_owed;

                $no_of_days++;

                $total_interest += $compound_interest;

                $total_principal += $principal_amount;

            }

        }



        return $data_scheds;

    }



    function calculate_percentage($post_var)

    {

        $frequency = 1;

        $term = $post_var["NoOfPayments"];

        $period = $post_var["PayTerm"];

        $penalty_amount = $post_var["penalty_amount"];

        $exclude_schedules = $post_var["exclude_schedules"];



        switch ($period)

        {

            case "day":

                $frequency = 365;

                break;

            case "week":

                $frequency = 52;

                break;

            case "month":

                $frequency = 12;

                break;

            case "year":

                $frequency = 1;

                break;

        }



        $loan_amount = $post_var["ApplyAmt"];

        $interest_rate = ( $post_var["TotIntRate"] / 100) / $frequency;



        $r = (1 + $interest_rate);

        $pow = pow($r, $term);



        $data_scheds = [];



        $payment_amount = $loan_amount * (($interest_rate * $pow) / ($pow - 1));



        $total_amount = 0;

        $no_of_days = 0;

        $total_interest = 0;

        $total_principal = 0;

        for ($i = 1; $i <= $term; $i++)

        {

            $compound_interest = $loan_amount * $interest_rate;

            $principal_amount = $payment_amount - $compound_interest;

            $balance_owed = $loan_amount - $principal_amount;



            $total_amount += $payment_amount;



            if ($this->config->item('date_format') == 'd/m/Y')

            {

                $payment_date = uk_to_isodate($post_var["InstallmentStarted"]);

            }

            else

            {

                $payment_date = $post_var["InstallmentStarted"];

            }



            switch ($period)

            {

                case "day":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +' . ($i + 1) . ' days'));

                    break;

                case "week":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +' . ($i * 7) . ' days'));

                    break;

                case "month":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +' . ($i + 1) . ' months'));

                    break;

                case "year":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +' . ($i + 1) . ' years'));

                    break;

            }

            

            if (is_plugin_active("holidays") )

            {

                $payment_date = get_excluded_days($payment_date, $exclude_schedules);

            }



            $tmp = [];

            $tmp["payment_date"] = $payment_date;

            $tmp["payment_balance"] = $balance_owed;

            $tmp["penalty_amount"] = $penalty_amount;

            $tmp["interest"] = $compound_interest;

            $tmp["payment_amount"] = $payment_amount;



            $data_scheds[] = $tmp;



            $loan_amount = $balance_owed;

            $no_of_days++;

            $total_interest += $compound_interest;

            $total_principal += $principal_amount;

        }



        return $data_scheds;

    }



    function calculate_outstanding_interest($post_var)

    {

        $grace_period = $post_var["grace_period_days"];

        $term = $post_var["NoOfPayments"];

        $loan_amount = $post_var["ApplyAmt"];

        $interest_rate = $post_var["TotIntRate"];

        $period = $post_var["PayTerm"];

        $exclude_sundays = $post_var["exclude_sundays"];

        $penalty_amount = $post_var["penalty_amount"];

        $exclude_schedules = $post_var["exclude_schedules"];



        $interest_amount = ( (float) ($loan_amount) * (((float) $interest_rate) / 100) );

        $principal_amount = (float) ($loan_amount) + (float) ($interest_amount);



        $payment_amount = (float) ($interest_amount) + 50;

        // Perform a loop to find the closest payment amount



        $data_scheds = [];



        $total_amount = 0;

        $no_of_days = 0;

        $total_interest = 0;

        $total_principal = 0;



        $i = 0;

        $y = 1;

        $increment_day = 0;

        

        if ($this->config->item('date_format') == 'd/m/Y')

        {

            $payment_date = uk_to_isodate($post_var["InstallmentStarted"]);

        }

        else

        {

            $payment_date = $post_var["InstallmentStarted"];

        }



        do

        {

            $balance_owed = ($principal_amount - $payment_amount);



            $total_amount += $payment_amount;

            

            $color = $title = $grace_period_days = '';

            if ( isset($grace_period[$y]) )

            {

                $payment_date = date("Y-m-d", strtotime($payment_date . ' +' . $grace_period[$y] . ' day'));

                $color = 'red';

                $title = 'In grace period';

                $grace_period_days = $grace_period[$y] . ' days';

            }



            if (is_plugin_active("holidays") )

            {

                $payment_date = get_excluded_days($payment_date, $exclude_schedules);

            }

            else

            {

                if ($exclude_sundays)

                {

                    $in_day = date("l", strtotime($payment_date));

                    $in_day = strtolower($in_day);



                    if ($in_day == "sunday")

                    {

                        $payment_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime($payment_date)) . '+1 day'));

                        $increment_day++;

                    }

                }

            }



            $tmp = [];

            $tmp["payment_date"] = date($this->config->item('date_format'), strtotime($payment_date));

            $tmp["payment_balance"] = $balance_owed;

            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';

            $tmp["penalty_amount"] = $penalty_amount;

            $tmp["interest"] = $interest_amount;

            $tmp["payment_amount"] = $payment_amount;



            $data_scheds[] = $tmp;



            $loan_amount = $balance_owed;

            $no_of_days++;

            $total_interest += $interest_amount;

            $total_principal += $principal_amount;



            $principal_amount = ((float) ($balance_owed) + ( ((float) ($balance_owed) * ((float) ($interest_rate) / 100)) ) );

            $interest_amount = ((float) ($balance_owed) * ((float) ($interest_rate) / 100));

            

            switch ($period)

            {

                case "day":

                    $payment_date = date('Y-m-d', strtotime($payment_date . ' +1 day'));

                    break;

                case "week":

                    $payment_date = date('Y-m-d', strtotime($payment_date . ' +7 day'));

                    break;

                case "month":

                    $payment_date = date('Y-m-d', strtotime($payment_date . ' +1 month'));

                    break;

                case "year":

                    $payment_date = date('Y-m-d', strtotime($payment_date . ' +1 year'));

                    break;

            }



            $increment_day++;

            $i++;

            $y++;

        } while ($balance_owed > 0);



        return $data_scheds;

    }



    function calculate_interest_only($post_var)

    {

        $grace_period = $post_var["grace_period_days"];

        $term = $post_var["NoOfPayments"];

        $loan_amount = $post_var["ApplyAmt"];

        $interest_rate = $post_var["TotIntRate"];

        $period = $post_var["PayTerm"];

        $exclude_sundays = $post_var["exclude_sundays"];

        $penalty_amount = $post_var["penalty_amount"];

        $exclude_schedules = $post_var["exclude_schedules"];



        $interest_amount = ( (float) ($loan_amount) * (((float) $interest_rate) / 100) );

        $principal_amount = (float) ($loan_amount) + (float) ($interest_amount);



        $payment_amount = (float) ($interest_amount);

        // Perform a loop to find the closest payment amount

        

        $principal_interest = $loan_amount / $term;



        $data_scheds = [];



        $total_amount = 0;

        $no_of_days = 0;

        $total_interest = 0;

        $total_principal = 0;



        $i = 0;

        $y=1;

        $increment_day = -1;



        if ($this->config->item('date_format') == 'd/m/Y')

        {

            $payment_date = uk_to_isodate($post_var["InstallmentStarted"]);

        }

        else

        {

            $payment_date = $post_var["InstallmentStarted"];

        }
        
        if ( $period == 'biweekly' )
        {
            $term *= 2;
            $payment_amount /= 2;
            $principal_interest /= 2;
        }
        
        if ( $period == 'month_weekly' )
        {
            $term *= 4;
            $payment_amount /= 4;
            $principal_interest /= 4;
        }

        do

        {

            $total_amount += $payment_amount;

            $interest_amount = abs($payment_amount - $principal_interest);

            $balance_owed = ($loan_amount - $principal_interest);



            $color = $title = $grace_period_days = '';

            if ( isset($grace_period[$y]) )

            {

                $payment_date = date("Y-m-d", strtotime($payment_date . ' +' . $grace_period[$y] . ' day'));

                $color = 'red';

                $title = 'In grace period';

                $grace_period_days = $grace_period[$y] . ' days';

            }

            

            if (is_plugin_active("holidays") )

            {

                $payment_date = get_excluded_days($payment_date, $exclude_schedules);

            }

            else

            {

                if ($exclude_sundays)

                {

                    $in_day = date("l", strtotime($payment_date));

                    $in_day = strtolower($in_day);



                    if ($in_day == "sunday")

                    {

                        $payment_date = date($this->config->item('date_format'), strtotime(date($this->config->item('date_format'), strtotime($payment_date)) . '+1 day'));

                        $increment_day++;

                    }

                }

            }



            $formatted_payment_date = date($this->config->item('date_format'), strtotime($payment_date));

            

            $tmp = [];

            $tmp["payment_date"] = $formatted_payment_date;

            $tmp["payment_balance"] = $balance_owed;

            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';

            $tmp["penalty_amount"] = $penalty_amount;

            $tmp["interest"] = $interest_amount;

            $tmp["payment_amount"] = $payment_amount;



            $data_scheds[] = $tmp;



            $loan_amount = $balance_owed;

            $no_of_days++;

            $total_interest += $interest_amount;

            $total_principal += $principal_amount;



            $principal_amount = ((float) ($balance_owed) + ( ((float) ($balance_owed) * ((float) ($interest_rate) / 100)) ) );

            $interest_amount = ((float) ($balance_owed) * ((float) ($interest_rate) / 100));

            

            switch ($period)

            {

                case "day":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +1 day'));

                    break;

                case "week":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +7 day'));

                    break;

                case "month":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +1 month'));

                    break;
                
                case "biweekly":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +14 day'));

                    break;
                case "month_weekly":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +7 day'));

                    break;

                case "year":

                    $payment_date = date($this->config->item('date_format'), strtotime($payment_date . ' +1 year'));

                    break;

            }

            

            if ($this->config->item('date_format') == 'd/m/Y')

            {

                $payment_date = uk_to_isodate($payment_date);

            }

            else

            {

                $payment_date = $payment_date;

            }



            $increment_day++;

            $i++;

            $y++;

        } while ($i < $term);



        return $data_scheds;

    }

    

    function calculate_mortgage2_old($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $penalty_amount = $post_var["penalty_amount"];

        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $data_scheds = [];
        $interest_rate = ($interest_rate / 100);
        $balance_owed = $apply_amount;
        
        $term = (int) $post_var["NoOfPayments"];
        
        if ( $pay_term_name == 'biweekly' )
        {
            $term *= 2;
            $pay_term *= 2;
            $interest_rate /= 2;
        }
        
        if ( $pay_term_name == 'month_weekly' )
        {
            $term *= 4;
            $pay_term *= 4;
            $interest_rate /= 4;
        }

        $i = 0;
        $y = 1;
        while ($balance_owed > 0 && $i < $term)
        {
            $pay_term = $pay_term > 0 ? $pay_term : 1;
            $deno = 1 - 1 / pow((1 + $interest_rate), $pay_term);
            $deno = $deno > 0 ? $deno : 1;

            $term_pay = ($apply_amount * $interest_rate) / $deno;
            $interest = $apply_amount * $interest_rate;
            $principal_amount = $term_pay - $interest;
            $balance_owed = $apply_amount - $principal_amount;

            $color = $title = $grace_period_days = '';
            if (isset($grace_period[$y]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$y] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$y] . ' days';
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount;
            $tmp["interest"] = $interest;
            $tmp["payment_amount"] = $term_pay;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;

            $pay_term = $pay_term - 1;
            $i++;
            $y++;
        }

        return $data_scheds;
    }

    function calculate_mortgage2($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
		$operating_expenses = $post_var["operating_expenses"];
        $penalty_amount = $post_var["penalty_amount"];
        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];
		$adicional_fees = $post_var["additional_fees"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $term = (int) $post_var["NoOfPayments"];

        if ( $pay_term_name == 'biweekly' )
        {
            $term *= 2;
            $pay_term *= 2;
			$interest_rate /= 2;
            $operating_expenses /= 2;
        }
        
        if ( $pay_term_name == 'month_weekly' )
        {
            $term *= 4;
            $pay_term *= 4;
			$interest_rate /= 4;
            $operating_expenses /= 4;
        }

        $data_scheds = [];
        $interest_rate = ($interest_rate / 100);
		//$operating_expenses = ($operating_expenses / 100);
        $balance_owed = $apply_amount;
                
        $i = 0;
        $y = 1;
        while ($balance_owed > 0 && $i < $term)
        {
            $pay_term = $pay_term > 0 ? $pay_term : 1;
            $deno = 1 - 1 / pow((1 + $interest_rate), $pay_term);
            $deno = $deno > 0 ? $deno : 1;

            //$operating_expenses_amount = $apply_amount * $operating_expenses;
            $operating_expenses_amount = $operating_expenses;
            $term_pay = ($apply_amount * $interest_rate) / $deno;
			$payment_amount = $term_pay + $operating_expenses_amount;
            $interest = $apply_amount * $interest_rate;
            $principal_amount = $term_pay - $interest;
            $balance_owed = $apply_amount - $principal_amount;
            /*
            $datos = array();
            array_push($datos,$deno);
            array_push($datos,$operating_expenses_amount);
            array_push($datos,$term_pay);
            array_push($datos,$payment_amount);
            array_push($datos,$interest);
            array_push($datos,$principal_amount);
            array_push($datos,$balance_owed);
            array_push($datos,$interest_rate);
            array_push($datos,$operating_expenses);
            */

            $color = $title = $grace_period_days = '';
            if (isset($grace_period[$y]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$y] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$y] . ' days';
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount;
            $tmp["interest"] = $interest;
            $tmp["payment_amount"] = $payment_amount;
            $tmp["payment_amount_capital"] = $principal_amount;
			$tmp["operating_expenses_amount"] = $operating_expenses_amount;
            $tmp["valores_calculados"] = $datos;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;

            $pay_term = $pay_term - 1;
            $i++;
            $y++;
        }

        return $data_scheds;
    }


    function calculate_loan_pago_excel($post_var)

    {

        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int)$post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
        $operating_expenses = $post_var["operating_expenses"];
        $penalty_amount = $post_var["penalty_amount"];
        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];
        $adicional_fees = $post_var["additional_fees"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        if ( $pay_term_name == 'biweekly' )
        {
            $term *= 2;
            $pay_term *= 2;
            $interest_rate /= 2;
            $operating_expenses /=2;
        }
        
        if ( $pay_term_name == 'month_weekly' )
        {
            $term *= 4;
            $pay_term *= 4;
            $interest_rate /= 4;
            $operating_expenses /=2;
        }

        $loan_amount = $apply_amount;
        $interest_amount = ($loan_amount * ($interest_rate/100));
        //$operating_expenses_amount = ($loan_amount * ($operating_expenses/100));
        $operating_expenses_amount = $operating_expenses;
        $payment_amount = (($interest_rate/100)*pow((1+($interest_rate/100)),$pay_term))*($loan_amount/(pow((1+($interest_rate/100)),$pay_term)-1));
        $payment_amount_fees = $payment_amount + $operating_expenses_amount;
        $data_scheds = [];
        $no_of_days = 0;

        for ( $i=1; $i <= $pay_term; $i++ )
        {
            $balance_owed = $loan_amount - ($payment_amount_fees - $interest_amount - $operating_expenses_amount);
            $color = $title = $grace_period_days = '';
            if ( isset($grace_period[$i]) )
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$i] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$i] . ' days';
            }
            if (is_plugin_active("holidays") )
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);
                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed; 
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount / $pay_term;
            $tmp["interest"] = $interest_amount;
            $tmp["payment_amount"] = $payment_amount_fees;
            $tmp["payment_amount_capital"] = $payment_amount;
            $tmp["operating_expenses_amount"] = $operating_expenses_amount;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }
            //$tmp["principal_amount"] = $principal_amount;
            //$payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);

            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;
            $loan_amount = $balance_owed;
            $interest_amount = ($loan_amount * ($interest_rate/100));
            //$operating_expenses_amount = ($loan_amount * ($operating_expenses/100));
            $payment_amount_fees = $payment_amount + $operating_expenses_amount;
            //$principal_amount = $payment_amount_fees - $interest_amount - $adicional_fees_amount;
            $no_of_days++;

        }



        return $data_scheds;

    }


    function calculate_fixed_fee($post_var)
    {
        $grace_period = $post_var["grace_period_days"];
        $apply_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $pay_term = (int) $post_var["NoOfPayments"];
        $pay_term_name = $post_var["PayTerm"];
		$operating_expenses = $post_var["operating_expenses"];
        $penalty_amount = $post_var["penalty_amount"];
        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];
		$adicional_fees = $post_var["additional_fees"];

        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
        }
        else
        {
            $payment_date = strtotime($post_var["InstallmentStarted"]);
        }

        $term = (int) $post_var["NoOfPayments"];

        if ( $pay_term_name == 'biweekly' )
        {
            $term *= 2;
            $pay_term *= 2;
			$interest_rate /= 2;
            $operating_expenses /= 2;
        }
        
        if ( $pay_term_name == 'month_weekly' )
        {
            $term *= 4;
            $pay_term *= 4;
			$interest_rate /= 4;
            $operating_expenses /= 4;
        }

        $data_scheds = [];
        $interest_rate = ($interest_rate / 100);
		//$operating_expenses = ($operating_expenses / 100);
        $balance_owed = $apply_amount;
        //$operating_expenses_amount = $apply_amount * $operating_expenses;        
        $operating_expenses_amount = $operating_expenses;

        $i = 0;
        $y = 1;
        while ($balance_owed > 0 && $i < $term)
        {
            $pay_term = $pay_term > 0 ? $pay_term : 1;
            $deno = 1 - 1 / pow((1 + $interest_rate), $pay_term);
            $deno = $deno > 0 ? $deno : 1;
            
            $term_pay = ($apply_amount * $interest_rate) / $deno;
			$payment_amount = $term_pay + $operating_expenses_amount;
            $interest = $apply_amount * $interest_rate;
            $principal_amount = $term_pay - $interest;
            $balance_owed = $apply_amount - $principal_amount;
            /*
            $datos = array();
            array_push($datos,$deno);
            array_push($datos,$operating_expenses_amount);
            array_push($datos,$term_pay);
            array_push($datos,$payment_amount);
            array_push($datos,$interest);
            array_push($datos,$principal_amount);
            array_push($datos,$balance_owed);
            array_push($datos,$interest_rate);
            array_push($datos,$operating_expenses);
            */

            $color = $title = $grace_period_days = '';
            if (isset($grace_period[$y]))
            {
                $payment_date = strtotime(date("Y-m-d", $payment_date) . ' +' . $grace_period[$y] . ' day');
                $color = 'red';
                $title = 'In grace period';
                $grace_period_days = $grace_period[$y] . ' days';
            }

            $tmp = [];
            $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
            $tmp["payment_balance"] = $balance_owed;
            $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
            $tmp["penalty_amount"] = $penalty_amount;
            $tmp["interest"] = $interest;
            $tmp["payment_amount"] = $payment_amount;
            $tmp["payment_amount_capital"] = $principal_amount;
			$tmp["operating_expenses_amount"] = $operating_expenses_amount;
            $tmp["valores_calculados"] = $datos;

            if ( $pay_term_name == 'biweekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+2 week');
            }
            else if ( $pay_term_name == 'month_weekly' )
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 week');
            }
            else
            {
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term_name);
            }

            if (is_plugin_active("holidays"))
            {
                $payment_date = get_excluded_days($payment_date, $exclude_schedules);
            }
            else
            {
                if ($exclude_sundays)
                {
                    $in_day = date("l", $payment_date);
                    $in_day = strtolower($in_day);

                    if ($in_day == "sunday")
                    {
                        $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                    }
                }
            }

            $apply_amount = $balance_owed;
            $data_scheds[] = $tmp;

            $pay_term = $pay_term - 1;
            $i++;
            $y++;
        }

        return $data_scheds;
    }

    function calculate_fixed_fee_old($post_var)
   
    {
        $grace_period = $post_var["grace_period_days"];
        $term = $post_var["NoOfPayments"];
        $loan_amount = $post_var["ApplyAmt"];
        $interest_rate = $post_var["TotIntRate"];
        $exclude_sundays = $post_var["exclude_sundays"];
        $exclude_schedules = $post_var["exclude_schedules"];
        $penalty_amount = $post_var["penalty_amount"];
        $pay_term = $post_var["PayTerm"];
        $operating_expenses = $post_var["operating_expenses"];


        if ($pay_term == 'biweekly')
        {
            $fixed_amount = ($loan_amount * ($interest_rate / 100)) * ($term);
            $interest_amount = ($loan_amount * ($interest_rate/100));
            $operating_expenses_amount = ($loan_amount * ($operating_expenses/100));
            $payment_amount = (($interest_rate/100)*pow((1+($interest_rate/100)),$pay_term))*($loan_amount/(pow((1+($interest_rate/100)),$pay_term)-1));
            $payment_amount_fees = $payment_amount + $operating_expenses_amount;
            $total_amount = 0;
            $no_of_days = 0;
            $total_interest = 0;
            $total_principal = 0;

            if ($this->config->item('date_format') == 'd/m/Y')
            {
                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
            }
            else
            {
                $payment_date = strtotime($post_var["InstallmentStarted"]);
            }
            $data_scheds = [];
            for ($i = 1; $i <= ($term * 2); $i++)
            {
                $compound_interest = $interest_amount / 2;
                $principal_amount = $payment_amount_fees - $operating_expenses_amount - $compound_interest;
                $balance_owed = $loan_amount - $principal_amount;
                $total_amount += $payment_amount_fees;
                $color = $title = $grace_period_days = '';

                if ( isset($grace_period[$i]) )
                {
                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');
                    $color = 'red';
                    $title = 'In grace period';
                    $grace_period_days = $grace_period[$i] . ' days';
                }
                $tmp = [];
                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
                $tmp["payment_balance"] = $balance_owed;
                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
                $tmp["penalty_amount"] = $penalty_amount;
                $tmp["interest"] = $compound_interest;
                $tmp["payment_amount"] = $payment_amount_fees;
                $tmp["payment_amount_capital"] = $principal_amount;
                $tmp["operating_expenses_amount"] =  $operating_expenses_amount;

                $data_scheds[] = $tmp;
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+15 days');
                if (is_plugin_active("holidays") )
                {
                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);
                }
                else
                {
                    if ($exclude_sundays)
                    {
                        $in_day = date("l", $payment_date);
                        $in_day = strtolower($in_day);
                        if ($in_day == "sunday")
                        {
                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                        }
                    }
                }
                $loan_amount = $balance_owed;
                $no_of_days++;
                $total_interest += $compound_interest;
                $total_principal += $principal_amount;
            }
        }
        else if ( $pay_term == 'month_weekly' )
        {
            $fixed_amount = ($loan_amount * ($interest_rate / 100)) * ($term);
            $fixed_operating_amount = ($loan_amount * ($operating_expenses/100)) * ($term);
            $payment_amount = (($interest_rate/100)*pow((1+($interest_rate/100)),$pay_term))*($loan_amount/(pow((1+($interest_rate/100)),$pay_term)-1));
            $operating_expenses_amount = ($loan_amount * ($operating_expenses/100));
            $payment_amount_fees = $payment_amount + $operating_expenses_amount;
            $payment_amount_adjust = ($payment_amount_fees * $term) / ($term * 4);
            $interest_amount = ($loan_amount * ($interest_rate/100));
            $total_amount = 0;
            $no_of_days = 0;
            $total_interest = 0;
            $total_principal = 0;
            if ($this->config->item('date_format') == 'd/m/Y')
            {
                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
            }
            else
            {
                $payment_date = strtotime($post_var["InstallmentStarted"]);
            }
            $data_scheds = [];
            for ($i = 1; $i <= ($term * 4); $i++)
            {
                $compound_interest = $interest_amount / 4;
                $principal_amount = $payment_amount_adjust - $fixed_operating_amount - $compound_interest;
                $balance_owed = $loan_amount - $principal_amount;
                $total_amount += $payment_amount;
                $color = $title = $grace_period_days = '';
                if ( isset($grace_period[$i]) )
                {
                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');
                    $color = 'red';
                    $title = 'In grace period';
                    $grace_period_days = $grace_period[$i] . ' days';
                }
                $tmp = [];
                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
                $tmp["payment_balance"] = $balance_owed;
                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
                $tmp["penalty_amount"] = $penalty_amount;
                $tmp["interest"] = $compound_interest;
                $tmp["payment_amount"] = $payment_amount;
                $tmp["payment_amount_capital"] = $principal_amount;
                $tmp["operating_expenses_amount"] =  $operating_expenses_amount;

                $data_scheds[] = $tmp;
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+7 days');
                if (is_plugin_active("holidays") )
                {
                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);
                }
                else
                {
                    if ($exclude_sundays)
                    {
                        $in_day = date("l", $payment_date);
                        $in_day = strtolower($in_day);
                        if ($in_day == "sunday")
                        {
                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                        }
                    }
                }
                $loan_amount = $balance_owed;
                $no_of_days++;
                $total_interest += $compound_interest;
                $total_principal += $principal_amount;
            }
        }
        else
        {
            $data_scheds = [];
            $fixed_amount = ($loan_amount * ($interest_rate / 100)) * $term;
            $payment_amount = (($interest_rate/100)*pow((1+($interest_rate/100)),$pay_term))*($loan_amount/(pow((1+($interest_rate/100)),$pay_term)-1));
            $operating_expenses_amount = ($loan_amount * ($operating_expenses/100));
            $payment_amount_fees = $payment_amount + $operating_expenses_amount;
            $interest_amount = ($loan_amount * ($interest_rate/100));
            $total_amount = 0;
            $no_of_days = 0;
            $total_interest = 0;
            $total_principal = 0;
            if ($this->config->item('date_format') == 'd/m/Y')
            {
                $payment_date = strtotime(uk_to_isodate($post_var["InstallmentStarted"]));
            }
            else
            {
                $payment_date = strtotime($post_var["InstallmentStarted"]);
            }
            for ($i = 1; $i <= $term; $i++)
            {
                $compound_interest = $interest_amount;
                $principal_amount = $payment_amount_fees - $operating_expenses_amount - $compound_interest;
                $balance_owed = $loan_amount - $principal_amount;
                $total_amount += $payment_amount_fees;
                $color = $title = $grace_period_days = '';
                if ( isset($grace_period[$i]) )
                {
                    $payment_date = strtotime(date("Y-m-d H:i:s",$payment_date) . ' +' . $grace_period[$i] . ' day');
                    $color = 'red';
                    $title = 'In grace period';
                    $grace_period_days = $grace_period[$i] . ' days';
                }
                $tmp = [];
                $tmp["payment_date"] = date($this->config->item('date_format'), $payment_date);
                $tmp["payment_balance"] = $balance_owed;
                $tmp["grace_period"] = $title != '' ? $grace_period_days : '';
                $tmp["penalty_amount"] = $penalty_amount;
                $tmp["interest"] = $compound_interest;
                $tmp["payment_amount"] = $payment_amount;
                $tmp["payment_amount_capital"] = $principal_amount;
                $tmp["operating_expenses_amount"] =  $operating_expenses_amount;
                $data_scheds[] = $tmp;
                $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 ' . $pay_term);
                if (is_plugin_active("holidays") )
                {
                    $payment_date = get_excluded_days($payment_date, $exclude_schedules);
                }
                else
                {
                    if ($exclude_sundays)
                    {
                        $in_day = date("l", $payment_date);
                        $in_day = strtolower($in_day);
                        if ($in_day == "sunday")
                        {
                            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
                        }
                    }
                }
                $loan_amount = $balance_owed;
                $no_of_days++;
                $total_interest += $compound_interest;
                $total_principal += $principal_amount;
            }
        }
        return $data_scheds;
    }
}



?>