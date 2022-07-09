<?php

class Document_model extends CI_Model {

    function get_list($filters = [], &$count_all = 0)
    {
        $this->_apply_filters($filters);
        $query = $this->db->get("documents");
        $count_all = $query->num_rows();
        
        $this->_apply_filters($filters);
        $this->db->from("documents");
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    private function _apply_filters($filters)
    {
        if ( isset($filters["customer_id"]) && $filters["customer_id"] > 0 )
        {
            $this->db->where("foreign_id", $filters["customer_id"]);
        }
        
        if ( isset($filters["foreign_id"]) && $filters["foreign_id"] > 0 )
        {
            $this->db->where("foreign_id", $filters["foreign_id"]);
        }
        
        if ( isset($filters["document_type"]) && $filters["document_type"] != '' )
        {
            $this->db->where("document_type", $filters["document_type"]);            
        }
        
        if ( isset($filters["document_ids"]) && is_array($filters["document_ids"]))
        {
            $this->db->where_in("document_id", $filters["document_ids"]);            
        }
        
        if ( isset($filters["order_by"]) && $filters["order_by"] != '' )
        {
            $this->db->order_by($filters["order_by"]);            
        }
    }
    
    function save($id = '', $data)
    {
        if ( $id != '' )
        {
            $this->db->where("document_id", $id);
            $this->db->update("documents", $data);
            
            return $id;
        }
        
        $this->db->insert("documents", $data);
        $id = $this->db->insert_id();
        
        return $id;
    }
}

?>
