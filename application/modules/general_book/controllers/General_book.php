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
        $this->load->view('general_book/general_book_view', $data);
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
        
        // Obtener transacciones para el libro mayor
        $result = $this->general_book_model->get_general_book_data($filters);
        $data["transactions"] = $result['transactions'];
        $data["total_debit"] = $result['total_debit'];
        $data["total_credit"] = $result['total_credit'];
        $data["totals"] = $this->general_book_model->get_totals($filters);
        $data["date_from"] = $this->input->post('date_from');
        $data["date_to"] = $this->input->post('date_to');
        
        $this->load->view('general_book/general_book_report', $data);
    }


    // Métodos requeridos por la interfaz iData_controller
    function search()
    {
        
    }

    function suggest()
    {
        
    }

    function get_row()
    {
        
    }

    function delete()
    {
        
    }

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
}
?>