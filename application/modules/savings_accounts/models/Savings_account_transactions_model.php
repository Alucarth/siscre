<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_transactions_model extends CI_Model
{
    protected $table = 'savings_account_transactions';

    /**
     * Obtener transacciones con filtros opcionales.
     */
    public function get_all(array $f = [])
    {
        $this->db
            ->select('tx.*, sa.account_number, p.first_name, p.last_name')
            ->from($this->table.' tx')
            ->join('savings_accounts sa','sa.savings_account_id=tx.savings_account_id')
            ->join('people p','p.person_id=sa.person_id');

        if (!empty($f['account_id'])) {
            $this->db->where('tx.savings_account_id',$f['account_id']);
        }
        if (!empty($f['trans_type'])) {
            $this->db->where('tx.trans_type',$f['trans_type']);
        }
        if (!empty($f['date_from'])) {
            $this->db->where('tx.trans_date >=',$f['date_from']);
        }
        if (!empty($f['date_to'])) {
            $this->db->where('tx.trans_date <=',$f['date_to']);
        }

        return $this->db
            ->order_by('tx.trans_date','DESC')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('transaction_id',$id)
            ->get($this->table)
            ->row();
    }

    /**
     * Inserta una transacción y ajusta el saldo de la cuenta.
     */
    public function insert($d)
    {
        $this->db->trans_start();

        // registrar transacción
        $d['person_id'] = $this->session->userdata('person_id');
        $d['trans_date'] = $d['trans_date'] ?? date('Y-m-d H:i:s');
        $this->db->insert($this->table,$d);

        // ajustar saldo
        $mult = ($d['trans_type']=='withdraw' || $d['trans_type']=='transfer') ? -1 : 1;
        $delta = $d['amount'] * $mult;
        $this->db->set('current_balance','current_balance + '.$delta, FALSE)
                 ->where('savings_account_id',$d['savings_account_id'])
                 ->update('savings_accounts');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update($id, $d)
    {
        // para simplificar, no ajustamos saldo histórico aquí
        return $this->db
            ->where('transaction_id',$id)
            ->update($this->table,$d);
    }

    /**
     * Soft-delete: deshabilita la transacción (para trazabilidad).
     */
    public function delete($id)
    {
        return $this->db
            ->where('transaction_id',$id)
            ->update($this->table, ['status'=>0]);
    }

    /**
     * Inserta un depósito y actualiza el saldo.
     */
    public function deposit(array $d)
    {
        $this->db->trans_start();

        // 1) Preparar datos
        $d['trans_type']       = 'deposit';
        $d['person_id']        = $this->session->userdata('person_id');      // operador
        $d['registered_by']    = $d['person_id'];
        $d['ip_address']       = $this->input->ip_address();
        $d['date_added']       = time();
        $d['status']           = 1;

        // 2) Insertar
        $this->db->insert($this->table, $d);

        // 3) Ajustar saldo
        $this->db
            ->set('current_balance', 'current_balance + ' . $d['amount'], FALSE)
            ->where('savings_account_id', $d['savings_account_id'])
            ->update('savings_accounts');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
