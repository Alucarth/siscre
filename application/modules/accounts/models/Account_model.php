<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Account_model extends CI_Model
{
    function get_list($limit = 10000, $offset = 0, $search = "", $order = array())
    {
        $sorter = array(
            "",
            "account_name",
            "description",
        );
        
        $this->db->from('accounts la');
        
        if ($search !== "")
        {
            $this->db->where("(
                la.account_name LIKE '%" . $search . "%' OR
                la.description LIKE '%" . $search . "%' OR
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
            track_action($user_id, "Accounts", "Viewed loan accounts list");
        }
        
        return $query;
    }
    
    public function get_details($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get("accounts");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $loan_product_info = $query->row();
        }
        else
        {
            $loan_product_info = new stdClass();
            $loan_product_info->id = -1;
            $loan_product_info->account_name = "";
            $loan_product_info->description = "";
        }
        
        if (is_plugin_active('activity_log'))
        {
            if ( $id > 0 )
            {
                $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($user_id, "Accounts", "Viewed loan account details #" . $id);
            }
        }
        
        return $loan_product_info;
    }
    
    public function get_transaction_info($id, $trans_type = 'deposit')
    {
        $this->db->select("a.account_name, CONCAT(p.first_name, ' ', p.last_name) customer_name, p.photo_url, at.*");
        $this->db->where("at.id", $id);
        $this->db->where("at.trans_type", $trans_type);
        $this->db->from("account_transactions at");
        $this->db->join("accounts a", "a.id = at.account_id", "LEFT");
        $this->db->join("people p", "p.person_id = at.added_by", "LEFT");
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            $info = $query->row();
        }
        else
        {
            $info = new stdClass();
            $info->id = -1;
            $info->added_by = "";
            $info->account_id = "";
            $info->amount = 0;
            $info->description = "";
            $info->account_name = "";
            $info->customer_name = "";
            //$info->customer_photo = "";
        }
        
        if (is_plugin_active('activity_log'))
        {
            if ( $id > 0 )
            {
                $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                track_action($user_id, "Account Deposit", "Viewed deposit details #" . $id);
            }
        }
        
        return $info;
    }
    
    public function save($id, $data)
    {
        if ( $id < 0 )
        {
            $this->db->insert("accounts", $data);
            
            $id = $this->db->insert_id();
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Accounts", "Added loan account details #" . $id);
                }
            }
        }
        else
        {
            $this->db->where("id", $id);
            $this->db->update("accounts", $data);
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Accounts", "Updated loan account details #" . $id);
                }
            }
        }
        
        return $id;
    }
    
    public function save_deposit($id, $data)
    {
        if ( $id < 0 )
        {
            $this->db->insert("account_transactions", $data);
            
            $id = $this->db->insert_id();
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Deposit Account", "Added deposit account details #" . $id);
                }
            }
        }
        else
        {
            $this->db->where("id", $id);
            $this->db->update("account_transactions", $data);
            
            if (is_plugin_active('activity_log'))
            {
                if ( $id > 0 )
                {
                    $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
                    track_action($user_id, "Deposit Account", "Updated deposit account details #" . $id);
                }
            }
        }
        
        return $id;
    }
    
    public function get_accounts()
    {
        $query = $this->db->get("accounts");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }

    public function get_account_balance($user_id, $account_id)
    {
        $sql = "SELECT SUM(amount) balance, trans_type FROM c19_account_transactions WHERE added_by = $user_id AND account_id = $account_id GROUP BY trans_type";
        $query = $this->db->query( $sql );
        
        $deposit = 0;
        $withdraw = 0;
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                if ( $row->trans_type == 'deposit' )
                {
                    $deposit = $row->balance;
                }
                
                if ( $row->trans_type == 'withdraw' )
                {
                    $withdraw = $row->balance;
                }
            }
        }
        
        return $deposit + $withdraw;
    }
    
    function get_accounts_total($filters = [])
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->Employee->getLowerLevels($low_levels);
        
        $where = 'WHERE 1';
        if ( isset($filters["sel_user"]) && $filters["sel_user"] > 0 )
        {
            $user_id = ($filters["sel_user"]) ? $filters["sel_user"] : $user_id;
            $where .= ' AND t.added_by=' . $user_id;
        }
        
        if ( isset($filters["account_id"]) && $filters["account_id"] > 0 )
        {
            $where .= ' AND t.account_id=' . $filters["account_id"];
        }
        
        if ( isset($filters["date_from"]) && $filters["date_from"] != '' )
        {
            $where .= ' AND t.trans_date >="' . $filters["date_from"] . ' 00:00:00"';
        }
        
        if ( isset($filters["date_to"]) && $filters["date_to"] != '' )
        {
            $where .= ' AND t.trans_date <="' . $filters["date_to"] . ' 23:59:59"';
        }
        
        if ( isset($filters["search"]) && $filters["search"] !== "")
        {
            $search = $filters["search"];
            $or_where = "(
                a.account_name LIKE '%" . $search . "%' OR
                CONCAT(p.first_name, ' ', p.last_name) LIKE '%" . $search . "%' OR
                t.amount LIKE '%" . $search . "%' OR
                t.description LIKE '%" . $search . "%' OR
                t.trans_type LIKE '%" . $search . "%' )";
            $this->db->where($or_where);
            $where .= ' AND ' . $or_where;
        }
        
        $total_amount = 0;
        $sql = "SELECT ifNULL(SUM(t.amount), 0) total_amount
                FROM c19_account_transactions t
                LEFT JOIN c19_people p ON p.person_id = t.added_by
                LEFT JOIN c19_accounts a ON a.id = t.account_id
                $where";
        $query = $this->db->query( $sql );
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $total_amount = $row->total_amount;
        }
        
        return $total_amount;
    }
    
    function get_account_transactions($limit = 10000, $offset = 0, $search = "", $order = array(), $sel_user = '', $filters = [], &$count_all = 0)
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $this->Employee->getLowerLevels($low_levels);
        
        $sorter = array(
            "",
            "account_name",
            "amount",
            "description",
            "trans_type",
            "trans_date",
            "added_by_name",
            "created_by_name",
        );
        
        $this->db->select("CONCAT(p.first_name, ' ', p.last_name) added_by_name, CONCAT(p2.first_name, ' ', p2.last_name) created_by_name,  t.*, a.account_name");
        $this->db->from('account_transactions t');
        $this->db->join("people p", "p.person_id = t.added_by", "LEFT");
        $this->db->join("people p2", "p2.person_id = t.modified_by", "LEFT");
        $this->db->join("accounts a", "a.id = t.account_id", "LEFT");
        
        $where = 'WHERE 1 ';
        if ( $sel_user > 0 )
        {
            $user_id = ($sel_user) ? $sel_user : $user_id;
            $this->db->where('t.added_by', $user_id);
            $where .= ' AND t.added_by=' . $user_id;
        }
        
        if ( isset($filters["account_id"]) && $filters["account_id"] > 0 )
        {
            $this->db->where("t.account_id", $filters["account_id"]);
            $where .= ' AND t.account_id=' . $filters["account_id"];
        }
        
        if ( isset($filters["date_from"]) && $filters["date_from"] != '' )
        {
            $this->db->where("t.trans_date >=", $filters["date_from"] . ' 00:00:00');
            $where .= ' AND t.trans_date >="' . $filters["date_from"] . ' 00:00:00"';
        }
        
        if ( isset($filters["date_to"]) && $filters["date_to"] != '' )
        {
            $this->db->where("t.trans_date <=", $filters["date_to"] . ' 23:59:59');
            $where .= ' AND t.trans_date <="' . $filters["date_to"] . ' 23:59:59"';
        }
        
        if ($search !== "")
        {
            $or_where = "(
                a.account_name LIKE '%" . $search . "%' OR
                CONCAT(p.first_name, ' ', p.last_name) LIKE '%" . $search . "%' OR
                t.amount LIKE '%" . $search . "%' OR
                t.description LIKE '%" . $search . "%' OR
                t.trans_type LIKE '%" . $search . "%' )";
            $this->db->where($or_where);
            $where .= ' AND ' . $or_where;
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
        
        $sql = "SELECT COUNT(*) cnt
                FROM c19_account_transactions t
                LEFT JOIN c19_people p ON p.person_id = t.added_by
                LEFT JOIN c19_accounts a ON a.id = t.account_id
                $where";
        $q = $this->db->query( $sql );
        if ( $q && $q->num_rows() > 0 )
        {
            $r = $q->row();
            $count_all = $r->cnt;
        }
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "Account Transaction", "Viewed Account Transaction list");
        }
        
        return $query;
    }
    
    public function get_users($role_id = '')
    {
        $this->db->where("role_id", $role_id);
        $this->db->from("people p");
        $this->db->join("employees e", "e.person_id = p.person_id", "LEFT");
        $this->db->order_by("p.first_name, p.last_name");
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    public function get_branch($id)
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

    function get_person($person_id)
    {

        $query = $this->db->get_where('people', array('person_id' => $person_id), 1);



        if ($query->num_rows() == 1)
        {

            return $query->row();
        }
        else
        {

            //create object with empty properties.

            $fields = $this->db->list_fields('people');

            $person_obj = new stdClass;



            foreach ($fields as $field)
            {

                $person_obj->$field = '';
            }



            return $person_obj;
        }
    }
    
    function get_customer_search_suggestions($search, $limit = 25)
    {
        $suggestions = array();

        $this->db->from("customers e ");
        $this->db->join("people p", "e.person_id = p.person_id", "LEFT");
        //$this->db->where("role_id", ROLE_CLIENT);
        
        $like_where = "(
            p.first_name LIKE '%$search%' OR
            p.last_name LIKE '%$search%'
            )";
        $this->db->where($like_where);
        
        $this->db->order_by("p.first_name, p.last_name");
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ($query->result() as $row)
            {
                $suggestions[] = $row->person_id . '|' . $row->first_name . ' ' . $row->last_name . '|' . $row->email;
            }
        }
        
        //only return $limit suggestions
        if (count($suggestions) > $limit)
        {
            $suggestions = array_slice($suggestions, 0, $limit);
        }
        return $suggestions;
    }
    
    function get_customers()
    {
        $sql = "SELECT p.person_id, p.first_name, p.last_name, p.photo_url 
                FROM c19_account_transactions t
                LEFT JOIN c19_people p ON p.person_id = t.added_by
                GROUP BY t.added_by";
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_account_reports($limit = 10000, $offset = 0, $search = "", $order = array(), $sel_user = '', $filters = [])
    {
        $accounts = $this->get_list();
        
        $sql = "
            SELECT 

            DATE_FORMAT(a.trans_date, '%Y-%m-%d') trans_date,
            CONCAT(p.first_name, ' ', p.last_name) customer_name,
            ";

        $tmp = [];
        foreach ( $accounts->result() as $row )
        {
            $tmp[] = "SUM(
                        CASE 
                            WHEN a.`trans_type`='deposit' AND a.account_id = " . $row->id . "
                            THEN a.amount
                            ELSE NULL 
                        END
                    ) AS 'deposit_{$row->id}',

                SUM(
                        CASE 
                            WHEN a.`trans_type`='withdraw' AND a.account_id = " . $row->id . "
                            THEN a.amount
                            ELSE NULL 
                        END
                    ) AS 'withdraw_{$row->id}'";
        }
        
        $sql .= implode(", ", $tmp);

        $sql .= " FROM c19_account_transactions a 
            LEFT JOIN c19_people p ON p.person_id = a.added_by
            WHERE a.trans_date BETWEEN '{$filters["date_from"]} 00:00:00' AND '{$filters["date_to"]} 23:00:00' 
            AND a.added_by = '{$sel_user}'

            GROUP BY DATE_FORMAT(a.trans_date, '%Y-%m-%d')

            ORDER BY trans_date
            ";
            
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_accounts_total_deposit( $filters = [] )
    {
        $user_info = $this->Employee->get_logged_in_employee_info();
        $this->Employee->getLowerLevels($low_levels);
        
        $where = '';
        
        if ( is_array($low_levels) && count($low_levels) > 0 )
        {
            $low_levels[] = $user_info->role_id;
            
            $this->db->select("person_id");
            $this->db->where_in("role_id", $low_levels);
            $query = $this->db->get("people");
            
            if ( $query && $query->num_rows() > 0 )
            {
                $staff_ids = [];
                foreach ( $query->result() as $row )
                {
                    $staff_ids[] = $row->person_id;
                }
                
                $where = ' AND a.modified_by IN (' . implode(",", $staff_ids) . ')';
            }
        }
        else
        {
            $where = ' AND a.modified_by = ' . $user_info->person_id;
        }
        
        $sql = "
            SELECT 
                SUM(
                    CASE 
                        WHEN a.`trans_type`='deposit'
                        THEN a.amount
                        ELSE NULL 
                    END
                ) total_deposit, 
                
                SUM(
                    CASE 
                        WHEN a.`trans_type`='withdraw'
                        THEN a.amount
                        ELSE NULL 
                    END
                ) total_withdraw,
                a.account_id 
            FROM c19_account_transactions a 
            WHERE a.trans_date BETWEEN '{$filters["date_from"]} 00:00:00' AND '{$filters["date_to"]} 23:00:00'
            AND a.modified_by = {$filters["user_id"]}
            $where
            GROUP BY a.account_id
            ";
            
        $query = $this->db->query( $sql );
        
        $total_deposits = [];
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $total_deposits[$row->account_id] = $row->total_deposit - $row->total_withdraw;
            }
        }
        
        return $total_deposits;
    }
}
