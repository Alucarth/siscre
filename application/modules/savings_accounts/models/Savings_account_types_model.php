<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_types_model extends CI_Model
{
    protected $table = 'savings_account_types'; // usa prefijo c19_ automáticamente

    public function get_all()
    {
        return $this->db
            ->order_by('savings_account_type_id','DESC')
            ->get($this->table)
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('savings_account_type_id', (int)$id)
            ->get($this->table)
            ->row();
    }

    public function insert($data)
    {
        // Asegurar defaults y normalizaciones
        $row = [
            'name'           => trim($data['name'] ?? ''),
            'interest_rate'  => (float)($data['interest_rate'] ?? 0),
            'interest_rate_apy' => $this->calc_apy($row['interest_rate']),
            'description'    => $data['description'] ?? null,
            'status'         => isset($data['status']) ? (int)$data['status'] : 1,
            'is_fixed_term'  => isset($data['is_fixed_term']) ? (int)$data['is_fixed_term'] : 0,
            'term_days'      => isset($data['term_days']) ? (int)$data['term_days'] : 0,
            'date_added'     => time(),
            'added_by'       => (int)$this->session->userdata('person_id'),
        ];

        // Si es plazo fijo, term_days debe ser >=1
        if ($row['is_fixed_term'] && $row['term_days'] <= 0) {
            $row['term_days'] = 1;
        }

        // Generar CODE automático sólo en alta
        $row['code'] = $this->generate_code($row['name']);

        $ok = $this->db->insert($this->table, $row);
        return $ok;
    }

    public function update($id, $data)
    {
        $row = [
            'name'           => trim($data['name'] ?? ''),
            'interest_rate'  => (float)($data['interest_rate'] ?? 0),
            'interest_rate_apy' => $this->calc_apy($row['interest_rate']),
            'description'    => $data['description'] ?? null,
            'status'         => isset($data['status']) ? (int)$data['status'] : 1,
            'is_fixed_term'  => isset($data['is_fixed_term']) ? (int)$data['is_fixed_term'] : 0,
            'term_days'      => isset($data['term_days']) ? (int)$data['term_days'] : 0,
            'date_modified'  => time(),
            'modified_by'    => (int)$this->session->userdata('person_id'),
        ];

        if ($row['is_fixed_term'] && $row['term_days'] <= 0) {
            $row['term_days'] = 1;
        }

        return $this->db
            ->where('savings_account_type_id', (int)$id)
            ->update($this->table, $row);
    }

    public function delete($id)
    {
        // Soft delete – desactivar
        return $this->db
            ->where('savings_account_type_id', (int)$id)
            ->update($this->table, ['status'=>0, 'date_modified'=>time(), 'modified_by'=>(int)$this->session->userdata('person_id')]);
    }

    private function generate_code($name)
    {
        // Prefijo: 3 letras del nombre (solo A-Z), o "SAT" por defecto
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/','', $name), 0, 3));
        if ($prefix === '') $prefix = 'SAT';

        // Buscar último correlativo con ese prefijo
        $like = $prefix . '-%';
        $last = $this->db->select('code')
            ->like('code', $prefix.'-', 'after')
            ->order_by('savings_account_type_id', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        $next = 1;
        if ($last && preg_match('/^'.preg_quote($prefix,'/').'\-(\d{3,})$/', $last->code, $m)) {
            $next = ((int)$m[1]) + 1;
        }

        // Formato PREFIX-001
        return sprintf('%s-%03d', $prefix, $next);
    }

    // 1) Agrega en la clase (por ejemplo debajo de generate_code):
    private function calc_apy($apr_percent)
    {
        // $apr_percent viene como % anual (p.ej. 12.00)
        $apr = max(0.0, (float)$apr_percent);
        $daily = $apr / 100 / 365;                 // interés diario
        $apy = pow(1 + $daily, 365) - 1;           // capitalización diaria
        return round($apy, 4);                      // 4 decimales como en la columna
    }

}
