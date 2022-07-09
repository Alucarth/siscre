<?php

class Leads_model extends CI_Model {
    
    function get_articles( $filters = [], $limit = 10000, $offset = 0, $search = "", $order = array() )
    {
        $this->db->select("la.*, CONCAT(p.first_name, ' ', p.last_name) added_by_name");
        $this->db->from("leads_articles la");
        $this->db->join("people p", "p.person_id = la.added_by", "LEFT");
        
        if ( $search != '' )
        {
            $where = "(
                    title LIKE '%" . trim($search) . "%' OR
                    content LIKE '%" . trim($search) . "%'
            )";
            $this->db->where($where);
        }
        
        $sorter = [
            "",
            "title",
            "content",
            "added_date",
            "added_by_name",
            "published"
        ];
        
        if (count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("added_date", "desc");
        }
        
        if ( isset($filters["published"]) && $filters["published"] )
        {
            $this->db->where("published", 1);
        }
        
        $this->db->limit($limit);
        $this->db->offset($offset);

        $query = $this->db->get();
        
        return $query;
    }
    
    function get_all( $filters = [], $limit = 10000, $offset = 0, $search = "", $order = array() )
    {
        $this->db->from("leads");
        
        if ( $search != '' )
        {
            $where = "(
                    full_name LIKE '%" . trim($search) . "%' OR
                    email LIKE '%" . trim($search) . "%' OR
                    address1 LIKE '%" . trim($search) . "%' OR
                    city LIKE '%" . trim($search) . "%' OR
                    street_no LIKE '%" . trim($search) . "%'
            )";
            $this->db->where($where);
        }
        
        $sorter = [
            "",
            "full_name",
            "email",
            "occupation",            
            "added_date",
        ];
        
        if (count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("added_date", "desc");
        }
        
        $this->db->where("signup_complete", 1);
        $this->db->limit($limit);
        $this->db->offset($offset);

        $query = $this->db->get();
        
        return $query;
    }
    
    function get_fields($controller_name)
    {
        return $this->db->list_fields($controller_name);
    }
    
    function save($id, $data)
    {
        $data["added_date"] = date("Y-m-d H:i:s");
        if ( $id > 0 )
        {
            // Update
            $data["modified_date"] = date("Y-m-d H:i:s");
            $this->db->where("id", $id);            
            $this->db->update("leads", $data);
            return $id;
        }
        
        // Insert
        $this->db->insert("leads", $data);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    function get_details($id)
    {
        $this->db->where("l.id", $id);
        $this->db->from("leads l");        
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row();
        }
        
        return false;
    }
    
    function save_documents( $leads_id, $update_data = [] )
    {
        $this->db->where("id", $leads_id);
        $this->db->update("leads", $update_data);
    }
    
    function delete($id)
    {
        $this->db->where("foreign_id", $id);
        $this->db->where("document_type", "leads_galleries");
        $query = $this->db->get("documents");
        
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                @unlink( FCPATH . 'uploads/' . $row->path );
            }
        }
        
        $this->db->where("foreign_id", $id);
        $this->db->where("document_type", "leads_galleries");
        $this->db->delete("documents");
        
        $this->db->where("id", $id);
        $query = $this->db->get("leads");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            @unlink( FCPATH . 'uploads/' . $row->icon );
        }
        
        $this->db->where("id", $id);
        $this->db->delete("leads");
    }
    
    function email_exists($email, &$leads_id = '')
    {
        $this->db->where("email", $email);        
        $query = $this->db->get("leads");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            
            if ( $row->signup_complete )
            {
                return true;                
            }
            else
            {
                $leads_id = $row->id;
                
                return false;
                
            }
            
        }
        
        return false;
    }
    
    function get_info( $leads_id )
    {
        $select = "l.*";
        
        if (is_plugin_active("branches"))
        {
            $select .= ", c.branch_id";
        }
        
        $this->db->select($select);
        $this->db->where("l.id", $leads_id);
        $this->db->from("leads l");
        $this->db->join("customers c", "c.person_id = l.customer_id", "LEFT");
        $query = $this->db->get();
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row();
        }
        
        return false;
    }
    
    function login($email, $pass_code)
    {
        $query = $this->db->get_where('leads', array('email' => $email, 'password' => md5($pass_code), 'signup_complete' => 1), 1);

        if ($query->num_rows() == 1)
        {
            $row = $query->row();

            $this->session->set_userdata('session_leads_id', $row->id);
            $this->session->set_userdata('user_info', $row);

            return true;
        }

        return false;
    }
    
    function is_logged_in()
    {
        return $this->session->userdata('session_leads_id') != false;
    }
    
    function logout($redirect = true)
    {

        $this->session->sess_destroy();

        if ( $redirect )
        {
            redirect('leads');
        }
    }
    
    function save_article($article_id = '', $data)
    {
        if ( $article_id > 0 )
        {
            // Do some update
            $this->db->where("article_id", $article_id);
            $this->db->update("leads_articles", $data);
            
            return $article_id;
        }
        
        $this->db->insert("leads_articles", $data);
        $article_id = $this->db->insert_id();
        
        return $article_id;
    }
    
    function get_article($article_id)
    {
        $this->db->where("article_id", $article_id);
        $query = $this->db->get("leads_articles");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row();
        }
        
        $article_info = new stdClass();
        $article_info->title = '';
        $article_info->content = '';
        $article_info->published = '';
        
        return $article_info;
    }
    
    function get_loan_products()
    {
        $query = $this->db->get("loan_products");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_leads_application( $leads_id )
    {
        $this->db->where("leads_id", $leads_id);
        $this->db->where("status", "pending");
        $query = $this->db->get("leads_application");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row();
        }
        
        return false;
    }
    
    function get_loan_product_info( $loan_product_id )
    {
        $this->db->where("id", $loan_product_id);
        $query = $this->db->get("loan_products");
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row();
        }
        
        return false;
    }
    
    function get_loan_history($limit = 10000, $offset = 0, $search = "", $order = array(), $status = "", $filters = [])
    {
        $user_info = $this->Employee->get_logged_in_employee_info();
        $id = $user_info->person_id;
        
        $sorter = array(
            "",
            "loan_id",
            "loan_amount",
            "loan_balance",            
            "loan_applied_date",
            "loan_payment_date",
            "loan_status",
        );
        
        if ( isset($filters["sorter"]) && is_array($filters["sorter"]) )
        {
            $sorter = $filters["sorter"];
        }
        
        $this->db->where("customer_id", $user_info->person_id);

        $select = "l.*, CONCAT(customer.first_name, ' ', customer.last_name) as customer_name, 
                   CONCAT(agent.first_name, ' ',agent.last_name) as agent_name, 
                   CONCAT(approver.first_name, ' ', approver.last_name) as approver_name,
                   IF (
                        l.loan_type_id = 0,
                        'Flexible',
                        lt.name
                   ) AS loan_type";

        $this->db->select($select, FALSE);
        $this->db->from('loans l');
        $this->db->join('people as customer', 'customer.person_id = l.customer_id', 'LEFT');
        $this->db->join('people as agent', 'agent.person_id = l.loan_agent_id', 'LEFT');
        $this->db->join('people as approver', 'approver.person_id = l.loan_approved_by_id', 'LEFT');
        $this->db->join('loan_types lt', 'lt.loan_type_id = l.loan_type_id', 'LEFT');

        if ( isset($filters["from_date"]) && trim($filters["from_date"]) != '' )
        {
            $this->db->where("loan_applied_date >=", $filters["from_date"]);
        }
        
        if ( isset($filters["to_date"]) && trim($filters["to_date"]) != '' )
        {
            $this->db->where("loan_applied_date <=", $filters["to_date"]);
        }

        if ($search !== "")
        {
            $this->db->where("(
                account LIKE '%" . $search . "%' OR
                l.description LIKE '%" . $search . "%' OR
                customer.first_name LIKE '%" . $search . "%' OR
                customer.last_name LIKE '%" . $search . "%' OR
                CONCAT(customer.first_name,' ', customer.last_name) LIKE '%" . $search . "%' OR
                lt.name LIKE '%" . $search . "%' OR        
                agent.first_name LIKE '%" . $search . "%' OR
                agent.last_name LIKE '%" . $search . "%' OR 
                CONCAT(agent.first_name, ' ', agent.last_name) LIKE '%" . $search . "%'
                )");
        }

        if ( isset($order['index']) && count($order) > 0 && $order['index'] < count($sorter))
        {
            $this->db->order_by($sorter[$order['index']], $order['direction']);
        }
        else
        {
            $this->db->order_by("loan_id", "desc");
        }

        $this->db->where('delete_flag', 0);

        if ($status !== "")
        {
            if ($status === "paid")
            {
                $this->db->where("loan_balance", 0);
            }
            else if ($status === "unpaid")
            {
                $this->db->where("loan_balance >", 0);
            }
            else if ($status === "overdue")
            {
                $this->db->where("loan_payment_date < UNIX_TIMESTAMP()");
                $this->db->where("loan_status <>", 'pending');
                $this->db->where("loan_balance > ", 0);
            }
        }

        $this->db->limit($limit);
        $this->db->offset($offset);
        $query = $this->db->get();
        
        if (is_plugin_active('activity_log'))
        {
            $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
            track_action($user_id, "loans", "Viewed loan transactions");
        }
        
        return $query;
    }
    
    function get_all_employers()
    {
        $sql = "
            SELECT b.first_name, b.last_name, c.company_name, a.person_id FROM c19_customers a 
            LEFT JOIN c19_people b ON b.person_id = a.person_id
            LEFT JOIN c19_leads c ON c.customer_id = a.person_id
            WHERE (a.parent_id = 0 OR a.parent_id IS NULL) AND a.deleted = 0
            ";
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function get_my_employees($employer_id, $filters = [], &$count_all = 0)
    {
        $offset = $filters["offset"];
        $limit = $filters["limit"];
        
        $sql = "
            SELECT COUNT(*) cnt 
            FROM c19_customers a 
            LEFT JOIN c19_people b ON b.person_id = a.person_id
            LEFT JOIN c19_leads c ON c.customer_id = a.person_id
            WHERE a.parent_id = '$employer_id' AND a.deleted = 0";
        $query = $this->db->query( $sql );
        if ( $query && $query->num_rows() > 0 )
        {
            $count_all = $query->row()->cnt;
        }
        
        $sql = "
            SELECT b.first_name, b.last_name, c.company_name, a.person_id, b.email 
            FROM c19_customers a 
            LEFT JOIN c19_people b ON b.person_id = a.person_id
            LEFT JOIN c19_leads c ON c.customer_id = a.person_id
            WHERE a.parent_id = '$employer_id' AND a.deleted = 0
            LIMIT $offset, $limit
            ";
        
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
    
    function save_customer($lead_data)
    {
        $customer_id = $lead_data["customer_id"];
        
        $this->db->where("customer_id", $customer_id);
        $query = $this->db->get("leads");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $this->db->where("customer_id", $customer_id);
            $this->db->update("leads", $lead_data);
        }
        else
        {
            $this->db->insert("leads", $lead_data);
        }
    }
    
    function save_employee($lead_data)
    {
        $customer_id = $lead_data["customer_id"];
        
        $this->db->where("customer_id", $customer_id);
        $query = $this->db->get("leads");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $this->db->where("customer_id", $customer_id);
            $this->db->update("leads", $lead_data);
        }
        else
        {
            $this->db->insert("leads", $lead_data);
        }
    }
    
    function get_loan_transactions($limit, $offset, $filters = [], &$count_all = 0)
    {
        $where = '';
        
        if ( isset($filters["customer_id"]) && trim($filters["customer_id"]) != '' )
        {
            $where .= " AND a.customer_id = '" . $filters["customer_id"] . "'";
        }
        
        if ( isset($filters["from_date"]) && trim($filters["from_date"]) != '' )
        {
            $where .= " AND a.added_date >= '" . date("Y-m-d 00:00:00", $filters["from_date"]) . "'";
        }
        
        if ( isset($filters["to_date"]) && trim($filters["to_date"]) != '' )
        {
            $where .= " AND a.added_date <= '" . date("Y-m-d 23:59:59", $filters["to_date"]) . "'";
        }
        
        if ( isset($filters["trans_type"]) && trim($filters["trans_type"]) != '' )
        {
            $where .= " AND a.trans_type = '" . trim($filters["trans_type"]) . "'";
        }
        
        if ( isset($filters["keywords"]) && trim($filters["keywords"]) != '' )
        {
            $where .= " AND (
                    b.first_name LIKE '%".trim($filters["keywords"])."%' OR
                    a.description LIKE '%".trim($filters["keywords"])."%' OR
                    a.trans_type LIKE '%".trim($filters["keywords"])."%' OR
                    a.amount LIKE '%".trim($filters["keywords"])."%'
                )";
        }
        
        $sql = "SELECT COUNT(*) cnt 
                FROM c19_transaction_logs a
                LEFT JOIN c19_people b ON b.person_id = a.customer_id
                WHERE 1 $where                
                ";
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            $count_all = $query->row()->cnt;
        }
        
        $sql = "SELECT a.*, CONCAT(b.first_name, ' ', b.last_name) customer_name 
                FROM c19_transaction_logs a
                LEFT JOIN c19_people b ON b.person_id = a.customer_id
                WHERE 1 $where
                ORDER BY a.added_date DESC
                LIMIT $offset, $limit";
        
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }
}
