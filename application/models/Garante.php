<?php

class Garante extends CI_Model {
    /*
      Determines if a given loan_payment_id is a payment
     */

    function exists($loan_id)
    {
        $this->db->from('garantes');
        $this->db->where('loan_id', $loan_id);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }

    /*
      Gets information about a particular loan
     */

    function get_info($loan_id)
    {
        $this->db->from('garantes');
        $this->db->where('loan_id', $loan_id);
        
        $query = $this->db->get();
        
        //en caso de no existir datos en la base de datos devolver null
        if ($query->num_rows() == 0)
        {
            return null;
        }
        
        //var_dump($query);die;

        if ($query->num_rows() == 1)
        {
            return $query->row();
        }
        else
        {
            //Get empty base parent object, as $loan_id is NOT a loan
            $obj = new stdClass();

            //Get all the fields from items table
            $fields = $this->db->list_fields('garante');

            foreach ($fields as $field)
            {
                $obj->$field = '';
            }

            return $obj;
        }
    }

    /*
      Inserts or updates a payment
     */

    function save(&$garante_data, $garante_id = false)
    {
        $garante_data["garante_id"] = $garante_id;
        if (!$garante_id or ! $this->exists($garante_id))
        {
            if ($this->db->insert('garantes', $garante_data))
            {
                $garante_data['garante_id'] = $this->db->insert_id();
                return true;
            }
            return false;
        }

        $this->db->where('garante_id', $garante_id);
        return $this->db->update('garantes', $garante_data);
    }
    
    function get_list($filters, &$count_all = 0)
    {
        $loan_id = $filters["loan_id"];
        
        $sql = "SELECT COUNT(*) cnt FROM c19_garantes WHERE loan_id = '$loan_id'";
        $query = $this->db->query($sql);
        if ( $query && $query->num_rows() > 0 )
        {
            $count_all = $query->row()->cnt;
        }
        
        $this->db->where("loan_id", $loan_id);
        
        if ( isset($filters["offset"]) )
        {
            $this->db->offset($filters["offset"]);
        }
        
        if ( isset($filters["limit"]) )
        {
            $this->db->limit($filters["limit"]);
        }
        
        $query = $this->db->get("garantes");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }

    /*
      Gets information about a particular loan
     */

    function get_details($id)
    {
        $this->db->from('garantes');
        $this->db->where('garante_id', $id);

        $query = $this->db->get();

        if ($query->num_rows() == 1)
        {
            return $query->row();
        }
        else
        {
            //Get empty base parent object, as $loan_id is NOT a loan
            $obj = new stdClass();

            //Get all the fields from items table
            $fields = $this->db->list_fields('garantes');

            foreach ($fields as $field)
            {
                $obj->$field = '';
            }

            return $obj;
        }
    }
    
    function save_details($id = '', $data = [])
    {
        if ($id > 0)
        {
            $this->db->where('garante_id', $id);
            $this->db->update('garante', $data);
        }
        else
        {
            $this->db->insert('garante', $data);
            $id = $this->db->insert_id();            
        }
        
        return $id;
    }

    /**
    * Verifica si un CI ya figura como garante de un crédito con estado “active”
    *
    * @param string $ci
    * @return bool  true si ya existe un garante activo con ese CI
    */
    public function is_active_guarantee($ci)
    {
        /*
        $this->db->select('g.garante_id');
        $this->db->from('c19_garantes g');
        $this->db->join('c19_loans l', 'g.loan_id = l.loan_id');
        $this->db->where('g.ci', $ci);
        $this->db->where('l.loan_status', 'approved');  
        $query = $this->db->get();

        return ($query->num_rows() > 0);
        */
        /**
         * Si ya hay al menos 1 (antes) o ahora 2 (ajustado) garantías activas.
         */
        return $this->count_active_guarantees($ci) > 0;
    }

    /**
     * Cuenta cuántos créditos activos tiene un CI como garante.
     *
     * @param string $ci
     * @return int
     */
    public function count_active_guarantees($ci)
    {
        $this->db
        ->from('c19_garantes g')
        ->join('c19_loans l', 'g.loan_id = l.loan_id')
        ->where('g.ci', $ci)
        ->where('l.loan_status', 'active'); // ajusta al valor real
        return $this->db->count_all_results();
    }

}

?>