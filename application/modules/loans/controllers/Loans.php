<?php

require_once (APPPATH . "controllers/Secure_area.php");
require_once (APPPATH . "controllers/interfaces/idata_controller.php");

class Loans extends Secure_area implements iData_controller
{
    function __construct()
    {
        parent::__construct('loans', null, ['assets', 'fix_breakdown', 'statement']);
        
        $this->load->library('DataTableLib');
        $this->load->model('Document_model');
    }

    function index()
    {
        $data['controller_name'] = strtolower(get_class());
        $data['form_width'] = $this->get_form_width();

        $data['count_overdues'] = $this->_count_overdues();

        $res = $this->Employee->getLowerLevels();
        $data['staffs'] = $res;

        $this->load->library('DataTableLib');

        $this->set_dt_transactions($this->datatablelib->datatable());
        $data["tbl_loan_transactions"] = $this->datatablelib->render();
        $data["customers"] = $this->get_customers();
        
        if ( is_plugin_active('branches') )
        {
            $this->load->model('branches/Branch_model');
            $data["branches"] = $this->Branch_model->get_branches();
        }
        
        $this->load->view('loans/list', $data);
    }
    
    private function get_customers()
    {
        $sql = "SELECT  a.person_id, 
                        b.first_name, 
                        b.last_name,
                        b.phone_number 
                FROM c19_customers a 
                LEFT JOIN c19_people b ON b.person_id = a.person_id
                WHERE a.deleted = 0
                ORDER BY b.first_name";
        
        $query = $this->db->query( $sql );
        
        if ( $query && $query->num_rows() > 0 )
        {
            return $query->result();
        }
        
        return [];
    }

