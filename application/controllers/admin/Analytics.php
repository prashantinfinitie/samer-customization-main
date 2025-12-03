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
     * Main Analytics Dashboard
     */
    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/dashboard';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Analytics Dashboard | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Analytics Dashboard';
            $this->data['currency'] = get_settings('currency');

            // Get sellers list for filter - use group_id = 4 for sellers, status = 1 for approved
            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)  // Sellers use group_id = 4, not 2
                ->where('sd.status', 1)     // Only approved sellers
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Sales & Revenue Reports
     */
    public function sales_revenue()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/sales-revenue';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Sales & Revenue Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Sales & Revenue Analytics';
            $this->data['currency'] = get_settings('currency');

            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Profitability Reports
     */
    public function profitability()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/profitability';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Profitability Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Profitability Analytics';
            $this->data['currency'] = get_settings('currency');

            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 2)
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Product & Catalog Reports
     */
    public function products_catalog()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/products-catalog';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Product & Catalog Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Product Analytics';
            $this->data['currency'] = get_settings('currency');

            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->where('sd.status', 1) // Only approved sellers
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Inventory & Purchasing Reports
     */
    public function inventory()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/inventory';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Inventory Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Inventory Analytics';
            $this->data['currency'] = get_settings('currency');

            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->where('sd.status', 1) // Only approved sellers
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Returns Dashboard
     */
    public function returns()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/returns';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Returns Analytics | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Returns Analytics';
            $this->data['currency'] = get_settings('currency');

            $this->data['sellers'] = $this->db->select('u.id as seller_id, u.username as seller_name, sd.store_name')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->where('sd.status', 1) // Only approved sellers
                ->get()
                ->result_array();

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Seller Comparison
     */
    public function seller_comparison()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = 'analytics/seller-comparison';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Seller Comparison | ' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Seller Comparison';
            $this->data['currency'] = get_settings('currency');

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    // =====================================================================
    // API ENDPOINTS FOR AJAX DATA LOADING
    // =====================================================================

    /**
     * Get Sales Overview Data (API)
     */
    public function get_sales_overview()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

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
     * Get Sales Time Series Data for Charts (API)
     */
    public function get_sales_time_series()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

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
     * Get Profit Report Data (API)
     */
    public function get_profit_report()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

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
     * Get Product Report Data (API)
     */
    public function get_product_report()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;
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
     * Get Category Report Data (API)
     */
    public function get_category_report()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

            $data = $this->Analytics_model->get_category_wise_report($period, $start_date, $end_date, $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Category report retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Top Products Table Data (API)
     */
    public function get_top_products_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;
            $limit = $this->input->get('limit') ?: 20;

            $data = $this->Analytics_model->get_product_wise_report($period, $start_date, $end_date, $seller_id, $limit, 0);

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

    /**
     * Get Top Sellers Table Data (API)
     */
    public function get_top_sellers_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');

            $data = $this->Analytics_model->get_seller_wise_report($period, $start_date, $end_date);

            // Format data for Bootstrap Table
            $rows = [];
            foreach ($data as $seller) {
                $rows[] = [
                    'seller_id' => $seller['seller_id'],
                    'seller_name' => $seller['seller_name'],
                    'store_name' => $seller['store_name'],
                    'total_revenue' => $seller['total_revenue'],
                    'total_profit' => $seller['total_profit'],
                    'total_orders' => $seller['total_orders'],
                    'total_units' => $seller['total_units'],
                    'average_margin' => $seller['average_margin'] . '%'
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

    /**
     * Get Seller Comparison Data (API)
     */
    public function get_seller_comparison()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');

            $data = $this->Analytics_model->get_seller_wise_report($period, $start_date, $end_date);

            $this->response['error'] = false;
            $this->response['message'] = 'Seller comparison retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }

    /**
     * Get Inventory Health Data (API)
     */
    public function get_inventory_health()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $seller_id = $this->input->get('seller_id') ?: null;

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
     * Get Purchase Suggestions (API)
     */
    public function get_purchase_suggestions()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $seller_id = $this->input->get('seller_id') ?: null;
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
     * Get Returns Dashboard Data (API)
     */
    public function get_returns_dashboard()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

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
     * Get Repeat Purchase Metrics (API)
     */
    public function get_repeat_purchase_metrics()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $period = $this->input->get('period') ?: 'monthly';
            $start_date = $this->input->get('start_date');
            $end_date = $this->input->get('end_date');
            $seller_id = $this->input->get('seller_id') ?: null;

            $date_range = get_analytics_date_range($period, $start_date, $end_date);
            $data = $this->Analytics_model->get_repeat_purchase_metrics($date_range['start'], $date_range['end'], $seller_id);

            $this->response['error'] = false;
            $this->response['message'] = 'Repeat purchase metrics retrieved successfully';
            $this->response['data'] = $data;
            echo json_encode($this->response);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            echo json_encode($this->response);
        }
    }
}

