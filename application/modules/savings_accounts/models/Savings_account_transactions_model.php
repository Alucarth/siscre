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
    public function delete($id)
    {
        // Si la tabla tiene 'status'
        if ($this->db->field_exists('status', $this->db->dbprefix($this->table))) {
            return $this->db
                ->where('transaction_id',(int)$id)
                ->update($this->table, [
                    'status'        => 0,
                    'date_modified' => time(),
                    'modified_by'   => (int)($this->session->userdata('person_id') ?: 0),
                ]);
        }

        // Fallback: eliminar físico (no recomendado, pero evita error)
        return $this->db->where('transaction_id',(int)$id)->delete($this->table);
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
                 ->update('savings_accounts');

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

        // Asiento 1: retiro en origen
        $row_withdraw = [
            'savings_account_id' => $src_account_id,
            'trans_type'         => 'withdraw',
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

        // Saldo origen
        $this->db->set('current_balance', 'current_balance - '.$amount, FALSE)
                 ->where('savings_account_id', $src_account_id)
                 ->update('savings_accounts');

        // Asiento 2: depósito en destino
        $row_deposit = [
            'savings_account_id' => $dst_account_id,
            'trans_type'         => 'deposit',
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

        // Saldo destino
        $this->db->set('current_balance', 'current_balance + '.$amount, FALSE)
                 ->where('savings_account_id', $dst_account_id)
                 ->update('savings_accounts');

        $this->db->trans_complete();
        return $this->db->trans_status() ? ['withdraw_id' => $w_id, 'deposit_id' => $d_id] : false;
    }
}
