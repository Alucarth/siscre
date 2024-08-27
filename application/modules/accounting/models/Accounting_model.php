<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Accounting_model extends CI_Model
{
    function get_accounts($limit = 10000, $offset = 0, $search = "", $order = [], $filters = [], &$count_all = 0)
    {
        $sorter = array(
            "",
            "code_number",
            "account_name",
            "description",
        );
        
        $str_where = "WHERE account_type = '{$filters["account_type"]}' ";
        $this->db->from('accounting_accounts aa');
        $this->db->where("account_type", $filters["account_type"]);
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("aa.branch_id", $this->session->userdata("branch_id"));
        }
        
        if ($search !== "")
        {
            $this->db->where("(
                aa.code_number LIKE '%" . $search . "%' OR
                aa.account_name LIKE '%" . $search . "%' OR
                aa.description LIKE '%" . $search . "%'
                )");
            
            $str_where .= " AND (
                aa.code_number LIKE '%" . $search . "%' OR
                aa.account_name LIKE '%" . $search . "%' OR
                aa.description LIKE '%" . $search . "%'
                )";
        }

        if ( isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("id", "desc");
        }

        $this->db->limit($limit);
        $this->db->offset($offset);
        
        $query = $this->db->get();
        
        $sql = "SELECT COUNT(*) cnt FROM c19_accounting_accounts aa $str_where";
        $q = $this->db->query($sql);
        if ( $q && $q->num_rows() > 0 )
        {
            $count_all = $q->row()->cnt;
        }
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "Assets", "Viewed assets list");
        }
        
        return $query;
    }
    
    function get_sel_accounts($account_type)
    {
        $this->db->where("account_type", $account_type);
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("branch_id", $this->session->userdata("branch_id"));
        }
        
        $query = $this->db->get("accounting_accounts");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_transactions($limit = 10000, $offset = 0, $search = "", $order = [], $filters = [], &$count_all = 0)
    {
        $sorter = array(
            "",
            "account_name",
            "description",
        );
        
        $str_where = "WHERE transaction_type = '{$filters["transaction_type"]}' ";
        $this->db->select("at.*, aa.account_name");
        $this->db->from('accounting_transactions at');
        $this->db->join("accounting_accounts aa", "aa.id=at.account_id", "LEFT");
        $this->db->where("transaction_type", $filters["transaction_type"]);
        
        if ($search !== "")
        {
            $this->db->where("(
                aa.account_name LIKE '%" . $search . "%' OR
                at.description LIKE '%" . $search . "%'
                )");
            
            $str_where .= " AND (
                aa.account_name LIKE '%" . $search . "%' OR
                at.description LIKE '%" . $search . "%'
                )";
        }
        
        if(is_plugin_active("branches"))
        {
            $this->db->where("at.branch_id", $this->session->userdata("branch_id"));
            $str_where .= " AND at.branch_id = " . $this->session->userdata("branch_id");
        }

        if ( isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("at.id", "desc");
        }

        $this->db->limit($limit);
        $this->db->offset($offset);
        
        $query = $this->db->get();
        
        $sql = "SELECT COUNT(*) cnt FROM c19_accounting_transactions at LEFT JOIN c19_accounting_accounts aa ON aa.id = at.account_id $str_where";
        $q = $this->db->query($sql);
        if ( $q && $q->num_rows() > 0 )
        {
            $count_all = $q->row()->cnt;
        }
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "Assets", "Viewed assets transaction list");
        }
        
        return $query;
    }
    
    public function get_trial_balance_data( $filters = [] )
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.added_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.added_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  b.account_type, 
                    b.account_name, 
                    SUM(a.amount) amount,
                    SUM(a.depreciate_amount) depreciation_amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE 1 $where
            GROUP BY b.account_name
            ORDER BY FIELD(account_type, 'asset', 'liability', 'equity', 'income', 'expenses')
            ";
        
        $query = $this->db->query( $sql );
        
        $tmp = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach( $query->result() as $row )
            {
                $tmp[] = $row;
            }
        }
        
        // Integrating Accounts Plugin - START
        if(is_plugin_active("accounts"))
        {
            $where = '';
            if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
            {
                $where .= " AND a.trans_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
            }

            if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
            {
                $where .= " AND a.trans_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
            }

            if(is_plugin_active("branches"))
            {
                $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
            }

            $sql = "
                SELECT  b.account_type, 
                        b.account_name, 
                        SUM(a.amount) amount,
                        0 depreciation_amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE 1 $where
                GROUP BY b.account_name
                ORDER BY FIELD(account_type, 'asset', 'liability', 'equity', 'income', 'expenses')
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        $tmp[] = $row;
                    }
                }
            }
        }
        // Integrating Accounts Plugin - END
        
        return $tmp;
    }
    
    public function get_financial_income_data( $filters = [] )
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.added_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.added_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  b.account_type, 
                    b.account_name, 
                    SUM(a.amount) amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE account_type IN ('income', 'expenses') $where
            GROUP BY b.account_name
            ORDER BY FIELD(account_type, 'income', 'expenses')
            ";
        
        $query = $this->db->query( $sql );
        
        $financial_data = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $financial_data[] = $row;
            }
        }
        
        // Account Plugin - START
        if(is_plugin_active("accounts"))
        {
            $where = '';
            if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
            {
                $where .= " AND a.trans_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
            }

            if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
            {
                $where .= " AND a.trans_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
            }

            if(is_plugin_active("branches"))
            {
                $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
            }

            $sql = "
                SELECT  b.account_type, 
                        b.account_name, 
                        SUM(a.amount) amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE b.account_type IN ('asset', 'expenses') $where
                GROUP BY b.account_name
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        $financial_data[] = $row;
                    }
                }
            }
        }
        // Account Plugin - END
        
        return $financial_data;
    }
    
    private function _get_map_account_ids()
    {
        $sql = "SELECT id, account_map FROM c19_accounting_accounts a WHERE a.account_map <> ''";
        $query = $this->db->query( $sql );
        
        $map_ids["cash"] = 0;
        $map_ids["bank"] = 0;
        $map_ids["loan"] = 0;
        $map_ids["loan_loss_reserve"] = 0;
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                switch( $row->account_map )
                {
                    case "cash":
                        $map_ids["cash"] = $row->id;
                        break;
                    case "bank":
                        $map_ids["bank"] = $row->id;
                        break;
                    case "loan":
                        $map_ids["loan"] = $row->id;
                        break;
                    case "loan_loss_reserve":
                        $map_ids["loan_loss_reserve"] = $row->id;
                        break;
                }
            }
        }
        
        return $map_ids;
    }
    
    public function get_balance_sheet_data( $filters = [] )
    {
        $map_ids = $this->_get_map_account_ids();
        $cash_account_id = $map_ids["cash"];
        $bank_account_id = $map_ids["bank"];
        $loan_account_id = $map_ids["loan"];
        $loan_loss_reserve_account_id = $map_ids["loan_loss_reserve"];
        
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.added_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.added_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  b.id, 
                    SUM(a.amount) amount, 
                    SUM(a.depreciate_amount) depreciation_amount,
                    b.account_name 
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE a.account_id IN ($cash_account_id, $bank_account_id, $loan_account_id, $loan_loss_reserve_account_id) $where
            GROUP BY a.account_id;
            ";
        
        $query = $this->db->query( $sql );
        
        $cash_amount = 0;
        $cash_amount_bank = 0;
        $loan_loss_reserve = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach( $query->result() as $row )
            {
                switch( $row->id )
                {
                    case $cash_account_id:
                        $cash_amount = $row->amount;
                        break;
                    case $bank_account_id:
                        $cash_amount_bank = $row->amount;
                        break;
                    case $loan_loss_reserve_account_id:
                        $loan_loss_reserve = $row->amount;
                        break;
                }
            }
        }
        
        $current_loan_amount = $this->get_current_loan_amount($filters);
        
        $tmp["cash_amount"] = $cash_amount;
        $tmp["cash_amount_bank"] = $cash_amount_bank;
        $tmp["current_loan_amount"] = $current_loan_amount;
        $tmp["loan_loss_reserve"] = $loan_loss_reserve;
        
        $interest_receivable = $this->get_interest_on_current($filters);
        $net_loan_outstanding = $current_loan_amount + $interest_receivable - $loan_loss_reserve;
        
        $tmp["net_loan_outstanding"] = $net_loan_outstanding;
        $tmp["total_current_assets"] = $cash_amount + $cash_amount_bank + $net_loan_outstanding;
        
        // Non-current Assets
        $non_current_assets = [];
        
        if (is_plugin_active("accounts"))
        {
            $str_where = '';
            if(is_plugin_active("branches"))
            {
                $str_where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
            }

            $sql = "
                SELECT  b.id, 
                        SUM(a.amount) amount, 
                        SUM(a.depreciate_amount) depreciation_amount, 
                        b.account_name 
                FROM c19_accounting_transactions a 
                LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
                WHERE a.account_id NOT IN ($cash_account_id, $bank_account_id, $loan_account_id, $loan_loss_reserve_account_id) $where
                AND b.account_type = 'asset' $str_where
                GROUP BY a.account_id;
                ";

            $query = $this->db->query( $sql );
            
            if ( $query && $query->num_rows() > 0 )
            {
                foreach ( $query->result() as $row )
                {
                    $non_current_assets[] = $row;
                }
            }        
        
            // Account Plugin - START
        
            $where = '';
            if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
            {
                $where .= " AND a.trans_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
            }

            if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
            {
                $where .= " AND a.trans_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
            }

            if(is_plugin_active("branches"))
            {
                $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
            }

            $sql = "
                SELECT  b.account_type, 
                        b.account_name, 
                        SUM(a.amount) amount,
                        0 depreciation_amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE b.account_type = 'asset' $where
                GROUP BY b.account_name
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        $non_current_assets[] = $row;
                    }
                }
            }
        }
        // Account Plugin - END
        
        $tmp["non_current_assets"] = $non_current_assets;
        $tmp['liability_accounts'] = $this->get_account_data('liability', $filters);
        $tmp['equity_accounts'] = $this->get_account_data('equity', $filters);
        
        return $tmp;
    }
    
    public function get_account_data($account_type = '', $filters = [])
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.added_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.added_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  b.id, 
                    SUM(a.amount) amount,
                    b.account_name 
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = '$account_type' $where
            GROUP BY a.account_id;
            ";
        
        $query = $this->db->query( $sql );
        
        $account_data = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $account_data[] = $row;
            }
        }
        
        // Account Plugin - START
        if (is_plugin_active("accounts"))
        {
            $where = '';
            if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
            {
                $where .= " AND a.trans_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
            }

            if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
            {
                $where .= " AND a.trans_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
            }

            if(is_plugin_active("branches"))
            {
                $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
            }

            $sql = "
                SELECT  b.account_type, 
                        b.account_name, 
                        SUM(a.amount) amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE b.account_type = '$account_type' $where
                GROUP BY b.account_name
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        $account_data[] = $row;
                    }
                }
            }
        }
        // Account Plugin - END
        
        return $account_data;
    }
    
    function get_current_loan_amount($filters)
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.loan_approved_date >= '". $filters["date_from"] ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.loan_approved_date <= '". $filters["date_to"] ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND c.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  SUM(t1.current_loan) total_current_loan FROM (

                    SELECT  (t.interest - (t.per_interest * t.payment_cnt)) unpaid_interest,
                            (t.per_interest * t.payment_cnt) paid_interest,
                            if( t.loan_balance - (t.interest - (t.per_interest * t.payment_cnt)) > 0, t.loan_balance - (t.interest - (t.per_interest * t.payment_cnt)), 0 ) current_loan
                    FROM (
                            SELECT  a.loan_id, 
                                              a.loan_balance,
                                    a.periodic_loan_table,
                                    a.apply_amount,
                                    (a.loan_amount - a.apply_amount) interest,
                                    ((a.loan_amount - a.apply_amount)/a.payment_term) 'per_interest',
                                    ( SELECT COUNT(*) cnt FROM c19_loan_payments b WHERE b.loan_id = a.loan_id ) payment_cnt
                            FROM c19_loans a 
                            LEFT JOIN c19_customers c ON c.person_id = a.customer_id
                            WHERE a.loan_status = 'approved' 
                            AND a.delete_flag = 0 $where
                    ) t

            ) t1
            ";
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row()->total_current_loan;
        }
        
        return 0;
    }
    
    function get_interest_on_current($filters, $past_due_only = true)
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.loan_approved_date >= '". $filters["date_from"] ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.loan_approved_date <= '". $filters["date_to"] ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND c.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  a.loan_id, 
                    a.periodic_loan_table,
                    a.apply_amount,
                    (a.loan_amount - a.apply_amount) interest,
                    ( SELECT COUNT(*) cnt FROM c19_loan_payments b WHERE b.loan_id = a.loan_id ) payment_cnt
            FROM c19_loans a 
            LEFT JOIN c19_customers c ON c.person_id = a.customer_id
            WHERE a.loan_status = 'approved' $where
            ";
        $query = $this->db->query( $sql );
        
        $outstanding_interest = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $interest = $row->interest;
                $schedule = json_decode($row->periodic_loan_table, TRUE);
                
                if ( $past_due_only )
                {
                    $paid_interest = 0;
                    for ( $i=0; $i<$row->payment_cnt; $i++ )
                    {
                        $paid_interest += $schedule[$i]["interest"];
                    }

                    //$outstanding_interest += $interest - $paid_interest;
                    $outstanding_interest += $paid_interest;
                }
                else
                {
                    $outstanding_interest += $interest;
                }
            }
            
            return $outstanding_interest;
        }
    }
    
    function get_unpaid_interest_on_current($filters)
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.loan_approved_date >= '". $filters["date_from"] ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.loan_approved_date <= '". $filters["date_to"] ."'";
        }
        
        if(is_plugin_active("branches"))
        {
            $where .= " AND c.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  a.loan_id, 
                    a.periodic_loan_table,
                    a.apply_amount,
                    (a.loan_amount - a.apply_amount) interest,
                    ( SELECT COUNT(*) cnt FROM c19_loan_payments b WHERE b.loan_id = a.loan_id ) payment_cnt
            FROM c19_loans a 
            LEFT JOIN c19_customers c ON c.person_id = a.customer_id
            WHERE a.loan_status = 'approved' $where
            ";
        $query = $this->db->query( $sql );
        
        $outstanding_interest = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $schedule = json_decode($row->periodic_loan_table, TRUE);
                
                $paid_interest = 0;
                for ( $i=0; $i<$row->payment_cnt; $i++ )
                {
                    $paid_interest += $schedule[$i]["interest"];
                }
                
                $total_interest_amount = 0;
                for ( $i=0; $i<count($schedule); $i++ )
                {
                    $total_interest_amount += $schedule[$i]["interest"];
                }

                $outstanding_interest += $total_interest_amount - $paid_interest;
                
            }
            
            return $outstanding_interest;
        }
    }
}
