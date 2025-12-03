<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stores extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model('Store_model');

        if (!has_permissions('read', 'seller')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-store';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Store Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Store Management | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_stores()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $status_filter = isset($_GET['store_status']) && !empty($_GET['store_status']) ? $_GET['store_status'] : '';
            $this->Store_model->get_stores_list(null, $status_filter);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function approve_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'seller'), PERMISSION_ERROR_MSG, 'store', false)) {
                return false;
            }

            $store_id = $this->input->post('id', true);
            if (empty($store_id)) {
                $this->response = array();
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                print_r(json_encode($this->response));
                return false;
            }

            $this->response = array();
            $result = update_details(['status' => 1], ['id' => $store_id], 'stores');
            if ($result) {
                $this->response['error'] = false;
                $this->response['message'] = 'Store approved successfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to approve store';
            }
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function reject_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'seller'), PERMISSION_ERROR_MSG, 'store', false)) {
                return false;
            }

            $store_id = $this->input->post('id', true);
            if (empty($store_id)) {
                $this->response = array();
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                print_r(json_encode($this->response));
                return false;
            }

            $this->response = array();
            $result = update_details(['status' => 2], ['id' => $store_id], 'stores');
            if ($result) {
                $this->response['error'] = false;
                $this->response['message'] = 'Store rejected successfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to reject store';
            }
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function deactivate_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'seller'), PERMISSION_ERROR_MSG, 'store', false)) {
                return false;
            }

            $store_id = $this->input->post('id', true);
            if (empty($store_id)) {
                $this->response = array();
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                print_r(json_encode($this->response));
                return false;
            }

            $this->response = array();
            $result = update_details(['status' => 0], ['id' => $store_id], 'stores');
            if ($result) {
                $this->response['error'] = false;
                $this->response['message'] = 'Store deactivated successfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to deactivate store';
            }
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('delete', 'seller'), PERMISSION_ERROR_MSG, 'store', false)) {
                return false;
            }

            $store_id = $this->input->post('id', true);
            if (empty($store_id)) {
                $this->response = array();
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                print_r(json_encode($this->response));
                return false;
            }

            $this->response = array();
            // Check if store has products
            $product_count = $this->db->where('store_id', $store_id)->count_all_results('products');
            if ($product_count > 0) {
                $this->response['error'] = true;
                $this->response['message'] = 'Cannot delete store with existing products. Please remove products first.';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                print_r(json_encode($this->response));
                return false;
            }

            if ($this->db->where('id', $store_id)->delete('stores')) {
                $this->response['error'] = false;
                $this->response['message'] = 'Store deleted successfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to delete store';
            }
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}

