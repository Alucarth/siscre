<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_types_model extends CI_Model
{
    protected $table = 'savings_account_types'; // usa prefijo c19_ automáticamente

    /* =========================
       Helpers de normalización
       ========================= */
    private function calc_apy($apr_percent)
    {
        // $apr_percent viene como % anual (p.ej. 12.00)
        $apr = max(0.0, (float)$apr_percent);
        $daily = $apr / 100 / 365;          // interés diario (divisor parametrizable si luego lo movemos a config)
        $apy = pow(1 + $daily, 365) - 1;    // capitalización diaria
        return round($apy, 4);              // 4 decimales como en la columna
    }

    private function generate_code($name)
    {
        // Prefijo: 3 letras del nombre (solo A-Z), o "SAT" por defecto
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/','', (string)$name), 0, 3));
        if ($prefix === '') $prefix = 'SAT';

        // Buscar último correlativo con ese prefijo (CODE con formato PREFIX-###)
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
        return sprintf('%s-%03d', $prefix, $next);
    }

    private function alias_from_code($code)
    {
        // CAJ-001 -> CAJ001 (sin guion)
        $code = strtoupper(trim((string)$code));
        return str_replace('-', '', $code);
    }

    /* =========================
       CRUD
       ========================= */
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
        // Campos base
        $name          = trim($data['name'] ?? '');
        $interest_rate = (float)($data['interest_rate'] ?? 0);
        $apy           = $this->calc_apy($interest_rate);

        $row = [
            'name'              => $name,
            'interest_rate'     => $interest_rate,
            'interest_rate_apy' => $apy,
            'description'       => $data['description'] ?? null,
            'status'            => isset($data['status']) ? (int)$data['status'] : 1,
            'is_fixed_term'     => isset($data['is_fixed_term']) ? (int)$data['is_fixed_term'] : 0,
            'term_days'         => isset($data['term_days']) ? (int)$data['term_days'] : 0,
            'date_added'        => time(),
            'added_by'          => (int)$this->session->userdata('person_id'),
        ];

        if ($row['is_fixed_term'] && $row['term_days'] <= 0) {
            $row['term_days'] = 1;
        }

        // Generar CODE automático (solo en alta)
        $row['code']  = $this->generate_code($row['name']);
        // Generar alias desde code
        $row['account_type_alias'] = $this->alias_from_code($row['code']);

        $ok = $this->db->insert($this->table, $row);
        return $ok;
    }

    public function update($id, $data)
    {
        // No tocamos CODE en update (permanece estable)
        $name          = trim($data['name'] ?? '');
        $interest_rate = (float)($data['interest_rate'] ?? 0);
        $apy           = $this->calc_apy($interest_rate);

        $row = [
            'name'              => $name,
            'interest_rate'     => $interest_rate,
            'interest_rate_apy' => $apy,
            'description'       => $data['description'] ?? null,
            'status'            => isset($data['status']) ? (int)$data['status'] : 1,
            'is_fixed_term'     => isset($data['is_fixed_term']) ? (int)$data['is_fixed_term'] : 0,
            'term_days'         => isset($data['term_days']) ? (int)$data['term_days'] : 0,
            'date_modified'     => time(),
            'modified_by'       => (int)$this->session->userdata('person_id'),
        ];

        if ($row['is_fixed_term'] && $row['term_days'] <= 0) {
            $row['term_days'] = 1;
        }

        // Si permites editar manualmente el alias desde el formulario,
        // respétalo; si viene vacío o no viene, lo recalculamos desde el CODE guardado.
        if (array_key_exists('account_type_alias', $data) && trim((string)$data['account_type_alias']) !== '') {
            $row['account_type_alias'] = strtoupper(str_replace('-', '', trim((string)$data['account_type_alias'])));
        } else {
            $curr = $this->get($id);
            if ($curr && !empty($curr->code)) {
                $row['account_type_alias'] = $this->alias_from_code($curr->code);
            }
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
            ->update($this->table, [
                'status'        => 0,
                'date_modified' => time(),
                'modified_by'   => (int)$this->session->userdata('person_id')
            ]);
    }
}

