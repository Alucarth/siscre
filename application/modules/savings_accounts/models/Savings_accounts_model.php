<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_accounts_model extends CI_Model
{
    protected $table = 'savings_accounts';

    public function get_all($only_active = true)
    {
        $this->db
            ->select('sa.*, sat.name AS type_name, p.first_name, p.last_name')
            ->from($this->table . ' sa')
            ->join('savings_account_types sat', 'sat.savings_account_type_id = sa.savings_account_type_id')
            ->join('people p', 'p.person_id = sa.person_id');

        if ($only_active) {
            $this->db->where('sa.status', 1);
        } else {
            $this->db->where('sa.status', 0);
        }

        return $this->db
            ->order_by('sa.opening_date', 'DESC')
            ->get()
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('savings_account_id',$id)
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        // 1) Generar account_number si no viene en $data
        if (empty($data['account_number'])) {
            // Definir prefijo (ajústalo si quieres otro)
            $prefix = 'SA-';
            // Tomar el último número generado
            $row = $this->db
                ->select('account_number')
                ->like('account_number', $prefix, 'after')
                ->order_by('savings_account_id','DESC')
                ->limit(1)
                ->get($this->table)
                ->row();

            if ($row) {
                // Extraer parte numérica y sumar 1
                $last = (int) str_replace($prefix, '', $row->account_number);
                $next = $last + 1;
            } else {
                $next = 1;
            }
            // Formatear con 4 dígitos (por ejemplo): SA-0001, SA-0002, …
            $data['account_number'] = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        // 2) Maturity date si aplica (plazo fijo)
        $tipo = $this->db->get_where('savings_account_types',
            ['savings_account_type_id'=>$data['savings_account_type_id']])->row();
        if ($tipo && $tipo->is_fixed_term) {
            $data['maturity_date'] = date(
            'Y-m-d',
            strtotime("+{$tipo->term_days} days", strtotime($data['opening_date']))
            );
        }

        // 3) Resto de campos
        $data['date_added']      = time();
        $data['added_by']        = $this->session->userdata('person_id');
        $data['current_balance'] = $data['initial_balance'] ?? 0;

        return $this->db->insert($this->table, $data);
    }


    public function update($id,$data)
    {
        $data['date_modified'] = time();
        $data['modified_by']   = $this->session->userdata('person_id');
        return $this->db
            ->where('savings_account_id',$id)
            ->update($this->table,$data);
    }

    /**
     * Soft‐delete: deshabilita la cuenta en lugar de borrarla.
     *
     * @param int $id  ID de la cuenta
     * @return bool     TRUE si se actualizó correctamente
     */
    public function delete($id)
    {
        $data = [
            'status'        => 0,
            'date_modified' => time(),
            'modified_by'   => $this->session->userdata('person_id')
        ];

        return $this->db
            ->where('savings_account_id', $id)
            ->update($this->table, $data);
    }

}
