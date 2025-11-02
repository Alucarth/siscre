<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class General_ledger_model extends CI_Model {

    function get_accounting_transactions($filters = [])
    {
        $where_vouchers = ' WHERE 1=1';
        
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where_vouchers .= " AND DATE(v.voucher_date) >= '$date_from'";
        }
        
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where_vouchers .= " AND DATE(v.voucher_date) <= '$date_to'";
        }
        
        if (is_plugin_active("branches")) {
            $branch_id = $this->session->userdata("branch_id");
            $where_vouchers .= " AND v.branch_id = $branch_id";
        }
        
        $sql_vouchers = "
            SELECT 
                v.id as voucher_id,
                v.voucher_date,
                v.description as voucher_description,
                v.total_debit,
                v.total_credit
            FROM c19_accounting_vouchers v
            $where_vouchers
            ORDER BY v.id DESC
        ";
        
        $query_vouchers = $this->db->query($sql_vouchers);

        $vouchers = [];
        
        if ($query_vouchers && $query_vouchers->num_rows() > 0) {
            foreach ($query_vouchers->result() as $voucher_row) {
                $voucher_id = $voucher_row->voucher_id;
                $vouchers[$voucher_id] = new stdClass();
                $vouchers[$voucher_id]->voucher_info = new stdClass();
                $vouchers[$voucher_id]->voucher_info->voucher_number = $voucher_row->voucher_number;
                $vouchers[$voucher_id]->voucher_info->voucher_date = $voucher_row->voucher_date;
                $vouchers[$voucher_id]->voucher_info->voucher_description = $voucher_row->voucher_description;
                $vouchers[$voucher_id]->voucher_info->total_debit = $voucher_row->total_debit;
                $vouchers[$voucher_id]->voucher_info->total_credit = $voucher_row->total_credit;
                $vouchers[$voucher_id]->transactions = [];
                
                $sql_transactions = "
                    SELECT 
                        t.id as transaction_id,
                        t.amount,
                        t.description as transaction_description,
                        t.added_date,
                        t.movement_type,
                        a.account_name,
                        a.code_number as account_number,
                        a.account_type
                    FROM c19_accounting_transactions t
                    LEFT JOIN c19_accounting_accounts a ON a.id = t.account_id
                    WHERE t.voucher_id = $voucher_id
                    ORDER BY t.transaction_order ASC
                ";
                
                $query_transactions = $this->db->query($sql_transactions);
                
                if ($query_transactions && $query_transactions->num_rows() > 0) {
                    foreach ($query_transactions->result() as $trans_row) {
                        $transaction = new stdClass();
                        $transaction->date = date($this->config->item("date_format"), strtotime($trans_row->added_date));
                        $transaction->voucher_date = date($this->config->item("date_format"), strtotime($voucher_row->voucher_date));
                        $transaction->voucher_number = $voucher_row->voucher_number;
                        $transaction->explanation = $trans_row->transaction_description;
                        $transaction->account_name = $trans_row->account_name;
                        $transaction->account_number = $trans_row->account_number;
                        $transaction->amount = $trans_row->amount;
                        
                        if ($trans_row->movement_type == 'debit') {
                            $transaction->debit = $trans_row->amount;
                            $transaction->credit = 0;
                        } else {
                            $transaction->debit = 0;
                            $transaction->credit = $trans_row->amount;
                        }
                        
                        $transaction->balance = $trans_row->amount;
                        
                        $vouchers[$voucher_id]->transactions[] = $transaction;
                    }
                }
            }
        }
        
        uasort($vouchers, function($a, $b) {
            return $b->voucher_info->voucher_number - $a->voucher_info->voucher_number;
        });
        
        return $vouchers;
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