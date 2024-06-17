<?php

class Home_model extends CI_Model {

    function get_total_wallet( $filters = [] )
    {
        // credit
        $this->load->model("role");
        $person_info = $this->Employee->get_logged_in_employee_info();
        $role_info = $this->role->get_info($person_info->role_id);
        
        $user_id = $person_info->person_id;
        
        $user_ids = [];
        $user_ids[] = $user_id;
        
        $tmp = json_decode($role_info->low_level, 1);
        if (is_array($tmp) && count($tmp) > 0 )
        {
            $res = $this->role->get_staff_user_ids(implode(",", $tmp));
            foreach($res as $user_id)
            {
                $user_ids[] = $user_id;
            }
        }
        
        $user_ids = implode(",", $user_ids);

        if ( isset($filters["date_from"]) && $filters["date_to"] )
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'credit' AND added_by IN ($user_ids) AND trans_date >= " . strtotime($filters["date_from"] . ' 00:00:00') . " AND trans_date <= " . strtotime($filters["date_to"] . ' 23:00:00');
        }
        else
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'credit' AND added_by IN ($user_ids)";
        }
        
        if (is_plugin_active("branches"))
        {
            $sql .= " AND branch_id = " . $this->session->userdata("branch_id");
        }
        
        $query = $this->db->query($sql);
        $credit = $query->row()->amount;

        // debit
        if ( isset($filters["date_from"]) && $filters["date_to"] )
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'debit' AND added_by IN ($user_ids) AND trans_date >= " . strtotime($filters["date_from"] . ' 00:00:00') . " AND trans_date <= " . strtotime($filters["date_to"] . ' 23:00:00');
        }
        else
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'debit' AND added_by IN ($user_ids)";            
        }
        
        if (is_plugin_active("branches"))
        {
            $sql .= " AND branch_id = " . $this->session->userdata("branch_id");
        }
        
        $query = $this->db->query($sql);
        $debit = $query->row()->amount;
        
        // transfer to
        if ( isset($filters["date_from"]) && $filters["date_to"] )
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'transfer' AND added_by IN ($user_ids) AND transfer_to NOT IN ($user_ids) AND trans_date >= " . strtotime($filters["date_from"] . ' 00:00:00') . " AND trans_date <= " . strtotime($filters["date_to"] . ' 23:00:00');
        }
        else
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'transfer' AND added_by IN ($user_ids) AND transfer_to NOT IN ($user_ids)";
        }
        
        if (is_plugin_active("branches"))
        {
            $sql .= " AND branch_id = " . $this->session->userdata("branch_id");
        }
        
        $query = $this->db->query($sql);
        $transfer_to = $query->row()->amount;

        // transfer to me
        if ( isset($filters["date_from"]) && $filters["date_to"] )
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'transfer' AND transfer_to IN ($user_ids) AND trans_date >= " . strtotime($filters["date_from"] . ' 00:00:00') . " AND trans_date <= " . strtotime($filters["date_to"] . ' 23:00:00');
        }
        else
        {
            $sql = "SELECT SUM(amount) AS amount FROM " . $this->db->dbprefix("wallets") . " WHERE wallet_type = 'transfer' AND transfer_to IN ($user_ids)";            
        }
        
        if (is_plugin_active("branches"))
        {
            $sql .= " AND branch_id = " . $this->session->userdata("branch_id");
        }
        
        $query = $this->db->query($sql);
        $transfer_to_me = $query->row()->amount;

        return $debit - $credit - $transfer_to + $transfer_to_me;
    }
    
    function get_total_loans( $filters = [] )
    {
        $this->db->select("SUM(apply_amount) as total_loans");        
        $this->db->from("loans");
        $this->db->join("customers c", "c.person_id = loans.customer_id", "LEFT");
        $this->db->where("delete_flag", "0");
        $this->db->where("loan_status", "approved");
        $this->db->where("c.deleted", 0);
        
        if ( isset($filters["date_from"]) )
        {
            $this->db->where("loan_approved_date >=", strtotime($filters["date_from"] . ' 00:00:00'));
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("loan_approved_date <=", strtotime($filters["date_to"] . ' 23:00:00'));
        }
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("c.branch_id", $this->session->userdata("branch_id"));
        }
        
        $query = $this->db->get();
        
        $res = $query->row();

        return to_currency($res->total_loans, TRUE, 0);
    }
    
    function get_total_fees( $filters = [] )
    {
        $this->db->select("add_fees");        
        $this->db->from("loans");
        $this->db->join("customers c", "c.person_id = loans.customer_id", "LEFT");
        $this->db->where("delete_flag", "0");
        $this->db->where("loan_status", "approved");
        $this->db->where("c.deleted", 0);
        
        if ( isset($filters["date_from"]) )
        {
            $this->db->where("loan_approved_date >=", strtotime($filters["date_from"] . ' 00:00:00'));
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("loan_approved_date <=", strtotime($filters["date_to"] . ' 23:00:00'));
        }
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("c.branch_id", $this->session->userdata("branch_id"));
        }
        
        $query = $this->db->get();
        
        $total_fees = 0;
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $tmp = json_decode($row->add_fees, true);
                foreach ( $tmp as $fee )
                {
                    $total_fees += $fee;
                }
            }
        }

        return to_currency($total_fees, TRUE, 0);
    }
    
    function get_total_interest( $filters = [] )
    {
        $select = "if(interest_type='loan_deduction', SUM(loan_amount-net_proceeds), SUM(loan_amount-apply_amount)) as total_interest";
        $this->db->select($select);        
        $this->db->from("loans");
        $this->db->join("customers c", "c.person_id = loans.customer_id", "LEFT");
        $this->db->where("delete_flag", "0");
        $this->db->where("loan_status", "approved");
        $this->db->where("c.deleted", 0);
        
        if ( isset($filters["date_from"]) )
        {
            $this->db->where("loan_approved_date >=", strtotime($filters["date_from"] . ' 00:00:00'));
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("loan_approved_date <=", strtotime($filters["date_to"] . ' 23:00:00'));
        }
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("c.branch_id", $this->session->userdata("branch_id"));
        }
        
        $query = $this->db->get();
        
        $res = $query->row();

        return to_currency($res->total_interest, TRUE, 0);
    }
    
    function get_total_customers( $filters = [] )
    {
        $this->db->from('customers');
        $this->db->where('deleted', 0);
        
        if ( isset($filters["date_from"]) )
        {
            $this->db->where("date_added >=", $filters["date_from"] . ' 00:00:00');
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("date_added <=", $filters["date_to"] . ' 23:00:00');
        }
        
        if (is_plugin_active("branches"))
        {
            $this->db->where("branch_id", $this->session->userdata('branch_id'));
        }
        
        return $this->db->count_all_results();
    }
    
    function get_total_payments( $filters = [] )
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->Employee->getLowerLevels($low_levels);
        
        $user_ids = [];
        $user_ids[] = $user_id;        
        if ( is_array($low_levels) && count($low_levels) > 0 )
        {
            $this->db->select("person_id");
            $this->db->where_in("role_id", $low_levels);
            $query = $this->db->get("people");
            
            if ( $query && $query->num_rows() > 0 )
            {
                foreach ( $query->result() as $row )
                {
                    $user_ids[] = $row->person_id;
                }
            }
        } 
        
        $this->db->select("SUM(paid_amount) as total_payments");
        $this->db->from("loan_payments");
        $this->db->join("customers my_customer", "my_customer.person_id=loan_payments.customer_id", "LEFT");

        if ( isset($filters["date_from"]) )
        {
            $this->db->where("date_paid >=", strtotime($filters["date_from"] . ' 00:00:00'));
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("date_paid <=", strtotime($filters["date_to"] . ' 23:00:00'));
        }
        
        if ( is_array($user_ids) && count($user_ids) > 0 )
        {
            $this->db->where_in("teller_id", $user_ids);
        }
        
        $this->db->where("loan_payments.delete_flag", 0);
        $this->db->where("my_customer.deleted", 0);
        
        if (is_plugin_active("branches"))
        {
            $this->db->where("my_customer.branch_id", $this->session->userdata('branch_id'));
        }
        
        $query = $this->db->get();
        
        $res = $query->row();

        return to_currency($res->total_payments, TRUE, 0);
    }
    
    function get_total_num_payments( $filters = [] )
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->Employee->getLowerLevels($low_levels);
        
        $user_ids = [];
        $user_ids[] = $user_id;        
        if ( is_array($low_levels) && count($low_levels) > 0 )
        {
            $this->db->select("person_id");
            $this->db->where_in("role_id", $low_levels);
            $query = $this->db->get("people");
            
            if ( $query && $query->num_rows() > 0 )
            {
                foreach ( $query->result() as $row )
                {
                    $user_ids[] = $row->person_id;
                }
            }
        } 
        
        $this->db->select("count(*) as total_num_payments");
        $this->db->from("loan_payments");
        $this->db->join("customers my_customer", "my_customer.person_id=loan_payments.customer_id", "LEFT");

        if ( isset($filters["date_from"]) )
        {
            $this->db->where("date_paid >=", strtotime($filters["date_from"] . ' 00:00:00'));
        }
        
        if ( isset($filters["date_to"]) )
        {
            $this->db->where("date_paid <=", strtotime($filters["date_to"] . ' 23:00:00'));
        }
        
        $this->db->where("loan_payments.delete_flag", 0);
        $this->db->where("my_customer.deleted", 0);
        
        if ( is_array($user_ids) && count($user_ids) > 0 )
        {
            $this->db->where_in("teller_id", $user_ids);
        }
        
        if (is_plugin_active("branches"))
        {
            $this->db->where("my_customer.branch_id", $this->session->userdata('branch_id'));
        }
        
        $query = $this->db->get();
        $res = $query->row();

        return $res->total_num_payments;
    }
    
    function get_overdue_customers()
    {
        $where = '';
        if (is_plugin_active("branches"))
        {
            $where = ' AND a.branch_id=' . $this->session->userdata("branch_id");            
        }
        
        $sql = "
            SELECT
                CONCAT(p.first_name, ' ', p.last_name) full_name,
                p.photo_url,
                al.loan_payment_date,
                (SELECT a.payment_due FROM c19_loan_payments a WHERE a.loan_id = al.loan_id AND a.delete_flag = 0 ORDER BY a.payment_due DESC LIMIT 1) due_paid,
                al.loan_balance,
                al.periodic_loan_table,
                a.*,
                (select count(*) from c19_loans l where l.customer_id = a.person_id and l.loan_status = 'approved' and l.delete_flag = 0) total_loan_approved,
                (select count(*) from c19_loan_payments lp where lp.customer_id = a.person_id and lp.delete_flag = 0) total_payments_made,
                (select lad.loan_applied_date from c19_loans lad where lad.customer_id = a.person_id and lad.delete_flag = 0 ORDER BY lad.loan_applied_date DESC LIMIT 1) last_applied_date

            FROM c19_loans al
            LEFT JOIN c19_customers a ON a.person_id = al.customer_id
            LEFT JOIN c19_people p on p.person_id = a.person_id
            WHERE from_unixtime(al.loan_payment_date) <= '".date('Y-m-d')."' 
            AND al.loan_status = 'approved' 
            AND al.loan_balance > 0
            AND al.delete_flag = 0
            AND a.deleted = 0
            $where
            ORDER BY al.loan_payment_date desc
            LIMIT 7
            ";
        $query = $this->db->query($sql);
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_overdue_today_customers()
    {
        $where = '';
        if (is_plugin_active("branches"))
        {
            $where = ' AND a.branch_id=' . $this->session->userdata("branch_id");            
        }
        
        $sql = "
            SELECT
                CONCAT(p.first_name, ' ', p.last_name) full_name,
                p.photo_url,
                al.loan_payment_date,
                al.loan_balance,
                a.*,
                (select count(*) from c19_loans l where l.customer_id = a.person_id and l.loan_status = 'approved' and l.delete_flag = 0) total_loan_approved,
                (select count(*) from c19_loan_payments lp where lp.customer_id = a.person_id) total_payments_made,
                (select lad.loan_applied_date from c19_loans lad where lad.customer_id = a.person_id and lad.delete_flag = 0 ORDER BY lad.loan_applied_date DESC LIMIT 1) last_applied_date

            FROM c19_loans al
            LEFT JOIN c19_customers a ON a.person_id = al.customer_id
            LEFT JOIN c19_people p on p.person_id = a.person_id
            WHERE from_unixtime(al.loan_payment_date) = '".date('Y-m-d')."' 
            AND al.loan_status <> 'pending' 
            AND al.loan_balance > 0
            AND al.delete_flag = 0
            AND a.deleted = 0
            $where
            ORDER BY al.loan_payment_date desc
            LIMIT 7
            ";
        $query = $this->db->query($sql);
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }

    function get_top_customers()
    {
        $where = '';
        if (is_plugin_active("branches"))
        {
            $where = ' AND a.branch_id=' . $this->session->userdata("branch_id");            
        }
        $sql = "
            SELECT
                CONCAT(p.first_name, ' ', p.last_name) full_name,
                p.photo_url,
                a.*,
                (select count(*) from c19_loans l where l.customer_id = a.person_id and l.loan_status = 'approved' and l.delete_flag = 0) total_loan_approved,
                (select count(*) from c19_loan_payments lp where lp.customer_id = a.person_id and lp.delete_flag = 0) total_payments_made,
                (select lad.loan_applied_date from c19_loans lad where lad.customer_id = a.person_id and lad.delete_flag = 0 ORDER BY lad.loan_applied_date DESC LIMIT 1) last_applied_date

            FROM c19_customers a
            LEFT JOIN c19_people p on p.person_id = a.person_id
            WHERE a.deleted = 0
            $where
            ORDER BY total_loan_approved desc
            LIMIT 7
            ";
        $query = $this->db->query($sql);
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_latest_loans()
    {
        $where = '';
        if (is_plugin_active("branches"))
        {
            $where = ' AND a.branch_id=' . $this->session->userdata("branch_id");            
        }
        $sql = "
            SELECT
                CONCAT(p.first_name, ' ', p.last_name) full_name,
                p.photo_url,
                a.*,
                l.apply_amount loan_approved,
                l.loan_balance,
                l.loan_approved_date approved_date,
                l.loan_id
            FROM c19_loans l
            LEFT JOIN c19_customers a ON a.person_id = l.customer_id
            LEFT JOIN c19_people p on p.person_id = a.person_id
            WHERE a.deleted = 0
            AND l.loan_status = 'approved'
            $where
            ORDER BY l.loan_approved_date desc
            LIMIT 5
            ";
        $query = $this->db->query($sql);
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_loan_sales()
    {
        if (is_plugin_active('loan_products'))
        {
            $where = '';
            if (is_plugin_active("branches"))
            {
                $where = ' AND a.branch_id=' . $this->session->userdata("branch_id");            
            }
        
            $sql = "
                SELECT l.loan_product_id, ifNULL(lp.product_name, 'Loan') product_name, count(*) cnt 
                FROM c19_loans l 
                LEFT JOIN c19_loan_products lp ON lp.id = l.loan_product_id 
                LEFT JOIN c19_customers a ON a.person_id = l.customer_id
                WHERE 1 $where
                GROUP BY l.loan_product_id
                ";
            $query = $this->db->query($sql);

            if ( $query && $query->num_rows() > 0 )
            {
                return $query->result();
            }
        }
        
        return [];
    }
    
    function get_transaction_data()
    {
        $where = '';
        if (is_plugin_active("branches"))
        {
            $where = ' AND c.branch_id=' . $this->session->userdata("branch_id");            
        }
            
        $sql = "
            SELECT  COUNT(*) cnt, 
                    DATE_FORMAT(FROM_UNIXTIME(`loan_applied_date`), '%Y-%m') applied_date 
            FROM c19_loans l
            LEFT JOIN c19_customers c ON c.person_id = l.customer_id
            WHERE loan_status = 'approved'
            AND delete_flag = 0
            AND c.deleted = 0
            $where
            GROUP BY applied_date
            ";
        $query = $this->db->query($sql);
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
}

?>