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
    
        public function get_last_accounts() {
            $this->load->model('Loans_model');

            $data = $this->Loans_model->get_last_50_accounts();

            if ($data) {
                echo json_encode([
                    "status" => "OK",
                    "data" => $data,
                    "total_proceeds" => array_sum(array_column($data, 'amount')),
                    "total_balance" => array_sum(array_column($data, 'balance'))
                ]);
            } else {
                echo json_encode(["status" => "ERROR"]);
            }
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
    // 1. Consulta SQL simple para prueba
    $query = $this->db->query("SELECT * FROM c19_branches");
    $result = $query->result_array();

    // 2. Preparar datos para la vista
    $data = [
        'titulo' => 'Reporte de Sucursales (Prueba)',
        'fecha' => date('d/m/Y H:i:s'),
        'registros' => $result
    ];

    // 3. Cargar vista como HTML
    $html = $this->load->view('pdf/reporte_cuentas_pdf', $data, TRUE);

    // 4. Generar PDF con tu librería actual
    $this->load->library('pdf');
    $mpdf = $this->pdf->load('"en-GB-x","A4","","",10,10,10,10,6,3,"L"');
    
    // Configuración adicional
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->WriteHTML($html);
    
    // Salida del PDF
    $filename = 'reporte_sucursales_'.date('Ymd_His').'.pdf';
    $mpdf->Output($filename, 'D'); // 'D' para descarga forzada
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
