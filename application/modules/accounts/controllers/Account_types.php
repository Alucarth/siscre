<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'modules/accounts/controllers/Accounts.php';


class Account_types extends Accounts {
    public function __construct() {
        parent::__construct();
        $this->load->model('Account_types_model');
        $this->load->helper('app');  // carga la función set_alert()
    }

    public function index() {
        $data['types'] = $this->Account_types_model->get_all();
        $this->load->view('account_types/index', $data);
    }

    public function form($id = NULL) {
        if ($this->input->post()) {
            $post = $this->input->post();
            $save = $id
                ? $this->Account_types_model->update($id, $post)
                : $this->Account_types_model->insert($post);

            if ($save) {
                $this->session->set_flashdata('success', 'Tipo de cuenta guardado correctamente.');
                redirect('accounts/account_types');
            }
        }
        $data['type'] = $id ? $this->Account_types_model->get($id) : NULL;
        $this->load->view('account_types/form', $data);
    }

    public function delete() {
        $id = $this->uri->segment(4);

        // Verificar si hay cuentas asociadas antes de “deshabilitar”
        $this->load->model('Account_model');
        $count = $this->Account_model->count_by_type($id);

        if ($count > 0) {
            $this->session->set_flashdata('error',
                "No se puede eliminar: hay $count cuentas de ahorro usando este tipo.");
        } else {
            // Cambiamos status a 0 en lugar de borrar
            $this->Account_types_model->update($id, ['status'=>0]);
            $this->session->set_flashdata('success',
                'Tipo de cuenta deshabilitado correctamente.');
        }

        redirect('accounts/account_types');
    }
}
