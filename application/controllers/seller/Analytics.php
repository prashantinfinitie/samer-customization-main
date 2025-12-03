<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Analytics extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'analytics_helper']);
        $this->load->model(['Analytics_model', 'Sales_report_model']);
        $this->session->set_flashdata('authorize_flag', "");
    }

    /**
     * Seller Analytics Dashboard
     */
    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/dashboard';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Analytics Dashboard | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Seller Analytics Dashboard';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    /**
     * Sales & Revenue Reports
     */
    public function sales_revenue()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/sales-revenue';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Sales & Revenue Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Sales & Revenue Analytics';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    /**
     * Profitability Reports
     */
    public function profitability()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/profitability';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Profitability Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Profitability Analytics';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    /**
     * Product Reports
     */
    public function products()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/products';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Product Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Product Analytics';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    /**
     * Inventory Reports
     */
    public function inventory()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/inventory';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Inventory Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Inventory Analytics';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    /**
     * Returns Dashboard
     */
    public function returns()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $this->data['main_page'] = 'analytics/returns';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Returns Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Returns Analytics';
            $this->data['currency'] = get_settings('currency');
            $this->data['seller_id'] = $_SESSION['user_id'];

            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    // =====================================================================
    // API ENDPOINTS FOR AJAX DATA LOADING
    // =====================================================================

    /**
     * Get Sales Overview Data (API) - Seller
     */
    public function get_sales_overview()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data

            $data = $this->Analytics_model->get_sales_overview($period, $start_date, $end_date, $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Sales overview retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Profit Report Data (API) - Seller
     */
    public function get_profit_report()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data

            $data = $this->Analytics_model->get_profit_report($period, $start_date, $end_date, $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Profit report retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Product Report Data (API) - Seller
     */
    public function get_product_report()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data
            $limit = $this->input->get('limit') ?: 50;
            $offset = $this->input->get('offset') ?: 0;

            $data = $this->Analytics_model->get_product_wise_report($period, $start_date, $end_date, $seller_id, $limit, $offset);

            $this->response['error'] = false;
            $this->response['message'] = 'Product report retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Inventory Health Data (API) - Seller
     */
    public function get_inventory_health()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $seller_id = $_SESSION['user_id']; // Force seller's own data

            $data = $this->Analytics_model->get_inventory_health($seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Inventory health retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Purchase Suggestions (API) - Seller
     */
    public function get_purchase_suggestions()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $seller_id = $_SESSION['user_id']; // Force seller's own data
            $weeks_ahead = $this->input->get('weeks_ahead') ?: 4;

            $data = $this->Analytics_model->get_purchase_suggestions($seller_id, $weeks_ahead);

            $this->response['error'] = false;
            $this->response['message'] = 'Purchase suggestions retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Returns Dashboard Data (API) - Seller
     */
    public function get_returns_dashboard()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data

            $data = $this->Analytics_model->get_returns_dashboard($period, $start_date, $end_date, $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Returns dashboard retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Sales Time Series Data for Charts (API) - Seller
     */
    public function get_sales_time_series()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data

            $data = $this->Analytics_model->get_sales_time_series($period, $start_date, $end_date, $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Time series data retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Top Products Table Data (API) - Seller
     */
    public function get_top_products_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $_SESSION['user_id']; // Force seller's own data
            $limit = $this->input->get('limit') ?: 20;
            $offset = $this->input->get('offset') ?: 0;

            $data = $this->Analytics_model->get_product_wise_report($period, $start_date, $end_date, $seller_id, $limit, $offset);

            // Format data for Bootstrap Table
            $rows = [];
            foreach ($data as $product) {
                $rows[] = [
                    'product_name' => $product['product_name'],
                    'sku' => $product['sku'],
                    'total_sold' => $product['total_sold'],
                    'total_revenue' => $product['total_revenue'],
                    'total_profit' => $product['total_profit'],
                    'margin' => $product['margin'] . '%',
                    'avg_sale_price' => $product['avg_sale_price']
                ];
            }

            $this->response['total'] = count($rows);
            $this->response['rows'] = $rows;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }
}

