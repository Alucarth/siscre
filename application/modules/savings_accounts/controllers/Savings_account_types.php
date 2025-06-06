<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_account_types extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        //————— INYECCIÓN PARA EL SIDEBAR —————
        $this->load->model('Employee');
        $this->load->model('Module');
        $this->load->model('Message'); 


        $user_info       = $this->Employee->get_logged_in_employee_info();
        if (!is_object($user_info)) {
            redirect('login');
        }
        $allowed_modules = $this->Module->get_allowed_modules($user_info->person_id);

        $messages = [];
        $alerts   = [];
        
        $this->load->vars(compact('user_info','allowed_modules', 'messages', 'alerts'));
        //——————————————————————————————————————

        // Modelo del módulo
        $this->load->model('savings_accounts/Savings_account_types_model');
    }

    public function index()
    {
        $data['types'] = $this->Savings_account_types_model->get_all();
        $this->load->view('savings_account_types/index', $data);
    }

    public function form($id = NULL)
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            unset($post['code']);

            if ($id) {
                $this->Savings_account_types_model->update($id, $post);
            } else {
                $this->Savings_account_types_model->insert($post);
            }
            $this->session->set_flashdata('success', 'Tipo guardado correctamente.');
            redirect('savings_accounts/savings_account_types');
        }

        $data['type'] = $id 
            ? $this->Savings_account_types_model->get($id) 
            : NULL;
        $this->load->view('savings_account_types/form', $data);
    }

    public function delete($id = NULL)
    {
        // Si no vino por parámetro, lo tomamos de la URL
        if (is_null($id)) {
            $id = $this->uri->segment(4);
        }

        if ($id && $this->Savings_account_types_model->delete($id)) {
            $this->session->set_flashdata('success', 'Tipo de cuenta deshabilitado correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo deshabilitar el tipo de cuenta.');
        }

        redirect('savings_accounts/savings_account_types');
    }

}
