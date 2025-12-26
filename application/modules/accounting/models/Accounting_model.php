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
            "at.added_date", // 2 - added_date
            "at.amount", // 3 - amount
            "at.purchased_date", // 4 - purchased_date
            "at.purchased_amount", // 5 - purchased_amount
            "at.depreciate_amount", // 6 - depreciate_amount
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
        $iue = 0.25 * $net_income;
        $utilidad = $net_income - $iue;
        
        return [
            'accounts' => $consolidated_data,
            'total_income' => $income_total,
            'total_expenses' => $expenses_total,
            'net_income' => $net_income,
            'iue' => $iue,
            'utilidad' => $utilidad
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

    public function get_equity_evolution_data($filters = [])
    {        
        $data = [];
        
        // Configurar filtros de fecha
        $where = '';
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(at.added_date) >= '$date_from'";
        }
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(at.added_date) <= '$date_to'";
        }
        
        if(is_plugin_active("branches")) {
            $where .= " AND at.branch_id = " . $this->session->userdata("branch_id");
        }

        // Obtener todas las cuentas patrimoniales que tienen transacciones
        $sql_cuentas = "
            SELECT 
                aa.id,
                aa.code_number,
                aa.account_name,
                aa.account_type,
                SUM(CASE 
                    WHEN at.movement_type = 'credit' THEN at.amount 
                    WHEN at.movement_type = 'debit' THEN -at.amount 
                END) as saldo_total
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE aa.code_number LIKE '3%'
            $where
            GROUP BY aa.id, aa.code_number, aa.account_name, aa.account_type
            HAVING ABS(saldo_total) > 0.01
            ORDER BY aa.code_number
        ";
        
        $query_cuentas = $this->db->query($sql_cuentas);
        $cuentas_con_saldo = [];
        $total_general = 0;
        
        if ($query_cuentas && $query_cuentas->num_rows() > 0) {
            foreach ($query_cuentas->result() as $row) {
                $cuentas_con_saldo[] = $row;
                $total_general += $row->saldo_total;
            }
        }

        // Preparar datos para la vista
        $data['cuentas_con_saldo'] = $cuentas_con_saldo;
        $data['total_general'] = $total_general;
        
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
        // Validar filtros de fecha
        if (!isset($filters["date_from"]) || !isset($filters["date_to"])) {
            return [];
        }
        
        $date_from = date("Y-m-d", $filters["date_from"]);
        $date_to = date("Y-m-d", $filters["date_to"]);
        
        $branch_condition = "";
        if (is_plugin_active("branches")) {
            $branch_id = $this->session->userdata("branch_id");
            $branch_condition = " AND at.branch_id = $branch_id";
        }
        
        // PASO 1: Obtener saldos iniciales (antes de date_from)
        $sql_saldos_iniciales = "
            SELECT 
                aa.id as account_id,
                aa.code_number,
                aa.account_name,
                aa.account_type,
                SUM(CASE 
                    WHEN at.movement_type = 'debit' THEN at.amount 
                    ELSE -at.amount 
                END) as saldo_inicial
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE DATE(at.added_date) < '$date_from'
            $branch_condition
            GROUP BY aa.id, aa.code_number, aa.account_name, aa.account_type
            HAVING ABS(saldo_inicial) > 0.01
        ";
        
        $query_inicial = $this->db->query($sql_saldos_iniciales);
        $saldos_iniciales = [];
        
        if ($query_inicial && $query_inicial->num_rows() > 0) {
            foreach ($query_inicial->result() as $row) {
                $saldos_iniciales[$row->account_id] = $row->saldo_inicial;
            }
        } else {
        }
        
        // PASO 2: Obtener transacciones del período con saldos finales
        $sql_transacciones = "
            SELECT 
                aa.id,
                aa.code_number,
                aa.account_name,
                aa.account_type,
                at.amount,
                at.movement_type,
                at.added_date,
                at.description,
                at.voucher_id,
                at.payment_methods,
                -- Calcular saldo acumulado por cuenta
                (SELECT SUM(CASE 
                    WHEN at2.movement_type = 'debit' THEN at2.amount 
                    ELSE -at2.amount 
                END)
                FROM c19_accounting_transactions at2 
                WHERE at2.account_id = aa.id 
                AND DATE(at2.added_date) <= '$date_to'
                $branch_condition) as saldo_final
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE DATE(at.added_date) BETWEEN '$date_from' AND '$date_to'
            $branch_condition
            ORDER BY aa.account_type, aa.code_number, at.added_date
        ";
        
        $query = $this->db->query($sql_transacciones);
        
        if (!$query) {
            $error = $this->db->error();
            return [];
        }
        
        $cash_flow_data = [];
        $cuentas_procesadas = [];
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Determinar tipo de actividad
                $activity_type = $this->get_activity_type(
                    $row->code_number, 
                    $row->account_type, 
                    $row->movement_type
                );
                
                // Obtener saldo inicial
                $saldo_inicial = isset($saldos_iniciales[$row->id]) ? $saldos_iniciales[$row->id] : 0;
                
                // Calcular variación
                $monto_variacion = $row->movement_type == 'debit' ? $row->amount : -$row->amount;
                
                // Para la primera transacción de cada cuenta, calcular variación total
                if (!isset($cuentas_procesadas[$row->id])) {
                    $variacion_total = $row->saldo_final - $saldo_inicial;
                    $cuentas_procesadas[$row->id] = true;
                } else {
                    $variacion_total = 0; // Solo se muestra en la primera fila de cada cuenta
                }
                
                $cash_flow_data[] = (object) array(
                    'id' => $row->id,
                    'code_number' => $row->code_number,
                    'account_name' => $row->account_name,
                    'account_type' => $row->account_type,
                    'amount' => $row->amount,
                    'movement_type' => $row->movement_type,
                    'added_date' => $row->added_date,
                    'description' => $row->description,
                    'voucher_id' => $row->voucher_id,
                    'payment_methods' => $row->payment_methods,
                    'activity_type' => $activity_type,
                    'saldo_inicial' => $saldo_inicial,
                    'saldo_final' => $row->saldo_final,
                    'variacion' => $variacion_total,
                    'monto_variacion' => $monto_variacion,
                    'es_primera_cuenta' => !isset($cuentas_procesadas[$row->id])
                );
                
                // Marcar cuenta como procesada
                $cuentas_procesadas[$row->id] = true;
            }
        }
        
        return $cash_flow_data;
    }

    public function get_activity_type($account_code, $account_type, $movement_type)
    {
        if (empty($account_code)) {
            return 'operating';
        }
        
        $code_prefix = substr($account_code, 0, 2);
        $first_digit = substr($account_code, 0, 1);
        
        // ACTIVIDAD OPERATIVA
        if ($first_digit == '4' || $first_digit == '5' || $first_digit == '6') {
            return 'operating';
        }
        
        // Activos corrientes (11xx) - Operativa
        if ($code_prefix == '11') {
            return 'operating';
        }
        
        // ACTIVIDAD DE INVERSIÓN
        if ($first_digit == '1' && $code_prefix != '11') {
            return 'investing';
        }
        
        // ACTIVIDAD DE FINANCIAMIENTO  
        if ($first_digit == '3') {
            return 'financing';
        }
        
        // Pasivos - determinar por naturaleza
        if ($first_digit == '2') {
            // Pasivos corrientes cortos -> operativa, largos -> financiamiento
            if (strlen($account_code) >= 4) {
                $sub_code = substr($account_code, 0, 4);
                // Préstamos bancarios, deudas largo plazo -> financiamiento
                if (in_array($sub_code, ['2101', '2102', '2103', '2201', '2202'])) {
                    return 'financing';
                }
            }
            return 'operating';
        }
        
        // Por defecto
        return 'operating';
    }

    public function get_cash_flow_with_totals($filters = [])
    {
        
        // Obtener datos base
        $cash_flow_data = $this->get_cash_flow_data($filters);
        
        if (empty($cash_flow_data)) {
            return [
                'accounts' => [],
                'totals' => [
                    'operating' => 0,
                    'investing' => 0, 
                    'financing' => 0,
                    'net_cash_flow' => 0
                ],
                'summary' => []
            ];
        }
        
        // Estructuras para cálculos
        $totals_by_activity = [
            'operating' => 0,
            'investing' => 0,
            'financing' => 0
        ];
        
        $accounts_summary = [];
        $processed_accounts = [];
        
        // PASO 1: Procesar cada transacción y calcular totales        
        foreach ($cash_flow_data as $transaction) {
            $account_id = $transaction->id;
            
            // Solo procesar la primera transacción de cada cuenta para el resumen
            if (!isset($processed_accounts[$account_id]) && $transaction->es_primera_cuenta) {
                $accounts_summary[$account_id] = [
                    'code_number' => $transaction->code_number,
                    'account_name' => $transaction->account_name,
                    'account_type' => $transaction->account_type,
                    'activity_type' => $transaction->activity_type,
                    'saldo_inicial' => $transaction->saldo_inicial,
                    'saldo_final' => $transaction->saldo_final,
                    'variacion' => $transaction->variacion,
                    'clasificacion' => $this->get_variacion_clasificacion($transaction->variacion)
                ];
                $processed_accounts[$account_id] = true;
            }
            
            // Acumular por tipo de actividad (usar monto_variacion de cada transacción)
            $monto_actividad = $transaction->monto_variacion;
            $totals_by_activity[$transaction->activity_type] += $monto_actividad;
        }
        
        // PASO 2: Calcular flujo neto de efectivo
        $net_cash_flow = $totals_by_activity['operating'] + $totals_by_activity['investing'] + $totals_by_activity['financing'];
        
        // PASO 3: Preparar datos finales
        $result = [
            'accounts' => $cash_flow_data,
            'totals' => [
                'operating' => $totals_by_activity['operating'],
                'investing' => $totals_by_activity['investing'],
                'financing' => $totals_by_activity['financing'],
                'net_cash_flow' => $net_cash_flow
            ],
            'summary' => array_values($accounts_summary),
            'period' => [
                'date_from' => isset($filters["date_from"]) ? $filters["date_from"] : null,
                'date_to' => isset($filters["date_to"]) ? $filters["date_to"] : null
            ]
        ];
        
        return $result;
    }

    // Función auxiliar para clasificar la variación
    private function get_variacion_clasificacion($variacion)
    {
        if ($variacion > 0) {
            return 'positivo';
        } elseif ($variacion < 0) {
            return 'negativo';
        } else {
            return 'neutral';
        }
    }

    public function get_cash_flow_consolidated($filters = [])
    {        
        $date_from = date("Y-m-d", $filters["date_from"]);
        $date_to = date("Y-m-d", $filters["date_to"]);
        
        $branch_condition = "";
        if (is_plugin_active("branches")) {
            $branch_id = $this->session->userdata("branch_id");
            $branch_condition = " AND at.branch_id = $branch_id";
        }
        
        // Consulta consolidada por cuenta
        $sql = "
            SELECT 
                aa.id,
                aa.code_number,
                aa.account_name,
                aa.account_type,
                -- Saldo inicial (antes del período)
                (SELECT SUM(CASE 
                    WHEN at2.movement_type = 'debit' THEN at2.amount 
                    ELSE -at2.amount 
                END)
                FROM c19_accounting_transactions at2 
                WHERE at2.account_id = aa.id 
                AND DATE(at2.added_date) < '$date_from'
                $branch_condition) as saldo_inicial,
                
                -- Saldo final (hasta fin del período)
                (SELECT SUM(CASE 
                    WHEN at3.movement_type = 'debit' THEN at3.amount 
                    ELSE -at3.amount 
                END)
                FROM c19_accounting_transactions at3 
                WHERE at3.account_id = aa.id 
                AND DATE(at3.added_date) <= '$date_to'
                $branch_condition) as saldo_final,
                
                -- Movimientos del período (débitos - créditos)
                SUM(CASE 
                    WHEN at.movement_type = 'debit' THEN at.amount 
                    ELSE -at.amount 
                END) as variacion_periodo,
                
                -- Total débitos del período
                SUM(CASE WHEN at.movement_type = 'debit' THEN at.amount ELSE 0 END) as total_debitos,
                
                -- Total créditos del período  
                SUM(CASE WHEN at.movement_type = 'credit' THEN at.amount ELSE 0 END) as total_creditos,
                
                COUNT(at.id) as num_transacciones
                
            FROM c19_accounting_accounts aa
            LEFT JOIN c19_accounting_transactions at ON aa.id = at.account_id 
                AND DATE(at.added_date) BETWEEN '$date_from' AND '$date_to'
                $branch_condition
            WHERE aa.id IS NOT NULL
            GROUP BY aa.id, aa.code_number, aa.account_name, aa.account_type
            HAVING saldo_inicial IS NOT NULL OR saldo_final IS NOT NULL OR variacion_periodo IS NOT NULL
            ORDER BY aa.code_number
        ";
        
        $query = $this->db->query($sql);
        
        if (!$query) {
            $error = $this->db->error();
            return [];
        }
        
        $consolidated_data = [];
        $total_variacion = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Calcular diferencia
                $diferencia = $row->saldo_final - $row->saldo_inicial;
                
                // Determinar tipo de variación
                $tipo_variacion = 'SIN DIFERENCIA';
                if ($diferencia > 0) {
                    $tipo_variacion = 'AUMENTO';
                } elseif ($diferencia < 0) {
                    $tipo_variacion = 'DISMINUCIÓN';
                }
                
                // Determinar clasificación por actividad
                $clasificacion = $this->get_activity_type($row->code_number, $row->account_type, 'debit');
                
                // Determinar tipo de cuenta para mostrar
                $tipo_cuenta = '';
                switch($row->account_type) {
                    case 'asset': $tipo_cuenta = 'ACTIVO'; break;
                    case 'liability': $tipo_cuenta = 'PASIVO'; break;
                    case 'equity': $tipo_cuenta = 'PATRIMONIO'; break;
                    case 'income': $tipo_cuenta = 'INGRESO'; break;
                    case 'expenses': $tipo_cuenta = 'EGRESO'; break;
                    case 'Asset': $tipo_cuenta = 'ACTIVO'; break;
                    case 'Liability': $tipo_cuenta = 'PASIVO'; break;
                    case 'Equity': $tipo_cuenta = 'PATRIMONIO'; break;
                    case 'Income': $tipo_cuenta = 'INGRESO'; break;
                    case 'Expenses': $tipo_cuenta = 'EGRESO'; break;
                    default: $tipo_cuenta = strtoupper($row->account_type);
                }
                
                $consolidated_data[] = (object) [
                    'id' => $row->id,
                    'code_number' => $row->code_number,
                    'account_name' => $row->account_name,
                    'tipo_cuenta' => $tipo_cuenta,
                    'clasificacion' => $clasificacion,
                    'saldo_inicial' => $row->saldo_inicial ?: 0,
                    'saldo_final' => $row->saldo_final ?: 0,
                    'diferencia' => $diferencia,
                    'tipo_variacion' => $tipo_variacion,
                    'total_debitos' => $row->total_debitos ?: 0,
                    'total_creditos' => $row->total_creditos ?: 0,
                    'num_transacciones' => $row->num_transacciones ?: 0
                ];
                
                $total_variacion += $diferencia;
            }
        }
        
        return $consolidated_data;
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
        $this->db->order_by('code_number');
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