<?php

class General_book_model extends CI_Model {

    function get_general_book_data($filters = [])
    {
        $where = ' WHERE 1=1';
        
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(t.added_date) >= '$date_from'";
        }
        
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(t.added_date) <= '$date_to'";
        }
        
        if (is_plugin_active("branches")) {
            $branch_id = $this->session->userdata("branch_id");
            $where .= " AND t.branch_id = $branch_id";
        }
        
        $sql = "
            SELECT 
                t.id,
                t.added_date,
                t.description,
                t.amount,
                t.movement_type,
                v.voucher_type,
                v.voucher_number,
                v.voucher_date,
                CASE 
                    WHEN t.movement_type = 'debit' THEN t.amount
                    ELSE 0 
                END as debit,
                CASE 
                    WHEN t.movement_type = 'credit' THEN t.amount
                    ELSE 0 
                END as credit
            FROM c19_accounting_transactions t
            LEFT JOIN c19_accounting_vouchers v ON v.id = t.voucher_id
            $where
            ORDER BY t.added_date ASC, t.transaction_order ASC, t.id ASC
        ";
        
        $query = $this->db->query($sql);
        
        $transactions = [];
        $total_debit = 0;
        $total_credit = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $transaction = new stdClass();
                $transaction->id = $row->id;
                $transaction->added_date = $row->added_date;
                $transaction->description = $row->description;
                $transaction->movement_type = $row->movement_type;
                $transaction->voucher_type = $row->voucher_type;
                $transaction->voucher_number = $row->voucher_number;
                $transaction->amount = $row->amount;
                $transaction->debit = $row->debit;
                $transaction->credit = $row->credit;
                
                $total_debit += $row->debit;
                $total_credit += $row->credit;
                
                $transactions[] = $transaction;
            }
        }
        
        return [
            'transactions' => $transactions,
            'total_debit' => $total_debit,
            'total_credit' => $total_credit
        ];
    }

    function get_totals($filters = [])
    {
        $where = ' WHERE 1=1';
        
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $where .= " AND DATE(added_date) >= '$date_from'";
        }
        
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $where .= " AND DATE(added_date) <= '$date_to'";
        }
        
        $sql = "
            SELECT 
                SUM(CASE WHEN movement_type = 'debit' THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN movement_type = 'credit' THEN amount ELSE 0 END) as total_credit,
                COUNT(*) as total_transactions
            FROM c19_accounting_transactions
            $where
        ";
        
        $query = $this->db->query($sql);
        
        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }
        
        return (object)[
            'total_debit' => 0, 
            'total_credit' => 0, 
            'total_transactions' => 0
        ];
    }
}
?>