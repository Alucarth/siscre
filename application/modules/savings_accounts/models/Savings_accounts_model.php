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

    public function get_all_with_person_active()
    {
        return $this->db
            ->select("sa.*, sat.name AS type_name,
                    CONCAT(COALESCE(p.first_name,''),' ',COALESCE(p.last_name,'')) AS person_name")
            ->from('savings_accounts sa')
            ->join('savings_account_types sat','sat.savings_account_type_id=sa.savings_account_type_id')
            ->join('people p','p.person_id = sa.person_id','left')
            ->where('sa.status', 1) // solo cuentas activas para operar
            ->order_by('p.first_name','asc')
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

    public function get_with_type($id)
    {
        return $this->db
            ->select('sa.*, sat.name AS type_name, sat.is_fixed_term, sat.term_days')
            ->from('savings_accounts sa')
            ->join('savings_account_types sat','sat.savings_account_type_id = sa.savings_account_type_id','inner')
            ->where('sa.savings_account_id', $id)
            ->get()->row();
    }

    public function adjust_balance($id, $delta)
    {
        // Usa expresión para evitar race conditions simples
        $this->db->set('current_balance', "current_balance + (". $this->db->escape($delta) .")", false)
                ->where('savings_account_id', $id)
                ->update('savings_accounts');
    }

    public function insert($data)
    {
        // 1) Generar account_number si no viene en $data
        if (empty($data['account_number'])) {
            $type_id   = (int)$data['savings_account_type_id'];
            $person_id = (int)$data['person_id'];
            $data['account_number'] = $this->compose_account_number($type_id, $person_id);
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

    /**
     * Cambia el estado de la cuenta (1=activa, 0=inactiva) con auditoría opcional.
     */
    public function set_status($account_id, $status, $reason = '', $actor_id = 0)
    {
        $account_id = (int)$account_id;
        $status     = (int)$status ? 1 : 0;
        if ($account_id <= 0) return false;

        $stamp  = date('Y-m-d H:i:s');
        $actor  = (int)$actor_id;
        $action = $status ? 'REACTIVAR' : 'DESHABILITAR';
        $reason = trim((string)$reason);
        if ($reason === '') $reason = 'Sin motivo especificado';

        $this->db->set('status', $status);
        $this->db->set('date_modified', time());
        $this->db->set('modified_by', $actor);

        // Si existe la columna comments, anexamos una línea de auditoría.
        if ($this->db->field_exists('comments', $this->db->dbprefix($this->table))) {
            $this->db->set(
                'comments',
                "CONCAT(COALESCE(comments,''), '\n[$stamp] $action por $actor: $reason')",
                false
            );
        }

        return $this->db
            ->where('savings_account_id', $account_id)
            ->update($this->db->dbprefix($this->table));
    }

    /** Helpers opcionales por si quieres llamarlos directo */
    public function reactivate($account_id, $reason = '', $actor_id = 0)
    {
        return $this->set_status($account_id, 1, $reason, $actor_id);
    }
    public function disable_account($account_id, $reason = '', $actor_id = 0)
    {
        return $this->set_status($account_id, 0, $reason, $actor_id);
    }

    private function person_identifier($person_id)
    {
        $pid = (int)$person_id;

        // 1) CI desde leads.id_no (match: leads.customer_id == accounts.person_id), solo dígitos
        $lead = $this->db->select('id_no')
                        ->from('leads')           // CI aplicará prefijo c19_
                        ->where('customer_id', $pid)
                        ->limit(1)
                        ->get()->row();

        if ($lead) {
            $digits = preg_replace('/\D+/', '', (string)$lead->id_no); // quita todo salvo 0-9
            if ($digits !== '') {
                return $digits;
            }
        }

        // 2) Fallback: person_id (ya es numérico)
        return (string)$pid;
    }

    private function type_alias($type_id)
    {
        $t = $this->db->select('account_type_alias, code, name')
                    ->from('savings_account_types')
                    ->where('savings_account_type_id', (int)$type_id)
                    ->get()->row();
        if ($t && !empty($t->account_type_alias)) return strtoupper($t->account_type_alias);
        if ($t && !empty($t->code))               return strtoupper(str_replace('-', '', $t->code));
        // Fallback muy defensivo
        $pre = strtoupper(substr(preg_replace('/[^A-Za-z]/','', (string)($t->name ?? 'SAT')), 0, 3));
        if ($pre === '') $pre = 'SAT';
        return $pre.'001';
    }

    private function next_seq_for_person_type($person_id, $type_id)
    {
        $row = $this->db->select('COUNT(1) AS c', FALSE)
                        ->from('savings_accounts')
                        ->where('person_id', (int)$person_id)
                        ->where('savings_account_type_id', (int)$type_id)
                        ->where('status', 1) // solo activas
                        ->get()->row();
        $n = (int)($row->c ?? 0) + 1;
        return str_pad((string)$n, 3, '0', STR_PAD_LEFT);
    }

    private function compose_account_number($type_id, $person_id)
    {
        $alias = $this->type_alias($type_id);              // p.ej. CAJ001
        $ident = $this->person_identifier($person_id);     // CI o person_id
        $seq   = $this->next_seq_for_person_type($person_id, $type_id); // 001
        return "{$alias}-{$ident}-{$seq}";
    }

        /**
     * Búsqueda con paginación de cuentas de ahorro.
     *
     * @param array $filters  ['q' => texto_buscado]
     * @param int   $limit
     * @param int   $offset
     * @param bool  $only_active  true = solo activas, false = solo inactivas
     * @return array
     */
    public function search_accounts(array $filters = [], $limit = 20, $offset = 0, $only_active = true)
    {
        $q = isset($filters['q']) ? trim($filters['q']) : '';

        $this->db
            ->select('sa.*, sat.name AS type_name, p.first_name, p.last_name')
            ->from($this->table . ' sa')
            ->join('savings_account_types sat', 'sat.savings_account_type_id = sa.savings_account_type_id')
            ->join('people p', 'p.person_id = sa.person_id');

        // Estado (activas / inactivas)
        if ($only_active) {
            $this->db->where('sa.status', 1);
        } else {
            $this->db->where('sa.status', 0);
        }

        // Filtro de búsqueda (nro cuenta o nombre de persona)
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('sa.account_number', $q);
            $this->db->or_like("CONCAT(COALESCE(p.first_name,''),' ',COALESCE(p.last_name,''))", $q, 'both', false);
            $this->db->group_end();
        }

        return $this->db
            ->order_by('sa.opening_date', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Cuenta cuántas cuentas hay para una búsqueda dada (para paginación).
     *
     * @param array $filters
     * @param bool  $only_active
     * @return int
     */
    public function count_accounts(array $filters = [], $only_active = true)
    {
        $q = isset($filters['q']) ? trim($filters['q']) : '';

        $this->db
            ->from($this->table . ' sa')
            ->join('savings_account_types sat', 'sat.savings_account_type_id = sa.savings_account_type_id')
            ->join('people p', 'p.person_id = sa.person_id');

        if ($only_active) {
            $this->db->where('sa.status', 1);
        } else {
            $this->db->where('sa.status', 0);
        }

        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('sa.account_number', $q);
            $this->db->or_like("CONCAT(COALESCE(p.first_name,''),' ',COALESCE(p.last_name,''))", $q, 'both', false);
            $this->db->group_end();
        }

        return (int) $this->db->count_all_results();
    }

}
