<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Branch_model extends CI_Model
{
    function get_list($limit = 10000, $offset = 0, $search = "", $order = array(), &$count_all = 0)
    {
        $sql = "SELECT COUNT(*) cnt FROM c19_branches b WHERE 
                (
                    b.branch_name LIKE '%" . $search . "%' OR
                    b.descriptions LIKE '%" . $search . "%' 
                )
            ";        
        $query = $this->db->query( $sql );
        if ( $query && $query->num_rows() > 0 )
        {
            $count_all = $query->row()->cnt;
        }
        
        $sorter = array(
            "",
            "branch_name",
            "descriptions"
        );
        
        $this->db->from('branches b');
        
        if ($search !== "")
        {
            $this->db->where("(
                b.branch_name LIKE '%" . $search . "%' OR
                b.descriptions LIKE '%" . $search . "%'
                )");
        }

        if ( isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("id", "desc");
        }

        $this->db->limit($limit);
        $this->db->offset($offset);
        $query = $this->db->get();
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "Branches", "Viewed branches list");
        }
        
        return $query;
    }
    
    public function get_details($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get("branches");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $branch_info = $query->row();
        }
        else
        {
            $branch_info = new stdClass();
            $fields = $this->db->list_fields('branches');

            foreach ($fields as $field)
            {
                $branch_info->$field = "";
            }
        }   
        
        if (is_plugin_active('activity_log'))
        {
            if ( $id > 0 )
            {
                $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($user_id, "Branches", "Viewed loan product details #" . $id);
            }
        }
        
        return $branch_info;
    }
    
    public function save($id, $data)
    {
        if ( $id < 0 )
        {
            $this->db->insert("branches", $data);
            
            $id = $this->db->insert_id();
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Branches", "Added branch details #" . $id);
                }
            }
        }
        else
        {
            $this->db->where("id", $id);
            $this->db->update("branches", $data);
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Branches", "Updated branch details #" . $id);
                }
            }
        }
        
        return $id;
        
    }
    
    function get_branches()
    {
        $this->db->order_by("branch_name");
        $query = $this->db->get("branches");
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }

}
