<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounting_tools extends MX_Controller {

    private $admin_id = 1; 
    private $default_branch_id = 1; 

    public function __construct() {
        parent::__construct();
        
        if (!$this->input->is_cli_request()) {
            die("ERROR: Acceso denegado. Solo CLI.");
        }

        echo "1. Iniciando Herramienta (Modo Cronológico Unificado)...\n";
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

    public function pobla_conta($fecha_inicio_str = null, $fecha_fin_str = null) {
        ini_set('memory_limit', '2048M');
        set_time_limit(0); 

        if (!$fecha_inicio_str || !$fecha_fin_str) {
            echo "ERROR: Faltan fechas (YYYY-MM-DD)\n"; return;
        }

        $ts_inicio = strtotime($fecha_inicio_str . ' 00:00:00');
        $ts_fin    = strtotime($fecha_fin_str . ' 23:59:59');

        echo "=== RECOPILANDO TRANSACCIONES ($fecha_inicio_str al $fecha_fin_str) ===\n";

        $timeline = [];

        $this->db->select('*')->from('c19_loans')
                 ->where('loan_approved_date >=', $ts_inicio)
                 ->where('loan_approved_date <=', $ts_fin);
        $prestamos = $this->db->get()->result_array();
        
        foreach ($prestamos as $p) {
            $fecha = is_numeric($p['loan_approved_date']) ? $p['loan_approved_date'] : strtotime($p['loan_approved_date']);
            
            $timeline[] = [
                'tipo'      => 'prestamo',
                'timestamp' => $fecha,
                'fecha_legible' => date('Y-m-d H:i:s', $fecha),
                'data'      => $p
            ];
        }
        echo "   -> " . count($prestamos) . " Préstamos encontrados.\n";

        $this->db->select('*')->from('c19_loan_payments')
                 ->where('date_paid >=', $ts_inicio)
                 ->where('date_paid <=', $ts_fin);
        $pagos = $this->db->get()->result_array();

        foreach ($pagos as $p) {
            $fecha = is_numeric($p['date_paid']) ? $p['date_paid'] : strtotime($p['date_paid']);

            $timeline[] = [
                'tipo'      => 'pago',
                'timestamp' => $fecha,
                'fecha_legible' => date('Y-m-d H:i:s', $fecha),
                'data'      => $p
            ];
        }
        echo "   -> " . count($pagos) . " Pagos encontrados.\n";

        echo "=== ORDENANDO TRANSACCIONES... ===\n";
        
        usort($timeline, function($a, $b) {
            if ($a['timestamp'] == $b['timestamp']) {
                return 0;
            }
            return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
        });

        echo "=== INICIANDO PROCESAMIENTO SECUENCIAL ===\n";
        
        $total_ok = 0;
        $total_error = 0;
        $saltados = 0;

        foreach ($timeline as $evento) {
            $tipo = $evento['tipo'];
            $data = $evento['data'];
            $fecha_log = $evento['fecha_legible'];

            if ($tipo === 'prestamo') {
                $id = $data['loan_id'];
                
                if ($this->_voucher_existe("Préstamo #$id")) {
                    $saltados++; continue;
                }

                echo "[$fecha_log] Procesando Préstamo #$id... ";
                if ($this->_crear_voucher_prestamo_local($data)) {
                    echo "OK\n"; $total_ok++;
                } else {
                    echo "ERROR\n"; $total_error++;
                }

            } elseif ($tipo === 'pago') {
                $id = $data['loan_payment_id'];
                $loan_id = $data['loan_id'];

                if ($this->_voucher_existe("Pago de préstamo #$loan_id", "Cuota")) {
                }

                echo "[$fecha_log] Procesando Pago ID $id... ";
                if ($this->_crear_voucher_pago_local($data)) {
                    echo "OK\n"; $total_ok++;
                } else {
                    echo "ERROR\n"; $total_error++;
                }
            }
        }

        echo "\n=== RESUMEN FINAL ===\n";
        echo "Procesados OK: $total_ok\n";
        echo "Errores:       $total_error\n";
        echo "Ya existían:   $saltados\n";
    }

    private function _crear_voucher_prestamo_local($loan_data) {
        try {
            $loan_id = $loan_data['loan_id'];
            $amount  = floatval($loan_data['apply_amount']);
            if ($amount <= 0) return false;

            $branch_id = (isset($loan_data['branch_id']) && $loan_data['branch_id'] > 0) 
                         ? $loan_data['branch_id'] 
                         : 1;

            $customer_name = $this->_get_customer_name($loan_data['customer_id']);
            $employee_id   = $this->admin_id; 
            
            $descripcion = "Desembolso de préstamo #" . $loan_id . " - Cliente: " . $customer_name;
            
            $fecha_raw = $loan_data['loan_approved_date'];
            $fecha_historica = is_numeric($fecha_raw) 
                               ? date('Y-m-d H:i:s', $fecha_raw) 
                               : $fecha_raw; 

            $this->db->trans_start();

            $voucher_data = [
                'voucher_date' => $fecha_historica,
                'voucher_type' => 'egreso',
                'description'  => $descripcion,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'added_by'     => $employee_id,
                'added_date'   => $fecha_historica, 
                'branch_id'    => $branch_id
            ];
            $this->db->insert('c19_accounting_vouchers', $voucher_data);
            $voucher_id = $this->db->insert_id();

            if (!$voucher_id) { $this->db->trans_rollback(); return false; }

            $entries = [
                ['acc' => 58, 'deb' => $amount, 'cre' => 0, 'desc' => 'Préstamo por cobrar', 'type' => 'asset', 'ord' => 0],
                ['acc' => 5,  'deb' => 0, 'cre' => $amount, 'desc' => 'Desembolso en caja',  'type' => 'asset', 'ord' => 1]
            ];

            foreach ($entries as $e) {
                $t_data = [
                    'account_id'       => $e['acc'],
                    'amount'           => ($e['deb'] > 0) ? $e['deb'] : $e['cre'],
                    'description'      => $descripcion . ' - ' . $e['desc'],
                    'added_date'       => $fecha_historica, 
                    'added_by'         => $employee_id,
                    'transaction_type' => $e['type'],
                    'movement_type'    => ($e['deb'] > 0) ? 'debit' : 'credit',
                    'voucher_id'       => $voucher_id,
                    'payment_methods'  => 'efectivo',
                    'invoice_number'   => 'LOAN-' . $loan_id,
                    'purchased_date'   => $fecha_historica,
                    'transaction_order'=> $e['ord'],
                    'branch_id'        => $branch_id 
                ];
                $this->db->insert('c19_accounting_transactions', $t_data);
            }

            $this->db->trans_complete();
            return $this->db->trans_status();

        } catch (Exception $e) {
            echo "Excepción en préstamo: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function _crear_voucher_pago_local($payment_data) {
        $payment_id = $payment_data['loan_payment_id'];

        try {
            $branch_id = (isset($payment_data['branch_id']) && $payment_data['branch_id'] > 0) 
                         ? $payment_data['branch_id'] 
                         : 1;

            $loan_info = $this->db->where('loan_id', $payment_data['loan_id'])->get('c19_loans')->row();
            if (!$loan_info) return false;

            $amount = floatval($payment_data['paid_amount']); 
            
            $fecha_raw = $payment_data['date_paid'];
            $fecha_historica = is_numeric($fecha_raw) 
                               ? date('Y-m-d H:i:s', $fecha_raw) 
                               : $fecha_raw;

            $installment_number = 0;
            $interest = 0;
            
            $json_objects = json_decode($loan_info->periodic_loan_table);

            if (!empty($json_objects)) {
                $fecha_target_raw = isset($payment_data['payment_due']) && $payment_data['payment_due'] > 0 
                                    ? $payment_data['payment_due'] 
                                    : $payment_data['date_paid'];

                $fecha_target_ymd = is_numeric($fecha_target_raw) 
                                    ? date('Y-m-d', $fecha_target_raw) 
                                    : date('Y-m-d', strtotime($fecha_target_raw));

                $num = 0;
                foreach($json_objects as $obj) {
                    $num++;
                    $fecha_json_ymd = "N/A";
                    if (isset($obj->payment_date)) {
                        $temp_date = str_replace('/', '-', $obj->payment_date); 
                        $fecha_json_ymd = date('Y-m-d', strtotime($temp_date));
                    }

                    if ($fecha_json_ymd == $fecha_target_ymd) {
                        $installment_number = $num;
                        $interest = floatval($obj->interest);
                        break;
                    }
                    if (substr($fecha_json_ymd, 0, 7) == substr($fecha_target_ymd, 0, 7)) {
                        $installment_number = $num;
                        $interest = floatval($obj->interest);
                    }
                }
            }
            if ($installment_number == 0) $interest = 0;

            $custom_round = function($number) {
                $number = floatval($number);
                $partes = explode('.', strval($number));
                if (count($partes) === 2 && strlen($partes[1]) > 2) {
                     if (intval(substr($partes[1], 2, 1)) >= 5) return round($number + 0.001, 2);
                }
                return round($number, 2);
            };

            $interest_cobrado = ($amount < $interest) ? $amount : $interest;
            $intereses_amortizables = $custom_round($interest_cobrado * 0.87);
            $iva = $custom_round($interest_cobrado * 0.13);
            $it = $custom_round(($iva + $intereses_amortizables) * 0.03);
            $capital_final = $custom_round($amount - $iva - $intereses_amortizables);
            if ($capital_final < 0) $capital_final = 0;
            $caja_moneda_nacional = $custom_round($capital_final + $iva + $intereses_amortizables);
            $total_val = $custom_round($caja_moneda_nacional + $it);

            $customer_name = $this->_get_customer_name($payment_data['customer_id']);
            $descripcion = "Pago de préstamo #{$payment_data['loan_id']} - Cliente: {$customer_name} - Cuota N° {$installment_number}";
            
            $metodo_pago = isset($payment_data['payment_type']) ? $payment_data['payment_type'] : 'Efectivo';

            $this->db->trans_start();

            $voucher_data = [
                'voucher_date' => $fecha_historica,
                'voucher_type' => 'ingreso',
                'description'  => $descripcion,
                'total_debit'  => $total_val,
                'total_credit' => $total_val,
                'added_by'     => $this->admin_id,
                'added_date'   => $fecha_historica,
                'branch_id'    => $branch_id
            ];
            $this->db->insert('c19_accounting_vouchers', $voucher_data);
            $voucher_id = $this->db->insert_id();

            if (!$voucher_id) { $this->db->trans_rollback(); return false; }

            $entries = [
                ['acc'=>348, 'deb'=>$it, 'cre'=>0, 'desc'=>'IT', 'type'=>'expenses', 'ord'=>0],
                ['acc'=>5,   'deb'=>$caja_moneda_nacional, 'cre'=>0, 'desc'=>'Caja MN', 'type'=>'asset', 'ord'=>1],
                ['acc'=>58,  'deb'=>0, 'cre'=>$capital_final, 'desc'=>'Capital', 'type'=>'asset', 'ord'=>2],
                ['acc'=>375, 'deb'=>0, 'cre'=>$intereses_amortizables, 'desc'=>'Int. Amortizables', 'type'=>'income', 'ord'=>3],
                ['acc'=>84,  'deb'=>0, 'cre'=>$iva, 'desc'=>'IVA', 'type'=>'liability', 'ord'=>4],
                ['acc'=>85,  'deb'=>0, 'cre'=>$it, 'desc'=>'IT', 'type'=>'liability', 'ord'=>5]
            ];

            foreach ($entries as $e) {
                if ($e['deb'] > 0 || $e['cre'] > 0) {
                    $t_data = [
                        'account_id'       => $e['acc'],
                        'amount'           => ($e['deb'] > 0) ? $e['deb'] : $e['cre'],
                        'description'      => $descripcion . ' - ' . $e['desc'],
                        'added_date'       => $fecha_historica,
                        'added_by'         => $this->admin_id,
                        'transaction_type' => $e['type'],
                        'movement_type'    => ($e['deb'] > 0) ? 'debit' : 'credit',
                        'voucher_id'       => $voucher_id,
                        'payment_methods'  => $metodo_pago,
                        'invoice_number'   => 'PAGO-' . $payment_data['loan_id'],
                        'purchased_date'   => $fecha_historica,
                        'transaction_order'=> $e['ord'],
                        'branch_id'        => $branch_id
                    ];
                    $this->db->insert('c19_accounting_transactions', $t_data);
                }
            }

            $this->db->trans_complete();
            return $this->db->trans_status();

        } catch (Exception $e) {
            echo "Excepción: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function _get_customer_name($person_id) {
        if (!$person_id) return "Cliente General";
        $p = $this->db->select('first_name, last_name')->where('person_id', $person_id)->get('c19_people')->row();
        return $p ? trim($p->first_name . " " . $p->last_name) : "Cliente #$person_id";
    }

    private function _voucher_existe($str1, $str2 = null) {
        $this->db->group_start();
        $this->db->like('description', $str1); 
        if ($str2) $this->db->like('description', $str2);
        $this->db->group_end();
        return $this->db->count_all_results('c19_accounting_vouchers') > 0;
    }

    public function _remap($method, $params = array()) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }
    }
}