<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Loan_product_model extends CI_Model
{
    function get_list($limit = 10000, $offset = 0, $search = "", $order = array(), &$count_all = 0)
    {
        $sql = "SELECT COUNT(*) cnt FROM c19_loan_products lp WHERE 
                (
                    lp.product_name LIKE '%" . $search . "%' OR
                    lp.description LIKE '%" . $search . "%' OR
                    lp.interest_rate LIKE '%" . $search . "%' OR
                    lp.interest_type LIKE '%" . $search . "%' OR
                    lp.term LIKE '%" . $search . "%' OR
                    lp.term_period LIKE '%" . $search . "%'
                )
            ";        
        $query = $this->db->query( $sql );
        if ( $query && $query->num_rows() > 0 )
        {
            $count_all = $query->row()->cnt;
        }
        
        $sorter = array(
            "",
            "product_name",
            "description",
            "interest_rate",
            "interest_type",
            "term",
            "term_period"
        );
        
        $this->db->from('loan_products lp');
        
        if ($search !== "")
        {
            $this->db->where("(
                lp.product_name LIKE '%" . $search . "%' OR
                lp.description LIKE '%" . $search . "%' OR
                lp.interest_rate LIKE '%" . $search . "%' OR
                lp.interest_type LIKE '%" . $search . "%' OR
                lp.term LIKE '%" . $search . "%' OR
                lp.term_period LIKE '%" . $search . "%'
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
            track_action($user_id, "Loan products", "Viewed loan products list");
        }
        
        return $query;
    }
    
    public function get_details($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get("loan_products");
        
        $interest_types = $this->Loan->get_loan_interest_types();
        
        if ( $query && $query->num_rows() > 0 )
        {
            $loan_product_info = $query->row();
            $loan_product_info->interest_type_name = isset($interest_types[$loan_product_info->interest_type]) ? $interest_types[$loan_product_info->interest_type] : 'Flat rate';
        }
        else
        {
            $loan_product_info = new stdClass();
            $loan_product_info->id = -1;
            $loan_product_info->product_name = "";
            $loan_product_info->description = "";
            $loan_product_info->interest_rate = "";
            $loan_product_info->interest_type = "flat";
            $loan_product_info->interest_type_name = 'Flat rate';
            $loan_product_info->term = "";
            $loan_product_info->penalty_value = 0;
            $loan_product_info->penalty_type = "percentage";
            $loan_product_info->term_period = "";
            $loan_product_info->grace_periods = "";
        }
        
        if (is_plugin_active('activity_log'))
        {
            if ( $id > 0 )
            {
                $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($user_id, "Loan products", "Viewed loan product details #" . $id);
            }
        }
        
        return $loan_product_info;
    }
    
    public function save($id, $data)
    {
        if ( $id < 0 )
        {
            $this->db->insert("loan_products", $data);
            
            $id = $this->db->insert_id();
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Loan products", "Added loan product details #" . $id);
                }
            }
        }
        else
        {
            $this->db->where("id", $id);
            $this->db->update("loan_products", $data);
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Loan products", "Updated loan product details #" . $id);
                }
            }
        }
        
        return $id;
        
    }

}
