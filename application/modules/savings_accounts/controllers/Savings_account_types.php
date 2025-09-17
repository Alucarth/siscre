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
        $this->load->library('form_validation');

        $this->form_validation->set_rules('name','Nombre','required|trim');
        $this->form_validation->set_rules('interest_rate','Tasa de interés','required|numeric');
        $this->form_validation->set_rules('is_fixed_term','¿Plazo Fijo?','in_list[0,1]');
        // term_days será validado a posteriori si is_fixed_term=1

        if ($this->input->post()) {
            $post = $this->input->post();

            if ((int)($post['is_fixed_term'] ?? 0) === 1 && (int)($post['term_days'] ?? 0) <= 0) {
                $this->form_validation->set_rules('term_days','Plazo (días)','required|integer|greater_than[0]');
            }

            if ($this->form_validation->run()) {
                if ($id) {
                    $ok = $this->Savings_account_types_model->update($id, $post);
                } else {
                    $ok = $this->Savings_account_types_model->insert($post);
                }

                if ($ok) {
                    $this->session->set_flashdata('success', 'Tipo de cuenta guardado correctamente.');
                    return redirect('savings_accounts/savings_account_types');
                } else {
                    $db_err = $this->db->error();
                    $this->session->set_flashdata('error', 'No se pudo guardar. ' .
                        (!empty($db_err['message']) ? ('DB: '.$db_err['message']) : ''));
                }
            } else {
                $this->session->set_flashdata('error', validation_errors(' ', ' '));
            }
        }

        $data['type'] = $id ? $this->Savings_account_types_model->get($id) : NULL;
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
