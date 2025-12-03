<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fund_transfer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model('Fund_transfers_model');

        if (!has_permissions('read', 'fund_transfer')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-fund-transfers';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'View Fund Transfer | ' . $settings['app_name'];
            $this->data['meta_description'] = ' View Fund Transfer  | ' . $settings['app_name'];
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('delivery_boys', ['id' => $_GET['edit_id'], 'status' => '1']);
            }
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_fund_transfer()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('create', 'fund_transfer')) {
                return false;
            }

            // Determine target user type (optional, default to delivery_boy for backward compatibility)
            $user_type = $this->input->post('user_type', true);
            if (empty($user_type)) {
                $user_type = 'delivery_boy';
            }

            // Set validation rules depending on type
            if ($user_type === 'shipping_company') {
                $this->form_validation->set_rules('shipping_company_id', 'Shipping Company', 'trim|required|xss_clean|numeric');
            } else {
                $this->form_validation->set_rules('delivery_boy_id', 'Delivery Boy', 'trim|required|xss_clean|numeric');
            }

            $this->form_validation->set_rules('transfer_amt', 'Transfer Amount', 'trim|required|xss_clean|numeric');
            $this->form_validation->set_rules('message', 'Message', 'trim|xss_clean');

            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                echo json_encode($this->response);
                return false;
            }

            $transfer_amt = (float)$this->input->post('transfer_amt', true);
            $message = $this->input->post('message', true);

            // --- Shipping company flow ---
            if ($user_type === 'shipping_company') {
                $company_id = (int)$this->input->post('shipping_company_id', true);

                if (!is_exist(['id' => $company_id], 'users')) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Shipping Company does not exist in your database';
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    echo json_encode($this->response);
                    return false;
                }

                $res = fetch_details('users', ['id' => $company_id], 'balance');

                $current_balance = isset($res[0]['balance']) ? (float)$res[0]['balance'] : 0.00;
                if ($current_balance <= 0) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Balance should be greater than 0';
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    echo json_encode($this->response);
                    return false;
                }

                if ($transfer_amt > $current_balance) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Transfer amount should be less than ' . $current_balance;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    echo json_encode($this->response);
                    return false;
                }

                // debit wallet (you already use this for delivery boys)
                update_wallet_balance('debit', $company_id, $transfer_amt);

                // insert fund transfer record (shipping company specific)
                $this->Fund_transfers_model->set_fund_transfer_shipping_company($company_id, $transfer_amt, $current_balance, 'success', $message);

                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'Amount Successfully Transferred to Shipping Company';
                echo json_encode($this->response);
                return false;
            }

            // --- Delivery boy flow (existing behaviour) ---
            $delivery_boy_id = $this->input->post('delivery_boy_id', true);
            if (!is_exist(['id' => $delivery_boy_id], 'users')) {
                $this->response['error'] = true;
                $this->response['message'] = 'Delivery Boy does not exist in your database';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return false;
            }

            $res = fetch_details('users', ['id' => $delivery_boy_id], 'balance');
            $current_balance = isset($res[0]['balance']) ? (float)$res[0]['balance'] : 0.00;

            if ($current_balance <= 0) {
                $this->response['error'] = true;
                $this->response['message'] = 'Balance should be greater than 0';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return false;
            }

            if ($transfer_amt > $current_balance) {
                $this->response['error'] = true;
                $this->response['message'] = 'Transfer amount should be less than ' . $current_balance;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return false;
            }

            update_wallet_balance('debit', $delivery_boy_id, $transfer_amt);
            $this->Fund_transfers_model->set_fund_transfer($delivery_boy_id, $transfer_amt, $current_balance, 'success', $message);

            $this->response['error'] = false;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = 'Amount Successfully Transferred';
            echo json_encode($this->response);
            return false;
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function view_fund_transfers()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            // If client requests shipping_company data, call that model method.
            $user_type = $this->input->get('user_type', true);
            if (!empty($user_type) && $user_type === 'shipping_company') {
                return $this->Fund_transfers_model->get_fund_transfers_list_shipping_company();
            }
            // Default: delivery_boy list
            return $this->Fund_transfers_model->get_fund_transfers_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
