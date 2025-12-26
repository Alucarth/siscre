<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Accounting extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('accounting');

        $this->load->model("accounting_model");
    }
    
    function get_next_account_code()
    {
        $account_type = $this->input->post("account_type");
        $account_subtype = $this->input->post("account_subtype");
        
        if (!$account_type || !$account_subtype) {
            $return["status"] = "ERROR";
            $return["msg"] = "Faltan parámetros requeridos";
            send($return);
            return;
        }
        
        // El código base es el subtipo (ej: "11" o "12")
        $base_code = $account_subtype;
        
        // Buscar el último código existente con este subtipo
        $this->db->like("code_number", $base_code, "after");
        $this->db->where("account_type", $account_type);
        $this->db->order_by("code_number", "DESC");
        $this->db->limit(1);
        
        $query = $this->db->get("accounting_accounts");
        
        $next_number = 1; // Empezar desde 01
        
        if ($query && $query->num_rows() > 0) {
            $last_code = $query->row()->code_number;
            
            // Extraer los últimos 2 dígitos del código existente
            $last_digits = substr($last_code, -2);
            
            // Convertir a número y sumar 1
            $next_number = intval($last_digits) + 1;
            
            // Si es menor que 10, agregar cero a la izquierda
            if ($next_number < 10) {
                $next_number = "0" . $next_number;
            }
            
            // Asegurarse de que no supere 99
            if ($next_number > 99) {
                $return["status"] = "ERROR";
                $return["msg"] = "No hay más números disponibles para este subtipo";
                send($return);
                return;
            }
        } else {
            // Si no hay códigos existentes, usar "01"
            $next_number = ($next_number < 10) ? "0" . $next_number : $next_number;
        }
        
        // Si el subtipo tiene menos de 4 dígitos, completar con ceros
        $padded_subtype = str_pad($account_subtype, 4, "0", STR_PAD_RIGHT);
        
        $full_code = $padded_subtype . $next_number;
        
        // Verificar que el código no exista ya
        $this->db->where("code_number", $full_code);
        $this->db->where("account_type", $account_type);
        $check_query = $this->db->get("accounting_accounts");
        
        if ($check_query && $check_query->num_rows() > 0) {
            // Si por alguna razón el código ya existe, buscar el siguiente disponible
            for ($i = $next_number + 1; $i <= 99; $i++) {
                $test_number = ($i < 10) ? "0" . $i : $i;
                $test_code = $padded_subtype . $test_number;
                
                $this->db->where("code_number", $test_code);
                $this->db->where("account_type", $account_type);
                $test_query = $this->db->get("accounting_accounts");
                
                if (!$test_query || $test_query->num_rows() == 0) {
                    $full_code = $test_code;
                    break;
                }
            }
        }
        
        $return["status"] = "OK";
        $return["code_number"] = $full_code;
        send($return);
    }

    // Función para obtener cuentas padre filtradas por longitud requerida
    function get_parent_accounts_by_level()
    {
        $account_type = $this->input->post("account_type");
        $required_length = $this->input->post("required_length");
        
        if (!$account_type || !$required_length) {
            $return["status"] = "ERROR";
            $return["msg"] = "Parámetros incompletos";
            send($return);
            return;
        }
        
        // Validar que la longitud sea válida (2, 4, 6 dígitos)
        if (!in_array($required_length, [2, 4, 6])) {
            $return["status"] = "ERROR";
            $return["msg"] = "Longitud de código padre no válida";
            send($return);
            return;
        }
        
        // Obtener cuentas del tipo especificado con la longitud exacta requerida
        $this->db->where("account_type", $account_type);
        $this->db->where("LENGTH(code_number)", $required_length);
        $this->db->order_by("code_number", "ASC");
        $query = $this->db->get("accounting_accounts");
        
        $accounts = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $accounts[] = [
                    'code_number' => $row->code_number,
                    'account_name' => $row->account_name
                ];
            }
        }
        
        // Si no hay cuentas y estamos buscando de 2 dígitos para activos,
        // incluir la cuenta raíz "1" convertida a "10", "11", etc.
        if (empty($accounts) && $required_length == 2 && $account_type == 'asset') {
            // Buscar cuentas de nivel 1 existentes para determinar el siguiente código
            $this->db->where("account_type", 'asset');
            $this->db->where("LENGTH(code_number)", 2);
            $this->db->order_by("code_number", "DESC");
            $level1_query = $this->db->get("accounting_accounts");
            
            $base_digit = '1';
            $next_number = 1;
            
            if ($level1_query && $level1_query->num_rows() > 0) {
                $last_code = $level1_query->row()->code_number;
                $last_number = intval(substr($last_code, -1));
                $next_number = $last_number + 1;
                
                // Solo incluir si hay espacio (1-9)
                if ($next_number <= 9) {
                    $accounts[] = [
                        'code_number' => $base_digit . $next_number,
                        'account_name' => 'Nueva cuenta de Nivel 1'
                    ];
                }
            } else {
                // Si no hay cuentas de nivel 1, sugerir "11"
                $accounts[] = [
                    'code_number' => '11',
                    'account_name' => 'Nueva cuenta de Nivel 1'
                ];
            }
        }
        
        if (empty($accounts)) {
            $return["status"] = "ERROR";
            $return["msg"] = "No hay cuentas padre disponibles con " . $required_length . " dígitos";
            send($return);
            return;
        }
        
        $return["status"] = "OK";
        $return["accounts"] = $accounts;
        send($return);
    }
    
    // Función para generar código jerárquico
    function generate_hierarchical_code()
    {
        $account_type = $this->input->post("account_type");
        $parent_code = $this->input->post("parent_code");
        $account_level = $this->input->post("account_level");
        
        if (!$account_type || !$account_level) {
            $return["status"] = "ERROR";
            $return["msg"] = "Faltan parámetros requeridos";
            send($return);
            return;
        }
        
        // NIVEL 1: Siempre 2 dígitos
        if ($account_level == 1) {
            $base_digit = '';
            switch($account_type) {
                case 'asset': $base_digit = '1'; break;
                case 'liability': $base_digit = '2'; break;
                case 'equity': $base_digit = '3'; break;
                case 'income': $base_digit = '4'; break;
                case 'expenses': $base_digit = '5'; break;
            }
            
            // Buscar el siguiente código disponible para nivel 1
            $this->db->where("account_type", $account_type);
            $this->db->where("LENGTH(code_number)", 2);
            $this->db->like("code_number", $base_digit, "after");
            $this->db->order_by("code_number", "DESC");
            $this->db->limit(1);
            
            $query = $this->db->get("accounting_accounts");
            
            if ($query && $query->num_rows() > 0) {
                $last_code = $query->row()->code_number;
                $last_number = intval(substr($last_code, -1));
                $next_number = $last_number + 1;
                
                // Verificar que no supere 9
                if ($next_number > 9) {
                    $return["status"] = "ERROR";
                    $return["msg"] = "No hay más números disponibles para nivel 1 (máximo 9 cuentas)";
                    send($return);
                    return;
                }
                
                $full_code = $base_digit . $next_number;
            } else {
                // Si no hay cuentas de nivel 1, empezar con "11", "21", "31", etc.
                $full_code = $base_digit . '1';
            }
            
            $return["status"] = "OK";
            $return["code_number"] = $full_code;
            send($return);
            return;
        }
        
        // Para niveles 2, 3 y 4: validar cuenta padre
        if (empty($parent_code)) {
            $return["status"] = "ERROR";
            $return["msg"] = "Debe seleccionar una cuenta padre para el nivel " . $account_level;
            send($return);
            return;
        }
        
        // Validar longitud del código padre según nivel
        $parent_length = strlen($parent_code);
        $expected_parent_length = 0;
        
        switch($account_level) {
            case 2: $expected_parent_length = 2; break;  // Nivel 2 requiere padre de 2 dígitos
            case 3: $expected_parent_length = 4; break;  // Nivel 3 requiere padre de 4 dígitos
            case 4: $expected_parent_length = 6; break;  // Nivel 4 requiere padre de 6 dígitos
        }
        
        if ($parent_length != $expected_parent_length) {
            $return["status"] = "ERROR";
            $return["msg"] = "La cuenta padre seleccionada debe tener " . $expected_parent_length . " dígitos para el nivel " . $account_level;
            send($return);
            return;
        }
        
        // Verificar que la cuenta padre exista y sea del tipo correcto
        $this->db->where("code_number", $parent_code);
        $this->db->where("account_type", $account_type);
        $parent_query = $this->db->get("accounting_accounts");
        
        if (!$parent_query || $parent_query->num_rows() == 0) {
            $return["status"] = "ERROR";
            $return["msg"] = "La cuenta padre seleccionada no existe";
            send($return);
            return;
        }
        
        // Usar el código padre como base
        $code_prefix = $parent_code;
        
        // Determinar longitud objetivo
        $target_length = $expected_parent_length + 2;
        
        // Buscar el siguiente número disponible
        $this->db->like("code_number", $code_prefix, "after");
        $this->db->where("account_type", $account_type);
        $this->db->where("LENGTH(code_number)", $target_length);
        $this->db->order_by("code_number", "DESC");
        $this->db->limit(1);
        
        $query = $this->db->get("accounting_accounts");
        
        $next_suffix = '01'; // Empezar desde 01
        
        if ($query && $query->num_rows() > 0) {
            $last_code = $query->row()->code_number;
            $last_suffix = substr($last_code, -2);
            $next_number = intval($last_suffix) + 1;
            
            if ($next_number > 99) {
                $return["status"] = "ERROR";
                $return["msg"] = "No hay más números disponibles para este nivel (máximo 99 subcuentas)";
                send($return);
                return;
            }
            
            $next_suffix = str_pad($next_number, 2, "0", STR_PAD_LEFT);
        }
        
        // Construir código completo
        $full_code = $code_prefix . $next_suffix;
        
        // Verificar unicidad
        $this->db->where("code_number", $full_code);
        $this->db->where("account_type", $account_type);
        $check_query = $this->db->get("accounting_accounts");
        
        if ($check_query && $check_query->num_rows() > 0) {
            // Buscar siguiente disponible
            for ($i = intval($next_suffix) + 1; $i <= 99; $i++) {
                $test_suffix = str_pad($i, 2, "0", STR_PAD_LEFT);
                $test_code = $code_prefix . $test_suffix;
                
                $this->db->where("code_number", $test_code);
                $this->db->where("account_type", $account_type);
                $test_query = $this->db->get("accounting_accounts");
                
                if (!$test_query || $test_query->num_rows() == 0) {
                    $full_code = $test_code;
                    break;
                }
            }
        }
        
        $return["status"] = "OK";
        $return["code_number"] = $full_code;
        send($return);
    }
    
    function getIndentLevel(string $code): int {
        $len = strlen(trim($code));
        
        // Niveles basados en longitud de código
        if ($len <= 2) {
            return 0; // Nivel 1: 2 dígitos
        } elseif ($len <= 4) {
            return 1; // Nivel 2: 4 dígitos
        } elseif ($len <= 6) {
            return 2; // Nivel 3: 6 dígitos
        } else {
            return 3; // Nivel 4+: 8+ dígitos
        }
    }

    function ajax()
    {
        $type = $this->input->post("type");
        switch( $type )
        {
            case 1:
                $this->_dt_accounts();
                break;
            case 2: // Delete account
                $this->_delete_account();
                break;
            case 3:
                $this->_save_account();
                break;
            case 4:
                $this->_load_account();
                break;
            case 5:
                $this->asset();
                break;
            case 6:
                $this->liability();
                break;
            case 7:
                $this->equity();
                break;
            case 8:
                $this->income();
                break;
            case 9:
                $this->expenses();
                break;
            case 10:
                $this->_dt_transactions();
                break;
            case 11: // Delete transaction
                $this->_delete_transaction();
                break;
            case 12:
                $this->_save_transaction();
                break;
            case 13:
                $this->_load_transaction();
                break;
            case 14:
                $this->asset_transaction();
                break;
            case 15:
                $this->liability_transaction();
                break;
            case 16:
                $this->equity_transaction();
                break;
            case 17:
                $this->income_transaction();
                break;
            case 18:
                $this->expenses_transaction();
                break;
            
        }
    }
    
    // function getIndentLevel(string $code): int {
    //     $len = strlen(trim($code));
    //     $digits = preg_replace('/[^0-9]/', '', $code); // Solo contar dígitos
        
    //     if (strlen($digits) <= 2) {
    //         return 0; // grupo grande (activo, pasivo, etc.)
    //     }

    //     cada 2 dígitos extra después de los primeros 2 aumenta el nivel
    //     return intdiv((strlen($digits) - 2), 2);
    // }
    
    private function _load_account()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $query = $this->db->get("accounting_accounts");
        
        $row = new stdClass();
        $row->id = '';
        $row->code_number = '';
        $row->account_name = '';
        $row->description = '';
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
        }
        
        $return['status'] = "OK";
        $return['row'] = $row;
        send($return);
    }
    
    private function _delete_account()
    {
        $id = $this->input->post("id");
        $this->db->where("id", $id);
        $this->db->delete("accounting_accounts");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _save_account()
    {
        $id = $this->input->post("id");
        $code_number = $this->input->post("code_number");
        $account_name = $this->input->post("account_name");
        $description = $this->input->post("description");
        $account_type = $this->input->post("account_type");
        $account_map = $this->input->post("account_map");
        $parent_code = $this->input->post("parent_code");
        $account_level = $this->input->post("account_level");
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        
        // Validaciones básicas
        if ( $id == '' && trim($code_number) == '' )
        {
            $return["msg"] = 'El número de código es un campo requerido!';
            $return["status"] = "ERROR";
            send($return);
        }
        
        if ( trim($account_name) == '' )
        {
            $return["msg"] = 'El nombre de la cuenta es un campo requerido!';
            $return["status"] = "ERROR";
            send($return);
        }
        
        // Validar formato del código (debe ser número y longitud válida)
                if ( $id == '' )
        {
            if (!is_numeric($code_number)) {
                $return["msg"] = 'El código debe contener solo números!';
                $return["status"] = "ERROR";
                send($return);
            }
            
            $code_length = strlen($code_number);
            // Validar que la longitud sea 2, 4, 6 u 8 dígitos (siempre par)
            if (!in_array($code_length, [2, 4, 6, 8])) {
                $return["msg"] = 'El código debe tener 2, 4, 6 u 8 dígitos exactos!';
                $return["status"] = "ERROR";
                send($return);
            }
            
            // Validar que el primer dígito coincida con el tipo de cuenta
            $first_digit = substr($code_number, 0, 1);
            $expected_digit = '';
            switch($account_type) {
                case 'asset': $expected_digit = '1'; break;
                case 'liability': $expected_digit = '2'; break;
                case 'equity': $expected_digit = '3'; break;
                case 'income': $expected_digit = '4'; break;
                case 'expenses': $expected_digit = '5'; break;
            }
            
            if ($first_digit != $expected_digit) {
                $return["msg"] = 'El primer dígito del código no coincide con el tipo de cuenta seleccionado!';
                $return["status"] = "ERROR";
                send($return);
            }
            
            // Para nivel 1 (2 dígitos), validar que el segundo dígito no sea 0
            if ($code_length == 2) {
                $second_digit = substr($code_number, 1, 1);
                if ($second_digit == '0') {
                    $return["msg"] = 'Para nivel 1, el segundo dígito no puede ser 0!';
                    $return["status"] = "ERROR";
                    send($return);
                }
            }
            
            // Verificar que el código no exista ya
            $this->db->where("code_number", $code_number);
            $this->db->where("account_type", $account_type);
            $query = $this->db->get("accounting_accounts");
            
            if ( $query && $query->num_rows() > 0 )
            {
                $return['msg'] = "El número de código ya existe. Por favor ingrese otro";
                $return["status"] = "ERROR";
                send($return);
            }
        }
        
        $data = [];
        $data["code_number"] = $code_number;
        $data["account_name"] = $account_name;
        $data["description"] = $description;
        $data["account_type"] = $account_type;
        
        // Si se proporcionó un código padre, usarlo como account_map
        if ($parent_code) {
            $data["account_map"] = $parent_code;
        } else if ($account_map != '') {
            $this->_clear_account_map($account_map);
            $data["account_map"] = $account_map;
        }
        
        if (is_plugin_active("branches"))
        {
            $data["branch_id"] = $this->session->userdata("branch_id");
        }
        
        if ( $id > 0 )
        {
            $data["modified_by"] = $user_id;
            $this->db->where("id", $id);
            $this->db->update("accounting_accounts", $data);
        }
        else
        {
            $data["added_by"] = $user_id;
            $data["added_date"] = date("Y-m-d H:i:s");
            $this->db->insert("accounting_accounts", $data);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _clear_account_map( $account_map )
    {
        $this->db->where("account_map", $account_map);
        $this->db->update("accounting_accounts", ["account_map" => ""]);
    }
    
    function code_exists($code_number, $account_type)
    {
        $sql = "SELECT * FROM c19_accounting_accounts WHERE code_number = '$code_number' AND account_type = '$account_type'";
        $query = $this->db->query($sql);
        if ( $query && $query->num_rows() > 0 )
        {
            return true;
        }
        
        return false;
    }
    
    function accounts()
    {
        $this->load->view('accounting/accounts');
    }
    
    function asset()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'asset');
        $data["tbl_assets"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/asset', $data);
    }
    
    function liability()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'liability');
        $data["tbl_liability"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/liability', $data);
    }
    
    function equity()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'equity');
        $data["tbl_equity"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/equity', $data);
    }
    
    function income()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'income');
        $data["tbl_income"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/income', $data);
    }
    
    function expenses()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'expenses');
        $data["tbl_expenses"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/expenses', $data);
    }
    
    function set_dt_accounts($datatable, $account_type)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1, 'account_type' => $account_type]);
        $datatable->ajax_url = site_url('accounting/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('code_number', false);
        $datatable->add_column('account_name', false);
        $datatable->add_column('description', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'asc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';
        $datatable->no_scroll = true;

        $datatable->table_id = "#tbl_" . $account_type;
        $datatable->add_titles('Account - ' . $account_type);
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_accounts()
    {
        $account_type = $this->input->post("account_type");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $filters = [];
        $filters["account_type"] = $account_type;
        $assets = $this->accounting_model->get_accounts($limit, $offset, $keywords, $order, $filters, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($assets->result() as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-". $account_type ."' data-id='" . $row->id . "' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "accounts", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }
            
            // Calcular nivel de sangría basado en code_number
            $indent_level = $this->getIndentLevel($row->code_number);
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["code_number"] = ucwords($row->code_number);
            $data_row["account_name"] = ucwords($row->account_name);
            $data_row["description"] = truncate_html($row->description, 250);
            $data_row["indent_level"] = $indent_level; // Nuevo campo para el nivel de sangría
            
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }

    function search()
    {
        
    }

    /*
      Gives search suggestions based on what is being searched for
     */

    function suggest()
    {
        
    }

    function get_row()
    {
        
    }

    function delete()
    {
        
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width()
    {
        return 360;
    }

    public function index()
    {
        
    }

    public function save($data_item_id = -1)
    {
        
    }

    public function view($data_item_id = -1)
    {
        
    }
    
    /**
     * Transactions
     */
    private function _load_transaction()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $query = $this->db->get("accounting_transactions");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $row->added_date = date($this->config->item('date_format'), strtotime($row->added_date));
            $row->purchased_date = date($this->config->item('date_format'), strtotime($row->purchased_date));
        }
        
        $return['status'] = "OK";
        $return['row'] = $row;
        send($return);
    }
    
    private function _delete_transaction()
    {
        $id = $this->input->post("id");
        $this->db->where("id", $id);
        $this->db->delete("accounting_transactions");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _save_transaction()
    {
        $id = $this->input->post("id");
        $account_id = $this->input->post("account_id");
        $amount = $this->input->post("amount");
        $added_date = $this->input->post("added_date");
        $purchased_date = $this->input->post("purchased_date");
        $purchased_amount = $this->input->post("purchased_amount");
        $depreciate_amount = $this->input->post("depreciate_amount");
        $description = $this->input->post("description");
        $transaction_type = $this->input->post("transaction_type");
        $payment_methods = $this->input->post("payment_methods");
        $invoice_number = $this->input->post("invoice_number");
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        
        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $added_date = date("Y-m-d H:i:s", strtotime(uk_to_isodate($added_date)));
            $purchased_date = date("Y-m-d H:i:s", strtotime(uk_to_isodate($purchased_date)));
        }
        else
        {
            $added_date = date("Y-m-d H:i:s", strtotime($added_date));
            $purchased_date = date("Y-m-d H:i:s", strtotime($purchased_date));
        }
        
        if ( $id == '' && trim($account_id) == '' )
        {
            $return["msg"] = 'El ID de cuenta es un campo requerido!';
            send($return);
        }
        
        $data = [];
        $data["account_id"] = $account_id;
        $data["amount"] = $amount;
        $data["added_date"] = $added_date;
        $data["purchased_date"] = $purchased_date;
        $data["transaction_type"] = $transaction_type;
        $data["purchased_amount"] = $purchased_amount;
        $data["depreciate_amount"] = $depreciate_amount;
        $data["description"] = $description;
        $data["payment_methods"] = $payment_methods;
        $data["invoice_number"] = $invoice_number;
        
        if (is_plugin_active("branches"))
        {
            $data["branch_id"] = $this->session->userdata("branch_id");
        }
        
        if ( $id > 0 )
        {
            $data["modified_by"] = $user_id;
            $this->db->where("id", $id);
            $this->db->update("accounting_transactions", $data);
        }
        else
        {
            $data["added_by"] = $user_id;
            $this->db->insert("accounting_transactions", $data);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    function transactions()
    {
        $this->load->view('accounting/transactions');
    }

    // Agregar después de la función transactions()
    function voucher_create()
    {
        // Cargar datos necesarios para la vista
        $data['accounts'] = $this->accounting_model->get_all_accounts()->result();
        $data['next_voucher_id'] = $this->accounting_model->get_next_voucher_id();
        
        $this->load->view('transactions/voucher_create', $data);
    }

    function voucher_save()
    {
        $voucher_date_input = $this->input->post('voucher_date');
        $voucher_description = $this->input->post('description');
        $voucher_type = $this->input->post('voucher_type');
        
        if ($this->config->item('date_format') == 'd/m/Y') {
            $voucher_date = date('Y-m-d', strtotime(uk_to_isodate($voucher_date_input)));
        } else {
            $voucher_timestamp = strtotime($voucher_date_input);
            if ($voucher_timestamp === false || $voucher_timestamp < 0) {
                $voucher_timestamp = time();
            }
            $voucher_date = date('Y-m-d', $voucher_timestamp);
        }

        $payment_methods = $this->input->post('payment_methods');

        $voucher_data = array(
            'voucher_date' => $voucher_date,
            'voucher_type' => $voucher_type,
            'description'  => $voucher_description,
            'total_debit'  => $this->input->post('total_debit'),
            'total_credit' => $this->input->post('total_credit'),
            'added_by'     => $this->Employee->get_logged_in_employee_info()->person_id,
            'added_date'   => date('Y-m-d H:i:s'),
        );

        if (is_plugin_active("branches")) {
            $voucher_data["branch_id"] = $this->session->userdata("branch_id");
        }

        $this->db->insert('c19_accounting_vouchers', $voucher_data);
        $voucher_id = $this->db->insert_id();

        $transaction_date = date('Y-m-d H:i:s');

        // Guardar transacciones
        $accounts            = $this->input->post('accounts');
        $debits              = $this->input->post('debits');
        $credits             = $this->input->post('credits');
        $descriptions        = $this->input->post('descriptions');
        $invoice_numbers     = $this->input->post('invoice_numbers');
        $purchased_dates     = $this->input->post('purchased_dates');
        $purchased_amounts   = $this->input->post('purchased_amounts');
        $depreciate_amounts  = $this->input->post('depreciate_amounts');

        for ($i = 0; $i < count($accounts); $i++) {
            if (!empty($accounts[$i]) && ($debits[$i] > 0 || $credits[$i] > 0)) {
                $amount        = $debits[$i] > 0 ? $debits[$i] : $credits[$i];
                $movement_type = $debits[$i] > 0 ? 'debit' : 'credit';

                $this->db->select('account_type');
                $this->db->from('c19_accounting_accounts');
                $this->db->where('id', $accounts[$i]);
                $query = $this->db->get();
                $account_row = $query->row();
                $transaction_type = $account_row ? $account_row->account_type : 'unknown';

                $purchased_date = $transaction_date;
                if (!empty($purchased_dates[$i])) {
                    if ($this->config->item('date_format') == 'd/m/Y') {
                        $purchased_date = date('Y-m-d H:i:s', strtotime(uk_to_isodate($purchased_dates[$i])));
                    } else {
                        $purchased_timestamp = strtotime($purchased_dates[$i]);
                        if ($purchased_timestamp !== false && $purchased_timestamp > 0) {
                            $purchased_date = date('Y-m-d H:i:s', $purchased_timestamp);
                        }
                    }
                }

                $transaction_description = trim($descriptions[$i]);
                if (empty($transaction_description)) {
                    $transaction_description = $voucher_description;
                }

                $transaction_data = array(
                    'account_id'        => $accounts[$i],
                    'amount'            => $amount,
                    'description'       => $transaction_description,
                    'added_date'        => $transaction_date,
                    'added_by'          => $this->Employee->get_logged_in_employee_info()->person_id,
                    'transaction_type'  => $transaction_type,
                    'movement_type'     => $movement_type,
                    'voucher_id'        => $voucher_id,
                    'payment_methods'   => $payment_methods,
                    'invoice_number'    => $invoice_numbers[$i] ?? '',
                    'purchased_date'    => $purchased_date,
                    'purchased_amount'  => $purchased_amounts[$i] ?? 0,
                    'depreciate_amount' => $depreciate_amounts[$i] ?? 0,
                    'transaction_order' => $i
                );

                if (is_plugin_active("branches")) {
                    $transaction_data["branch_id"] = $this->session->userdata("branch_id");
                }

                $this->db->insert('c19_accounting_transactions', $transaction_data);
            }
        }

        $return["status"]     = "OK";
        $return["voucher_id"] = $voucher_id;
        send($return);
    }

    function get_accounts_by_type()
    {
        $type = $this->input->post('type');
        $accounts = $this->accounting_model->get_sel_accounts($type);
        
        $options = '<option value="">Seleccionar cuenta</option>';
        foreach ($accounts as $account) {
            $options .= '<option value="' . $account->id . '">' . $account->code_number . ' - ' . $account->account_name . '</option>';
        }
        
        $return["status"] = "OK";
        $return["options"] = $options;
        send($return);
    }
    
    function asset_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'asset');
        $data["tbl_assets"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('asset');
        $this->load->view('accounting/transactions/asset', $data);
    }
    
    function liability_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'liability');
        $data["tbl_liability"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('liability');
        $this->load->view('accounting/transactions/liability', $data);
    }
    
    function equity_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'equity');
        $data["tbl_equity"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('equity');
        $this->load->view('accounting/transactions/equity', $data);
    }
    
    function income_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'income');
        $data["tbl_income"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('income');
        $this->load->view('accounting/transactions/income', $data);
    }
    
    function expenses_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'expenses');
        $data["tbl_expenses"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('expenses');
        $this->load->view('accounting/transactions/expenses', $data);
    }
    
    function set_dt_transactions($datatable, $transaction_type)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 10, 'transaction_type' => $transaction_type]);
        $datatable->ajax_url = site_url('accounting/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('account_name', false);
        switch( $transaction_type )
        {
            case 'asset':
                $datatable->add_column('added_date', false);
                $datatable->add_column('amount', false);
                $datatable->add_column('purchased_date', false);
                $datatable->add_column('purchased_amount', false);
                $datatable->add_column('depreciate_amount', false);
                $datatable->add_column('description', false);
                break;
            case 'liability':
                $datatable->add_column('amount', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'equity':
                $datatable->add_column('amount', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'income':
                $datatable->add_column('amount', false);
                $datatable->add_column('payment_methods', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'expenses':
                $datatable->add_column('amount', false);
                $datatable->add_column('payment_methods', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('invoice_number', false);
                $datatable->add_column('description', false);
                break;
        }
        
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[2, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';
        $datatable->no_scroll = true;

        $datatable->table_id = "#tbl_" . $transaction_type;
        $datatable->add_titles('transaction - ' . $transaction_type);
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_transactions()
    {
        $transaction_type = $this->input->post("transaction_type");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $filters = [];
        $filters["transaction_type"] = $transaction_type;
        $assets = $this->accounting_model->get_transactions($limit, $offset, $keywords, $order, $filters, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($assets->result() as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-".$transaction_type."' data-id='" . $row->id . "' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "transactions", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["account_name"] = ucwords($row->account_name);
            $data_row["amount"] = to_currency($row->amount);
            $data_row["payment_methods"] = $row->payment_methods;
            $data_row["invoice_number"] = $row->invoice_number;
            $data_row["added_date"] = date($this->config->item('date_format'), strtotime($row->added_date));
            $data_row["purchased_date"] = date($this->config->item('date_format'), strtotime($row->purchased_date));
            $data_row["purchased_amount"] = to_currency($row->purchased_amount);
            $data_row["depreciate_amount"] = to_currency($row->depreciate_amount);
            $data_row["description"] = truncate_html($row->description, 250);
            
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function reports()
    {
        $this->load->view('accounting/reports');
    }
    
    public function report_export()
    {        
        ini_set('memory_limit', '-1');
        
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        if ($report_type == 'balance_sheet') {
            $income_data = $this->accounting_model->get_consolidated_income_statement([
                "date_from" => $data["date_from"],
                "date_to" => $data["date_to"]
            ]);
            
            $data["total_income"] = $income_data['total_income'];
            $data["total_expenses"] = $income_data['total_expenses'];
            $data["net_income"] = $income_data['net_income'];
            $data["iue"] = $income_data['iue'];
            $data["utilidad"] = $income_data['utilidad'];
        }
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true);
        
        $timestamp = date('dmyHis');
        $filename = "{$report_type}_{$timestamp}";
        $pdfFilePath = FCPATH . "downloads/reports/{$filename}.pdf";
        
        $downloads_dir = FCPATH . "downloads/reports/";
        if (!is_dir($downloads_dir)) {
            mkdir($downloads_dir, 0755, true);
        }
        
        if (file_exists($pdfFilePath)) {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' || $report_type == 'equity_evolution')
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html);
        $pdf->Output($pdfFilePath, 'F');

        redirect(base_url("downloads/reports/{$filename}.pdf"));
    }
    
    public function report_csv()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $out = $this->load->view('accounting/reports/csv/' . $report_type, $data, true); 
        
        header("Content-type: text/csv");
        header("Content-Disposition: inline; filename=$report_type-" . date('YmdHis') . ".csv");
        header("Pragma: public");
        header("Expires: 0");
        ini_set('zlib.output_compression', '0');
        echo $out;
    }
    
    private function _load_report_data( $report_type )
    {
        $filters = [];
        
        if ($this->config->item('date_format') == 'd/m/Y') {
            $filters["date_from"] = strtotime(uk_to_isodate($this->input->post('date_from')));
            $filters["date_to"] = strtotime(uk_to_isodate($this->input->post('date_to')));
        } else {
            $filters["date_from"] = strtotime($this->input->post('date_from'));
            $filters["date_to"] = strtotime($this->input->post('date_to'));
        }
        
        // Asegurarnos de que las fechas sean válidas
        if ($filters["date_from"] === false) {
            $filters["date_from"] = strtotime('-1 month');
        }
        if ($filters["date_to"] === false) {
            $filters["date_to"] = time();
        }
        
        $data = [];
        switch( $report_type )
        {
            case 'trial_balance':
                $data["accounts"] = $this->accounting_model->get_trial_balance_data($filters);
                break;
            case 'income_statement':
                $income_data = $this->accounting_model->get_consolidated_income_statement($filters);
                $data["accounts"] = $income_data['accounts'];
                $data["total_income"] = $income_data['total_income'];
                $data["total_expenses"] = $income_data['total_expenses'];
                $data["net_income"] = $income_data['net_income'];
                $data["iue"] = $income_data['iue'];
                $data["utilidad"] = $income_data['utilidad'];
                break; 
            case 'balance_sheet':
                $data = $this->accounting_model->get_balance_sheet_data($filters);
                $data["interest_on_current"] = $this->accounting_model->get_interest_on_current($filters);
                break;
            case 'general_ledger':
                $data["accounts"] = $this->accounting_model->get_general_ledger_data($filters);
                break;
            case 'profit_loss':
                $data["accounts"] = $this->accounting_model->get_profit_loss_data($filters);
                break;
            case 'cash_flow':
                $data["accounts"] = $this->accounting_model->get_cash_flow_consolidated($filters);
                break;
            case 'equity_evolution':
                $data["accounts"] = $this->accounting_model->get_equity_evolution_data($filters);
                break;
            case 'aged_receivables':
                $data["accounts"] = $this->accounting_model->get_aged_receivables_data($filters);   
                break;
            case 'aged_payables':
                $data["accounts"] = $this->accounting_model->get_aged_payables_data($filters);
                break;
            case 'transaction_detail':
                $data["accounts"] = $this->accounting_model->get_transaction_detail_data($filters);
                break;
            case 'transaction_list':
                $data["accounts"] = $this->accounting_model->get_transaction_list_data($filters);
                break;
            case 'account_list':
                $data["accounts"] = $this->accounting_model->get_account_list_data($filters);
                break;
            case 'account_balance':
                $data["accounts"] = $this->accounting_model->get_account_balance_data($filters);
                break;
            case 'account_transaction':
                $data["accounts"] = $this->accounting_model->get_account_transaction_data($filters);
                break;
            case 'account_reconciliation':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_data($filters);
                break;
            case 'account_reconciliation_detail':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_detail_data($filters);
                break;
            case 'account_reconciliation_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_summary_data($filters);
                break;
            case 'account_reconciliation_report':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_report_data($filters);
                break;
            case 'account_reconciliation_detail_report':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_detail_report_data($filters);
                break;
            case 'account_reconciliation_summary_report':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_summary_report_data($filters);
                break;
            case 'account_reconciliation_detail_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_detail_summary_data($filters);
                break;
            case 'account_reconciliation_summary_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_summary_summary_data($filters);
                break;
            case 'account_reconciliation_report_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_report_summary_data($filters);
                break;
            case 'account_reconciliation_detail_report_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_detail_report_summary_data($filters);
                break;
            case 'account_reconciliation_summary_report_summary':
                $data["accounts"] = $this->accounting_model->get_account_reconciliation_summary_report_summary_data($filters);
                break;
        }
        
        return $data;
    }
    
    public function report_print()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        if ($report_type == 'cash_flow') {
            $this->load->view('reports/cash_flow', $data);
        } else {
            $this->load->view('accounting/reports/' . $report_type, $data);
        }
    }
    
    public function report_view()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $this->load->view('accounting/reports/' . $report_type, $data);
    }
    
    public function report_download()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_email()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_save()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_share()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_print_view()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $this->load->view('accounting/reports/' . $report_type, $data);
    }
    
    public function report_print_download()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_print_email()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_print_save()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_print_share()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
}