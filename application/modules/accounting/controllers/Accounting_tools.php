<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounting_tools extends MX_Controller {

    private $admin_id = 1; 
    private $default_branch_id = 1; 
    
    // Cache para evitar duplicados en la misma ejecución
    private $cache_ids_procesados = [];

    public function __construct() {
        parent::__construct();
        
        if (!$this->input->is_cli_request()) {
            die("ERROR: Acceso denegado. Solo CLI.");
        }

        echo "1. Iniciando Herramienta (Recuperación de Hora Real + Ordenamiento Estricto)...\n";
        $this->load->database();
        $this->load->model('Customer'); 
        
        $this->_configurar_admin();
    }

    private function _configurar_admin() {
        $sql = "SELECT person_id, branch_ids FROM c19_employees WHERE deleted = 0 AND person_id = 1 LIMIT 1";
        $admin = $this->db->query($sql)->row();
        
        if (!$admin) {
            $admin = $this->db->query("SELECT person_id, branch_ids FROM c19_employees WHERE deleted = 0 LIMIT 1")->row();
        }

        if ($admin) {
            $this->admin_id = $admin->person_id;
            $b_ids = ($admin->branch_ids) ? explode(',', $admin->branch_ids) : [];
            $this->default_branch_id = (count($b_ids) > 0 && $b_ids[0] > 0) ? $b_ids[0] : 1;
            echo "2. Admin Configurado: ID {$this->admin_id} | Sucursal Default: {$this->default_branch_id}\n";
        }
    }

    // =========================================================================
    // COMANDO PRINCIPAL
    // =========================================================================
    public function pobla_conta($fecha_inicio_str = null, $fecha_fin_str = null) {
        ini_set('memory_limit', '2048M');
        set_time_limit(0); 

        if (!$fecha_inicio_str || !$fecha_fin_str) {
            echo "ERROR: Faltan fechas (YYYY-MM-DD)\n"; return;
        }

        $ts_inicio = strtotime($fecha_inicio_str . ' 00:00:00');
        $ts_fin    = strtotime($fecha_fin_str . ' 23:59:59');

        echo "=== RECOPILANDO DATOS ($fecha_inicio_str al $fecha_fin_str) ===\n";
        $this->cache_ids_procesados = [];

        $timeline = [];

        // 1. CARGAR PRÉSTAMOS
        $this->db->select('*')->from('c19_loans')
                 ->where('loan_approved_date >=', $ts_inicio)
                 ->where('loan_approved_date <=', $ts_fin);
        $prestamos = $this->db->get()->result_array();
        
        foreach ($prestamos as $p) {
            // Recuperar fecha/hora real
            $fecha_full = $this->_obtener_fecha_real_con_hora($p, 'loan_approved_date', null); 
            $ts_evento = strtotime($fecha_full);

            $timeline[] = [
                'tipo'      => 'prestamo', 
                'timestamp' => $ts_evento,
                'fecha_txt' => $fecha_full,
                'id_ref'    => 'LOAN-' . $p['loan_id'], 
                'data'      => $p
            ];
        }

        // 2. CARGAR PAGOS
        $this->db->select('*')->from('c19_loan_payments')
                 ->where('date_paid >=', $ts_inicio)
                 ->where('date_paid <=', $ts_fin);
        $pagos = $this->db->get()->result_array();

        foreach ($pagos as $p) {
            // Recuperar fecha/hora real (usando date_modified como fallback si date_paid es 00:00)
            $fecha_full = $this->_obtener_fecha_real_con_hora($p, 'date_paid', 'date_modified'); 
            $ts_evento = strtotime($fecha_full);

            $timeline[] = [
                'tipo'      => 'pago',
                'timestamp' => $ts_evento,
                'fecha_txt' => $fecha_full,
                'id_ref'    => 'PAGO-' . $p['loan_id'] . '-' . $p['loan_payment_id'], 
                'data'      => $p
            ];
        }

        echo "   -> Total eventos encontrados: " . count($timeline) . "\n";

        // 3. ORDENAMIENTO ESTRICTO (DÍA > TIPO > HORA)
        echo "=== ORDENANDO (Día -> Desembolsos -> Pagos) ===\n";
        
        usort($timeline, function($a, $b) {
            // 1. Comparar solo el DÍA (Ymd)
            $dia_a = date('Ymd', $a['timestamp']);
            $dia_b = date('Ymd', $b['timestamp']);

            if ($dia_a != $dia_b) {
                return ($dia_a < $dia_b) ? -1 : 1;
            }
            
            // 2. Si es el MISMO DÍA: Desembolso (0) gana a Pago (1)
            $peso_a = ($a['tipo'] === 'prestamo') ? 0 : 1;
            $peso_b = ($b['tipo'] === 'prestamo') ? 0 : 1;
            
            if ($peso_a != $peso_b) {
                return ($peso_a < $peso_b) ? -1 : 1;
            }
            
            // 3. Si es Mismo Día y Mismo Tipo: Ordenar por Hora
            if ($a['timestamp'] != $b['timestamp']) {
                return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
            }

            return 0;
        });

        // 4. PROCESAR
        echo "=== INICIANDO PROCESAMIENTO ===\n";
        
        $ok = 0; $err = 0; $skip = 0;

        foreach ($timeline as $evento) {
            $tipo = $evento['tipo'];
            $data = $evento['data'];
            $id_ref = $evento['id_ref'];
            $fecha_real = $evento['fecha_txt']; // Usamos la fecha corregida con hora

            if (in_array($id_ref, $this->cache_ids_procesados)) continue;

            if ($this->_ya_existe_en_bd($id_ref)) {
                $skip++;
                $this->cache_ids_procesados[] = $id_ref;
                continue;
            }

            $resultado = false;

            if ($tipo === 'prestamo') {
                $resultado = $this->_crear_voucher_prestamo($data, $id_ref, $fecha_real);
                if ($resultado) echo "OK: Desembolso #{$data['loan_id']} [$fecha_real]\n";
                else echo "ERROR: Desembolso #{$data['loan_id']}\n";

            } elseif ($tipo === 'pago') {
                $resultado = $this->_crear_voucher_pago($data, $id_ref, $fecha_real);
                if ($resultado) echo "OK: Pago ID {$data['loan_payment_id']} [$fecha_real]\n";
                else echo "ERROR: Pago ID {$data['loan_payment_id']}\n";
            }

            if ($resultado) {
                $ok++;
                $this->cache_ids_procesados[] = $id_ref;
            } else {
                $err++;
            }
        }

        echo "\n=== RESULTADO ===\n";
        echo "Creados: $ok | Errores: $err | Ya existían: $skip\n";
    }

    // =========================================================================
    // LÓGICA DE RECUPERACIÓN DE FECHA (CORAZÓN DEL ARREGLO)
    // =========================================================================

    private function _obtener_fecha_real_con_hora($row, $col_principal, $col_fallback = null) {
        $val_principal = isset($row[$col_principal]) ? $row[$col_principal] : null;
        
        // Convertir a Timestamp si es string
        $ts_p = is_numeric($val_principal) ? $val_principal : strtotime($val_principal);
        
        // Verificar si tiene hora (es decir, si NO es medianoche exacta 00:00:00)
        // Ojo: Si la transacción fue realmente a medianoche, esto la moverá, pero es un caso raro.
        $tiene_hora = (date('H:i:s', $ts_p) !== '00:00:00');

        if ($tiene_hora) {
            return date('Y-m-d H:i:s', $ts_p);
        }

        // Si es 00:00:00, intentamos usar el fallback (date_modified)
        if ($col_fallback && isset($row[$col_fallback]) && !empty($row[$col_fallback])) {
            $val_fallback = $row[$col_fallback];
            $ts_f = is_numeric($val_fallback) ? $val_fallback : strtotime($val_fallback);
            
            // Solo usar fallback si es el MISMO DÍA (para no falsear la fecha contable)
            if (date('Y-m-d', $ts_p) === date('Y-m-d', $ts_f)) {
                // Usamos la fecha original + la hora del fallback
                return date('Y-m-d H:i:s', $ts_f);
            }
        }

        // Si no hay fallback válido, retornamos la original (ni modo, será 00:00:00)
        return date('Y-m-d H:i:s', $ts_p);
    }

    // =========================================================================
    // LÓGICA DE NEGOCIO (VOUCHERS)
    // =========================================================================

    private function _crear_voucher_prestamo($loan_data, $invoice_ref, $fecha_op) {
        $loan_id = $loan_data['loan_id'];
        $amount  = floatval($loan_data['apply_amount']);
        if ($amount <= 0) return false;

        $branch_id = (isset($loan_data['branch_id']) && $loan_data['branch_id'] > 0) 
                     ? $loan_data['branch_id'] : $this->default_branch_id;

        $customer_name = $this->_get_customer_name($loan_data['customer_id']);
        $descripcion = "Desembolso de préstamo #" . $loan_id . " - Cliente: " . $customer_name;

        $this->db->trans_start();

        // Voucher
        $voucher_data = [
            'voucher_date' => $fecha_op, // Fecha con Hora Real
            'voucher_type' => 'egreso',
            'description'  => $descripcion,
            'total_debit'  => $amount,
            'total_credit' => $amount,
            'added_by'     => $this->admin_id,
            'added_date'   => $fecha_op, 
            'branch_id'    => $branch_id
        ];
        
        if (!$this->db->insert('c19_accounting_vouchers', $voucher_data)) {
            $this->db->trans_rollback(); return false;
        }
        $voucher_id = $this->db->insert_id();

        // Asientos
        $entries = [
            ['acc' => 58, 'deb' => $amount, 'cre' => 0, 'desc' => 'Préstamo por cobrar', 'type' => 'asset', 'ord' => 0],
            ['acc' => 5,  'deb' => 0, 'cre' => $amount, 'desc' => 'Desembolso en caja',  'type' => 'asset', 'ord' => 1]
        ];

        foreach ($entries as $e) {
            $t_data = [
                'account_id'       => $e['acc'],
                'amount'           => ($e['deb'] > 0) ? $e['deb'] : $e['cre'],
                'description'      => $descripcion . ' - ' . $e['desc'],
                'added_date'       => $fecha_op,
                'added_by'         => $this->admin_id,
                'transaction_type' => $e['type'],
                'movement_type'    => ($e['deb'] > 0) ? 'debit' : 'credit',
                'voucher_id'       => $voucher_id,
                'payment_methods'  => 'efectivo',
                'invoice_number'   => $invoice_ref, 
                'purchased_date'   => $fecha_op,
                'transaction_order'=> $e['ord'],
                'branch_id'        => $branch_id
            ];
            
            if (!$this->db->insert('c19_accounting_transactions', $t_data)) {
                $this->db->trans_rollback(); return false;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function _crear_voucher_pago($payment_data, $invoice_ref, $fecha_op) {
        $loan_id = $payment_data['loan_id'];
        $branch_id = (isset($payment_data['branch_id']) && $payment_data['branch_id'] > 0) 
                     ? $payment_data['branch_id'] : $this->default_branch_id;

        $loan_info = $this->db->where('loan_id', $loan_id)->get('c19_loans')->row();
        if (!$loan_info) return false;

        $amount = floatval($payment_data['paid_amount']); 
        
        // Lógica de Cuota
        $installment_number = 0;
        $interest = 0;
        $json_objects = json_decode($loan_info->periodic_loan_table);

        if (!empty($json_objects)) {
            $fecha_target_raw = isset($payment_data['payment_due']) && $payment_data['payment_due'] > 0 
                                ? $payment_data['payment_due'] : $payment_data['date_paid'];
            $fecha_target_ymd = is_numeric($fecha_target_raw) ? date('Y-m-d', $fecha_target_raw) : date('Y-m-d', strtotime($fecha_target_raw));

            $num = 0;
            foreach($json_objects as $obj) {
                $num++;
                $fecha_json_ymd = "N/A";
                if (isset($obj->payment_date)) {
                    $temp_date = str_replace('/', '-', $obj->payment_date); 
                    $fecha_json_ymd = date('Y-m-d', strtotime($temp_date));
                }
                if ($fecha_json_ymd == $fecha_target_ymd) {
                    $installment_number = $num; $interest = floatval($obj->interest); break;
                }
                if (substr($fecha_json_ymd, 0, 7) == substr($fecha_target_ymd, 0, 7)) {
                    $installment_number = $num; $interest = floatval($obj->interest);
                }
            }
        }
        if ($installment_number == 0) $interest = 0;

        // Cálculos
        $custom_round = function($n) {
            $n = floatval($n); $p = explode('.', strval($n));
            if (count($p) === 2 && strlen($p[1]) > 2 && intval(substr($p[1], 2, 1)) >= 5) return round($n + 0.001, 2);
            return round($n, 2);
        };

        $interest_cobrado = ($amount < $interest) ? $amount : $interest;
        $iva = $custom_round($interest_cobrado * 0.13);
        $int_neto = $custom_round($interest_cobrado * 0.87); 
        $it = $custom_round(($iva + $int_neto) * 0.03);
        $capital = $custom_round($amount - $iva - $int_neto);
        if ($capital < 0) $capital = 0;
        $caja = $custom_round($capital + $iva + $int_neto);
        $total = $custom_round($caja + $it);

        $customer_name = $this->_get_customer_name($payment_data['customer_id']);
        $descripcion = "Pago de préstamo #$loan_id - Cliente: $customer_name - Cuota N° $installment_number";
        $metodo = isset($payment_data['payment_type']) ? $payment_data['payment_type'] : 'Efectivo';

        $this->db->trans_start();

        $voucher_data = [
            'voucher_date' => $fecha_op, // Fecha con Hora Real
            'voucher_type' => 'ingreso',
            'description'  => $descripcion,
            'total_debit'  => $total,
            'total_credit' => $total,
            'added_by'     => $this->admin_id,
            'added_date'   => $fecha_op,
            'branch_id'    => $branch_id
        ];
        
        if (!$this->db->insert('c19_accounting_vouchers', $voucher_data)) {
            $this->db->trans_rollback(); return false;
        }
        $voucher_id = $this->db->insert_id();

        $entries = [
            ['acc'=>348, 'deb'=>$it, 'cre'=>0, 'desc'=>'IT', 'type'=>'expenses', 'ord'=>0],
            ['acc'=>5,   'deb'=>$caja, 'cre'=>0, 'desc'=>'Caja MN', 'type'=>'asset', 'ord'=>1],
            ['acc'=>58,  'deb'=>0, 'cre'=>$capital, 'desc'=>'Capital', 'type'=>'asset', 'ord'=>2],
            ['acc'=>375, 'deb'=>0, 'cre'=>$int_neto, 'desc'=>'Int. Amortizables', 'type'=>'income', 'ord'=>3],
            ['acc'=>84,  'deb'=>0, 'cre'=>$iva, 'desc'=>'IVA', 'type'=>'liability', 'ord'=>4],
            ['acc'=>85,  'deb'=>0, 'cre'=>$it, 'desc'=>'IT', 'type'=>'liability', 'ord'=>5]
        ];

        foreach ($entries as $e) {
            if ($e['deb'] > 0 || $e['cre'] > 0) {
                $t_data = [
                    'account_id'       => $e['acc'],
                    'amount'           => ($e['deb'] > 0) ? $e['deb'] : $e['cre'],
                    'description'      => $descripcion . ' - ' . $e['desc'],
                    'added_date'       => $fecha_op,
                    'added_by'         => $this->admin_id,
                    'transaction_type' => $e['type'],
                    'movement_type'    => ($e['deb'] > 0) ? 'debit' : 'credit',
                    'voucher_id'       => $voucher_id,
                    'payment_methods'  => $metodo,
                    'invoice_number'   => $invoice_ref, 
                    'purchased_date'   => $fecha_op,
                    'transaction_order'=> $e['ord'],
                    'branch_id'        => $branch_id
                ];
                if (!$this->db->insert('c19_accounting_transactions', $t_data)) {
                    $this->db->trans_rollback(); return false;
                }
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function _ya_existe_en_bd($invoice_ref) {
        $this->db->where('invoice_number', $invoice_ref);
        return ($this->db->count_all_results('c19_accounting_transactions') > 0);
    }

    private function _get_customer_name($person_id) {
        if (!$person_id) return "Cliente General";
        static $cache_clientes = [];
        if (isset($cache_clientes[$person_id])) return $cache_clientes[$person_id];

        $p = $this->db->select('first_name, last_name')->where('person_id', $person_id)->get('c19_people')->row();
        $nombre = $p ? trim($p->first_name . " " . $p->last_name) : "Cliente #$person_id";
        $cache_clientes[$person_id] = $nombre;
        return $nombre;
    }

    public function _remap($method, $params = array()) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }
    }
}