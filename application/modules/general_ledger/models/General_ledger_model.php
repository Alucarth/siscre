<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class General_ledger_model extends CI_Model {

    function get_accounting_transactions($filters = [])
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.added_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.added_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if (is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
                SELECT 	b.account_name,
                        b.code_number,
			b.account_type, 
			a.amount,
                        a.added_date
                FROM c19_accounting_transactions a
                LEFT JOIN c19_accounting_accounts b ON b.id = a.account_id
                WHERE 1 $where
                ORDER BY a.added_date
            ";
        
        $query = $this->db->query( $sql );
        
        $return = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $obj = new stdClass();
                $obj->date = date($this->config->item("date_format"), strtotime($row->added_date));
                $obj->explanation = "";
                $obj->account_name = $row->account_name;
                $obj->account_number = $row->code_number;
                
                if ( in_array($row->account_type, ['asset', 'expenses']) )
                {
                    $obj->debit = $row->amount;
                    $obj->credit = 0;
                    $obj->balance = $row->amount;
                }
                else
                {
                    $obj->debit = 0;
                    $obj->credit = $row->amount;
                    $obj->balance = $row->amount;
                }
                
                $return[] = $obj;
            }
        }
        
        return $return;
    }

    function get_account_transactions($filters = [])
    {
        if(!is_plugin_active("accounts"))
        {
            return [];
        }
        
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.trans_date >= '". date("Y-m-d", $filters["date_from"]) ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.trans_date <= '". date("Y-m-d", $filters["date_to"]) ."'";
        }
        
        if (is_plugin_active("branches"))
        {
            $where .= " AND a.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
                SELECT  b.account_name,
                        b.id code_number,
                        a.trans_type,
                        a.amount,
                        a.trans_date 
               FROM c19_account_transactions a
               LEFT JOIN c19_accounts b ON b.id = a.account_id
               WHERE 1 $where
               ORDER BY a.trans_date
            ";
        
        $query = $this->db->query( $sql );
        
        $return = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $obj = new stdClass();
                $obj->date = date($this->config->item("date_format"), strtotime($row->trans_date));
                $obj->explanation = "";
                $obj->account_name = $row->account_name;
                $obj->account_number = $row->code_number;
                
                if ( $row->trans_type == 'withdraw' )
                {
                    $obj->debit = $row->amount;
                    $obj->credit = 0;
                    $obj->balance = $row->amount;
                }
                else
                {
                    $obj->debit = 0;
                    $obj->credit = $row->amount;
                    $obj->balance = $row->amount;
                }
                
                $return[] = $obj;
            }
        }
        
        return $return;
    }

    function get_loan_transactions($filters = [])
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.loan_approved_date >= '". $filters["date_from"] ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.loan_approved_date <= '". $filters["date_to"] ."'";
        }
        
        if (is_plugin_active("branches"))
        {
            $where .= " AND b.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  a.loan_id, 
                    a.periodic_loan_table,
                    a.apply_amount,
                    (a.loan_amount - a.apply_amount) interest,
                    ( SELECT COUNT(*) cnt FROM c19_loan_payments b WHERE b.loan_id = a.loan_id AND b.delete_flag = 0) payment_cnt,
                    a.loan_approved_date,
                    a.loan_applied_date,
                    (SELECT SUM(paid_amount) FROM c19_loan_payments b WHERE b.delete_flag = 0 AND b.loan_id = a.loan_id) total_paid_amount
            FROM c19_loans a 
            LEFT JOIN c19_customers b ON b.person_id = a.customer_id
            WHERE a.loan_status = 'approved' AND a.delete_flag=0 AND b.deleted=0 $where
            ";
        
        $query = $this->db->query( $sql );
        
        $return = [];
        $total_repayment_amount = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $interest = $row->interest;
                $schedule = json_decode($row->periodic_loan_table, TRUE);
                
                $paid_amount = 0;
                for ( $i=0; $i<$row->payment_cnt; $i++ )
                {
                    $paid_amount += $schedule[$i]["payment_amount"];
                }

                $total_repayment_amount += $paid_amount;
                
                $obj = new stdClass();
                $obj->date = date($this->config->item("date_format"), $row->loan_approved_date);
                $obj->explanation = "Loan Issued";
                $obj->debit = $row->apply_amount;
                $obj->credit = $row->total_paid_amount;
                
                $return[] = $obj;
            }
        }
        
        return $return;
    }
    
    function get_loan_interest_transactions($filters = [])
    {
        $where = '';
        if ( isset($filters["date_from"]) && trim($filters["date_from"]) != '' )
        {
            $where .= " AND a.loan_approved_date >= '". $filters["date_from"] ."'";
        }
        
        if ( isset($filters["date_to"]) && trim($filters["date_to"]) != '' )
        {
            $where .= " AND a.loan_approved_date <= '". $filters["date_to"] ."'";
        }
        
        if (is_plugin_active("branches"))
        {
            $where .= " AND b.branch_id = " . $this->session->userdata("branch_id");
        }
        
        $sql = "
            SELECT  a.loan_id, 
                    a.periodic_loan_table,
                    a.apply_amount,
                    (a.loan_amount - a.apply_amount) interest,
                    ( SELECT COUNT(*) cnt FROM c19_loan_payments b WHERE b.loan_id = a.loan_id ) payment_cnt,
                    a.loan_approved_date
            FROM c19_loans a 
            LEFT JOIN c19_customers b ON b.person_id = a.customer_id
            WHERE a.loan_status = 'approved' $where
            ";
        
        $query = $this->db->query( $sql );
        
        $return = [];
        $outstanding_interest = 0;
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $interest = $row->interest;
                $schedule = json_decode($row->periodic_loan_table, TRUE);
                
                $paid_interest = 0;
                for ( $i=0; $i<$row->payment_cnt; $i++ )
                {
                    $paid_interest += $schedule[$i]["interest"];
                }

                $outstanding_interest += $paid_interest;
                
                $obj = new stdClass();
                $obj->date = date($this->config->item("date_format"), $row->loan_approved_date);
                $obj->explanation = "Interest Received";
                $obj->debit = 0;
                $obj->credit = $outstanding_interest;
                
                $return[] = $obj;
            }
        }
        
        return $return;
    }

}
