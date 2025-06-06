<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_transactions extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        // sidebar/header
        $this->load->model('Employee');
        $this->load->model('Module');
        $user_info = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) redirect('login');
        $allowed_modules = $this->Module->get_allowed_modules($user_info->person_id);
        $messages = $alerts = [];
        $this->load->vars(compact('user_info','allowed_modules','messages','alerts'));

        // modelos
        $this->load->model('savings_accounts/Savings_accounts_model');
        $this->load->model('savings_accounts/Savings_account_transactions_model');
        $this->load->model('savings_accounts/Savings_account_types_model');
    }

    public function index()
    {
        // opcional: filtros GET ?account_id=&trans_type=&date_from=&date_to=
        $filters = $this->input->get();
        $data['transactions'] = $this->Savings_account_transactions_model->get_all($filters);
        $this->load->view('savings_accounts/savings_account_transactions/index', $data);
    }

    public function form($id = NULL)
    {
        // dropdown de cuentas
        $accounts = $this->Savings_accounts_model->get_all();
        $opts = [];
        foreach ($accounts as $a) {
            $opts[$a->savings_account_id] = $a->account_number . ' – ' . $a->first_name . ' ' . $a->last_name;
        }
        $data['account_options'] = $opts;

        // tipos de operación
        $data['type_options'] = [
            'deposit'  => 'Depósito',
            'withdraw' => 'Retiro',
            'transfer' => 'Transferencia'
        ];

        if ($this->input->post()) {
            $post = $this->input->post();
            if ($id) {
                $this->Savings_account_transactions_model->update($id, $post);
            } else {
                $this->Savings_account_transactions_model->insert($post);
            }
            $this->session->set_flashdata('success','Transacción guardada correctamente.');
            redirect('savings_accounts/savings_account_transactions');
        }

        $data['tx'] = $id
            ? $this->Savings_account_transactions_model->get($id)
            : NULL;

        $this->load->view('savings_accounts/savings_account_transactions/form', $data);
    }

    public function delete($id = NULL)
    {
        if (is_null($id)) $id = $this->uri->segment(4);
        if ($id && $this->Savings_account_transactions_model->delete($id)) {
            $this->session->set_flashdata('success','Transacción deshabilitada.');
        }
        redirect('savings_accounts/savings_account_transactions');
    }

    /**
     * Formulario y lógica de depósito.
     */
    public function deposit($tx_id = NULL)
    {
        // 1) Dropdown de cuentas
        $accounts = $this->Savings_accounts_model->get_all();
        $opts = [];
        foreach ($accounts as $a) {
            $opts[$a->savings_account_id] = 
                $a->account_number . ' – ' . $a->first_name . ' ' . $a->last_name;
        }
        $data['account_options'] = $opts;

        if ($this->input->post()) {
            // recogen todos los campos del form
            $post = $this->input->post();
            if ($this->Savings_account_transactions_model->deposit($post)) {
                $this->session->set_flashdata('success','Depósito registrado correctamente.');
                redirect('savings_accounts/savings_account_transactions/index');
            } else {
                $this->session->set_flashdata('error','Error al procesar el depósito.');
            }
        }

        $data['tx'] = $tx_id
            ? $this->Savings_account_transactions_model->get($tx_id)
            : NULL;

        $this->load->view('savings_accounts/savings_account_transactions/deposit_form', $data);
    }
}
