<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Seller_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    function add_seller($data, $profile = [], $com_data = [])
    {
        $data = escape_array($data);
        $profile = (!empty($profile)) ? escape_array($profile) : [];
        $com_data = (!empty($com_data)) ? escape_array($com_data) : [];

        $seller_data = [
            'user_id' => $data['user_id'],
            'national_identity_card' => (isset($data['national_identity_card']) && !empty($data['national_identity_card'])) ? $data['national_identity_card'] : '',
            'address_proof' => (isset($data['address_proof']) && !empty($data['address_proof'])) ?  $data['address_proof'] : '',
            'logo' => $data['store_logo'],
            'authorized_signature' => (isset($data['authorized_signature']) && !empty($data['authorized_signature'])) ? $data['authorized_signature'] : '',
            'status' => (isset($data['status']) && $data['status'] != "") ? $data['status'] : 2,
            'pan_number' => (isset($data['pan_number']) && !empty($data['pan_number'])) ? $data['pan_number'] : '',
            'tax_number' => (isset($data['tax_number']) && !empty($data['tax_number'])) ? $data['tax_number'] : '',
            'tax_name' => (isset($data['tax_name']) && !empty($data['tax_name'])) ? $data['tax_name'] : '',
            'bank_name' => (isset($data['bank_name']) && !empty($data['bank_name'])) ? $data['bank_name'] : '',
            'bank_code' => (isset($data['bank_code']) && !empty($data['bank_code'])) ? $data['bank_code'] : '',
            'account_name' => (isset($data['account_name']) && !empty($data['account_name'])) ? $data['account_name'] : '',
            'account_number' => (isset($data['account_number']) && !empty($data['account_number'])) ? $data['account_number'] : '',
            'store_description' => (isset($data['store_description']) && !empty($data['store_description'])) ?  $data['store_description'] : '',
            'store_url' => (isset($data['store_url']) && !empty($data['store_url'])) ? $data['store_url'] : '',
            'store_name' => (isset($data['store_name']) && !empty($data['store_name'])) ? $data['store_name'] : '',
            'commission' => (isset($data['global_commission']) && $data['global_commission'] != "") ? $data['global_commission'] : 0,
            'category_ids' => (isset($data['categories']) && $data['categories'] != "") ? $data['categories'] : null,
            'permissions' => (isset($data['permissions']) && $data['permissions'] != "") ? json_encode($data['permissions']) : null,
            'serviceable_zipcodes' => isset($data['serviceable_zipcodes']) && !empty($data['serviceable_zipcodes']) ? $data['serviceable_zipcodes'] : '',
            'serviceable_cities' => isset($data['serviceable_cities']) && !empty($data['serviceable_cities']) ? $data['serviceable_cities'] : '',
            'deliverable_zipcode_type' => isset($data['deliverable_zipcode_type']) && !empty($data['deliverable_zipcode_type']) ? $data['deliverable_zipcode_type'] : '',
            'deliverable_city_type' => isset($data['deliverable_city_type']) && !empty($data['deliverable_city_type']) ? $data['deliverable_city_type'] : '',
            'low_stock_limit' => isset($data['low_stock_limit']) && !empty($data['low_stock_limit']) ? $data['low_stock_limit'] : 0,
            'slug' => isset($data['slug']) && !empty($data['slug']) ? $data['slug'] : '',
            'seo_page_title' => isset($data['seo_page_title']) && !empty($data['seo_page_title']) ? $data['seo_page_title'] : '',
            'seo_meta_keywords' => isset($data['seo_meta_keywords']) && !empty($data['seo_meta_keywords']) ? $data['seo_meta_keywords'] : '',
            'seo_meta_description' => isset($data['seo_meta_description']) && !empty($data['seo_meta_description']) ? $data['seo_meta_description'] : '',
            'seo_og_image' => isset($data['seo_og_image']) && !empty($data['seo_og_image']) ? $data['seo_og_image'] : '',
        ];

        if (isset($data['edit_seller_data_id']) && !empty($data['edit_seller_data_id'])) {
            $seller = fetch_details('seller_data', ['id' => $data['edit_seller_data_id']]);
            $seller_data['slug'] = $seller[0]['slug'];
        } else {
            $seller_data['slug'] = $data['slug'];
        }
        if (isset($data['categories']) && $data['categories'] == "seller_profile") {
            unset($seller_data['category_ids']);
            unset($seller_data['permissions']);
        }

        if (!empty($profile)) {

            $seller_profile = [
                'username' => $profile['name'],
                'email' => $profile['email'],
                'mobile' => $profile['mobile'],
                'address' => $profile['address'],
                'latitude' => $profile['latitude'],
                'longitude' => $profile['longitude'],
                'image' => (isset($profile['image']) && !empty($profile['image'])) ? $profile['image'] : '',
            ];
        }
        if (isset($data['edit_seller_data_id']) && !empty($data['edit_seller_data_id'])) {
            if (!empty($com_data)) {
                // process update commissions and categories
                delete_details(['seller_id' => $com_data[0]['seller_id']], 'seller_commission');
                $this->db->insert_batch('seller_commission', $com_data);
            }
            if ($this->db->set($seller_profile)->where('id', $data['user_id'])->update('users')) {
                $this->db->set($seller_data)->where('id', $data['edit_seller_data_id'])->update('seller_data');
                return true;
            } else {
                return false;
            }
        } else {
            if (!empty($com_data)) {
                $this->db->insert_batch('seller_commission', $com_data);
            }
            $this->db->insert('seller_data', $seller_data);
            $insert_id = $this->db->insert_id();
            if (!empty($insert_id)) {
                return  $insert_id;
            } else {
                return false;
            }
        }
    }

    function create_slug($data)
    {
        $data = escape_array($data);
        $this->db->set($data)->where('id', $data['id'])->update('seller_data');
    }

    function get_sellers_list($get_sellers_list = "")
    {

        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }

        if ($get_sellers_list == "approved") {
            $count_res->where('sd.status', '1');
        }
        if ($get_sellers_list == "not_approved") {
            $count_res->where('sd.status', '2');
        }
        if ($get_sellers_list == "deactive") {
            $count_res->where('sd.status', '0');
        }
        if ($get_sellers_list == "removed") {
            $count_res->where('sd.status', '7');
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.* ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        if ($get_sellers_list == "approved") {
            $search_res->where('sd.status', '1');
        }
        if ($get_sellers_list == "not_approved") {
            $search_res->where('sd.status', '2');
        }
        if ($get_sellers_list == "deactive") {
            $search_res->where('sd.status', '0');
        }
        if ($get_sellers_list == "removed") {
            $search_res->where('sd.status', '7');
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = " <a href='manage-seller?edit_id=" . $row['user_id'] . "' data-id=" . $row['user_id'] . " class='btn action-btn btn-success btn-xs mr-2 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            $operate .= '<a  href="javascript:void(0)" class="delete-sellers btn action-btn btn-danger btn-xs mr-2 mb-1" title="Delete Seller with related seller data "   data-id="' . $row['user_id'] . '" ><i class="fa fa-trash"></i></a>';
            if ($row['status'] == '1' || $row['status'] == '0' || $row['status'] == '2') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers action-btn btn btn-warning btn-xs mr-2 mb-1" title="Remove Seller only"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user-slash"></i></a>';
            } else if ($row['status'] == '7') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers action-btn btn btn-primary btn-xs mr-2 mb-1" title="Restore Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user"></i></a>';
            }
            $operate .= '<a href="' . base_url('admin/orders?seller_id=' . $row['user_id']) . '" class="btn action-btn btn-primary btn-xs mr-2 mb-1" title="View Orders" ><i class="fa fa-eye"></i></a>';

            $tempRow['id'] = $row['user_id'];
            $tempRow['name'] = $row['username'];
            if (isset($row['email']) && !empty($row['email']) && $row['email'] != "" && $row['email'] != " ") {
                $tempRow['email'] = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['email']) - 3) . substr($row['email'], -3) : ucfirst($row['email']);
            } else {
                $tempRow['email'] = "";
            }
            if (isset($row['mobile']) && !empty($row['mobile']) && $row['mobile'] != "" && $row['mobile'] != " ") {
                $tempRow['mobile'] =  (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['mobile']) - 3) . substr($row['mobile'], -3) : $row['mobile'];
            } else {
                $tempRow['mobile'] = "";
            }
            $tempRow['address'] = $row['address'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';;
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['pan_number'] = $row['pan_number'];

            // seller status
            if ($row['status'] == 2)
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            else if ($row['status'] == 1)
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            else if ($row['status'] == 0)
                $tempRow['status'] = "<label class='badge badge-danger'>Deactive</label>";
            else if ($row['status'] == 7)
                $tempRow['status'] = "<label class='badge badge-danger'>Removed</label>";

            $categories = explode(",", $row['category_ids']);
            $category_names = array();

            if (isset($categories) && count($categories) > 0 && !empty($categories)) {
                foreach ($categories as $category_id) {
                    $category = fetch_details('categories', ['id' => $category_id], 'name');
                    if (!empty($category)) {
                        $category_names[] = str_replace('\\', ' ', $category[0]['name']);
                    }
                }
            }

            $tempRow['category_ids'] = implode(' , ', $category_names);

            if (empty($row['logo'])) {
                $row['logo_img'] = base_url() . NO_IMAGE;
            } else {
                $row['logo_img'] = base_url() . $row['logo'];
            }

            $tempRow['logo'] = '<div class="mx-auto product-image image-box-100"><a href=' . $row['logo_img'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo_img'] . ' class="rounded"></a></div>';

            if (empty($row['national_identity_card'])) {
                $row['national_identity_card'] = base_url() . NO_IMAGE;
            } else {
                $row['national_identity_card'] = base_url() . $row['national_identity_card'];
            }
            $row['national_identity_card'] = get_image_url($row['national_identity_card']);
            $tempRow['national_identity_card'] = '<div class="mx-auto product-image image-box-100"><a href=' . $row['national_identity_card'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['national_identity_card'] . ' class="rounded"></a></div>';

            if (empty($row['address_proof'])) {
                $row['address_proof'] = base_url() . NO_IMAGE;
            } else {
                $row['address_proof'] = base_url() . $row['address_proof'];
            }
            $row['address_proof'] = get_image_url($row['address_proof']);
            $tempRow['address_proof'] = '<div class="mx-auto product-image image-box-100"><a href=' . $row['address_proof'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['address_proof'] . ' class="rounded"></a></div>';

            $permissions = $row['permissions'];
            $permissions_data = json_decode($permissions, true);

            if (isset($permissions_data) && !empty($permissions_data)) {
                $permissions_html = ''; // Initialize an empty string to store permissions HTML

                foreach ($permissions_data as $key => $value) {
                    $permissions_key = str_replace('_', ' ', $key);
                    $permissions_value = ($value == 1) ? "<label class='badge badge-success'>Yes</label>" : "<label class='badge badge-danger'>No</label>";
                    // Concatenate each permission to the HTML string
                    $permissions_html .= '<div class="' . htmlspecialchars($permissions_key) . '">' . htmlspecialchars($permissions_key) . ' -> ' . $permissions_value . '</div>';
                }
                // Assign the concatenated HTML string to $row['permissions']
                $row['permissions'] = $permissions_html;
            }

            $tempRow['permissions'] = $row['permissions'];
            $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : number_format($row['balance'], 2);
            $tempRow['date'] = date('d-m-Y', strtotime($row['created_at']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function update_balance($amount, $seller_id, $action)
    {
        /**
         * @param
         * action = deduct / add
         */

        if ($action == "add") {
            $this->db->set('balance', 'balance+' . $amount, FALSE);
        } elseif ($action == "deduct") {
            $this->db->set('balance', 'balance-' . $amount, FALSE);
        }
        return $this->db->where('id', $seller_id)->update('users');
    }
    public function get_sellers($zipcode_id = "", $limit = NULL, $offset = '', $sort = 'u.id', $order = 'DESC', $search = NULL, $filter = [])
    {
        $multipleWhere = '';
        $where = ['u.active' => 1, 'sd.status' => 1];
        if (isset($filter) && !empty($filter['slug']) && $filter['slug'] != "") {
            $where['sd.slug'] = $filter['slug'];
        }
        if (isset($_POST['seller_id']) && !empty($_POST['seller_id']) && $_POST['seller_id'] != "") {
            $where['sd.user_id'] = $_POST['seller_id'];
        }
        if (isset($search) && $search != '') {
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search, 'sd.`store_name`' => $search];
        }

        $count_res = $this->db->select(' COUNT(DISTINCT u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ')->join('products p', ' p.seller_id = u.id AND p.status = 1 ', 'left');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }
        if (isset($filter) && !empty($filter['top_rated_seller']) && strtolower($filter['top_rated_seller']) == 'top_rated_seller') {
            $sort = null;
            $order = null;
            $count_res->order_by("sd.rating", "desc");
            $count_res->order_by("sd.no_of_ratings", "desc");
        }
        if (isset($zipcode_id) && !empty($zipcode_id) && $zipcode_id != "") {
            $this->db->group_Start();
            $where2 = "((deliverable_type='2' and FIND_IN_SET('$zipcode_id', deliverable_zipcodes)) or deliverable_type = '1') OR (deliverable_type='3' and NOT FIND_IN_SET('$zipcode_id', deliverable_zipcodes)) ";
            $this->db->where($where2);
            $this->db->group_End();
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.*,u.id as seller_id ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ')->join('products p', ' p.seller_id = u.id AND p.status = 1 ', 'left');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        if (isset($filter) && !empty($filter['top_rated_seller']) && strtolower($filter['top_rated_seller']) == 'top_rated_seller') {
            $sort = null;
            $order = null;
            $search_res->order_by("sd.rating", "desc");
            $search_res->order_by("sd.no_of_ratings", "desc");
        }

        if (isset($zipcode_id) && !empty($zipcode_id) && $zipcode_id != "") {
            $this->db->group_Start();
            $where2 = "((deliverable_type='2' and FIND_IN_SET('$zipcode_id', deliverable_zipcodes)) or deliverable_type = '1') OR (deliverable_type='3' and NOT FIND_IN_SET('$zipcode_id', deliverable_zipcodes)) ";
            $this->db->where($where2);
            $this->db->group_End();
        }

        $offer_search_res = $search_res->group_by('u.id')->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();
        $bulkData = array();

        $bulkData['error'] = (empty($offer_search_res)) ? true : false;
        $bulkData['message'] = (empty($offer_search_res)) ? 'Seller(s) does not exist' : 'Seller retrieved successfully';
        $bulkData['total'] = (empty($offer_search_res)) ? 0 : $total;
        $rows = $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $where = ['p.seller_id' =>  $row['seller_id'], 'p.status' => '1', 'pv.status' => 1];
            $this->db->group_Start();
            $this->db->or_where('c.status', '1');
            $this->db->or_where('c.status', '0');
            $this->db->group_End();
            $total = $this->db->select(' COUNT(DISTINCT p.id) as `total` ')->join('seller_data sd', ' p.seller_id = sd.id ', 'left')->join('`product_variants` pv', 'p.id = pv.product_id', 'LEFT')->join(" categories c", "p.category_id=c.id ", 'LEFT')->where($where)->get('products p')->result_array();

            $tempRow['seller_id'] = $row['seller_id'];
            $tempRow['seller_name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['slug'] = $row['slug'];
            $tempRow['seller_rating'] = $row['rating'];
            $tempRow['no_of_ratings'] = $row['no_of_ratings'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['seller_profile'] = base_url() . $row['logo'];
            $tempRow['seller_profile_path'] = $row['logo'];
            $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : number_format($row['balance'], 2);
            $tempRow['total_products'] = isset($total[0]['total']) ? $total[0]['total'] : 0;
            $rows[] = $tempRow;
        }
        $bulkData['data'] = $rows;
        if (!empty($bulkData)) {
            return $bulkData;
        } else {
            return $bulkData;
        }
    }

    /**
     * Get sellers by location with distance calculation
     * Uses Haversine formula for accurate distance calculation
     * Note: This method uses the provided radius parameter to filter sellers by distance.
     * It does NOT check against seller's service_radius field - only uses the provided radius.
     * 
     * @param float $latitude User's latitude
     * @param float $longitude User's longitude
     * @param float $radius Maximum distance in kilometers (optional) - used to filter sellers, NOT seller's service_radius
     * @param int $limit Number of results to return
     * @param int $offset Pagination offset
     * @param string $sort Sort field
     * @param string $order Sort order (ASC/DESC)
     * @param string $search Search keyword
     * @param array $filter Additional filters
     * @return array
     */
    public function get_sellers_by_location($latitude = null, $longitude = null, $radius = null, $limit = 25, $offset = 0, $sort = 'distance', $order = 'ASC', $search = null, $filter = [])
    {
        $where = ['u.active' => 1, 'sd.status' => 1, 'ug.group_id' => 4];
        $distance_select = "";
        $distance_having = "";
        $has_location = ($latitude !== null && $longitude !== null);
        
        // Build distance calculation if location provided
        if ($has_location) {
            $lat = floatval($latitude);
            $lng = floatval($longitude);
            
            $distance_select = ", CASE 
                WHEN u.latitude IS NOT NULL AND u.longitude IS NOT NULL 
                    AND u.latitude != '' AND u.longitude != '' 
                    AND CAST(u.latitude AS DECIMAL(10,8)) BETWEEN -90 AND 90 
                    AND CAST(u.longitude AS DECIMAL(11,8)) BETWEEN -180 AND 180 THEN
                    (6371 * ACOS(
                        LEAST(1, GREATEST(-1,
                            COS(RADIANS({$lat})) * COS(RADIANS(CAST(u.latitude AS DECIMAL(10,8)))) * 
                            COS(RADIANS(CAST(u.longitude AS DECIMAL(11,8))) - RADIANS({$lng})) + 
                            SIN(RADIANS({$lat})) * SIN(RADIANS(CAST(u.latitude AS DECIMAL(10,8))))
                        ))
                    ))
                ELSE NULL 
            END AS distance";
            
            if ($radius !== null && is_numeric($radius) && $radius > 0) {
                $radius_val = floatval($radius);
                $distance_having = "HAVING (distance <= {$radius_val} OR distance IS NULL)";
            }
        }
        
        // Apply filters
        if (isset($filter['slug']) && !empty($filter['slug'])) {
            $where['sd.slug'] = $filter['slug'];
        }
        if (isset($_POST['seller_id']) && !empty($_POST['seller_id'])) {
            $where['sd.user_id'] = intval($_POST['seller_id']);
        }
        
        // Build search conditions only if search is provided
        $search_conditions = "";
        if ($search !== null && $search !== '') {
            $search_escaped = $this->db->escape_like_str($search);
            $search_conditions = " AND (
                u.id LIKE '%{$search_escaped}%' OR 
                u.username LIKE '%{$search_escaped}%' OR 
                u.email LIKE '%{$search_escaped}%' OR 
                u.mobile LIKE '%{$search_escaped}%' OR 
                u.address LIKE '%{$search_escaped}%' OR 
                u.balance LIKE '%{$search_escaped}%' OR 
                sd.store_name LIKE '%{$search_escaped}%'
            )";
        }
        
        // Build count query - must match main query logic including distance filter
        if ($has_location && !empty($distance_having)) {
            // Count query with distance calculation and HAVING clause
            $count_query = "SELECT COUNT(*) as total FROM (
                SELECT u.id, 
                    CASE 
                        WHEN u.latitude IS NOT NULL AND u.longitude IS NOT NULL 
                            AND u.latitude != '' AND u.longitude != '' 
                            AND CAST(u.latitude AS DECIMAL(10,8)) BETWEEN -90 AND 90 
                            AND CAST(u.longitude AS DECIMAL(11,8)) BETWEEN -180 AND 180 THEN
                            (6371 * ACOS(
                                LEAST(1, GREATEST(-1,
                                    COS(RADIANS({$lat})) * COS(RADIANS(CAST(u.latitude AS DECIMAL(10,8)))) * 
                                    COS(RADIANS(CAST(u.longitude AS DECIMAL(11,8))) - RADIANS({$lng})) + 
                                    SIN(RADIANS({$lat})) * SIN(RADIANS(CAST(u.latitude AS DECIMAL(10,8))))
                                ))
                            ))
                        ELSE NULL 
                    END AS distance
                FROM users u 
                JOIN users_groups ug ON ug.user_id = u.id 
                JOIN seller_data sd ON sd.user_id = u.id 
                LEFT JOIN products p ON p.seller_id = u.id AND (p.status = 1 OR p.id IS NULL)
                WHERE u.active = 1 AND sd.status = 1 AND ug.group_id = 4" . $search_conditions . "
                GROUP BY u.id
                " . $distance_having . "
            ) as filtered_sellers";
        } else {
            // Simple count query without distance filter
            $count_query = "SELECT COUNT(DISTINCT u.id) as total 
                FROM users u 
                JOIN users_groups ug ON ug.user_id = u.id 
                JOIN seller_data sd ON sd.user_id = u.id 
                LEFT JOIN products p ON p.seller_id = u.id AND (p.status = 1 OR p.id IS NULL)
                WHERE u.active = 1 AND sd.status = 1 AND ug.group_id = 4" . $search_conditions;
        }
        
        $count_result = $this->db->query($count_query)->row_array();
        $total = isset($count_result['total']) ? intval($count_result['total']) : 0;
        
        // Build main query
        $select_fields = "u.*, sd.*, u.id as seller_id" . $distance_select;
        
        // Determine sorting
        $order_clause = "";
        if (isset($filter['top_rated_seller']) && strtolower($filter['top_rated_seller']) == 'top_rated_seller') {
            $order_clause = "ORDER BY sd.rating DESC, sd.no_of_ratings DESC";
        } elseif ($sort == 'distance' && $has_location) {
            // Handle NULL distances - put them last when ASC, first when DESC
            // (distance IS NULL) returns 1 for NULL, 0 for non-NULL
            // So for ASC: NULLs (1) come after non-NULLs (0)
            // For DESC: We want NULLs first, so we need to handle it differently
            if ($order == 'DESC') {
                $order_clause = "ORDER BY (distance IS NULL) DESC, distance DESC";
            } else {
                $order_clause = "ORDER BY (distance IS NULL) ASC, distance ASC";
            }
        } elseif (!empty($sort)) {
            $order_clause = "ORDER BY {$sort} " . ($order == 'DESC' ? 'DESC' : 'ASC');
        }
        
        // Build complete query
        $main_query = "SELECT {$select_fields} 
            FROM users u 
            JOIN users_groups ug ON ug.user_id = u.id 
            JOIN seller_data sd ON sd.user_id = u.id 
            LEFT JOIN products p ON p.seller_id = u.id AND (p.status = 1 OR p.id IS NULL)
            WHERE u.active = 1 AND sd.status = 1 AND ug.group_id = 4" . $search_conditions . "
            GROUP BY u.id";
        
        if (!empty($distance_having)) {
            $main_query .= " " . $distance_having;
        }
        
        if (!empty($order_clause)) {
            $main_query .= " " . $order_clause;
        }
        
        // Ensure offset and limit are integers
        $offset_int = max(0, intval($offset)); // Ensure offset is never negative
        $limit_int = max(1, intval($limit)); // Ensure limit is at least 1
        
        // Always use LIMIT offset, limit syntax for consistency
        // This is the standard MySQL syntax: LIMIT offset, limit
        $main_query .= " LIMIT {$offset_int}, {$limit_int}";
        
        // Execute query
        $offer_search_res = $this->db->query($main_query)->result_array();
        
        $bulkData = array();
        $bulkData['error'] = (empty($offer_search_res)) ? true : false;
        $bulkData['message'] = (empty($offer_search_res)) ? 'Seller(s) does not exist' : 'Seller retrieved successfully';
        $bulkData['total'] = $total."";
        $rows = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            
            // Count products for this seller
            $where_products = ['p.seller_id' => $row['seller_id'], 'p.status' => '1', 'pv.status' => 1];
            $this->db->group_start();
            $this->db->or_where('c.status', '1');
            $this->db->or_where('c.status', '0');
            $this->db->group_end();
            $total_products = $this->db->select('COUNT(DISTINCT p.id) as total')
                ->join('seller_data sd', 'p.seller_id = sd.id', 'left')
                ->join('product_variants pv', 'p.id = pv.product_id', 'LEFT')
                ->join('categories c', 'p.category_id = c.id', 'LEFT')
                ->where($where_products)
                ->get('products p')
                ->result_array();

            $tempRow = array();
            $tempRow['seller_id'] = $row['seller_id'];
            $tempRow['seller_name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['latitude'] = isset($row['latitude']) && !empty($row['latitude']) ? $row['latitude'] : '';
            $tempRow['longitude'] = isset($row['longitude']) && !empty($row['longitude']) ? $row['longitude'] : '';
            $tempRow['slug'] = $row['slug'];
            $tempRow['seller_rating'] = isset($row['rating']) ? $row['rating'] : 0;
            $tempRow['no_of_ratings'] = isset($row['no_of_ratings']) ? $row['no_of_ratings'] : 0;
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = isset($row['store_url']) ? $row['store_url'] : '';
            $tempRow['store_description'] = isset($row['store_description']) ? $row['store_description'] : '';
            $tempRow['seller_profile'] = base_url() . $row['logo'];
            $tempRow['seller_profile_path'] = $row['logo'];
            $tempRow['balance'] = ($row['balance'] == null || $row['balance'] == 0 || empty($row['balance'])) ? "0" : number_format($row['balance'], 2);
            $tempRow['total_products'] = (isset($total_products[0]['total']) ? intval($total_products[0]['total']) : 0)."";
            $tempRow['distance'] = isset($row['distance']) && $row['distance'] !== null && $row['distance'] !== '' ? round(floatval($row['distance']), 2) : null;
            $tempRow['distance_text'] = isset($row['distance']) && $row['distance'] !== null && $row['distance'] !== '' ? $this->format_distance(floatval($row['distance'])) : '';
            $rows[] = $tempRow;
        }
        
        $bulkData['data'] = $rows;
        return $bulkData;
    }

    /**
     * Format distance for display
     * 
     * @param float $distance Distance in kilometers
     * @return string Formatted distance string
     */
    private function format_distance($distance)
    {
        if ($distance === null) {
            return '';
        }
        
        $distance = floatval($distance);
        
        if ($distance < 1) {
            // Show in meters if less than 1 km
            return round($distance * 1000) . ' m';
        } elseif ($distance < 10) {
            // Show with 1 decimal place for distances under 10 km
            return number_format($distance, 1) . ' km';
        } else {
            // Show without decimals for longer distances
            return round($distance) . ' km';
        }
    }

    /**
     * Get nearest sellers to a location
     * 
     * @param float $latitude User's latitude
     * @param float $longitude User's longitude
     * @param int $limit Number of results
     * @return array
     */
    public function get_nearest_sellers($latitude, $longitude, $limit = 10)
    {
        return $this->get_sellers_by_location($latitude, $longitude, null, $limit, 0, 'distance', 'ASC');
    }

    /**
     * Check if a seller delivers to a specific location
     * 
     * @param int $seller_id Seller ID
     * @param float $latitude User's latitude
     * @param float $longitude User's longitude
     * @return array Contains deliverable status and distance
     */
    public function check_seller_delivers_to_location($seller_id, $latitude, $longitude)
    {
        $seller = $this->db->select('u.latitude, u.longitude, sd.service_radius, sd.is_location_based')
            ->join('seller_data sd', 'sd.user_id = u.id')
            ->where('u.id', $seller_id)
            ->get('users u')
            ->row_array();
        
        if (empty($seller)) {
            return ['deliverable' => false, 'message' => 'Seller not found', 'distance' => null];
        }
        
        // If seller is not location-based, they deliver everywhere
        if (empty($seller['is_location_based']) || $seller['is_location_based'] == 0) {
            return ['deliverable' => true, 'message' => 'Seller delivers to all locations', 'distance' => null];
        }
        
        // If seller doesn't have coordinates set
        if (empty($seller['latitude']) || empty($seller['longitude'])) {
            return ['deliverable' => true, 'message' => 'Seller location not set', 'distance' => null];
        }
        
        // Calculate distance
        $distance = $this->calculate_distance(
            $latitude, 
            $longitude, 
            $seller['latitude'], 
            $seller['longitude']
        );
        
        // Check if within service radius
        if (!empty($seller['service_radius']) && $distance > $seller['service_radius']) {
            return [
                'deliverable' => false, 
                'message' => 'Location is outside seller\'s service area',
                'distance' => round($distance, 2),
                'service_radius' => $seller['service_radius']
            ];
        }
        
        return [
            'deliverable' => true, 
            'message' => 'Seller delivers to this location',
            'distance' => round($distance, 2)
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public function calculate_distance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // Earth's radius in kilometers
        
        $lat1 = deg2rad(floatval($lat1));
        $lat2 = deg2rad(floatval($lat2));
        $lon1 = deg2rad(floatval($lon1));
        $lon2 = deg2rad(floatval($lon2));
        
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlon / 2) * sin($dlon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earth_radius * $c;
    }

    public function get_seller_commission_data($id)
    {

        $data = $this->db->select("sc.*,c.name")
            ->join('categories c', 'c.id = sc.category_id')
            ->where('seller_id', $id)
            ->order_by('category_id', 'ASC')
            ->get('seller_commission sc')->result_array();

        if (!empty($data)) {
            return $data;
        } else {
            return false;
        }
    }

    function settle_seller_commission($is_date = TRUE)
    {

        $date = date('Y-m-d');
        $settings = get_settings('system_settings', true);
        if ($is_date == TRUE) {
            $where = "oi.active_status='delivered' AND is_credited=0 and  DATE_ADD(DATE_FORMAT(oi.date_added, '%Y-%m-%d'), INTERVAL " . $settings['max_product_return_days'] . " DAY) = '" . $date . "'";
        } else {
            $where = "oi.active_status='delivered' AND is_credited=0 ";
        }
        $data = $this->db->select("c.id as category_id, oi.id,date(oi.date_added) as order_date,oi.order_id,oi.product_variant_id,oi.seller_id,oi.sub_total ")
            ->join('product_variants pv', 'pv.id=oi.product_variant_id', 'left')
            ->join('products p', 'p.id=pv.product_id')
            ->join('categories c', 'p.category_id=c.id')
            ->where($where)
            ->get('order_items oi')->result_array();
        $wallet_updated = false;
        if (isset($data) && !empty($data)) {

            foreach ($data as $row) {
                $cat_com = fetch_details('seller_commission', ['seller_id' => $row['seller_id'], 'category_id' => $row['category_id']], 'commission');
                if (!empty($cat_com) && ($cat_com[0]['commission'] != 0)) {
                    $commission_pr = $cat_com[0]['commission'];
                } else {
                    $global_comm = fetch_details('seller_data', ['user_id' => $row['seller_id']],  'commission');
                    $commission_pr = $global_comm[0]['commission'];
                }

                $commission_amt = $row['sub_total'] / 100 * $commission_pr;
                $transfer_amt = $row['sub_total'] - $commission_amt;
                $response = update_wallet_balance('credit', $row['seller_id'], $transfer_amt, 'Commission Amount Credited for Order Item ID  : ' . $row['id']);
                if ($response['error'] == false) {
                    update_details(['is_credited' => 1, 'admin_commission_amount' => $commission_amt, "seller_commission_amount" => $transfer_amt], ['id' => $row['id']], 'order_items');
                    $wallet_updated = true;
                    $response_data['error'] = false;
                    $response_data['message'] = 'Commission settled Successfully';
                } else {
                    $wallet_updated = false;
                    $response_data['error'] =  true;
                    $response_data['message'] =  'Commission not settled';
                }
            }
            if ($wallet_updated == true) {
                $seller_ids = array_values(array_unique(array_column($data, "seller_id")));
                foreach ($seller_ids as $seller) {
                    //custom message
                    $settings = get_settings('system_settings', true);
                    $firebase_project_id = get_settings('firebase_project_id');
                    $service_account_file = get_settings('service_account_file');
                    $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
                    $user_res = fetch_details('users', ['id' => $seller], 'username,fcm_id,email,mobile,platform_type');
                    $custom_notification = fetch_details('custom_notifications', ['type' => "settle_seller_commission"], '');
                    $hashtag_cutomer_name = '< cutomer_name >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification[0]['message']) ? json_encode($custom_notification[0]['message'], JSON_UNESCAPED_UNICODE) : "";
                    $hashtag = html_entity_decode($string);
                    $data = str_replace(array($hashtag_cutomer_name, $hashtag_application_name), array($user_res[0]['username'], $app_name), $hashtag);
                    $message = output_escaping(trim($data, '"'));
                    $customer_title = (!empty($custom_notification)) ? $custom_notification[0]['title'] : "Commission Amount Credited";
                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]['username'] . 'Commission Amount Credited, which orders are delivered. Please take note of it! Regards' . $app_name . '';

                    (notify_event(
                        "settle_seller_commission",
                        ["seller" => [$user_res[0]['email']]],
                        ["seller" => [$user_res[0]['mobile']]],
                        ["users.mobile" => $user_res[0]['mobile']]
                    ));

                    // Step 1: Group by platform
                    $groupedByPlatform = [];
                    foreach ($user_res as $item) {
                        $platform = $item['platform_type'];
                        $groupedByPlatform[$platform][] = $item['fcm_id'];
                    }

                    // Step 2: Chunk each platform group into arrays of 1000
                    $fcm_ids = [];
                    foreach ($groupedByPlatform as $platform => $fcmIds) {
                        $fcm_ids[$platform] = array_chunk($fcmIds, 1000);
                    }

                    if (!empty($user_res[0]['fcm_id']) && isset($firebase_project_id) && isset($service_account_file) && !empty($firebase_project_id) && !empty($service_account_file)) {
                        $fcmMsg = array(
                            'title' => $customer_title,
                            'body' => $customer_msg,
                            'type' => "commission",
                        );

                        $fcm_ids[0][] = $fcm_ids;
                        send_notification($fcmMsg, $fcm_ids, $fcmMsg);
                    }
                }
            } else {
                $response_data['error'] =  true;
                $response_data['message'] =  'Commission not settled';
            }
        } else {
            $response_data['error'] =  true;
            $response_data['message'] =  'No order found for settlement';
        }
        print_r(json_encode($response_data));
    }

    public function top_sellers()
    {
        $query = $this->db->select(" `seller_id`, s.store_name,(SELECT username FROM users as u WHERE u.id=s.user_id) as seller_name ,( SELECT SUM(sub_total) AS total FROM order_items i WHERE i.seller_id = oi.seller_id AND active_status = 'delivered' ) AS total")
            ->join('seller_data s', 's.user_id = oi.seller_id', "left")
            ->join('users u', 'u.id=s.id', 'left')
            ->limit('5')
            ->group_by('seller_id')
            ->order_by('total', 'Desc')
            ->get('order_items oi');

        $data['total'] = $query->num_rows();
        $data['rows'] = $query->result_array();


        print_r(json_encode($data));
    }

    function approved_sellers()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->where('sd.status', 1)->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.* ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ')->where('sd.status', 1);
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = " <a href='" . base_url('admin/sellers/manage-seller') . "?edit_id=" . $row['user_id'] . "' data-id=" . $row['user_id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            $operate .= '<a  href="javascript:void(0)" class="delete-sellers btn btn-danger btn-xs mr-1 mb-1" title="Delete"   data-id="' . $row['user_id'] . '" ><i class="fa fa-trash"></i></a>';
            if ($row['status'] == '1' || $row['status'] == '0' || $row['status'] == '2') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-warning btn-xs mr-1 mb-1" title="Remove Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user-slash"></i></a>';
            } else if ($row['status'] == '7') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-primary btn-xs mr-1 mb-1" title="Restore Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user"></i></a>';
            }
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';;
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['pan_number'] = $row['pan_number'];

            // seller status
            if ($row['status'] == 2)
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            else if ($row['status'] == 1)
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            else if ($row['status'] == 0)
                $tempRow['status'] = "<label class='badge badge-danger'>Deactive</label>";
            else if ($row['status'] == 7)
                $tempRow['status'] = "<label class='badge badge-danger'>Removed</label>";

            $tempRow['category_ids'] = $row['category_ids'];

            $row['logo'] = base_url() . $row['logo'];
            $tempRow['logo'] = '<div class="mx-auto product-image"><a href=' . $row['logo'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo'] . ' class="image-box-100 rounded"></a></div>';

            $row['national_identity_card'] = get_image_url($row['national_identity_card']);
            $tempRow['national_identity_card'] = '<div class="mx-auto product-image"><a href=' . $row['national_identity_card'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['national_identity_card'] . ' class="image-box-100 rounded"></a></div>';

            $row['address_proof'] = get_image_url($row['address_proof']);
            $tempRow['address_proof'] = '<div class="mx-auto product-image"><a href=' . $row['address_proof'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['address_proof'] . ' class="image-box-100 rounded"></a></div>';

            $tempRow['permissions'] = $row['permissions'];
            $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : $row['balance'];
            $tempRow['date'] =  date('d-m-Y', strtotime($row['created_at']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function not_approved_sellers()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.*')->where('sd.status', '2')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = " <a href='" . base_url('admin/sellers/manage-seller') . "?edit_id=" . $row['user_id'] . "' data-id=" . $row['user_id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            $operate .= '<a  href="javascript:void(0)" class="delete-sellers btn btn-danger btn-xs mr-1 mb-1" title="Delete"   data-id="' . $row['user_id'] . '" ><i class="fa fa-trash"></i></a>';
            if ($row['status'] == '1' || $row['status'] == '0' || $row['status'] == '2') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-warning btn-xs mr-1 mb-1" title="Remove Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user-slash"></i></a>';
            } else if ($row['status'] == '7') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-primary btn-xs mr-1 mb-1" title="Restore Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user"></i></a>';
            }
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';;
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['pan_number'] = $row['pan_number'];

            // seller status
            if ($row['status'] == 2)
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            else if ($row['status'] == 1)
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            else if ($row['status'] == 0)
                $tempRow['status'] = "<label class='badge badge-danger'>Deactive</label>";
            else if ($row['status'] == 7)
                $tempRow['status'] = "<label class='badge badge-danger'>Removed</label>";

            $tempRow['category_ids'] = $row['category_ids'];

            $row['logo'] = base_url() . $row['logo'];
            $tempRow['logo'] = '<div class="mx-auto product-image"><a href=' . $row['logo'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo'] . ' class="image-box-100 rounded"></a></div>';

            $row['national_identity_card'] = get_image_url($row['national_identity_card']);
            $tempRow['national_identity_card'] = '<div class="mx-auto product-image"><a href=' . $row['national_identity_card'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['national_identity_card'] . ' class="image-box-100 rounded"></a></div>';

            $row['address_proof'] = get_image_url($row['address_proof']);
            $tempRow['address_proof'] = '<div class="mx-auto product-image"><a href=' . $row['address_proof'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['address_proof'] . ' class="image-box-100 rounded"></a></div>';

            $tempRow['permissions'] = $row['permissions'];
            $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : $row['balance'];
            $tempRow['date'] =  date('d-m-Y', strtotime($row['created_at']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function deactive_sellers()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.* ')->where('sd.status', '0')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = " <a href='" . base_url('admin/sellers/manage-seller') . "?edit_id=" . $row['user_id'] . "' data-id=" . $row['user_id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            $operate .= '<a  href="javascript:void(0)" class="delete-sellers btn btn-danger btn-xs mr-1 mb-1" title="Delete"   data-id="' . $row['user_id'] . '" ><i class="fa fa-trash"></i></a>';
            if ($row['status'] == '1' || $row['status'] == '0' || $row['status'] == '2') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-warning btn-xs mr-1 mb-1" title="Remove Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user-slash"></i></a>';
            } else if ($row['status'] == '7') {
                $operate .= '<a  href="javascript:void(0)" class="remove-sellers btn btn-primary btn-xs mr-1 mb-1" title="Restore Seller"  data-id="' . $row['user_id'] . '" data-seller_status="' . $row['status'] . '" ><i class="fas fa-user"></i></a>';
            }
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';;
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['pan_number'] = $row['pan_number'];

            // seller status
            if ($row['status'] == 2)
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            else if ($row['status'] == 1)
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            else if ($row['status'] == 0)
                $tempRow['status'] = "<label class='badge badge-danger'>Deactive</label>";
            else if ($row['status'] == 7)
                $tempRow['status'] = "<label class='badge badge-danger'>Removed</label>";

            $tempRow['category_ids'] = $row['category_ids'];

            $row['logo'] = base_url() . $row['logo'];
            $tempRow['logo'] = '<div class="mx-auto product-image"><a href=' . $row['logo'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo'] . ' class="image-box-100 rounded"></a></div>';

            $row['national_identity_card'] = get_image_url($row['national_identity_card']);
            $tempRow['national_identity_card'] = '<div class="mx-auto product-image"><a href=' . $row['national_identity_card'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['national_identity_card'] . ' class="image-box-100 rounded"></a></div>';

            $row['address_proof'] = get_image_url($row['address_proof']);
            $tempRow['address_proof'] = '<div class="mx-auto product-image"><a href=' . $row['address_proof'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['address_proof'] . ' class="image-box-100 rounded"></a></div>';

            $tempRow['permissions'] = $row['permissions'];
            $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : $row['balance'];
            $tempRow['date'] =  date('d-m-Y', strtotime($row['created_at']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function search_seller($ssearch)
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];
        if ($ssearch != "") {
            $search = $_GET['search'];
            $search = $ssearch;
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $count_res->where($where);
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.*,sd.* ')->join('users_groups ug', ' ug.user_id = u.id ')->join('seller_data sd', ' sd.user_id = u.id ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '4';
            $search_res->where($where);
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);

            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['store_name'] = $row['store_name'];
            $tempRow['store_url'] = $row['store_url'];
            $tempRow['store_description'] = $row['store_description'];
            $tempRow['account_number'] = $row['account_number'];
            $tempRow['account_name'] = $row['account_name'];
            $tempRow['bank_code'] = $row['bank_code'];
            $tempRow['bank_name'] = $row['bank_name'];
            $tempRow['latitude'] = $row['latitude'];
            $tempRow['longitude'] = $row['longitude'];
            $tempRow['tax_name'] = $row['tax_name'];
            $tempRow['rating'] = ' <p> (' . intval($row['rating']) . '/' . $row['no_of_ratings'] . ') </p>';;
            $tempRow['tax_number'] = $row['tax_number'];
            $tempRow['pan_number'] = $row['pan_number'];

            // seller status


            $tempRow['category_ids'] = $row['category_ids'];

            $row['logo'] = base_url() . $row['logo'];
            $tempRow['logo'] = '<div class="mx-auto product-image"><a href=' . $row['logo'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['logo'] . ' class="image-box-100 rounded"></a></div>';

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
}
