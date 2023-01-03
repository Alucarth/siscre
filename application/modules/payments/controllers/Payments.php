<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");
require("util.php");

class Payments extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('payments');
        
        $this->load->library('DataTableLib');
        $this->load->library('user_agent');
    }

    function index()
    {
        $data['controller_name'] = strtolower(get_class());
        $data['form_width'] = $this->get_form_width();
        
        $res = $this->Employee->getLowerLevels();
        $data['staffs'] = $res;
        
        $this->set_dt_payments($this->datatablelib->datatable());
        $data["tbl_payments"] = $this->datatablelib->render();   
        
        $data["is_mobile"] = $this->agent->is_mobile();
        
        $this->load->view('payments/manage', $data);
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch( $type )
        {
            case 1: // get loan schedules
                $this->_get_loan_schedules();
                break;
            case 2: // check for penalties
                $this->_check_loan_penalties();
                break;
            case 3: // get payments table
                $this->_dt_payments();
                break;
            case 4:
                $this->_get_total_balance();
                break;
            case 5:
                $this->load_transaction_history();
                break;
        }
    }
    
    private function _get_total_balance()
    {
        $total_balance = $this->session->userdata("tbl_balance");
        
        $return["total_balance"] = to_currency($total_balance, 1);
        $return["status"] = "OK";
        
        send($return);
    }
    
    function set_dt_payments($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 3]);
        $datatable->ajax_url = site_url('payments/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('trans_id', false);
        $datatable->add_column('customer', false);
        $datatable->add_column('loan_amount', false);
        $datatable->add_column('payable_amount', false);
        $datatable->add_column('loan_balance', false);
        $datatable->add_column('trans_date', false);
        $datatable->add_column('teller', false);
        

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        $datatable->callbacks["footerCallback"] = "paymentsFooter";
        
        $datatable->table_id = "#tbl_payments";
        $datatable->add_titles('Payments');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_payments()
    {
        $selected_user = $this->input->post("employee_id");
        $status = $this->input->post("status");
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        $loan_status = $this->input->post("loan_status");

        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $filters = [];
        $filters["from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($from_date)) : strtotime($from_date);
        $filters["to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($to_date)) : strtotime($to_date);
        $filters["loan_status"] = $loan_status;
        
        $payments = $this->Payment->get_all($limit, $offset, $keywords, $order, $selected_user, $filters);  
        $count_all = $this->Payment->get_all($limit, $offset, $keywords, $order, $selected_user, $filters, 1);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        $tbl_balance = 0;
        foreach ($payments->result() as $payment)
        {
            $actions = "<a href='" . site_url('payments/view/' . $payment->loan_payment_id) . "' class='btn btn-xs btn-default btn-secondary' title='View'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "payments", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-payment-id='" . $payment->loan_payment_id . "' title='Delete'><span class='fa fa-trash'></span></a> ";
            }
            $actions .= "<a href='javascript:void(0)' data-url='".  site_url('payments/printIt/' . $payment->loan_payment_id) ."' class='btn-print-receipt btn btn-default'>Print</a>";

            $data_row = [];
            $data_row["DT_RowId"] = $payment->loan_payment_id;
            $data_row["actions"] = $actions;
            
            $data_row["trans_id"] = $payment->loan_payment_id;
            $data_row["customer"] = ucwords($payment->customer_name);
            $data_row["loan_amount"] = (trim($payment->loan_type) !== "" ? $payment->loan_type : "Flexible") . " (" . to_currency($payment->loan_amount) . ")";
            $data_row["loan_balance"] = to_currency($payment->balance_amount - $payment->paid_amount);
            $data_row["payable_amount"] = to_currency($payment->paid_amount);
            $data_row["trans_date"] = date($this->config->item('date_format'), $payment->date_paid);
            $data_row["teller"] = ucwords($payment->teller_name);
            
            $tbl_balance += $payment->paid_amount;

            $tmp[] = $data_row;
        }

        $this->session->set_userdata("tbl_balance", $tbl_balance);
        
        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    public function recalc_bal($loan_id = '')
    {
        $this->Payment->balance_recalc($loan_id);
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

    function view($payment_id = -1)
    {
        $data['payment_info'] = $this->Payment->get_info($payment_id);
        $res = $this->Payment->get_loans($data['payment_info']->customer_id);

        $loans = array();
        foreach ($res as $loan)
        {
            $tmp['loan_id'] = $loan->loan_id;
            $tmp['balance'] = $loan->loan_balance;
            $tmp['text'] = $loan->loan_type . " (" . to_currency($loan->loan_amount) . ") - bal: " . to_currency($loan->loan_balance);
            $loans[] = $tmp;
        }

        $data['loans'] = $loans;
        $data["is_mobile"] = $this->agent->is_mobile();
        $this->load->view("payments/form", $data);
    }

    function printIt($payment_id = -1)
    {
        $payment = $this->Payment->get_info($payment_id);
        $loan = $this->Loan->get_info($payment->loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $person = $this->Person->get_info($payment->teller_id);
        $loan_agent = $this->Person->get_info($loan->loan_agent_id);
        $customer = $this->Person->get_info($payment->customer_id);
        $collateral = $this->Guarantee->get_info($payment->loan_id);

        // pdf viewer 
        $data['collateral'] = $collateral;
        $data['count'] = $payment->loan_payment_id;
        $data['client'] = ucwords($customer->first_name." ".$customer->last_name);
        $data['account'] = $loan->account;
        //$data['loan'] = to_currency($loan->loan_amount);
        $data['loan'] = to_currency($loan->apply_amount);
        //campos añadidos gabo
        $data['loan_agent'] = $loan_agent->first_name . " " . $loan_agent->last_name;
        $data['loan_approved_date'] = date($this->config->item('date_format'), $loan->loan_approved_date);
        // $data['interest'] = to_currency($loan->interest_rate);
        $data['balance'] = to_currency($loan->loan_balance);
        $data['paid'] = to_currency($payment->paid_amount);
        $data['lpp'] = to_currency($payment->lpp_amount);
        $data['trans_date'] = date($this->config->item('date_format'), $payment->date_paid);
        $data['time_date'] = date("h:i:sa");
        $data['payment_due']= date($this->config->item('date_format'), $payment->payment_due);
        $data['teller'] = $person->first_name . " " . $person->last_name;
        //$data['user'] = $person->username;
        // $data['capital'] =  to_currency($payment->paid_amount - $loan->interest_rate );
        $data["customer"] = $customer;
        $data["json_objetcs"] = $loan->periodic_loan_table;
        $json_objects = json_decode($loan->periodic_loan_table);
        $data["object"] = null;
        $has_nextpay = false;
        $lpp = $payment->lpp_amount;
        $number = 0;
        $size = sizeof($json_objects);
        $data["size"] = $size;
   
        foreach($json_objects as $object)
        {
            $number++;
         
            if($has_nextpay)
            {
                $data['next_pay'] =   $object->payment_date;
                $has_nextpay = false;
            }

            if($object->payment_date ==  $data['payment_due'] )
            {
                $data['number'] = $number;
                $data["object"] = $object;
                $data['capital'] =  to_currency($object->payment_amount_capital);
                //$data['capital'] =  to_currency($object->payment_amount_capital - $object->interest); // el payment_amount_capital tiene el interes sumando por lo cual se esta restando el interes revisar donde se guarda el valor o tener encuenta este datos siempre al momento de hacer consultas
                $data['interest'] = to_currency($object->interest);
                $data['operating_expenses_amount'] = to_currency($object->operating_expenses_amount);
                $data['total'] = to_currency($object->payment_amount_capital+$object->interest+$object->operating_expenses_amount+$lpp);
                $data["literal"] = Util::convertirNumeroLetra(number_format((float)($object->payment_amount_capital+$object->interest+$object->operating_expenses_amount+$lpp), 2, '.', '')," BOLIVIANOS");
                //$data['total'] = to_currency($object->payment_amount_capital+$object->operating_expenses_amount);// ense caso como se asume que esta el interes se le adiciona los gastos operativos
                //$data["literal"] = Util::convertirNumeroLetra(number_format((float)($object->payment_amount_capital+$object->operating_expenses_amount), 2, '.', '')," Bs"); //mismo caso que la linea de arriba
                $has_nextpay = true;
            }
        }
        
        

        //$data["david"] = 'hola'; //por aqui se envia las variables

        $filename = "payments_".date("ymdhis");
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";
        
        $data["is_mobile"] = $this->agent->is_mobile();

        ini_set('memory_limit', '-1');
        $html = $this->load->view('payments/pdf_report', $data, true); // render the view into HTML

        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        //footer pie de pagina
        // $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822)); 
        $pdf->WriteHTML($html); // write the HTML into the PDF
        
        // end of pdf viewer
        if ($this->agent->is_mobile())
        {
            $pdf->Output($pdfFilePath, 'I');
        }
        else
        {
            $pdf->Output($pdfFilePath, 'F'); // save to file because we can
            $data['pdf_file'] = base_url("downloads/reports/$filename.pdf");
            $this->load->view("payments/print", $data);
        }
    }

    function save($payment_id = -1)
    {
        $payment_data = array(
            'account' => $this->input->post('account'),
            'loan_id' => $this->input->post('loan_id'),
            'customer_id' => $this->input->post('customer'),
            'paid_amount' => $this->input->post('paid_amount'),
            'balance_amount' => $this->input->post('balance_amount'),
            'date_paid' => $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_paid'))) : strtotime($this->input->post('date_paid')),
            'remarks' => $this->input->post('remarks'),
            'teller_id' => $this->input->post('teller'),
            'modified_by' => $this->input->post('modified_by') > 0 ? $this->input->post('modified_by') : 0,
            'payment_due' => $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('payment_due'))) : strtotime($this->input->post('payment_due')),
            'lpp_amount' => $this->input->post('lpp_amount')
        );

        if ($this->input->post("loan_payment_id") > 0)
        {
            $payment_data['loan_payment_id'] = $this->input->post('loan_payment_id');
        }

        // transactional to make sure that everything is working well
        $this->db->trans_start();
        
        if ($this->Payment->save($payment_data, $payment_id))
        {
            $wallet_data["amount"] = $payment_data["paid_amount"];
            $wallet_data["wallet_type"] = "debit";
            $wallet_data["trans_date"] = strtotime(date("Y-m-d H:i:s"));
            $wallet_data["added_by"] = $this->Employee->get_logged_in_employee_info()->person_id;
            $wallet_data["descriptions"] = "In payments for <a href='" . site_url("payments/view/" . $payment_data['loan_payment_id']) . "' target='_blank'>" . site_url("payments/view/" . $payment_data['loan_payment_id']) . "</a>";
            
            $this->My_wallet->save($wallet_data);
            
            $this->Loan->update_balance($payment_data['loan_id']);
            
            //New Payment            
            if ($payment_id == -1)
            {
                $return = array(
                    'success' => true, 
                    'message' => $this->lang->line('loans_successful_adding') . ' ' . $payment_data['loan_payment_id'], 
                    'loan_payment_id' => $payment_data['loan_payment_id']
                );
                
                $payment_id = $payment_data['loan_payment_id'];
            }
            else //previous loan
            {
                $return = array(
                    'success' => true, 
                    'message' => $this->lang->line('loans_successful_updating') . ' ' . $payment_data['loan_payment_id'], 
                    'loan_payment_id' => $payment_id
                );
            }
            
        }
        else//failure
        {
            $return = array(
                'success' => false, 
                'message' => $this->lang->line('loans_error_adding_updating') . ' ' . $payment_data['loan_payment_id'], 
                'loan_payment_id' => -1
            );
        }
        
        $this->db->trans_complete();
        
        send($return);
    }

    function delete()
    {
        $payments_to_delete = $this->input->post('ids');

        if ($this->Payment->delete_list($payments_to_delete))
        {
            foreach ( $payments_to_delete as $payment_id )
            {
                $payment = $this->Payment->get_info($payment_id);
                $this->Payment->balance_recalc($payment->loan_id);
            }
            echo json_encode(array('success' => true, 'message' => $this->lang->line('loans_successful_deleted') . ' ' .
                count($payments_to_delete) . ' ' . $this->lang->line('payments_one_or_multiple')));
        }
        else
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('payments_cannot_be_deleted')));
        }
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width()
    {
        return 360;
    }

    function data()
    {
        $sel_user = $this->input->get("employee_id");
        $index = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : 1;
        $dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : "asc";
        $order = array("index" => $index, "direction" => $dir);
        $length = isset($_GET['length'])?$_GET['length']:50;
        $start = isset($_GET['start'])?$_GET['start']:0;
        $key = isset($_GET['search']['value'])?$_GET['search']['value']:"";

        $payments = $this->Payment->get_all($length, $start, $key, $order, $sel_user);

        $format_result = array();

        foreach ($payments->result() as $payment)
        {
            $actions = anchor('payments/view/' . $payment->loan_payment_id, $this->lang->line('common_view'), array('class' => 'btn btn-success', "title" => $this->lang->line('payments_update'))) . " ";
            $actions .= "<a href='javascript:void(0)' data-url='".  site_url('payments/printIt/' . $payment->loan_payment_id) ."' class='btn-print-receipt btn btn-default'>Print</a>";
            //$actions .= anchor('payments/printIt/' . $payment->loan_payment_id, $this->lang->line('common_print'), array('class' => 'modal_link btn btn-default', 'data-toggle' => 'modal', 'data-target' => '#print_modal', "title" => $this->lang->line('payments_print')));
            
            $format_result[] = array(
                "<input type='checkbox' name='chk[]' id='payment_$payment->loan_payment_id' value='" . $payment->loan_payment_id . "'/>",
                $payment->loan_payment_id,
                ucwords($payment->customer_name),
                (trim($payment->loan_type) !== "" ? $payment->loan_type : "Flexible") . " (" . to_currency($payment->loan_amount) . ")",
                to_currency($payment->balance_amount),
                to_currency($payment->paid_amount),
                date($this->config->item('date_format'), $payment->date_paid),
                ucwords($payment->teller_name),
                $actions
            );
        }

        $data = array(
            "recordsTotal" => $this->Payment->count_all($sel_user),
            "recordsFiltered" => $this->Payment->count_all($sel_user),
            "data" => $format_result
        );

        echo json_encode($data);
        exit;
    }

    function get_loans($customer_id)
    {
        $loans = $this->Payment->get_loans($customer_id);

        foreach ($loans as $loan)
        {
            $loan->loan_amount = to_currency($loan->loan_amount);
            $loan->loan_balance = "bal: " . to_currency($loan->loan_balance);
            $loan->loan_type = (trim($loan->loan_type) !== "")?$loan->loan_type:"Flexible";
        }

        echo json_encode($loans);
        exit;
    }

    function get_customer($customer_id)
    {
        $customer = $this->Customer->get_info($customer_id);
        $suggestion['data'] = $customer->person_id;
        $suggestion['value'] = $customer->first_name . " " . $customer->last_name;

        echo json_encode($suggestion);
        exit;
    }
    
    private function _check_loan_penalties()
    {
        $due_date = $this->input->post("due_date");
        $amount_to_pay = $this->input->post("amount_to_pay");
        $penalty_value = $this->input->post("penalty_value");
        $penalty_type = $this->input->post("penalty_type");
        
        if ($this->config->item('date_format') == 'd/m/Y')
        {
            $due_date = uk_to_isodate($due_date);
        }
        
        $penalty_amount = 0;
        if ( time() > strtotime($due_date) )
        {
            // penalize
            $penalty = $penalty_value;
            if ( $penalty_type == 'percentage' && $penalty_value > 0 )
            {
                $penalty = $amount_to_pay * ($penalty_value / 100);
            }
            
            if ( $penalty > 0 )
            {
                $penalty_amount = $amount_to_pay + $penalty;
            }
        }
        
        $return['status'] = "OK";
        $return['penalty_amount'] = $penalty_amount;
        $return['amount_to_pay'] = number_format($amount_to_pay, 2, '.', '');
        
        send($return);
    }
    
    private function _get_loan_schedules()
    {
        $loan_id = $this->input->post("loan_id");
        
        $this->db->where("loan_id", $loan_id);
        $query = $this->db->get("loans");
        
        $loan_balance = 0;
        $options = '<option value="">Choose</option>';
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $penalty_value = $row->penalty_value;
            $penalty_type = $row->penalty_type;
            
            // Get the due date paids for this loan
            $due_date_paids = $this->_get_due_date_paids($loan_id);
            
            // Match the payment date where it belongs
            $scheds = json_decode($row->periodic_loan_table);
            if ( count($scheds) > 0 )
            {
                foreach ( $scheds as $sched )
                {
                    if ( $this->config->item('date_format') == 'd/m/Y' )
                    {
                        $payment_date = strtotime(uk_to_isodate($sched->payment_date));
                    }
                    else
                    {
                        $payment_date = strtotime($sched->payment_date);
                    }
                    
                    if ( in_array($payment_date, $due_date_paids) )
                    {
                        $options .= '<option value="'.$sched->payment_date.'" data-amount-to-pay="'. $sched->payment_amount .'" data-penalty-value="' . $penalty_value . '" data-penalty-type="' . $penalty_type . '" disabled="disabled">' . $sched->payment_date . ' (paid)</option>';
                    }
                    else
                    {
                        $options .= '<option value="'.$sched->payment_date.'" data-amount-to-pay="'. $sched->payment_amount .'" data-penalty-value="' . $penalty_value . '" data-penalty-type="' . $penalty_type . '">' . $sched->payment_date . '</option>';
                    }
                }
            }
            
            $loan_balance = $row->loan_balance;
        }
        
        $return["status"] = "OK";
        $return["options"] = $options;
        $return["balance"] = $loan_balance;
        send($return);
    }

    private function load_transaction_history()
    {
        $loan_payment_id = $this->input->post("loan_payment_id");
        
        $loan_id = $this->db->select("loan_id")
                ->from("loan_payments")
                ->where("delete_flag", 0)
                ->where("loan_payment_id", $loan_payment_id)
                ->get()->row()->loan_id;
        
        $this->db->where("loan_id", $loan_id);
        $query = $this->db->get("loans");
        
        $loan_balance = 0;
        
        $data["scheds"] = [];
        if ( $query && $query->num_rows() > 0 )
        {
            $row = $query->row();
            $penalty_value = $row->penalty_value;
            $penalty_type = $row->penalty_type;
            
            $due_date_paids = $this->_get_due_date_paids($loan_id);
            
            // Match the payment date where it belongs
            $scheds = json_decode($row->periodic_loan_table, 1);
            if ( count($scheds) > 0 )
            {
                $data["scheds"] = $scheds;
            }
            
            $loan_balance = $row->loan_balance;
            $data["loan_balance"] = $loan_balance;
            $data["due_date_paids"] = $due_date_paids;
        }
        
        $html = $this->load->view("payments/modals/transaction_history", $data, 1);
        
        $return["status"] = "OK";
        $return["html"] = $html;
        $return["balance"] = $loan_balance;
        send($return);
    }
    
    private function _get_due_date_paids($loan_id)
    {
        $sql = "SELECT a.payment_due FROM c19_loan_payments a WHERE a.loan_id = '$loan_id' AND a.delete_flag = 0";
        $query = $this->db->query( $sql );
        
        $tmp = [];
        if ( $query && $query->num_rows() > 0 )
        {
            foreach ( $query->result() as $row )
            {
                $tmp[] = $row->payment_due;
            }
        }
        
        return $tmp;
    }
}

?>