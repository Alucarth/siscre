<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Loan_products extends Secure_area implements iData_controller
{

    function __construct()
    {
        parent::__construct('loan_products');
        
        $this->load->model("loan_product_model");
    }

    function index()
    {
        $this->load->library('DataTableLib');

        $this->set_dt_loan_products($this->datatablelib->datatable());
        $data["tbl_loan_products"] = $this->datatablelib->render();
        $this->load->view('loan_products/list', $data);
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch($type)
        {
            case 1:
                $this->_dt_loan_products();
                break;
            case 2: // Load loan product options
                $this->_handle_loan_product_options();
                break;
            case 3: // Load loan product info to calculator
                $this->_handle_loan_product_info();
                break;
            case 4: // Delete loan product
                $this->_handle_delete_loan_product();
                break;
            case 5: // Save grace period
                $this->_handle_save_grace_period();
                break;
        }
    }
    
    private function _handle_delete_loan_product()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $this->db->delete("loan_products");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_loan_product_info()
    {
        $id = $this->input->post("id");
        
        $loan_product_info = $this->loan_product_model->get_details($id);
        
        $tmp = json_decode($loan_product_info->grace_periods, TRUE);
        
        $data["grace_periods"] = is_array($tmp) ? $tmp : [];
        $loan_product_info->grace_periods_html = $this->load->view("loan_products/widgets/grace_periods_row", $data, 1);
        
        $return["status"] = "OK";
        $return["loan_product_info"] = $loan_product_info;
        
        send($return);
    }
    
    private function _handle_loan_product_options()
    {
        $loan_products = $this->loan_product_model->get_list();
        $val = $this->input->post("val");
        
        $options = '<option>Elegir</option>';
        if ( $loan_products )
        {
            foreach($loan_products->result() as $row)
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

    function set_dt_loan_products($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1]);
        $datatable->ajax_url = site_url('loan_products/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('product_name', false);
        $datatable->add_column('description', false);
        $datatable->add_column('interest_rate', false);
        $datatable->add_column('interest_type', false);
        $datatable->add_column('term', false);
        $datatable->add_column('term_period', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';

        $datatable->table_id = "#tbl_loan_products";
        $datatable->add_titles('Loan products');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_loan_products()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $loan_products = $this->loan_product_model->get_list($limit, $offset, $keywords, $order, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($loan_products->result() as $row)
        {
            $actions = "<a href='" . site_url('loan_products/view/' . $row->id) . "' class='btn btn-xs btn-default btn-secondary' title='Ver'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "loan_products", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Eliminar'><span class='fa fa-trash'></span></a>";
            }
            
            $term_period = "";
            switch( $row->term_period )
            {
                case "day":
                    $term_period = "Día";
                    break;
                case "week":
                    $term_period = "Semana";
                    break;
                case "month":
                    $term_period = "Mes";
                    break;
                case "biweekly":
                    $term_period = "Mes (Quincenal)";
                    break;
                case "month_weekly":
                    $term_period = "Mes (Semanal)";
                    break;
                case "year":
                    $term_period = "Año";
                    break;
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["product_name"] = ucwords($row->product_name);
            $data_row["description"] = truncate_html($row->description, 250);
            $data_row["interest_rate"] = $row->interest_rate . "%";
            $data_row["interest_type"] = ucwords(str_replace("_", " ", $row->interest_type));
            $data_row["term"] = $row->term;
            $data_row["term_period"] = $term_period;

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

    function view($id = -1)
    {
        $loan_product_info = $this->loan_product_model->get_details($id);
        
        $data = [];        
        $data["loan_product_info"] = $loan_product_info;
        $data["interest_types"] = $this->Loan->get_loan_interest_types();
        
        $grace_periods = is_array(json_decode($loan_product_info->grace_periods, TRUE)) ? json_decode($loan_product_info->grace_periods, TRUE) : [];
        $data["grace_periods"] = $grace_periods;
        
        $this->load->view("loan_products/form", $data);
    }
    
    private function _handle_save_grace_period()
    {
        $loan_product_id = $this->input->post("loan_product_id");
        
        $period = $this->input->post("period");
        $qty = $this->input->post("qty");
        $unit = $this->input->post("unit");
        
        $tmp = [];
        $i = 0;
        foreach ( $period as $key => $value )
        {
            $tmp[$value] = ["period" => $value, "qty" => $qty[$i], "unit" => $unit[$i]];
            $i++;
        }

        $grace_periods = json_encode($tmp);
            
        if ( $loan_product_id > 0 )
        {
            $this->db->where("id", $loan_product_id);
            $this->db->update("loan_products", ["grace_periods" => $grace_periods]);
            $return["ret_grace_periods"] = '';
        }
        else
        {
            $return["ret_grace_periods"] = $grace_periods;
        }
        
        $return["status"] = "OK";
        send($return);
    }
   
    function save($id = -1)
    {
        $product_name = $this->input->post("product_name");
        $description = $this->input->post("description");
        $interest_rate = $this->input->post("interest_rate");
        $interest_type = $this->input->post("interest_type");
        $term = $this->input->post("term");
        $term_period = $this->input->post("term_period");
        $penalty_value = $this->input->post("penalty_value");
        $penalty_type = $this->input->post("penalty_type");
        
        if ( trim($product_name) == '' ) 
        {
            $return["message"] = "El nombre del producto de préstamo es un campo obligatorio";
            send($return);
        }
        
        if ( trim($interest_rate) == '' ) 
        {
            $return["message"] = "La tasa de interés es un campo obligatorio";
            send($return);
        }
        
        if ( trim($term) == '' ) 
        {
            $return["message"] = "El término es un campo obligatorio";
            send($return);
        }
            
        $data["product_name"] = $product_name;
        $data["description"] = $description;
        $data["interest_rate"] = $interest_rate;
        $data["interest_type"] = $interest_type;
        $data["term"] = $term;
        $data["term_period"] = $term_period;
        $data["penalty_value"] = $penalty_value;
        $data["penalty_type"] = $penalty_type;

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
        
        if ( $this->input->post("grace_periods_json") != '' )
        {
            $data['grace_periods'] = $this->input->post("grace_periods_json");
        }

        $id = $this->loan_product_model->save($id, $data);
        
        $return["status"] = "OK";
        $return["url"] = site_url('loan_products/view/' . $id);
        
        send($return);
    }

    function delete()
    {
        $loans_to_delete = $this->input->post('ids');

        if ($this->Loan->delete_list($loans_to_delete))
        {
            echo json_encode(array('success' => true, 'message' => $this->lang->line('loans_successful_deleted') . ' ' .
                count($loans_to_delete) . ' ' . $this->lang->line('loans_one_or_multiple')));
        }
        else
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('loans_cannot_be_deleted')));
        }
    }

    /*
      get the width for the add/edit form
     */

    function get_form_width()
    {
        return 360;
    }
}

?>