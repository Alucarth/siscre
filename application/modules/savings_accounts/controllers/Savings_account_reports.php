<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_reports extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Sidebar/header
        $this->load->model('Employee');
        $this->load->model('Module');

        $user_info = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) redirect('login');

        $allowed_modules = $this->Module->get_allowed_modules($user_info->person_id);
        $messages = $alerts = [];
        $this->load->vars(compact('user_info','allowed_modules','messages','alerts'));

        // Models necesarios
        $this->load->model('savings_accounts/Savings_accounts_model');
        $this->load->model('savings_accounts/Savings_account_types_model');
        $this->load->database();
        $this->load->helper(['url','form']);
    }

    public function index()
    {
        // Redirige o delega a tu pantalla por defecto
        return $this->daily_summary(); 
        // o: redirect('savings_accounts/savings_account_reports/daily_summary');
    }

    /* =======================================================
       1) Resumen diario (empresa)
       GET ?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&branch_id=
       ======================================================= */

    public function daily_summary()
    {
        $data['active_tab'] = 'daily';

        // --- filtros coherentes (fecha única) ---
        $date      = $this->input->get('date', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);
        if (empty($date)) $date = date('Y-m-d');

        // --- sucursales (map id => nombre) ---
        $rows_br = $this->db->select('id, branch_name')
                            ->from($this->db->dbprefix('branches'))
                            ->order_by('branch_name')->get()->result();
        $branch_options = ['' => '— Todas —'];
        foreach ($rows_br as $r) $branch_options[$r->id] = $r->branch_name;

        // --- WHERE: fecha exacta; status solo si existe la columna ---
        $where  = ["DATE(tx.trans_date) = ?"];
        $params = [$date];

        if ($this->db->field_exists('status', $this->db->dbprefix('savings_account_transactions'))) {
            $where[] = "tx.status = 1";
        }

        if (!empty($branch_id)) {
            $where[]  = "tx.branch_id = ?";
            $params[] = (int)$branch_id;
        }

        $sql = "
            SELECT
            DATE(tx.trans_date) AS tx_date,
            tx.branch_id,
            SUM(CASE WHEN tx.trans_type='deposit'  THEN tx.amount ELSE 0 END) AS deposits,
            SUM(CASE WHEN tx.trans_type='withdraw' THEN tx.amount ELSE 0 END) AS withdraws,
            SUM(CASE WHEN tx.trans_type='transfer' THEN tx.amount ELSE 0 END) AS transfers,
            SUM(CASE
                    WHEN tx.trans_type='deposit'  THEN  tx.amount
                    WHEN tx.trans_type='withdraw' THEN -tx.amount
                    ELSE 0
                END) AS net_cash
            FROM {$this->db->dbprefix('savings_account_transactions')} tx
            WHERE ".implode(' AND ', $where)."
            GROUP BY DATE(tx.trans_date), tx.branch_id
            ORDER BY tx.branch_id ASC
        ";

        $rows = $this->db->query($sql, $params)->result();

        // --- totales ---
        $totals = ['deposits'=>0.0,'withdraws'=>0.0,'transfers'=>0.0,'net_cash'=>0.0];
        foreach ($rows as $r) {
            $totals['deposits']  += (float)$r->deposits;
            $totals['withdraws'] += (float)$r->withdraws;
            $totals['transfers'] += (float)$r->transfers;
            $totals['net_cash']  += (float)$r->net_cash;
        }

        // --- datos para la vista (lo que tu vista ya usa) ---
        $data['filters']        = ['date'=>$date, 'branch_id'=>$branch_id];
        $data['branch_options'] = $branch_options;
        $data['rows']           = $rows;
        $data['totals']         = $totals;

        $this->load->view('savings_accounts/reports/daily_summary', $data);
    }

    // 1) Filtros para Intereses (rango)
    private function _interest_filters()
    {
        $date_from = $this->input->get('date_from', TRUE);
        $date_to   = $this->input->get('date_to', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);

        // por defecto: mes actual
        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to   = date('Y-m-t');
        }

        return compact('date_from','date_to','branch_id');
    }

    // 2) Consulta de intereses (una sola fuente para pantalla/CSV/PDF)
    private function _interest_rows($date_from, $date_to, $branch_id)
    {
        $dbp = $this->db->dbprefix;

        // WHERE y params
        $where  = ["DATE(tx.trans_date) BETWEEN ? AND ?"];
        $params = [$date_from, $date_to];

        // si tu tabla tiene status, lo aplicamos sin romper si no existe
        if ($this->db->field_exists('status', $dbp.'savings_account_transactions')) {
            $where[] = "tx.status = 1";
        }
        if (!empty($branch_id)) {
            $where[]  = "tx.branch_id = ?";
            $params[] = (int)$branch_id;
        }

        // NOTA DE ASUNCIÓN:
        // - tx.trans_type = 'interest' (si usas otro literal, dímelo y lo cambio)
        // - acc.id = tx.account_id
        // - acc.type_id -> types.id
        // - acc.customer_id -> customers.person_id -> people (nombres)
        $sql = "
            SELECT
                acc.account_number                                AS account_number,
                CONCAT(p.first_name, ' ', p.last_name)            AS owner,
                t.type_name                                       AS type_name,
                t.apy                                             AS apy,
                COALESCE(SUM(CASE WHEN tx.trans_type = 'interest'
                                THEN tx.amount ELSE 0 END),0)   AS interest
            FROM {$dbp}savings_accounts acc
            JOIN {$dbp}savings_account_types t
                ON t.id = acc.type_id
            JOIN {$dbp}customers c
                ON c.person_id = acc.customer_id
            JOIN {$dbp}people p
                ON p.person_id = c.person_id
            LEFT JOIN {$dbp}savings_account_transactions tx
                ON tx.account_id = acc.id
            " . (!empty($where) ? "WHERE " . implode(' AND ',$where) : "") . "
            GROUP BY acc.id, acc.account_number, owner, t.type_name, t.apy
            ORDER BY owner ASC, acc.account_number ASC
        ";

        return $this->db->query($sql, $params)->result();
    }

    /* =======================================================
       2) Intereses por cuenta en período
       GET ?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&branch_id=
       ======================================================= */
    public function interest_summary()
    {
        $data['active_tab'] = 'interest';
        $date_from = $this->input->get('date_from', TRUE);
        $date_to   = $this->input->get('date_to', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);

        // Rango por defecto: mes actual
        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to   = date('Y-m-t');
        }

        // Catálogo de sucursales
        $branches = $this->db->select('id, branch_name')
                             ->from($this->db->dbprefix('branches'))
                             ->order_by('branch_name')->get()->result();
        $branch_options = ['' => '— Todas —'];
        foreach ($branches as $b) $branch_options[$b->id] = $b->branch_name;

        // Traer cuentas (opcionalmente por sucursal)
        $this->db->select('sa.*, sat.name AS type_name, sat.interest_rate_apy, p.first_name, p.last_name');
        $this->db->from($this->db->dbprefix('savings_accounts').' sa');
        $this->db->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id = sa.savings_account_type_id','left');
        $this->db->join($this->db->dbprefix('people').' p','p.person_id = sa.person_id','left');
        if (!empty($branch_id)) {
            $this->db->where('sa.branch_id', (int)$branch_id);
        }
        $this->db->where('sa.status', 1);
        $accounts = $this->db->get()->result();

        $this->load->model('Savings_accounts_model');

        // Helper local: calcula interés del período usando el APY (simple / 365)
        $calc_interest = function($account_id, $apy, $from, $to) {
            // Si tienes ya un helper establecido, úsalo aquí.
            // Implementación sencilla: interés = Σ (saldo_día * apy/365) para cada día del período.
            // Para ser “ligero” en dev, aproximamos con saldo al cierre de cada movimiento.
            // Nota: asume MySQL 5.7 (sin ventanas).
            $from_dt = $from.' 00:00:00';
            $to_dt   = $to.' 23:59:59';

            // 1) saldo al inicio del período
            $CI =& get_instance();
            $acc = $CI->Savings_accounts_model->get((int)$account_id);
            $now_balance = (float)($acc->current_balance ?? 0);

            // suma de eventos posteriores al inicio => para retroceder al saldo de apertura
            $sum_newer = $CI->db->query("
                SELECT COALESCE(SUM(CASE WHEN trans_type='withdraw' THEN -amount
                                         WHEN trans_type='deposit' THEN amount
                                         ELSE 0 END),0) AS s
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ? AND trans_date > ?
            ", [$account_id, $from_dt])->row()->s ?? 0.0;

            $opening = $now_balance - (float)$sum_newer;

            // 2) Traer movimientos del período (ASC)
            $tx = $CI->db->query("
                SELECT transaction_id, trans_date, trans_type, amount
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ?
                  AND trans_date BETWEEN ? AND ?
                  AND status = 1
                ORDER BY trans_date ASC, transaction_id ASC
            ", [$account_id, $from_dt, $to_dt])->result();

            // 3) Integración diaria simple por tramos entre movimientos
            $rate_daily = (float)$apy / 365.0;
            $prev_time  = strtotime($from_dt);
            $balance    = $opening;
            $accum_int  = 0.0;

            foreach ($tx as $t) {
                $t_time = strtotime($t->trans_date);
                if ($t_time > $prev_time) {
                    $days = max(0, (int) floor(($t_time - $prev_time) / 86400));
                    if ($days > 0) {
                        $accum_int += $balance * $rate_daily * $days;
                        $prev_time += $days * 86400;
                    }
                }
                // aplicar movimiento
                $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
                $balance += $sign * (float)$t->amount;
            }

            // tramo final hasta fin de periodo
            $end_time = strtotime($to_dt);
            if ($end_time > $prev_time) {
                $days = max(0, (int) floor(($end_time - $prev_time) / 86400) + 1); // incluye el día final
                $accum_int += $balance * $rate_daily * $days;
            }

            return round($accum_int, 2);
        };

        $rows = [];
        $total_interest = 0.0;

        foreach ($accounts as $a) {
            $apy = (float)($a->interest_rate_apy ?? 0);
            if ($apy <= 0) continue;

            $interest = $calc_interest($a->savings_account_id, $apy, $date_from, $date_to);
            $total_interest += $interest;

            $rows[] = (object)[
                'account_number' => $a->account_number ?: ('CA-'.str_pad($a->savings_account_id,6,'0',STR_PAD_LEFT)),
                'owner'          => trim(($a->first_name ?? '').' '.($a->last_name ?? '')),
                'type_name'      => (string)$a->type_name,
                'apy'            => $apy,
                'interest'       => $interest,
            ];
        }

        $data = [
            'filters'        => compact('date_from','date_to','branch_id'),
            'branch_options' => $branch_options,
            'rows'           => $rows,
            'total_interest' => $total_interest,
        ];

        $this->load->view('savings_accounts/reports/interest_summary', $data);
    }

        // 3) Estado de cuenta / Extracto por cliente
    public function account_statement()
    {
        $data['active_tab'] = 'statement';

        $f = $this->_statement_filters();
        $person_q       = $f['person_q'];
        $account_number = $f['account_number'];
        $date_from      = $f['date_from'];
        $date_to        = $f['date_to'];

        $header        = null;
        $rows          = [];
        $totals        = ['debit'=>0.0,'credit'=>0.0,'opening'=>0.0,'closing'=>0.0];
        $error         = '';
        $accounts_list = [];

        // 1) Buscar cuentas por cliente
        if (!empty($person_q) && empty($account_number)) {
            $dbp       = $this->db->dbprefix;
            $table_sa  = $dbp.'savings_accounts';
            $table_sat = $dbp.'savings_account_types';
            $table_p   = $dbp.'people';
            $like = '%'.$this->db->escape_like_str($person_q).'%';

            $accounts_list = $this->db->query("
                SELECT
                    sa.savings_account_id,
                    sa.account_number,
                    sa.status,
                    sat.name AS type_name,
                    p.first_name,
                    p.last_name
                FROM {$table_sa} sa
                JOIN {$table_p}   p   ON p.person_id = sa.person_id
                JOIN {$table_sat} sat ON sat.savings_account_type_id = sa.savings_account_type_id
                WHERE CONCAT(p.first_name,' ',p.last_name) LIKE ?
                ORDER BY p.first_name, p.last_name, sa.account_number
                LIMIT 50
            ", [$like])->result();
        }

        // 2) Cargar extracto si hay cuenta y rango
        if (!empty($account_number) && !empty($date_from) && !empty($date_to)) {
            list($header, $rows, $opening, $totals) = $this->_statement_data($account_number, $date_from, $date_to);
            if (!$header) {
                $error = 'No se encontró una cuenta con ese número.';
            }
        }

        // --- Normalizar filas con datos faltantes desde la tabla de transacciones ---
        if (!empty($rows)) {
            $dbp = $this->db->dbprefix;

            // 1) Reunir IDs de transacción presentes en las filas
            $tx_ids = [];
            foreach ($rows as $rr) {
                if (!empty($rr->transaction_id)) {
                    $tx_ids[] = (int)$rr->transaction_id;
                }
            }
            $tx_ids = array_values(array_unique(array_filter($tx_ids)));

            // 2) Traer SOLO los campos que nos faltan
            if (!empty($tx_ids)) {
                $tx_map = [];
                $tx_extra = $this->db->select("
                        tx.transaction_id,
                        tx.savings_account_id,
                        tx.counterparty_account_id,
                        tx.depositor_name,
                        tx.depositor_document,
                        tx.trans_type,
                        tx.description,
                        tx.transfer_group_id,
                        tx.transfer_kind
                    ", FALSE)
                    ->from($dbp.'savings_account_transactions tx')
                    ->where_in('tx.transaction_id', $tx_ids)
                    ->get()->result();

                foreach ($tx_extra as $t) {
                    $tx_map[(int)$t->transaction_id] = $t;
                }

                // 3) Fusionar sin pisar lo que ya vino
                foreach ($rows as &$rr) {
                    $tid = (int)($rr->transaction_id ?? 0);
                    if ($tid && isset($tx_map[$tid])) {
                        $t = $tx_map[$tid];
                        if (!isset($rr->savings_account_id)      || empty($rr->savings_account_id))      $rr->savings_account_id      = $t->savings_account_id;
                        if (!isset($rr->counterparty_account_id) || $rr->counterparty_account_id===null) $rr->counterparty_account_id = $t->counterparty_account_id;
                        if (!isset($rr->transfer_group_id)       || empty($rr->transfer_group_id))       $rr->transfer_group_id       = $t->transfer_group_id;
                        if (!isset($rr->depositor_name)          || $rr->depositor_name==='')            $rr->depositor_name          = $t->depositor_name;
                        if (!isset($rr->depositor_document)      || $rr->depositor_document==='')        $rr->depositor_document      = $t->depositor_document;
                        if (!isset($rr->trans_type)              || $rr->trans_type==='')                $rr->trans_type              = $t->trans_type;
                        if (!isset($rr->description)             || $rr->description==='')               $rr->description             = $t->description;
                    }
                }
                unset($rr);
            }

            // 4) Resolver contrapartes y construir ui_description
            $has_people_document = $this->db->field_exists('document', $dbp.'people');

            // Resolver por grupo cuando falte counterparty_account_id
            $group_ids = [];
            foreach ($rows as $r) {
                if (empty($r->counterparty_account_id) && !empty($r->transfer_group_id)) {
                    $group_ids[] = (int)$r->transfer_group_id;
                }
            }
            $group_ids = array_values(array_unique(array_filter($group_ids)));

            $group_map = [];
            if (!empty($group_ids)) {
                $pairs = $this->db->select("transfer_group_id, transaction_id, savings_account_id")
                    ->from($dbp.'savings_account_transactions')
                    ->where_in('transfer_group_id', $group_ids)
                    ->get()->result();
                foreach ($pairs as $p) {
                    $group_map[(int)$p->transfer_group_id][(int)$p->transaction_id] = (int)$p->savings_account_id;
                }
            }

            // Cuentas involucradas
            $acc_ids = [];
            foreach ($rows as $r) {
                if (!empty($r->savings_account_id))      $acc_ids[] = (int)$r->savings_account_id;
                if (!empty($r->counterparty_account_id)) $acc_ids[] = (int)$r->counterparty_account_id;
                if (empty($r->counterparty_account_id) && !empty($r->transfer_group_id)) {
                    $grp = $group_map[(int)$r->transfer_group_id] ?? [];
                    foreach ($grp as $tid => $accid) {
                        if ($tid != (int)$r->transaction_id) { $acc_ids[] = (int)$accid; break; }
                    }
                }
            }
            $acc_ids = array_values(array_unique(array_filter($acc_ids)));

            // Mapa cuenta -> (nombre, doc, nro)
            $by_acc = [];
            if (!empty($acc_ids)) {
                $sel_doc = $has_people_document ? 'p.document' : "'' AS document";
                $acc_people = $this->db->select("
                        sa.savings_account_id,
                        sa.account_number AS accno,
                        CONCAT(COALESCE(p.first_name,''),' ',COALESCE(p.last_name,'')) AS full_name,
                        {$sel_doc}", FALSE)
                    ->from($dbp.'savings_accounts sa')
                    ->join($dbp.'people p','p.person_id = sa.person_id','left')
                    ->where_in('sa.savings_account_id', $acc_ids)
                    ->get()->result();

                foreach ($acc_people as $ap) {
                    $by_acc[(int)$ap->savings_account_id] = [
                        'name'  => trim((string)$ap->full_name),
                        'doc'   => $has_people_document ? trim((string)$ap->document) : '',
                        'accno' => (string)$ap->accno,
                    ];
                }
            }

            $label_for = ['deposit'=>'Depósito','withdraw'=>'Retiro','transfer'=>'Transferencia'];

            foreach ($rows as &$r) {
                $tt = strtolower((string)$r->trans_type);
                $r->type_label = $label_for[$tt] ?? ucfirst($tt);

                // Completar contraparte si sigue faltando y hay grupo
                if (empty($r->counterparty_account_id) && !empty($r->transfer_group_id)) {
                    $grp = $group_map[(int)$r->transfer_group_id] ?? [];
                    foreach ($grp as $tid => $accid) {
                        if ($tid != (int)$r->transaction_id) {
                            $r->counterparty_account_id = (int)$accid;
                            break;
                        }
                    }
                }

                $base = trim((string)($r->description ?? ''));
                if ($base === '') $base = '—';
                $extras = [];

                if ($tt === 'deposit') {
                    $dn = trim((string)($r->depositor_name ?? ''));
                    $dd = trim((string)($r->depositor_document ?? ''));
                    if ($dn !== '' || $dd !== '') {
                        $line = 'Depositante: '.($dn ?: '—');
                        if ($dd !== '') $line .= "\nCI: ".$dd;
                        $extras[] = $line;
                    }
                } elseif ($tt === 'withdraw') {
                    // Siempre mostrar propietario de la cuenta (retiro normal)
                    $own = $by_acc[(int)($r->savings_account_id ?? 0)] ?? ['name'=>'','doc'=>'','accno'=>''];
                    $label = $own['name'] !== '' ? $own['name'] : ($own['accno'] ?: '—');
                    $line  = 'Propietario de la cuenta: ' . $label;
                    if ($own['doc'] !== '') {
                        $line .= ' — CI: ' . $own['doc'];
                    }
                    $extras[] = $line;
                } elseif ($tt === 'transfer') {
                    // Transferencias: origen/destino consistentes
                    static $group_cache = [];

                    $origin_acc_id = null;
                    $dest_acc_id   = null;

                    if (property_exists($r, 'transfer_group_id') && !empty($r->transfer_group_id)) {
                        $g = (int)$r->transfer_group_id;

                        if (!isset($group_cache[$g])) {
                            $pair = $this->db->select('savings_account_id, counterparty_account_id, transaction_id')
                                ->from($dbp.'savings_account_transactions')
                                ->where('transfer_group_id', $g)
                                ->order_by('transaction_id', 'ASC')
                                ->get()->result();

                            if (count($pair) >= 2) {
                                $origin_acc_id = (int)$pair[0]->savings_account_id;
                                $dest_acc_id   = (int)$pair[1]->savings_account_id;
                            } elseif (count($pair) === 1) {
                                $origin_acc_id = (int)$pair[0]->savings_account_id;
                                $dest_acc_id   = (int)$pair[0]->counterparty_account_id;
                            }

                            if ($origin_acc_id && $dest_acc_id) {
                                $group_cache[$g] = [
                                    'origin' => $origin_acc_id,
                                    'dest'   => $dest_acc_id,
                                ];
                            } else {
                                $group_cache[$g] = [
                                    'origin' => (int)$r->savings_account_id,
                                    'dest'   => (int)$r->counterparty_account_id,
                                ];
                            }
                        }

                        $origin_acc_id = $group_cache[$g]['origin'];
                        $dest_acc_id   = $group_cache[$g]['dest'];
                    } else {
                        $origin_acc_id = (int)$r->savings_account_id;
                        $dest_acc_id   = (int)$r->counterparty_account_id;
                    }

                    $origin = $by_acc[$origin_acc_id] ?? ['name'=>'','doc'=>'','accno'=>''];
                    $dest   = $by_acc[$dest_acc_id]   ?? ['name'=>'','doc'=>'','accno'=>''];

                    $origin_label = $origin['name'] !== '' ? $origin['name'] : ($origin['accno'] ?: '—');
                    $dest_label   = $dest['name']   !== '' ? $dest['name']   : ($dest['accno']   ?: '—');

                    $l1 = 'Cuenta origen: '  . $origin_label;
                    if ($origin['doc'] !== '') $l1 .= ' — CI: '.$origin['doc'];

                    $l2 = 'Cuenta destino: ' . $dest_label;
                    if ($dest['doc'] !== '') $l2 .= ' — CI: '.$dest['doc'];

                    $extras[] = $l1;
                    $extras[] = $l2;
                }

                $r->ui_description = $base.(empty($extras) ? '' : "\n".implode("\n",$extras));
            }
            unset($r);
        }

        // Catálogo de cuentas
        $accounts = $this->Savings_accounts_model->get_all_with_person_active();
        $account_options = [];
        foreach ($accounts as $a) {
            $accno = $a->account_number ?: ('CA-' . str_pad($a->savings_account_id, 6, '0', STR_PAD_LEFT));
            $owner = trim($a->person_name) ?: ('ID ' . $a->person_id);
            $account_options[$a->savings_account_id] = '['.$accno.'] '.$owner.' — '.$a->type_name;
        }

        $data['filters']          = compact('person_q','account_number','date_from','date_to');
        $data['header']           = $header;
        $data['rows']             = $rows;
        $data['totals']           = $totals;
        $data['error']            = $error;
        $data['accounts_list']    = $accounts_list;
        $data['account_options']  = $account_options;

        $this->load->view('savings_accounts/reports/account_statement', $data);
    }

        /* ================================
       EXPORT: Estado de cuenta -> PDF
       ================================ */
    public function account_statement_export_pdf()
    {
        $f = $this->_statement_filters();
        $account_number = $f['account_number'];
        $date_from      = $f['date_from'];
        $date_to        = $f['date_to'];

        if (empty($account_number) || empty($date_from) || empty($date_to)) {
            show_error('Debe indicar número de cuenta y rango de fechas.');
        }

        list($header, $rows, $opening, $totals) = $this->_statement_data($account_number, $date_from, $date_to);
        if (!$header) {
            show_error('No se encontró una cuenta con ese número.');
        }

        // ============================
        // ENRIQUECER FILAS PARA EL PDF
        // ============================
        if (!empty($rows)) {
            $dbp = $this->db->dbprefix;

            // 1) Traer info extra de la tabla de transacciones por transaction_id
            $tx_ids = [];
            foreach ($rows as $r) {
                if (!empty($r->transaction_id)) {
                    $tx_ids[] = (int)$r->transaction_id;
                }
            }
            $tx_ids = array_values(array_unique(array_filter($tx_ids)));

                    $tx_map   = [];
                    $group_map = [];     // transfer_group_id => ['withdraw'=>acc_id, 'deposit'=>acc_id]
                    $acc_ids  = [];      // para luego traer nombre y CI

                    if (!empty($tx_ids)) {
                        $tx_extra = $this->db->select("
                                tx.transaction_id,
                                tx.savings_account_id,
                                tx.counterparty_account_id,
                                tx.trans_type,
                                tx.transfer_group_id,
                                tx.transfer_kind,
                                tx.description,
                                tx.depositor_name,
                                tx.depositor_document
                            ", FALSE)
                            ->from($dbp.'savings_account_transactions tx')
                            ->where_in('tx.transaction_id', $tx_ids)
                            ->get()->result();

                        foreach ($tx_extra as $t) {
                            $tid = (int)$t->transaction_id;
                            $tx_map[$tid] = $t;

                            // cuentas involucradas
                            if (!empty($t->savings_account_id)) {
                                $acc_ids[] = (int)$t->savings_account_id;
                            }
                            if (!empty($t->counterparty_account_id)) {
                                $acc_ids[] = (int)$t->counterparty_account_id;
                            }
                        }

                        // ---- NUEVO: resolver origen/destino por grupo usando TODAS las filas del grupo ----
                        $group_ids = [];
                        foreach ($tx_extra as $t) {
                            if (!empty($t->transfer_group_id)) {
                                $group_ids[] = (int)$t->transfer_group_id;
                            }
                        }
                        $group_ids = array_values(array_unique(array_filter($group_ids)));

                        if (!empty($group_ids)) {
                            $pairs = $this->db->select('transfer_group_id, savings_account_id, transfer_kind')
                                ->from($dbp.'savings_account_transactions')
                                ->where_in('transfer_group_id', $group_ids)
                                ->where('trans_type', 'transfer')
                                ->get()->result();

                            foreach ($pairs as $p) {
                                $g = (string)$p->transfer_group_id;
                                $k = strtolower((string)$p->transfer_kind); // withdraw / deposit

                                if (!isset($group_map[$g])) {
                                    $group_map[$g] = ['withdraw' => null, 'deposit' => null];
                                }

                                if ($k === 'withdraw' || $k === 'deposit') {
                                    $group_map[$g][$k] = (int)$p->savings_account_id;
                                }
                            }
                        }
                    }

            $acc_ids = array_values(array_unique(array_filter($acc_ids)));

            // 2) Traer nombres / CI de las cuentas involucradas
            $by_acc = []; // acc_id => ['name'=>..., 'doc'=>..., 'accno'=>...]
            $has_people_document = $this->db->field_exists('document', $dbp.'people');

            if (!empty($acc_ids)) {
                $sel_doc = $has_people_document ? 'p.document' : "'' AS document";
                $acc_people = $this->db->select("
                        sa.savings_account_id,
                        sa.account_number AS accno,
                        CONCAT(COALESCE(p.first_name,''),' ',COALESCE(p.last_name,'')) AS full_name,
                        {$sel_doc}
                    ", FALSE)
                    ->from($dbp.'savings_accounts sa')
                    ->join($dbp.'people p','p.person_id = sa.person_id','left')
                    ->where_in('sa.savings_account_id', $acc_ids)
                    ->get()->result();

                foreach ($acc_people as $ap) {
                    $by_acc[(int)$ap->savings_account_id] = [
                        'name'  => trim((string)$ap->full_name),
                        'doc'   => $has_people_document ? trim((string)$ap->document) : '',
                        'accno' => (string)$ap->accno,
                    ];
                }
            }

            // 3) Etiquetas legibles de tipo
            $label_for = [
                'deposit'  => 'Depósito',
                'withdraw' => 'Retiro',
                'transfer' => 'Transferencia',
            ];

            // 4) Preparar type_label y una descripción enriquecida similar a la vista
            foreach ($rows as $r) {
                $tid = (int)($r->transaction_id ?? 0);
                $tx  = $tx_map[$tid] ?? null;

                $tt_raw = $tx ? $tx->trans_type : $r->trans_type;
                $tt     = strtolower((string)$tt_raw);

                $r->type_label = $label_for[$tt] ?? ucfirst($tt);

                // base = descripción original
                $base = trim((string)(
                    $tx->description
                    ?? $r->description
                    ?? ''
                ));
                if ($base === '') {
                    $base = '—';
                }

                $extras = [];

                if ($tt === 'deposit') {
                    // Depósitos simples: mostrar depositante
                    $dn = trim((string)($tx->depositor_name ?? ''));
                    $dd = trim((string)($tx->depositor_document ?? ''));
                    if ($dn !== '' || $dd !== '') {
                        $line = 'Depositante: '.($dn !== '' ? $dn : '—');
                        if ($dd !== '') {
                            $line .= "\nCI: ".$dd;
                        }
                        $extras[] = $line;
                    }
                } elseif ($tt === 'transfer') {
                    // Transferencias: origen/destino usando transfer_group_id + transfer_kind
                    $origin_acc_id = null;
                    $dest_acc_id   = null;

                    if ($tx && !empty($tx->transfer_group_id)) {
                        $g = (string)$tx->transfer_group_id;
                        if (isset($group_map[$g])) {
                            $origin_acc_id = $group_map[$g]['withdraw'] ?: null;
                            $dest_acc_id   = $group_map[$g]['deposit']  ?: null;
                        }
                    }

                    // Fallback si faltara algo
                    if (!$origin_acc_id && $tx && !empty($tx->savings_account_id)) {
                        $origin_acc_id = (int)$tx->savings_account_id;
                    }
                    if (!$dest_acc_id && $tx && !empty($tx->counterparty_account_id)) {
                        $dest_acc_id = (int)$tx->counterparty_account_id;
                    }

                    $origin = $by_acc[$origin_acc_id] ?? ['name'=>'','doc'=>'','accno'=>''];
                    $dest   = $by_acc[$dest_acc_id]   ?? ['name'=>'','doc'=>'','accno'=>''];

                    $origin_label = $origin['name'] !== '' ? $origin['name'] : ($origin['accno'] ?: '—');
                    $dest_label   = $dest['name']   !== '' ? $dest['name']   : ($dest['accno']   ?: '—');

                    $l1 = 'Cuenta origen: '.$origin_label;
                    if ($origin['doc'] !== '') {
                        $l1 .= ' — CI: '.$origin['doc'];
                    }

                    $l2 = 'Cuenta destino: '.$dest_label;
                    if ($dest['doc'] !== '') {
                        $l2 .= ' — CI: '.$dest['doc'];
                    }

                    if ($origin_label !== '—' || $dest_label !== '—') {
                        $extras[] = $l1;
                        $extras[] = $l2;
                    }
                }

                // Guardamos una descripción enriquecida para el PDF
                $r->ui_description = $base.(empty($extras) ? '' : "\n".implode("\n", $extras));
            }
            unset($r);
        }

        // ======================
        // GENERACIÓN DEL PDF
        // ======================
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $this->load->library('pdf');
        $mpdf = $this->pdf->load('"en-GB-x","A4","","",10,10,10,10,6,3,"P"');
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $html  = '<html><head><meta charset="utf-8"></head><body>';
        $html .= '<h3>Extracto de Caja de Ahorros</h3>';
        $html .= '<p><small>Período: '.htmlspecialchars($date_from).' al '.htmlspecialchars($date_to).'</small></p>';

        $html .= '<table width="100%" cellpadding="2" cellspacing="0">';
        $html .= '<tr><td><strong>Titular:</strong> '.htmlspecialchars($header->owner).'</td>'
            .  '<td align="right"><strong>Cuenta:</strong> '.htmlspecialchars($header->account_number).'</td></tr>';
        $html .= '<tr><td><strong>Producto:</strong> '.htmlspecialchars($header->product).'</td>'
            .  '<td align="right"><strong>Saldo inicial:</strong> '.number_format((float)$totals['opening'], 2).'</td></tr>';
        $html .= '<tr><td colspan="2"><strong>Estado:</strong> '.htmlspecialchars($header->status).'</td></tr>';
        $html .= '</table><br>';

        // Cabecera de la tabla: ahora con columna "Tipo"
        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<thead><tr>
                    <th>Fecha y hora</th>
                    <th>Tipo</th>
                    <th>Descripción de la transacción</th>
                    <th>Monto</th>
                    <th>Saldo</th>
                </tr></thead><tbody>';

        foreach ($rows as $r) {
            $amt = (float)$r->amount;

            $type_label = isset($r->type_label) ? $r->type_label : $r->trans_type;
            $desc = trim((string)($r->ui_description ?? $r->description ?? ''));
            if ($desc === '') { $desc = '—'; }

            $html .= '<tr>'
                .  '<td>'.date('d/m/Y H:i', strtotime($r->trans_date)).'</td>'
                .  '<td>'.htmlspecialchars($type_label).'</td>'
                .  '<td>'.nl2br(htmlspecialchars($desc)).'</td>'
                .  '<td align="right">'.(($amt >= 0 ? '+' : '').number_format($amt, 2)).'</td>'
                .  '<td align="right">'.number_format((float)$r->balance, 2).'</td>'
                .  '</tr>';
        }

        // Pie de tabla (5 columnas: 3 de texto + 2 numéricas)
        $html .= '</tbody><tfoot><tr>'
            .  '<th colspan="3" align="right">Totales</th>'
            .  '<th align="right">'.number_format((float)$totals['credit'] - (float)$totals['debit'], 2).'</th>'
            .  '<th align="right">'.number_format((float)$totals['closing'], 2).'</th>'
            .  '</tr></tfoot>';
        $html .= '</table></body></html>';

        $mpdf->WriteHTML($html, 2);
        $filename = 'account_statement_'.$account_number.'_'.$date_from.'_'.$date_to.'.pdf';
        $mpdf->Output($filename, 'I');
        error_reporting($old_level);
        exit;
    }

    /* ============================
    EXPORT: Resumen diario -> CSV
    ============================ */
    public function daily_summary_export_csv()
    {
        $f = $this->_daily_filters();
        $date_from = $f['date_from'];
        $date_to   = $f['date_to'];
        $branch_id = $f['branch_id'];

        $params = [];
        $where  = ["tx.status = 1"];
        if (!empty($date_from)) { $where[] = "DATE(tx.trans_date) >= ?"; $params[] = $date_from; }
        if (!empty($date_to))   { $where[] = "DATE(tx.trans_date) <= ?"; $params[] = $date_to; }
        if (!empty($branch_id)) { $where[] = "tx.branch_id = ?";         $params[] = (int)$branch_id; }

        $sql = "
            SELECT
            DATE(tx.trans_date) AS tx_date,
            tx.branch_id,
            SUM(CASE WHEN tx.trans_type='deposit'  THEN tx.amount ELSE 0 END) AS deposits,
            SUM(CASE WHEN tx.trans_type='withdraw' THEN tx.amount ELSE 0 END) AS withdraws,
            SUM(CASE WHEN tx.trans_type='transfer' THEN tx.amount ELSE 0 END) AS transfers,
            SUM(CASE
                    WHEN tx.trans_type='deposit'  THEN  tx.amount
                    WHEN tx.trans_type='withdraw' THEN -tx.amount
                    ELSE 0
                END) AS net_cash
            FROM {$this->db->dbprefix('savings_account_transactions')} tx
            WHERE ".implode(' AND ', $where)."
            GROUP BY DATE(tx.trans_date), tx.branch_id
            ORDER BY tx_date ASC, tx.branch_id ASC
        ";

        $rows = $this->db->query($sql, $params)->result();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="resumen_diario.csv"');

        // BOM (mejora apertura en Excel)
        echo "\xEF\xBB\xBF";
        
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Fecha','Sucursal','Depósitos','Retiros','Transferencias','Neto']);

        // Mapa de sucursales para el nombre
        list($_branches, $branch_options) = $this->_branches_data();

        foreach ($rows as $r) {
            $branch_label = isset($branch_options[$r->branch_id]) ? $branch_options[$r->branch_id] : $r->branch_id;
            fputcsv($out, [
                date('d/m/Y', strtotime($r->tx_date)),
                $branch_label,
                number_format((float)$r->deposits,2,'.',''),
                number_format((float)$r->withdraws,2,'.',''),
                number_format((float)$r->transfers,2,'.',''),
                number_format((float)$r->net_cash,2,'.',''),
            ]);
        }
        fclose($out);
        exit;
    }

    /* ===========================
    EXPORT: Resumen diario -> PDF
    =========================== */
    public function daily_summary_export_pdf()
    {
        $f = $this->_daily_filters();
        $date_from = $f['date_from'];
        $date_to   = $f['date_to'];
        $branch_id = $f['branch_id'];

        $params = [];
        $where  = ["tx.status = 1"];
        if (!empty($date_from)) { $where[] = "DATE(tx.trans_date) >= ?"; $params[] = $date_from; }
        if (!empty($date_to))   { $where[] = "DATE(tx.trans_date) <= ?"; $params[] = $date_to; }
        if (!empty($branch_id)) { $where[] = "tx.branch_id = ?";         $params[] = (int)$branch_id; }

        $sql = "
            SELECT
            DATE(tx.trans_date) AS tx_date,
            tx.branch_id,
            SUM(CASE WHEN tx.trans_type='deposit'  THEN tx.amount ELSE 0 END) AS deposits,
            SUM(CASE WHEN tx.trans_type='withdraw' THEN tx.amount ELSE 0 END) AS withdraws,
            SUM(CASE WHEN tx.trans_type='transfer' THEN tx.amount ELSE 0 END) AS transfers,
            SUM(CASE
                    WHEN tx.trans_type='deposit'  THEN  tx.amount
                    WHEN tx.trans_type='withdraw' THEN -tx.amount
                    ELSE 0
                END) AS net_cash
            FROM {$this->db->dbprefix('savings_account_transactions')} tx
            WHERE ".implode(' AND ', $where)."
            GROUP BY DATE(tx.trans_date), tx.branch_id
            ORDER BY tx_date ASC, tx.branch_id ASC
        ";

        $rows = $this->db->query($sql, $params)->result();

        // mPDF legacy (silenciar warnings)
        $this->load->library('pdf');
        $mpdf = $this->pdf->load('"en-GB-x","A4","","",10,10,10,10,6,3,"P"');
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $html = '<html><head><meta charset="utf-8"></head><body>';
        $html .= '<h3>Resumen diario de movimientos</h3>';
        $html .= '<p><small>Período: '.htmlspecialchars($date_from).' a '.htmlspecialchars($date_to).( $branch_id ? (' — Sucursal: '.htmlspecialchars($branch_map[$branch_id] ?? $branch_id)) : '' ).'</small></p>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<thead><tr>
                    <th>Fecha</th>
                    <th>Sucursal</th>
                    <th>Depósitos</th>
                    <th>Retiros</th>
                    <th>Transferencias</th>
                    <th>Neto de caja</th>
                </tr></thead><tbody>';

        $totals = ['dep'=>0.0,'wit'=>0.0,'tra'=>0.0,'net'=>0.0];

        // Mapa de sucursales
        $branch_map = [];
        $q = $this->db->select('id, branch_name')
                    ->from($this->db->dbprefix('branches'))
                    ->get();
        foreach ($q->result() as $b) $branch_map[$b->id] = $b->branch_name;

        // ...

        foreach ($rows as $r) {
            $totals['dep'] += (float)$r->deposits;
            $totals['wit'] += (float)$r->withdraws;
            $totals['tra'] += (float)$r->transfers;
            $totals['net'] += (float)$r->net_cash;

            // Dentro del bucle que arma las filas <tr>
            $branch_label = isset($branch_map[$r->branch_id]) ? $branch_map[$r->branch_id] : $r->branch_id;

            $html .= '<tr>'
                .  '<td>'.date('d/m/Y', strtotime($r->tx_date)).'</td>'
                .  '<td>'.htmlspecialchars($branch_label).'</td>'
                .  '<td style="text-align:right">'.number_format((float)$r->deposits,  2).'</td>'
                .  '<td style="text-align:right">'.number_format((float)$r->withdraws, 2).'</td>'
                .  '<td style="text-align:right">'.number_format((float)$r->transfers, 2).'</td>'
                .  '<td style="text-align:right"><strong>'.number_format((float)$r->net_cash, 2).'</strong></td>'
                .  '</tr>';
        }
        $html .= '</tbody>';
        $html .= '<tfoot><tr>'
            .  '<th colspan="2" align="right">Totales</th>'
            .  '<th align="right">'.number_format($totals['dep'], 2).'</th>'
            .  '<th align="right">'.number_format($totals['wit'], 2).'</th>'
            .  '<th align="right">'.number_format($totals['tra'], 2).'</th>'
            .  '<th align="right">'.number_format($totals['net'], 2).'</th>'
            .  '</tr></tfoot>';
        $html .= '</table></body></html>';

        $mpdf->WriteHTML($html, 2);
        $filename = 'daily_summary_'.$date_from.'_'.$date_to.( $branch_id ? ('_branch'.$branch_id) : '' ).'.pdf';
        $mpdf->Output($filename, 'I');
        error_reporting($old_level);
        exit;
    }
    
    /* =================================
    EXPORT: Intereses por cuenta -> CSV
    ================================= */
    public function interest_summary_export_csv()
    {
        // Reutilizamos interest_summary() para no duplicar lógica:
        $date_from = $this->input->get('date_from', TRUE);
        $date_to   = $this->input->get('date_to', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);

        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to   = date('Y-m-t');
        }

        $this->load->model('Savings_accounts_model');

        // Traemos el mismo set que pinta la vista
        // (copiamos el bloque de interest_summary() que obtiene $accounts y calcula intereses)
        $branches = $this->db->select('id, branch_name')
                            ->from($this->db->dbprefix('branches'))
                            ->order_by('branch_name')->get()->result();

        $this->db->select('sa.*, sat.name AS type_name, sat.interest_rate_apy, p.first_name, p.last_name');
        $this->db->from($this->db->dbprefix('savings_accounts').' sa');
        $this->db->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id = sa.savings_account_type_id','left');
        $this->db->join($this->db->dbprefix('people').' p','p.person_id = sa.person_id','left');
        if (!empty($branch_id)) $this->db->where('sa.branch_id', (int)$branch_id);
        $this->db->where('sa.status', 1);
        $accounts = $this->db->get()->result();

        $calc_interest = function($account_id, $apy, $from, $to) {
            $CI =& get_instance();
            $from_dt = $from.' 00:00:00';
            $to_dt   = $to.' 23:59:59';
            $acc     = $CI->Savings_accounts_model->get((int)$account_id);
            $now_balance = (float)($acc->current_balance ?? 0);
            $sum_newer = $CI->db->query("
                SELECT COALESCE(SUM(CASE WHEN trans_type='withdraw' THEN -amount
                                        WHEN trans_type='deposit' THEN amount
                                        ELSE 0 END),0) AS s
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ? AND trans_date > ?
            ", [$account_id, $from_dt])->row()->s ?? 0.0;
            $opening = $now_balance - (float)$sum_newer;

            $tx = $CI->db->query("
                SELECT transaction_id, trans_date, trans_type, amount
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ?
                AND trans_date BETWEEN ? AND ?
                AND status = 1
                ORDER BY trans_date ASC, transaction_id ASC
            ", [$account_id, $from_dt, $to_dt])->result();

            $rate_daily = (float)$apy / 365.0;
            $prev_time  = strtotime($from_dt);
            $balance    = $opening;
            $accum_int  = 0.0;

            foreach ($tx as $t) {
                $t_time = strtotime($t->trans_date);
                if ($t_time > $prev_time) {
                    $days = max(0, (int) floor(($t_time - $prev_time) / 86400));
                    if ($days > 0) {
                        $accum_int += $balance * $rate_daily * $days;
                        $prev_time += $days * 86400;
                    }
                }
                $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
                $balance += $sign * (float)$t->amount;
            }
            $end_time = strtotime($to_dt);
            if ($end_time > $prev_time) {
                $days = max(0, (int) floor(($end_time - $prev_time) / 86400) + 1);
                $accum_int += $balance * $rate_daily * $days;
            }
            return round($accum_int, 2);
        };

        $rows = [];
        $total_interest = 0.0;
        foreach ($accounts as $a) {
            $apy = (float)($a->interest_rate_apy ?? 0);
            if ($apy <= 0) continue;
            $interest = $calc_interest($a->savings_account_id, $apy, $date_from, $date_to);
            $total_interest += $interest;
            $rows[] = [
                'account_number' => $a->account_number ?: ('CA-'.str_pad($a->savings_account_id,6,'0',STR_PAD_LEFT)),
                'owner'          => trim(($a->first_name ?? '').' '.($a->last_name ?? '')),
                'type_name'      => (string)$a->type_name,
                'apy'            => $apy,
                'interest'       => $interest,
            ];
        }

        $suffix = $date_from.'_'.$date_to.( $branch_id ? ('_branch'.$branch_id) : '' );
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="interest_summary_'.$suffix.'.csv"');

        // BOM (mejora apertura en Excel)
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output','w');
        fputcsv($out, ['Cuenta','Cliente','Tipo','APY (%)','Interés (período)'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['account_number'],
                $r['owner'],
                $r['type_name'],
                number_format($r['apy']*100, 2, '.', ''),
                number_format($r['interest'], 2, '.', ''),
            ], ';');
        }
        // total
        fputcsv($out, ['','','','TOTAL', number_format($total_interest, 2, '.', '')], ';');
        fclose($out);
        exit;
    }

    /* ================================
    EXPORT: Intereses por cuenta -> PDF
    ================================ */
    public function interest_summary_export_pdf()
    {
        $date_from = $this->input->get('date_from', TRUE);
        $date_to   = $this->input->get('date_to', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);

        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to   = date('Y-m-t');
        }

        $this->load->model('Savings_accounts_model');

        $this->db->select('sa.*, sat.name AS type_name, sat.interest_rate_apy, p.first_name, p.last_name');
        $this->db->from($this->db->dbprefix('savings_accounts').' sa');
        $this->db->join($this->db->dbprefix('savings_account_types').' sat','sat.savings_account_type_id = sa.savings_account_type_id','left');
        $this->db->join($this->db->dbprefix('people').' p','p.person_id = sa.person_id','left');
        if (!empty($branch_id)) $this->db->where('sa.branch_id', (int)$branch_id);
        $this->db->where('sa.status', 1);
        $accounts = $this->db->get()->result();

        $calc_interest = function($account_id, $apy, $from, $to) {
            $CI =& get_instance();
            $from_dt = $from.' 00:00:00';
            $to_dt   = $to.' 23:59:59';
            $acc     = $CI->Savings_accounts_model->get((int)$account_id);
            $now_balance = (float)($acc->current_balance ?? 0);
            $sum_newer = $CI->db->query("
                SELECT COALESCE(SUM(CASE WHEN trans_type='withdraw' THEN -amount
                                        WHEN trans_type='deposit' THEN amount
                                        ELSE 0 END),0) AS s
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ? AND trans_date > ?
            ", [$account_id, $from_dt])->row()->s ?? 0.0;
            $opening = $now_balance - (float)$sum_newer;

            $tx = $CI->db->query("
                SELECT transaction_id, trans_date, trans_type, amount
                FROM {$CI->db->dbprefix('savings_account_transactions')}
                WHERE savings_account_id = ?
                AND trans_date BETWEEN ? AND ?
                AND status = 1
                ORDER BY trans_date ASC, transaction_id ASC
            ", [$account_id, $from_dt, $to_dt])->result();

            $rate_daily = (float)$apy / 365.0;
            $prev_time  = strtotime($from_dt);
            $balance    = $opening;
            $accum_int  = 0.0;

            foreach ($tx as $t) {
                $t_time = strtotime($t->trans_date);
                if ($t_time > $prev_time) {
                    $days = max(0, (int) floor(($t_time - $prev_time) / 86400));
                    if ($days > 0) {
                        $accum_int += $balance * $rate_daily * $days;
                        $prev_time += $days * 86400;
                    }
                }
                $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
                $balance += $sign * (float)$t->amount;
            }
            $end_time = strtotime($to_dt);
            if ($end_time > $prev_time) {
                $days = max(0, (int) floor(($end_time - $prev_time) / 86400) + 1);
                $accum_int += $balance * $rate_daily * $days;
            }
            return round($accum_int, 2);
        };

        $rows = [];
        $total_interest = 0.0;
        foreach ($accounts as $a) {
            $apy = (float)($a->interest_rate_apy ?? 0);
            if ($apy <= 0) continue;
            $interest = $calc_interest($a->savings_account_id, $apy, $date_from, $date_to);
            $total_interest += $interest;
            $rows[] = (object)[
                'account_number' => $a->account_number ?: ('CA-'.str_pad($a->savings_account_id,6,'0',STR_PAD_LEFT)),
                'owner'          => trim(($a->first_name ?? '').' '.($a->last_name ?? '')),
                'type_name'      => (string)$a->type_name,
                'apy'            => $apy,
                'interest'       => $interest,
            ];
        }

        $this->load->library('pdf');
        $mpdf = $this->pdf->load('"en-GB-x","A4","","",10,10,10,10,6,3,"P"');
        $old_level = error_reporting();
        error_reporting($old_level & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        $html  = '<html><head><meta charset="utf-8"></head><body>';
        $html .= '<h3>Intereses por cuenta (período)</h3>';
        $html .= '<p><small>Período: '.htmlspecialchars($date_from).' a '.htmlspecialchars($date_to).'</small></p>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<thead><tr>
                    <th>Cuenta</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>APY</th>
                    <th>Interés (período)</th>
                </tr></thead><tbody>';

        foreach ($rows as $r) {
            $html .= '<tr>'
                .  '<td>'.htmlspecialchars($r->account_number).'</td>'
                .  '<td>'.htmlspecialchars($r->owner).'</td>'
                .  '<td>'.htmlspecialchars($r->type_name).'</td>'
                .  '<td align="right">'.number_format((float)$r->apy*100, 2).'%</td>'
                .  '<td align="right"><strong>'.number_format((float)$r->interest, 2).'</strong></td>'
                .  '</tr>';
        }
        $html .= '</tbody>';
        $html .= '<tfoot><tr>'
            .  '<th colspan="4" align="right">Total intereses</th>'
            .  '<th align="right">'.number_format((float)$total_interest, 2).'</th>'
            .  '</tr></tfoot>';
        $html .= '</table></body></html>';

        $mpdf->WriteHTML($html, 2);
        $filename = 'interest_summary_'.$date_from.'_'.$date_to.( $branch_id ? ('_branch'.$branch_id) : '' ).'.pdf';
        $mpdf->Output($filename, 'I');
        error_reporting($old_level);
        exit;
    }

    // --- reutiliza filtros para pantalla y exportaciones ---
    private function _daily_filters()
    {
        $date = $this->input->get('date', TRUE);
        $branch_id = $this->input->get('branch_id', TRUE);

        if (empty($date)) $date = date('Y-m-d');

        // rango = mismo día (compatibles con BETWEEN)
        $date_from = $date;
        $date_to   = $date;

        return compact('date','branch_id','date_from','date_to');
    }

        // Filtros para Estado de Cuenta (extracto por cuenta)
    private function _statement_filters()
    {
        $person_q       = trim($this->input->get('person_q', TRUE));      // nombre/CI del cliente
        $account_number = trim($this->input->get('account_number', TRUE));
        $date_from      = $this->input->get('date_from', TRUE);
        $date_to        = $this->input->get('date_to', TRUE);

        // Por defecto: mes actual
        if (empty($date_from) && empty($date_to)) {
            $date_from = date('Y-m-01');
            $date_to   = date('Y-m-t');
        }

        // Si sólo viene una fecha, usamos la misma para ambos extremos
        if (!empty($date_from) && empty($date_to)) {
            $date_to = $date_from;
        }

        return compact('person_q','account_number','date_from','date_to');
    }

        // Consulta de datos de cabecera + movimientos para el extracto
    private function _statement_data($account_number, $date_from, $date_to)
    {
        $dbp       = $this->db->dbprefix;
        $table_trx = $dbp.'savings_account_transactions';

        if (empty($account_number) || empty($date_from) || empty($date_to)) {
            return [null, [], 0.0, ['debit'=>0.0, 'credit'=>0.0, 'opening'=>0.0, 'closing'=>0.0]];
        }

        // 1) Buscar la cuenta por número
        $acc = $this->db->select('
                sa.savings_account_id,
                sa.account_number,
                sa.status,
                sat.name AS type_name,
                sat.interest_rate_apy,
                p.first_name,
                p.last_name
            ')
            ->from($dbp.'savings_accounts sa')
            ->join($dbp.'savings_account_types sat','sat.savings_account_type_id = sa.savings_account_type_id','left')
            ->join($dbp.'people p','p.person_id = sa.person_id','left')
            ->where('sa.account_number', $account_number)
            ->get()
            ->row();

        if (!$acc) {
            return [null, [], 0.0, ['debit'=>0.0, 'credit'=>0.0, 'opening'=>0.0, 'closing'=>0.0]];
        }

        $from_dt = $date_from.' 00:00:00';
        $to_dt   = $date_to.' 23:59:59';

        // 2) Saldo de apertura
        $this->load->model('Savings_accounts_model');
        $acc_model   = $this->Savings_accounts_model->get((int)$acc->savings_account_id);
        $now_balance = (float)($acc_model->current_balance ?? 0);

        $sum_newer_row = $this->db->query("
                SELECT COALESCE(SUM(
                            CASE
                                WHEN trans_type='withdraw' THEN -amount
                                WHEN trans_type='deposit'  THEN  amount
                                WHEN trans_type='fee'      THEN -amount
                                ELSE 0
                            END
                        ),0) AS s
                FROM {$table_trx}
                WHERE savings_account_id = ?
                AND trans_date > ?
            ", [$acc->savings_account_id, $from_dt])->row();

        $sum_newer = (float)($sum_newer_row->s ?? 0.0);
        $opening   = $now_balance - $sum_newer;

        // 3) Movimientos del período
        $rows_db = $this->db->query("
                SELECT
                    tx.transaction_id,
                    tx.savings_account_id,
                    tx.counterparty_account_id,
                    tx.transfer_group_id,
                    tx.depositor_name,
                    tx.depositor_document,
                    tx.trans_date,
                    tx.trans_type,
                    tx.description,
                    tx.amount
                FROM {$table_trx} tx
                WHERE tx.savings_account_id = ?
                AND tx.trans_date BETWEEN ? AND ?
                ORDER BY tx.trans_date ASC, tx.transaction_id ASC
            ", [$acc->savings_account_id, $from_dt, $to_dt])->result();

        $balance      = $opening;
        $rows         = [];
        $total_debit  = 0.0;
        $total_credit = 0.0;

        foreach ($rows_db as $r) {
            $type = strtolower($r->trans_type);
            // retiros/cargos como débito
            $sign = in_array($type, ['withdraw','fee','debit']) ? -1 : 1;
            $amt  = (float)$r->amount;

            if ($sign < 0) {
                $total_debit  += $amt;
            } else {
                $total_credit += $amt;
            }

            $balance += $sign * $amt;

            $row                          = new stdClass();
            $row->transaction_id          = (int)$r->transaction_id;
            $row->savings_account_id      = (int)$r->savings_account_id;
            $row->counterparty_account_id = $r->counterparty_account_id !== null
                                            ? (int)$r->counterparty_account_id
                                            : null;
            $row->transfer_group_id       = $r->transfer_group_id;
            $row->depositor_name          = $r->depositor_name;
            $row->depositor_document      = $r->depositor_document;
            $row->trans_date              = $r->trans_date;
            $row->trans_type              = $r->trans_type;
            $row->description             = $r->description;
            $row->amount                  = $sign * $amt;  // ya con signo
            $row->balance                 = $balance;

            $rows[] = $row;
        }

        $status_label = ((int)$acc->status === 1) ? 'Activa' : 'Inactiva';

        $header = (object)[
            'account_number' => $acc->account_number,
            'owner'          => trim($acc->first_name.' '.$acc->last_name),
            'product'        => $acc->type_name,
            'status'         => $status_label,
        ];

        $totals = [
            'debit'   => $total_debit,
            'credit'  => $total_credit,
            'opening' => $opening,
            'closing' => $balance,
        ];

        return [$header, $rows, $opening, $totals];
    }

    private function _branches_data()
    {
        $rows = $this->db->select('id, branch_name')
                        ->from($this->db->dbprefix('branches'))
                        ->order_by('branch_name')->get()->result();

        // Para vistas que esperan lista de objetos:
        $branches = [];
        foreach ($rows as $r) {
            $o = new stdClass();
            $o->id = $r->id;
            // nombre “genérico” por si la vista usa ->name
            $o->name = $r->branch_name;
            $branches[] = $o;
        }

        // Para vistas que esperan map
        $branch_options = ['' => '— Todas —'];
        foreach ($rows as $r) $branch_options[$r->id] = $r->branch_name;

        return [$branches, $branch_options];
    }

}
