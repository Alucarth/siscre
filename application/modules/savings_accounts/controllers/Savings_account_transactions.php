<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_transactions extends MX_Controller
{
    private $require_owner_auth = false;
    public function __construct()
    {
        parent::__construct();

        $this->require_owner_auth = (bool)($this->config->item('savings_require_owner_auth') ?? false);
        // si quieres forzar sin tocar config.php, descomenta la siguiente:
        $this->require_owner_auth = false;

        // Sidebar/header
        $this->load->model('Employee');
        $this->load->model('Module');

        $user_info = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) redirect('login');

        $allowed_modules = $this->Module->get_allowed_modules($user_info->person_id);
        $messages = $alerts = [];
        $this->load->vars(compact('user_info','allowed_modules','messages','alerts'));

        // Modelos
        $this->load->model('savings_accounts/Savings_accounts_model');
        $this->load->model('savings_accounts/Savings_account_transactions_model');
        $this->load->model('savings_accounts/Savings_account_types_model');
    }

    public function index()
    {
        // --- Filtros desde GET ---
        $filters = [
            'account_id'    => $this->input->get('account_id', TRUE),
            'trans_type'    => $this->input->get('trans_type', TRUE),
            'date_from'     => $this->input->get('date_from', TRUE),
            'date_to'       => $this->input->get('date_to', TRUE),
            'branch_id'     => $this->input->get('branch_id', TRUE),
            'registered_by' => $this->input->get('registered_by', TRUE),
            'status'        => $this->input->get('status', TRUE),
            'q'             => $this->input->get('q', TRUE),
        ];

        // --- Paginación (query string) ---
        $this->load->library('pagination');

        $limit = (int)($this->input->get('limit') ?: 25);
        if (!in_array($limit, [25,50,100,200], true)) $limit = 25;

        // página solicitada (puede venir inflada)
        $req_page = max(1, (int)($this->input->get('page') ?: 1));

        // contamos primero para poder **acotar** la página
        $total = $this->Savings_account_transactions_model->count_all($filters);
        $max_page = max(1, (int)ceil($total / $limit));
        $page = min($req_page, $max_page);
        $offset = ($page - 1) * $limit;

        // ahora sí, traer filas de la página correcta
        $rows = $this->Savings_account_transactions_model->get_all($filters, $limit, $offset);

        // paginador
        $config = [
            'base_url'             => site_url('savings_accounts/savings_account_transactions'),
            'total_rows'           => $total,
            'per_page'             => $limit,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'page',
            'reuse_query_string'   => TRUE,
        ];
        $this->pagination->initialize($config);

        // --- Catálogos ---
        $accounts = $this->Savings_accounts_model->get_all_with_person_active();
        $account_options = ['' => '— Todas —'];
        foreach ($accounts as $a) {
            $accno = $a->account_number ?: ('CA-' . str_pad($a->savings_account_id, 6, '0', STR_PAD_LEFT));
            $owner = trim($a->person_name) !== '' ? trim($a->person_name) : ('ID ' . $a->person_id);
            $label = '['.$accno.'] '.$owner.' — '.$a->type_name;
            $account_options[$a->savings_account_id] = $label;
        }

        $type_options = ['' => '— Todos —','deposit'=>'Depósito','withdraw'=>'Retiro','transfer'=>'Transferencia'];

        $branches = $this->db->select('id, branch_name')
                            ->from($this->db->dbprefix('branches'))
                            ->order_by('branch_name')->get()->result();
        $branch_options = ['' => '— Todas —'];
        foreach ($branches as $b) $branch_options[$b->id] = $b->branch_name;

        $ops = $this->db->select('e.person_id, p.first_name, p.last_name')
                        ->from($this->db->dbprefix('employees').' e')
                        ->join($this->db->dbprefix('people').' p','p.person_id=e.person_id')
                        ->where('e.deleted', 0)
                        ->order_by('p.first_name, p.last_name')->get()->result();
        $operator_options = ['' => '— Todos —'];
        foreach ($ops as $o) $operator_options[$o->person_id] = trim($o->first_name.' '.$o->last_name);

        $status_options = ['' => '— Todos —', '1'=>'Activos', '0'=>'Inactivos'];

        // ================================
        // Totales de la página (para la vista)
        // ================================
        $page_deposits  = 0.0;
        $page_withdraws = 0.0;
        foreach ($rows as $r) {
            $amt = (float)$r->amount;
            $tt  = strtolower((string)$r->trans_type);
            if ($tt === 'deposit')  { $page_deposits  += $amt; }
            if ($tt === 'withdraw') { $page_withdraws += $amt; }
        }
        $page_totals = [
            'deposit'  => $page_deposits,
            'withdraw' => $page_withdraws,
            'net'      => $page_deposits - $page_withdraws,
        ];

        // ================================
        // Saldos período + running balance
        // ================================
        $saldo_inicial_periodo = null;
        $saldo_final_periodo   = null;

        $start_dt = !empty($filters['date_from']) ? $filters['date_from'].' 00:00:00' : null;
        $end_dt   = !empty($filters['date_to'])   ? $filters['date_to'].' 23:59:59' : null;

        // --- Caso A: UNA cuenta (como antes)
        if (!empty($filters['account_id'])) {
            $acc_id = (int)$filters['account_id'];
            $acc    = $this->Savings_accounts_model->get($acc_id);
            $now_balance = (float)($acc->current_balance ?? 0);

            if ($end_dt) {
                $sum_newer_than_end = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($end_dt), $filters);
                $saldo_final_periodo = $now_balance - $sum_newer_than_end;
            } else {
                $saldo_final_periodo = $now_balance;
            }

            if ($start_dt) {
                $sum_newer_than_start = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($start_dt), $filters);
                $saldo_inicial_periodo = $now_balance - $sum_newer_than_start;
            }

            if (!empty($rows)) {
                $first = $rows[0];
                $conds = [];
                $conds[] = "(trans_date > ".$this->db->escape($first->trans_date)." OR (trans_date = ".$this->db->escape($first->trans_date)." AND transaction_id > ".(int)$first->transaction_id."))";
                if ($start_dt) $conds[] = "trans_date >= ".$this->db->escape($start_dt);
                if ($end_dt)   $conds[] = "trans_date <= ".$this->db->escape($end_dt);

                $sum_newer_in_period = $this->_sum_signed($acc_id, implode(' AND ', $conds), array_merge($filters, ['trans_type'=>null]));
                $seed = $saldo_final_periodo - $sum_newer_in_period;

                $running = $seed;
                foreach ($rows as &$r) {
                    $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
                    $r->running_balance = $running;      // saldo después de ese movimiento
                    $running -= ($sign * (float)$r->amount);
                }
                unset($r);
            }
        }
        // --- Caso B: VARIAS cuentas (nuevo) -> running balance por cuenta
        else if (!empty($rows)) {

            // 1) Primer movimiento mostrado por cuenta (en esta página)
            $first_by_acc = [];           // acc_id => primera fila (objeto)
            $acc_ids = [];
            foreach ($rows as $rr) {
                $aid = (int)$rr->savings_account_id;
                if (!isset($first_by_acc[$aid])) {
                    $first_by_acc[$aid] = $rr;
                    $acc_ids[] = $aid;
                }
            }

            // 2) Saldo actual por cuenta (una consulta IN)
            $acc_balances = []; // acc_id => current_balance
            if (!empty($acc_ids)) {
                $acc_rows = $this->db->select('savings_account_id, current_balance')
                                    ->from($this->db->dbprefix('savings_accounts'))
                                    ->where_in('savings_account_id', $acc_ids)
                                    ->get()->result();
                foreach ($acc_rows as $ar) {
                    $acc_balances[(int)$ar->savings_account_id] = (float)$ar->current_balance;
                }
            }

            // 3) Cálculo de seed y recorrido por cuenta
            $closing_by_acc = [];  // acc_id => closing balance del período
            $seed_by_acc    = [];  // acc_id => seed para la primera fila de esa cuenta en esta página

            foreach ($first_by_acc as $aid => $first) {
                $now_balance = (float)($acc_balances[$aid] ?? 0);

                // closing (saldo al final del período para esa cuenta)
                if ($end_dt) {
                    $sum_newer_than_end = $this->_sum_signed($aid, "trans_date > ".$this->db->escape($end_dt), $filters);
                    $closing_by_acc[$aid] = $now_balance - $sum_newer_than_end;
                } else {
                    $closing_by_acc[$aid] = $now_balance;
                }

                // sumas más nuevas que la PRIMERA fila de esa cuenta en esta página, dentro del período
                $conds = [];
                $conds[] = "(trans_date > ".$this->db->escape($first->trans_date)." OR (trans_date = ".$this->db->escape($first->trans_date)." AND transaction_id > ".(int)$first->transaction_id."))";
                if ($start_dt) $conds[] = "trans_date >= ".$this->db->escape($start_dt);
                if ($end_dt)   $conds[] = "trans_date <= ".$this->db->escape($end_dt);

                $sum_newer_in_period = $this->_sum_signed($aid, implode(' AND ', $conds), array_merge($filters, ['trans_type'=>null]));

                // seed por cuenta
                $seed_by_acc[$aid] = $closing_by_acc[$aid] - $sum_newer_in_period;
            }

            // 4) Asignar running_balance por cuenta respetando el orden de la página
            $running_by_acc = $seed_by_acc; // copia inicial
            foreach ($rows as &$r) {
                $aid  = (int)$r->savings_account_id;
                $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
                $r->running_balance = isset($running_by_acc[$aid]) ? $running_by_acc[$aid] : null;
                $running_by_acc[$aid] = ($running_by_acc[$aid] ?? 0) - ($sign * (float)$r->amount);
            }
            unset($r);
        }

        // ================================
        // Totales del PERÍODO (sin paginar)
        // ================================
        $pt_q = $this->db->select("
                    SUM(CASE WHEN LOWER(trans_type)='deposit'  THEN amount ELSE 0 END) AS dep,
                    SUM(CASE WHEN LOWER(trans_type)='withdraw' THEN amount ELSE 0 END) AS wit
                ", FALSE)
                ->from($this->db->dbprefix('savings_account_transactions') . ' tx')
                ->where('tx.status', 1); 

        if (!empty($filters['account_id']))    $pt_q->where('tx.savings_account_id', (int)$filters['account_id']);
        if (!empty($filters['date_from']))     $pt_q->where('tx.trans_date >=', $filters['date_from'].' 00:00:00');
        if (!empty($filters['date_to']))       $pt_q->where('tx.trans_date <=', $filters['date_to'].' 23:59:59');
        if (!empty($filters['branch_id']))     $pt_q->where('tx.branch_id', (int)$filters['branch_id']);
        if (!empty($filters['registered_by'])) $pt_q->where('tx.registered_by', (int)$filters['registered_by']);
        // Nota: no aplicamos 'q' ni 'trans_type' en los totales del PERÍODO.

        $pt = $pt_q->get()->row();
        $period_totals = [
            'deposit'  => (float)($pt->dep ?? 0),
            'withdraw' => (float)($pt->wit ?? 0),
            'net'      => (float)($pt->dep ?? 0) - (float)($pt->wit ?? 0),
        ];

        // ================================
        // Resumen del período (solo si hay cuenta y al menos un límite de fecha)
        // ================================
        $opening_balance = null;
        $closing_balance = null;
        $period_totals   = null;

        if (!empty($filters['account_id']) && ($start_dt || $end_dt)) {
            $acc_id = (int)$filters['account_id'];

            // 1) Saldos inicial/final ya calculados arriba
            $opening_balance = $saldo_inicial_periodo;  // puede ser null si no hay date_from
            $closing_balance = $saldo_final_periodo;    // nunca null (toma now si no hay date_to)

            // 2) Totales del período (depósitos / retiros dentro del rango)
            $conds = [];
            $conds[] = "savings_account_id = ".$acc_id;
            if ($start_dt) { $conds[] = "trans_date >= ".$this->db->escape($start_dt); }
            if ($end_dt)   { $conds[] = "trans_date <= ".$this->db->escape($end_dt); }

            // Si deseas respetar 'status' del filtro en el resumen, descomenta:
            if ($filters['status'] !== null && $filters['status'] !== '') {
                $conds[] = "status = ".((int)$filters['status']);
            }

            $where = implode(' AND ', $conds);

            $sql_tot = "
                SELECT
                  SUM(CASE WHEN trans_type = 'deposit'  THEN amount ELSE 0 END) AS dep,
                  SUM(CASE WHEN trans_type = 'withdraw' THEN amount ELSE 0 END) AS wd
                FROM {$this->db->dbprefix('savings_account_transactions')}
                WHERE {$where}
            ";
            $tot = $this->db->query($sql_tot)->row();
            $dep = (float)($tot->dep ?? 0);
            $wd  = (float)($tot->wd  ?? 0);

            $period_totals = [
                'deposit'  => $dep,
                'withdraw' => $wd,
                'net'      => $dep - $wd,
            ];
        }

        // --- URLs de exportación conservando filtros (sin la paginación) ---
        $qs = $this->input->get(NULL, TRUE);
        unset($qs['page']);
        $qs_str = http_build_query(array_filter($qs, fn($v) => $v !== '' && $v !== null));

        // Aliases para compatibilidad con la vista (solo si es 1 cuenta tiene sentido mostrarlos)
        $opening_balance = $saldo_inicial_periodo;
        $closing_balance = $saldo_final_periodo;

        // --- Interés estimado del período (solo si hay UNA cuenta y rango completo) ---
        $period_interest = null;
        if (!empty($filters['account_id']) && $start_dt && $end_dt) {
            $period_interest = $this->_interest_preview((int)$filters['account_id'], $start_dt, $end_dt);
        }

        $data = [
            'filters'           => $filters,
            'transactions'      => $rows,
            'pagination_links'  => $this->pagination->create_links(),
            'total_rows'        => $total,
            'from_row' => $total ? ($offset + 1) : 0,
            'to_row'   => $total ? min($offset + count($rows), $total) : 0,
            'limit'             => $limit,

            'account_options'   => $account_options,
            'type_options'      => $type_options,
            'branch_options'    => $branch_options,
            'operator_options'  => $operator_options,
            'status_options'    => $status_options,

            'saldo_inicial_periodo' => $saldo_inicial_periodo,
            'saldo_final_periodo'   => $saldo_final_periodo,
            'opening_balance'       => $opening_balance,
            'closing_balance'       => $closing_balance,

            'period_interest'       => $period_interest,

            'page_totals'       => $page_totals,
            'period_totals'     => $period_totals,

            'export_csv_url'    => site_url('savings_accounts/savings_account_transactions/export_csv') . ($qs_str ? '?'.$qs_str : ''),
            'export_pdf_url'    => site_url('savings_accounts/savings_account_transactions/export_pdf') . ($qs_str ? '?'.$qs_str : ''),
        ];

        $this->load->view('savings_accounts/savings_account_transactions/index', $data);
    }

    /**
     * Suma firmada (depósito=+, retiro=-) para una cuenta y una condición extra.
     * Respeta filters: branch_id, registered_by, status, date_from/date_to (si le pasas en $filters).
     */
    private function _sum_signed(int $account_id, string $extra_where = '1=1', array $filters = [])
    {
        $tb = $this->db->dbprefix('savings_account_transactions');

        $this->db->select("COALESCE(SUM(CASE WHEN trans_type='withdraw' THEN -amount ELSE amount END),0) AS s", false)
                ->from($tb)
                ->where('savings_account_id', $account_id);

        // Estado si se pidió (1/0)
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $this->db->where('status', (int)$filters['status']);
        }

        // Filtros de sucursal / operador
        if (!empty($filters['branch_id']))     $this->db->where('branch_id', (int)$filters['branch_id']);
        if (!empty($filters['registered_by'])) $this->db->where('registered_by', (int)$filters['registered_by']);

        // Si viene rango por filtros, aplícalo aquí también (pero cuidado: el seed ya arma su propio rango)
        if (!empty($filters['date_from'])) $this->db->where('trans_date >=', $filters['date_from'].' 00:00:00');
        if (!empty($filters['date_to']))   $this->db->where('trans_date <=', $filters['date_to'].' 23:59:59');

        // Extra WHERE libre (para "newer than" etc.)
        if ($extra_where && trim($extra_where) !== '1=1') {
            $this->db->where($extra_where, null, false);
        }

        $row = $this->db->get()->row();
        return (float)($row->s ?? 0);
    }

    public function owner_info($account_id)
    {
        $account_id = (int)$account_id;

        $sql = "
            SELECT 
                sa.savings_account_id,
                p.person_id,
                CONCAT(p.first_name,' ',p.last_name) AS full_name,
                p.photo_url,
                l.id_no,
                c.password AS customer_password
            FROM {$this->db->dbprefix('savings_accounts')} sa
            JOIN {$this->db->dbprefix('people')} p
                ON p.person_id = sa.person_id
            LEFT JOIN {$this->db->dbprefix('leads')} l
                ON l.customer_id = sa.person_id
            LEFT JOIN {$this->db->dbprefix('customers')} c
                ON c.person_id = sa.person_id
            WHERE sa.savings_account_id = ?
            LIMIT 1
        ";

        // 👇 ESTA LÍNEA ES CLAVE
        $row = $this->db->query($sql, [$account_id])->row();

        if (!$row) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false]));
        }

        $photo_full = $this->_photo_url((int)$row->person_id, (string)($row->photo_url ?? ''));

        $out = [
            'ok'        => true,
            'person_id' => (int)$row->person_id,
            'full_name' => (string)$row->full_name,
            'id_no'     => (string)($row->id_no ?? ''),
            'photo_url' => $photo_full,
        ];

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode($out));
    }

    public function form($id = NULL)
    {
        $user_info = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) {
            redirect('login'); return;
        }

        $this->load->model('Savings_account_transactions_model');

        // ==== Datos base para la vista ====
        $data = [];
        // === Opciones de cuenta, a prueba de columnas faltantes ===
        $activeWhere = '';  // no filtra por activo si no hay columna
        if ($this->db->field_exists('is_active', 'savings_accounts')) {
            $activeWhere = 'COALESCE(sa.is_active,1) = 1';
        } elseif ($this->db->field_exists('status', 'savings_accounts')) {
            $activeWhere = 'COALESCE(sa.status,1) = 1';
        } elseif ($this->db->field_exists('disabled', 'savings_accounts')) {
            $activeWhere = 'COALESCE(sa.disabled,0) = 0';
        }

        $this->db->select('sa.savings_account_id AS id, sa.account_number, COALESCE(CONCAT(TRIM(p.first_name), " ", TRIM(p.last_name)), "") AS owner_name', false);
        $this->db->from($this->db->dbprefix('savings_accounts') . ' sa');
        $this->db->join($this->db->dbprefix('people') . ' p', 'p.person_id = sa.person_id', 'left');
        if ($activeWhere !== '') { $this->db->where($activeWhere); }
        $this->db->order_by('sa.account_number', 'ASC');
        $rows = $this->db->get()->result();

        $account_options = ['' => '— Seleccione —'];
        foreach ($rows as $r) {
            $label = $r->account_number;
            if (!empty($r->owner_name)) $label .= ' · ' . $r->owner_name;
            $account_options[$r->id] = $label;
        }
        $data['account_options'] = $account_options;
       
        // 2) Si estoy editando y la cuenta de esa transacción está inactiva hoy,
        //    aseguro que aparezca en el combo (rotulada “inactiva”)
        if (!empty($data['tx']) && !isset($data['account_options'][(int)$data['tx']->savings_account_id])) {
            $r = $this->db->query("
                SELECT sa.savings_account_id AS id, sa.account_number,
                    COALESCE(CONCAT(TRIM(p.first_name),' ',TRIM(p.last_name)),'') AS owner_name
                FROM {$this->db->dbprefix('savings_accounts')} sa
                LEFT JOIN {$this->db->dbprefix('people')} p ON p.person_id = sa.person_id
                WHERE sa.savings_account_id = ?
                LIMIT 1
            ", [(int)$data['tx']->savings_account_id])->row();
            if ($r) {
                $label = trim(($r->account_number ? $r->account_number : 'CTA-'.$r->id).' - '.$r->owner_name).' (inactiva)';
                $data['account_options'][(int)$r->id] = $label;
            }
        }

        if ($id) {
            $data['tx'] = $this->Savings_account_transactions_model->find((int)$id);
            if (!$data['tx']) {
                $this->session->set_flashdata('error','Transacción no encontrada.');
                redirect('savings_accounts/savings_account_transactions'); return;
            }
        }

        // ==== POST ====
        if ($this->input->post()) {
            $trans_type = strtolower((string)$this->input->post('trans_type'));
            $account_id = (int)$this->input->post('savings_account_id');
            $amount     = (float)$this->input->post('amount');
            $desc       = trim((string)$this->input->post('description'));
            $trans_dt   = (string)$this->input->post('trans_date');

            // Validación mínima común
            if (!in_array($trans_type, ['deposit','withdraw','transfer'], true) || $account_id <= 0 || $amount <= 0) {
                $this->session->set_flashdata('error','Datos inválidos.');
                redirect(current_url()); return;
            }

            // === Estado de la cuenta ORIGEN: columnas opcionales ===
            $selectParts = ['sa.savings_account_id'];
            $hasActive   = $this->db->field_exists('is_active',     'savings_accounts') 
                    || $this->db->field_exists('status',         'savings_accounts')
                    || $this->db->field_exists('disabled',       'savings_accounts');
            $hasPF       = $this->db->field_exists('time_deposit',  'savings_accounts');
            $hasMat      = $this->db->field_exists('maturity_date', 'savings_accounts');

            if ($this->db->field_exists('is_active','savings_accounts')) {
                $selectParts[] = 'COALESCE(sa.is_active,1) AS is_active';
            } elseif ($this->db->field_exists('status','savings_accounts')) {
                $selectParts[] = 'COALESCE(sa.status,1) AS is_active';
            } elseif ($this->db->field_exists('disabled','savings_accounts')) {
                // disabled=0 => activo
                $selectParts[] = 'CASE WHEN COALESCE(sa.disabled,0)=0 THEN 1 ELSE 0 END AS is_active';
            } else {
                // no hay columna de estado; asumir activo (no validamos estado)
                $selectParts[] = '1 AS is_active';
            }

            if ($hasPF)  { $selectParts[] = 'COALESCE(sa.time_deposit,0) AS is_time_deposit'; }
            else         { $selectParts[] = '0 AS is_time_deposit'; }

            if ($hasMat) { $selectParts[] = 'sa.maturity_date'; }
            else         { $selectParts[] = 'NULL AS maturity_date'; }

            $src = $this->db->query("
                SELECT ".implode(',', $selectParts)."
                FROM {$this->db->dbprefix('savings_accounts')} sa
                WHERE sa.savings_account_id = ?
                LIMIT 1
            ", [$account_id])->row();

            if (!$src) {
                $this->session->set_flashdata('error','Cuenta de origen no encontrada.');
                redirect(current_url()); return;
            }
            // 🔒 Validación de cuenta ORIGEN inactiva
            if ((int)$src->is_active !== 1) {
                $this->session->set_flashdata('error', 'La cuenta de origen está inactiva. No se puede realizar ninguna transacción.');
                redirect(current_url());
                return;
            }

            // 💰 Validación de saldo insuficiente (retiro o transferencia)
            if (in_array($trans_type, ['withdraw','transfer'], true)) {
                $balance = $this->db->select('current_balance')
                                    ->from($this->db->dbprefix('savings_accounts'))
                                    ->where('savings_account_id', $account_id)
                                    ->get()->row();

                if (!$balance) {
                    $this->session->set_flashdata('error', 'No se pudo verificar el saldo de la cuenta.');
                    redirect(current_url());
                    return;
                }

                if ((float)$balance->current_balance < $amount) {
                    $this->session->set_flashdata('error', 'Saldo insuficiente para realizar esta operación.');
                    redirect(current_url());
                    return;
                }
            }

            if ($hasPF && (int)$src->is_time_deposit === 1) {
                $today = date('Y-m-d');
                if (!$hasMat || empty($src->maturity_date) || $src->maturity_date > $today) {
                    $this->session->set_flashdata('error','La cuenta de origen es de plazo fijo y aún no venció.');
                    redirect(current_url()); return;
                }
            }

            // ====== TRANSFERENCIA ======
            if ($trans_type === 'transfer') {
            $dst_id = (int)$this->input->post('dst_account_id');
            if ($dst_id <= 0 || $dst_id === $account_id) {
                $this->session->set_flashdata('error','Datos inválidos para la transferencia.');
                redirect(current_url()); return;
            }

            $dstActiveSelect = '1 AS is_active';  // por defecto “activo” si no hay columna
            if ($this->db->field_exists('is_active','savings_accounts')) {
                $dstActiveSelect = 'COALESCE(sa.is_active,1) AS is_active';
            } elseif ($this->db->field_exists('status','savings_accounts')) {
                $dstActiveSelect = 'COALESCE(sa.status,1) AS is_active';
            } elseif ($this->db->field_exists('disabled','savings_accounts')) {
                $dstActiveSelect = 'CASE WHEN COALESCE(sa.disabled,0)=0 THEN 1 ELSE 0 END AS is_active';
            }

            $dst = $this->db->query("
                SELECT sa.savings_account_id, $dstActiveSelect
                FROM {$this->db->dbprefix('savings_accounts')} sa
                WHERE sa.savings_account_id = ?
                LIMIT 1
            ", [$dst_id])->row();

            if (!$dst) {
                $this->session->set_flashdata('error','Cuenta destino no encontrada.');
                redirect(current_url()); return;
            }
            // 🔒 Validación de cuenta DESTINO inactiva
            if ((int)$dst->is_active !== 1) {
                $this->session->set_flashdata('error', 'La cuenta destino está inactiva. No se puede realizar la transferencia.');
                redirect(current_url());
                return;
            }
            // solo aplica si realmente tenemos una columna de estado
            if (($this->db->field_exists('is_active','savings_accounts')
            || $this->db->field_exists('status','savings_accounts')
            || $this->db->field_exists('disabled','savings_accounts'))
                && (int)$dst->is_active !== 1) {
                $this->session->set_flashdata('error','La cuenta destino está inactiva.');
                redirect(current_url()); return;
            }
        
                // Ejecutar la transferencia y salir
                $res = $this->Savings_account_transactions_model->create_transfer(
                    $account_id,                // origen
                    $dst_id,                    // destino
                    $amount,
                    $desc,
                    (int)($user_info->branch_id ?? 0),
                    (int)($user_info->person_id ?? 0)
                );

                if ($res && !empty($res['withdraw_id']) && !empty($res['deposit_id'])) {
                    $this->session->set_flashdata('success','Transferencia realizada correctamente.');
                    // (opcional) Voucher:
                    $this->session->set_flashdata(
                        'pdf_url',
                        site_url('savings_accounts/savings_account_transactions/voucher_transfer/'.$res['withdraw_id'].'/'.$res['deposit_id'])
                    );
                    redirect('savings_accounts/savings_account_transactions');
                    return;
                }

                $msg = property_exists($this->Savings_account_transactions_model,'last_error')
                    ? ($this->Savings_account_transactions_model->last_error ?: '')
                    : '';
                if ($msg === '') {
                    $msg = 'No se pudo completar la transferencia (saldo insuficiente, cuenta inactiva o plazo fijo no vencido).';
                }
                $this->session->set_flashdata('error', $msg);
                redirect(current_url());
                return;
            }

            // ====== DEPÓSITO / RETIRO ======
            // Campos sólo para depósito
            $dep_name = trim((string)($this->input->post('depositor_name') ?? ''));
            $dep_doc  = trim((string)($this->input->post('depositor_document') ?? ''));

            if ($trans_type === 'deposit') {
                if ($dep_name === '' || $dep_doc === '') {
                    $this->session->set_flashdata('error','Completa depositante y documento.');
                    redirect(current_url()); return;
                }
            }

            // Inserta movimiento simple
            $row = [
                'savings_account_id' => $account_id,
                'trans_type'         => $trans_type,
                'amount'             => $amount,
                'description'        => $desc,
                'trans_date'         => $trans_dt ?: date('Y-m-d H:i:s'),
                'branch_id'          => (int)($user_info->branch_id ?? 0),
                'registered_by'      => (int)($user_info->person_id ?? 0),
                // compatibilidad si existen las columnas
                'depositor_name'     => ($trans_type==='deposit') ? $dep_name : '',
                'depositor_document' => ($trans_type==='deposit') ? $dep_doc  : '',
            ];

            $ok_id = $this->Savings_account_transactions_model->insert($row);
            if ($ok_id) {
                $msg = ($trans_type === 'deposit') ? 'Depósito' : 'Retiro';
                $this->session->set_flashdata('success', "$msg registrado correctamente.");
                // Voucher (opcional)
                $this->session->set_flashdata(
                    'pdf_url',
                    site_url('savings_accounts/savings_account_transactions/voucher/'.$ok_id)
                );
                redirect('savings_accounts/savings_account_transactions'); return;
            }

            $this->session->set_flashdata(
                'error',
                property_exists($this->Savings_account_transactions_model,'last_error')
                    ? ($this->Savings_account_transactions_model->last_error ?: 'No se pudo guardar.')
                    : 'No se pudo guardar.'
            );
            redirect(current_url()); return;
        }
        // === Opciones de tipo de transacción ===
        $data['type_options'] = [
            'deposit'  => 'Depósito',
            'withdraw' => 'Retiro',
            'transfer' => 'Transferencia',
        ];

        // ==== GET → Mostrar formulario ====
        $this->load->view('savings_accounts/savings_account_transactions/form', $data);
    }
   
    /** helper privado en el mismo controlador **/
    private function _photo_url($person_id, $filename)
    {
        if ($filename && file_exists(FCPATH."uploads/profile-$person_id/$filename")) {
            // ¡OJO!: base_url, no site_url
        return base_url("uploads/profile-$person_id/$filename");
        }
        // fallback local si tienes un avatar por defecto en tu tema
        return base_url('uploads/people/avatar.png');
    }

    public function delete($id = NULL)
    {
        // Por ahora: soft-delete de la transacción (no revierte saldos).
        // Si quieres reversar saldos al “eliminar”, lo hacemos en un paso siguiente.
        if (is_null($id)) $id = $this->uri->segment(4);
        if ($id && $this->Savings_account_transactions_model->delete($id)) {
            $this->session->set_flashdata('success','Transacción deshabilitada.');
        } else {
            $this->session->set_flashdata('error','No fue posible deshabilitar la transacción.');
        }
        redirect('savings_accounts/savings_account_transactions');
    }

    /* ==========================================================
       Los siguientes métodos son opcionales si más adelante
       decides separar formularios. Por ahora no se usan.
       ========================================================== */

    public function deposit($tx_id = NULL)
    {
        $accounts = $this->Savings_accounts_model->get_all();
        $opts = [];
        foreach ($accounts as $a) {
            $owner = trim(($a->first_name ?? '').' '.($a->last_name ?? ''));
            $opts[$a->savings_account_id] = ($a->account_number ?: 'CA-'.$a->savings_account_id).' – '.$owner;
        }
        $data['account_options'] = $opts;

        if ($this->input->post()) {
            $post = $this->input->post();
            if ($this->Savings_account_transactions_model->insert($post)) {
                $this->session->set_flashdata('success','Depósito registrado correctamente.');
                redirect('savings_accounts/savings_account_transactions/index');
                return;
            } else {
                $this->session->set_flashdata('error','Error al procesar el depósito.');
            }
        }

        $data['tx'] = $tx_id ? $this->Savings_account_transactions_model->get($tx_id) : NULL;
        $this->load->view('savings_accounts/savings_account_transactions/deposit_form', $data);
    }

    public function form_transfer()
    {
        $data['accounts'] = $this->Savings_accounts_model->get_all();
        $this->load->view('savings_accounts/savings_account_transactions/form_transfer', $data);
    }

    private function _validate_transfer($src_id, $dst_id, $amount)
    {
        $amount = (float)$amount;
        if ($amount <= 0) {
            return ['ok'=>false,'msg'=>'El monto debe ser mayor a cero.'];
        }

        $chk = $this->_validate_transfer($account_id, $dst_id, $amount);
        if (!$chk['ok']) {
            $this->session->set_flashdata('error', $chk['msg'] ?? 'No se pudo validar la transferencia.');
            redirect(current_url());
            return;
        }   

        $this->load->model('savings_accounts/Savings_account_transactions_model', 'txm');
        $state = $this->txm->get_accounts_state($src_id, $dst_id);

        if (!$state['src'] || !$state['dst']) {
            return ['ok'=>false,'msg'=>'No se encontró información de las cuentas seleccionadas.'];
        }

        $src = $state['src'];
        $dst = $state['dst'];

        // 1) Ambas activas
        if ((int)$src->is_active !== 1) return ['ok'=>false,'msg'=>'La cuenta de origen está inactiva.'];
        if ((int)$dst->is_active !== 1) return ['ok'=>false,'msg'=>'La cuenta de destino está inactiva.'];

        // 2) Plazo fijo: debe estar vencido
        if ((int)$src->is_time_deposit === 1) {
            // si no manejas horas, comparamos fecha a medianoche
            $hoy = date('Y-m-d');
            $vence = $src->maturity_date ? substr($src->maturity_date,0,10) : null;
            if (!$vence || $vence > $hoy) {
                return ['ok'=>false,'msg'=>'La cuenta de origen es de plazo fijo y aún no ha vencido.'];
            }
        }

        // 3) Mínimo requerido
        // Si no tienes min_transfer_amount en la tabla de tipos, puedes fijar un mínimo global:
        $min_req = isset($src->min_transfer_amount) ? (float)$src->min_transfer_amount : 0.00;

        // Saldo suficiente (monto + mínimo requerido)
        $disp = (float)$src->available_balance;
        if ($disp < ($amount + $min_req)) {
            $det = sprintf('Saldo disponible %.2f; mínimo requerido %.2f; monto %.2f.', $disp, $min_req, $amount);
            return ['ok'=>false,'msg'=>"Saldo insuficiente para la transferencia. $det"];
        }

        return ['ok'=>true,'msg'=>'OK'];
    }

    public function store_transfer()
    {
        $old = error_reporting();
        error_reporting($old & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

        $this->output->set_content_type('application/json; charset=UTF-8');

        try {
            // Lee SIEMPRE por input->post
            $src_id   = (int)$this->input->post('src_account_id');
            $dst_id   = (int)$this->input->post('dst_account_id');
            $amount   = (float)$this->input->post('amount');
            $desc     = trim($this->input->post('description'));

            // Validaciones mínimas (sin tocar reglas de negocio existentes)
            if ($src_id <= 0 || $dst_id <= 0 || $src_id === $dst_id) {
                throw new Exception('Cuentas de origen/destino inválidas.');
            }
            if ($amount <= 0) {
                throw new Exception('El monto debe ser mayor a 0.');
            }

            // Llama a tu modelo que ya transfiere (no cambiamos lógica)
            // Debe retornar ids de las 2 transacciones o booleano
            $result = $this->Savings_account_transactions_model->transfer($src_id, $dst_id, $amount, $desc);

            if (!$result) {
                // Ideal: el modelo devuelve un mensaje de error
                $msg = $this->Savings_account_transactions_model->last_error ?? 'No se pudo completar la transferencia.';
                throw new Exception($msg);
            }

            // Estandariza respuesta
            $payload = [
                'ok' => true,
                'message' => 'Transferencia realizada correctamente.',
                'data' => $result, // ej: ['withdraw_id'=>..., 'deposit_id'=>...]
            ];
            echo json_encode($payload);
        } catch (Exception $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        } finally {
            error_reporting($old);
        }
        return; // evita que CI siga procesando
    }

    public function voucher($transaction_id)
    {
        $tx_id = (int)$transaction_id;

        // --- DATA ---
        $sql = "
            SELECT 
                tx.*,
                sa.account_number, sa.savings_account_id, sa.person_id AS owner_id,
                sat.name AS account_type_name,
                p.first_name, p.last_name,
                l.id_no,
                b.branch_name,
                op.first_name AS op_first, op.last_name AS op_last
            FROM {$this->db->dbprefix('savings_account_transactions')} tx
            LEFT JOIN {$this->db->dbprefix('savings_accounts')} sa 
                ON sa.savings_account_id = tx.savings_account_id
            LEFT JOIN {$this->db->dbprefix('savings_account_types')} sat
                ON sat.savings_account_type_id = sa.savings_account_type_id
            LEFT JOIN {$this->db->dbprefix('people')} p 
                ON p.person_id = sa.person_id
            LEFT JOIN {$this->db->dbprefix('leads')} l 
                ON l.customer_id = sa.person_id
            LEFT JOIN {$this->db->dbprefix('branches')} b 
                ON b.id = tx.branch_id
            LEFT JOIN {$this->db->dbprefix('people')} op 
                ON op.person_id = tx.registered_by
            WHERE tx.transaction_id = ?
            LIMIT 1
        ";
        $row = $this->db->query($sql, [$tx_id])->row();
        // Asegura cargar la librería Pdf ANTES de usarla
        if (!isset($this->pdf) || !is_object($this->pdf)) {
            $this->load->library('pdf');   // Clase application/libraries/Pdf.php (P mayúscula)
        }

        // Si antes usabas $this->_mpdf_load('P'), elimínalo y usa:
        $mpdf = $this->pdf->load(['utf-8','A5','','',8,8,10,10,0,0,'P']);

        // Escribe el HTML 1 sola vez (no inyectes CSS adicional aquí)
        $html = $this->load->view('savings_accounts/vouchers/voucher_simple', ['tx'=>$row], TRUE);

        // Limpia buffers ruidosos
        $old = error_reporting();
        error_reporting($old & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->SetAutoPageBreak(true, 8);
        $mpdf->shrink_tables_to_fit = 1;

        $mpdf->WriteHTML($html);

        $filename = 'voucher_'.($row->account_number ?: 'CTA').'_'.$row->owner_id.'_'.date('Ymd_His', strtotime($row->trans_date)).'.pdf';
        $mpdf->Output($filename, 'I');

        error_reporting($old);
        exit;
    }

    public function voucher_transfer($withdraw_id, $deposit_id)
    {
        $wId = (int)$withdraw_id;
        $dId = (int)$deposit_id;

        $fetch = function($id){
            return $this->db->query("
                SELECT 
                    tx.*,
                    sa.account_number, sa.savings_account_id, sa.person_id AS owner_id,
                    p.first_name, p.last_name,
                    b.branch_name
                FROM {$this->db->dbprefix('savings_account_transactions')} tx
                LEFT JOIN {$this->db->dbprefix('savings_accounts')} sa ON sa.savings_account_id=tx.savings_account_id
                LEFT JOIN {$this->db->dbprefix('people')} p ON p.person_id=sa.person_id
                LEFT JOIN {$this->db->dbprefix('branches')} b ON b.id=tx.branch_id
                WHERE tx.transaction_id=? LIMIT 1
            ", [$id])->row();
        };

        $rowW = $fetch($wId);
        $rowD = $fetch($dId);
        if (!$rowW || !$rowD) show_error('Transacciones de transferencia no encontradas', 404);

        $html = $this->load->view('savings_accounts/vouchers/voucher_transfer', ['w'=>$rowW,'d'=>$rowD], TRUE);

        $this->load->library('pdf');
        $mpdf = $this->pdf->load(['utf-8','Letter','',8,8,8,8,0,0,'P']);

        $old = error_reporting();
        error_reporting($old & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $mpdf->SetAutoPageBreak(true, 8);
        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->WriteHTML('<style>body{font-family:sans-serif;font-size:12px}</style>',1);
        $mpdf->WriteHTML($html,2);

        $filename = sprintf('voucher_transfer_%s_to_%s_%s.pdf',
            $rowW->account_number ?: 'CTA_ORIG',
            $rowD->account_number ?: 'CTA_DEST',
            date('Ymd_His', strtotime($rowW->trans_date))
        );
        $mpdf->Output($filename, 'I');
        error_reporting($old);
        exit;
    }

    public function export_csv()
    {
        // ---------- 1) Normalizar filtros ----------
        $in = $this->input->get(NULL, TRUE) ?: [];
        $filters = array_merge([
            'account_id'    => null,
            'trans_type'    => null,
            'date_from'     => null,
            'date_to'       => null,
            'branch_id'     => null,
            'registered_by' => null,
            'status'        => null,
            'q'             => null,
        ], $in);

        // ---------- 2) Silenciar notices y limpiar buffers ----------
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

        // Cerrar TODOS los buffers abiertos (por seguridad)
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) { @ob_end_clean(); }
        }

        // ---------- 3) Traer datos ----------
        // Límite sano para exportar (ajusta si lo necesitas)
        $rows = $this->Savings_account_transactions_model->get_all($filters, 10000, 0);

        // ¿Calculamos running balance? Solo si hay UNA cuenta seleccionada
        $running_by_row = [];
        if (!empty($filters['account_id'])) {
            // Semilla: saldo después de la transacción más reciente del set exportado
            $acc_id = (int)$filters['account_id'];
            $acc    = $this->Savings_accounts_model->get($acc_id);
            $now_balance = (float)($acc->current_balance ?? 0);

            // Si hay tope superior del período, calcula saldo a esa fecha; si no, es el actual
            $end_dt = !empty($filters['date_to']) ? $filters['date_to'].' 23:59:59' : null;
            if ($end_dt) {
                $sum_newer_than_end = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($end_dt), $filters);
                $closing_period = $now_balance - $sum_newer_than_end;
            } else {
                $closing_period = $now_balance;
            }

            // Orden de exportación es el mismo que en pantalla (desc): calculamos de nuevo el seed
            if (!empty($rows)) {
                $first = $rows[0];
                $conds = [];
                $conds[] = "(trans_date > ".$this->db->escape($first->trans_date)
                        ." OR (trans_date = ".$this->db->escape($first->trans_date)
                        ." AND transaction_id > ".(int)$first->transaction_id."))";

                if (!empty($filters['date_from'])) $conds[] = "trans_date >= ".$this->db->escape($filters['date_from'].' 00:00:00');
                if ($end_dt)                         $conds[] = "trans_date <= ".$this->db->escape($end_dt);

                // Para el seed ignoramos el filtro por tipo; el saldo depende de TODOS
                $sum_newer_in_period = $this->_sum_signed($acc_id, implode(' AND ', $conds), array_merge($filters, ['trans_type'=>null]));
                $seed = $closing_period - $sum_newer_in_period;

                // Construir running balance para cada fila (desc)
                $running = $seed;
                foreach ($rows as $r) {
                    $running_by_row[$r->transaction_id] = $running; // saldo después de esa transacción
                    $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
                    $running -= ($sign * (float)$r->amount);
                }
            }
        }

        // ---------- 4) Enviar CSV ----------
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="transacciones'.(
            !empty($filters['account_id'])
                ? ('_'.$this->Savings_accounts_model->get((int)$filters['account_id'])->account_number.'_'.$this->Savings_accounts_model->get((int)$filters['account_id'])->person_id)
                : ''
        ).'_'.date('Ymd').'.csv"');

        // BOM para Excel (opcional pero útil)
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output','w');
        // Usa “;” si tu Excel regional lo prefiere, si no cambia a “,”
        $sep = ';';

        // Cabecera
        fputcsv($out, ['Fecha','Cuenta','Cliente','Tipo','Monto','Saldo resultante','Descripción'], $sep);

        foreach ($rows as $r) {
            $cliente = trim(($r->first_name ?? '').' '.($r->last_name ?? ''));
            $saldo   = '';
            if (!empty($filters['account_id']) && isset($running_by_row[$r->transaction_id])) {
                $saldo = number_format((float)$running_by_row[$r->transaction_id], 2, '.', '');
            }
            fputcsv($out, [
                date('Y-m-d H:i', strtotime($r->trans_date)),
                (string)$r->account_number,
                $cliente,
                ucfirst((string)$r->trans_type),
                number_format((float)$r->amount, 2, '.', ''),
                $saldo,
                (string)$r->description,
            ], $sep);
        }
        fclose($out);

        // ---------- 5) Restaurar nivel de errores y salir ----------
        error_reporting($old_level);
        exit;
    }

    public function export_pdf()
    {
        $filters = $this->input->get(NULL, TRUE);
        $rows    = $this->Savings_account_transactions_model->get_all($filters, 1000, 0);

        // Running balance si hay una cuenta
        $opening = $closing = $totals = null;
        if (!empty($filters['account_id'])) {
            list($rows, $opening, $closing, $totals) = $this->_with_running_balance($rows, $filters);
        } else {
            foreach ($rows as &$r) { $r->running_balance = null; } unset($r);
        }

        // Cargar mPDF legacy
        $this->load->library('pdf');
        $mpdf = $this->pdf->load('"en-GB-x","A4","","",10,10,10,10,6,3,"P"');

        // Blindaje mPDF legacy
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        // Encabezado amigable
        $title = 'Reporte de Transacciones';
        $subtitle = '';
        if (!empty($filters['account_id'])) {
            $acc = $this->Savings_accounts_model->get((int)$filters['account_id']);
            if ($acc) {
                $subtitle = 'Cuenta: '.htmlspecialchars($acc->account_number).' · Cliente ID: '.(int)$acc->person_id;
            }
        }
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $subtitle .= ($subtitle ? ' · ' : '');
            $subtitle .= 'Período: '
                    . (!empty($filters['date_from']) ? $filters['date_from'] : '—')
                    . ' a '
                    . (!empty($filters['date_to'])   ? $filters['date_to']   : '—');
        }

        // HTML simple (sin <style>)
        $html  = '<html><head><meta charset="utf-8"></head><body>';
        $html .= '<h3>'.$title.'</h3>';
        if ($subtitle) $html .= '<div>'.$subtitle.'</div>';

        // Resumen del período si aplica
        if (!empty($filters['account_id'])) {
            $html .= '<div><strong>Saldo inicial:</strong> '.($opening!==null?number_format($opening,2):'—')
                .' &nbsp; | &nbsp; <strong>Saldo final:</strong> '.($closing!==null?number_format($closing,2):'—').'</div>';
            if (is_array($totals)) {
                $html .= '<div><strong>Depósitos:</strong> '.number_format($totals['deposit'],2)
                    .' &nbsp; · &nbsp; <strong>Retiros:</strong> '.number_format($totals['withdraw'],2)
                    .' &nbsp; · &nbsp; <strong>Neto:</strong> '.number_format($totals['net'],2).'</div>';
            }
            $html .= '<br>';
        }

        // Tabla
        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<thead><tr>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Saldo resultante</th>
                    <th>Descripción</th>
                </tr></thead><tbody>';

        foreach ($rows as $r) {
            $cliente = trim(($r->first_name ?? '').' '.($r->last_name ?? ''));
            $html .= '<tr>'
                .  '<td>'.htmlspecialchars(date('d/m/Y H:i', strtotime($r->trans_date)), ENT_QUOTES, 'UTF-8').'</td>'
                .  '<td>'.htmlspecialchars((string)$r->account_number, ENT_QUOTES, 'UTF-8').'</td>'
                .  '<td>'.htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8').'</td>'
                .  '<td>'.htmlspecialchars(ucfirst((string)$r->trans_type), ENT_QUOTES, 'UTF-8').'</td>'
                .  '<td align="right">'.number_format((float)$r->amount, 2).'</td>'
                .  '<td align="right">'.($r->running_balance!==null ? number_format((float)$r->running_balance,2) : '').'</td>'
                .  '<td>'.htmlspecialchars((string)$r->description, ENT_QUOTES, 'UTF-8').'</td>'
                .  '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</body></html>';

        $mpdf->WriteHTML($html, 2);

        // Nombre de archivo amigable
        $filename = 'transacciones_'.date('Ymd').'.pdf';
        if (!empty($filters['account_id']) && !empty($acc)) {
            $filename = sprintf('transacciones_%s_%s_%s.pdf',
                $acc->account_number, $acc->person_id, date('Ymd'));
        }

        $mpdf->Output($filename, 'I');
        error_reporting($old_level);
        exit;
    }

    public function interest_accrue()
    {
        $account_id = (int)$this->input->get_post('account_id');
        $date_from  = $this->input->get_post('date_from'); // Y-m-d
        $date_to    = $this->input->get_post('date_to');   // Y-m-d
        $preview    = (int)$this->input->get_post('preview' , TRUE) ?: (int)$this->input->get('preview' , TRUE);

        if ($account_id <= 0 || !$date_from || !$date_to) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'Parámetros incompletos']));
        }

        // Normalizamos a límites del día
        $start_dt = $date_from.' 00:00:00';
        $end_dt   = $date_to.' 23:59:59';

        // Traemos cuenta y tipo con tasa
        $acc = $this->db->select('sa.*, sat.interest_rate_apy')
            ->from($this->db->dbprefix('savings_accounts').' sa')
            ->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id=sa.savings_account_type_id','left')
            ->where('sa.savings_account_id', $account_id)
            ->get()->row();

        if (!$acc) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'Cuenta no encontrada']));
        }

        $apy = (float)($acc->interest_rate_apy ?? 0);
        if ($apy <= 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'La cuenta no tiene tasa configurada']));
        }

        // 1) Saldo al inicio y movimientos del período
        $opening = $this->_opening_balance_at($account_id, $start_dt);
        $txs     = $this->_tx_in_period_asc($account_id, $start_dt, $end_dt);

        // 2) Construir segmentos por cambio de saldo
        $segments = [];
        $curr_balance = (float)$opening;
        $curr_from    = $start_dt;

        foreach ($txs as $t) {
            $ts = $t->trans_date; // Y-m-d H:i:s
            // segmento desde curr_from hasta la fecha de esta transacción
            $segments[] = ['from'=>$curr_from, 'to'=>$ts, 'balance'=>$curr_balance];

            // aplicar la transacción
            $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
            $curr_balance += $sign * (float)$t->amount;

            // siguiente segmento arranca desde este timestamp
            $curr_from = $ts;
        }
        // último segmento hasta fin del período
        $segments[] = ['from'=>$curr_from, 'to'=>$end_dt, 'balance'=>$curr_balance];

        // 3) Promedio diario (ADB) y días
        $total_days = 0.0;
        $weighted   = 0.0;
        foreach ($segments as $s) {
            $d = $this->_days_between($s['from'], $s['to']);
            if ($d > 0) {
                $total_days += $d;
                $weighted   += $s['balance'] * $d;
            }
        }
        // En períodos cortos sin movimientos, total_days podría ser 0 si from>=to (validamos)
        if ($total_days <= 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'Período sin días efectivos']));
        }

        $adb = $weighted / $total_days;

        // 4) Interés simple por día: APY/365, sin capitalización intra-período
        $interest = $adb * ($apy / 365.0) * $total_days;

        // Redondeo a 2 decimales (ajústalo si usas más precisión)
        $interest = round($interest, 2);

        // PREVIEW
        if ($preview) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'ok'          => true,
                    'account_id'  => $account_id,
                    'date_from'   => $date_from,
                    'date_to'     => $date_to,
                    'days'        => $total_days,
                    'apy'         => $apy,
                    'opening'     => round($opening,2),
                    'adb'         => round($adb,2),
                    'interest'    => $interest,
                ]));
        }

        // 5) Registrar el abono como transacción (depósito)
        if ($interest <= 0) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'Interés calculado no positivo']));
        }

        // branch_id y actor desde sesión (igual que en form())
        $branch_id = (int)($this->session->userdata('branch_id') ?: 0);
        if ($branch_id === 0) {
            $emp = $this->Employee->get_logged_in_employee_info();
            if (is_object($emp) && isset($emp->branch_id)) {
                $branch_id = (int)$emp->branch_id;
            }
        }
        $actor = (int)($this->session->userdata('person_id') ?: 0);

        $payload = [
            'savings_account_id' => $account_id,
            'trans_type'         => 'deposit', // abono por interés
            'amount'             => $interest,
            'trans_date'         => $end_dt,   // abonamos al cierre del período
            'description'        => sprintf('Interés del %s al %s (ADB %.2f; %s días; APY %.4f)',
                                            $date_from, $date_to, $adb, $total_days, $apy),
            'branch_id'          => $branch_id,
            'depositor_name'     => 'Sistema de Intereses',
            'depositor_document' => 'N/A',
        ];

        $ok_id = $this->Savings_account_transactions_model->post_simple($payload);

        if ($ok_id) {
            // Actualiza last_interest_calc si corresponde (opcional)
            $this->db->where('savings_account_id', $account_id)
                    ->update($this->db->dbprefix('savings_accounts'), ['last_interest_calc' => $date_to]);

            // Ponemos flash para abrir voucher automáticamente desde el index (ya lo tienes)
            $this->session->set_flashdata('print_tx_id', $ok_id);

            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>true,'transaction_id'=>$ok_id,'interest'=>$interest]));
        } else {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false,'error'=>'No se pudo registrar el abono de interés']));
        }
    }

    public function monthly_interest_batch($yyyymm = null)
    {
        // Mes objetivo: por defecto el mes anterior (en hora del servidor)
        if (!$yyyymm) {
            $yyyymm = date('Ym', strtotime('first day of last month'));
        }
        $year = (int)substr($yyyymm, 0, 4);
        $month = (int)substr($yyyymm, 4, 2);

        // Rango: 1er día 00:00:00 al último día 23:59:59
        $date_from = date('Y-m-01', strtotime("$year-$month-01"));
        $date_to   = date('Y-m-t',  strtotime("$year-$month-01"));

        // Cuentas con tasa > 0 (y que quieras considerar activas)
        $accs = $this->db->select('sa.savings_account_id, sat.interest_rate_apy')
            ->from($this->db->dbprefix('savings_accounts').' sa')
            ->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id=sa.savings_account_type_id','left')
            ->where('IFNULL(sat.interest_rate_apy,0) >', 0)
            ->where('sa.deleted', 0)  // ajusta si tienes flag de inactividad
            ->get()->result();

        $ok = 0; $fail = 0; $log = [];

        foreach ($accs as $a) {
            // Reusamos la misma acción vía método interno para evitar HTTP:
            $_POST = [
                'account_id' => $a->savings_account_id,
                'date_from'  => $date_from,
                'date_to'    => $date_to,
                'preview'    => 0
            ];

            ob_start(); // capturamos la salida JSON
            $this->interest_accrue();
            $json = ob_get_clean();
            $res = json_decode($json, true);

            if (is_array($res) && !empty($res['ok'])) {
                $ok++;
            } else {
                $fail++;
                $log[] = ['account_id'=>$a->savings_account_id, 'error'=>$res['error'] ?? 'unknown'];
            }
        }

        // Salida para cron/log
        header('Content-Type: application/json');
        echo json_encode([
            'period' => $yyyymm,
            'processed' => count($accs),
            'ok' => $ok,
            'fail' => $fail,
            'errors' => $log
        ]);
        exit;
    }

    /**
     * Interés estimado del período [start_dt, end_dt]
     * - Usa APY del tipo de cuenta (interest_rate_apy)
     * - Base 365 días (fácil de cambiar)
     * - Recorre los movimientos del período para integrar saldo-tiempo
     * - Signo: withdraw = negativo; cualquier otro = positivo (dep/transfer-in)
     */
    private function _interest_preview(int $acc_id, string $start_dt, string $end_dt): float
    {
        // Tasa APY
        $row = $this->db->select('sa.current_balance, sat.interest_rate_apy')
            ->from($this->db->dbprefix('savings_accounts').' sa')
            ->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id=sa.savings_account_type_id','left')
            ->where('sa.savings_account_id', $acc_id)
            ->limit(1)->get()->row();

        if (!$row) return 0.0;

        $rate = (float)($row->interest_rate_apy ?? 0);
        if ($rate <= 0) return 0.0;

        $now_balance = (float)$row->current_balance;

        // Saldo de apertura al inicio del período (balance justo antes de start_dt)
        $sum_after_start = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($start_dt), ['trans_type'=>null]);
        $opening = $now_balance - $sum_after_start;

        // Transacciones dentro del período (ascendente)
        $txs = $this->db->select('transaction_id, trans_date, trans_type, amount')
            ->from($this->db->dbprefix('savings_account_transactions'))
            ->where('savings_account_id', $acc_id)
            ->where('trans_date >=', $start_dt)
            ->where('trans_date <=', $end_dt)
            ->where('status', 1) // ajusta si usas otro flag
            ->order_by('trans_date ASC, transaction_id ASC')
            ->get()->result();

        $seconds_per_day = 86400.0;
        $year_base = 365.0;

        $interest = 0.0;
        $running  = $opening;
        $last_ts  = strtotime($start_dt);
        $end_ts   = strtotime($end_dt);

        foreach ($txs as $t) {
            $t_ts = strtotime($t->trans_date);
            if ($t_ts > $end_ts) $t_ts = $end_ts;

            if ($t_ts > $last_ts) {
                $days = ($t_ts - $last_ts) / $seconds_per_day;
                if ($days > 0) {
                    $interest += $running * ($days / $year_base) * $rate;
                }
                $last_ts = $t_ts;
            }

            // Aplica el movimiento
            $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
            $running += $sign * (float)$t->amount;

            if ($last_ts >= $end_ts) break;
        }

        // Último tramo hasta end_dt
        if ($last_ts < $end_ts) {
            $days = ($end_ts - $last_ts) / $seconds_per_day;
            if ($days > 0) {
                $interest += $running * ($days / $year_base) * $rate;
            }
        }

        // Nunca negativo
        if ($interest < 0) $interest = 0.0;

        return round($interest, 2);
    }

    /** Carga mPDF con librería Pdf y sanea HTML */
    private function _mpdf_load($orientation = 'P')
    {
        // Evita que warnings viejos de mPDF rompan cabeceras
        ini_set('display_errors','0');

        $this->load->library('pdf'); // usa application/libraries/Pdf.php
        // Tu Pdf::load recibe una cadena con parámetros (idioma,tamaño,márgenes,orientación)
        // Mantengo A4 y orientación variable:
        $params = '"en-GB-x","A4","","",8,8,8,8,4,3,"'.($orientation === 'L' ? 'L' : 'P').'"';
        $mpdf = $this->pdf->load($params);

        // Opcionales, ayudan con imágenes y avisos
        $mpdf->showImageErrors = false;
        return $mpdf;
    }

    private function _clean_html($html)
    {
        // quita BOM y bytes raros que a veces mete la vista si hay espacios/echo antes
        $html = preg_replace('/^\xEF\xBB\xBF/', '', $html); // BOM UTF-8
        $html = str_replace(["\0","\x00"], '', $html);
        return $html;
    }

    /** 
     * Calcula running_balance por fila (orden esperado: DESC por fecha, tie-break por ID).
     * Devuelve [$rows_con_balance, $opening_balance, $closing_balance, $period_totals]
     */
    private function _with_running_balance(array $rows, array $filters)
    {
        if (empty($filters['account_id']) || empty($rows)) {
            // Sin cuenta única o sin filas: no hay running balance ni resumen.
            foreach ($rows as &$r) { $r->running_balance = null; }
            unset($r);
            return [$rows, null, null, null];
        }

        $acc_id  = (int)$filters['account_id'];
        $start_dt = !empty($filters['date_from']) ? $filters['date_from'].' 00:00:00' : null;
        $end_dt   = !empty($filters['date_to'])   ? $filters['date_to'].' 23:59:59' : null;

        // Estado actual de la cuenta
        $acc         = $this->Savings_accounts_model->get($acc_id);
        $now_balance = (float)($acc->current_balance ?? 0);

        // Saldo final del período (a la fecha_to si existe; si no, es el saldo actual)
        if ($end_dt) {
            $sum_newer_than_end = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($end_dt), $filters);
            $closing_balance    = $now_balance - $sum_newer_than_end;
        } else {
            $closing_balance    = $now_balance;
        }

        // Saldo inicial del período (justo antes del date_from)
        $opening_balance = null;
        if ($start_dt) {
            $sum_newer_than_start = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($start_dt), $filters);
            $opening_balance      = $now_balance - $sum_newer_than_start;
        }

        // Totales del período (depósitos/retiros en el rango)
        $conds = ["savings_account_id = ".$acc_id];
        if ($start_dt) $conds[] = "trans_date >= ".$this->db->escape($start_dt);
        if ($end_dt)   $conds[] = "trans_date <= ".$this->db->escape($end_dt);
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $conds[] = "status = ".((int)$filters['status']);
        }
        $where = implode(' AND ', $conds);
        $sql_tot = "
            SELECT
            SUM(CASE WHEN trans_type='deposit'  THEN amount ELSE 0 END) AS dep,
            SUM(CASE WHEN trans_type='withdraw' THEN amount ELSE 0 END) AS wd
            FROM {$this->db->dbprefix('savings_account_transactions')}
            WHERE {$where}
        ";
        $tot = $this->db->query($sql_tot)->row();
        $period_totals = [
            'deposit'  => (float)($tot->dep ?? 0),
            'withdraw' => (float)($tot->wd  ?? 0),
            'net'      => (float)($tot->dep ?? 0) - (float)($tot->wd ?? 0),
        ];

        // Seed (lo que NO ves porque está más nuevo que la primera fila mostrada)
        $first = $rows[0];
        $conds_pg = [];
        $conds_pg[] = "(trans_date > ".$this->db->escape($first->trans_date)." OR (trans_date = ".$this->db->escape($first->trans_date)." AND transaction_id > ".(int)$first->transaction_id."))";
        if ($start_dt) $conds_pg[] = "trans_date >= ".$this->db->escape($start_dt);
        if ($end_dt)   $conds_pg[] = "trans_date <= ".$this->db->escape($end_dt);

        // ¡Importante!: ignoramos trans_type del filtro para saldo real
        $sum_newer_in_period = $this->_sum_signed($acc_id, implode(' AND ', $conds_pg), array_merge($filters, ['trans_type'=>null]));

        $seed    = $closing_balance - $sum_newer_in_period;
        $running = $seed;

        foreach ($rows as &$r) {
            $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
            $r->running_balance = $running;            // saldo luego de aplicar TODO lo más nuevo que no ves + lo ya aplicado arriba
            $running -= ($sign * (float)$r->amount);   // preparamos para la siguiente (más antigua)
        }
        unset($r);

        return [$rows, $opening_balance, $closing_balance, $period_totals];
    }

    /** Saldo justo ANTES de $start_dt (Y-m-d H:i:s) */
    private function _opening_balance_at($account_id, $start_dt)
    {
        $acc = $this->Savings_accounts_model->get((int)$account_id);
        $now_balance = (float)($acc->current_balance ?? 0);

        if (!$start_dt) return $now_balance; // sin fecha, devolvemos actual

        // Suma firmada de transacciones posteriores a $start_dt (no filtramos por tipo)
        $sum_newer = $this->_sum_signed((int)$account_id, "trans_date > ".$this->db->escape($start_dt), ['trans_type'=>null]);

        // Saldo al inicio = saldo actual - lo que pasó después de ese inicio
        return $now_balance - $sum_newer;
    }

    /** Transacciones del período [start_dt, end_dt] orden ASC */
    private function _tx_in_period_asc($account_id, $start_dt, $end_dt)
    {
        $this->db->from($this->db->dbprefix('savings_account_transactions'))
                ->where('savings_account_id', (int)$account_id)
                ->where('status', 1);
        if ($start_dt) $this->db->where('trans_date >=', $start_dt);
        if ($end_dt)   $this->db->where('trans_date <=', $end_dt);
        $this->db->order_by('trans_date','ASC')->order_by('transaction_id','ASC');
        return $this->db->get()->result();
    }

    /** Días exactos (UTC naive) entre dos timestamps Y-m-d H:i:s */
    private function _days_between($from, $to)
    {
        $t1 = strtotime($from);
        $t2 = strtotime($to);
        if ($t2 <= $t1) return 0.0;
        return ($t2 - $t1) / 86400.0;
    }

}
