<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Accounting extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('accounting');

        $this->load->model("accounting_model");
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch( $type )
        {
            case 1:
                $this->_dt_accounts();
                break;
            case 2: // Delete account
                $this->_delete_account();
                break;
            case 3:
                $this->_save_account();
                break;
            case 4:
                $this->_load_account();
                break;
            case 5:
                $this->asset();
                break;
            case 6:
                $this->liability();
                break;
            case 7:
                $this->equity();
                break;
            case 8:
                $this->income();
                break;
            case 9:
                $this->expenses();
                break;
            
            
            case 10:
                $this->_dt_transactions();
                break;
            case 11: // Delete transaction
                $this->_delete_transaction();
                break;
            case 12:
                $this->_save_transaction();
                break;
            case 13:
                $this->_load_transaction();
                break;
            case 14:
                $this->asset_transaction();
                break;
            case 15:
                $this->liability_transaction();
                break;
            case 16:
                $this->equity_transaction();
                break;
            case 17:
                $this->income_transaction();
                break;
            case 18:
                $this->expenses_transaction();
                break;
            
        }
    }
    
    private function _load_account()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $query = $this->db->get("accounting_accounts");
        
        $row = new stdClass();
        $row->id = '';
        $row->code_number = '';
        $row->account_name = '';
        $row->description = '';
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
        }
        
        $return['status'] = "OK";
        $return['row'] = $row;
        send($return);
    }
    
    private function _delete_account()
    {
        $id = $this->input->post("id");
        $this->db->where("id", $id);
        $this->db->delete("accounting_accounts");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _save_account()
    {
        $id = $this->input->post("id");
        $code_number = $this->input->post("code_number");
        $account_name = $this->input->post("account_name");
        $description = $this->input->post("description");
        $account_type = $this->input->post("account_type");
        $account_map = $this->input->post("account_map");
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        
        if ( $id == '' && trim($code_number) == '' )
        {
            $return["msg"] = 'El numero de codigo es un campo requerido!';
            send($return);
        }
        
        if ( trim($account_name) == '' )
        {
            $return["msg"] = 'El nombre de la cuenta es un campo requerido!';
            send($return);
        }
        
        if ( $id == '' && $this->code_exists($code_number, 'asset') )
        {
            $return['msg'] = "El numero de codigo ya existe, Por favor ingrese otro";
            send($return);
        }
        
        $data = [];
        $data["code_number"] = $code_number;
        $data["account_name"] = $account_name;
        $data["description"] = $description;
        $data["account_type"] = $account_type;
        
        if ( $account_map != '' )
        {
            $this->_clear_account_map( $account_map );
            $data["account_map"] = $account_map;
        }
        
        if (is_plugin_active("branches"))
        {
            $data["branch_id"] = $this->session->userdata("branch_id");
        }
        
        if ( $id > 0 )
        {
            $data["modified_by"] = $user_id;
            $this->db->where("id", $id);
            $this->db->update("accounting_accounts", $data);
        }
        else
        {
            $data["added_by"] = $user_id;
            $data["added_date"] = date("Y-m-d H:i:s");
            $this->db->insert("accounting_accounts", $data);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _clear_account_map( $account_map )
    {
        $this->db->where("account_map", $account_map);
        $this->db->update("accounting_accounts", ["account_map" => ""]);
    }
    
    function code_exists($code_number, $account_type)
    {
        $sql = "SELECT * FROM c19_accounting_accounts WHERE code_number = '$code_number' AND account_type = '$account_type'";
        $query = $this->db->query($sql);
        if ( $query && $query->num_rows() > 0 )
        {
            return true;
        }
        
        return false;
    }
    
    function accounts()
    {
        $this->load->view('accounting/accounts');
    }
    
    function asset()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'asset');
        $data["tbl_assets"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/asset', $data);
    }
    
    function liability()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'liability');
        $data["tbl_liability"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/liability', $data);
    }
    
    function equity()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'equity');
        $data["tbl_equity"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/equity', $data);
    }
    
    function income()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'income');
        $data["tbl_income"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/income', $data);
    }
    
    function expenses()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_accounts($this->datatablelib->datatable(), 'expenses');
        $data["tbl_expenses"] = $this->datatablelib->render();
        $this->load->view('accounting/accounts/expenses', $data);
    }
    
    function set_dt_accounts($datatable, $account_type)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1, 'account_type' => $account_type]);
        $datatable->ajax_url = site_url('accounting/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('code_number', false);
        $datatable->add_column('account_name', false);
        $datatable->add_column('description', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';
        $datatable->no_scroll = true;

        $datatable->table_id = "#tbl_" . $account_type;
        $datatable->add_titles('Account - ' . $account_type);
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_accounts()
    {
        $account_type = $this->input->post("account_type");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $filters = [];
        $filters["account_type"] = $account_type;
        $assets = $this->accounting_model->get_accounts($limit, $offset, $keywords, $order, $filters, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($assets->result() as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-". $account_type ."' data-id='" . $row->id . "' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "accounts", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["code_number"] = ucwords($row->code_number);
            $data_row["account_name"] = ucwords($row->account_name);
            $data_row["description"] = truncate_html($row->description, 250);
            
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }

    function search()
    {
        
    }

    /*
      Gives search suggestions based on what is being searched for
     */

    function suggest()
    {
        
    }

    function get_row()
    {
        
    }

    function delete()
    {
        
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width()
    {
        return 360;
    }

    public function index()
    {
        
    }

    public function save($data_item_id = -1)
    {
        
    }

    public function view($data_item_id = -1)
    {
        
    }
    
    /**
     * Transactions
     */
    private function _load_transaction()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $query = $this->db->get("accounting_transactions");
        
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $row->added_date = date($this->config->item('date_format'), strtotime($row->added_date));
            $row->purchased_date = date($this->config->item('date_format'), strtotime($row->purchased_date));
        }
        
        $return['status'] = "OK";
        $return['row'] = $row;
        send($return);
    }
    
    private function _delete_transaction()
    {
        $id = $this->input->post("id");
        $this->db->where("id", $id);
        $this->db->delete("accounting_transactions");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _save_transaction()
    {
        $id = $this->input->post("id");
        $account_id = $this->input->post("account_id");
        $amount = $this->input->post("amount");
        $added_date = $this->input->post("added_date");
        $purchased_date = $this->input->post("purchased_date");
        $purchased_amount = $this->input->post("purchased_amount");
        $depreciate_amount = $this->input->post("depreciate_amount");
        $description = $this->input->post("description");
        $transaction_type = $this->input->post("transaction_type");
        $payment_methods = $this->input->post("payment_methods");
        $invoice_number = $this->input->post("invoice_number");
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        
        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $added_date = date("Y-m-d H:i:s", strtotime(uk_to_isodate($added_date)));
            $purchased_date = date("Y-m-d H:i:s", strtotime(uk_to_isodate($purchased_date)));
        }
        else
        {
            $added_date = date("Y-m-d H:i:s", strtotime($added_date));
            $purchased_date = date("Y-m-d H:i:s", strtotime($purchased_date));
        }
        
        if ( $id == '' && trim($account_id) == '' )
        {
            $return["msg"] = 'El ID de cuenta es un campo requerido!';
            send($return);
        }
        
        $data = [];
        $data["account_id"] = $account_id;
        $data["amount"] = $amount;
        $data["added_date"] = $added_date;
        $data["purchased_date"] = $purchased_date;
        $data["transaction_type"] = $transaction_type;
        $data["purchased_amount"] = $purchased_amount;
        $data["depreciate_amount"] = $depreciate_amount;
        $data["description"] = $description;
        $data["payment_methods"] = $payment_methods;
        $data["invoice_number"] = $invoice_number;
        
        if (is_plugin_active("branches"))
        {
            $data["branch_id"] = $this->session->userdata("branch_id");
        }
        
        if ( $id > 0 )
        {
            $data["modified_by"] = $user_id;
            $this->db->where("id", $id);
            $this->db->update("accounting_transactions", $data);
        }
        else
        {
            $data["added_by"] = $user_id;
            $this->db->insert("accounting_transactions", $data);
        }
        
        $return["status"] = "OK";
        send($return);
    }
    
    function transactions()
    {
        $this->load->view('accounting/transactions');
    }
    
    function asset_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'asset');
        $data["tbl_assets"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('asset');
        $this->load->view('accounting/transactions/asset', $data);
    }
    
    function liability_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'liability');
        $data["tbl_liability"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('liability');
        $this->load->view('accounting/transactions/liability', $data);
    }
    
    function equity_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'equity');
        $data["tbl_equity"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('equity');
        $this->load->view('accounting/transactions/equity', $data);
    }
    
    function income_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'income');
        $data["tbl_income"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('income');
        $this->load->view('accounting/transactions/income', $data);
    }
    
    function expenses_transaction()
    {
        $this->load->library('DataTableLib');
        
        $this->set_dt_transactions($this->datatablelib->datatable(), 'expenses');
        $data["tbl_expenses"] = $this->datatablelib->render();
        $data["asset_accounts"] = $this->accounting_model->get_sel_accounts('expenses');
        $this->load->view('accounting/transactions/expenses', $data);
    }
    
    function set_dt_transactions($datatable, $transaction_type)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 10, 'transaction_type' => $transaction_type]);
        $datatable->ajax_url = site_url('accounting/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('account_name', false);
        switch( $transaction_type )
        {
            case 'asset':
                $datatable->add_column('added_date', false);
                $datatable->add_column('amount', false);
                $datatable->add_column('purchased_date', false);
                $datatable->add_column('purchased_amount', false);
                $datatable->add_column('depreciate_amount', false);
                $datatable->add_column('description', false);
                break;
            case 'liability':
                $datatable->add_column('amount', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'equity':
                $datatable->add_column('amount', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'income':
                $datatable->add_column('amount', false);
                $datatable->add_column('payment_methods', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('description', false);
                break;
            case 'expenses':
                $datatable->add_column('amount', false);
                $datatable->add_column('payment_methods', false);
                $datatable->add_column('added_date', false);
                $datatable->add_column('invoice_number', false);
                $datatable->add_column('description', false);
                break;
        }
        
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';
        $datatable->no_scroll = true;

        $datatable->table_id = "#tbl_" . $transaction_type;
        $datatable->add_titles('transaction - ' . $transaction_type);
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_transactions()
    {
        $transaction_type = $this->input->post("transaction_type");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $filters = [];
        $filters["transaction_type"] = $transaction_type;
        $assets = $this->accounting_model->get_transactions($limit, $offset, $keywords, $order, $filters, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($assets->result() as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-".$transaction_type."' data-id='" . $row->id . "' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "transactions", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["account_name"] = ucwords($row->account_name);
            $data_row["amount"] = to_currency($row->amount);
            $data_row["payment_methods"] = $row->payment_methods;
            $data_row["invoice_number"] = $row->invoice_number;
            $data_row["added_date"] = date($this->config->item('date_format'), strtotime($row->added_date));
            $data_row["purchased_date"] = date($this->config->item('date_format'), strtotime($row->purchased_date));
            $data_row["purchased_amount"] = to_currency($row->purchased_amount);
            $data_row["depreciate_amount"] = to_currency($row->depreciate_amount);
            $data_row["description"] = truncate_html($row->description, 250);
            
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function reports()
    {
        $this->load->view('accounting/reports');
    }
    
    public function report_export()
    {
        ini_set('memory_limit', '-1');
        
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $html = $this->load->view('accounting/reports/' . $report_type, $data, true); // render the view into HTML
        
        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";
        
        if ( file_exists($pdfFilePath) )
        {
            @unlink($pdfFilePath);
        }
        
        $this->load->library('pdf');
        
        if ( $report_type == 'balance_sheet' )
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');            
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        }
        
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }
    
    public function report_csv()
    {
        $report_type = urldecode($this->input->get("report_type"));
        
        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;
        
        $_POST["data_only"] = 1;
        $data = $this->_load_report_data( $report_type );
        
        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $out = $this->load->view('accounting/reports/csv/' . $report_type, $data, true); 
        
        header("Content-type: text/csv");
        header("Content-Disposition: inline; filename=$report_type-" . date('YmdHis') . ".csv");
        header("Pragma: public");
        header("Expires: 0");
        ini_set('zlib.output_compression', '0');
        echo $out;
    }
    
    private function _load_report_data( $report_type )
    {
        $filters = [];
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));
        
        $data = [];
        switch( $report_type )
        {
            case 'trial_balance':
                $data = $this->accounting_model->get_balance_sheet_data($filters);
                $data["accounts"] = $this->accounting_model->get_trial_balance_data($filters);
                $data["loan_fund_capital"] = $this->accounting_model->get_current_loan_amount($filters);
                $data["interest_on_current"] = $this->accounting_model->get_interest_on_current($filters);
                $data["interest_on_current_and_past_due"] = $this->accounting_model->get_interest_on_current($filters, 0);
                break;
            case 'financial_income':
                $data["accounts"] = $this->accounting_model->get_financial_income_data($filters);
                $data["interest_on_current"] = $this->accounting_model->get_interest_on_current($filters, 0);
                break;
            case 'balance_sheet':
                $data = $this->accounting_model->get_balance_sheet_data($filters);
                $data["interest_on_current"] = $this->accounting_model->get_interest_on_current($filters);
                break;
        }
        
        return $data;
    }
}

?>