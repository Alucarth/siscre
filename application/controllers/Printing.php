<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once ("Secure_area.php");
require_once ("interfaces/idata_controller.php");

class Printing extends CI_Controller {

    function __construct()
    {
        parent::__construct('overdues');
    }

    public function print_list($filename = '')
    {
        ini_set('memory_limit', '-1');

        $title = $this->input->post("title");
        $html_title = $this->input->post("html_title");
        
        $html = '<div style="width:100%;text-align:left;padding-bottom:10px">
                    <table style="width:100%">
                        <tr>
                            <!--<td style="width:12%">-->
                            <td>
                                <img id="img-pic" style="max-height:80px; width:100%" src="'. ((trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png")) .'" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <!--
                                ' . ucwords($this->config->item('company')) . ' <br/>
                                ' . ucwords($this->config->item('address')) . ' <br/>
                                ' . $this->config->item('phone') . ' <br/>
                            -->
                            </td>
                            <td style="text-align:right">
                            <h1>' . $title . '</h1>
                            </td>
                        </tr>
                    </table>                    
                </div>';
        
        if ( $html_title != '' )
        {
            $html = '<div style="width:100%;text-align:left;padding-bottom:10px">
                        <table style="width:100%">
                            <tr>
                                <td style="width:12%">
                                    <img id="img-pic" style="max-height:80px; width:100%" src="'. ((trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png")) .'" />
                                </td>
                                <td>
                                <!--
                                    ' . ucwords($this->config->item('company')) . ' <br/>
                                    ' . ucwords($this->config->item('address')) . ' <br/>
                                    ' . $this->config->item('phone') . ' <br/>
                                -->
                                </td>
                                <td style="text-align:right">
                                ' . $html_title . '
                                </td>
                            </tr>
                        </table>                    
                    </div>';
        }
        
        $html .= $this->input->post("html");

        $pdfFilePath = FCPATH . "/downloads/reports/" . $filename;

        if (file_exists($pdfFilePath))
        {
            @unlink($pdfFilePath);
        }

        $this->load->library('pdf');

        $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can
        
        $return["status"] = "OK";
        $return["url"] = base_url("downloads/reports/" . $filename);

        send($return);
    }
    
    //tarea 3
    public function export_all_receivables_pdf()
    {
        // Consulta mejorada con validaciones
        $query = $this->db->query("
            SELECT 
                l.loan_id,
                CONCAT(pc.first_name, ' ', pc.last_name) AS customer,
                pc.phone_number AS customer_phone,
                l.description,
                l.loan_amount,
                l.loan_balance,
                CONCAT(pa.first_name, ' ', pa.last_name) AS agent,
                CONCAT(pap.first_name, ' ', pap.last_name) AS approved_by,
                FROM_UNIXTIME(l.loan_approved_date, '%d/%m/%Y') AS formatted_loan_approved_date,
                FROM_UNIXTIME(l.loan_payment_date, '%d/%m/%Y') AS formatted_payment_date,
                l.loan_status
            FROM c19_loans l
            LEFT JOIN c19_customers c ON l.customer_id = c.person_id
            LEFT JOIN c19_people pc ON c.person_id = pc.person_id
            LEFT JOIN c19_employees a ON l.loan_agent_id = a.person_id
            LEFT JOIN c19_people pa ON a.person_id = pa.person_id
            LEFT JOIN c19_employees ap ON l.loan_approved_by_id = ap.person_id
            LEFT JOIN c19_people pap ON ap.person_id = pap.person_id
            WHERE l.loan_status = 'approved'
            AND l.loan_payment_date IS NOT NULL
            AND MONTH(FROM_UNIXTIME(l.loan_payment_date)) = MONTH(CURDATE())
            AND YEAR(FROM_UNIXTIME(l.loan_payment_date)) = YEAR(CURDATE())
            ORDER BY l.loan_id DESC
        ");
        
        $result = $query->result_array();

        // Verificar si hay resultados
        if (empty($result)) {
            // Crear un PDF vacío con mensaje
            $html = '<h2>Reporte de Préstamos (Aprobados, Mes Actual)</h2>
                    <p>No se encontraron registros para el mes actual</p>';
        } else {
            $data = [
                'titulo' => 'Reporte de Préstamos (Aprobados, Mes Actual)',
                'fecha' => date('d/m/Y H:i:s'),
                'registros' => $result
            ];

            // Cargar la vista - asegúrate de que la ruta views/pdf/reporte_cuentas_pdf.php existe
            $html = $this->load->view('pdf/reporte_cuentas_pdf', $data, TRUE);
        }

        // Usar la misma librería PDF que el resto de la aplicación
        $pdfFilePath = FCPATH . "/downloads/reports/reporte_prestamos_".date('Ymd_His').".pdf";

        $this->load->library('pdf');
        $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html);
        
        // Salida directa al navegador
        $pdf->Output('reporte_prestamos.pdf', 'I');
        
        // Detener la ejecución para evitar conflicto con CI
        exit;
    }
    //

    public function payment_list($filename = '')
    {
        ini_set('memory_limit', '-1');
        $this->load->model("Payment");
        
        $title = $this->input->post("title");
        $html_title = $this->input->post("html_title");
        
        $html = '<div style="width:100%;text-align:left;padding-bottom:10px">
                    <table style="width:100%">
                        <tr>
                            <td style="width:12%">
                                <img id="img-pic" style="max-height:80px; width:100%" src="'. ((trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png")) .'" />
                            </td>
                            <td>
                            <!--
                                ' . ucwords($this->config->item('company')) . ' <br/>
                                ' . ucwords($this->config->item('address')) . ' <br/>
                                ' . $this->config->item('phone') . ' <br/>
                            -->
                            </td>
                            <td style="text-align:right">
                            <h1>' . $title . '</h1>
                            </td>
                        </tr>
                    </table>                    
                </div>';
        
        if ( $html_title != '' )
        {
            $html = '<div style="width:100%;text-align:left;padding-bottom:10px">
                        <table style="width:100%">
                            <tr>
                                <td style="width:12%">
                                    <img id="img-pic" style="max-height:80px; width:100%" src="'. ((trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png")) .'" />
                                </td>
                                <td>
                                <!--
                                    ' . ucwords($this->config->item('company')) . ' <br/>
                                    ' . ucwords($this->config->item('address')) . ' <br/>
                                    ' . $this->config->item('phone') . ' <br/>
                                -->
                                </td>
                                <td style="text-align:right">
                                ' . $html_title . '
                                </td>
                            </tr>
                        </table>                    
                    </div>';
        }
        
        $html .= $this->Payment->get_payment_list();

        $pdfFilePath = FCPATH . "/downloads/reports/" . $filename;

        if (file_exists($pdfFilePath))
        {
            @unlink($pdfFilePath);
        }

        $this->load->library('pdf');

        $pdf = $this->pdf->load('"en-GB-x","A4-L","","",10,10,10,10,6,3');
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can
        
        $return["status"] = "OK";
        $return["url"] = base_url("downloads/reports/" . $filename);

        send($return);
    }

    public function delete()
    {
        
    }

    public function get_form_width()
    {
        
    }

    public function get_row()
    {
        
    }

    public function index()
    {
        
    }

    public function save($data_item_id = -1)
    {
        
    }

    public function search()
    {
        
    }

    public function suggest()
    {
        
    }

    public function view($data_item_id = -1)
    {
        
    }

}
