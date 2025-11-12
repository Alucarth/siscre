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
        
            // if(is_plugin_active("branches"))
            // {
            //     $this->db->where("aa.branch_id", $this->session->userdata("branch_id"));
            // }
        
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
            track_action($user_id, "assets", "Viewed assets list");
        }
        
        return $query;
    }
    
    function get_sel_accounts($account_type)
    {
        $this->db->where("account_type", $account_type);
        
        // if(is_plugin_active("branches"))
        // {
        //     $this->db->where("branch_id", $this->session->userdata("branch_id"));
        // }
        
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
            "", // 0 - actions
            "aa.account_name", // 1 - account_name
            "at.added_date", // 2 - added_date (NUEVO)
            "at.amount", // 3 - amount (NUEVO)
            "at.purchased_date", // 4 - purchased_date (NUEVO)
            "at.purchased_amount", // 5 - purchased_amount (NUEVO)
            "at.depreciate_amount", // 6 - depreciate_amount (NUEVO)
            "at.description", // 7 - description
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
            track_action($user_id, "assets", "Viewed assets transaction list");
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
                    b.code_number, 
                    b.account_name, 
                    SUM(CASE WHEN a.movement_type = 'debit' THEN a.amount ELSE 0 END) as debit_amount,
                    SUM(CASE WHEN a.movement_type = 'credit' THEN a.amount ELSE 0 END) as credit_amount,
                    SUM(a.depreciate_amount) depreciation_amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE 1 $where
            GROUP BY b.account_name, b.account_type, b.code_number
            ORDER BY FIELD(b.account_type, 'asset', 'liability', 'equity', 'income', 'expenses'), b.account_name
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
                        b.code_number 
                        b.account_name, 
                        SUM(a.amount) as debit_amount,
                        0 as credit_amount,
                        0 depreciation_amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE 1 $where
                GROUP BY b.account_name, b.account_type, b.code_number
                ORDER BY FIELD(b.account_type, 'asset', 'liability', 'equity', 'income', 'expenses')
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        // Para el plugin de accounts, asumimos que todo es débito
                        $tmp[] = $row;
                    }
                }
            }
        }
        // Integrating Accounts Plugin - END
        
        return $tmp;
    }

    public function get_trial_balance_with_balances( $filters = [] )
    {
        $accounts = $this->get_trial_balance_data($filters);
        $result = [];
        
        foreach($accounts as $account) {
            $balance = 0;
            
            // Determinar si la cuenta es de naturaleza débito o crédito
            switch($account->account_type) {
                case 'asset':
                case 'expenses':
                    // Cuentas de naturaleza débito
                    $balance = $account->debit_amount - $account->credit_amount;
                    break;
                case 'liability':
                case 'equity':
                case 'income':
                    // Cuentas de naturaleza crédito
                    $balance = $account->credit_amount - $account->debit_amount;
                    break;
            }
            
            $account->balance = $balance;
            $result[] = $account;
        }
        
        return $result;
    }
    
    public function get_income_statement_data($filters = [])
    {
        $date_from = isset($filters["date_from"]) ? date("Y-m-d", $filters["date_from"]) : '1900-01-01';
        $date_to = isset($filters["date_to"]) ? date("Y-m-d", $filters["date_to"]) : '2100-01-01';
        
        $branch_condition = "";
        if (is_plugin_active("branches")) {
            $branch_id = $this->session->userdata("branch_id");
            $branch_condition = " AND a.branch_id = $branch_id";
        }
        
        $sql = "
            SELECT 
                b.account_type, 
                b.account_name, 
                b.account_map,
                b.code_number,
                SUM(a.amount) as amount,
                COUNT(a.id) as transaction_count
            FROM c19_accounting_transactions a 
            INNER JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type IN ('income', 'expenses')
            AND DATE(a.added_date) BETWEEN '$date_from' AND '$date_to'
            AND a.amount != 0
            $branch_condition
            GROUP BY b.id, b.account_name, b.account_type, b.code_number, b.account_map
            ORDER BY b.account_type, b.code_number
        ";
                
        $query = $this->db->query($sql);
        
        if (!$query) {
            $error = $this->db->error();
            return [];
        }
        
        $financial_data = [];
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $financial_data[] = $row;
            }
        }
        
        return $financial_data;
    }

    public function get_consolidated_income_statement($filters = [])
    {
        $accounts = $this->get_income_statement_data($filters);
               
        $income_total = 0;
        $expenses_total = 0;
        $consolidated_data = [];
        
        foreach ($accounts as $index => $account) {           
            $account_type = isset($account->account_type) ? strtolower(trim($account->account_type)) : '';
            $amount = isset($account->amount) ? floatval($account->amount) : 0;
            
            if ($account_type == 'income') {
                $income_total += $amount;
            } elseif ($account_type == 'expenses') {
                $expenses_total += $amount;
            }
            
            $consolidated_data[] = $account;
        }
        
        $net_income = $income_total - $expenses_total;
        
        return [
            'accounts' => $consolidated_data,
            'total_income' => $income_total,
            'total_expenses' => $expenses_total,
            'net_income' => $net_income
        ];
    }
    
    public function get_balance_sheet_data($filters = [])
    {
        $data = [];
        
        // Configurar filtros de fecha
        $where = '';
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(a.added_date) >= '$date_from'";
        }
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(a.added_date) <= '$date_to'";
        }
        
        if(is_plugin_active("branches")) {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }

        // 1. ACTIVOS CORRIENTES (cuentas que empiezan con 11)
        $sql_activos_corrientes = "
            SELECT 
                b.id, 
                b.account_name, 
                b.account_map,
                SUM(CASE 
                    WHEN a.movement_type = 'debit' THEN a.amount 
                    ELSE -a.amount 
                END) as amount,
                0 as depreciation_amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'asset' 
            AND b.account_map LIKE '11%' 
            $where
            GROUP BY b.id, b.account_name, b.account_map
            HAVING ABS(amount) > 0.01
            ORDER BY b.account_map
        ";
        
        $query = $this->db->query($sql_activos_corrientes);
        $activos_corrientes = [];
        $total_activos_corrientes = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $activos_corrientes[] = $row;
                $total_activos_corrientes += $row->amount;
            }
        }

        // 2. ACTIVOS NO CORRIENTES (cuentas que empiezan con 12)
        $sql_activos_no_corrientes = "
            SELECT 
                b.id, 
                b.account_name, 
                b.account_map,
                SUM(CASE 
                    WHEN a.movement_type = 'debit' THEN a.amount 
                    ELSE -a.amount 
                END) as amount,
                COALESCE((
                    SELECT SUM(at2.depreciate_amount) 
                    FROM c19_accounting_transactions at2 
                    WHERE at2.account_id = b.id 
                    AND at2.depreciate_amount != 0
                    $where
                ), 0) as depreciation_amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'asset' 
            AND b.account_map LIKE '12%' 
            $where
            GROUP BY b.id, b.account_name, b.account_map
            HAVING ABS(amount) > 0.01
            ORDER BY b.account_map
        ";
        
        $query = $this->db->query($sql_activos_no_corrientes);
        $activos_no_corrientes = [];
        $total_activos_no_corrientes = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $activos_no_corrientes[] = $row;
                $net_amount = $row->amount - $row->depreciation_amount;
                $total_activos_no_corrientes += $net_amount;
            }
        }

        // 3. PASIVOS (todas las cuentas de liability)
        $sql_pasivos = "
            SELECT 
                b.id, 
                b.account_name, 
                b.account_map,
                SUM(CASE 
                    WHEN a.movement_type = 'credit' THEN a.amount 
                    ELSE -a.amount 
                END) as amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'liability' 
            $where
            GROUP BY b.id, b.account_name, b.account_map
            HAVING ABS(amount) > 0.01
            ORDER BY b.account_map
        ";
        
        $query = $this->db->query($sql_pasivos);
        $pasivos = [];
        $total_pasivos = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pasivos[] = $row;
                $total_pasivos += $row->amount;
            }
        }

        // 4. PATRIMONIO (todas las cuentas de equity - SOLO LAS QUE EXISTEN EN LA BD)
        $sql_patrimonio = "
            SELECT 
                b.id, 
                b.account_name, 
                b.account_map,
                SUM(CASE 
                    WHEN a.movement_type = 'credit' THEN a.amount 
                    ELSE -a.amount 
                END) as amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'equity' 
            $where
            GROUP BY b.id, b.account_name, b.account_map
            HAVING ABS(amount) > 0.01
            ORDER BY b.account_map
        ";
        
        $query = $this->db->query($sql_patrimonio);
        $patrimonio = [];
        $total_patrimonio = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $patrimonio[] = $row;
                $total_patrimonio += $row->amount;
            }
        }

        // 5. CALCULAR TOTALES FINALES (SOLO CON DATOS REALES DE LA BD)
        $total_activos = $total_activos_corrientes + $total_activos_no_corrientes;
        $total_pasivos_patrimonio = $total_pasivos + $total_patrimonio;
        
        // Verificar si el balance cuadra (con tolerancia para decimales)
        $diferencia = abs($total_activos - $total_pasivos_patrimonio);
        $balance_cuadra = ($diferencia < 0.01);

        // 6. PREPARAR DATOS PARA LA VISTA
        $data['activos_corrientes'] = $activos_corrientes;
        $data['total_activos_corrientes'] = $total_activos_corrientes;
        
        $data['activos_no_corrientes'] = $activos_no_corrientes;
        $data['total_activos_no_corrientes'] = $total_activos_no_corrientes;
        
        $data['pasivos'] = $pasivos;
        $data['total_pasivos'] = $total_pasivos;
        
        $data['patrimonio'] = $patrimonio;
        $data['total_patrimonio'] = $total_patrimonio;
        
        $data['total_activos'] = $total_activos;
        $data['total_pasivos_patrimonio'] = $total_pasivos_patrimonio;
        $data['balance_cuadra'] = $balance_cuadra;
        $data['diferencia'] = $total_activos - $total_pasivos_patrimonio;

        return $data;
    }

    public function get_income_expenses_for_balance_sheet($filters = [])
    {
        $where = '';
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(a.added_date) >= '$date_from'";
        }
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(a.added_date) <= '$date_to'";
        }
        
        if(is_plugin_active("branches")) {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }

        $sql_income = "
            SELECT SUM(CASE WHEN a.movement_type = 'credit' THEN a.amount ELSE -a.amount END) as total_income
            FROM c19_accounting_transactions a 
            INNER JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'income'
            $where
        ";
        
        $query_income = $this->db->query($sql_income);
        $total_income = $query_income->row() ? floatval($query_income->row()->total_income) : 0;
        
        $sql_expenses = "
            SELECT SUM(CASE WHEN a.movement_type = 'debit' THEN a.amount ELSE -a.amount END) as total_expenses
            FROM c19_accounting_transactions a 
            INNER JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = 'expenses'
            $where
        ";
        
        $query_expenses = $this->db->query($sql_expenses);
        $total_expenses = $query_expenses->row() ? floatval($query_expenses->row()->total_expenses) : 0;
        
        $net_income = $total_income - $total_expenses;
        
        return [
            'total_income' => $total_income,
            'total_expenses' => $total_expenses,
            'net_income' => $net_income
        ];
    }
    
    public function get_cash_flow_data($filters = [])
    {
        log_message('debug', '=== INICIANDO GET_CASH_FLOW_DATA ===');
        
        $where = '';
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(at.added_date) >= '$date_from'";
            log_message('debug', 'Date from SQL: ' . $date_from);
        }
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(at.added_date) <= '$date_to'";
            log_message('debug', 'Date to SQL: ' . $date_to);
        }
        
        if(is_plugin_active("branches")) {
            $where .= " AND at.branch_id = " . $this->session->userdata("branch_id");
            log_message('debug', 'Branch condition added');
        }
        
        $sql = "
            SELECT 
                aa.code_number,
                aa.account_name,
                aa.account_type,
                at.amount,
                at.movement_type,
                at.added_date,
                at.description
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE 1=1 $where
            ORDER BY aa.account_type, aa.code_number, at.added_date
        ";
        
        log_message('debug', 'SQL: ' . $sql);
        
        $query = $this->db->query($sql);
        log_message('debug', 'Query executed, num rows: ' . $query->num_rows());
        
        $cash_flow_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $cash_flow_data[] = $row;
            }
        }
        
        log_message('debug', '=== FINALIZANDO GET_CASH_FLOW_DATA ===');
        return $cash_flow_data;
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
    // FUNCIONES PARA COMPROBANTES CONTABLES (VOUCHERS)

    function get_all_accounts()
    {
        $this->db->order_by('account_type, code_number');
        return $this->db->get('c19_accounting_accounts');
    }

    function get_next_voucher_id()
    {
        $this->db->select_max('id');
        $result = $this->db->get('c19_accounting_vouchers')->row();
        
        if ($result && isset($result->id)) 
        {
            return $result->id + 1;
        } else {
            return;
        }
    }

    function get_voucher($voucher_id)
    {
        $this->db->where('id', $voucher_id);
        return $this->db->get('c19_accounting_vouchers')->row();
    }

    function get_voucher_details($voucher_id)
    {
        $this->db->select('at.*, aa.account_name, aa.code_number');
        $this->db->from('c19_accounting_transactions at');
        $this->db->join('c19_accounting_accounts aa', 'aa.id = at.account_id');
        $this->db->where('at.voucher_id', $voucher_id);
        $this->db->order_by('at.transaction_order', 'asc');
        
        return $this->db->get()->result();
    }
}