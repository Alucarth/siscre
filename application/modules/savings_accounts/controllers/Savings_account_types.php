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

        $user_info = $this->Employee->get_logged_in_employee_info();
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

        // Normalizar ID
        $id = $id !== NULL ? (int)$id : NULL;

        // Cargar tipo actual (si edición)
        $current = NULL;
        if ($id) {
            $current = $this->Savings_account_types_model->get($id);
            if (!$current) {
                $this->session->set_flashdata('error', 'Tipo de cuenta no encontrado.');
                redirect('savings_accounts/savings_account_types');
                return;
            }
        }

        // ¿Este tipo ya tiene cuentas asociadas? → solo para mostrar aviso en la vista
        $locked = false;
        if ($id) {
            $locked = $this->Savings_account_types_model->has_accounts($id);
        }

        if ($this->input->post()) {
            $post = $this->input->post();

            /* ===========================
               ALTA (id = NULL)
               =========================== */
            if ($id === NULL) {

                // Reglas para creación completa
                $this->form_validation->set_rules('name','Nombre','required|trim');
                $this->form_validation->set_rules('interest_rate','Tasa de interés','required|numeric');
                $this->form_validation->set_rules('is_fixed_term','¿Plazo Fijo?','in_list[0,1]');

                if ((int)($post['is_fixed_term'] ?? 0) === 1 && (int)($post['term_days'] ?? 0) <= 0) {
                    $this->form_validation->set_rules(
                        'term_days',
                        'Plazo (días)',
                        'required|integer|greater_than[0]'
                    );
                }

                if ($this->form_validation->run()) {
                    $ok = $this->Savings_account_types_model->insert($post);

                    if ($ok) {
                        $this->session->set_flashdata('success', 'Tipo de cuenta guardado correctamente.');
                        redirect('savings_accounts/savings_account_types');
                        return;
                    }

                    // Error del modelo (p.ej. nombre duplicado)
                    $model_err = property_exists($this->Savings_account_types_model, 'last_error')
                        ? trim((string)$this->Savings_account_types_model->last_error)
                        : '';

                    if ($model_err !== '') {
                        $this->session->set_flashdata('error', $model_err);
                    } else {
                        $db_err = $this->db->error();
                        $this->session->set_flashdata(
                            'error',
                            'No se pudo guardar. ' .
                            (!empty($db_err['message']) ? ('DB: '.$db_err['message']) : '')
                        );
                    }
                } else {
                    $this->session->set_flashdata('error', validation_errors(' ', ' '));
                }

            /* ===========================
               EDICIÓN (id != NULL)
               SOLO: descripción + estado
               =========================== */
            } else {

                // Validación ligera (opcional)
                // status: si viene, que sea 0 o 1
                if (array_key_exists('status', $post)) {
                    $post['status'] = (int)$post['status'];
                    if (!in_array($post['status'], [0,1], true)) {
                        $this->session->set_flashdata('error', 'Estado inválido.');
                        redirect(current_url());
                        return;
                    }
                }

                // Armamos el payload solo con campos editables
                $payload = [
                    'description' => $post['description'] ?? $current->description,
                ];

                if (array_key_exists('status', $post)) {
                    $payload['status'] = $post['status'];
                }

                $ok = $this->Savings_account_types_model->update_meta($id, $payload);

                if ($ok) {
                    $msg = 'Tipo de cuenta guardado correctamente.';
                    if ($locked) {
                        $msg .= ' (Solo se actualizaron descripción y estado porque el tipo ya tiene cuentas asociadas.)';
                    }
                    $this->session->set_flashdata('success', $msg);
                    redirect('savings_accounts/savings_account_types');
                    return;
                }

                // Error en update_meta (muy raro, pero lo manejamos)
                $model_err = property_exists($this->Savings_account_types_model, 'last_error')
                    ? trim((string)$this->Savings_account_types_model->last_error)
                    : '';

                if ($model_err !== '') {
                    $this->session->set_flashdata('error', $model_err);
                } else {
                    $db_err = $this->db->error();
                    $this->session->set_flashdata(
                        'error',
                        'No se pudo guardar. ' .
                        (!empty($db_err['message']) ? ('DB: '.$db_err['message']) : '')
                    );
                }
            }
        }

        $data['type']   = $current;
        $data['locked'] = $locked; // para que la vista deshabilite los campos no editables

        $this->load->view('savings_account_types/form', $data);
    }

    public function delete($id = NULL)
    {
        // Si no vino por parámetro, lo tomamos de la URL
        if (is_null($id)) {
            $id = $this->uri->segment(4);
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->session->set_flashdata('error', 'ID de tipo de cuenta inválido.');
            redirect('savings_accounts/savings_account_types');
            return;
        }

        $result = $this->Savings_account_types_model->toggle_status($id);

        if ($result === 1) {
            // Cambió a HABILITADO
            $this->session->set_flashdata('success', 'Tipo de cuenta habilitado correctamente.');
        } elseif ($result === 0) {
            // Cambió a DESHABILITADO
            $this->session->set_flashdata('success', 'Tipo de cuenta deshabilitado correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo cambiar el estado del tipo de cuenta.');
        }

        redirect('savings_accounts/savings_account_types');
    }

}



