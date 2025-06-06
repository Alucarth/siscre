<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_types_model extends CI_Model
{
    /** Nombre de la tabla sin prefijo */
    protected $table = 'savings_account_types';

    /**
     * Genera un código único basado en las primeras letras del nombre
     * y un contador secuencial (XXX-001, XXX-002…).
     */
    protected function generate_code(string $name): string
    {
        // Toma las 3 primeras consonantes/letras
        $prefix = strtoupper(preg_replace('/[^A-Z]/i','',$name));
        $prefix = substr($prefix, 0, 3);

        // Busca el último existente con ese prefijo
        $row = $this->db
            ->select('code')
            ->like('code', $prefix.'-', 'after')
            ->order_by('code','DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        $lastNum = $row 
            ? (int) explode('-', $row->code)[1] 
            : 0;

        return sprintf('%s-%03d', $prefix, $lastNum + 1);
    }


    public function get_all()
    {
        return $this->db
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get($id)
    {
        return $this->db
            ->where('savings_account_type_id', $id)
            ->get($this->table)
            ->row();
    }

   public function insert($data)
    {
        if (empty($data['code']) && !empty($data['name'])) {
            $data['code'] = $this->generate_code($data['name']);
        }

        $data['date_added'] = time();
        $data['added_by']   = $this->session->userdata('person_id');
        return $this->db->insert($this->table, $data);
    }


    public function update($id, $data)
    {
        $data['date_modified'] = time();
        $data['modified_by']   = $this->session->userdata('person_id');
        return $this->db
            ->where('savings_account_type_id', $id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where('savings_account_type_id', $id)
            ->delete($this->table);
    }
}
