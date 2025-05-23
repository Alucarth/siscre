<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_types_model extends CI_Model {
    protected $table = 'c19_account_types';

    /**
     * Genera un código único basado en las primeras letras del nombre
     * y un contador secuencial (XXX-001, XXX-002…).
     */
    protected function generate_code(string $name): string
    {
        // Toma las 3 primeras consonantes/lo que quede de la palabra
        $prefix = strtoupper(preg_replace('/[^A-Z]/i', '', $name));
        $prefix = substr($prefix, 0, 3);

        // Busca el último código con ese prefijo
        $this->db
            ->select('code')
            ->like('code', $prefix.'-', 'after')
            ->order_by('account_type_id', 'DESC')
            ->limit(1);

        $row = $this->db->get($this->table)->row();
        $lastNum = $row 
            ? (int) explode('-', $row->code)[1] 
            : 0;

        // Siguiente número con padding
        $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        return $prefix . '-' . $nextNum;
    }


    public function get_all() {
        return $this->db
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get($id) {
        return $this->db
            ->where('account_type_id', $id)
            ->get($this->table)
            ->row();
    }

    public function insert($data) {
        // Generar el código si no viene del formulario
        if (empty($data['code'])) {
            $data['code'] = $this->generate_code($data['name']);
        }

        $data['date_added']   = time();
        $data['added_by']     = $this->session->userdata('person_id');
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        $data['date_modified'] = time();
        $data['modified_by']   = $this->session->userdata('person_id');
        return $this->db
            ->where('account_type_id', $id)
            ->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db
            ->where('account_type_id', $id)
            ->delete($this->table);
    }
}
