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
        //$this->require_owner_auth = false;

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

        // justo después de construir $filters:
        $start_dt = !empty($filters['date_from']) ? $filters['date_from'].' 00:00:00' : null;
        $end_dt   = !empty($filters['date_to'])   ? $filters['date_to'].' 23:59:59' : null;


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
        
        // Si pidieron una página mayor al máximo, redirige a la última (evita URLs 'page=50' con mismo contenido)
        if ($req_page > $max_page) {
            $qs = $this->input->get(NULL, TRUE) ?: [];
            $qs['page'] = $max_page;
            redirect(current_url() . '?' . http_build_query($qs), 'location', 302);
            return;
        }

        // ahora sí, traer filas de la página correcta
        $rows = $this->Savings_account_transactions_model->get_all($filters, $limit, $offset);

        // inicializa variables usadas más abajo
        $saldo_inicial_periodo = null;
        $saldo_final_periodo   = null;

        // --- Saldos de apertura/cierre del PERÍODO (si hay cuenta seleccionada) ---
        if (!empty($filters['account_id'])) {
            $acc_id = (int)$filters['account_id'];

            // Saldo actual de la cuenta
            $acc = $this->Savings_accounts_model->get($acc_id);
            $now_balance = (float)($acc->current_balance ?? 0);

            // Cierre del período: saldo al final del rango seleccionado
            if ($end_dt) {
                // OJO: para saldo, NO aplicar otros filtros (branch, registered_by, etc.)
                $sum_newer_than_end = $this->_sum_signed(
                    $acc_id,
                    "trans_date > ".$this->db->escape($end_dt),
                    ['trans_type'=>null]
                );
                $saldo_final_periodo = $now_balance - $sum_newer_than_end;
            } else {
                // Sin fecha fin: el cierre es el saldo actual
                $saldo_final_periodo = $now_balance;
            }

            // Apertura del período: saldo justo antes de $start_dt
            if ($start_dt) {
                $saldo_inicial_periodo = $this->_opening_balance_at($acc_id, $start_dt);
            }
        }

        // Si ya existe balance_after persistido, úsalo directo para la columna "Saldo resultante"
        $__has_persisted = $this->_normalize_running_balance_with_persisted($rows);

        // NUEVO: poblar running_balance desde balance_after (o fallback)
        $rows = $this->_apply_balance_after($rows);

        // paginador
        $config = [
            'base_url'             => current_url(),
            'total_rows'           => $total,
            'per_page'             => $limit,

            'use_page_numbers'     => TRUE,
            'page_query_string'    => TRUE,
            'query_string_segment' => 'page',
            'reuse_query_string'   => TRUE,

            // 🔧 fuerza la página “clamp” que calculaste (evita que CI crea que sigues en ?page=50)
            'cur_page'             => $page,
        ];

        // (Opcional) preserva el resto de filtros en los enlaces
        $qs_all = $this->input->get(NULL, TRUE) ?: [];
        unset($qs_all['page']);
        $qs_keep = http_build_query(array_filter($qs_all, fn($v) => $v !== '' && $v !== null));
        if ($qs_keep !== '') {
            $config['suffix']    = '&' . $qs_keep;
            $config['first_url'] = current_url() . '?page=1&' . $qs_keep;
        }

        // Apariencia y usabilidad
        $config['num_links']        = 3; // cuántos números a cada lado
        $config['first_link']       = '« Primero';
        $config['last_link']        = 'Último »';
        $config['next_link']        = 'Siguiente ›';
        $config['prev_link']        = '‹ Anterior';

        // Contenedor <nav><ul>
        $config['full_tag_open']    = '<nav aria-label="Paginación"><ul class="pagination pagination-sm justify-content-end" style="margin:0">';
        $config['full_tag_close']   = '</ul></nav>';

        // Items <li>
        $config['num_tag_open']     = '<li class="page-item">';
        $config['num_tag_close']    = '</li>';

        $config['cur_tag_open']     = '<li class="page-item active" aria-current="page"><span class="page-link">';
        $config['cur_tag_close']    = '</span></li>';

        $config['next_tag_open']    = '<li class="page-item">';
        $config['next_tag_close']   = '</li>';

        $config['prev_tag_open']    = '<li class="page-item">';
        $config['prev_tag_close']   = '</li>';

        $config['first_tag_open']   = '<li class="page-item">';
        $config['first_tag_close']  = '</li>';

        $config['last_tag_open']    = '<li class="page-item">';
        $config['last_tag_close']   = '</li>';

        // Enlaces <a> con clase page-link
        $config['attributes']       = ['class' => 'page-link'];

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
        if (!$__has_persisted) {
            
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

        foreach ($rows as &$r) {
            $r->trans_type_label = $this->_label_tx_row($r);
        }
        unset($r);

        $this->load->view('savings_accounts/savings_account_transactions/index', $data);
    }

    /**
     * Suma firmada (depósito=+, retiro=-) para una cuenta.
     * - Por defecto hereda filtros de $filters (status, branch_id, registered_by, date_from/to).
     * - Puedes pasar $range_override = ['from' => 'Y-m-d H:i:s'|null, 'to' => 'Y-m-d H:i:s'|null]
     *   para forzar un rango específico y evitar mezclar con date_from/date_to del filtro.
     * - $extra_where debe venir de código interno controlado (no input de usuario).
     */
    private function _sum_signed(
        int $account_id,
        string $extra_where = '1=1',
        array $filters = [],
        array $range_override = null,
        bool $inherit_filters = true
    ) {
        $tb = $this->db->dbprefix('savings_account_transactions');

        // ---------- Memoization (evita recomputar con mismos parámetros) ----------
        static $cache = [];
        $cache_key = md5(json_encode([
            'acc'    => $account_id,
            'extra'  => $extra_where,
            'f'      => $inherit_filters ? $filters : [],
            'range'  => $range_override,
        ], JSON_UNESCAPED_UNICODE));
        if (isset($cache[$cache_key])) {
            return $cache[$cache_key];
        }

        // ---------- Build query ----------
        // Nota: usamos LOWER(trans_type) para blindar mayúsculas/minúsculas
        $this->db->select("
                COALESCE(SUM(
                    CASE WHEN LOWER(trans_type)='withdraw' THEN -amount ELSE amount END
                ),0) AS s
            ", false)
            ->from($tb)
            ->where('savings_account_id', $account_id);

        // Filtros heredados (si procede)
        if ($inherit_filters) {
            if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
                $this->db->where('status', (int)$filters['status']);
            }
            if (!empty($filters['branch_id']))     $this->db->where('branch_id', (int)$filters['branch_id']);
            if (!empty($filters['registered_by'])) $this->db->where('registered_by', (int)$filters['registered_by']);

            // Solo aplicamos date_from/date_to si NO nos pasaron override explícito
            if ($range_override === null) {
                if (!empty($filters['date_from'])) $this->db->where('trans_date >=', $filters['date_from'].' 00:00:00');
                if (!empty($filters['date_to']))   $this->db->where('trans_date <=', $filters['date_to'].' 23:59:59');
            }
        }

        // Rango explícito (no se mezcla con filtros)
        if (is_array($range_override)) {
            if (!empty($range_override['from'])) $this->db->where('trans_date >=', $range_override['from']);
            if (!empty($range_override['to']))   $this->db->where('trans_date <=', $range_override['to']);
        }

        // Extra WHERE (solo para cláusulas internas, controladas por nosotros)
        if ($extra_where && trim($extra_where) !== '1=1') {
            $this->db->where($extra_where, null, false);
        }

        $row = $this->db->get()->row();
        // Importante: MySQL DECIMAL viene como string; si prefieres evitar floats, devuelve (string)$row->s
        $sum = (float)($row->s ?? 0);

        // Cachea y retorna
        $cache[$cache_key] = $sum;
        return $sum;
    }

    public function owner_info($account_id)
    {
        $account_id = (int)$account_id;
        if ($account_id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false, 'error'=>'account_id inválido']));
        }

        // Memoization simple por request
        static $cache = [];
        if (isset($cache[$account_id])) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode($cache[$account_id]));
        }

        // Subselect determinístico para CI (id_no): último lead del titular
        // Si no tienes updated_at/created_at, usamos el mayor lead_id como “más reciente”.
        $tbl_sa   = $this->db->dbprefix('savings_accounts');
        $tbl_p    = $this->db->dbprefix('people');
        $tbl_lead = $this->db->dbprefix('leads');

        $sql = "
            SELECT 
                sa.savings_account_id,
                p.person_id,
                TRIM(CONCAT(COALESCE(p.first_name,''), ' ', COALESCE(p.last_name,''))) AS full_name,
                p.photo_url,
                (
                    SELECT l.id_no
                    FROM {$this->db->dbprefix('leads')} l
                    WHERE l.customer_id = sa.person_id
                    AND l.id_no IS NOT NULL AND l.id_no <> ''
                    LIMIT 1
                ) AS id_no
            FROM {$this->db->dbprefix('savings_accounts')} sa
            JOIN {$this->db->dbprefix('people')} p
            ON p.person_id = sa.person_id
            WHERE sa.savings_account_id = ?
            LIMIT 1
        ";

        $row = $this->db->query($sql, [(int)$account_id])->row();

        if (!$row) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['ok'=>false, 'error'=>'Cuenta no encontrada']));
        }

        // Limpieza opcional de CI a sólo dígitos (si prefieres conservar formato original, usa $row->id_no tal cual)
        $id_no_raw = (string)($row->id_no ?? '');
        $id_no_digits = preg_replace('/\D+/', '', $id_no_raw) ?: $id_no_raw;

        // Resolver foto a URL pública
        $photo_full = $this->_photo_url((int)$row->person_id, (string)($row->photo_url ?? ''));

        $out = [
            'ok'        => true,
            'person_id' => (int)$row->person_id,
            'full_name' => (string)$row->full_name,
            'id_no'     => $id_no_digits,
            'photo_url' => $photo_full,
        ];

        // Cache por request
        $cache[$account_id] = $out;

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

        // Cacheo de columnas (evita múltiples field_exists)
        static $colmap = null;
        if ($colmap === null) {
            $colmap = [
                'has_is_active' => $this->db->field_exists('is_active', 'savings_accounts'),
                'has_status'    => $this->db->field_exists('status', 'savings_accounts'),
                'has_disabled'  => $this->db->field_exists('disabled', 'savings_accounts'),
                'has_time_dep'  => $this->db->field_exists('time_deposit',  'savings_accounts'),
                'has_mat'       => $this->db->field_exists('maturity_date', 'savings_accounts'),
            ];
        }

        $data = [];
        // === Opciones de cuenta, a prueba de columnas faltantes ===
        $activeWhere = '';
        if ($colmap['has_is_active']) {
            $activeWhere = 'COALESCE(sa.is_active,1) = 1';
        } elseif ($colmap['has_status']) {
            $activeWhere = 'COALESCE(sa.status,1) = 1';
        } elseif ($colmap['has_disabled']) {
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
            $trans_type = strtolower((string)$this->input->post('trans_type', TRUE));
            $account_id = (int)$this->input->post('savings_account_id', TRUE);
            $amount     = (float)$this->input->post('amount', TRUE);
            $desc       = trim((string)$this->input->post('description', TRUE));
            $trans_dt   = (string)$this->input->post('trans_date', TRUE);
            // Normaliza $trans_dt a 'Y-m-d H:i:s' o now()
            if ($trans_dt) {
                $t = strtotime($trans_dt);
                $trans_dt = $t ? date('Y-m-d H:i:s', $t) : date('Y-m-d H:i:s');
            } else {
                $trans_dt = date('Y-m-d H:i:s');
            }

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

            if ($colmap['has_is_active']) {
                $selectParts[] = 'COALESCE(sa.is_active,1) AS is_active';
            } elseif ($colmap['has_status']) {
                $selectParts[] = 'COALESCE(sa.status,1) AS is_active';
            } elseif ($colmap['has_disabled']) {
                $selectParts[] = 'CASE WHEN COALESCE(sa.disabled,0)=0 THEN 1 ELSE 0 END AS is_active';
            } else {
                $selectParts[] = '1 AS is_active';
            }

            $selectParts[] = $colmap['has_time_dep'] ? 'COALESCE(sa.time_deposit,0) AS is_time_deposit' : '0 AS is_time_deposit';
            $selectParts[] = $colmap['has_mat'] ? 'sa.maturity_date' : 'NULL AS maturity_date';

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
                // usa el validador “puro” (no redirige ni setea flash por dentro)
                $chk = $this->_validate_transfer($account_id, $dst_id, $amount);
                if (!$chk['ok']) {
                    $this->session->set_flashdata('error', $chk['msg'] ?: 'No se pudo validar la transferencia.');
                    redirect(current_url());
                    return;
                }

                // Ejecutar la transferencia
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
                    // Voucher opcional (como ya venías haciendo)
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
                    $msg = 'No se pudo completar la transferencia.';
                }
                $this->session->set_flashdata('error', $msg);
                redirect(current_url());
                return;
            }

            // ====== DEPÓSITO / RETIRO ======
            // Campos sólo para depósito
            $dep_name = trim((string)($this->input->post('depositor_name', TRUE) ?? ''));
            $dep_doc  = trim((string)($this->input->post('depositor_document', TRUE) ?? ''));

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
   
    /** Helper privado: resuelve la URL de foto del titular con fallback seguro */
    private function _photo_url($person_id, $filename)
    {
        $placeholder = base_url('uploads/people/placeholder-80x80.png');

        // Normaliza entradas
        $pid = (int)$person_id;
        $fn  = trim((string)$filename);
        if ($fn === '') return $placeholder;

        // Si ya es URL absoluta, úsala
        if (preg_match('#^https?://#i', $fn)) {
            return $fn;
        }

        // Sanea nombre (sin ../ ni backslashes, sin prefijos con /)
        $safe = ltrim(str_replace(['..', '\\'], '', $fn), '/');

        // Cache simple por petición para no golpear disco repetidas veces
        static $cache = [];
        $ckey = $pid . ':' . $safe;
        if (array_key_exists($ckey, $cache)) {
            return $cache[$ckey];
        }

        // Candidatas en orden de preferencia
        $candidates = [];
        if ($pid > 0) $candidates[] = "uploads/profile-{$pid}/{$safe}";
        $candidates[] = "uploads/people/{$safe}";

        foreach ($candidates as $rel) {
            $abs = FCPATH . $rel;
            if (is_file($abs)) {
                return $cache[$ckey] = base_url($rel);
            }
        }

        // Fallback
        return $cache[$ckey] = $placeholder;
    }

    public function delete($id = NULL)
    {
        // 1) Id seguro
        if (is_null($id)) { $id = $this->uri->segment(4); }
        $id = (int)$id;
        if ($id <= 0) {
            $this->session->set_flashdata('error','ID inválido.');
            redirect('savings_accounts/savings_account_transactions'); return;
        }

        // 2) Debe existir
        $tx = $this->Savings_account_transactions_model->find($id);
        if (!$tx) {
            $this->session->set_flashdata('error','La transacción no existe o ya fue deshabilitada.');
            redirect('savings_accounts/savings_account_transactions'); return;
        }

        // 3) (Opcional pero recomendado) Solo permitir vía POST para tener CSRF
        if (strtoupper($this->input->server('REQUEST_METHOD') ?? '') !== 'POST') {
            $this->session->set_flashdata('error','Operación no permitida. Use el botón “Deshabilitar”.');
            redirect('savings_accounts/savings_account_transactions'); return;
        }

        // 4) Transferencias: evitar borrar solo una pata
        if (strtolower($tx->trans_type) === 'transfer') {
            $this->session->set_flashdata('error','Esta transacción es parte de una transferencia. Use la opción específica para anular transferencias.');
            redirect('savings_accounts/savings_account_transactions'); return;
        }

        // 5) Ejecutar soft-delete con auditoría mínima
        $actor_id = (int)($this->session->userdata('person_id') ?: 0);
        $reason   = trim((string)$this->input->post('reason') ?? 'Sin motivo');
        $ok = $this->Savings_account_transactions_model->delete($id, [
            'deleted_by' => $actor_id,
            'delete_reason' => $reason,
        ]);

        if ($ok) {
            $this->session->set_flashdata('success','Transacción deshabilitada.');
            // (E – próxima iteración) trigger para reflow si tenemos balance_after persistido
        } else {
            $this->session->set_flashdata('error','No fue posible deshabilitar la transacción.');
        }
        redirect('savings_accounts/savings_account_transactions');
    }

    /**
     * Valida una transferencia entre cuentas.
     * No hace redirects ni set_flashdata: SOLO devuelve ['ok'=>bool,'msg'=>string].
     */
    private function _validate_transfer($src_id, $dst_id, $amount)
    {
        $src_id = (int)$src_id;
        $dst_id = (int)$dst_id;
        $amount = (float)$amount;

        // 0) Reglas básicas
        if ($amount <= 0) {
            return ['ok'=>false, 'msg'=>'El monto debe ser mayor a cero.'];
        }
        if ($src_id <= 0 || $dst_id <= 0) {
            return ['ok'=>false, 'msg'=>'Debes seleccionar cuentas válidas.'];
        }
        if ($src_id === $dst_id) {
            return ['ok'=>false, 'msg'=>'La cuenta de origen y destino no pueden ser la misma.'];
        }

        // 1) Traer estado de ambas cuentas con columnas "flexibles"
        //    (igual criterio que usaste en form(): is_active / plazo fijo / vencimiento / saldo actual)
        $hasIsActive = $this->db->field_exists('is_active', 'savings_accounts')
                    || $this->db->field_exists('status',   'savings_accounts')
                    || $this->db->field_exists('disabled', 'savings_accounts');

        $selectActive = '1 AS is_active';
        if ($this->db->field_exists('is_active','savings_accounts')) {
            $selectActive = 'COALESCE(sa.is_active,1) AS is_active';
        } elseif ($this->db->field_exists('status','savings_accounts')) {
            $selectActive = 'COALESCE(sa.status,1) AS is_active';
        } elseif ($this->db->field_exists('disabled','savings_accounts')) {
            $selectActive = 'CASE WHEN COALESCE(sa.disabled,0)=0 THEN 1 ELSE 0 END AS is_active';
        }

        $selectTimeDep = $this->db->field_exists('time_deposit','savings_accounts')
            ? 'COALESCE(sa.time_deposit,0) AS is_time_deposit'
            : '0 AS is_time_deposit';

        $selectMaturity = $this->db->field_exists('maturity_date','savings_accounts')
            ? 'sa.maturity_date'
            : 'NULL AS maturity_date';

        // saldo actual siempre
        $selectBalance = 'COALESCE(sa.current_balance,0) AS current_balance';

        // mínimo requerido (si existiera alguna columna de política; si no, 0)
        $selectMinReq = $this->db->field_exists('min_transfer_amount','savings_accounts')
            ? 'COALESCE(sa.min_transfer_amount,0) AS min_transfer_amount'
            : '0 AS min_transfer_amount';

        $tb = $this->db->dbprefix('savings_accounts');

        $src = $this->db->query("
            SELECT sa.savings_account_id, {$selectActive}, {$selectTimeDep}, {$selectMaturity},
                {$selectBalance}, {$selectMinReq}
            FROM {$tb} sa
            WHERE sa.savings_account_id = ?
            LIMIT 1
        ", [$src_id])->row();

        $dst = $this->db->query("
            SELECT sa.savings_account_id, {$selectActive}, {$selectTimeDep}, {$selectMaturity},
                {$selectBalance}, {$selectMinReq}
            FROM {$tb} sa
            WHERE sa.savings_account_id = ?
            LIMIT 1
        ", [$dst_id])->row();

        if (!$src || !$dst) {
            return ['ok'=>false, 'msg'=>'No se encontró información de las cuentas seleccionadas.'];
        }

        // 2) Activas
        if ($hasIsActive) {
            if ((int)$src->is_active !== 1) return ['ok'=>false,'msg'=>'La cuenta de origen está inactiva.'];
            if ((int)$dst->is_active !== 1) return ['ok'=>false,'msg'=>'La cuenta de destino está inactiva.'];
        }

        // 3) Origen: plazo fijo → debe estar vencido
        if ((int)$src->is_time_deposit === 1) {
            $hoy   = date('Y-m-d');
            $vence = $src->maturity_date ? substr($src->maturity_date, 0, 10) : null;
            if (!$vence || $vence > $hoy) {
                return ['ok'=>false,'msg'=>'La cuenta de origen es de plazo fijo y aún no ha vencido.'];
            }
        }

        // 4) Saldo suficiente (saldo actual - mínimo requerido >= monto)
        $min_req = (float)$src->min_transfer_amount;
        $disp    = (float)$src->current_balance;
        if ($disp < ($amount + $min_req)) {
            $det = sprintf('Saldo disponible %.2f; mínimo requerido %.2f; monto %.2f.', $disp, $min_req, $amount);
            return ['ok'=>false,'msg'=>"Saldo insuficiente para la transferencia. {$det}"];
        }

        return ['ok'=>true, 'msg'=>'OK'];
    }

    public function store_transfer()
    {
        // Asegura JSON siempre
        $this->output->set_content_type('application/json; charset=UTF-8');

        // (Opcional pero recomendado)
        if (method_exists($this->input, 'is_ajax_request') && !$this->input->is_ajax_request()) {
            $this->output->set_status_header(400);
            echo json_encode(['ok'=>false, 'message'=>'Solicitud inválida.']);
            return;
        }

        try {
            // Leer POST de forma consistente
            $src_id = (int)$this->input->post('src_account_id', true);
            $dst_id = (int)$this->input->post('dst_account_id', true);
            $amount = (float)$this->input->post('amount', true);
            // Normalizar descripción (máx. 255 por si el schema lo limita)
            $desc   = substr(trim((string)$this->input->post('description')), 0, 255);

            // Validaciones mínimas de forma de datos
            if ($src_id <= 0 || $dst_id <= 0 || $src_id === $dst_id) {
                $this->output->set_status_header(400);
                echo json_encode(['ok'=>false, 'message'=>'Cuentas de origen/destino inválidas.']);
                return;
            }
            if ($amount <= 0) {
                $this->output->set_status_header(400);
                echo json_encode(['ok'=>false, 'message'=>'El monto debe ser mayor a 0.']);
                return;
            }

            // Reglas de negocio centralizadas
            $chk = $this->_validate_transfer($src_id, $dst_id, $amount);
            if (!$chk['ok']) {
                $this->output->set_status_header(400);
                echo json_encode(['ok'=>false, 'message'=>($chk['msg'] ?? 'No se pudo validar la transferencia.')]);
                return;
            }

            // Ejecutar operación (armonizado con form(): create_transfer)
            $res = $this->Savings_account_transactions_model->create_transfer(
                $src_id,
                $dst_id,
                $amount,
                $desc,
                (int)($this->session->userdata('branch_id') ?? 0),
                (int)($this->session->userdata('person_id') ?? 0)
            );

            if (!$res || empty($res['withdraw_id']) || empty($res['deposit_id'])) {
                $msg = property_exists($this->Savings_account_transactions_model,'last_error')
                    ? ($this->Savings_account_transactions_model->last_error ?: '')
                    : '';
                if ($msg === '') $msg = 'No se pudo completar la transferencia.';
                $this->output->set_status_header(400);
                echo json_encode(['ok'=>false, 'message'=>$msg]);
                return;
            }

            // OK
            $this->output->set_status_header(200);
            echo json_encode([
                'ok'      => true,
                'message' => 'Transferencia realizada correctamente.',
                'data'    => $res, // ['withdraw_id'=>..., 'deposit_id'=>...]
            ]);
            return;

        } catch (Throwable $e) {
            $this->output->set_status_header(500);
            echo json_encode(['ok'=>false, 'message'=>'Error interno: '.$e->getMessage()]);
            return;
        }
    }

    public function voucher($transaction_id)
    {
        $tx_id = (int)$transaction_id;

        // 1) Traer fila ya unida desde el modelo
        $row = $this->Savings_account_transactions_model->get_one_with_joins($tx_id);
        if (!$row) {
            show_error('Transacción no encontrada.', 404, 'Voucher');
            return;
        }

        // 2) Sucursal: sesión → por id → la del join
        $branch_name = (string)($this->session->userdata('branch_name') ?: '');
        if ($branch_name === '' && ($bid = (int)($this->session->userdata('branch_id') ?: 0))) {
            if ($this->db->table_exists($this->db->dbprefix('branches'))) {
                $b = $this->db->select('branch_name')
                    ->from($this->db->dbprefix('branches'))
                    ->where('id', $bid)->get()->row();
                if ($b) $branch_name = (string)$b->branch_name;
            }
        }
        if ($branch_name === '' && !empty($row->branch_name)) {
            $branch_name = (string)$row->branch_name;
        }
        $row->branch_name = $branch_name;

        // 3) id_no
        $personId = (int)($row->person_id ?? 0);
        if ($personId > 0 && method_exists($this, '_person_id_no')) {
            $row->id_no = (string)$this->_person_id_no($personId);
        } else {
            $row->id_no = (string)($row->id_no ?? '');
        }

        // 4) Contraparte (si es transferencia)
        if (!empty($row->counterparty_account_id)) {
            $cp = $this->db->select('sa.account_number, p.first_name, p.last_name')
                ->from($this->db->dbprefix('savings_accounts').' sa')
                ->join($this->db->dbprefix('people').' p','p.person_id=sa.person_id','left')
                ->where('sa.savings_account_id', (int)$row->counterparty_account_id)
                ->limit(1)->get()->row();

            if ($cp) {
                $row->counterparty_account_number = (string)($cp->account_number ?? '');
                $row->counterparty_owner = trim((string)($cp->first_name ?? '').' '.(string)($cp->last_name ?? ''));
            } else {
                $row->counterparty_account_number = '';
                $row->counterparty_owner = '';
            }
        }

        // 5) Literal
        $row->amount_literal = $this->_amount_literal_bs((float)($row->amount ?? 0));

        // 6) PDF
        if (!isset($this->pdf) || !is_object($this->pdf)) {
            $this->load->library('pdf');
        }

        // *** ESTA es la parte que cambia ***
        // Formato: [mode, format, default_font, margin_left, margin_right, margin_top, margin_bottom, margin_header, margin_footer, orientation]
        // Usamos Letter, pero con 14mm de margen izq/der.
        $mpdf = $this->pdf->load([
            'utf-8',          // mode
            'Letter',         // format
            '',               // default font
            14,               // margin_left
            14,               // margin_right
            10,               // margin_top
            8,                // margin_bottom
            0,                // margin_header
            0,                // margin_footer
            'P'               // orientation
        ]);

        $html = $this->load->view('savings_accounts/vouchers/voucher_simple', ['tx'=>$row], TRUE);
        if (!is_string($html)) { $html = (string)$html; }

        $old = error_reporting();
        error_reporting($old & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        while (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_end_clean(); }

        $mpdf->WriteHTML($html);

        $filename = 'voucher_'
            . ($row->account_number ?: 'CTA')
            . '_' . (int)($row->owner_id ?? 0)
            . '_' . date('Ymd_His', strtotime($row->trans_date))
            . '.pdf';

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

        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) { @ob_end_clean(); }
        }

        // ---------- 3) Traer datos ----------
        $rows = $this->Savings_account_transactions_model->get_all($filters, 10000, 0);

        // Totales de depósitos y retiros (solo esos dos tipos)
        $sum_dep = 0.0;
        $sum_ret = 0.0;
        foreach ($rows as $r) {
            $tt = strtolower((string)$r->trans_type);
            if ($tt === 'deposit')  { $sum_dep += (float)$r->amount; }
            if ($tt === 'withdraw') { $sum_ret += (float)$r->amount; }
        }

        // Normaliza running_balance desde balance_after si está persistido
        $__has_persisted = $this->_normalize_running_balance_with_persisted($rows);
        $rows = $this->_apply_balance_after($rows); // si ya lo tienes, mantiene compatibilidad

        // ¿Calculamos running balance? Solo si hay UNA cuenta seleccionada y no hay persisted
        if (!$__has_persisted) {
            $running_by_row = [];
            if (!empty($filters['account_id'])) {
                $acc_id = (int)$filters['account_id'];
                $acc    = $this->Savings_accounts_model->get($acc_id);
                $now_balance = (float)($acc->current_balance ?? 0);

                $end_dt = !empty($filters['date_to']) ? $filters['date_to'].' 23:59:59' : null;
                if ($end_dt) {
                    $sum_newer_than_end = $this->_sum_signed($acc_id, "trans_date > ".$this->db->escape($end_dt), $filters);
                    $closing_period = $now_balance - $sum_newer_than_end;
                } else {
                    $closing_period = $now_balance;
                }

                if (!empty($rows)) {
                    $first = $rows[0];
                    $conds = [];
                    $conds[] = "(trans_date > ".$this->db->escape($first->trans_date)
                            ." OR (trans_date = ".$this->db->escape($first->trans_date)
                            ." AND transaction_id > ".(int)$first->transaction_id."))";

                    if (!empty($filters['date_from'])) $conds[] = "trans_date >= ".$this->db->escape($filters['date_from'].' 00:00:00');
                    if ($end_dt)                         $conds[] = "trans_date <= ".$this->db->escape($end_dt);

                    $sum_newer_in_period = $this->_sum_signed($acc_id, implode(' AND ', $conds), array_merge($filters, ['trans_type'=>null]));
                    $seed = $closing_period - $sum_newer_in_period;

                    $running = $seed;
                    foreach ($rows as &$r) {
                        $r->running_balance = $running; // saldo después de esa transacción
                        $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
                        $running -= ($sign * (float)$r->amount);
                    }
                    unset($r);
                }
            }
        }

        // ---------- 4) Enviar CSV ----------
        header('Content-Type: text/csv; charset=UTF-8');
        $fname_extra = '';
        if (!empty($filters['account_id'])) {
            $acc = $this->Savings_accounts_model->get((int)$filters['account_id']);
            if ($acc) {
                $accno = (string)($acc->account_number ?? '');
                $pid   = (int)($acc->person_id ?? 0);
                $fname_extra = '_'.$accno.'_'.$pid;
            }
        }
        header('Content-Disposition: attachment; filename="transacciones'.$fname_extra.'_'.date('Ymd').'.csv"');

        // BOM para Excel (UTF-8)
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output','w');
        $sep = ';'; // usa ; si tu Excel regional lo prefiere

        // Cabecera
        fputcsv($out, ['Fecha','Cuenta','Cliente','Tipo','Monto','Saldo resultante','Descripción'], $sep);

        // Detalle
        foreach ($rows as $r) {
            fputcsv($out, [
                date('d/m/Y H:i', strtotime($r->trans_date)),
                $r->account_number,
                trim(($r->first_name ?? '').' '.($r->last_name ?? '')),
                $this->_label_tx_row($r),
                number_format((float)$r->amount, 2, '.', ''),
                ($r->running_balance !== null ? number_format((float)$r->running_balance, 2, '.', '') : ''),
                (string)($r->description ?? '')
            ], $sep);
        }

        // --- Fila de totales (sin tocar la vista; solo CSV/PDF) ---
        // Línea en blanco opcional
        fputcsv($out, ['', '', '', '', '', '', ''], $sep);

        // Misma cantidad de columnas:
        // - "Monto" => total de Depósitos
        // - "Saldo resultante" => total de Retiros
        fputcsv($out, [
            '',                         // Fecha
            'TOTALES',                         // Cuenta
            'Depósitos: ',                         // Cliente
            number_format($sum_dep, 2, '.', ''),  // Monto => Depósitos
            'Retiros: ',                  // Tipo
            number_format($sum_ret, 2, '.', ''),  // Saldo resultante => Retiros
            ''
        ], $sep);

        fclose($out);

        // ---------- 5) Restaurar nivel de errores y salir ----------
        error_reporting($old_level);
        exit;
    }

    public function export_pdf()
    {
        $filters = $this->input->get(NULL, TRUE);
        $rows    = $this->Savings_account_transactions_model->get_all($filters, 1000, 0);

        $__has_persisted = $this->_normalize_running_balance_with_persisted($rows);
        $rows = $this->_apply_balance_after($rows);
        // Running balance si hay una cuenta
        $opening = $closing = $totals = null;
        if (!$__has_persisted) {
            if (!empty($filters['account_id'])) {
                list($rows, $opening, $closing, $totals) = $this->_with_running_balance($rows, $filters);
            } else {
                foreach ($rows as &$r) { $r->running_balance = null; } unset($r);
            }
        }

        $sum_dep = 0.0;
        $sum_ret = 0.0;
        foreach ($rows as $r) {
            $tt = strtolower((string)$r->trans_type);
            if ($tt === 'deposit')  { $sum_dep += (float)$r->amount; }
            if ($tt === 'withdraw') { $sum_ret += (float)$r->amount; }
        }
        
        // Cargar mPDF legacy
        $this->load->library('pdf');
        $mpdf = $this->pdf->load(['utf-8','A4','',10,10,10,10,6,3,'P']);

        // Blindaje mPDF legacy
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) { @ob_end_clean(); }
        }

        // Encabezado amigable
        $title = 'Reporte de Transacciones';
        $subtitle = '';
        $acc = null;
        $filename = 'transacciones_'.date('Ymd').'.pdf';
        if (!empty($filters['account_id']) && $acc) {
            $filename = sprintf('transacciones_%s_%s_%s.pdf',
                $acc->account_number, $acc->person_id, date('Ymd'));
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
                .  '<td>'.htmlspecialchars($this->_label_tx_row($r), ENT_QUOTES, 'UTF-8').'</td>'
                .  '<td align="right">'.number_format((float)$r->amount, 2).'</td>'
                .  '<td align="right">'.($r->running_balance!==null ? number_format((float)$r->running_balance,2) : '').'</td>'
                .  '<td>'.htmlspecialchars((string)$r->description, ENT_QUOTES, 'UTF-8').'</td>'
                .  '</tr>';
        }
        $html .= '<tr style="font-weight:bold;">'
                .  '<td colspan="4" style="text-align:right;border-top:1px solid #000;">TOTALES</td>'
                .  '<td style="text-align:right;border-top:1px solid #000;">Depósitos: '.number_format($sum_dep,2,'.',',').'</td>'   // Depósitos en "Monto"
                .  '<td style="text-align:right;border-top:1px solid #000;">Retiros: '.number_format($sum_ret,2,'.',',').'</td>'   // Retiros en "Saldo resultante"
                .  '</tr>';
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
        //$params = ['utf-8','Letter','','',8,8,10,10,0,0,'P'];
        //$params = '"en-GB-x","A4","","",8,8,8,8,4,3,"'.($orientation === 'L' ? 'L' : 'P').'"';
        $params = '"utf-8","Letter","","",8,8,10,10,0,0,"'.($orientation === 'L' ? 'L' : 'P').'"';
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

    private function _apply_balance_after(array $rows) {
        // 1) Si ya viene balance_after, úsalo directo
        $has_any = false;
        foreach ($rows as $r) {
            if (isset($r->balance_after) && $r->balance_after !== null) {
                $r->running_balance = (float)$r->balance_after;
                $has_any = true;
            }
        }
        if ($has_any) return $rows;

        // 2) Fallback (histórico sin backfill): correr acumulado por cuenta (orden DESC)
        $seed = [];
        foreach ($rows as $r) {
            $aid = (int)$r->savings_account_id;
            if (!array_key_exists($aid, $seed)) {
                // Primer saldo mostrado para esta cuenta: cerrar con el saldo actual o con saldo a "date_to" si vino
                $acc = $this->Savings_accounts_model->get($aid);
                $seed[$aid] = (float)($acc->current_balance ?? 0);
            }
            $r->running_balance = $seed[$aid];
            $sign = (strtolower($r->trans_type) === 'withdraw') ? -1 : 1;
            $seed[$aid] = round($seed[$aid] - $sign * (float)$r->amount, 2);
        }
        return $rows;
    }

    /** Si las filas traen balance_after, lo copia a running_balance y devuelve TRUE.
     *  Si no hay balance_after (o todo es NULL), devuelve FALSE y no toca nada.
     */
    private function _normalize_running_balance_with_persisted(&$rows)
    {
        if (empty($rows) || !isset($rows[0])) return FALSE;

        $has = FALSE;
        foreach ($rows as $r) {
            if (property_exists($r, 'balance_after') && $r->balance_after !== null) { $has = TRUE; break; }
        }
        if (!$has) return FALSE;

        foreach ($rows as &$r) {
            $r->running_balance = (float)$r->balance_after;
        }
        unset($r);
        return TRUE;
    }

    /** Traduce el tipo de transacción a etiqueta en español */
    private function _label_trans_type($t) {
        $t = strtolower((string)$t);
        $map = [
            'deposit'    => 'Depósito',
            'withdraw'   => 'Retiro',
            'transfer'   => 'Transferencia',
            'interest'   => 'Interés',
            'int'        => 'Interés',
            'aju'        => 'Ajuste',
            'adjust'     => 'Ajuste',
            'fee'        => 'Comisión',
        ];
        return $map[$t] ?? ucfirst($t);
    }

    // ==================== PREVIEW & PRINT VOUCHER ====================

    /** Devuelve el HTML del voucher (para el modal de vista previa) */
    // controllers/Savings_account_transactions.php
    public function voucher_preview($transaction_id)
    {
        $tx = $this->Savings_account_transactions_model->get_one_with_joins((int)$transaction_id);
        if (!$tx) { show_404(); return; }

        // Sucursal: primero sesión, luego la propia fila
        $branch_name = (string)($this->session->userdata('branch_name') ?: '');
        if ($branch_name === '' && ($bid = (int)($this->session->userdata('branch_id') ?: 0))) {
            if ($this->db->table_exists($this->db->dbprefix('branches'))) {
                $b = $this->db->select('branch_name')
                    ->from($this->db->dbprefix('branches'))
                    ->where('id', $bid)->get()->row();
                if ($b) $branch_name = (string)$b->branch_name;
            }
        }
        $tx->branch_name = $branch_name;

        // id_no (respeta el del modelo; si tienes helper, úsalo)
        $personId = (int)($tx->person_id ?? 0);
        if ($personId > 0 && method_exists($this, '_person_id_no')) {
            $tx->id_no = (string)$this->_person_id_no($personId);
        } else {
            $tx->id_no = (string)($tx->id_no ?? '');
        }

        // Datos de contraparte (si es transferencia)
        if (!empty($tx->counterparty_account_id)) {
            $cp = $this->db->select('sa.account_number, p.first_name, p.last_name')
                ->from($this->db->dbprefix('savings_accounts').' sa')
                ->join($this->db->dbprefix('people').' p', 'p.person_id=sa.person_id', 'left')
                ->where('sa.savings_account_id', (int)$tx->counterparty_account_id)
                ->limit(1)->get()->row();
            if ($cp) {
                $tx->counterparty_account_number = (string)($cp->account_number ?? '');
                $tx->counterparty_owner = trim((string)($cp->first_name ?? '').' '.(string)($cp->last_name ?? ''));
            } else {
                $tx->counterparty_account_number = '';
                $tx->counterparty_owner = '';
            }
        }

        // Monto en literal (mismo helper que usa voucher)
        $tx->amount_literal = $this->_amount_literal_bs((float)($tx->amount ?? 0));

        // Renderiza la MISMA vista del PDF (sin mPDF)
        $this->load->view(
            'savings_accounts/vouchers/voucher_simple',
            ['tx' => $tx, 'is_preview' => true]   // <<— banderita
        );
    }

    private function _amount_literal_bs($amount): string
    {
        if (!function_exists('monto_literal_bs')) {
            $this->load->helper('util'); // carga application/helpers/util_helper.php
        }
        try {
            $txt = (string) monto_literal_bs((float)$amount);
            return trim($txt) !== '' ? $txt : number_format((float)$amount, 2, '.', '').' (Bolivianos)';
        } catch (\Throwable $e) {
            return number_format((float)$amount, 2, '.', '').' (Bolivianos)';
        }
    }

    /* Lee branding desde app_config (tabla key/value) y arma la URL pública del logo.
     * Claves preferidas: app_logo, app_brand_name.
     * Fallbacks: company_logo, company.
     */
    private function _get_branding()
    {
        $tbl = $this->db->dbprefix('app_config'); // c19_app_config

        // Helper interno para leer una clave
        $get = function($key, $default = '') use ($tbl) {
            $row = $this->db->select('value')->from($tbl)->where('key', $key)->get()->row();
            return $row ? (string)$row->value : $default;
        };

        // Lee claves (con fallback a nombres "clásicos")
        $logo_file = $get('app_logo', $get('company_logo', 'logo_demo.png'));
        $brand     = $get('app_brand_name', $get('company', ''));

        // Construye la URL pública al logo (carpeta que nos diste)
        $logo_url = base_url('uploads/app/' . ltrim($logo_file, '/'));

        // (Opcional) Validar existencia física:
        // $abs = FCPATH.'uploads/app/'.ltrim($logo_file,'/');
        // if (!is_file($abs)) { $logo_url = base_url('uploads/people/placeholder-80x80.png'); }

        // Fallback si por alguna razón quedó vacío
        if ($logo_file === '' || $logo_file === null) {
            $logo_url = base_url('uploads/people/placeholder-80x80.png');
        }

        return [
            'brand_name' => $brand ?: '',
            'logo_url'   => $logo_url,
        ];
    }

    private function _cfg($key, $default = '')
    {
        $tbl = $this->db->dbprefix('app_config'); // c19_app_config
        $row = $this->db->select('value')->from($tbl)->where('key', $key)->get()->row();
        return $row ? (string)$row->value : $default;
    }

    private function _person_id_no(int $person_id): string
    {
        $sql = "
            SELECT l.id_no
            FROM {$this->db->dbprefix('leads')} l
            WHERE l.customer_id = ?
            AND l.id_no IS NOT NULL AND l.id_no <> ''
            LIMIT 1
        ";
        $row = $this->db->query($sql, [$person_id])->row();
        return (string)($row->id_no ?? '');
    }

    /** Etiqueta de tipo considerando transferencias (no rompe lo existente) */
    private function _label_tx_row($r): string
    {
        // Si viene marcado como transferencia, etiqueta específica
        if (!empty($r->transfer_group_id)) {
            $role = strtolower((string)($r->transfer_kind ?? ''));
            if ($role === 'withdraw') return 'Transferencia — Retiro';
            if ($role === 'deposit') return 'Transferencia — Depósito';
            return 'Transferencia';
        }
        // Fallback: etiqueta clásica
        return $this->_label_trans_type((string)$r->trans_type);
    }

    private function _branch_name_by_id(int $branch_id): string
    {
        if ($branch_id <= 0) return '';
        $row = $this->db->select('branch_name')
                        ->from($this->db->dbprefix('branches'))
                        ->where('id', $branch_id)
                        ->limit(1)
                        ->get()->row();
        return (string)($row->branch_name ?? '');
    }

}