    function set_dt_transactions($datatable)
    {
        $datatable->add_server_params('', '', [$this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), "ajax_type" => 3]);
        $datatable->ajax_url = site_url('loans/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('id', false);
        $datatable->add_column('customer', false);
        $datatable->add_column('customer_phone', false);//tlefono del cliente
        $datatable->add_column('loan_product', false);
        $datatable->add_column('description', false);
        $datatable->add_column('loan_amount', false);
        $datatable->add_column('net_proceeds', false);
        $datatable->add_column('loan_balance', false);
        $datatable->add_column('agent', false);
        $datatable->add_column('approved_by', false);
        $datatable->add_column('formatted_loan_approved_date', false);
        $datatable->add_column('formatted_payment_date', false);
        $datatable->add_column('loan_status', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        $datatable->callbacks["footerCallback"] = "loansFooter";

        $datatable->table_id = "#tbl_loans_transactions";
        $datatable->add_titles('Loans');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_transactions()
    {
        $selected_user = $this->input->post("employee_id");
        $due_from_date = $this->input->post("due_from_date");
        $due_to_date = $this->input->post("due_to_date");
        $applied_from_date = $this->input->post("applied_from_date");
        $applied_to_date = $this->input->post("applied_to_date");
        $approved_from_date = $this->input->post("approved_from_date");
        $approved_to_date = $this->input->post("approved_to_date");
        $customer_id = $this->input->post("customer_id");
        $status = $this->input->post("status");
        $no_delete = $this->input->post("no_delete");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");
        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];
        $order = array("index" => $index, "direction" => $dir);
        $filters = [
            "customer_id" => $customer_id
        ];
        if ( $due_from_date != '' )
        {
            $filters["due_from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($due_from_date)) : strtotime($due_from_date);
        }
        if( $due_to_date != '' )
        {
            $filters["due_to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($due_to_date) . " 23:00:00") : strtotime($due_to_date . " 23:00:00");
        }
        if ( $applied_from_date != '' )
        {
            $filters["applied_from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($applied_from_date)) : strtotime($applied_from_date);
        }
        if( $applied_to_date != '' )
        {
            $filters["applied_to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($applied_to_date) . " 23:00:00") : strtotime($applied_to_date . " 23:00:00");
        }
        if ( $approved_from_date != '' )
        {
            $filters["approved_from_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($approved_from_date)) : strtotime($approved_from_date);
        }
        if( $approved_to_date != '' )
        {
            $filters["approved_to_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($approved_to_date) . " 23:00:00") : strtotime($approved_to_date . " 23:00:00");
        }
        if (is_plugin_active("branches") )
        {
            $filters["branch_id"] = $this->input->post("branch_id");
        }
        $loans = $this->Loan->get_all($limit, $offset, $keywords, $order, $status, $selected_user, $filters);
        $count_all = $this->Loan->get_all($limit, $offset, $keywords, $order, $status, $selected_user, $filters, 1);
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        $tmp = array();
        $tbl_net_proceeds = 0;
        $tbl_proceeds = 0;
        $tbl_balance = 0;
        foreach ($loans->result() as $loan)
        {
            $loan_status = $loan->loan_status;
            if ($loan->loan_balance <= 0)
            {
                $loan_status = "Paid";
            }
            $actions = "<a href='" . site_url('loans/view/' . $loan->loan_id) . "' class='btn-xs btn-default btn btn-secondary' title='View'><span class='fa fa-eye'></span></a> ";
            if ( !$no_delete )
            {
                if ( check_access($user_info->role_id, "loans", 'delete') )
                {
                    $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-loan-id='" . $loan->loan_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
                }
            }
            $scheds = json_decode($loan->periodic_loan_table);
            $next_payment_date = sync_payment_date( $loan->due_paid, $scheds);
            $fees = json_decode($loan->misc_fees, true);
            $total_fees = 0;
            if ( is_array($fees) && count($fees) > 0 )
            {
                foreach ( $fees as $name => $amount )
                {
		    if ( is_numeric( $amount ) )
		    {
                    	$total_fees += $amount;
		    }
                }
            }
            $net_proceeds = $loan->net_proceeds > 0 ? ($loan->net_proceeds) : ($loan->apply_amount - $total_fees);
            $data_row = [];
            $data_row["DT_RowId"] = $loan->loan_id;
            $data_row["actions"] = $actions;
            $data_row["id"] = $loan->loan_id;
            $data_row["loan_product"] = $loan->loan_type;
            $data_row["description"] = $loan->description;
            $data_row["net_proceeds"] = to_currency($net_proceeds);
            $data_row["loan_amount"] = to_currency($loan->loan_amount);
            $data_row["loan_balance"] = to_currency($loan->loan_balance);
            $data_row["customer"] = $loan->customer_name;
            $data_row["customer_phone"] = $loan->customer_phone;//telefono cliente
            $data_row["agent"] = $loan->agent_name;
            $data_row["approved_by"] = $loan->approver_name;
            $data_row["formatted_loan_approved_date"] = $loan->loan_approved_date > 0 ? date($this->config->item('date_format'), $loan->loan_approved_date) : '';
            $data_row["formatted_payment_date"] = ($next_payment_date > 0) ? date($this->config->item('date_format'), $next_payment_date) : '';
            $data_row["loan_status"] = $loan->loan_balance > 0 ? ktranslate2(ucwords($loan->loan_status)) : ktranslate2('Paid');

            $tbl_net_proceeds += $net_proceeds;
            $tbl_proceeds += $loan->loan_amount;
            $tbl_balance += $loan->loan_balance;
            //se requiere reparar URGENTE
            if($loan->loan_status == 'approved')
            {
                $tmp[] = $data_row;
            }
        }

        $this->session->set_userdata("tbl_net_proceeds", $tbl_net_proceeds);
        $this->session->set_userdata("tbl_proceeds", $tbl_proceeds);
        $this->session->set_userdata("tbl_balance", $tbl_balance);
        
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

    function view($loan_id = -1)
    {
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        
        $data['loan_info'] = $this->Loan->get_info($loan_id);
        $data['guarantee_info'] = $this->Guarantee->get_info($loan_id);
        $data["interest_types"] = $this->Loan->get_loan_interest_types();
        
        if (is_plugin_active("roles"))
        {
            $this->load->model("roles/Role_model");
            $data["role_info"] = $this->Role_model->get_info( $user_info->role_id );
        }
        else
        {
            $this->load->model("role");
            $data["role_info"] = $this->role->get_info( $user_info->role_id );
        }
        
        $loan_types = $this->Loan_type->get_multiple_loan_types();

        $tmp = array("" => $this->lang->line("common_please_select"));
        foreach ($loan_types as $loan_type)
        {
            $tmp[$loan_type->loan_type_id] = $loan_type->name;
        }

        $data['loan_types'] = $tmp;

        $data['misc_fees'] = array(
            array("", "")
        );
        $data['add_fees'] = array(
            array("", "")
        );

        $misc_fees = json_decode($data['loan_info']->misc_fees, true);
        $add_fees = json_decode($data['loan_info']->add_fees, true);

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                $tmp[] = array($fee, $charge);
            endforeach;
            $data['misc_fees'] = $tmp;
        }
        
        if (is_array($add_fees))
        {
            $tmp = array();
            foreach ($add_fees as $fee => $charge):
                $tmp[] = array($fee, $charge);
            endforeach;
            $data['add_fees'] = $tmp;
        }
        
        // payment scheds - start
        $c_payment_sches = [
            "term" => "",
            "term_period" => "",
            "payment_sched" => "",
            "interest_rate" => "",
            "penalty" => "",
            "payment_breakdown" => [
                "schedule" => [],
                "balance" => [],
                "interest" => [],
                "amount" => []
            ]
        ];

        $c_payment_scheds = trim($data["loan_info"]->payment_scheds) !== "" ? json_decode($data["loan_info"]->payment_scheds, TRUE) : $c_payment_sches;
        $data["c_payment_scheds"] = $c_payment_scheds;
        // payment scheds - end
        
        $data["period_cnt"] = count(json_decode($data["loan_info"]->periodic_loan_table, true));
        
        $loan_status = (isset($data['loan_info']->loan_status) && trim($data['loan_info']->loan_status) !== "") ? $this->lang->line("common_" . strtolower($data['loan_info']->loan_status)) : $this->lang->line("common_pending");
        if ($data['loan_info']->loan_balance <= 0 && $loan_id > -1)
        {
            $loan_status = "paid";
        }
        $data['loan_status'] = $loan_status;

        $employees = $this->Employee->get_all()->result();
        $emps = array();
        foreach ($employees as $employee)
        {
            $emps[$employee->person_id] = $employee->first_name . " " . $employee->last_name;
        }

        $data['employees'] = $emps;
        
        $grace_periods = is_array(json_decode($data['loan_info']->grace_periods, TRUE)) ? json_decode($data['loan_info']->grace_periods, TRUE) : [];
        
        $data["grace_periods"] = $grace_periods;
        
        $data["loan_id"] = $loan_id;

        $proof_ids = json_decode($data['guarantee_info']->proof, TRUE);
        $support_doc_ids = json_decode($data['guarantee_info']->images, TRUE);
        
        if ( !is_array($proof_ids) || (is_array($proof_ids) && count($proof_ids) <= 0) )
        {
            $proof_ids[] = -1;
        }
        if ( !is_array($support_doc_ids) || (is_array($support_doc_ids) && count($support_doc_ids) <= 0) )
        {
            $support_doc_ids[] = -1;
        }
        
	if (is_plugin_active("holidays"))
        {
            $exclude_schedules = json_decode($data['loan_info']->exclude_schedules, true);
            $data["exclude_schedules"] = $exclude_schedules;
	}
        
        $filters = ["document_ids" => $proof_ids];
        $data["proofs"] = $this->Document_model->get_list($filters);
        
        $filters = ["document_ids" => $support_doc_ids];
        $data["supporting_docs"] = $this->Document_model->get_list($filters);
        
        $this->set_dt_documents($this->datatablelib->datatable(), $loan_id);
        $data["tbl_documents"] = $this->datatablelib->render();
        
        $this->set_dt_doc_list($this->datatablelib->datatable(), $loan_id);
        $data["tbl_doc_list"] = $this->datatablelib->render();
        
        $this->set_dt_collateral($this->datatablelib->datatable(), $loan_id);
        $data["tbl_collateral"] = $this->datatablelib->render();

        /** Datatable garantes */
        $this->set_dt_garantes($this->datatablelib->datatable(), $loan_id);
        $data["tbl_garante"] = $this->datatablelib->render();
        
        $operative_expenses = 0;
        $loan_info_exp =  $this->Loan->get_info($loan_id);
        if(isset($loan_info_exp->periodic_loan_table))
        {
            $rows = json_decode($loan_info_exp->periodic_loan_table);
            $row = $rows[0];
            //$operative_expenses = ($row-> operating_expenses_amount / $loan_info_exp->apply_amount )*100;
            $operative_expenses = ($row-> operating_expenses_amount);
        }
        $data["operative_expenses"]=$operative_expenses;
        

        $this->load->view("loans/form", $data);
    }
    
    function set_dt_doc_list($datatable, $loan_id)
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "ajax_type" => 7, 
            "loan_id" => $loan_id,
            "doc_viewer" => 1
        ];
        $datatable->add_server_params('', '', $params);
        $datatable->ajax_url = site_url('loans/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('document_name', false);
        $datatable->add_column('descriptions', false);
        $datatable->add_column('modified_date', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#tbl_doc_list";
        $datatable->add_titles('Documents');
        $datatable->has_edit_dblclick = 0;
    }
    
    function set_dt_documents($datatable, $id, $document_type = "loan_document")
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "ajax_type" => 7, 
            "id" => $id,
            "document_type" => $document_type
        ];
        $datatable->add_server_params('', '', $params);
        $datatable->ajax_url = site_url('loans/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('document_name', false);
        $datatable->add_column('descriptions', false);
        $datatable->add_column('modified_date', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = $document_type == 'loan_document' ? "#tbl_documents" : "#tbl_doc_list";
        $datatable->add_titles('Documents');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_documents()
    {
        $this->load->model("Document_model");
        
        $id = $this->input->post("id");
        $document_type = $this->input->post("document_type");
        $doc_viewer = $this->input->post("doc_viewer");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        $user_info = $this->Employee->get_info($user_id);
        
        $tmp = array();
        $count_all = 0;
        
        $filters = [];
        $filters["foreign_id"] = $id;
        $filters["document_type"] = $document_type;
        $filters["order_by"] = 'modified_date DESC';
        $documents = $this->Document_model->get_list($filters, $count_all);

        foreach ($documents as $document)
        {
            if ( $doc_viewer )
            {
                $actions = "<input type='checkbox' value='" . $document->document_id . "' class='_select_doc' />";
            }
            else
            {
                $actions = "<a href='" . base_url($document->document_path) . "' target='_blank' class='btn btn-xs btn-default btn-secondary' title='View'><span class='fa fa-download'></span></a> ";

                if ( check_access($user_info->role_id, "customers", 'delete') )
                {
                    $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete btn' data-document-id='" . $document->document_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
                }
            }

            $data_row = [];
            $data_row["DT_RowId"] = $document->document_id;
            $data_row["actions"] = $actions;
            
            $data_row["document_name"] = $document->document_name;
            $data_row["descriptions"] = $document->descriptions;
            $data_row["modified_date"] = date($this->config->item('date_format') ." H:i:s", strtotime($document->added_date));
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }

    private function _get_files($ids, $file)
    {
        $tmp = array();
        if (is_array($ids))
        {
            foreach ($ids as $id):
                $tmp[] = $file[$id];
            endforeach;
        }

        return $tmp;
    }

    private function _get_formatted_file($id, $filename, $desc)
    {
        $words = array("doc", "docx", "odt");
        $xls = array("xls", "xlsx", "csv");
        $tmp = explode(".", $filename);
        $ext = $tmp[1];

        if (in_array(strtolower($ext), $words))
        {
            $tmp['icon'] = base_url("images/word-filetype.jpg");
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
            $tmp['descriptions'] = $desc;
        }
        else if (strtolower($ext) === "pdf")
        {
            $tmp['icon'] = base_url("images/pdf-filetype.jpg");
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
            $tmp['descriptions'] = $desc;
        }
        else if (in_array(strtolower($ext), $xls))
        {
            $tmp['icon'] = base_url("images/xls-filetype.jpg");
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
            $tmp['descriptions'] = $desc;
        }
        else
        {
            $tmp['icon'] = base_url("images/image-filetype.jpg");
            $tmp['filename'] = $filename;
            $tmp['id'] = $id;
            $tmp['descriptions'] = $desc;
        }

        return $tmp;
    }

    function save($loan_id = -1)
    {
        $fees = $this->input->post("fees");
        $amounts = $this->input->post("amounts");
        
        $add_fees = $this->input->post("add_fees");
        $add_fee_amounts = $this->input->post("add_fee_amounts");
        
        $current_user_id = $this->Employee->get_logged_in_employee_info()->person_id;
        // No permite modificar pr[estamos rechazados]
        $loan_info = $this->Loan->get_info($loan_id);
        if ($loan_id > 0 && strtolower($loan_info->loan_status) === 'reject') {
            echo json_encode(array('success' => false, 'message' => 'No se puede modificar un préstamo rechazado. Cree un nuevo préstamo.'));
            return;
        }
        
        if (is_plugin_active("holidays"))
        {
            $exclude_schedules = [];
            if ( is_array($this->input->post("loan")["exclude_schedule"]) )
            {
                foreach ( $this->input->post("loan")["exclude_schedule"] as $exclude_schedule )
                {
                    $exclude_schedules[] = $exclude_schedule;
                }
            }
        }

        $misc_fees = array();
        $total_fees = 0;
        for ($i = 0; $i < count($fees); $i++)
        {
            $misc_fees[$fees[$i]] = $amounts[$i];
            $total_fees += $amounts[$i];
        }
        
        $add_fees_arr = array();
        $total_add_fees = 0;
        for ($i = 0; $i < count($add_fees); $i++)
        {
            $add_fees_arr[$add_fees[$i]] = $add_fee_amounts[$i];
            $total_add_fees += $add_fee_amounts[$i];
        }

        // payment scheds - start        
        $post_var["InterestType"] = $this->input->post("interest_type");
        $post_var["NoOfPayments"] = $this->input->post("term");
        $post_var["ApplyAmt"] = $this->input->post("apply_amount");
        $post_var["TotIntRate"] = $this->input->post("interest_rate");
        $post_var["InstallmentStarted"] = $this->input->post("start_date");
        $post_var["PayTerm"] = $this->input->post("term_period");
        $post_var["exclude_sundays"] = $this->input->post('exclude_sundays') == 'on' ? 1 : 0;
        $post_var['penalty_value'] = $this->input->post("penalty_value");
        $post_var['penalty_type'] = $this->input->post("penalty_type");  
        $post_var['operating_expenses'] = $this->input->post("operating_expenses");  
        
        if (is_plugin_active("holidays"))
        {
            $post_var["exclude_schedules"] = $exclude_schedules;
        }

        $apply_amount = $this->input->post('apply_amount');

        $penalty_amount = $post_var['penalty_value'];
        if ($post_var['penalty_type'] == 'percentage')
        {
            $penalty_amount = ($apply_amount * ($post_var['penalty_value'] / 100));
        }
        $post_var["penalty_amount"] = $penalty_amount;
        
        $grace_period_days = $this->get_grace_period_days( $loan_id );
        $post_var["grace_period_days"] = $grace_period_days;
        
        $loan_schedule = $this->Loan->get_loan_schedule($post_var);
        //var_dump($loan_schedule);die;
        // payment scheds - end

        $net_proceeds = 0;
        
        if ( $post_var["InterestType"] == 'loan_deduction' )
        {
            $interest_amount = $post_var["ApplyAmt"] * ($post_var["TotIntRate"]/100);
            $net_proceeds = $post_var["ApplyAmt"] - $interest_amount - $total_fees;
        }
        
        $loan_status = $this->input->post("status");
        $exclude_additional_fees = $this->input->post("exclude_additional_fees") != '' ? 1 : 0;

        $loan_data = array(
            'account' => $this->input->post('account'),
            'description' => $this->input->post('description'),
            'loan_type_id' => $this->input->post('loan_type_id') > 0 ? $this->input->post('loan_type_id') : 0,
            'loan_amount' => $this->input->post('amount'),
            'customer_id' => $this->input->post('customer'),
            'loan_applied_date' => $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('apply_date'))) : strtotime($this->input->post('apply_date')),
            'remarks' => $this->input->post('remarks'),
            'loan_agent_id' => $this->input->post('agent'),            
            'misc_fees' => json_encode($misc_fees),
            'periodic_loan_table' => json_encode($loan_schedule),
            'apply_amount' => $this->input->post('apply_amount'),
            'interest_rate' => $this->input->post('interest_rate'),
            'interest_type' => ($this->input->post('interest_type') == '' ? 'fixed' : $this->input->post('interest_type')),
            'term_period' => $this->input->post('term_period'),
            'payment_term' => $this->input->post('term'),
            'payment_start_date' => $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('start_date'))) : strtotime($this->input->post('start_date')),
            'exclude_sundays' => $post_var["exclude_sundays"],
            'penalty_value' => ($this->input->post("penalty_value") > 0 ? $this->input->post("penalty_value") : 0),
            'penalty_type' => $this->input->post("penalty_type") != '' ? $this->input->post("penalty_type") : 'percentage',
            'net_proceeds' => $net_proceeds,
            'loan_status' => $loan_status,
            'add_fees' => json_encode($add_fees_arr),         
            'exclude_additional_fees' => $exclude_additional_fees
        );
        
        if (is_plugin_active("holidays"))
        {
            $loan_data['exclude_schedules'] = json_encode($exclude_schedules);
        }
        
        $loan_info = $this->Loan->get_info($loan_id);
        
        $is_reverted = false;
        if ( $loan_status == 'approved' )
        {
            if ( $loan_info->loan_status != $loan_status )
            {
                $loan_data['loan_approved_by_id'] = $current_user_id;
                $loan_data['loan_approved_date'] = time();
            }
        }
        else
        {
            if ( $loan_status == 'paid' )
            {
                
            }
            else
            {
                $loan_data['loan_approved_by_id'] = 0;
                $loan_data['loan_approved_date'] = 0;
                
                if ( $loan_info->loan_status == 'approved' )
                {
                    $is_reverted = true;
                }
            }
        }
        
        if ( $this->input->post("grace_periods_json") != '' )
        {
            $loan_data['grace_periods'] = $this->input->post("grace_periods_json");            
        }

        // check loan payment date
        if ($loan_data["loan_type_id"] > 0)
        {
            
        }
        else
        {
            if ( $loan_id <= 0 )
            {
                $loan_data["loan_payment_date"] = $this->config->item('date_format') == 'd/m/Y' ? strtotime(uk_to_isodate($this->input->post('start_date'))) : strtotime($this->input->post("start_date"));
            }
        }

        $guarantee_data = array(
            'loan_id' => $loan_id,
            'name' => $this->input->post("guarantee_name"),
            'type' => $this->input->post("guarantee_type"),
            'brand' => $this->input->post("guarantee_brand"),
            'make' => $this->input->post("guarantee_make"),
            'serial' => $this->input->post("guarantee_serial"),
            'proof' => json_encode($this->input->post("proofs")),
            'images' => json_encode($this->input->post("images")),
            'price' => $this->input->post("guarantee_price") > 0 ? $this->input->post("guarantee_price") : 0,
            'observations' => $this->input->post("guarantee_observations")
        );
        
        if (is_plugin_active('loan_products'))
        {
            $loan_data["loan_product_id"] = $this->input->post("loan_product");
        }

        if ($this->Loan->save($loan_data, $loan_id))
        {
            if ( $is_reverted )
            {
                $log_data = [];
                $log_data['description'] = 'Loan Reverted: ' . $loan_data['apply_amount'] . ' <br/>Remarks: Pending ' . $loan_data['remarks'];
                $log_data['amount'] = $loan_data['apply_amount'] * -1;
                $log_data['customer_id'] = $loan_data['customer_id'];
                $log_data['added_by'] = $current_user_id;
                $log_data['trans_type'] = 'Loan';
                $log_data['trans_id'] = ($loan_id == -1) ? $loan_data['loan_id'] : $loan_id;
                log_transactions($log_data);
            }
            else
            {
                $log_data = [];
                $log_data['description'] = 'Loan Approved: Amount of ' . $loan_data['apply_amount'] . ' <br/>Remarks: ' . $loan_data['remarks'];
                $log_data['amount'] = $loan_data['apply_amount'];
                $log_data['customer_id'] = $loan_data['customer_id'];
                $log_data['added_by'] = $current_user_id;
                $log_data['trans_type'] = 'Loan';
                $log_data['trans_id'] = ($loan_id == -1) ? $loan_data['loan_id'] : $loan_id;
                log_transactions($log_data);
            }
            
            //New Loan
            if ($loan_id == -1)
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('loans_successful_adding') . ' ' .
                    $loan_data['account'], 'loan_id' => $loan_data['loan_id']));
                $loan_id = $loan_data['loan_id'];
            }
            else //previous loan
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('loans_successful_updating') . ' ' .
                    $loan_data['account'], 'loan_id' => $loan_id));
            }

            $this->Guarantee->save($guarantee_data, $loan_id);
        }
        else//failure
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('loans_error_adding_updating') . ' ' .
                $loan_data['account'], 'loan_id' => -1));
        }
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

    function data($status = "")
    {
        $sel_user = $this->input->get("employee_id");
        $order = array("index" => $_GET['order'][0]['column'], "direction" => $_GET['order'][0]['dir']);
        $loans = $this->Loan->get_all($_GET['length'], $_GET['start'], $_GET['search']['value'], $order, $status, $sel_user);

        $format_result = array();

        foreach ($loans->result() as $loan)
        {
            $loan_status = $loan->loan_status;
            if ($loan->loan_balance <= 0)
            {
                $loan_status = "Paid";
            }

            $format_result[] = array(
                "<input type='checkbox' name='chk[]' id='loan_$loan->loan_id' value='" . $loan->loan_id . "'/>",
                $loan->loan_id,
                ucwords(str_replace('_', '<br/>', $loan->interest_type)),
                $loan->account,
                $loan->description,
                to_currency($loan->loan_amount),
                to_currency($loan->loan_balance),
                ucwords($loan->customer_name),
                $loan->customer_phone,//telefono cliente
                ucwords($loan->agent_name),
                ucwords($loan->approver_name),
                $loan->loan_approved_date > 0 ? date($this->config->item('date_format'), $loan->loan_approved_date) : '',
                ($loan->loan_payment_date > 0) ? date($this->config->item('date_format'), $loan->loan_payment_date) : '',
                $this->lang->line("common_" . strtolower($loan_status)),
                anchor('loans/view/' . $loan->loan_id, $this->lang->line('common_view'), array('class' => 'btn btn-success'))
            );
        }

        $data = array(
            "recordsTotal" => $this->Loan->count_all(),
            "recordsFiltered" => $this->Loan->count_all(),
            "data" => $format_result
        );

        echo json_encode($data);
        exit;
    }
    
    function overdues()
    {
        $order = array("index" => $_GET['order'][0]['column'], "direction" => $_GET['order'][0]['dir']);
        $loans = $this->Loan->get_all($_GET['length'], $_GET['start'], $_GET['search']['value'], $order, "overdue");
        $format_result = array();
        foreach ($loans->result() as $loan)
        {
            $loan_status = $loan->loan_status;
            if ($loan->loan_balance <= 0)
            {
                $loan_status = "Paid";
            }
            $format_result[] = array(
                "<input type='checkbox' name='chk[]' id='loan_$loan->loan_id' value='" . $loan->loan_id . "'/>",
                $loan->loan_id,
                $loan->loan_type,
                $loan->account,
                $loan->description,
                to_currency($loan->loan_amount),
                to_currency($loan->loan_balance),
                ucwords($loan->customer_name),
                $loan->customer_phone,//telefono en la lista de cuentas por cobrar
                ucwords($loan->agent_name),
                ucwords($loan->approver_name),
                date($this->config->item('date_format'), $loan->loan_applied_date),
                date($this->config->item('date_format'), $loan->loan_payment_date),
                $this->lang->line("common_" . strtolower($loan_status)),
                anchor('loans/view/' . $loan->loan_id, $this->lang->line('common_view'), array('class' => 'modal_link btn btn-success', "title" => "Update Loan"))
            );
        }

        $data = array(
            "recordsTotal" => $this->Loan->count_all(),
            "recordsFiltered" => $this->Loan->count_all(),
            "data" => $format_result
        );

        echo json_encode($data);
        exit;
    }

    function fix_breakdown($loan_id)
    {
        ini_set('memory_limit', '1024M');
        
        $loan = $this->Loan->get_info($loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $customer = $this->Customer->get_info($loan->customer_id);
        $leads = (object)  $this->Customer->get_lead($loan->customer_id)[0];

        $filename = "schedule" . time();
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";

        $data['company_name'] = $this->config->item("company"); // company name
        $data['company_address'] = $this->config->item("address"); // company address
        $data['phone'] = $this->config->item("phone"); // company address
        $data['fax'] = $this->config->item("fax"); // company address
        $data['email'] = $this->config->item("email"); // company address

        $data['loan_amount'] = to_currency($loan->apply_amount); // loan amount
        $data['payable'] = to_currency($loan->loan_amount);

        $data['rate'] = $loan->interest_rate; // interest rate
        $data['term'] = $loan->payment_term;

        $data['loan'] = $loan;
        $data['loan_type'] = $loan->interest_type;
        $data['term_period'] = $loan->term_period;
        $data['schedules'] = json_decode($loan->periodic_loan_table);
        $data['term_schedules'] = $this->Payment_schedule->get_schedules();

        $data['document_number'] =   $leads->id_no;
        $data['misc_fees'] = array();

        $misc_fees = json_decode($loan->misc_fees, true);
        $total_deductions = 0;
        $loan_deduction_interest = 0;
        
        if ( $loan->interest_type == 'loan_deduction' )
        {
            $loan_deduction_interest = ($loan->apply_amount * ( $loan->interest_rate/100 ));
        }

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_deductions += $charge;
            endforeach;
            $data['misc_fees'] = $tmp;
        }
        
        $total_deductions += $loan_deduction_interest;

        $data['loan_deduction_interest'] = to_currency($loan_deduction_interest);
        $data['customer_name'] = ucwords($customer->first_name . " " . $customer->last_name);
        $data['customer_phone'] = $customer->customer_phone;//telefono cliente
        $data['customer_address'] = ucwords($customer->address_1);
        $data['total_deductions'] = to_currency($total_deductions);
        $data['net_loan'] = $loan->net_proceeds > 0 ? to_currency($loan->net_proceeds) : to_currency($loan->apply_amount - $total_deductions);
        $data["add_fees"] = json_decode($loan->add_fees, true);
        
        $html = $this->load->view('loans/pdf/payment_schedule', $data, true); // render the view into HTML
        
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822)); 
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/$filename.pdf"));
    }

    function generate_breakdown($loan_id)
    {
        $loan = $this->Loan->get_info($loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $customer = $this->Customer->get_info($loan->customer_id);
        $leads = (object)  $this->Customer->get_lead($loan->customer_id)[0];
        if ($loan_type->term_period_type === "year")
        {
            $period = $this->_get_period($loan_type->payment_schedule);
        }
        else
        {
            $period = $this->_get_period($loan_type->payment_schedule, false);
        }

        $payable = $this->_calculate_mortgage($loan->loan_balance, $loan_type->percent_charge1, $loan_type->term, $period);

        $filename = "schedule" . time();
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";

        $data['company_name'] = $this->config->item("company"); // company name
        $data['company_address'] = $this->config->item("address"); // company address
        $data['phone'] = $this->config->item("phone"); // company address
        $data['fax'] = $this->config->item("fax"); // company address
        $data['email'] = $this->config->item("email"); // company address

        $data['loan_amount'] = to_currency($loan->loan_amount); // loan amount
        $data['payable'] = to_currency($loan->loan_balance);
        $data['rate'] = $loan_type->percent_charge1; // interest rate
        $data['term'] = $loan_type->term;

        $data['loan'] = $loan;
        $data['loan_type'] = $loan_type;
        $data['period_type'] = $loan_type->payment_schedule;
        $data['schedules'] = $this->Payment_schedule->get_schedules();

        $data['document_number'] =   $leads->id_no;

        $data['misc_fees'] = array();

        $misc_fees = json_decode($loan->misc_fees, true);
        $total_deductions = 0;

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_deductions += $charge;
            endforeach;
            $data['misc_fees'] = $tmp;
        }

        $data['customer_name'] = ucwords($customer->first_name . " " . $customer->last_name);
        $data['customer_phone'] = $customer->customer_phone;//telefono cliente
        $data['customer_address'] = ucwords($customer->address_1);
        $data['total_deductions'] = to_currency($total_deductions);
        $data['net_loan'] = to_currency($loan->loan_amount - $total_deductions);
        $data['total_interest'] = $this->_calculate_total_interest($loan->loan_balance, $loan_type->term, $payable, $period); // pass data to the view

        if ($loan_type->term_period_type === "year")
        {
            $tmp = $this->_get_repayment_amount_year_term($loan_type, $loan->loan_amount);
        }
        else
        {
            $tmp = $this->_get_repayment_amount_month_term($loan_type, $loan->loan_amount);
        }

        $data['repayment_amount'] = to_currency($payable);
        $data['payment_sched'] = strtoupper($tmp['payment_sched']);
        $data['apr'] = $tmp['apr'];


        $data['breakdown_data'] = $this->_calculate_breakdown($loan_type->term, $period, $loan_type->percent_charge1, $payable, $loan->loan_balance, $loan->loan_payment_date, $loan_type->payment_schedule);

        ini_set('memory_limit', '64M');
        $html = $this->load->view('loans/payment_schedule', $data, true); // render the view into HTML

        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/$filename.pdf"));
    }

    function print_disclosure($loan_id)
    {
        ini_set('memory_limit', '1024M');
        $this->load->model("Garante");
     
        $loan = $this->Loan->get_info($loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $customer = $this->Customer->get_info($loan->customer_id);
        $leads = (object)  $this->Customer->get_lead($loan->customer_id)[0];
        $employee = $this->Employee->get_logged_in_employee_info();
        $garante = (object) $this->Garante->get_info($loan->loan_id);
        
        
        if ($loan_type->term_period_type === "year")
        {
            $period = $this->_get_period($loan_type->payment_schedule);
        }
        else
        {
            $period = $this->_get_period($loan_type->payment_schedule, false);
        }


        $payable = $this->_calculate_mortgage($loan->loan_balance, $loan_type->percent_charge1, $loan_type->term, $period);

        $filename = "disclosure" . time();
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";

        $data['company_name'] = $this->config->item("company"); // company name
        $data['company_address'] = $this->config->item("address"); // company address
      
        $data['phone'] = $this->config->item("phone"); // company address
        $data['fax'] = $this->config->item("fax"); // company address
        $data['email'] = $this->config->item("email"); // company address

        $data['loan_amount'] = to_currency($loan->apply_amount); // loan amount
        $data['payable'] = to_currency($loan->loan_balance);
        $data['rate'] = $loan_type->percent_charge1; // interest rate
        
        $data['rate'] = $loan->interest_rate; // interest rate
        $data['term'] = $loan->payment_term;
        $data['term_period'] = $loan->term_period;

        $data['loan'] = $loan;
        $data['loan_type'] = $loan_type;
        $data['period_type'] = $loan_type->payment_schedule;
        $data['schedules'] = $this->Payment_schedule->get_schedules();

        $data['misc_fees'] = array();

        $misc_fees = json_decode($loan->misc_fees, true);
        $total_deductions = 0;
        $loan_deduction_interest = 0;
        
        if ( $loan->interest_type == 'loan_deduction' )
        {
            $loan_deduction_interest = ($loan->apply_amount * ($loan->interest_rate/100));
        }

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_deductions += $charge;
            endforeach;
            $data['misc_fees'] = $tmp;
        }
        
        $total_deductions += $loan_deduction_interest;
        
        $data["add_fees"] = json_decode($loan->add_fees, true);

        $data["loan_deduction_interest"] = to_currency($loan_deduction_interest);
        $data['customer_name'] = ucwords($customer->first_name . " " . $customer->last_name);
        $data['employee_user'] = ucwords($employee->first_name . " " . $employee->last_name);
        // $data['employee_user'] = ucwords( json_encode($employee));
        $data['employee_id'] = ucwords($employee->person_id );
        $data['customer_address'] = ucwords($customer->address_1);
        $data['customer_id'] = ucwords($loan->customer_id);
        $data['document_number'] =   $leads->id_no;
        $data['prueba_garante'] = $garante;
        $data['garante_name'] = ucwords($garante->nombre);
        $data['garante_id'] = $garante->ci;
        $data['total_deductions'] = to_currency($total_deductions);
        $data['net_loan'] = $loan->net_proceeds > 0 ? to_currency($loan->net_proceeds) : to_currency($loan->apply_amount - $total_deductions);
        
        if ($loan_type->term_period_type === "year")
        {
            $tmp = $this->_get_repayment_amount_year_term($loan_type, $loan->loan_amount);
        }
        else
        {
            $tmp = $this->_get_repayment_amount_month_term($loan_type, $loan->loan_amount);
        }

        $data['repayment_amount'] = to_currency($payable);
        $data['payment_sched'] = strtoupper($tmp['payment_sched']);
        $data['apr'] = $tmp['apr'];

        ini_set('memory_limit', '64M');
        $html = $this->load->view('loans/pdf/pdf_report', $data, true); // render the view into HTML

        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822));
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/$filename.pdf"));
    }

    function fix_disclosure($loan_id)
    {
        $loan = $this->Loan->get_info($loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $customer = $this->Customer->get_info($loan->customer_id);

        $filename = "disclosure" . time();
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";

        $data['company_name'] = $this->config->item("company"); // company name
        $data['company_address'] = $this->config->item("address"); // company address
        $data['phone'] = $this->config->item("phone"); // company address
        $data['fax'] = $this->config->item("fax"); // company address
        $data['email'] = $this->config->item("email"); // company address

        $data['loan_amount'] = to_currency($loan->loan_amount); // loan amount
        $data['payable'] = to_currency($loan->loan_balance);
        $data['rate'] = $loan_type->percent_charge1; // interest rate
        $data['term'] = $loan_type->term;
        $data['expenses'] = $loan_type->term;

        $data['loan'] = $loan;
        $data['loan_type'] = $loan_type;
        $data['period_type'] = $loan_type->payment_schedule;
        $data['schedules'] = $this->Payment_schedule->get_schedules();

        $data['misc_fees'] = array();

        $misc_fees = json_decode($loan->misc_fees, true);
        $total_deductions = 0;

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_deductions += $charge;
            endforeach;
            $data['misc_fees'] = $tmp;
        }

        $data['customer_name'] = ucwords($customer->first_name . " " . $customer->last_name);
        $data['customer_address'] = ucwords($customer->address_1);
        $data['total_deductions'] = to_currency($total_deductions);
        $data['net_loan'] = to_currency($loan->loan_amount - $total_deductions);


        $data['repayment_amount'] = to_currency($payable);
        $c_payment_sches = [
            "term" => "",
            "term_period" => "",
            "payment_sched" => "",
            "interest_rate" => "",
            "penalty" => "",
            "payment_breakdown" => [
                "schedule" => [],
                "balance" => [],
                "interest" => [],
                "amount" => []
            ]
        ];

        $c_payment_scheds = trim($loan->payment_scheds) !== "" ? json_decode($loan->payment_scheds, TRUE) : $c_payment_sches;
        $data["c_payment_scheds"] = $c_payment_scheds;

        ini_set('memory_limit', '64M'); // boost the memory limit if it's low <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $html = $this->load->view('loans/pdf_report_fix', $data, true); // render the view into HTML

        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822)); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/$filename.pdf"));
    }

    private function _get_repayment_amount_year_term($loan_type, $loan_balance_amount)
    {
        // get the term of payment
        $term = $loan_type->term; // 1yr
        // period_type1 means recurrence of interest
        // how to get the APR (Annual Percentage Rate) if 
        // im going to give an interest rate of 3% every 3 weeks
        // of course im going get how many time 3 weeks in a year
        // 52 weeks / 3 weeks = 17 weeks then multiply it by 3%
        // the answer is the APR, which is 51% in year.
        // interest rate is computed by APR / number times in a year

        switch ($loan_type->period_type1)
        {
            case "Week": // 52 weeks in 1yr
                $period = 52 * $term;
                $apr = (52 / $loan_type->period_charge1) * $loan_type->percent_charge1;
                $interest_rate = $apr / 52;
                break;
            case "Month": // 12 months in 1yr
                $period = 12 * $term;
                $apr = (12 / $loan_type->period_charge1) * $loan_type->percent_charge1;
                $interest_rate = $apr / 12;
                break;
            case "Year": // 1yr in 1yr
                $period = 1 * $term;
                $apr = (1 / $loan_type->period_charge1) * $loan_type->percent_charge1;
                $interest_rate = $apr / 1;
                break;
        }

        switch ($loan_type->payment_schedule)
        {
            case "weekly": // 52 weeks in 1yr
                $factor = 52 * $term;
                $payment_sched = "Weekly payment";
                break;
            case "monthly": // 12 months in 1yr
                $factor = 12 * $term;
                $payment_sched = "Monthly payment";
                break;
            case "yearly": // 1yr in 1yr
                $factor = 1 * $term;
                $payment_sched = "Yearly payment";
                break;
            case "daily":
                $factor = 365 * $term;
                $payment_sched = "Daily payment";
                break;
        }

        if ($apr > 0)
        {
            $rate_per_payment = 1 / (1 + ($interest_rate / 100) );
            $payment = $loan_balance_amount * ( (1 - $rate_per_payment) / ( $rate_per_payment - (pow($rate_per_payment, $factor)) ) );

            return array("repayment_amount" => $payment, "apr" => $apr, "payment_sched" => $payment_sched);
        }
        else
        {
            $payment = $loan_balance_amount / $factor;
            return array("repayment_amount" => $payment, "apr" => $apr, "payment_sched" => $payment_sched);
        }
    }

    private function _get_repayment_amount_month_term($loan_type, $loan_balance_amount)
    {
        // get the term of payment
        $term = $loan_type->term; // 1month
        // period_type1 means recurrence of interest
        // how to get the APR (Annual Percentage Rate) if 
        // im going to give an interest rate of 3% every 3 weeks
        // of course im going get how many time 3 weeks in a year
        // 52 weeks / 3 weeks = 17 weeks then multiply it by 3%
        // the answer is the APR, which is 51% in year.
        // interest rate is computed by APR / number times in a year

        /* switch ($loan_type->period_type1)
          {
          case "Week": // 52 weeks in 1yr
          $period = 52 * $term;
          $amr = (52 / $loan_type->period_charge1) * $loan_type->percent_charge1;
          $interest_rate = $amr / 52;
          break;
          case "Month": // 12 months in 1yr
          $period = 12 * $term;
          $amr = (12 / $loan_type->period_charge1) * $loan_type->percent_charge1;
          $interest_rate = $amr / 12;
          break;
          case "Year": // 1yr in 1yr
          $period = 1 * $term;
          $amr = (1 / $loan_type->period_charge1) * $loan_type->percent_charge1;
          $interest_rate = $amr / 1;
          break;
          } */

        switch ($loan_type->payment_schedule)
        {
            case "weekly": // 4 weeks in 1month
                $factor = 4 * $term;
                $payment_sched = "Weekly payment";
                break;
            case "biweekly": // 4 weeks in 1month
                $factor = 2 * $term;
                $payment_sched = "Bi-Weekly payment";
                break;
            case "monthly": // 1 month in 1month
                $factor = 1 * $term;
                $payment_sched = "Monthly payment";
                break;
            case "daily":
                $factor = 30 * $term;
                $payment_sched = "Daily payment";
                break;
        }

        $interest_rate = $loan_type->percent_charge1;

        if ($interest_rate > 0)
        {
            $rate_per_payment = 1 / (1 + ($interest_rate / 100) );
            $repayment = $loan_balance_amount * ( (1 - $rate_per_payment) / ( $rate_per_payment - (pow($rate_per_payment, $factor)) ) );
            return array("repayment_amount" => $repayment, "apr" => $interest_rate, "payment_sched" => $payment_sched);
        }
        else
        {
            $payment = @$factor > 0 ? $loan_balance_amount / @$factor : 0;
            return array("repayment_amount" => $payment, "apr" => @$amr, "payment_sched" => @$payment_sched);
        }
    }

    private function _calculate_breakdown($term, $period, $rate, $pay, $balance, $payment_date, $payment_schedule)
    {
        $data = array();
        for ($i = 0; $i < ($term * $period); $i++)
        {
            $tmp = (($pay) - ($balance * ($rate / 100 / $period)));
            $diff = round($tmp, 2);
            $int = round(($balance * $rate / 100 / $period), 2);
            $princ = $balance - $diff;
            $balance = round($balance, 0);

            $data[$i]['month'] = date("M d, Y", $payment_date);

            switch ($payment_schedule)
            {
                case "weekly":
                    $payment_date = strtotime("+7 day", $payment_date);
                    break;
                case "biweekly":
                    $payment_date = strtotime("+14 day", $payment_date);
                    break;
                case "monthly":
                    $payment_date = strtotime("+1 month", $payment_date);
                    break;
                case "bimonthly":
                    $payment_date = strtotime("+2 month", $payment_date);
                    break;
                case "daily":
                    $payment_date = strtotime("+1 day", $payment_date);
                    break;
            }

            $data[$i]['balance'] = to_currency($balance);
            $data[$i]['interest'] = $int;
            $data[$i]['pay'] = to_currency($pay);

            $balance = $princ;
        }

        return $data;
    }

    private function _get_period($period_type, $is_yearly = true)
    {
        if ($is_yearly)
        {
            // 52 - Weekly
            // 26 - Biweekly
            // 12 - Monthly
            //  6 - Bimonthly
            // $period = 12;
            $period = 12;
            switch ($period_type)
            {
                case "weekly":
                    $period = 52;
                    break;
                case "biweekly":
                    $period = 26;
                    break;
                case "monthly":
                    $period = 12;
                    break;
                case "bimonthly":
                    $period = 6;
                    break;
            }
        }
        else
        {
            // 4 - Weekly
            // $period = 12;
            $period = 4;
            switch ($period_type)
            {
                case "weekly":
                    $period = 4;
                    break;
                case "biweekly":
                    $period = 2;
                    break;
                case "daily":
                    $period = 30;
                    break;
                case "monthly":
                    $period = 1;
                    break;
            }
        }

        return $period;
    }

    private function _calculate_mortgage($balance, $rate, $term, $period)
    {
        $N = (int) $term * (int) $period;
        $I = ((float) $rate / 100) / (int) $period;
        $v = pow((1 + $I), $N);
        $t = ($v - 1) > 0 ? ($I * $v) / ($v - 1) : 1;
        $result = $balance * $t;

        return $result;
    }

    private function _calculate_total_interest($balance, $term, $pay, $period)
    {
        return (($term * $pay * $period) - $balance);
    }

    private function _count_overdues()
    {
        return $this->Loan->count_overdues();
    }

    function customer_search()
    {
        $suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('query'), 30);
        $data = $tmp = array();

        foreach ($suggestions as $suggestion):
            $t = explode("|", $suggestion);
            $tmp = array("value" => $t[1], "data" => $t[0]);
            $data[] = $tmp;
        endforeach;

        echo json_encode(array("suggestions" => $data));
        exit;
    }

    function select_customer()
    {
        $customer_id = $this->input->post("customer");
        $this->sale_lib->set_customer($customer_id);
        $this->_reload();
    }

