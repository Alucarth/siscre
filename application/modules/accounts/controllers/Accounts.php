<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Accounts extends Secure_area implements iData_controller
{

    function __construct()
    {
        parent::__construct('accounts');
        
        $this->load->model("account_model");
        
        $this->define_client();
    }
    
    private function define_client()
    {
        $this->db->like("name", "client");
        $this->db->or_like("name", "customer");
        $query = $this->db->get("roles");
        
        if ( $query && $query->num_rows() > 0 )
        {
            define("ROLE_CLIENT", $query->row()->role_id);
        }
    }

    function index()
    {
        $this->load->library('DataTableLib');

        $this->set_dt_accounts($this->datatablelib->datatable());
        $data["tbl_accounts"] = $this->datatablelib->render();
        $this->load->view('accounts/list', $data);
    }
    
    function transactions()
    {
        $this->load->library('DataTableLib');
        
        $data["customers"] = $this->account_model->get_customers();
        $data["accounts"] = $this->account_model->get_list();

        $this->set_dt_account_transactions($this->datatablelib->datatable());
        $data["tbl_account_transactions"] = $this->datatablelib->render();
        $this->load->view('accounts/transactions', $data);
    }
    
    function report()
    {
        $this->load->library('DataTableLib');
        
        $res = $this->Employee->getLowerLevels();
        $data['staffs'] = $res;
        
        $data["customers"] = $this->account_model->get_customers();
        $data["accounts"] = $this->account_model->get_list();

        $this->set_dt_account_reports($this->datatablelib->datatable());
        $data["tbl_account_report"] = $this->datatablelib->render();
        $this->load->view('accounts/report', $data);
    }
    
    function set_dt_account_reports($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 7]);
        $datatable->ajax_url = site_url('accounts/ajax');

        $accounts = $this->account_model->get_list();
        
        $datatable->add_column('customer', false);
        $datatable->add_column('trans_date', false);
        
        if ( $accounts )
        {
            foreach ( $accounts->result() as $row )
            {
                $datatable->add_column('paid_in_' . $row->id, false);
                $datatable->add_column('withdrawal_' . $row->id, false);
                $datatable->add_column('balance_' . $row->id, false);
            }
        }
        
        
        $datatable->add_table_definition(["orderable" => false, "targets" => '_all']);

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';

        $datatable->no_scroll = true;
        $datatable->table_id = "#tbl_account_report";
        $datatable->add_titles('Account Report');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_account_reports()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $selected_user = $this->input->post("customer");
        
        $filters = [];
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_from_date')) : us_to_isodate($this->input->post('filter_from_date'));
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_to_date')) : us_to_isodate($this->input->post('filter_to_date'));
        
        $result = $this->account_model->get_account_reports($limit, $offset, $keywords, $order, $selected_user, $filters);
        $accounts = $this->account_model->get_list();
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        $count_all = 0;
        
        $total_deposit = [];
        $total_withdrawal = [];
        
        foreach ($result as $row)
        {
            $data_row = [];
            $data_row["DT_RowId"] = $row->trans_date;
            $data_row["customer"] = ucwords($row->customer_name);
            $data_row["trans_date"] = date($this->config->item('date_format'), strtotime($row->trans_date));
            
            if ( $accounts )
            {
                foreach ( $accounts->result() as $acc )
                {
                    $deposit_field = "deposit_" . $acc->id;
                    $withdrawal_field = "withdraw_" . $acc->id;
                    $data_row["paid_in_" . $acc->id] = to_currency($row->$deposit_field);
                    $data_row["withdrawal_" . $acc->id] = to_currency($row->$withdrawal_field);
                    
                    if ( !isset($total_deposit[$acc->id]) )
                    {
                        $total_deposit[$acc->id] = 0;
                    }
                    
                    if ( !isset($total_withdrawal[$acc->id]) )
                    {
                        $total_withdrawal[$acc->id] = 0;
                    }
                    
                    $total_deposit[$acc->id] += $row->$deposit_field;
                    $total_withdrawal[$acc->id] += $row->$withdrawal_field;
                    
                    $data_row["balance_" . $acc->id] = to_currency($total_deposit[$acc->id] - $total_withdrawal[$acc->id]);                    
                }
            }
            
            $tmp[] = $data_row;
            $count_all++;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch($type)
        {
            case 1:
                $this->_dt_accounts();
                break;
            case 2: // Load loan product options
                $this->_handle_account_options();
                break;
            case 3: // Load loan product info to calculator
                $this->_handle_account_info();
                break;
            case 4: // Delete loan product
                $this->_handle_delete_account();
                break;
            case 5: // Retrieve account balance
                $this->_handle_load_account_balance();
                break;
            case 6: // Retrieve account my transactions
                $this->_dt_account_transactions();
                break;
            case 7: // Retrieve account reports
                $this->_dt_account_reports();
                break;
            case 8:
                $this->_get_accounts_total();
        }
    }
    
    private function _get_accounts_total()
    {
        $filters = [];
        $filters["search"] = $this->input->post("search");
        $filters["sel_user"] = $this->input->post("employee_id");
        $filters["account_id"] = $this->input->post("account_id");
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_from_date')) : us_to_isodate($this->input->post('filter_from_date'));
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_to_date')) : us_to_isodate($this->input->post('filter_to_date'));
        
        $total_amount = $this->account_model->get_accounts_total($filters);
        
        $return["total_balance"] = to_currency($total_amount);
        $return["status"] = "OK";
        send( $return );
    }
    
    private function _handle_load_account_balance()
    {
        $user_id = $this->input->post("user_id");
        $account_id = $this->input->post("account_id");
        
        $balance = $this->account_model->get_account_balance($user_id, $account_id);
        
        $return["status"] = "OK";
        $return["balance"] = to_currency($balance);
        $return["raw_balance"] = $balance;
        
        send($return);
    }
    
    private function _handle_delete_account()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $this->db->delete("accounts");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_account_info()
    {
        $id = $this->input->post("id");
        
        $account_info = $this->account_model->get_details($id);
        
        $return["status"] = "OK";
        $return["account_info"] = $account_info;
        
        send($return);
    }
    
    private function _handle_account_options()
    {
        $accounts = $this->account_model->get_list();
        $val = $this->input->post("val");
        
        $options = '<option>Choose</option>';
        if ( $accounts )
        {
            foreach($accounts->result() as $row)
            {
                $selected = '';
                if ( $row->id == $val )
                {
                    $selected = 'selected="selected"';
                }
            
                $options .= '<option value="' . $row->id . '" ' . $selected . '>' . ucwords($row->product_name) . '</option>';
            }
        }
        
        $return["options"] = $options;
        $return["status"] = "OK";
        
        send($return);
    }

    function set_dt_accounts($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1]);
        $datatable->ajax_url = site_url('accounts/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('account_name', false);
        $datatable->add_column('account_type', false);
        $datatable->add_column('description', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';

        $datatable->table_id = "#tbl_accounts";
        $datatable->add_titles('Loan products');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_accounts()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $accounts = $this->account_model->get_list($limit, $offset, $keywords, $order);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        $count_all = 0;
        foreach ($accounts->result() as $row)
        {
            $actions = "<a href='" . site_url('accounts/view/' . $row->id) . "' class='btn btn-xs btn-default btn-secondary' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "accounts", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["account_name"] = ucwords($row->account_name);
            $data_row["account_type"] = ucwords($row->account_type);
            $data_row["description"] = truncate_html($row->description, 250);
            
            $tmp[] = $data_row;
            $count_all++;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    function set_dt_account_transactions($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 6]);
        $datatable->ajax_url = site_url('accounts/ajax');

        $datatable->add_column('account_name', false);
        $datatable->add_column('amount', false);
        $datatable->add_column('description', false);
        $datatable->add_column('trans_type', false);
        $datatable->add_column('trans_date', false);
        $datatable->add_column('added_by_name', false);
        $datatable->add_column('created_by_name', false);
        $datatable->add_column('actions', false);
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[5, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';

        $datatable->callbacks["footerCallback"] = "accountsFooter";
        $datatable->table_id = "#tbl_account_transactions";
        $datatable->add_titles('Account Transactions');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_account_transactions()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $selected_user = $this->input->post("employee_id");
        
        $filters = [];
        $filters["account_id"] = $this->input->post("account_id");
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_from_date')) : us_to_isodate($this->input->post('filter_from_date'));
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate($this->input->post('filter_to_date')) : us_to_isodate($this->input->post('filter_to_date'));
        
        $accounts = $this->account_model->get_account_transactions($limit, $offset, $keywords, $order, $selected_user, $filters, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();
        $amountt = 0;

        if ( $accounts )
        {
            foreach ($accounts->result() as $row)
            {
                $data_row = [];
                $data_row["DT_RowId"] = $row->id;
                $data_row["account_name"] = ucwords($row->account_name);
                $data_row["trans_type"] = $row->trans_type == "deposit"? 'deposito': 'retiro';
                if ($row->trans_type != "deposit")
                {
                    $amountt = $row->amount * -1;
                    $data_row["amount"] = to_currency($amountt);    
                }
                else{
                    $data_row["amount"] = to_currency($row->amount);
                }
                
                $data_row["description"] = truncate_html($row->description, 250);
                
                $data_row["trans_date"] = date($this->config->item('date_format'), strtotime($row->trans_date));
                $data_row["added_by_name"] = $row->added_by_name;
                $data_row["created_by_name"] = $row->created_by_name;
                $data_row["actions"] = '<a href="'.site_url('accounts/receipt/' . $row->id. '/'.$row->trans_type).'" target="_blank" >ver detalle</a>'; //se adiciono boton manualmente reenderiza html por lo del datatable

                $tmp[] = $data_row;
            }
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
    
    function receipt( $transaction_id = '', $trans_type = 'deposit' )
    {
        $transaction = $this->account_model->get_transaction_info($transaction_id, $trans_type);
        $user_info = $this->Employee->get_info($transaction->added_by);
        $amount = $transaction->amount;
        $trans_id = $transaction->id;
        $account_id = $transaction->account_id;
        $trans_date = $transaction->trans_date;

        $branch = $this->account_model->get_branch($transaction->branch_id);
        $person = $this->account_model->get_person($transaction->person_id);
        // $person = $this->Person->get_info($transaction->person_id);
        // $myfile = fopen("transactions.txt", "w") or die("Unable to open file!");
        // $txt = json_encode($transaction);
        // fwrite($myfile, $txt);
        // fclose($myfile);
        
        $data["customer"] = $transaction->customer_name;
        $data["user_info"] = $user_info;
        $data["amount"] = $amount;
        $data["trans_type"] = $trans_type;
        $data["trans_id"] = $trans_id;
        $data["account_id"] = $account_id;
        $data["trans_date"] = $trans_date;
        $data["branch"] = $branch;
        $data["person"] = $person;
        $this->load->view("accounts/receipt", $data);
    }
    
    function customer_search()
    {
        $suggestions = $this->account_model->get_customer_search_suggestions($this->input->get('query'), 30);
        $data = $tmp = array();

        foreach ($suggestions as $suggestion):
            $t = explode("|", $suggestion);
            $tmp = array("value" => $t[1], "data" => $t[0]);
            $data[] = $tmp;
        endforeach;

        echo json_encode(array("suggestions" => $data));
        exit;
    }
    
    function deposit($id = -1)
    {
        $order['index'] = 1;
        $order['direction'] = 'ASC';
        $accounts = $this->account_model->get_list(10000, 0, "", $order);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        $deposit_info = $this->account_model->get_transaction_info($id);
        
        /*$clients = [];
        if ( defined("ROLE_CLIENT") )
        {
            $clients = $this->account_model->get_users(ROLE_CLIENT);
        }*/
        
        $data = [];        
        $data["user_info"] = $user_info;
        $data["deposit_info"] = $deposit_info;
        $data["accounts"] = $accounts;
        $data["trans_id"] = $trans_id;
        $data["account_id"] = $account_id;
        //$data["clients"] = $clients;
        
        $this->load->view("accounts/deposit", $data);
    }
    
    function withdraw($id = -1)
    {
        $order['index'] = 1;
        $order['direction'] = 'ASC';
        $accounts = $this->account_model->get_list(10000, 0, "", $order);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        $withdraw_info = $this->account_model->get_transaction_info($id, 'withdraw');
        
        $data = [];        
        $data["user_id"] = $user_id;
        $data["user_info"] = $user_info;
        $data["withdraw_info"] = $withdraw_info;
        $data["accounts"] = $accounts;
        $data["trans_id"] = $trans_id;
        $data["account_id"] = $account_id;
        
        $this->load->view("accounts/withdraw", $data);
    }
    
    function save_transaction($id = -1)
    {
        $account_id = $this->input->post("account_id");
        $description = $this->input->post("description");
        $trans_type = $this->input->post("trans_type");
        $client_id = $this->input->post("client_id");
        if ($trans_type != "deposit"){
            $amount = $this->input->post("amount") * -1;    
        }
        else{
            $amount = $this->input->post("amount");
        }
        //$this->session->userdata();
        
        if ( !is_numeric($amount) || trim($amount) == '' ) 
        {
            $return["message"] = "La cantidad debe ser un valor numérico válido";
            send($return);
        }
    
        //if ( $this->has_current_loans( $client_id ) && $trans_type == 'withdraw' )
        //{
        //    $return["message"] = "Lo sentimos, el cliente seleccionado no puede retirar dinero hasta que haya pagado el préstamo en su totalidad.";
        //    send($return);
        //}
        
        $data["account_id"] = $account_id;
        $data["amount"] = $amount;
        $data["description"] = $description;
        $data["trans_type"] = $trans_type;
        $data["branch_id"] = $this->session->userdata()['branch_id'];
        $data["person_id"] = $this->session->userdata()['person_id'];
        
        if ( $id < 0 )
        {
            $data["trans_date"] = date("Y-m-d H:i:s");
            $data["added_by"] = $client_id;
            $data["modified_by"] = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        else
        {
            $data["date_modified"] = date("Y-m-d H:i:s");
            $data["added_by"] = $client_id;
            $data["modified_by"] = $this->Employee->get_logged_in_employee_info()->person_id;            
        }
        
        $id = $this->account_model->save_deposit($id, $data);
        
        $url = site_url('accounts/receipt/' . $id . '/' . $trans_type);
        
        $return["status"] = "OK";
        $return["url"] = $url;
        
        send($return);
    }
    
    private function has_current_loans( $client_id )
    {
        $sql = "SELECT count(*) cnt FROM c19_loans a WHERE a.loan_status = 'approved' AND a.loan_balance > 0 AND a.customer_id = $client_id";
        $query = $this->db->query( $sql );
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->row()->cnt > 0;
        }
        
        return false;
    }

    function view($id = -1)
    {
        $account_info = $this->account_model->get_details($id);
        
        $data = [];        
        $data["account_info"] = $account_info;
        $data["account_types"] = $this->get_account_types();
        
        $this->load->view("accounts/form", $data);
    }
    
    private function get_account_types()
    {
        $tmp = [
            "asset" => "Activo",
            "equity" => "Equidad",
            "expenses" => "Gastos",
            "income" => "Ingresos",
            "liability" => "Responsabilidad",
        ];
        
        return $tmp;
    }
    
    function save($id = -1)
    {
        $account_name = $this->input->post("account_name");
        $account_type = $this->input->post("account_type");
        $description = $this->input->post("description");
        
        if ( trim($account_name) == '' ) 
        {
            $return["message"] = "El nombre de la cuenta es un campo requerido";
            send($return);
        }
            
        $data["account_name"] = $account_name;
        $data["account_type"] = $account_type;
        $data["description"] = $description;
        
        if ( $id < 0 )
        {
            $data["date_added"] = time();
            $data["added_by"] = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        else
        {
            $data["date_modified"] = time();
            $data["modified_by"] = $this->Employee->get_logged_in_employee_info()->person_id;            
        }
        
        $id = $this->account_model->save($id, $data);
        
        $return["status"] = "OK";
        $return["url"] = site_url('accounts/view/' . $id);
        
        send($return);
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
    
    function print_receipt()
    {
        ini_set('memory_limit', '-1');
        
        $data["customer"] = $this->input->post("customer");
        $data["trans_type"] = $this->input->post("trans_type");
        $data["amount"] = $this->input->post("amount");
        $data["trans_id"] = $this->input->post("trans_id");
        $data["account_id"] = $this->input->post("account_id");
        $data["branch_name"] = $this->input->post("branch_name");
        $data["person_name"] = $this->input->post("person_name");
        
        $html = $this->load->view('accounts/pdf_receipt', $data, true);
        
        $filename = "receipt.pdf";
        
        $pdfFilePath = FCPATH . "/downloads/reports/" . $filename;

        if (file_exists($pdfFilePath))
        {
            @unlink($pdfFilePath);
        }

        $this->load->library('pdf');

        $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can
        
        $return["status"] = "OK";
        $return["url"] = base_url("downloads/reports/" . $filename);

        send($return);
    }
}

?>