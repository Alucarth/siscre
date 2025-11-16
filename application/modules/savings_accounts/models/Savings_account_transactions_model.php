<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_transactions_model extends CI_Model
{
    protected $table = 'savings_account_transactions';

    /* ============================
       Helpers internos
       ============================ */

    private function get_account($id)
    {
        return $this->db
            ->select('sa.*, sat.is_fixed_term, sat.term_days')
            ->from('savings_accounts sa')
            ->join('savings_account_types sat','sat.savings_account_type_id = sa.savings_account_type_id','left')
            ->where('sa.savings_account_id', (int)$id)
            ->get()
            ->row();
    }

    private function is_matured($account)
    {
        // Si no es a plazo, siempre “vencido”
        if (empty($account->is_fixed_term)) return TRUE;

        // Si es a plazo, debe existir maturity_date y ser hoy o pasado
        if (empty($account->maturity_date)) return FALSE;

        return (strtotime($account->maturity_date.' 23:59:59') <= time());
    }

    /* ============================
       Listados / lecturas
       ============================ */

    private function apply_filters(array $f = [])
    {
        $this->db
            ->from($this->table.' tx')
            ->join('savings_accounts sa','sa.savings_account_id = tx.savings_account_id')
            ->join('people p','p.person_id = sa.person_id','left')
            ->join('leads l','l.customer_id = sa.person_id','left');

        if (!empty($f['account_id']))    $this->db->where('tx.savings_account_id', (int)$f['account_id']);
        if (!empty($f['trans_type']))    $this->db->where('tx.trans_type', $f['trans_type']);
        if (($f['branch_id'] ?? '') !== '')       $this->db->where('tx.branch_id', (int)$f['branch_id']);
        if (($f['registered_by'] ?? '') !== '')   $this->db->where('tx.registered_by', (int)$f['registered_by']);
        if (($f['status'] ?? '') !== '')          $this->db->where('tx.status', (int)$f['status']);

        if (!empty($f['date_from'])) $this->db->where('tx.trans_date >=', $f['date_from'].' 00:00:00');
        if (!empty($f['date_to']))   $this->db->where('tx.trans_date <=', $f['date_to'].' 23:59:59');

        if (!empty($f['q'])) {
            $q = trim($f['q']);
            $this->db->group_start()
                    ->like('sa.account_number', $q)
                    ->or_like('p.first_name', $q)
                    ->or_like('p.last_name', $q)
                    ->or_like('l.id_no', $q)
                    ->group_end();
        }
    }

    public function get_all(array $f = [], $limit = null, $offset = null)
    {
        $this->db->select('tx.*, sa.account_number, sa.current_balance, p.first_name, p.last_name');
        $this->apply_filters($f);
        $this->db
            ->order_by('tx.trans_date','DESC')
            ->order_by('tx.transaction_id','DESC');
        if ($limit !== null) $this->db->limit((int)$limit, (int)$offset);
        return $this->db->get()->result();
    }

    public function count_all(array $f = [])
    {
        $this->apply_filters($f);
        return (int)$this->db->count_all_results();
    }

    public function get($id)
    {
        return $this->db->where('transaction_id',(int)$id)->get($this->table)->row();
    }

    /* ============================
       Altas / edición
       ============================ */

    /**
     * Insert genérico que mantiene compatibilidad con tu controlador:
     * - deposit / withdraw: lo procesa aquí
     * - transfer: si viene dst_account_id, delega a create_transfer()
     */
    public function insert($d)
    {
        $type   = strtolower(trim($d['trans_type'] ?? ''));
        $amount = (float)($d['amount'] ?? 0);

        if ($type === 'transfer') {
            $src = (int)($d['savings_account_id'] ?? 0);
            $dst = (int)($d['dst_account_id'] ?? 0); // el controlador debe poner este campo
            $desc= $d['description'] ?? '';
            $br  = (int)($d['branch_id'] ?? 0);
            $actor = (int)($this->session->userdata('person_id') ?: 0);

            return $this->create_transfer($src, $dst, $amount, $desc, $br, $actor);
        }

        // deposit / withdraw
        return $this->post_simple($d);
    }

    /**
     * Permite actualizar SOLO la descripción (para no romper trazabilidad).
     */
    public function update($id, $d)
    {
        $upd = [];
        if (isset($d['description'])) {
            $upd['description']   = $d['description'];
            $upd['date_modified'] = time();
            $upd['modified_by']   = (int)($this->session->userdata('person_id') ?: 0);
        }
        if (!$upd) return TRUE; // nada que hacer
        return $this->db->where('transaction_id',(int)$id)->update($this->table, $upd);
    }

    /**
     * Soft-delete simple (marca status=0 si existe ese campo).
     * OJO: si quieres reversas automáticas, lo hacemos en el controlador
     * para decidir la lógica según trans_type.
     */
   
    /**
     * Deshabilita (soft delete) una transacción y ajusta el saldo de la cuenta.
     * No elimina físicamente, solo marca status=0 y revierte el monto.
     */
    public function delete($id)
    {
        $tbl = $this->db->dbprefix($this->table); // c19_savings_account_transactions
        $id  = (int)$id;

        // 1) Obtener transacción
        $tx = $this->db->where('transaction_id', $id)->get($tbl)->row();
        if (!$tx) return false;

        // 2) Marcar como deshabilitada (si tiene columna status)
        if ($this->db->field_exists('status', $tbl)) {
            $this->db->where('transaction_id', $id)->update($tbl, [
                'status'        => 0,
                'date_modified' => time(),
                'modified_by'   => (int)($this->session->userdata('person_id') ?: 0),
            ]);
        } else {
            // Si no existe columna 'status', se borra físicamente
            $this->db->where('transaction_id', $id)->delete($tbl);
        }

        // 3) Revertir el saldo de la cuenta
        //    - Depósito => restar
        //    - Retiro   => sumar
        $sign  = (strtolower($tx->trans_type) === 'withdraw') ? +1 : -1;
        $delta = $sign * (float)$tx->amount;

        $this->db->set('current_balance', 'current_balance + ('. $this->db->escape($delta) .')', false)
                ->where('savings_account_id', (int)$tx->savings_account_id)
                ->update($this->db->dbprefix('savings_accounts'));

        return $this->db->affected_rows() > 0;
    }

    public function get_one_with_joins($transaction_id)
    {
        $tx_tbl  = $this->db->dbprefix('savings_account_transactions') . ' tx';
        $sa_tbl  = $this->db->dbprefix('savings_accounts') . ' sa';
        $sat_tbl = $this->db->dbprefix('savings_account_types') . ' sat';
        $p_tbl   = $this->db->dbprefix('people') . ' p';
        $b_tbl   = $this->db->dbprefix('branches') . ' b';
        $e_tbl   = $this->db->dbprefix('employees') . ' e';
        $op_tbl  = $this->db->dbprefix('people') . ' op';
        $l_tbl   = $this->db->dbprefix('leads'); // para subconsulta

        $sql = "
            SELECT
                tx.*,
                sa.account_number,
                sa.person_id AS owner_id,
                sa.savings_account_type_id,
                sat.name AS account_type_name,
                p.first_name, p.last_name,
                /* id_no sin depender de lead_id ni múltiples filas */
                (
                    SELECT l.id_no
                    FROM {$l_tbl} l
                    WHERE l.customer_id = sa.person_id
                    AND l.id_no IS NOT NULL AND l.id_no <> ''
                    LIMIT 1
                ) AS id_no,
                /* Sucursal correctamente unida por tx.branch_id → branches.id */
                b.branch_name,
                op.first_name AS op_first, op.last_name AS op_last
            FROM {$tx_tbl}
            JOIN {$sa_tbl}  ON sa.savings_account_id = tx.savings_account_id
            LEFT JOIN {$sat_tbl} ON sat.savings_account_type_id = sa.savings_account_type_id
            LEFT JOIN {$p_tbl}   ON p.person_id = sa.person_id
            LEFT JOIN {$b_tbl}   ON b.id = tx.branch_id
            LEFT JOIN {$e_tbl}   ON e.person_id = tx.registered_by
            LEFT JOIN {$op_tbl}  ON op.person_id = e.person_id
            WHERE tx.transaction_id = ?
            LIMIT 1
        ";

        return $this->db->query($sql, [(int)$transaction_id])->row();
    }

    public function reactivate_tx($id, $reason = '')
    {
        $tbl = $this->db->dbprefix($this->table);
        if (! $this->db->field_exists('status', $tbl)) return false;

        return $this->db->where('transaction_id', (int)$id)
                        ->update($tbl, [
                            'status'        => 1,
                            'date_modified' => time(),
                            'modified_by'   => (int)($this->session->userdata('person_id') ?: 0),
                            // podrías guardar $reason en otra tabla de bitácora si quieres
                        ]);
    }

    /* ============================
       Operaciones atómicas
       ============================ */

    /**
     * Depósito / Retiro con ajuste de saldo.
     * Campos esperados en $d:
     *  - savings_account_id, trans_type ('deposit'|'withdraw'), amount, trans_date?, description?, branch_id?
     *  - depositor_name / depositor_document (solo depósito; si no llegan se guardan como "")
     */
    public function post_simple(array $d)
    {
        $type   = strtolower(trim($d['trans_type'] ?? ''));
        $amount = (float)($d['amount'] ?? 0);
        $acc_id = (int)($d['savings_account_id'] ?? 0);

        if (!in_array($type, ['deposit','withdraw'], true)) return FALSE;
        if ($acc_id <= 0 || $amount <= 0) return FALSE;

        $acc = $this->get_account($acc_id);
        if (!$acc || !$acc->status) return FALSE;

        // Validaciones
        if ($type === 'withdraw') {
            // Plazo fijo sin vencer
            if (!empty($acc->is_fixed_term) && !$this->is_matured($acc)) return FALSE;
            // Saldo suficiente
            if ((float)$acc->current_balance < $amount) return FALSE;
        }

        $now = $d['trans_date'] ?? date('Y-m-d H:i:s');
        $operator_id = (int)($this->session->userdata('person_id') ?: 0);

        // Campos opcionales (mantienen compatibilidad con 004_alter_*.sql)
        $depositor_name     = isset($d['depositor_name'])     ? (string)$d['depositor_name']     : '';
        $depositor_document = isset($d['depositor_document']) ? (string)$d['depositor_document'] : '';
        $branch_id          = (int)($d['branch_id'] ?? 0);

        $this->db->trans_start();

        // Insert transacción
        $row = [
            'savings_account_id'  => $acc_id,
            'trans_type'          => $type,
            'amount'              => $amount,
            'trans_date'          => $now,
            'description'         => $d['description'] ?? null,
            'branch_id'           => $branch_id,
            'person_id'           => (int)$acc->person_id,        // titular de la cuenta
        ];

        // Campos de auditoría si existen
        if ($this->db->field_exists('registered_by', $this->db->dbprefix($this->table))) {
            $row['registered_by'] = $operator_id;
        }
        if ($this->db->field_exists('ip_address', $this->db->dbprefix($this->table))) {
            $row['ip_address'] = $this->input->ip_address();
        }
        if ($this->db->field_exists('status', $this->db->dbprefix($this->table))) {
            $row['status'] = 1;
        }
        if ($this->db->field_exists('date_added', $this->db->dbprefix($this->table))) {
            $row['date_added'] = time();
        }
        if ($this->db->field_exists('date_modified', $this->db->dbprefix($this->table))) {
            $row['date_modified'] = time();
        }
        if ($this->db->field_exists('modified_by', $this->db->dbprefix($this->table))) {
            $row['modified_by'] = 0;
        }
        if ($this->db->field_exists('depositor_name', $this->db->dbprefix($this->table))) {
            $row['depositor_name'] = $depositor_name;
        }
        if ($this->db->field_exists('depositor_document', $this->db->dbprefix($this->table))) {
            $row['depositor_document'] = $depositor_document;
        }

        $this->db->insert($this->table, $row);
        $tx_id = (int)$this->db->insert_id();

        // Ajuste de saldo
        $sign  = ($type === 'withdraw') ? -1 : 1;
        $delta = $sign * $amount;

        $this->db->set('current_balance', 'current_balance + '.$delta, FALSE)
                 ->where('savings_account_id', $acc_id)
                 ->update($this->db->dbprefix('savings_accounts'));
        $new_balance = (float)$acc->current_balance + (float)$delta;

        // Si existe la columna, persistimos el saldo resultante
        if ($this->db->field_exists('balance_after', $this->db->dbprefix($this->table))) {
            $this->db->where('transaction_id', $tx_id)
                    ->update($this->table, ['balance_after' => $new_balance]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $tx_id : false;
    }

    /**
     * Transferencia entre cuentas (crea 2 asientos: withdraw en origen y deposit en destino).
     */
    public function create_transfer($src_account_id, $dst_account_id, $amount, $description = '', $branch_id = 0, $actor_person_id = 0)
    {
        $amount = (float)$amount;
        $src_account_id = (int)$src_account_id;
        $dst_account_id = (int)$dst_account_id;

        if ($amount <= 0) return FALSE;
        if ($src_account_id <= 0 || $dst_account_id <= 0) return FALSE;
        if ($src_account_id === $dst_account_id) return FALSE;

        $now = date('Y-m-d H:i:s');
        $this->db->trans_start();

        // FOR UPDATE para evitar carreras
        $src = $this->db->query(
            "SELECT * FROM {$this->db->dbprefix('savings_accounts')} WHERE savings_account_id = ? FOR UPDATE",
            [$src_account_id]
        )->row();

        $dst = $this->db->query(
            "SELECT * FROM {$this->db->dbprefix('savings_accounts')} WHERE savings_account_id = ? FOR UPDATE",
            [$dst_account_id]
        )->row();

        if (!$src || !$dst || !$src->status || !$dst->status) {
            $this->db->trans_complete();
            return FALSE;
        }

        // Validaciones: saldo y plazo fijo en origen
        if ((float)$src->current_balance < $amount) {
            $this->db->trans_complete();
            return FALSE;
        }

        // ¿Es a plazo fijo y no venció?
        $src_full = $this->get_account($src_account_id);
        if (!empty($src_full->is_fixed_term) && !$this->is_matured($src_full)) {
            $this->db->trans_complete();
            return FALSE;
        }

        $gid = $this->generate_transfer_gid(); // o uniqid('', true) si no tienes UUID

        // Asiento 1: retiro en origen
        $row_withdraw = [
            'savings_account_id' => $src_account_id,
            'counterparty_account_id' => $dst_account_id,
            'trans_type'         => 'transfer',
            'transfer_group_id'    => $gid,
            'transfer_kind'        => 'withdraw',
            'amount'             => $amount,
            'trans_date'         => $now,
            'description'        => $description,
            'branch_id'          => (int)$branch_id,
            'person_id'          => (int)$src->person_id,     // titular de ORIGEN
        ];

        // Campos opcionales de auditoría
        if ($this->db->field_exists('registered_by', $this->db->dbprefix($this->table))) {
            $row_withdraw['registered_by'] = (int)$actor_person_id;
        }
        if ($this->db->field_exists('ip_address', $this->db->dbprefix($this->table))) {
            $row_withdraw['ip_address'] = $this->input->ip_address();
        }
        if ($this->db->field_exists('status', $this->db->dbprefix($this->table))) {
            $row_withdraw['status'] = 1;
        }
        if ($this->db->field_exists('date_added', $this->db->dbprefix($this->table))) {
            $row_withdraw['date_added'] = time();
        }
        if ($this->db->field_exists('date_modified', $this->db->dbprefix($this->table))) {
            $row_withdraw['date_modified'] = time();
        }
        if ($this->db->field_exists('modified_by', $this->db->dbprefix($this->table))) {
            $row_withdraw['modified_by'] = 0;
        }
        if ($this->db->field_exists('counterparty_account_id', $this->db->dbprefix($this->table))) {
            $row_withdraw['counterparty_account_id'] = $dst_account_id;
        }
        if ($this->db->field_exists('depositor_name', $this->db->dbprefix($this->table))) {
            $row_withdraw['depositor_name'] = ''; // no aplica
        }
        if ($this->db->field_exists('depositor_document', $this->db->dbprefix($this->table))) {
            $row_withdraw['depositor_document'] = ''; // no aplica
        }

        $this->db->insert($this->table, $row_withdraw);
        $withdraw_id = (int)$this->db->insert_id();

        if ($this->db->field_exists('balance_after', $this->db->dbprefix($this->table))) {
            $this->db->where('transaction_id', $withdraw_id)
                    ->update($this->table, ['balance_after' => (float)$src->current_balance - (float)$amount]);
        }

        // Saldo origen
        $this->db->set('current_balance', 'current_balance - '.$amount, FALSE)
                 ->where('savings_account_id', $src_account_id)
                 ->update($this->db->dbprefix('savings_accounts'));

        // Asiento 2: depósito en destino
        $row_deposit = [
            'savings_account_id' => $dst_account_id,
            'counterparty_account_id' => $src_account_id,
            'trans_type'         => 'transfer',
            'transfer_group_id'    => $gid,
            'transfer_kind'        => 'deposit',
            'amount'             => $amount,
            'trans_date'         => $now,
            'description'        => $description,
            'branch_id'          => (int)$branch_id,
            'person_id'          => (int)$dst->person_id,     // titular de DESTINO
        ];

        if ($this->db->field_exists('registered_by', $this->db->dbprefix($this->table))) {
            $row_deposit['registered_by'] = (int)$actor_person_id;
        }
        if ($this->db->field_exists('ip_address', $this->db->dbprefix($this->table))) {
            $row_deposit['ip_address'] = $this->input->ip_address();
        }
        if ($this->db->field_exists('status', $this->db->dbprefix($this->table))) {
            $row_deposit['status'] = 1;
        }
        if ($this->db->field_exists('date_added', $this->db->dbprefix($this->table))) {
            $row_deposit['date_added'] = time();
        }
        if ($this->db->field_exists('date_modified', $this->db->dbprefix($this->table))) {
            $row_deposit['date_modified'] = time();
        }
        if ($this->db->field_exists('modified_by', $this->db->dbprefix($this->table))) {
            $row_deposit['modified_by'] = 0;
        }
        if ($this->db->field_exists('counterparty_account_id', $this->db->dbprefix($this->table))) {
            $row_deposit['counterparty_account_id'] = $src_account_id;
        }
        if ($this->db->field_exists('depositor_name', $this->db->dbprefix($this->table))) {
            $row_deposit['depositor_name'] = ''; // no aplica
        }
        if ($this->db->field_exists('depositor_document', $this->db->dbprefix($this->table))) {
            $row_deposit['depositor_document'] = ''; // no aplica
        }

        $this->db->insert($this->table, $row_deposit);
        $deposit_id  = (int)$this->db->insert_id();

        // NEW: marcar pareja de transferencia (solo si existen las columnas)
        $tx_tbl = $this->db->dbprefix($this->table);
        $has_group = $this->db->field_exists('transfer_group_id', $tx_tbl);
        $has_role  = $this->db->field_exists('transfer_role', $tx_tbl);

        if ($has_group || $has_role) {
            $group_id = $withdraw_id; // usamos el retiro como id de grupo

            if ($has_group) {
                $this->db->where_in('transaction_id', [$withdraw_id, $deposit_id])
                        ->update($this->table, ['transfer_group_id' => $group_id]);
            }
            if ($has_role) {
                $this->db->where('transaction_id', $withdraw_id)
                        ->update($this->table, ['transfer_role' => 'withdraw']);
                $this->db->where('transaction_id', $deposit_id)
                        ->update($this->table, ['transfer_role' => 'deposit']);
            }
        }

        if ($this->db->field_exists('balance_after', $this->db->dbprefix($this->table))) {
            $this->db->where('transaction_id', $deposit_id)
                    ->update($this->table, ['balance_after' => (float)$dst->current_balance + (float)$amount]);
        }

        // Saldo destino
        $this->db->set('current_balance', 'current_balance + '.$amount, FALSE)
                 ->where('savings_account_id', $dst_account_id)
                 ->update($this->db->dbprefix('savings_accounts'));

        $this->db->trans_complete();
        return $this->db->trans_status() ? ['withdraw_id' => $withdraw_id, 'deposit_id' => $deposit_id] : false;
    }

    public function get_accounts_state($src_id, $dst_id)
    {
        // NOTA: ajusta los nombres de columna aquí ↓↓↓
        // - sa.status           → 1=activa (cámbialo si es is_active)
        // - sa.time_deposit     → 1=plazo fijo
        // - sa.maturity_date    → fecha de vencimiento (si aplica)
        // - sa.available_balance→ si NO existe, calculamos por movimientos
        // - sat.min_transfer_amount → mínimo requerido por tipo de cuenta (si no existe, lo manejamos en el controlador)

        $sql = "
        SELECT 
            sa.savings_account_id,
            sa.account_number,
            sa.currency,
            COALESCE(sa.status, 1) AS is_active,             -- <== cambia a sa.is_active si así se llama
            COALESCE(sa.time_deposit, 0) AS is_time_deposit, -- <== 1 si es plazo fijo
            sa.maturity_date,                                -- <== si usas otro nombre, cámbialo
            COALESCE(sa.available_balance, 
                ROUND(SUM(CASE 
                    WHEN tx.trans_type='deposit'  THEN tx.amount
                    WHEN tx.trans_type='withdraw' THEN -tx.amount
                    ELSE 0 END),2)
            ) AS available_balance,
            COALESCE(sat.min_transfer_amount, 0) AS min_transfer_amount
        FROM {$this->db->dbprefix('savings_accounts')} sa
        LEFT JOIN {$this->db->dbprefix('savings_account_transactions')} tx
            ON tx.savings_account_id = sa.savings_account_id
        LEFT JOIN {$this->db->dbprefix('savings_account_types')} sat
            ON sat.savings_account_type_id = sa.savings_account_type_id
        WHERE sa.savings_account_id IN (?,?)
        GROUP BY 
            sa.savings_account_id, sa.account_number, sa.currency, 
            is_active, is_time_deposit, sa.maturity_date, sa.available_balance, sat.min_transfer_amount
        ";
        $rows = $this->db->query($sql, [(int)$src_id, (int)$dst_id])->result();
        $out = ['src'=>null,'dst'=>null];

        foreach ($rows as $r) {
            if ((int)$r->savings_account_id === (int)$src_id) $out['src'] = $r;
            if ((int)$r->savings_account_id === (int)$dst_id) $out['dst'] = $r;
        }
        return $out;
    }

    public function calc_month_interest($acc_id, $year, $month)
    {
        $acc_id = (int)$acc_id;

        // 2.1 Toma parámetros
        $CI = &get_instance();
        $cfg = $CI->config->item('interest') ?: ['day_count'=>365,'rounding'=>'per_day'];
        $DAY_COUNT = (int)($cfg['day_count'] ?? 365);
        $ROUNDING  = (string)($cfg['rounding'] ?? 'per_day');

        // 2.2 Tasa anual desde el tipo de cuenta (APY si disponible; si no, interest_rate %)
        $row = $this->db->select('a.savings_account_id,
                          a.savings_account_type_id,
                          t.interest_rate,
                          t.interest_rate_apy', false)
            ->from($this->db->dbprefix('savings_accounts').' a')
            ->join($this->db->dbprefix('savings_account_types').' t','t.savings_account_type_id = a.savings_account_type_id')
            ->where('a.savings_account_id', $acc_id)
            ->limit(1)->get()->row();


        if (!$row) return ['amount'=>0.0,'daily'=>[]];

        $apy_raw  = (float)($row->interest_rate_apy ?? 0); // puede venir 0.12 o 12.00
        $apr_pct  = (float)($row->interest_rate ?? 0);     // típico 12.00

        if ($apy_raw > 0) {
            $annual = ($apy_raw > 1.0) ? ($apy_raw / 100.0) : $apy_raw; // normaliza a fracción
        } else {
            $annual = $apr_pct / 100.0; // % -> fracción
        }
        if ($annual <= 0) return ['amount'=>0.0,'daily'=>[]];

        // 2.3 Rango del mes
        $start_dt = new DateTime(sprintf('%04d-%02d-01 00:00:00', $year, $month));
        $end_dt   = (clone $start_dt)->modify('last day of this month 23:59:59');
        $start = $start_dt->format('Y-m-d H:i:s');
        $end   = $end_dt->format('Y-m-d H:i:s');

        // 2.4 Última transacción del día dentro del mes (para tomar balance_after = EOD)
        $day_last = $this->db->query("
            SELECT d, MAX(transaction_id) AS last_tx_id
            FROM (
            SELECT DATE(trans_date) AS d, transaction_id
            FROM {$this->db->dbprefix('savings_account_transactions')}
            WHERE savings_account_id = ?
                AND trans_date BETWEEN ? AND ?
            ) x
            GROUP BY d
            ORDER BY d ASC
        ", [$acc_id, $start, $end])->result();

        $last_map = []; foreach ($day_last as $r) { $last_map[$r->d] = (int)$r->last_tx_id; }

        // 2.5 Mapear esos IDs a balance_after
        $balance_map = [];
        if (!empty($last_map)) {
            $ids = array_values($last_map);
            foreach (array_chunk($ids, 1000) as $chunk) {
                $in = implode(',', array_map('intval', $chunk));
                $rs = $this->db->query("
                    SELECT transaction_id, balance_after
                    FROM {$this->db->dbprefix('savings_account_transactions')}
                    WHERE transaction_id IN ($in)
                ")->result();
                $by_id = [];
                foreach ($rs as $b) $by_id[(int)$b->transaction_id] = (float)$b->balance_after;
                foreach ($last_map as $d => $txid) {
                    if (isset($by_id[$txid])) $balance_map[$d] = $by_id[$txid];
                }
            }
        }

        // 2.6 “Seed” anterior al inicio del mes (último balance_before del día previo)
        $seed_row = $this->db->query("
            SELECT balance_after
            FROM {$this->db->dbprefix('savings_account_transactions')}
            WHERE savings_account_id = ?
            AND trans_date < ?
            ORDER BY trans_date DESC, transaction_id DESC
            LIMIT 1
        ", [$acc_id, $start])->row();
        $prev_eod = (float)($seed_row->balance_after ?? 0);

        // 2.7 Recorrer cada día, arrastrando EOD si no hubo movimientos
        $daily_rate = $annual / max(1,$DAY_COUNT);
        $daily = [];
        $sum = 0.0;
        $cursor = clone $start_dt;
        while ($cursor <= $end_dt) {
            $d = $cursor->format('Y-m-d');
            $eod = array_key_exists($d, $balance_map) ? (float)$balance_map[$d] : $prev_eod;

            $int_d = $eod * $daily_rate;
            if ($ROUNDING === 'per_day') { $int_d = round($int_d, 2); }
            $sum += $int_d;

            $daily[$d] = ['balance_eod'=>$eod, 'interest'=>($ROUNDING==='per_day'? $int_d : round($int_d, 6))];
            $prev_eod = $eod;
            $cursor->modify('+1 day');
        }
        if ($ROUNDING !== 'per_day') { $sum = round($sum, 2); }

        return ['amount'=>$sum, 'daily'=>$daily, 'annual_rate'=>$annual, 'day_count'=>$DAY_COUNT];
    }

    /**
     * Recalcula y persiste balance_after de TODAS las transacciones de una cuenta,
     * recorriendo en orden DESC por fecha y transaction_id.
     * Usa el current_balance como seed y va "deshaciendo" movimientos hacia atrás.
     */
    public function rebuild_balance_after($account_id)
    {
        $account_id = (int)$account_id;

        // 1) seed = saldo actual de la cuenta
        $acc = $this->db->select('current_balance')
            ->from($this->db->dbprefix('savings_accounts'))
            ->where('savings_account_id', $account_id)
            ->get()->row();
        $running = (float)($acc->current_balance ?? 0);

        // 2) Traer todas las transacciones de la cuenta (más recientes primero)
        $txs = $this->db->select('transaction_id, trans_type, amount')
            ->from($this->db->dbprefix('savings_account_transactions'))
            ->where('savings_account_id', $account_id)
            ->order_by('trans_date', 'DESC')
            ->order_by('transaction_id', 'DESC')
            ->get()->result();

        if (!$txs) return;

        // 3) Recalcular balance_after y persistir
        $updates = [];
        foreach ($txs as $t) {
            // balance después de ESTA transacción es el running actual
            $updates[] = [
                'transaction_id' => (int)$t->transaction_id,
                'balance_after'  => $running,
            ];

            // mover el running "antes" de esta transacción
            $sign = (strtolower($t->trans_type) === 'withdraw') ? -1 : 1;
            $running -= $sign * (float)$t->amount;
        }

        // 4) Persistir en lotes
        if (!empty($updates)) {
            // Si usas CI3: update_batch por transaction_id
            $this->db->update_batch(
                $this->db->dbprefix('savings_account_transactions'),
                $updates,
                'transaction_id'
            );
        }
    }

    private function generate_transfer_gid(): string
    {
        if (function_exists('random_bytes')) {
            return 'tg_' . bin2hex(random_bytes(8));
        }
        return 'tg_' . uniqid('', true);
    }

}