//    function upload()
//    {
//        $directory = FCPATH . 'uploads/loan-' . $_REQUEST["loan_id"] . "/";
//        $this->load->library('uploader');
//        $data = $this->uploader->upload($directory);
//
//        $this->Loan->save_attachments($data['params']['loan_id'], $data);
//
//        $file = $this->_get_formatted_file($data['attachment_id'], $data['filename'], "");
//        $file['loan_id'] = $data['params']['loan_id'];
//        $file['id'] = $data["attachment_id"];
//        $file['token_hash'] = $this->security->get_csrf_hash();
//
//        echo json_encode($file);
//        exit;
//    }

    function remove_file()
    {
        $file_id = $this->input->post("file_id");
        $return["status"] = $this->Loan->remove_file($file_id);
        $return['token_hash'] = $this->security->get_csrf_hash();
        echo json_encode($return);
        exit;
    }

    function attach_desc()
    {
        $id = $this->input->post("attach_id");
        $desc = $this->input->post("desc");
        $this->Loan->save_attach_desc($id, $desc);
        echo json_encode(array("success" => TRUE));
        exit;
    }

    function attachments($loan_id, $select_type)
    {
        $data['loan_info'] = $this->Loan->get_info($loan_id);
        $attachments = $this->Loan->get_attachments($loan_id);

        $file = array();
        foreach ($attachments as $attachment)
        {
            $file[] = $this->_get_formatted_file($attachment->attachment_id, $attachment->filename, $attachment->descriptions);
        }

        $data["select_type"] = $select_type;
        $data['attachments'] = $file;
        $this->load->view("loans/attachments", $data);
    }

    function ajax()
    {
        $ajax_type = $this->input->post('ajax_type');
        switch ($ajax_type)
        {
            case 0: 
                $this->_dt_garante(); 
                break;
            case 1: // Calculator
                $this->_handle_calculator();
                break;
            case 2: // Approve loan
                $this->_handle_approve_loan();
                break;
            case 3: // Get transactions
                $this->_dt_transactions();
                break;
            case 4: // Delete transactions
                $this->_handle_delete_transactions();
                break;
            case 5: // Save grace period
                $this->_handle_save_grace_period();
                break;
            case 6:
                $this->_get_total_balance();
                break;
            case 7: // Get documents table
                $this->_dt_documents();
                break;
            case 15: // Get documents table
                $this->_handle_load_garante_info();
                break;
            case 13:
                    $this->_delete_garante();
            case 8:
                $this->_handle_add_documents();
                break;
            case 9:
                $this->_handle_delete_file();
            case 10:
                $this->_dt_collateral();
            case 11:
                $this->_handle_save_collateral_info();
            case 12:
                $this->_handle_load_collateral_info();
           
            case 14:
                $this->_handle_load_collateral_doc_list();
        }
    }
    
    private function _handle_load_collateral_doc_list()
    {
        $id = $this->input->post("id");
        $this->set_dt_documents($this->datatablelib->datatable(), $id, "collateral_document");
        $data["tbl_doc_list"] = $this->datatablelib->render();
        $this->load->view("loans/tabs/collateral/documents/list", $data);
    }
    
    private function _delete_garante()
    {
        //TODO:se encuentra funcional sin embargo hay que revisar la implementacion del datatable
        $id = $this->input->post("id");
        // $myfile = fopen("garante.txt", "w") or die("Unable to open file!"); 
     
        $this->db->from('garantes');
        $this->db->where('garante_id', $id)->delete();
                
        $return["status"] = "OK";
        // $txt = "id: ".$id." status:".json_encode($return);
        // fwrite($myfile, $txt);
        fclose($myfile);

      
        
        send($return);
    }
    
    private function _handle_save_collateral_info()
    {
        $id = $this->input->post("collateral")["id"];
        
        $data = [
            "name" => $this->input->post("collateral")["name"],
            "type" => $this->input->post("collateral")["type"],
            "brand" => $this->input->post("collateral")["brand"],
            "make" => $this->input->post("collateral")["make"],
            "serial" => $this->input->post("collateral")["serial"],
            "price" => $this->input->post("collateral")["price"],
            "observations" => $this->input->post("collateral")["observations"],
            "loan_id" => $this->input->post("loan_id")
        ];
        
        $this->Guarantee->save_details($id, $data);
        
        $return["status"] = "OK";
        send($return);
    }
    private function  _handle_load_garante_info()
    {
        $this->load->model("Garante");
        $id = $this->input->post("id");
        
        $info = $this->Garante->get_details($id);
        
        $return["status"] = "OK";
        $return["info"] = $info;
        send($return);
    }

    
    private function _handle_load_collateral_info()
    {
        $id = $this->input->post("id");
        
        $info = $this->Guarantee->get_details($id);
        
        $return["status"] = "OK";
        $return["info"] = $info;
        send($return);
    }
    
    private function _handle_delete_file()
    {
        $loan_id = $this->input->post("loan_id");
        $document_id = $this->input->post("document_id");
        $document_type = $this->input->post("document_type");
        
        $guarantee_info = $this->Guarantee->get_info($loan_id);
        
        $file_ids = json_decode($guarantee_info->$document_type, true);

        $new_file_ids = [];
        foreach ( $file_ids as $id )
        {
            if ( $id != $document_id )
            {
                $new_file_ids[] = $id;
            }
        }
        
        $this->db->where("loan_id", $loan_id);
        $this->db->update("guarantee", [$document_type => json_encode($new_file_ids)]);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_add_documents()
    {
        $loan_id = $this->input->post("loan_id");
        $doc_type = $this->input->post("doc_type");
        $document_ids = $this->input->post("document_ids");
        
        $data['guarantee_info'] = $this->Guarantee->get_info($loan_id);        
        switch ( $doc_type )
        {
            case "proof":
                $doc_ids = json_decode($data['guarantee_info']->proof, TRUE);
                break;
            case "images":
                $doc_ids = json_decode($data['guarantee_info']->images, TRUE);
                break;
        }
        
        $new_doc_ids = $doc_ids;
        foreach ( $document_ids as $id )
        {
            if ( !in_array($id, $doc_ids) )
            {
                $new_doc_ids[] = $id;
            }
        }
        
        $data = [$doc_type => json_encode($new_doc_ids)];        
        $this->Guarantee->save($data, $loan_id);
        
        // Reload Views
        $data['guarantee_info'] = $this->Guarantee->get_info($loan_id);        
        $proof_ids = json_decode($data['guarantee_info']->proof, TRUE);
        $support_doc_ids = json_decode($data['guarantee_info']->images, TRUE);
        
        if ( !is_array($proof_ids) || (is_array($proof_ids) && count($proof_ids) <= 0) )
        {
            $proof_ids[] = -1;
        }
        if ( !is_array($support_doc_ids) || (is_array($support_doc_ids) && count($support_doc_ids) <= 0) )
        {
            $support_doc_ids[] = -1;
        }
        
        $data["loan_id"] = $loan_id;
        
        $filters = ["document_ids" => $proof_ids];
        $data["proofs"] = $this->Document_model->get_list($filters);
        
        $filters = ["document_ids" => $support_doc_ids];
        $data["supporting_docs"] = $this->Document_model->get_list($filters);
        
        switch( $doc_type )
        {
            case "proof":
                $html = $this->load->view('loans/tabs/collateral/proofs', $data, true);
                break;
            case "images":
                $html = $this->load->view('loans/tabs/collateral/docs', $data, true);
                break;
        }
        
        $return["status"] = "OK";
        $return["html"] = $html;
        
        send($return);
    }
    
    private function _handle_save_grace_period()
    {
        $loan_id = $this->input->post("loan_id");
        
        $period = $this->input->post("period");
        $qty = $this->input->post("qty");
        $unit = $this->input->post("unit");
        
        $period = $period ? $period : [];
        
        $tmp = [];
        $i = 0;
        foreach ( $period as $key => $value )
        {
            $tmp[$value] = ["period" => $value, "qty" => $qty[$i], "unit" => $unit[$i]];
            $i++;
        }

        $grace_periods = json_encode($tmp);
            
        if ( $loan_id > 0 )
        {
            $this->db->where("loan_id", $loan_id);
            $this->db->update("loans", ["grace_periods" => $grace_periods]);
            $return["ret_grace_periods"] = '';
        }
        else
        {
            $return["ret_grace_periods"] = $grace_periods;
        }
        
        $return["status"] = "OK";
        send($return);
    }

    private function _handle_delete_transactions()
    {
        $id = $this->input->post("id");
        $this->Loan->delete_list([$id]);

        $return["status"] = "OK";
        send($return);
    }

    private function _handle_approve_loan()
    {
        $approver = $this->input->post("approver");
        $loan_id = $this->input->post("loan_id");

        $update_data = [];
        $update_data["loan_approved_by_id"] = $approver;
        $update_data["loan_status"] = "approved";
        $update_data["loan_approved_date"] = time();

        $this->db->where("loan_id", $loan_id);
        $this->db->update("loans", $update_data);

        $return["status"] = "OK";
        send($return);
    }
    
    private function get_grace_period_days( $loan_id )
    {
        $sql = "SELECT grace_periods FROM c19_loans WHERE loan_id = '$loan_id'";
        $query = $this->db->query( $sql );
        
        $tmp = [];
        
        if ( $query && $query->num_rows() > 0 )
        {
            $grace_periods = json_decode($query->row()->grace_periods, true);
            $grace_periods = is_array($grace_periods) ? $grace_periods : [];
            foreach ( $grace_periods as $grace_period )
            {
                switch ( $grace_period["unit"] )
                {
                    case "Days":
                        $in_days = $grace_period["qty"] * 1;
                        break;
                    case "Weeks":
                        $in_days = $grace_period["qty"] * 7;
                        break;
                    case "Months":
                        $in_days = $grace_period["qty"] * 30;
                        break;
                    case "Years":
                        $in_days = $grace_period["qty"] * 365;
                        break;
                }
                $tmp[$grace_period["period"]] = $in_days;
            }
        }
        
        return $tmp;
    }

    private function _handle_calculator()
    {
        $apply_amount = $this->input->post('ApplyAmt');
        $penalty_value = $this->input->post("penalty_value");
        $penalty_type = $this->input->post("penalty_type");
        $total_additional_fees = $this->input->post("additional_fees");
        $exclude_additional_fees = $this->input->post("exclude_additional_fees");

        $penalty_amount = $penalty_value;
        if ($penalty_type == 'percentage')
        {
            $penalty_amount = ($apply_amount * ($penalty_value / 100));
        }

        $_POST['penalty_amount'] = $penalty_amount;
        
        if ( $this->input->post("grace_period_json") != '' )
        {
            $grace_periods = json_decode($this->input->post("grace_period_json"), true);
            $grace_periods = is_array($grace_periods) ? $grace_periods : [];
            foreach ( $grace_periods as $grace_period )
            {
                switch ( $grace_period["unit"] )
                {
                    case "Days":
                        $in_days = $grace_period["qty"] * 1;
                        break;
                    case "Weeks":
                        $in_days = $grace_period["qty"] * 7;
                        break;
                    case "Months":
                        $in_days = $grace_period["qty"] * 30;
                        break;
                    case "Years":
                        $in_days = $grace_period["qty"] * 365;
                        break;
                }
                $tmp[$grace_period["period"]] = $in_days;
            }
            
            $grace_period_days = $tmp;
        }
        else
        {
            $grace_period_days = $this->get_grace_period_days( $this->input->post("loan_id") );
        }
        
        $_POST["grace_period_days"] = $grace_period_days;

        $scheds = $this->Loan->get_loan_schedule($this->input->post());
        $data["scheds"] = $scheds;
        
        $table_scheds = $this->load->view('loans/tabs/table_scheds', $data, 1);
        
        $total_amount = 0;
        foreach( $scheds as $sched )
        {
            $total_amount += $sched["payment_amount"];
        }

        $return["table_scheds"] = $table_scheds;
        
        if ( $exclude_additional_fees )
        {
            $return["total_amount"] = $total_amount;
            $return["formatted_total_amount"] = to_currency($total_amount);
        }
        else
        {
            $return["total_amount"] = $total_amount + $total_additional_fees;
            $return["formatted_total_amount"] = to_currency($total_amount + $total_additional_fees);
        }
        
        
        $return["scheds"] = $scheds;
        $return["status"] = "OK";
        $return["asd"] =json_encode($this->input->post());
        send($return);
    }

    public function assets()
    {
        //---get working directory and map it to your module
        $file = getcwd() . '/application/modules/' . implode('/', $this->uri->segments);

        //----get path parts form extension
        $path_parts = pathinfo($file);
        //---set the type for the headers
        $file_type = strtolower($path_parts['extension']);

        if (is_file($file))
        {
            //----write propper headers
            switch ($file_type)
            {
                case 'css':
                    header('Content-type: text/css');
                    break;

                case 'js':
                    header('Content-type: text/javascript');
                    break;

                case 'json':
                    header('Content-type: application/json');
                    break;

                case 'xml':
                    header('Content-type: text/xml');
                    break;

                case 'pdf':
                    header('Content-type: application/pdf');
                    break;

                case 'woff2':
                    header('Content-type: application/font-woff2');
                    readfile($file);
                    exit;
                    break;

                case 'woff':
                    header('Content-type: application/font-woff');
                    readfile($file);
                    exit;
                    break;

                case 'ttf':
                    header('Content-type: applicaton/x-font-ttf');
                    readfile($file);
                    exit;
                    break;

                case 'jpg' || 'jpeg' || 'png' || 'gif':
                    header('Content-type: image/' . $file_type);
                    readfile($file);
                    exit;
                    break;
            }

            include $file;
        }
        else
        {
            show_404();
        }
        exit;
    }

    function statement($loan_id)
    {
        ini_set('memory_limit', '1024M');
        
        $this->load->model("payments/Payment");
        
        $loan = $this->Loan->get_info($loan_id);
        $loan_type = $this->Loan_type->get_info($loan->loan_type_id);
        $customer = $this->Customer->get_info($loan->customer_id);

        $filename = "statement" . time();
        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
        $pdfFilePath = FCPATH . "/downloads/reports/$filename.pdf";

        $data['company_name'] = $this->config->item("company"); // company name
        $data['company_address'] = $this->config->item("address"); // company address
        $data['phone'] = $this->config->item("phone"); // company address
        $data['fax'] = $this->config->item("fax"); // company address
        $data['email'] = $this->config->item("email"); // company address

        $data['loan_amount'] = to_currency($loan->apply_amount); // loan amount
        $data['payable'] = to_currency($loan->loan_amount);

        $data['rate'] = $loan->interest_rate; // interest rate
        $data['term'] = $loan->payment_term;

        $data['loan'] = $loan;
        $data['loan_type'] = $loan->interest_type;
        $data['term_period'] = $loan->term_period;
        $data['schedules'] = json_decode($loan->periodic_loan_table);
        $data['term_schedules'] = $this->Payment_schedule->get_schedules();

        $data['misc_fees'] = array();
        $data['add_fees'] = array();

        $misc_fees = json_decode($loan->misc_fees, true);
        $add_fees = json_decode($loan->add_fees, true);
        
        $total_deductions = 0;
        $loan_deduction_interest = 0;
        
        if ( $loan->interest_type == 'loan_deduction' )
        {
            $loan_deduction_interest = ($loan->apply_amount * ( $loan->interest_rate/100 ));
        }

        if (is_array($misc_fees))
        {
            $tmp = array();
            foreach ($misc_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_deductions += $charge;
            endforeach;
            $data['misc_fees'] = $tmp;
        }
        
        $total_add_fees = 0;
        if (is_array($add_fees))
        {
            $tmp = array();
            foreach ($add_fees as $fee => $charge):
                if (trim($charge) !== "")
                {
                    $tmp[] = array($fee, to_currency($charge));
                }
                $total_add_fees += $charge;
            endforeach;
        }
        
        $total_deductions += $loan_deduction_interest;
        
        $data["next_payment_date"] = $loan->loan_payment_date;
        $data["payments"] = $this->Payment->get_payments_by($loan->loan_id);

        $data['loan_deduction_interest'] = to_currency($loan_deduction_interest);
        $data['total_add_fees'] = $total_add_fees;
        $data['customer_name'] = ucwords($customer->first_name . " " . $customer->last_name);
        $data['customer_address'] = ucwords($customer->address_1);
        $data['total_deductions'] = to_currency($total_deductions);
        $data['net_loan'] = $loan->net_proceeds > 0 ? to_currency($loan->net_proceeds) : to_currency($loan->loan_amount - $total_deductions);
        
        $html = $this->load->view('loans/pdf/account_statement', $data, true); // render the view into HTML
        
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetFooter($_SERVER['HTTP_HOST'] . '|{PAGENO}|' . date(DATE_RFC822)); 
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($pdfFilePath, 'F'); // save to file because we can

        redirect(base_url("downloads/reports/$filename.pdf"));
    }
    
    private function _get_total_balance()
    {
        $total_balance = $this->session->userdata("tbl_balance");
        $total_proceeds = $this->session->userdata("tbl_proceeds");  
        $total_net_proceeds = $this->session->userdata("tbl_net_proceeds");
        
        $return["total_net_proceeds"] = to_currency($total_net_proceeds, 1);
        $return["total_proceeds"] = to_currency($total_proceeds, 1);
        $return["total_balance"] = to_currency($total_balance, 1);
        
        $return["status"] = "OK";        
        send($return);
    }
    
    public function upload()
    {
        $this->load->model("Document_model");
        
        $loan_id = $this->input->post('loan_id');
        $document_name = $this->input->post('document_name');
        $descriptions = $this->input->post('descriptions');
        
        $path = FCPATH . "/downloads/loans-$loan_id/";
        if ( !is_dir($path) )
        {
            mkdir($path, 0777, true);
        }
        
        $this->load->library('uploader');
        
        $_FILES['file']['name'] = str_replace(' ', '_', $_FILES['file']['name']);

        $data = $this->uploader->upload($path);
        
        if ( empty($data["filename"]) )
        {
            $return["status"] = "FAILED";
            send($return);
        }
        
        $doc_data = [];
        $doc_data["document_name"] = $document_name;
        $doc_data["descriptions"] = $descriptions;
        $doc_data["document_path"] = "/downloads/loans-$loan_id/" . $data['filename'];
        $doc_data["document_type"] = "loan_document";
        $doc_data["foreign_id"] = $loan_id;
        $doc_data["added_date"] = date("Y-m-d H:i:s");
        
        $document_id = $this->Document_model->save( '', $doc_data );

        $return["status"] = "OK";
        $return["document_id"] = $document_id;
        $return["filename"] = $data['filename'];
        $return["path"] = base_url("downloads/loans-$loan_id/" . $data['filename']);
        
        send($return);
    }
    
    function set_dt_collateral($datatable, $loan_id)
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "ajax_type" => 10, 
            "loan_id" => $loan_id
        ];
        $datatable->add_server_params('', '', $params);
        $datatable->ajax_url = site_url('loans/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('name', false);
        $datatable->add_column('type', false);
        $datatable->add_column('brand', false);
        $datatable->add_column('make', false);
        $datatable->add_column('serial', false);
        $datatable->add_column('price', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#tbl_collateral";
        $datatable->add_titles('Collateral');
        $datatable->has_edit_dblclick = 0;
    }

    function set_dt_garantes($datatable, $loan_id)
    {
        $params = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 
            "ajax_type" => 0, 
            "loan_id" => $loan_id
        ];
        $datatable->add_server_params('', '', $params);
        $datatable->ajax_url = site_url('loans/ajax');

        $datatable->add_column('actions', false);
        $datatable->add_column('nombre', false);
        $datatable->add_column('ci', false);
        $datatable->add_column('phone', false);
        $datatable->add_column('cellphone', false);
        $datatable->add_column('direccion_hogar', false);
        $datatable->add_column('direccion_trabajo', false);
        $datatable->add_column('email', false);

        $datatable->add_table_definition(["orderable" => false, "targets" => 0]);
        $datatable->order = [[1, 'desc']];

        $datatable->allow_search = true;
        $datatable->no_expand_height = true;
        
        $datatable->table_id = "#tbl_garante";
        $datatable->add_titles('Garantes');
        $datatable->has_edit_dblclick = 0;
    }

    function _dt_collateral()
    {
        $this->load->model("Document_model");
        
        $loan_id = $this->input->post("loan_id");
        $doc_viewer = $this->input->post("doc_viewer");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $user_info = $this->Employee->get_logged_in_employee_info();
        
        $tmp = array();
        $count_all = 0;
        
        $filters = [];
        $filters["loan_id"] = $loan_id;        
        $result = $this->Guarantee->get_list($filters, $count_all);

        foreach ($result as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-collateral' data-id='". $row->guarantee_id ."' title='View'><span class='fa fa-pencil'></span></a> ";

            if ( check_access($user_info->role_id, "loans", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete-collateral btn' data-id='" . $row->guarantee_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }

            $data_row = [];
            $data_row["DT_RowId"] = $row->guarantee_id;
            $data_row["actions"] = $actions;
            
            $data_row["name"] = $row->name;
            $data_row["type"] = $row->type;
            $data_row["brand"] = $row->brand;
            $data_row["make"] = $row->make;
            $data_row["serial"] = $row->serial;
            $data_row["price"] = to_currency($row->price);
            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }

    function _dt_garante()
    {
        $this->load->model("Garante");
        
        $loan_id = $this->input->post("loan_id");
        // $doc_viewer = $this->input->post("doc_viewer");
        $offset = $this->input->post("start");
        $limit = $this->input->post("length");

        $index = $this->input->post("order")[0]["column"];
        $dir = $this->input->post("order")[0]["dir"];
        $keywords = $this->input->post("search")["value"];

        $order = array("index" => $index, "direction" => $dir);
        
        $user_info = $this->Employee->get_logged_in_employee_info();
        
        $tmp = array();
        $count_all = 0;
        
        $filters = [];
        $filters["loan_id"] = $loan_id;        
        $result = $this->Garante->get_list($filters, $count_all);

        foreach ($result as $row)
        {
            $actions = "<a href='javascript:void(0)' class='btn btn-xs btn-default btn-secondary btn-edit-garante' data-id='". $row->garante_id ."' title='View'><span class='fa fa-pencil'></span></a> ";

            if ( check_access($user_info->role_id, "loans", 'delete') )
            {
                $actions .= "<a href='javascript:void(0)' class='btn-xs btn-danger btn-delete-garante btn' data-id='" . $row->garante_id . "' title='Delete'><span class='fa fa-trash'></span></a>";
            }

            $data_row = [];
            $data_row["DT_RowId"] = $row->garante_id;
            $data_row["actions"] = $actions;
            
            $data_row["nombre"] = $row->nombre;
            $data_row["ci"] = $row->ci;
            $data_row["phone"] = $row->phone;
            $data_row["cellphone"] = $row->cellphone;
            $data_row["direccion_hogar"] = $row->direccion_hogar;
            $data_row["direccion_trabajo"] = $row->direccion_trabajo;
            $data_row["email"] = $row->email;

            $tmp[] = $data_row;
        }

        $data["data"] = $tmp;
        $data["recordsTotal"] = $count_all;
        $data["recordsFiltered"] = $count_all;

        send($data);
    }
    
    public function upload_collateral()
    {
        $this->load->model("Document_model");
        
        $id = $this->input->post('doc_collateral')['id'];
        $document_name = $this->input->post('doc_collateral')['document_name'];
        $descriptions = $this->input->post('doc_collateral')['descriptions'];
        
        $path = FCPATH . "/downloads/loans-$loan_id/collateral/";
        if ( !is_dir($path) )
        {
            mkdir($path, 0777, true);
        }
        
        $this->load->library('uploader');
        
        $_FILES['file']['name'] = str_replace(' ', '_', $_FILES['doc_collateral']['name']['file']);
        $_FILES['file']['tmp_name'] = str_replace(' ', '_', $_FILES['doc_collateral']['tmp_name']['file']);

        $data = $this->uploader->upload($path);
        
        if ( empty($data["filename"]) )
        {
            $return["status"] = "FAILED";
            send($return);
        }
        
        $doc_data = [];
        $doc_data["document_name"] = $document_name;
        $doc_data["descriptions"] = $descriptions;
        $doc_data["document_path"] = "/downloads/loans-$loan_id/collateral/" . $data['filename'];
        $doc_data["document_type"] = "collateral_document";
        $doc_data["foreign_id"] = $id;
        $doc_data["added_date"] = date("Y-m-d H:i:s");
        
        $document_id = $this->Document_model->save( '', $doc_data );

        $return["status"] = "OK";
        $return["document_id"] = $document_id;
        $return["filename"] = $data['filename'];
        $return["path"] = base_url("downloads/loans-$loan_id/collateral/" . $data['filename']);
        
        send($return);
    }

    /**
     * Callback de form_validation:
     * Falla si count_active_guarantees($ci) >= 2
     */
    public function ci_limit_two($ci)
    {
        $count = $this->Garante->count_active_guarantees($ci);

        if ($count >= 2)
        {
            $this->form_validation->set_message(
                'ci_limit_two',
                "El CI {$ci} ya garantiza {$count} créditos activos (límite 2)."
            );
            return FALSE;
        }
        return TRUE;
    }
    
    public function save_garante()
    {
        $this->load->model('Garante');
        $this->load->library('form_validation');

        // 1) Recogemos loan_id y los datos del formulario
        $loan_id    = $this->input->post('loan_id');
        $garante_id = $this->input->post('garante_id');

        // 2) Reglas de validación
        $this->form_validation->set_data($this->input->post());
        $this->form_validation->set_rules('nombre',    'Nombre',               'required|regex_match[/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{3,250}$/]');
        //$this->form_validation->set_rules('ci',        'Cédula de Identidad',  'required|regex_match[/^[0-9]{6,8}$/]|callback_ci_not_registered'); //cambio de regla
        $this->form_validation->set_rules('ci',        'Cédula de Identidad',  'required|regex_match[/^[0-9]{6,8}$/]|callback_ci_limit_two');
        $this->form_validation->set_rules('phone',     'Teléfono fijo',        'required|regex_match[/^[0-9]{7,8}$/]');
        $this->form_validation->set_rules('cellphone', 'Teléfono celular',     'required|regex_match[/^[0-9]{8}$/]');
        $this->form_validation->set_rules('email',     'Correo electrónico',   'required|valid_email');

        // 3) Ejecutamos validación
        if ($this->form_validation->run() === FALSE)
        {
            // Devolvemos errores en JSON para procesar en la vista
            /*
            $return = [
                'status' => 'ERROR',
                'errors' => $this->form_validation->error_array()
            ];
            echo json_encode($return);
            */
            // Si la validación falla, envío los mensajes de cada campo
            echo json_encode([
                'status' => 'ERROR',
                'errors' => $this->form_validation->error_array()  // ej. ['ci' => 'El CI ya existe', ...]
            ]);
            return;
        }

        // 4) Preparamos array para guardar
        $garante_data = [
            'loan_id'   => $loan_id,                         // ¡Importante!
            'nombre'    => $this->input->post('nombre'),
            'ci'        => $this->input->post('ci'),
            'phone'     => $this->input->post('phone'),
            'cellphone' => $this->input->post('cellphone'),
            'email'     => $this->input->post('email'),
            'direccion_hogar' => $this->input->post('direccion_hogar'),
            'direccion_trabajo' => $this->input->post('direccion_trabajo'),
        ];

        // 5) Guardado
        /*$new_id = $this->Garante->save($garante_data, $garante_id);

        echo json_encode([
            'status'     => 'OK',
            'garante_id' => $new_id
        ]);
        */
        
        if (!$this->Garante->save($garante_data, $this->input->post('garante_id')))
        {
            // Si falla el INSERT/UPDATE por cualquier razón…
            echo json_encode([
                'status' => 'ERROR',
                'errors' => ['general' => 'No se pudo guardar el garante. Intenta de nuevo.']
            ]);
            return;
        }

        // Si todo OK:
        echo json_encode([
            'status'     => 'OK',
            'garante_id' => $garante_data['garante_id']
        ]);
    }

    /**
    * Callback para chequear CI no repetido en crédito activo
    */
    public function ci_not_registered($ci)
    {
        if ($this->Garante->is_active_guarantee($ci))
        {
            $this->form_validation->set_message('ci_not_registered',
                "El CI {$ci} ya está asignado como garante de un crédito activo."
            );
            return FALSE;
        }
        return TRUE;
    }

}

?>