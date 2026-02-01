<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class General_book extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('general_book');
        $this->load->model("general_book_model");
    }

    function index()
    {
        $data['page_title'] = 'Libro Mayor';
        $data['accounts'] = $this->general_book_model->get_accounts();
        $data['branches'] = $this->general_book_model->get_branches();
        $this->load->view('general_book/general_book_view', $data);
    }
    
    function generate()
    {
        $filters = $this->get_filters_from_request();
        
        $data = [];
        
        $result = $this->general_book_model->get_general_book_data($filters);
        $data["transactions"] = $result['transactions'];
        $data["total_debit"] = $result['total_debit'];
        $data["total_credit"] = $result['total_credit'];
        $data["totals"] = $this->general_book_model->get_totals($filters);
        $data["date_from"] = $this->input->post('date_from');
        $data["date_to"] = $this->input->post('date_to');
        $data["selected_account_id"] = $this->input->post('account_id');
        $data["selected_account_info"] = $this->get_account_info($this->input->post('account_id'));
        $data["selected_branch_id"] = $this->input->post('branch_id');
        $data["selected_branch_info"] = $this->get_branch_info($this->input->post('branch_id'));
        
        $this->load->view('general_book/general_book_report', $data);
    }
    
    function print_pdf()
    {
        $filters = $this->get_filters_from_request();
        $result = $this->general_book_model->get_general_book_data($filters);
        $totals = $this->general_book_model->get_totals($filters);
        
        $data = [
            'transactions' => $result['transactions'],
            'total_debit' => $result['total_debit'],
            'total_credit' => $result['total_credit'],
            'totals' => $totals,
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'selected_account_info' => $this->get_account_info($this->input->get('account_id')),
            'selected_branch_info' => $this->get_branch_info($this->input->get('branch_id'))
        ];
        
        $this->generate_mpdf($data);
    }
    
    function export_csv()
    {
        $filters = $this->get_filters_from_request();
        
        $result = $this->general_book_model->get_general_book_data($filters);
        
        $account_id = $this->input->get('account_id');
        $selected_account_info = $this->get_account_info($account_id);

        $branch_id = $this->input->get('branch_id');
        $selected_branch_info = $this->get_branch_info($branch_id);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="libro_mayor_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputs($output, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        fputcsv($output, ['CREDISURGIR S.R.L.'], ';');
        fputcsv($output, ['NIT: 485672023'], ';');
        fputcsv($output, [''], ';');
        fputcsv($output, ['LIBRO MAYOR'], ';');
        $periodo = 'Del ' . $this->input->get('date_from') . ' al ' . $this->input->get('date_to');
        fputcsv($output, [$periodo], ';');
        
        if(!empty($selected_account_info)) {
            $cuenta_info = 'Cuenta: ' . $selected_account_info->code_number . ' - ' . $selected_account_info->account_name;
            fputcsv($output, [$cuenta_info], ';');
        } else {
            fputcsv($output, ['Todas las cuentas'], ';');
        }
        
        if(!empty($selected_branch_info)) {
            $sucursal_info = 'Sucursal: ' . $selected_branch_info->branch_name;
            fputcsv($output, [$sucursal_info], ';');
        } else {
            fputcsv($output, ['Todas las sucursales'], ';');
        }

        fputcsv($output, [''], ';');
        
        $headers = ['N°', 'N° Transacción', 'N° Voucher', 'Fecha', 'Razón Social', 'Glosa', 'Debe', 'Haber'];
        fputcsv($output, $headers, ';');
        
        $counter = 1;
        foreach ($result['transactions'] as $transaction) {
            $row = [
                $counter++,
                $transaction->transaction_id,
                $transaction->voucher_id,
                date('d/m/Y', strtotime($transaction->added_date)),
                'CREDISURGIR S.R.L.',
                $transaction->description ?: 'Sin descripción',
                $transaction->movement_type == 'debit' ? number_format($transaction->amount, 2, '.', '') : '0.00',
                $transaction->movement_type == 'credit' ? number_format($transaction->amount, 2, '.', '') : '0.00'
            ];
            fputcsv($output, $row, ';');
        }
        
        $total_row = [
            '', '', '', '', '', 'TOTALES:',
            number_format($result['total_debit'], 2, '.', ''),
            number_format($result['total_credit'], 2, '.', '')
        ];
        fputcsv($output, $total_row, ';');
        
        fputcsv($output, [''], ';');
        fputcsv($output, [''], ';');
        fputcsv($output, ['______________________', '', '', '', '', '____________________'], ';');
        fputcsv($output, ['', '', '', '', '', ''], ';');
        fputcsv($output, ['Contador', '', '', '', '', 'Gerente General'], ';');
        fclose($output);
        exit;
    }
    
    private function get_filters_from_request()
    {
        $filters = [];
        
        $date_from = $this->input->post('date_from') ?: $this->input->get('date_from');
        $date_to = $this->input->post('date_to') ?: $this->input->get('date_to');
        $account_id = $this->input->post('account_id') ?: $this->input->get('account_id');
        $branch_id = $this->input->post('branch_id') ?: $this->input->get('branch_id');
        
        if ($date_from) {
            $filters["date_from"] = $this->config->item('date_format') == 'd/m/Y' 
                ? strtotime(uk_to_isodate($date_from)) 
                : strtotime($date_from);
        }
        
        if ($date_to) {
            $filters["date_to"] = $this->config->item('date_format') == 'd/m/Y' 
                ? strtotime(uk_to_isodate($date_to)) 
                : strtotime($date_to);
        }
        
        if (!empty($account_id)) {
            $filters["account_id"] = $account_id;
        }

        if (!empty($branch_id)) {
            $filters["branch_id"] = $branch_id;
        }
        
        return $filters;
    }
    
    private function generate_mpdf($data)
    {
        $reportsDir = FCPATH . "downloads/reports/";
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0777, true);
        }
        
        $html = $this->generate_pdf_html($data);
        
        $timestamp = date("ymdHis");
        $pdfFilePath = $reportsDir . "libro_mayor_{$timestamp}.pdf";

        $this->load->library('pdf');
        $pdf = $this->pdf->load('"en-GB-x","A4-P","","",10,10,10,10,6,3');

        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html);
        $pdf->Output($pdfFilePath, 'F');

        redirect(base_url("downloads/reports/" . basename($pdfFilePath)));
    }
    
    private function generate_pdf_html($data)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Libro Mayor</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
                .header { margin-bottom: 20px; }
                .company-info { float: left; width: 50%; }
                .company-info h3 { margin: 0; padding: 0; font-size: 14px; }
                .company-info p { margin: 2px 0; }
                .report-title { text-align: center; clear: both; padding-top: 10px; }
                .report-title h2 { margin: 0; padding: 0; }
                .report-title p { margin: 5px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                table th { background-color: #f8f9fa; font-weight: bold; padding: 8px; border: 1px solid #ddd; text-align: center; }
                table td { padding: 6px; border: 1px solid #ddd; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .total-row { background-color: #f8f9fa; font-weight: bold; }
                .no-data { text-align: center; padding: 20px; }
                .signatures { margin-top: 40px; width: 100%; }
                .signature-left { float: left; width: 40%; text-align: center; }
                .signature-right { float: right; width: 40%; text-align: center; }
                .signature-line { border-top: 1px solid #000; margin-top: 60px; padding-top: 5px; }
                .clear { clear: both; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-info">
                    <h3>CREDISURGIR S.R.L.</h3>
                    <p>NIT: 485672023</p>
                </div>
                <div class="clear"></div>
                
                <div class="report-title">
                    <h2>LIBRO MAYOR</h2>
                    <p><strong>Del <?= $data['date_from'] ?> al <?= $data['date_to'] ?></strong></p>
                    <?php if(!empty($data['selected_account_info'])): ?>
                        <p><strong>Cuenta: <?= $data['selected_account_info']->code_number ?> - <?= $data['selected_account_info']->account_name ?></strong></p>
                    <?php else: ?>
                        <p><strong>Todas las cuentas</strong></p>
                    <?php endif; ?>
                    <?php if(!empty($data['selected_branch_info'])): ?>
                        <p><strong>Sucursal: <?= $data['selected_branch_info']->branch_name ?></strong></p>
                    <?php else: ?>
                        <p><strong>Todas las sucursales</strong></p>
                    <?php endif; ?> 
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="9%" class="text-center">N°</th>
                        <th width="9%" class="text-center">N° Transacción</th>
                        <th width="8%" class="text-center">N° Voucher</th>
                        <th width="10%" class="text-center">Fecha</th>
                        <th width="40%" class="text-center">Glosa</th>
                        <th width="12%" class="text-center">Debe</th>
                        <th width="12%" class="text-center">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(!empty($data['transactions'])) {
                        $counter = 1;
                        foreach($data['transactions'] as $transaction): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $counter ?></td>
                            <td class="text-center"><?= $transaction->transaction_id ?></td>
                            <td class="text-center"><?= $transaction->voucher_id ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($transaction->added_date)) ?></td>
                            <td class="text-left"><?= $transaction->description ?: 'Sin descripción' ?></td>
                            <td class="text-right"><?= $transaction->movement_type == 'debit' ? number_format($transaction->amount, 2) : '0.00' ?></td>
                            <td class="text-right"><?= $transaction->movement_type == 'credit' ? number_format($transaction->amount, 2) : '0.00' ?></td>
                        </tr>
                    <?php 
                            $counter++;
                        endforeach; 
                    } else {
                        echo '<tr><td colspan="7" class="no-data">No se encontraron transacciones para el período seleccionado</td></tr>';
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" class="text-right"><strong>TOTALES:</strong></td>
                        <td class="text-right"><strong><?= number_format($data['total_debit'], 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($data['total_credit'], 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="signatures">
                <div class="signature-left">
                    <div class="signature-line">Contador</div>
                </div>
                <div class="signature-right">
                    <div class="signature-line">Gerente General</div>
                </div>
                <div class="clear"></div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function get_account_info($account_id)
    {
        if (empty($account_id)) {
            return null;
        }
        
        $this->db->select('code_number, account_name');
        $this->db->from('c19_accounting_accounts');
        $this->db->where('id', $account_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        
        return null;
    }

    private function get_branch_info($branch_id)
    {
        if (empty($branch_id)) {
            return null;
        }
        
        $this->db->select('branch_name');
        $this->db->from('c19_branches');
        $this->db->where('id', $branch_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        
        return null;
    }

    function search() {}
    function suggest() {}
    function get_row() {}
    function delete() {}
    function get_form_width() { return 360; }
    public function save($id = -1) {}
    public function view($id = -1) {}
}
?>