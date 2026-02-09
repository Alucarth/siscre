<?php

class General_book_model extends CI_Model {

    function get_accounts()
    {
        $this->db->select('id, code_number, account_name');
        $this->db->from('c19_accounting_accounts');
        $this->db->order_by('code_number', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }
    
    function get_branches()
    {
        $this->db->select('id, branch_name');
        $this->db->from('c19_branches');
        $this->db->order_by('branch_name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }

    function get_general_book_data($filters = [])
    {
        $sql = "
            SELECT 
                t.id as transaction_id,
                DATE(t.added_date) as fecha,
                t.added_date,
                t.description,
                t.amount,
                t.movement_type,
                a.code_number,
                a.account_name,
                b.branch_name,
                t.voucher_id,
                CASE 
                    WHEN t.movement_type = 'debit' THEN t.amount
                    ELSE 0 
                END as debit,
                CASE 
                    WHEN t.movement_type = 'credit' THEN t.amount
                    ELSE 0 
                END as credit
            FROM c19_accounting_transactions t
            LEFT JOIN c19_accounting_accounts a ON a.id = t.account_id
            LEFT JOIN c19_branches b ON b.id = t.branch_id
            WHERE 1=1
        ";
        
        // Filtros
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $sql .= " AND DATE(t.added_date) >= '$date_from'";
        }
        
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $sql .= " AND DATE(t.added_date) <= '$date_to'";
        }
        
        // Filtro por cuenta contable
        if (isset($filters["account_id"]) && !empty($filters["account_id"])) {
            $account_id = $filters["account_id"];
            $sql .= " AND t.account_id = $account_id";
        }

        // Filtro por sucursal
        if (isset($filters["branch_id"]) && !empty($filters["branch_id"])) {
            $branch_id = $filters["branch_id"];
            $sql .= " AND t.branch_id = $branch_id";
        }
        
        $sql .= " ORDER BY t.added_date ASC, t.id ASC";
        
        $query = $this->db->query($sql);
        
        $transactions = [];
        $total_debit = 0;
        $total_credit = 0;
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $transaction = new stdClass();
                $transaction->id = $row->transaction_id;
                $transaction->transaction_id = $row->transaction_id;
                $transaction->voucher_id = $row->voucher_id;
                $transaction->fecha = $row->fecha;
                $transaction->added_date = $row->added_date;
                $transaction->description = $row->description;
                $transaction->movement_type = $row->movement_type;
                $transaction->account_id = $row->account_id;
                $transaction->code_number = $row->code_number;
                $transaction->account_name = $row->account_name;
                $transaction->branch_name = $row->branch_name;
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
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN movement_type = 'debit' THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN movement_type = 'credit' THEN amount ELSE 0 END) as total_credit
            FROM c19_accounting_transactions
            WHERE 1=1
        ";
        
        if (isset($filters["date_from"]) && trim($filters["date_from"]) != '') {
            $date_from = date("Y-m-d", $filters["date_from"]);
            $sql .= " AND DATE(added_date) >= '$date_from'";
        }
        
        if (isset($filters["date_to"]) && trim($filters["date_to"]) != '') {
            $date_to = date("Y-m-d", $filters["date_to"]);
            $sql .= " AND DATE(added_date) <= '$date_to'";
        }
        
        if (isset($filters["account_id"]) && !empty($filters["account_id"])) {
            $account_id = $filters["account_id"];
            $sql .= " AND account_id = $account_id";
        }
        
        if (isset($filters["branch_id"]) && !empty($filters["branch_id"])) {
            $branch_id = $filters["branch_id"];
            $sql .= " AND branch_id = $branch_id";
        }

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