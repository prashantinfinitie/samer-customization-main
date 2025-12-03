<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Store_model extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    /**
     * Add or update a store
     */
    function add_store($data, $store_id = null)
    {
        $data = escape_array($data);
        
        $store_data = [
            'vendor_id' => $data['vendor_id'],
            'store_name' => isset($data['store_name']) && !empty($data['store_name']) ? $data['store_name'] : '',
            'store_description' => isset($data['store_description']) && !empty($data['store_description']) ? $data['store_description'] : '',
            'logo' => isset($data['store_logo']) && !empty($data['store_logo']) ? $data['store_logo'] : '',
            'store_url' => isset($data['store_url']) && !empty($data['store_url']) ? $data['store_url'] : '',
            'category_ids' => isset($data['categories']) && !empty($data['categories']) ? $data['categories'] : null,
            'serviceable_zipcodes' => isset($data['serviceable_zipcodes']) && !empty($data['serviceable_zipcodes']) ? $data['serviceable_zipcodes'] : '',
            'serviceable_cities' => isset($data['serviceable_cities']) && !empty($data['serviceable_cities']) ? $data['serviceable_cities'] : '',
            'deliverable_zipcode_type' => isset($data['deliverable_zipcode_type']) && !empty($data['deliverable_zipcode_type']) ? $data['deliverable_zipcode_type'] : '',
            'deliverable_city_type' => isset($data['deliverable_city_type']) && !empty($data['deliverable_city_type']) ? $data['deliverable_city_type'] : '',
            'commission' => isset($data['global_commission']) && $data['global_commission'] != "" ? $data['global_commission'] : 0,
            'low_stock_limit' => isset($data['low_stock_limit']) && !empty($data['low_stock_limit']) ? $data['low_stock_limit'] : 0,
            'status' => isset($data['status']) && $data['status'] != "" ? $data['status'] : 2,
            'seo_page_title' => isset($data['seo_page_title']) && !empty($data['seo_page_title']) ? $data['seo_page_title'] : '',
            'seo_meta_keywords' => isset($data['seo_meta_keywords']) && !empty($data['seo_meta_keywords']) ? $data['seo_meta_keywords'] : '',
            'seo_meta_description' => isset($data['seo_meta_description']) && !empty($data['seo_meta_description']) ? $data['seo_meta_description'] : '',
            'seo_og_image' => isset($data['seo_og_image']) && !empty($data['seo_og_image']) ? $data['seo_og_image'] : '',
        ];

        // Generate slug if not provided
        if (isset($data['slug']) && !empty($data['slug'])) {
            $store_data['slug'] = $data['slug'];
        } else {
            $store_data['slug'] = url_title($store_data['store_name'], 'dash', TRUE) . '-' . time();
        }

        // Check if slug already exists
        if (!empty($store_id)) {
            $existing = $this->db->where('id !=', $store_id)->where('slug', $store_data['slug'])->get('stores')->row();
        } else {
            $existing = $this->db->where('slug', $store_data['slug'])->get('stores')->row();
        }
        
        if ($existing) {
            $store_data['slug'] = $store_data['slug'] . '-' . rand(1000, 9999);
        }

        if (!empty($store_id)) {
            // Update existing store
            $this->db->where('id', $store_id)->where('vendor_id', $data['vendor_id'])->update('stores', $store_data);
            return $store_id;
        } else {
            // Insert new store
            $this->db->insert('stores', $store_data);
            return $this->db->insert_id();
        }
    }

    /**
     * Get stores for a vendor
     */
    function get_vendor_stores($vendor_id, $status = null)
    {
        $this->db->where('vendor_id', $vendor_id);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('date_added', 'DESC');
        return $this->db->get('stores')->result_array();
    }

    /**
     * Get store by ID
     */
    function get_store($store_id, $vendor_id = null)
    {
        $this->db->where('id', $store_id);
        if ($vendor_id !== null) {
            $this->db->where('vendor_id', $vendor_id);
        }
        return $this->db->get('stores')->row_array();
    }

    /**
     * Get default store for vendor
     */
    function get_default_store($vendor_id)
    {
        $this->db->where('vendor_id', $vendor_id);
        $this->db->where('is_default', 1);
        $store = $this->db->get('stores')->row_array();
        
        // If no default store, get first store
        if (empty($store)) {
            $this->db->where('vendor_id', $vendor_id);
            $this->db->order_by('date_added', 'ASC');
            $store = $this->db->get('stores')->limit(1)->row_array();
        }
        
        return $store;
    }

    /**
     * Set default store
     */
    function set_default_store($store_id, $vendor_id)
    {
        // Remove default from all stores
        $this->db->where('vendor_id', $vendor_id)->update('stores', ['is_default' => 0]);
        
        // Set new default
        $this->db->where('id', $store_id)->where('vendor_id', $vendor_id)->update('stores', ['is_default' => 1]);
        return true;
    }

    /**
     * Delete store
     */
    function delete_store($store_id, $vendor_id)
    {
        // Check if store has products
        $product_count = $this->db->where('store_id', $store_id)->count_all_results('products');
        
        if ($product_count > 0) {
            return ['error' => true, 'message' => 'Cannot delete store with existing products'];
        }

        // Check if it's the default store
        $store = $this->get_store($store_id, $vendor_id);
        if (!empty($store) && $store['is_default'] == 1) {
            return ['error' => true, 'message' => 'Cannot delete default store'];
        }

        $this->db->where('id', $store_id)->where('vendor_id', $vendor_id)->delete('stores');
        return ['error' => false, 'message' => 'Store deleted successfully'];
    }

    /**
     * Get stores list for admin/vendor
     */
    function get_stores_list($vendor_id = null, $get_stores_list = "")
    {
        $offset = 0;
        $limit = 10;
        $sort = 's.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = [];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "s.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['s.`store_name`' => $search, 'u.`username`' => $search, 'u.`email`' => $search];
        }

        if ($vendor_id !== null) {
            $where['s.vendor_id'] = $vendor_id;
        }

        $count_res = $this->db->select(' COUNT(s.id) as `total` ')
            ->join('users u', 'u.id = s.vendor_id', 'left');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        if ($get_stores_list == "approved") {
            $count_res->where('s.status', '1');
        }
        if ($get_stores_list == "not_approved") {
            $count_res->where('s.status', '2');
        }
        if ($get_stores_list == "deactive") {
            $count_res->where('s.status', '0');
        }

        $offer_count = $count_res->get('stores s')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' s.*, u.username as vendor_name, u.email as vendor_email, u.mobile as vendor_mobile ')
            ->join('users u', 'u.id = s.vendor_id', 'left');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        if ($get_stores_list == "approved") {
            $search_res->where('s.status', '1');
        }
        if ($get_stores_list == "not_approved") {
            $search_res->where('s.status', '2');
        }
        if ($get_stores_list == "deactive") {
            $search_res->where('s.status', '0');
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('stores s')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = '';
            
            // Different actions for admin vs vendor
            if ($vendor_id === null) {
                // Admin view - show approval actions
                if ($row['status'] == 2) {
                    $operate .= '<a href="javascript:void(0)" class="approve-store btn action-btn btn-success btn-xs mr-2 mb-1" title="Approve Store" data-id="' . $row['id'] . '" ><i class="fa fa-check"></i></a>';
                }
                if ($row['status'] == 1) {
                    $operate .= '<a href="javascript:void(0)" class="reject-store btn action-btn btn-warning btn-xs mr-2 mb-1" title="Reject Store" data-id="' . $row['id'] . '" ><i class="fa fa-times"></i></a>';
                    $operate .= '<a href="javascript:void(0)" class="deactivate-store btn action-btn btn-danger btn-xs mr-2 mb-1" title="Deactivate Store" data-id="' . $row['id'] . '" ><i class="fa fa-ban"></i></a>';
                }
                if ($row['status'] == 0) {
                    $operate .= '<a href="javascript:void(0)" class="approve-store btn action-btn btn-success btn-xs mr-2 mb-1" title="Approve Store" data-id="' . $row['id'] . '" ><i class="fa fa-check"></i></a>';
                }
                $operate .= '<a href="javascript:void(0)" class="delete-store btn action-btn btn-danger btn-xs mr-2 mb-1" title="Delete Store" data-id="' . $row['id'] . '" ><i class="fa fa-trash"></i></a>';
            } else {
                // Vendor view - show edit/delete/set default
                $operate = " <a href='" . base_url('seller/store/create_store?edit_id=' . $row['id']) . "' data-id=" . $row['id'] . " class='btn action-btn btn-success btn-xs mr-2 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
                $operate .= '<a  href="javascript:void(0)" class="delete-store btn action-btn btn-danger btn-xs mr-2 mb-1" title="Delete Store"   data-id="' . $row['id'] . '" ><i class="fa fa-trash"></i></a>';
                
                if ($row['is_default'] == 0) {
                    $operate .= '<a  href="javascript:void(0)" class="set-default-store btn action-btn btn-primary btn-xs mr-2 mb-1" title="Set as Default"  data-id="' . $row['id'] . '" ><i class="fas fa-star"></i></a>';
                }
            }

            $tempRow['id'] = $row['id'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['vendor_name'] = $row['vendor_name'];
            $tempRow['vendor_email'] = $row['vendor_email'];
            $tempRow['vendor_mobile'] = $row['vendor_mobile'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';
            $tempRow['is_default'] = $row['is_default'] == 1 ? '<label class="badge badge-success">Default</label>' : '';

            // Store status
            if ($row['status'] == 2)
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            else if ($row['status'] == 1)
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            else if ($row['status'] == 0)
                $tempRow['status'] = "<label class='badge badge-danger'>Deactive</label>";
            else if ($row['status'] == 7)
                $tempRow['status'] = "<label class='badge badge-danger'>Removed</label>";

            if (empty($row['logo'])) {
                $row['logo_img'] = base_url() . NO_IMAGE;
            } else {
                $row['logo_img'] = base_url() . $row['logo'];
            }

            $tempRow['logo'] = '<div class="mx-auto product-image image-box-100"><a href=' . $row['logo_img'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo_img'] . ' class="rounded"></a></div>';

            // Get product count for this store
            $product_count = $this->db->where('store_id', $row['id'])->count_all_results('products');
            $tempRow['product_count'] = $product_count;

            $tempRow['date'] = date('d-m-Y', strtotime($row['date_added']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
    
}

