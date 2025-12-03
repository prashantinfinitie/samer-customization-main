<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Store extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model(['Store_model', 'Category_model']);
    }

    public function index()
    {
        // Temporary debug - uncomment to test if route is working
        // die('STORE CONTROLLER IS WORKING! Route matched successfully.');
        
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            $this->data['main_page'] = TABLES . 'manage-store';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Store Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Store Management | ' . $settings['app_name'];
            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function create_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            $this->data['main_page'] = FORMS . 'store';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Add Store | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Add Store | ' . $settings['app_name'];
            $this->data['vendor_id'] = $vendor_id;
            $this->data['categories'] = json_decode(json_encode($this->Category_model->get_seller_categories($vendor_id)), 1);
            $this->data['cities'] = fetch_details('cities', "", 'name,id', '5');
            
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->data['title'] = 'Update Store | ' . $settings['app_name'];
                $this->data['meta_description'] = 'Update Store | ' . $settings['app_name'];
                $store_details = $this->Store_model->get_store($_GET['edit_id'], $vendor_id);
                if (empty($store_details)) {
                    redirect('seller/store', 'refresh');
                }
                $this->data['store_details'] = $store_details;
            }
            
            $this->load->view('seller/template', $this->data);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function add_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            
            $this->form_validation->set_rules('store_name', 'Store Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('store_description', 'Store Description', 'trim|xss_clean');
            $this->form_validation->set_rules('store_url', 'Store URL', 'trim|xss_clean');
            
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['message'] = validation_errors();
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return false;
            }

            $data = $this->input->post(null, true);
            $data['vendor_id'] = $vendor_id;

            // Handle file uploads
            if (isset($_FILES['store_logo']['name']) && !empty($_FILES['store_logo']['name'])) {
                $upload_path = FCPATH . 'uploads/stores/';
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = 5000;
                $config['file_name'] = 'store_logo_' . time();
                $this->upload->initialize($config);
                
                if ($this->upload->do_upload('store_logo')) {
                    $upload_data = $this->upload->data();
                    $data['store_logo'] = 'uploads/stores/' . $upload_data['file_name'];
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = $this->upload->display_errors();
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
            }

            // Handle categories
            if (isset($_POST['categories']) && !empty($_POST['categories'])) {
                if (is_array($_POST['categories'])) {
                    $data['categories'] = implode(",", $_POST['categories']);
                } else {
                    $data['categories'] = $_POST['categories'];
                }
            }

            // Handle serviceable zipcodes and cities
            if (isset($_POST['serviceable_zipcodes']) && !empty($_POST['serviceable_zipcodes'])) {
                if (is_array($_POST['serviceable_zipcodes'])) {
                    $data['serviceable_zipcodes'] = implode(",", $_POST['serviceable_zipcodes']);
                } else {
                    $data['serviceable_zipcodes'] = $_POST['serviceable_zipcodes'];
                }
            }

            if (isset($_POST['serviceable_cities']) && !empty($_POST['serviceable_cities'])) {
                if (is_array($_POST['serviceable_cities'])) {
                    $data['serviceable_cities'] = implode(",", $_POST['serviceable_cities']);
                } else {
                    $data['serviceable_cities'] = $_POST['serviceable_cities'];
                }
            }

            $store_id = isset($_POST['store_id']) && !empty($_POST['store_id']) ? $_POST['store_id'] : null;
            $insert_id = $this->Store_model->add_store($data, $store_id);

            if (!empty($insert_id)) {
                $this->response['error'] = false;
                $this->response['message'] = isset($_POST['store_id']) ? 'Store updated successfully' : 'Store added successfully';
                $this->response['data'] = array('store_id' => $insert_id);
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to add store';
                $this->response['data'] = array();
            }
            
            print_r(json_encode($this->response));
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function get_stores()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            $this->Store_model->get_stores_list($vendor_id);
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function delete_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            $store_id = $this->input->post('id', true);
            
            if (empty($store_id)) {
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                print_r(json_encode($this->response));
                return false;
            }

            $result = $this->Store_model->delete_store($store_id, $vendor_id);
            
            if ($result['error']) {
                $this->response['error'] = true;
                $this->response['message'] = $result['message'];
            } else {
                $this->response['error'] = false;
                $this->response['message'] = $result['message'];
            }
            
            print_r(json_encode($this->response));
        } else {
            redirect('seller/login', 'refresh');
        }
    }

    public function set_default_store()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_seller() && ($this->ion_auth->seller_status() == 1 || $this->ion_auth->seller_status() == 0)) {
            $vendor_id = $this->session->userdata('user_id');
            $store_id = $this->input->post('id', true);
            
            if (empty($store_id)) {
                $this->response['error'] = true;
                $this->response['message'] = 'Store ID is required';
                print_r(json_encode($this->response));
                return false;
            }

            $result = $this->Store_model->set_default_store($store_id, $vendor_id);
            
            if ($result) {
                $this->response['error'] = false;
                $this->response['message'] = 'Default store updated successfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Failed to update default store';
            }
            
            print_r(json_encode($this->response));
        } else {
            redirect('seller/login', 'refresh');
        }
    }
}

