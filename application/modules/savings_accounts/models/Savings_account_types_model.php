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
        $this->last_error = '';
        // Campos base
        $name          = trim($data['name'] ?? '');
        $interest_rate = (float)($data['interest_rate'] ?? 0);
        $apy           = $this->calc_apy($interest_rate);

        // 🔒 Validar nombre único (alta)
        if ($this->name_exists($name, null)) {
            $this->last_error = 'Ya existe un tipo de cuenta con ese nombre.';
            return false;
        }

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
        $this->last_error = '';
        $id = (int)$id;

        if ($id <= 0) {
            $this->last_error = 'ID de tipo de cuenta inválido.';
            return false;
        }

        // Registro actual
        $curr = $this->get($id);
        if (!$curr) {
            $this->last_error = 'Tipo de cuenta no encontrado.';
            return false;
        }

        // Normalizar datos de entrada
        $name          = trim($data['name'] ?? '');
        $interest_rate = (float)($data['interest_rate'] ?? 0);
        $is_fixed_term = isset($data['is_fixed_term']) ? (int)$data['is_fixed_term'] : 0;
        $term_days     = isset($data['term_days']) ? (int)$data['term_days'] : 0;
        $status        = isset($data['status']) ? (int)$data['status'] : 1;
        $description   = $data['description'] ?? null;

        $has_accounts = $this->has_accounts($id);

        /* ===========================================================
        CASO 1: EL TIPO YA TIENE CUENTAS ASOCIADAS
        → Solo se puede cambiar descripción y status.
        =========================================================== */
        if ($has_accounts) {
            // Ver si intentan cambiar campos estructurales
            $struct_changed = false;

            if ($name !== trim((string)$curr->name)) {
                $struct_changed = true;
            }
            if ($interest_rate != (float)$curr->interest_rate) {
                $struct_changed = true;
            }
            if ($is_fixed_term != (int)$curr->is_fixed_term) {
                $struct_changed = true;
            }
            if ($term_days != (int)$curr->term_days) {
                $struct_changed = true;
            }

            if ($struct_changed) {
                $this->last_error =
                    'Este tipo de cuenta ya tiene cuentas asociadas. '
                    .'Solo puede modificar la descripción y el estado (habilitado/deshabilitado).';
                return false;
            }

            // Solo actualizar meta: descripción + estado
            $row = [
                'description'   => $description,
                'status'        => $status,
                'date_modified' => time(),
                'modified_by'   => (int)$this->session->userdata('person_id'),
            ];

            return $this->db
                ->where('savings_account_type_id', $id)
                ->update($this->table, $row);
        }

        /* ===========================================================
        CASO 2: SIN CUENTAS ASOCIADAS
        → Se permite editar todo (nombre, tasa, plazo, etc.)
        =========================================================== */

        $apy = $this->calc_apy($interest_rate);

        // Validar nombre único solo si realmente cambia
        if ($name !== trim((string)$curr->name) && $this->name_exists($name, $id)) {
            $this->last_error = 'Ya existe otro tipo de cuenta con ese nombre.';
            return false;
        }

        if ($is_fixed_term && $term_days <= 0) {
            $term_days = 1;
        }

        $row = [
            'name'              => $name,
            'interest_rate'     => $interest_rate,
            'interest_rate_apy' => $apy,
            'description'       => $description,
            'status'            => $status,
            'is_fixed_term'     => $is_fixed_term,
            'term_days'         => $term_days,
            'date_modified'     => time(),
            'modified_by'       => (int)$this->session->userdata('person_id'),
        ];

        // Alias: mantenemos la lógica existente, pero sin complicarla
        if (array_key_exists('account_type_alias', $data)
            && trim((string)$data['account_type_alias']) !== ''
        ) {
            $row['account_type_alias'] = strtoupper(
                str_replace('-', '', trim((string)$data['account_type_alias']))
            );
        } else {
            if (!empty($curr->code)) {
                $row['account_type_alias'] = $this->alias_from_code($curr->code);
            }
        }

        return $this->db
            ->where('savings_account_type_id', $id)
            ->update($this->table, $row);
    }

    // En Savings_account_types_model

    public $last_error = '';

    private function name_exists($name, $exclude_id = null)
    {
        $name = trim((string)$name);
        if ($name === '') {
            return false;
        }

        $this->db->from($this->table);
        // Comparación case-insensitive
        $this->db->where('LOWER(name) =', mb_strtolower($name, 'UTF-8'));

        if (!empty($exclude_id)) {
            $this->db->where('savings_account_type_id !=', (int)$exclude_id);
        }

        return $this->db->count_all_results() > 0;
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

    public function has_accounts($type_id)
    {
        $type_id = (int)$type_id;
        if ($type_id <= 0) {
            return false;
        }

        $tbl_sa = $this->db->dbprefix('savings_accounts');

        return $this->db->from($tbl_sa)
                        ->where('savings_account_type_id', $type_id)
                        ->limit(1)
                        ->count_all_results() > 0;
    }

    public function update_meta($id, array $data)
    {
        $this->last_error = '';

        $row = [
            'description'   => $data['description'] ?? null,
            'date_modified' => time(),
            'modified_by'   => (int)$this->session->userdata('person_id'),
        ];

        if (isset($data['status'])) {
            $row['status'] = (int)$data['status'];
        }

        return $this->db
            ->where('savings_account_type_id', (int)$id)
            ->update($this->table, $row);
    }

    public function toggle_status($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        // Traer estado actual
        $row = $this->get($id);
        if (!$row) {
            return false;
        }

        // Si no tiene campo status, no hacemos nada
        if (!property_exists($row, 'status')) {
            return false;
        }

        $current = (int)$row->status;
        $new     = $current === 1 ? 0 : 1;

        $ok = $this->db
            ->where('savings_account_type_id', $id)
            ->update($this->table, [
                'status'        => $new,
                'date_modified' => time(),
                'modified_by'   => (int)$this->session->userdata('person_id'),
            ]);

        if (!$ok) {
            return false;
        }

        // Devolvemos el nuevo estado (0 o 1) para que el controlador sepa qué pasó
        return $new;
    }

}

