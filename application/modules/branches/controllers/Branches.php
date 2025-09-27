<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Branches extends Secure_area implements iData_controller
{

    function __construct()
    {
        parent::__construct('branches');
        
        $this->load->model("branch_model");
    }

    function index()
    {
        $this->load->library('DataTableLib');

        $this->set_dt_branches($this->datatablelib->datatable());
        $data["tbl_branches"] = $this->datatablelib->render();
        $this->load->view('branches/list', $data);
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch($type)
        {
            case 1:
                $this->_dt_branches();
                break;
            case 3: // Load loan product info to calculator
                $this->_handle_branch_info();
                break;
            case 4: // Delete loan product
                $this->_handle_delete_branch();
                break;            
        }
    }
    
    private function _handle_delete_branch()
    {
        $id = $this->input->post("id");
        
        $this->db->where("id", $id);
        $this->db->delete("branches");
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_branch_info()
    {
        $id = $this->input->post("id");
        
        $branch_info = $this->branch_model->get_details($id);
        
        $tmp = json_decode($branch_info->grace_periods, TRUE);
        
        $data["grace_periods"] = is_array($tmp) ? $tmp : [];
        $branch_info->grace_periods_html = $this->load->view("branches/widgets/grace_periods_row", $data, 1);
        
        $return["status"] = "OK";
        $return["branch_info"] = $branch_info;
        
        send($return);
    }

    function set_dt_branches($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "type" => 1]);
        $datatable->ajax_url = site_url('branches/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('branch_name', false);
        $datatable->add_column('branch_phone', false);
        $datatable->add_column('branch_address', false);
        $datatable->add_column('descriptions', false);
        
        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->dt_height = '350px';

        $datatable->table_id = "#tbl_branches";
        $datatable->add_titles('Branches');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_branches()
    {
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);

        $branches = $this->branch_model->get_list($limit, $offset, $keywords, $order, $count_all);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);

        $tmp = array();

        foreach ($branches->result() as $row)
        {
            $actions = "<a href='" . site_url('branches/view/' . $row->id) . "' class='btn btn-xs btn-default btn-secondary' title='Ver'><span class='fa fa-eye'></span></a> ";
            
            if ( check_access($user_info->role_id, "branches", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn btn-xs btn-danger btn-delete' data-id='" . $row->id . "' title='Borrar'><span class='fa fa-trash'></span></a>";
            }
            
            $data_row = [];
            $data_row["DT_RowId"] = $row->id;
            $data_row["actions"] = $actions;
            $data_row["branch_name"] = ucwords($row->branch_name);
            $data_row["branch_phone"] = ucwords($row->branch_phone);
            $data_row["branch_address"] = ucwords($row->branch_address);
            $data_row["descriptions"] = truncate_html($row->descriptions, 250);
            
            
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
        $branch_info = $this->branch_model->get_details($id);
        
        $data = [];        
        $data["branch_info"] = $branch_info;
        $this->load->view("branches/form", $data);
    }
    
   
    function save($id = -1)
    {
        $branch_name = $this->input->post("branch_name");
        $branch_phone = $this->input->post("branch_phone");
        $branch_address = $this->input->post("branch_address");
        $descriptions = $this->input->post("descriptions");
        
        if ( trim($branch_name) == '' ) 
        {
            $return["message"] = "El nombre de sucursal es un campo requerido";
            send($return);
        }
            
        $data["branch_name"] = $branch_name;
        $data["branch_phone"] = $branch_phone;
        $data["branch_address"] = $branch_address;
        $data["descriptions"] = $descriptions;
        
        if ( $id < 0 )
        {
            $data["created_date"] = time();
            $data["created_by"] = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        else
        {
            $data["modified_date"] = time();
            $data["modified_by"] = $this->Employee->get_logged_in_employee_info()->person_id;            
        }
        
        $id = $this->branch_model->save($id, $data);
        
        $return["status"] = "OK";
        $return["url"] = site_url('branches/view/' . $id);
        
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