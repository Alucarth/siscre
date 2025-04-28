<?php

class Guarantee extends CI_Model {
    /*
      Determines if a given loan_payment_id is a payment
     */

    function exists($loan_id)
    {
        $this->db->from('guarantee');
        $this->db->where('loan_id', $loan_id);
        $query = $this->db->get();

        return ($query->num_rows() == 1);
    }

    /*
      Gets information about a particular loan
     */

    function get_info($loan_id)
    {
        $this->db->from('guarantee');
        $this->db->where('loan_id', $loan_id);

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
            $fields = $this->db->list_fields('guarantee');

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

    function save(&$guarantee_data, $loan_id = false)
    {
        $guarantee_data["loan_id"] = $loan_id;
        if (!$loan_id or ! $this->exists($loan_id))
        {
            if ($this->db->insert('guarantee', $guarantee_data))
            {
                $guarantee_data['guarantee_id'] = $this->db->insert_id();
                return true;
            }
            return false;
        }

        $this->db->where('loan_id', $loan_id);
        return $this->db->update('guarantee', $guarantee_data);
    }
    
    function get_list($filters, &$count_all = 0)
    {
        $loan_id = $filters["loan_id"];
        
        $sql = "SELECT COUNT(*) cnt FROM c19_guarantee WHERE loan_id = '$loan_id'";
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
        
        $query = $this->db->get("guarantee");
        
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
        $this->db->from('guarantee');
        $this->db->where('guarantee_id', $id);

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
            $fields = $this->db->list_fields('guarantee');

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
            $this->db->where('guarantee_id', $id);
            $this->db->update('guarantee', $data);
        }
        else
        {
            $this->db->insert('guarantee', $data);
            $id = $this->db->insert_id();            
        }
        
        return $id;
    }
}

?>