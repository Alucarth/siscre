<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_accounts extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Inyectar sidebar/header
        $this->load->model('Employee');
        $this->load->model('Module');
        $user_info       = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) {
            redirect('login');
        }
        $allowed_modules = $this->Module->get_allowed_modules($user_info->person_id);
        $messages = $alerts = [];
        $this->load->vars(compact('user_info','allowed_modules','messages','alerts'));

        // Modelos de tipos y cuentas
        $this->load->model('savings_accounts/Savings_account_types_model');
        $this->load->model('savings_accounts/Savings_accounts_model');
    }

    public function index()
    {
        /*$path = APPPATH.'modules/savings_accounts/views/savings_accounts/index.php';
        if (! is_file($path)) {
            show_error("¡No encuentro la vista! Busqué: $path");
        }
        $data['accounts'] = $this->Savings_accounts_model->get_all();
        $this->load->view('savings_accounts/index', $data);*/
        $data['accounts'] = $this->Savings_accounts_model->get_all();
        $this->load->view('savings_accounts/savings_accounts/index', $data);
    }

    public function form($id = NULL)
    {
        $data['types'] = $this->Savings_account_types_model->get_all();
        // Carga el modelo de clientes y arma el dropdown
        // 1. Carga el modelo
        $this->load->model('Customer');

        // 2. Ejecuta get_all() y conviértelo en array de resultados
        $query = $this->Customer->get_all();

        // 3. Si viene un objeto de CI_DB_result, extrae result(); si no, asume que ya es array
        if ( is_object($query) && method_exists($query,'result') ) {
            $customers = $query->result();
        } elseif ( is_array($query) ) {
            $customers = $query;
        } else {
            $customers = [];
        }

        // 4. Arma el array [id => 'Nombre Completo']
        $customer_options = [];
        foreach ( $customers as $c ) {
            // asegúrate de que existan estas propiedades
            if (isset($c->person_id, $c->first_name, $c->last_name)) {
                $customer_options[ $c->person_id ] = $c->first_name . ' ' . $c->last_name;
            }
        }

        $data['customer_options'] = $customer_options;


        if ($this->input->post()) {
            $post = $this->input->post();
            if (isset($id)) {
                $this->Savings_accounts_model->update($id, $post);
            } else {
                $this->Savings_accounts_model->insert($post);
            }
            $this->session->set_flashdata('success','Cuenta guardada correctamente.');
            redirect('savings_accounts/savings_accounts');
        }
        $data['account'] = $id ? $this->Savings_accounts_model->get($id) : NULL;
        $this->load->view('savings_accounts/savings_accounts/form', $data);
    }

    public function delete($id = NULL)
    {
        if (is_null($id)) {
            $id = $this->uri->segment(4);
        }
        if ($id && $this->Savings_accounts_model->delete($id)) {
            $this->session->set_flashdata('success', 'Cuenta deshabilitada correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo deshabilitar la cuenta.');
        }
        redirect('savings_accounts/savings_accounts');
    }

    /**
     * Listado de cuentas inactivas (soft‐deleted).
     */
    public function inactive()
    {
        $data['accounts'] = $this->Savings_accounts_model->get_all(false);
        // Reutilizamos la misma vista, pero podemos pasar una bandera
        $data['show_inactive'] = true;
        $this->load->view('savings_accounts/savings_accounts/index', $data);
    }
}
