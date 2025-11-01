<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class General_ledger extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('general_ledger');

        $this->load->model("general_ledger_model");
    }

    function index()
    {
        $this->load->view('general_ledger/reports');
    }
    
    function generate()
    {
        $filters = [];
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' 
            ? strtotime(uk_to_isodate($this->input->post('date_from'))) 
            : strtotime($this->input->post('date_from'));
            
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' 
            ? strtotime(uk_to_isodate($this->input->post('date_to'))) 
            : strtotime($this->input->post('date_to'));
        
        $data = [];
        
        $accounting_transactions = $this->general_ledger_model->get_accounting_transactions($filters);
        
        if (!empty($accounting_transactions)) {
            uasort($accounting_transactions, function($a, $b) {
                return $b->voucher_info->voucher_number - $a->voucher_info->voucher_number;
            });
        }
        
        $data["accounting_transactions"] = $accounting_transactions;
        
        $data["account_transactions"] = $this->general_ledger_model->get_account_transactions($filters);
        $data["loan_transactions"] = $this->general_ledger_model->get_loan_transactions($filters);
        $data["loan_interest_transactions"] = $this->general_ledger_model->get_loan_interest_transactions($filters);
        
        $this->load->view('general_ledger/reports/data', $data);
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

    public function save($id = -1)
    {
        
    }

    public function view($id = -1)
    {
        
    }

    public function report_export()
    {
        ini_set('memory_limit', '-1');

        $report_type = urldecode($this->input->get("report_type"));

        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;

        $_POST["data_only"] = 1;
        $data = $this->_load_report_data($report_type);

        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));

        $html = $this->load->view('general_ledger/reports/' . $report_type, $data, true);

        $pdfFilePath = FCPATH . "/downloads/reports/$report_type.pdf";

        if (file_exists($pdfFilePath))
        {
            @unlink($pdfFilePath);
        }

        $this->load->library('pdf');

        if ($report_type == 'balance_sheet')
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');
        }
        else
        {
            $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');
        }

        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html);
        $pdf->Output($pdfFilePath, 'F');

        redirect(base_url("downloads/reports/" . $report_type . ".pdf"));
    }

    public function report_csv()
    {
        $report_type = urldecode($this->input->get("report_type"));

        $_POST["date_from"] = urldecode($this->input->get("date_from"));
        $_POST["date_to"] = urldecode($this->input->get("date_to"));
        $_POST["report_type"] = $report_type;

        $_POST["data_only"] = 1;
        $data = $this->_load_report_data($report_type);

        $data["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $data["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));

        $out = $this->load->view('general_ledger/reports/csv/' . $report_type, $data, true);

        header("Content-type: text/csv");
        header("Content-Disposition: inline; filename=$report_type-" . date('YmdHis') . ".csv");
        header("Pragma: public");
        header("Expires: 0");
        ini_set('zlib.output_compression', '0');
        echo $out;
    }

    private function _load_report_data($report_type)
    {
        $filters = [];
        $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_from'))) : strtotime($this->input->post('date_from'));
        $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('date_to'))) : strtotime($this->input->post('date_to'));

        $data = [];
        switch ($report_type)
        {
            case 'trial_balance':
                $data = $this->general_ledger_model->get_balance_sheet_data($filters);
                $data["accounts"] = $this->general_ledger_model->get_trial_balance_data($filters);
                $data["loan_fund_capital"] = $this->general_ledger_model->get_current_loan_amount($filters);
                $data["interest_on_current"] = $this->general_ledger_model->get_interest_on_current($filters);
                $data["interest_on_current_and_past_due"] = $this->general_ledger_model->get_interest_on_current($filters, 0);
                break;
            case 'financial_income':
                $data["accounts"] = $this->general_ledger_model->get_financial_income_data($filters);
                $data["interest_on_current"] = $this->general_ledger_model->get_interest_on_current($filters, 0);
                break;
            case 'balance_sheet':
                $data = $this->general_ledger_model->get_balance_sheet_data($filters);
                $data["interest_on_current"] = $this->general_ledger_model->get_interest_on_current($filters);
                break;
        }

        return $data;
    }

    public function print_voucher($voucher_id)
    {
        $voucher_query = $this->db->get_where('c19_accounting_vouchers', ['id' => $voucher_id]);

        if ($voucher_query->num_rows() == 0) {
            show_error('Voucher no encontrado con ID: ' . $voucher_id);
        }

        $data['voucher_info'] = $voucher_query->row();

        $data['voucher_info']->added_by_name = 'Sistema';
        if (!empty($data['voucher_info']->added_by)) {
            $this->db->select('p.first_name, p.last_name');
            $this->db->from('c19_employees e');
            $this->db->join('c19_people p', 'p.person_id = e.person_id');
            $this->db->where('e.person_id', $data['voucher_info']->added_by);
            $employee_query = $this->db->get();

            if ($employee_query->num_rows() > 0) {
                $employee = $employee_query->row();
                $data['voucher_info']->added_by_name = trim($employee->first_name . ' ' . $employee->last_name);
            }
        }

        $this->db->select('t.*, a.code_number, a.account_name, a.account_type');
        $this->db->from('c19_accounting_transactions t');
        $this->db->join('c19_accounting_accounts a', 'a.id = t.account_id');
        $this->db->where('t.voucher_id', $voucher_id);
        $this->db->order_by('t.transaction_order', 'asc');
        $transactions_query = $this->db->get();

        $data['transactions'] = $transactions_query->result();

        $html = $this->load->view('pdf/voucher_pdf', $data, true);

        $reportsDir = FCPATH . "downloads/reports/";
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }

        $timestamp = date("ymdHis");
        $pdfFilePath = $reportsDir . "voucher_{$voucher_id}_{$timestamp}.pdf";

        $this->load->library('pdf');
        $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');

        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));

        $pdf->WriteHTML($html);
        $pdf->Output($pdfFilePath, 'F');

        redirect(base_url("downloads/reports/" . basename($pdfFilePath)));
    }
}
?>