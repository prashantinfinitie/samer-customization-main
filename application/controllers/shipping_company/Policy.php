<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Policy extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth']);
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_shipping_company()) {
            $this->data['main_page'] = TABLES . 'privacy-policy';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Privacy Policy | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Privacy Policy | ' . $settings['app_name'];
            // Use shipping company privacy policy if exists, else use general one
            $this->data['privacy_policy'] = get_settings('shipping_company_privacy_policy');
            if (empty($this->data['privacy_policy'])) {
                $this->data['privacy_policy'] = get_settings('privacy_policy');
            }
            $this->load->view('shipping_company/template', $this->data);
        } else {
            redirect('shipping-company/login', 'refresh');
        }
    }

    public function terms_conditions()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_shipping_company()) {
            $this->data['main_page'] = TABLES . 'terms-conditions';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Terms And Conditions | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Terms And Conditions | ' . $settings['app_name'];
            // Use shipping company terms if exists, else use general one
            $this->data['terms_n_condition'] = get_settings('shipping_company_terms_conditions');
            if (empty($this->data['terms_n_condition'])) {
                $this->data['terms_n_condition'] = get_settings('terms_conditions');
            }
            $this->load->view('shipping_company/template', $this->data);
        } else {
            redirect('shipping-company/login', 'refresh');
        }
    }
}

