<?php

require_once ("Secure_area.php");
require_once ("interfaces/idata_controller.php");

class Overdues extends Secure_area implements iData_controller {

    function __construct()
    {
        parent::__construct('overdues');
    }

    function index()
    {
        $data['controller_name'] = strtolower(get_class());
        $data['form_width'] = $this->get_form_width();

        $res = $this->Employee->getLowerLevels();
        $data['staffs'] = $res;

        $this->load->library('DataTableLib');

        $data["customers"] = $this->get_customers();

        // ✅ MODO MORAS: esto hace que el AJAX mande dt_mode=moras
        $this->set_dt_transactions($this->datatablelib->datatable(), 'moras');

        $data["tbl_loan_transactions"] = $this->datatablelib->render();
        $this->load->view('loans/overdue_list', $data);
    }

    private function get_customers()
    {
        $sql = "SELECT  a.person_id,
                        b.first_name,
                        b.last_name
                FROM c19_customers a
                LEFT JOIN c19_people b ON b.person_id = a.person_id
                WHERE a.deleted = 0
                ORDER BY b.first_name";

        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0)
        {
            return $query->result();
        }

        return [];
    }

    // ✅ Ahora acepta modo
    function set_dt_transactions($datatable, $dt_mode = 'moras')
    {
        $datatable->add_server_params('', '', [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(),
            "ajax_type" => 3,
            "dt_mode"   => $dt_mode, // ✅ CLAVE: el backend lo leerá en _dt_transactions()
        ]);

        $datatable->ajax_url = site_url('loans/ajax');
        $datatable->add_table_definition(["scrollX" => true]);
        $datatable->add_table_definition(["autoWidth" => false]);

        $datatable->add_table_definition([
        "columnDefs" => [
            ["targets" => 0, "width" => "40px",  "orderable" => false],  // acciones
            ["targets" => 1, "width" => "80px"],                         // Trans ID
            ["targets" => 2, "width" => "240px"],                        // Cliente
            ["targets" => 3, "width" => "120px"],                        // Teléfono
            ["targets" => 4, "width" => "220px"],                        // Descripción
            ["targets" => 5, "width" => "120px", "className" => "text-right"], // Ingresos
            ["targets" => 6, "width" => "120px", "className" => "text-right"], // Saldo
            ["targets" => 9, "width" => "120px"],                        // Fecha Aprob
            ["targets" => 10, "width" => "120px"],                       // Sig fecha
            ["targets" => 11, "width" => "140px", "className" => "text-right"], // Monto cuota
            ["targets" => 12, "width" => "110px"],                       // Estado
        ]
        ]);

        // =========================
        // Columnas para OVERDUES (moras)
        // Deben coincidir con tu <thead> de overdue_list
        // =========================
        $datatable->add_column('actions', false);
        $datatable->add_column('id', false);
        $datatable->add_column('customer', false);
        $datatable->add_column('customer_phone', false);
        $datatable->add_column('description', false);

        // ✅ Proceeds (Los ingresos / Ganancias netas)
        $datatable->add_column('net_proceeds', false);

        // ✅ Balance (Saldo)
        $datatable->add_column('loan_balance', false);

        $datatable->add_column('agent', false);
        $datatable->add_column('approved_by', false);
        $datatable->add_column('formatted_loan_approved_date', false);

        // ✅ Next Payment Date
        $datatable->add_column('formatted_payment_date', false);

        // ✅ Monto Siguiente Cuota (NUEVO)
        $datatable->add_column('installment_amount', false);

        $datatable->add_column('loan_status', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        $datatable->callbacks["footerCallback"] = "loansFooter";

        $datatable->table_id = "#tbl_loans_transactions";
        $datatable->add_titles('Overdues');
        $datatable->has_edit_dblclick = 0;
    }

    function search() {}
    function suggest() {}
    function get_row() {}

    function delete()
    {
        $payments_to_delete = $this->input->post('ids');

        if ($this->Payment->delete_list($payments_to_delete))
        {
            echo json_encode([
                'success' => true,
                'message' => $this->lang->line('loans_successful_deleted') . ' ' .
                    count($payments_to_delete) . ' ' . $this->lang->line('payments_one_or_multiple')
            ]);
        }
        else
        {
            echo json_encode([
                'success' => false,
                'message' => $this->lang->line('payments_cannot_be_deleted')
            ]);
        }
    }

    function get_form_width()
    {
        return 360;
    }

    public function save($data_item_id = -1) {}
    public function view($data_item_id = -1) {}
}

?>
