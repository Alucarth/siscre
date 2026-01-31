<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Accounting_model extends CI_Model
{
    function get_accounts($limit, $offset, $keywords, $order, $filters, &$count_all)
    {
        $this->db->select('*');
        $this->db->from('c19_accounting_accounts');
        
        if (!empty($filters["account_type"])) {
            $this->db->where('account_type', $filters["account_type"]);
        }
        
        $this->db->order_by('LENGTH(code_number)', 'ASC');
        $this->db->order_by('code_number', 'ASC');
        
        $count_all = $this->db->count_all_results('', FALSE);
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query;
    }
    
    function diagnose_duplicate_issue()
    {
        // Buscar cuentas duplicadas exactas
        $sql = "SELECT code_number, account_name, COUNT(*) as duplicates 
                FROM c19_accounting_accounts 
                WHERE deleted = 0 
                GROUP BY code_number, account_name 
                HAVING duplicates > 1 
                ORDER BY duplicates DESC";
        
        $query = $this->db->query($sql);
        
        if ($query->num_rows() > 0) {
            error_log("=== DIAGNÓSTICO DE DUPLICADOS ===");
            foreach ($query->result() as $row) {
                error_log("Código: {$row->code_number}, Nombre: {$row->account_name}, Duplicados: {$row->duplicates}");
            }
            
            return $query->result();
        }
        
        return [];
    }

    function get_sel_accounts($account_type)
    {
        $this->db->where("account_type", $account_type);
        
        // if(is_plugin_active("branches"))
        // {
        //     $this->db->where("branch_id", $this->session->userdata("branch_id"));
        // }
        
        // Ordenar por estructura jerárquica
        $this->db->order_by('LENGTH(code_number)', 'ASC');
        $this->db->order_by('code_number', 'ASC');
        
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
            SELECT  b.id,
                    b.account_type,
                    b.code_number, 
                    b.account_name, 
                    SUM(CASE WHEN a.movement_type = 'debit' THEN a.amount ELSE 0 END) as debit_amount,
                    SUM(CASE WHEN a.movement_type = 'credit' THEN a.amount ELSE 0 END) as credit_amount,
                    SUM(a.depreciate_amount) depreciation_amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE 1 $where
            GROUP BY b.id, b.account_name, b.account_type, b.code_number
            ORDER BY FIELD(b.account_type, 'asset', 'liability', 'equity', 'income', 'expenses'), 
                    LENGTH(b.code_number) ASC,
                    b.code_number ASC
            ";
        
        $query = $this->db->query( $sql );
        
        $tmp = [];
        $cuentas_procesadas = []; // Para evitar duplicados
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach( $query->result() as $row )
            {
                // Usar el ID de cuenta como clave única
                if (!isset($cuentas_procesadas[$row->id])) {
                    $tmp[] = $row;
                    $cuentas_procesadas[$row->id] = true;
                }
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
                SELECT  b.id,
                        b.account_type,
                        b.code_number,
                        b.account_name, 
                        SUM(a.amount) as debit_amount,
                        0 as credit_amount,
                        0 depreciation_amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE 1 $where
                GROUP BY b.id, b.account_name, b.account_type, b.code_number
                ORDER BY FIELD(b.account_type, 'asset', 'liability', 'equity', 'income', 'expenses'),
                        LENGTH(b.code_number) ASC,
                        b.code_number ASC
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        // Verificar si ya existe esta cuenta en los resultados principales
                        $existe = false;
                        foreach($tmp as $cuenta_existente) {
                            if ($cuenta_existente->code_number == $row->code_number && 
                                $cuenta_existente->account_name == $row->account_name) {
                                // Sumar los montos si es la misma cuenta
                                $cuenta_existente->debit_amount += $row->debit_amount;
                                $existe = true;
                                break;
                            }
                        }
                        
                        if (!$existe) {
                            $tmp[] = $row;
                        }
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
    
    public function get_income_statement_data($date_from, $date_to = null)
    {
        // 1. Obtener todas las cuentas de ingresos y gastos
        $this->db->from('c19_accounting_accounts');
        $this->db->where_in('account_type', array('income', 'expenses'));
        $this->db->order_by('code_number', 'ASC');
        $all_accounts = $this->db->get()->result();

        // 2. Obtener saldos reales de transacciones (solo nivel detalle: 8 dígitos)
        $this->db->select('a.code_number, SUM(CASE WHEN t.movement_type = "debit" THEN t.amount ELSE -t.amount END) as total');
        $this->db->from('c19_accounting_transactions t');
        $this->db->join('c19_accounting_accounts a', 'a.id = t.account_id');
        $this->db->where('LENGTH(a.code_number)', 8);
        if ($date_from) $this->db->where('t.added_date >=', date('Y-m-d H:i:s', $date_from));
        if ($date_to) $this->db->where('t.added_date <=', date('Y-m-d H:i:s', $date_to));
        $this->db->group_by('a.code_number');
        $saldos_reales = $this->db->get()->result();

        $mapa_saldos = [];
        foreach($saldos_reales as $sr) { $mapa_saldos[$sr->code_number] = $sr->total; }

        // 3. Consolidar montos hacia arriba (Padres)
        $final_accounts = [];
        foreach ($all_accounts as $account) {
            $account->amount = 0;
            $prefix = (string)$account->code_number;
            foreach ($mapa_saldos as $code8 => $monto) {
                if (strpos($code8, $prefix) === 0) { $account->amount += $monto; }
            }
            if (abs($account->amount) > 0.001) {
                $account->amount = abs($account->amount);
                $final_accounts[] = $account;
            }
        }
        return $final_accounts;
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
        // 1. Configuración de filtros de fecha
        $where = " 1=1 ";
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(a.added_date) >= '$date_from'";
        }
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(a.added_date) <= '$date_to'";
        }

        // 2. SQL con Lógica de Naturaleza Contable (Debe vs Haber)
        // Activo/Gasto: Debe (+) Haber (-) | Pasivo/Patrimonio/Ingreso: Haber (+) Debe (-)
        $sql_saldos = "
            SELECT b.code_number, LOWER(b.account_type) as account_type,
                SUM(CASE 
                        WHEN LOWER(b.account_type) IN ('asset', 'expenses') THEN 
                            (CASE WHEN a.movement_type = 'debit' THEN a.amount ELSE -a.amount END)
                        ELSE 
                            (CASE WHEN a.movement_type = 'credit' THEN a.amount ELSE -a.amount END)
                END) as balance
            FROM c19_accounting_transactions a
            JOIN c19_accounting_accounts b ON a.account_id = b.id
            WHERE $where AND LENGTH(b.code_number) = 8
            GROUP BY b.code_number, b.account_type";
        
        $res_saldos = $this->db->query($sql_saldos)->result();
        
        $saldos_hoja = [];
        $total_ingresos = 0;
        $total_gastos = 0;

        foreach($res_saldos as $s) {
            $saldos_hoja[$s->code_number] = ['bal' => $s->balance, 'type' => $s->account_type];
            if($s->account_type == 'income') $total_ingresos += $s->balance;
            if($s->account_type == 'expenses') $total_gastos += $s->balance;
        }
        
        // Resultado de la gestión (Utilidad/Pérdida)
        $resultado_gestion = $total_ingresos - $total_gastos;

        // 3. Preparar estructura para la vista
        $data = [
            'activos_corrientes' => [], 'total_activos_corrientes' => 0,
            'activos_no_corrientes' => [], 'total_activos_no_corrientes' => 0,
            'pasivos_corrientes' => [], 'total_pasivos_corrientes' => 0,
            'pasivos_no_corrientes' => [], 'total_pasivos_no_corrientes' => 0,
            'patrimonio' => [], 'total_patrimonio' => 0,
            'total_activos' => 0, 'total_pasivos' => 0, 'total_pasivos_patrimonio' => 0
        ];

        // 4. Calcular Totales por Categoría (Prefijos 11, 12, 21, 22, 31...)
        foreach($saldos_hoja as $code8 => $info) {
            $monto = $info['bal'];
            $tipo = $info['type'];

            if ($tipo == 'asset') {
                if (strpos($code8, '11') === 0) $data['total_activos_corrientes'] += $monto;
                if (strpos($code8, '12') === 0) $data['total_activos_no_corrientes'] += $monto;
            } elseif ($tipo == 'liability') {
                if (strpos($code8, '21') === 0) $data['total_pasivos_corrientes'] += $monto;
                if (strpos($code8, '22') === 0) $data['total_pasivos_no_corrientes'] += $monto;
                $data['total_pasivos'] += $monto;
            } elseif ($tipo == 'equity') {
                $data['total_patrimonio'] += $monto;
            }
        }
        
        // Sumar resultado al patrimonio
        $data['total_patrimonio'] += $resultado_gestion;

        // 5. Cargar todas las cuentas y asignar saldos acumulados
        $todas_las_cuentas = $this->db->order_by('code_number', 'ASC')->get('c19_accounting_accounts')->result();

        foreach ($todas_las_cuentas as $acc) {
            $code = (string)$acc->code_number;
            $type = strtolower($acc->account_type);
            $acc->account_map = $code; // Para que la vista lo reconozca
            $acc->depreciation_amount = 0;

            $saldo_acum = 0;
            foreach ($saldos_hoja as $c8 => $info) {
                if (strpos($c8, $code) === 0) { $saldo_acum += $info['bal']; }
            }

            if (abs($saldo_acum) < 0.0001) continue;

            $acc->amount = $saldo_acum; // Mantiene el signo del SQL

            // No incluir niveles raíz (1, 2, 3 o 11, 21) en los listados de la tabla
            if (strlen($code) <= 2) continue;

            // Clasificación para los bucles foreach de la vista
            if ($type == 'asset') {
                if (strpos($code, '11') === 0) $data['activos_corrientes'][] = $acc;
                elseif (strpos($code, '12') === 0) $data['activos_no_corrientes'][] = $acc;
            } elseif ($type == 'liability') {
                if (strpos($code, '21') === 0) $data['pasivos_corrientes'][] = $acc;
                elseif (strpos($code, '22') === 0) $data['pasivos_no_corrientes'][] = $acc;
            } elseif ($type == 'equity') {
                $data['patrimonio'][] = $acc;
            }
        }
        
        // Inyectar Resultado de Gestión como objeto al final del Patrimonio
        $res_obj = new stdClass();
        $res_obj->account_map = ""; 
        $res_obj->account_name = "RESULTADO DE LA GESTIÓN";
        $res_obj->amount = $resultado_gestion;
        $data['patrimonio'][] = $res_obj;

        // Totales finales del reporte
        $data['total_activos'] = $data['total_activos_corrientes'] + $data['total_activos_no_corrientes'];
        $data['total_pasivos_patrimonio'] = $data['total_pasivos'] + $data['total_patrimonio'];

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
        
        // PASO 1: Obtener saldos iniciales con lógica de naturaleza
        $sql_saldos_iniciales = "
            SELECT aa.id as account_id, aa.code_number, aa.account_name, aa.account_type,
                SUM(CASE 
                    WHEN LOWER(aa.account_type) IN ('asset', 'expenses') THEN 
                        (CASE WHEN at.movement_type = 'debit' THEN at.amount ELSE -at.amount END)
                    ELSE 
                        (CASE WHEN at.movement_type = 'credit' THEN at.amount ELSE -at.amount END)
                END) as saldo_inicial
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE DATE(at.added_date) < '$date_from' $branch_condition
            GROUP BY aa.id, aa.code_number, aa.account_name, aa.account_type
            HAVING ABS(saldo_inicial) > 0.01";
        
        $query_inicial = $this->db->query($sql_saldos_iniciales);
        $saldos_iniciales = [];
        
        if ($query_inicial && $query_inicial->num_rows() > 0) {
            foreach ($query_inicial->result() as $row) {
                $saldos_iniciales[$row->account_id] = $row->saldo_inicial;
            }
        } else {
        }
        
        // PASO 2: Obtener transacciones y saldo final con lógica de naturaleza
        $sql_transacciones = "
            SELECT aa.id, aa.code_number, aa.account_name, aa.account_type, at.amount, at.movement_type, at.added_date, at.description, at.voucher_id, at.payment_methods,
            (SELECT SUM(CASE 
                        WHEN LOWER(aa2.account_type) IN ('asset', 'expenses') THEN 
                            (CASE WHEN at2.movement_type = 'debit' THEN at2.amount ELSE -at2.amount END)
                        ELSE 
                            (CASE WHEN at2.movement_type = 'credit' THEN at2.amount ELSE -at2.amount END)
                    END)
            FROM c19_accounting_transactions at2 
            INNER JOIN c19_accounting_accounts aa2 ON aa2.id = at2.account_id
            WHERE at2.account_id = aa.id AND DATE(at2.added_date) <= '$date_to' $branch_condition) as saldo_final
            FROM c19_accounting_transactions at
            INNER JOIN c19_accounting_accounts aa ON aa.id = at.account_id
            WHERE DATE(at.added_date) BETWEEN '$date_from' AND '$date_to' $branch_condition
            ORDER BY aa.account_type, aa.code_number, at.added_date";

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
                // Cálculo de variación individual según naturaleza
                if (LOWER($row->account_type) == 'asset' || LOWER($row->account_type) == 'expenses') {
                    $monto_variacion = ($row->movement_type == 'debit') ? $row->amount : -$row->amount;
                } else {
                    $monto_variacion = ($row->movement_type == 'credit') ? $row->amount : -$row->amount;
                }
                
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
                    return 'operating';
                }
            }
            return 'financing';
        }
        
        // Por defecto
        return 'operating';
    }

    public function get_cash_flow_with_totals($filters = [])
    {  
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
        
        $totals_by_activity = [
            'operating' => 0,
            'investing' => 0,
            'financing' => 0
        ];
        
        $accounts_summary = [];
        $processed_accounts = [];
        
        foreach ($cash_flow_data as $transaction) {
            $account_id = $transaction->id;
            
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
            
            $monto_actividad = $transaction->monto_variacion;
            $totals_by_activity[$transaction->activity_type] += $monto_actividad;
        }
        
        $net_cash_flow = $totals_by_activity['operating'] + $totals_by_activity['investing'] + $totals_by_activity['financing'];
        
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
                    b.code_number,
                    b.account_name,
                    SUM(a.amount) amount
            FROM c19_accounting_transactions a 
            LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
            WHERE b.account_type = '$account_type' $where
            GROUP BY b.id, b.code_number, b.account_name
            ORDER BY LENGTH(b.code_number) ASC, b.code_number ASC
            ";
        
        $query = $this->db->query( $sql );
        
        $account_data = [];
        $cuentas_procesadas = [];
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                // Evitar duplicados usando ID de cuenta como clave única
                if (!isset($cuentas_procesadas[$row->id])) {
                    $account_data[] = $row;
                    $cuentas_procesadas[$row->id] = true;
                }
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
                SELECT  b.id,
                        b.account_type, 
                        b.code_number,
                        b.account_name, 
                        SUM(a.amount) amount
                FROM c19_account_transactions a 
                LEFT JOIN c19_accounts b ON b.id = a.account_id
                WHERE b.account_type = '$account_type' $where
                GROUP BY b.id, b.account_name, b.code_number
                ORDER BY LENGTH(b.code_number) ASC, b.code_number ASC
                ";

            $query = $this->db->query( $sql );

            if ( $query && $query->num_rows() > 0 )
            {
                foreach( $query->result() as $row )
                {
                    if ( $row->account_type != '' )
                    {
                        // Verificar si ya existe esta cuenta en los resultados principales
                        $existe = false;
                        foreach($account_data as $cuenta_existente) {
                            if ($cuenta_existente->code_number == $row->code_number && 
                                $cuenta_existente->account_name == $row->account_name) {
                                // Sumar los montos si es la misma cuenta
                                $cuenta_existente->amount += $row->amount;
                                $existe = true;
                                break;
                            }
                        }
                        
                        if (!$existe) {
                            $account_data[] = $row;
                        }
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
        $this->db->order_by('LENGTH(code_number)', 'ASC');
        $this->db->order_by('code_number', 'ASC');
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