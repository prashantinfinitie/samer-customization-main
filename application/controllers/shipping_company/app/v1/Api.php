<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    /*
    ---------------------------------------------------------------------------
    Shipping Company API Methods:-
    ---------------------------------------------------------------------------
    1. login
    2. get_shipping_company_details
    3. get_orders
    4. update_order_status
    5. get_fund_transfers
    6. update_user
    7. update_fcm
    8. reset_password
    9. get_notifications
    10. verify_user
    11. get_settings
    12. get_cash_collection
    13. delete_shipping_company
    14. verify_otp
    15. resend_otp
    16. register
    17. get_zipcodes (provider_type = 'company')
    18. get_cities
    19. get_statistics
    20. get_payout_summary
    21. send_withdrawal_request
    22. get_withdrawal_request
    ---------------------------------------------------------------------------
    */

    private $user_details = [];

    protected $excluded_routes = [
        "shipping_company/app/v1/api",
        "shipping_company/app/v1/api/login",
        "shipping_company/app/v1/api/reset_password",
        "shipping_company/app/v1/api/get_notifications",
        "shipping_company/app/v1/api/verify_user",
        "shipping_company/app/v1/api/get_settings",
        "shipping_company/app/v1/api/register",
        "shipping_company/app/v1/api/get_zipcodes",
        "shipping_company/app/v1/api/get_cities",
        "shipping_company/app/v1/api/verify_otp",
        "shipping_company/app/v1/api/resend_otp",
    ];

    public function __construct()
    {
        parent::__construct();
        header("Content-Type: application/json");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $this->load->library(['upload', 'jwt', 'Key', 'ion_auth', 'form_validation']);
        $this->load->model(['Area_model', 'Order_model', 'notification_model', 'Shipping_company_model', 'Fund_transfers_model']);
        $this->load->helper(['language', 'string', 'function_helper', 'sms_helper']);
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load('auth');
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        $this->tables = $this->config->item('tables', 'ion_auth');

        $current_uri = uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            if ($token['error']) {
                header('Content-Type: application/json');
                http_response_code($token['status']);
                print_r(json_encode($token));
                die();
            }
            $this->user_details = $token['data'];
        }
    }

    public function index()
    {
        $this->load->helper('file');
        $this->output->set_content_type(get_mime_by_extension(base_url('shipping-company-api-doc.txt')));
        $this->output->set_output(file_get_contents(base_url('shipping-company-api-doc.txt')));
    }

    public function verify_token()
    {
        try {
            $token = $this->jwt->getBearerToken();
        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
            print_r(json_encode($response));
            return false;
        }

        if (!empty($token)) {
            $api_keys = fetch_details('client_api_keys', ['status' => 1]);
            if (empty($api_keys)) {
                $response['error'] = true;
                $response['message'] = 'No Client(s) Data Found !';
                print_r(json_encode($response));
                return false;
            }
            JWT::$leeway = 2000;
            $flag = true;
            $error = true;
            $message = '';
            try {
                $payload = $this->jwt->decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
                if (isset($payload->iss) && $payload->iss == 'eshop') {
                    $error = false;
                    $flag = false;
                } else {
                    $error = true;
                    $flag = false;
                    $message = 'Invalid Hash';
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
            }

            if ($flag) {
                $response['error'] = true;
                $response['message'] = $message;
                print_r(json_encode($response));
                return false;
            } else {
                if ($error == true) {
                    $response['error'] = true;
                    $response['message'] = $message;
                    print_r(json_encode($response));
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Unauthorized access not allowed";
            print_r(json_encode($response));
            return false;
        }
    }

    // 1. Login
    public function login()
    {
        $identity_column = $this->config->item('identity', 'ion_auth');
        if ($identity_column == 'mobile') {
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        } elseif ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        } else {
            $this->form_validation->set_rules('identity', 'Identity', 'trim|required|xss_clean');
        }
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('fcm_id', 'FCM ID', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $identity = ($identity_column == 'mobile') ? $this->input->post('mobile', true) : $this->input->post('email', true);
        $login = $this->ion_auth->login($identity, $this->input->post('password'), false, 'phone');

        if ($login) {
            $data = fetch_details('users', [$identity_column => $identity]);
            if ($this->ion_auth->in_group('shipping_company', $data[0]['id'])) {
                if (isset($_POST['fcm_id']) && $_POST['fcm_id'] != '') {
                    update_details(['fcm_id' => $_POST['fcm_id']], [$identity_column => $identity], 'users');
                }

                $existing_token = ($data[0]['apikey'] !== null && !empty($data[0]['apikey'])) ? $data[0]['apikey'] : "";
                unset($data[0]['password']);

                if ($existing_token == '') {
                    $token = generate_token($identity);
                    update_details(['apikey' => $token], [$identity_column => $identity], "users");
                }

                $row = output_escaping($data[0]);
                $tempRow = $this->format_user_data($row);

                $messages = array("0" => "Your account is not yet approved.", "1" => "Logged in successfully");
                $response['error'] = ($data[0]['status'] != "" && ($data[0]['status'] != 0)) ? false : true;
                $response['message'] = $messages[$data[0]['status']];
                $response['token'] = $existing_token !== "" ? $existing_token : $token;
                $response['data'] = (isset($data[0]['status']) && $data[0]['status'] != "" && ($data[0]['status'] == 1)) ? [$tempRow] : [];
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = true;
                $response['message'] = 'You are not registered as a shipping company.';
                echo json_encode($response);
                return false;
            }
        } else {
            $response['error'] = true;
            $response['message'] = strip_tags($this->ion_auth->errors());
            echo json_encode($response);
            return false;
        }
    }

    // 2. Get shipping company details
    public function get_shipping_company_details()
    {
        if (!$this->verify_token()) {
            return false;
        }
        $user_id = isset($this->user_details['id']) && $this->user_details['id'] !== null ? $this->user_details['id'] : '';

        $data = fetch_details('users', ['id' => $user_id]);
        if (empty($data)) {
            $response['error'] = true;
            $response['message'] = 'User not found';
            $response['data'] = [];
            print_r(json_encode($response));
            return false;
        }

        unset($data[0]['password']);
        $row = output_escaping($data[0]);
        $tempRow = $this->format_user_data($row);

        // Add KYC documents
        $kyc_docs = [];
        if (isset($row['kyc_documents']) && !empty($row['kyc_documents'])) {
            $docs = explode(',', $row['kyc_documents']);
            foreach ($docs as $doc) {
                $kyc_docs[] = base_url($doc);
            }
        }
        $tempRow['kyc_documents'] = $kyc_docs;

        $response['error'] = false;
        $response['message'] = 'Data retrieved successfully';
        $response['data'] = [$tempRow];
        print_r(json_encode($response));
    }

    // 3. Get orders assigned to shipping company
    public function get_orders()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $this->input->post('sort', true) : 'o.id';
        $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $this->input->post('order', true) : 'DESC';

        $this->form_validation->set_rules('active_status', 'Status', 'trim|xss_clean');
        $this->form_validation->set_rules('order_id', 'Order ID', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $shipping_company_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $active_status = (isset($_POST['active_status']) && !empty($_POST['active_status'])) ? $_POST['active_status'] : null;
        $order_id = (isset($_POST['order_id']) && !empty($_POST['order_id'])) ? $_POST['order_id'] : null;

        // Get orders assigned to this shipping company
        $this->db->select('o.*, oi.id as order_item_id, oi.product_variant_id, oi.quantity, oi.price, oi.sub_total, oi.active_status as item_status, oi.status as item_status_history,
                           p.name as product_name, p.name_ar as product_name_ar, p.short_description, p.short_description_ar, p.description, p.description_ar, p.image as product_image,
                           c.name as category_name, c.name_ar as category_name_ar,
                           pv.weight, pv.height, pv.breadth, pv.length,
                           u.username as customer_name, u.mobile as customer_mobile, u.email as customer_email,
                           a.address, a.landmark, a.pincode as address_pincode, a.city as address_city');
        $this->db->from('orders o');
        $this->db->join('order_items oi', 'oi.order_id = o.id', 'left');
        $this->db->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left');
        $this->db->join('products p', 'p.id = pv.product_id', 'left');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('users u', 'u.id = o.user_id', 'left');
        $this->db->join('addresses a', 'a.id = o.address_id', 'left');
        $this->db->where('o.shipping_company_id', $shipping_company_id);

        if ($active_status) {
            $this->db->where('oi.active_status', $active_status);
        }
        if ($order_id) {
            $this->db->where('o.id', $order_id);
        }

        // Count total
        $count_query = clone $this->db;
        $total = $count_query->count_all_results();

        // Get data
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);
        $orders = $this->db->get()->result_array();

        // Get language preference
        $lang = get_api_language();

        // Group by order
        $grouped_orders = [];
        foreach ($orders as $order_row) {
            $oid = $order_row['id'];
            if (!isset($grouped_orders[$oid])) {
                $grouped_orders[$oid] = [
                    'id' => $order_row['id'],
                    'user_id' => $order_row['user_id'],
                    'customer_name' => $order_row['customer_name'],
                    'customer_mobile' => $order_row['customer_mobile'],
                    'customer_email' => $order_row['customer_email'],
                    'address' => $order_row['address'],
                    'landmark' => $order_row['landmark'],
                    'pincode' => $order_row['address_pincode'],
                    'city' => $order_row['address_city'],
                    'total' => $order_row['total'],
                    'delivery_charge' => $order_row['delivery_charge'],
                    'final_total' => $order_row['final_total'],
                    'payment_method' => $order_row['payment_method'],
                    'order_note' => $order_row['order_note'],
                    'date_added' => $order_row['date_added'],
                    'otp' => $order_row['otp'],
                    'order_items' => []
                ];
            }
            if ($order_row['order_item_id']) {
                // Prepare product data for language transformation
                $product_data = [
                    'name' => $order_row['product_name'],
                    'name_ar' => isset($order_row['product_name_ar']) ? $order_row['product_name_ar'] : '',
                    'short_description' => isset($order_row['short_description']) ? $order_row['short_description'] : '',
                    'short_description_ar' => isset($order_row['short_description_ar']) ? $order_row['short_description_ar'] : '',
                    'description' => isset($order_row['description']) ? $order_row['description'] : '',
                    'description_ar' => isset($order_row['description_ar']) ? $order_row['description_ar'] : '',
                    'category_name' => isset($order_row['category_name']) ? $order_row['category_name'] : '',
                    'category_name_ar' => isset($order_row['category_name_ar']) ? $order_row['category_name_ar'] : '',
                ];

                // Apply language transformation
                $transformed_product = $this->apply_product_language([$product_data], $lang);
                $product = $transformed_product[0];

                // Parse status history
                $status_history = [];
                if (!empty($order_row['item_status_history'])) {
                    $status_history = parse_order_status_history($order_row['item_status_history']);
                }

                $grouped_orders[$oid]['order_items'][] = [
                    'order_item_id' => $order_row['order_item_id'],
                    'product_name' => $product['name'],
                    'product_name_en' => isset($product['name_en']) ? $product['name_en'] : $order_row['product_name'],
                    'short_description' => isset($product['short_description']) ? $product['short_description'] : '',
                    'short_description_en' => isset($product['short_description_en']) ? $product['short_description_en'] : '',
                    'description' => isset($product['description']) ? $product['description'] : '',
                    'description_en' => isset($product['description_en']) ? $product['description_en'] : '',
                    'category_name' => isset($product['category_name']) ? $product['category_name'] : '',
                    'category_name_en' => isset($product['category_name_en']) ? $product['category_name_en'] : '',
                    'product_image' => !empty($order_row['product_image']) ? base_url($order_row['product_image']) : '',
                    'quantity' => $order_row['quantity'],
                    'price' => $order_row['price'],
                    'sub_total' => $order_row['sub_total'],
                    'status' => $order_row['item_status'],
                    'current_status' => $order_row['item_status'],
                    'status_history' => $status_history,
                    'weight' => isset($order_row['weight']) ? $order_row['weight'] : '0',
                    'height' => isset($order_row['height']) ? $order_row['height'] : '0',
                    'breadth' => isset($order_row['breadth']) ? $order_row['breadth'] : '0',
                    'length' => isset($order_row['length']) ? $order_row['length'] : '0',
                ];
            }
        }

        // Count by status
        $status_counts = $this->get_order_status_counts($shipping_company_id);

        if (!empty($grouped_orders)) {
            $this->response['error'] = false;
            $this->response['message'] = 'Orders retrieved successfully';
            $this->response['total'] = strval($total);
            $this->response['awaiting'] = strval($status_counts['awaiting']);
            $this->response['received'] = strval($status_counts['received']);
            $this->response['processed'] = strval($status_counts['processed']);
            $this->response['shipped'] = strval($status_counts['shipped']);
            $this->response['delivered'] = strval($status_counts['delivered']);
            $this->response['cancelled'] = strval($status_counts['cancelled']);
            $this->response['returned'] = strval($status_counts['returned']);
            $this->response['data'] = array_values($grouped_orders);
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'No orders found';
            $this->response['total'] = "0";
            $this->response['awaiting'] = "0";
            $this->response['received'] = "0";
            $this->response['processed'] = "0";
            $this->response['shipped'] = "0";
            $this->response['delivered'] = "0";
            $this->response['cancelled'] = "0";
            $this->response['returned'] = "0";
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    private function get_order_status_counts($shipping_company_id)
    {
        $statuses = ['awaiting', 'received', 'processed', 'shipped', 'delivered', 'cancelled', 'returned'];
        $counts = [];
        foreach ($statuses as $status) {
            $this->db->select('COUNT(DISTINCT oi.id) as count');
            $this->db->from('order_items oi');
            $this->db->join('orders o', 'o.id = oi.order_id');
            $this->db->where('o.shipping_company_id', $shipping_company_id);
            $this->db->where('oi.active_status', $status);
            $result = $this->db->get()->row_array();
            $counts[$status] = isset($result['count']) ? $result['count'] : 0;
        }
        return $counts;
    }

    // 4. Update order status
    public function update_order_status()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('order_item_id', 'Order Item ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|xss_clean|in_list[received,processed,shipped,delivered,cancelled,returned]');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }

        $shipping_company_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $order_item_id = $this->input->post('order_item_id', true);
        $status = $this->input->post('status', true);
        $otp = $this->input->post('otp', true);

        // Verify this order item belongs to an order assigned to this shipping company
        $order_item = $this->db->select('oi.*, o.shipping_company_id, o.payment_method, o.otp, o.id as order_id, o.user_id')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('oi.id', $order_item_id)
            ->get()->row_array();

        if (empty($order_item) || $order_item['shipping_company_id'] != $shipping_company_id) {
            $this->response['error'] = true;
            $this->response['message'] = "You don't have access to update this order";
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }

        // Validate status transition
        $res = validate_order_status($order_item_id, $status, 'order_items');
        if ($res['error']) {
            $this->response['error'] = true;
            $this->response['message'] = $res['message'];
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }

        // Check OTP for delivery if enabled
        $system_settings = get_settings('system_settings', true);
        if ($status == 'delivered' && isset($system_settings['is_delivery_boy_otp_setting_on']) && $system_settings['is_delivery_boy_otp_setting_on'] == 1) {
            if (empty($otp) || $otp != $order_item['otp']) {
                $this->response['error'] = true;
                $this->response['message'] = 'Invalid OTP supplied!';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return false;
            }
        }

        // Update order item status
        if ($this->Order_model->update_order(['status' => $status], ['id' => $order_item_id], true, 'order_items')) {
            $this->Order_model->update_order(['active_status' => $status], ['id' => $order_item_id], false, 'order_items');

            // Handle refund for cancelled/returned
            if ($status == 'cancelled' || $status == 'returned') {
                process_refund($order_item_id, $status, 'order_items');
                if ($status == 'cancelled') {
                    $data = fetch_details('order_items', ['id' => $order_item_id], 'product_variant_id,quantity');
                    update_stock($data[0]['product_variant_id'], $data[0]['quantity'], 'plus');
                }
            }

            // Handle COD cash collection on delivery
            if ($status == 'delivered' && strtoupper($order_item['payment_method']) == 'COD') {
                $this->handle_cod_delivery($order_item, $shipping_company_id);
            }

            // Send notification to customer
            $this->send_order_status_notification($order_item['order_id'], $order_item['user_id'], $status);

            $this->response['error'] = false;
            $this->response['message'] = 'Status Updated Successfully';
            $this->response['data'] = array();
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Failed to update status';
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    private function handle_cod_delivery($order_item, $shipping_company_id)
    {
        $item_amount = floatval($order_item['sub_total']);

        // Check if already recorded
        $exists = $this->db->where([
            'user_id' => $shipping_company_id,
            'order_id' => $order_item['order_id'],
            'order_item_id' => $order_item['id'],
            'type' => 'shipping_company_cash'
        ])->get('transactions')->row_array();

        if (empty($exists)) {
            // Update cash_received for shipping company
            $this->db->set('cash_received', 'cash_received + ' . $item_amount, FALSE)
                ->where('id', $shipping_company_id)->update('users');

            // Log transaction
            $transaction_data = [
                'transaction_type' => "transaction",
                'user_id' => $shipping_company_id,
                'order_id' => $order_item['order_id'],
                'order_item_id' => $order_item['id'],
                'type' => "shipping_company_cash",
                'txn_id' => "",
                'amount' => $item_amount,
                'status' => "1",
                'message' => "COD collected by shipping company for order " . $order_item['order_id'],
                'transaction_date' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('transactions', escape_array($transaction_data));
        }
    }

    private function send_order_status_notification($order_id, $user_id, $status)
    {
        $user_res = fetch_details('users', ['id' => $user_id], 'username,fcm_id,email,mobile,platform_type');
        if (!empty($user_res) && !empty($user_res[0]['fcm_id'])) {
            $settings = get_settings('system_settings', true);
            $app_name = isset($settings['app_name']) ? $settings['app_name'] : '';

            $fcmMsg = array(
                'title' => "Order status updated",
                'body' => "Your order #$order_id status has been updated to $status",
                'type' => "order",
            );
            // Send notification (if FCM configured)
            // send_notification($fcmMsg, [...], $fcmMsg);
        }
    }

    // 5. Get fund transfers
    public function get_fund_transfers()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $this->input->post('sort', true) : 'id';
        $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $this->input->post('order', true) : 'DESC';

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';

        $this->db->select('count(id) as total');
        $total_result = $this->db->where('shipping_company_id', $user_id)->get('fund_transfers')->row_array();
        $total = $total_result['total'];

        $this->db->select('*');
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);
        $fund_transfers = $this->db->where('shipping_company_id', $user_id)->get('fund_transfers')->result_array();

        if (!empty($fund_transfers)) {
            $this->response['error'] = false;
            $this->response['message'] = 'Data retrieved successfully';
            $this->response['total'] = strval($total);
            $this->response['data'] = $fund_transfers;
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'No fund transfers found';
            $this->response['total'] = "0";
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    // 6. Update user profile
    public function update_user()
    {
        if (!$this->verify_token()) {
            return false;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';

        $this->form_validation->set_rules('email', 'Email', 'xss_clean|trim|valid_email|edit_unique[users.id.' . $user_id . ']');
        $this->form_validation->set_rules('mobile', 'Mobile', 'xss_clean|trim|numeric|edit_unique[users.id.' . $user_id . ']');
        $this->form_validation->set_rules('username', 'Username', 'xss_clean|trim');

        if (!empty($_POST['old']) || !empty($_POST['new'])) {
            $this->form_validation->set_rules('old', 'Old Password', 'required|xss_clean');
            $this->form_validation->set_rules('new', 'New Password', 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');
        }

        if (!$this->form_validation->run()) {
            $response['error'] = true;
            $response['message'] = strip_tags(validation_errors());
            echo json_encode($response);
            return false;
        }

        // Handle password change
        if (!empty($_POST['old']) || !empty($_POST['new'])) {
            $identity = ($identity_column == 'mobile') ? 'mobile' : 'email';
            $res = fetch_details('users', ['id' => $user_id], '*');
            if (!empty($res) && $this->ion_auth->in_group('shipping_company', $res[0]['id'])) {
                if (!$this->ion_auth->change_password($res[0][$identity], $this->input->post('old'), $this->input->post('new'))) {
                    $response['error'] = true;
                    $response['message'] = strip_tags($this->ion_auth->errors());
                    echo json_encode($response);
                    return;
                }
            }
        }

        // Handle profile image upload
        $profile_image = '';
        if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
            if (!file_exists(FCPATH . USER_IMG_PATH)) {
                mkdir(FCPATH . USER_IMG_PATH, 0777, true);
            }
            $config = [
                'upload_path' => FCPATH . USER_IMG_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif|webp',
                'max_size' => 8000,
            ];
            $this->upload->initialize($config);
            if ($this->upload->do_upload('image')) {
                $temp_array = $this->upload->data();
                $profile_image = USER_IMG_PATH . $temp_array['file_name'];
            }
        }

        $set = [];
        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $set['username'] = $this->input->post('username', true);
        }
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $set['email'] = $this->input->post('email', true);
        }
        if (isset($_POST['mobile']) && !empty($_POST['mobile'])) {
            $set['mobile'] = $this->input->post('mobile', true);
        }
        if (isset($_POST['address']) && !empty($_POST['address'])) {
            $set['address'] = $this->input->post('address', true);
        }
        if (!empty($profile_image)) {
            $set['image'] = $profile_image;
        }

        if (!empty($set)) {
            $set = escape_array($set);
            $this->db->set($set)->where('id', $user_id)->update($this->tables['login_users']);
        }

        $response['error'] = false;
        $response['message'] = 'Profile Updated Successfully';
        echo json_encode($response);
    }

    // 7. Update FCM
    public function update_fcm()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $this->form_validation->set_rules('fcm_id', 'FCM ID', 'trim|xss_clean');
        $this->form_validation->set_rules('device_type', 'Device Type', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        if (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) {
            update_details(['fcm_id' => $_POST['fcm_id'], 'platform_type' => $_POST['device_type']], ['id' => $user_id], 'users');
            $response['error'] = false;
            $response['message'] = 'FCM Updated Successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'FCM ID is required';
        }
        echo json_encode($response);
    }

    // 8. Reset password
    public function reset_password()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
        }

        $this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('new', 'New Password', 'trim|required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $res = fetch_details('users', ['mobile' => $_POST['mobile_no']]);

        if (!empty($res) && $this->ion_auth->in_group('shipping_company', $res[0]['id'])) {
            $identity = ($identity_column == 'email') ? $res[0]['email'] : $res[0]['mobile'];
            if (!$this->ion_auth->reset_password($identity, $_POST['new'])) {
                $response['error'] = true;
                $response['message'] = strip_tags($this->ion_auth->messages());
            } else {
                $response['error'] = false;
                $response['message'] = 'Password Reset Successfully';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'User does not exist!';
        }
        $response['data'] = array();
        echo json_encode($response);
    }

    // 9. Get notifications
    public function get_notifications()
    {
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $_POST['sort'] : 'id';

            $res = $this->notification_model->get_notifications($offset, $limit, $sort, $order);
            $this->response['error'] = false;
            $this->response['message'] = 'Notifications Retrieved Successfully';
            $this->response['total'] = $res['total'];
            $this->response['data'] = $res['data'];
        }
        print_r(json_encode($this->response));
    }

    // 10. Verify user
    public function verify_user()
    {
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('country_code', 'Country code', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $country_code = $this->input->post('country_code', true);
        $is_forgot_password = isset($_POST['is_forgot_password']) ? $_POST['is_forgot_password'] : 0;

        if ($is_forgot_password == 1 && !is_exist(['mobile' => $mobile], 'users')) {
            $this->response['error'] = true;
            $this->response['message'] = 'Mobile is not registered!';
            print_r(json_encode($this->response));
            return;
        }

        $user_data = fetch_details('users', ['mobile' => $mobile], 'id');
        if (!empty($user_data) && $this->ion_auth->in_group('shipping_company', $user_data[0]['id'])) {
            $auth_settings = get_settings('authentication_settings', true);
            if (isset($auth_settings['authentication_method']) && $auth_settings['authentication_method'] == "sms") {
                if (!is_exist(['mobile' => $mobile], 'otps')) {
                    $this->db->insert('otps', ['mobile' => $mobile]);
                }
                $otp = random_int(100000, 999999);
                set_user_otp($mobile, $otp, $country_code);
                $this->response['error'] = false;
                $this->response['message'] = 'OTP sent successfully!';
            } else {
                $this->response['error'] = false;
                $this->response['message'] = 'Mobile number verified.';
            }
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'You are not registered as a shipping company!';
        }
        print_r(json_encode($this->response));
    }

    // 11. Get settings
    public function get_settings()
    {
        $this->form_validation->set_rules('type', 'Setting Type', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $settings = get_settings('system_settings', true);
        $type = $_POST['type'];

        $allowed_settings = ['shipping_company_terms_conditions', 'shipping_company_privacy_policy', 'terms_conditions', 'privacy_policy', 'currency', 'authentication_settings', 'shipping_method'];

        if (!in_array($type, $allowed_settings)) {
            $this->response['error'] = true;
            $this->response['message'] = 'Invalid setting type';
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $settings_res = get_settings($type);
        // Fallback for shipping company specific settings
        if (empty($settings_res) && strpos($type, 'shipping_company_') === 0) {
            $fallback_type = str_replace('shipping_company_', '', $type);
            $settings_res = get_settings($fallback_type);
        }

        $this->response['error'] = false;
        $this->response['message'] = 'Settings retrieved successfully';
        $this->response['data'] = $settings_res;
        $this->response['currency'] = get_settings('currency');
        $this->response['system_settings'] = $settings;
        print_r(json_encode($this->response));
    }

    // 12. Get cash collection
    public function get_cash_collection()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('status', 'status', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $this->input->post('sort', true) : 'id';
        $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $this->input->post('order', true) : 'DESC';
        $status = (isset($_POST['status']) && !empty($_POST['status'])) ? $this->input->post('status', true) : '';

        // Get transactions
        $this->db->select('t.*, u.username, u.mobile, u.cash_received');
        $this->db->from('transactions t');
        $this->db->join('users u', 'u.id = t.user_id');
        $this->db->where('t.user_id', $user_id);
        $this->db->group_start();
        $this->db->where('t.type', 'shipping_company_cash');
        $this->db->or_where('t.type', 'shipping_company_cash_collection');
        $this->db->group_end();

        if (!empty($status)) {
            $this->db->where('t.type', $status);
        }

        // Count total
        $count_query = clone $this->db;
        $total = $count_query->count_all_results();

        // Get data
        $this->db->order_by('t.' . $sort, $order);
        $this->db->limit($limit, $offset);
        $transactions = $this->db->get()->result_array();

        $rows = [];
        foreach ($transactions as $row) {
            $rows[] = [
                'id' => $row['id'],
                'order_id' => $row['order_id'],
                'amount' => $row['amount'],
                'type' => $row['type'],
                'type_label' => ($row['type'] == 'shipping_company_cash') ? 'Received' : 'Collected',
                'message' => $row['message'],
                'transaction_date' => $row['transaction_date'],
                'cash_received' => $row['cash_received']
            ];
        }

        // Get cash summary
        $user_data = fetch_details('users', ['id' => $user_id], 'cash_received');
        $cash_in_hand = isset($user_data[0]['cash_received']) ? $user_data[0]['cash_received'] : 0;

        if (!empty($rows)) {
            $this->response['error'] = false;
            $this->response['message'] = 'Cash collection retrieved successfully';
            $this->response['total'] = strval($total);
            $this->response['cash_in_hand'] = strval($cash_in_hand);
            $this->response['data'] = $rows;
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'No cash collection records found';
            $this->response['total'] = "0";
            $this->response['cash_in_hand'] = strval($cash_in_hand);
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    // 13. Delete shipping company account
    public function delete_shipping_company()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return false;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $user_data = fetch_details('users', ['id' => $user_id, 'mobile' => $_POST['mobile']]);

        if (empty($user_data)) {
            $response['error'] = true;
            $response['message'] = 'User Not Found';
            echo json_encode($response);
            return;
        }

        $login = $this->ion_auth->login($this->input->post('mobile'), $this->input->post('password'), false, 'phone');
        if (!$login) {
            $response['error'] = true;
            $response['message'] = 'Invalid credentials';
            echo json_encode($response);
            return;
        }

        // Check for pending orders
        // Some installs use `active_status` on order items/tables; use existing column on `orders` table
        $status_column = $this->db->field_exists('active_status', 'orders') ? 'active_status' : 'status';
        $pending_orders = $this->db->select('COUNT(*) as count')
            ->from('orders')
            ->where('shipping_company_id', $user_id)
            ->where_not_in($status_column, ['delivered', 'cancelled', 'returned'])
            ->get()->row_array();

        if ($pending_orders['count'] > 0) {
            $response['error'] = true;
            $response['message'] = 'Cannot delete account. You have pending orders to deliver.';
            echo json_encode($response);
            return;
        }

        // Delete user
        delete_details(['id' => $user_id], 'users');
        delete_details(['user_id' => $user_id], 'users_groups');

        $response['error'] = false;
        $response['message'] = 'Account Deleted Successfully';
        echo json_encode($response);
    }

    // 14. Verify OTP
    public function verify_otp()
    {
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $otp = $this->input->post('otp', true);

        $otps = fetch_details('otps', ['mobile' => $mobile]);
        if (empty($otps)) {
            $this->response['error'] = true;
            $this->response['message'] = 'OTP not found';
            print_r(json_encode($this->response));
            return;
        }

        $time_expire = checkOTPExpiration($otps[0]['created_at']);
        if ($time_expire['error'] == 1) {
            $this->response['error'] = true;
            $this->response['message'] = $time_expire['message'];
            print_r(json_encode($this->response));
            return;
        }

        if ($otps[0]['otp'] != $otp) {
            $this->response['error'] = true;
            $this->response['message'] = 'Invalid OTP';
        } else {
            update_details(['varified' => 1], ['mobile' => $mobile], 'otps');
            $this->response['error'] = false;
            $this->response['message'] = 'OTP Verified Successfully';
        }
        $this->response['data'] = array();
        print_r(json_encode($this->response));
    }

    // 15. Resend OTP
    public function resend_otp()
    {
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('country_code', 'Country Code', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $country_code = $this->input->post('country_code', true);

        $otp = random_int(100000, 999999);
        set_user_otp($mobile, $otp, $country_code);

        $this->response['error'] = false;
        $this->response['message'] = 'OTP sent successfully';
        print_r(json_encode($this->response));
    }

    // 16. Register shipping company
    public function register()
    {
        $this->form_validation->set_rules('company_name', 'Company Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]|is_unique[users.mobile]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        }

        // Handle KYC document upload
        if (!file_exists(FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH)) {
            mkdir(FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH, 0777, true);
        }

        $kyc_docs = [];
        if (isset($_FILES['kyc_documents']) && !empty($_FILES['kyc_documents']['name'][0])) {
            $config = [
                'upload_path' => FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH,
                'allowed_types' => implode('|', allowed_media_types()),
                'max_size' => 8000,
            ];

            $files = $_FILES;
            $doc_count = count($files['kyc_documents']['name']);

            for ($i = 0; $i < $doc_count; $i++) {
                if (!empty($files['kyc_documents']['name'][$i])) {
                    $_FILES['temp_doc']['name'] = $files['kyc_documents']['name'][$i];
                    $_FILES['temp_doc']['type'] = $files['kyc_documents']['type'][$i];
                    $_FILES['temp_doc']['tmp_name'] = $files['kyc_documents']['tmp_name'][$i];
                    $_FILES['temp_doc']['error'] = $files['kyc_documents']['error'][$i];
                    $_FILES['temp_doc']['size'] = $files['kyc_documents']['size'][$i];

                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('temp_doc')) {
                        $temp_array = $this->upload->data();
                        $kyc_docs[] = SHIPPING_COMPANY_DOCUMENTS_PATH . $temp_array['file_name'];
                    }
                }
            }
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $email = strtolower($this->input->post('email', true));
        $mobile = $this->input->post('mobile', true);
        $identity = ($identity_column == 'mobile') ? $mobile : $email;
        $password = $this->input->post('password', true);

        $serviceable_zipcodes = isset($_POST['serviceable_zipcodes']) ? implode(",", $this->input->post('serviceable_zipcodes', true)) : NULL;
        $serviceable_cities = isset($_POST['serviceable_cities']) ? implode(",", $this->input->post('serviceable_cities', true)) : NULL;

        $additional_data = [
            'username' => $this->input->post('company_name', true),
            'address' => $this->input->post('address', true),
            'serviceable_zipcodes' => $serviceable_zipcodes,
            'serviceable_cities' => $serviceable_cities,
            'type' => 'phone',
            'kyc_documents' => implode(',', $kyc_docs),
            'status' => 0,
            'is_shipping_company' => 1
        ];

        $user_id = $this->ion_auth->register($identity, $password, $email, $additional_data, ['6']);

        if ($user_id) {
            update_details(['active' => 1], ['id' => $user_id], 'users');
            $this->response['error'] = false;
            $this->response['message'] = 'Shipping Company registered successfully. Please wait for admin approval.';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Registration failed. Please try again.';
        }
        print_r(json_encode($this->response));
    }

    // 17. Get zipcodes (for shipping company - provider_type = 'company')
    public function get_zipcodes()
    {
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
            $search = (isset($_POST['search']) && !empty($_POST['search'])) ? $this->input->post('search', true) : '';

            // Get zipcodes with provider_type = 'company' for shipping companies
            $res = $this->Area_model->get_zipcodes($search, $limit, $offset, '', 'company');
            $this->response['error'] = false;
            $this->response['message'] = 'Zipcodes Retrieved Successfully';
            $this->response['total'] = $res['total'];
            $this->response['data'] = $res['data'];
        }
        print_r(json_encode($this->response));
    }

    // 18. Get cities
    public function get_cities()
    {
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $this->input->post('limit', true) : 10;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset'])) ? $this->input->post('offset', true) : 0;
            $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $this->input->post('sort', true) : 'c.name';
            $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $this->input->post('order', true) : 'ASC';
            $search = (isset($_POST['search']) && !empty($_POST['search'])) ? $this->input->post('search', true) : "";

            $result = $this->Area_model->get_cities($sort, $order, $search, $limit, $offset);
            print_r(json_encode($result));
            return;
        }
        print_r(json_encode($this->response));
    }

    // 19. Get statistics for dashboard
    public function get_statistics()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';

        // Get order counts by status
        $status_counts = $this->get_order_status_counts($user_id);

        // Get user balance and cash received
        $user_data = fetch_details('users', ['id' => $user_id], 'balance, cash_received');
        $balance = isset($user_data[0]['balance']) ? $user_data[0]['balance'] : 0;
        $cash_in_hand = isset($user_data[0]['cash_received']) ? $user_data[0]['cash_received'] : 0;

        // Get payout summary (for prepaid orders)
        $payout_info = $this->Shipping_company_model->get_pending_payout($user_id);

        // Get total orders
        $total_orders = $this->db->select('COUNT(DISTINCT o.id) as count')
            ->from('orders o')
            ->where('o.shipping_company_id', $user_id)
            ->get()->row_array();

        // Get today's deliveries
        $today = date('Y-m-d');
        $today_delivered = $this->db->select('COUNT(DISTINCT oi.id) as count')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('o.shipping_company_id', $user_id)
            ->where('oi.active_status', 'delivered')
            ->where('DATE(oi.date_added)', $today)
            ->get()->row_array();

        $this->response['error'] = false;
        $this->response['message'] = 'Statistics retrieved successfully';
        $this->response['data'] = [
            'total_orders' => strval($total_orders['count']),
            'balance' => strval($balance),
            'cash_in_hand' => strval($cash_in_hand),
            'today_delivered' => strval($today_delivered['count']),
            'awaiting' => strval($status_counts['awaiting']),
            'received' => strval($status_counts['received']),
            'processed' => strval($status_counts['processed']),
            'shipped' => strval($status_counts['shipped']),
            'delivered' => strval($status_counts['delivered']),
            'cancelled' => strval($status_counts['cancelled']),
            'returned' => strval($status_counts['returned']),
            'total_earnings' => strval($payout_info['total_earnings']),
            'total_paid' => strval($payout_info['total_paid']),
            'pending_payout' => strval($payout_info['pending_amount'])
        ];
        print_r(json_encode($this->response));
    }

    // 20. Get payout summary (prepaid order earnings)
    public function get_payout_summary()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $payout_info = $this->Shipping_company_model->get_pending_payout($user_id);

        $this->response['error'] = false;
        $this->response['message'] = 'Payout summary retrieved successfully';
        $this->response['data'] = [
            'total_earnings' => strval($payout_info['total_earnings']),
            'total_paid' => strval($payout_info['total_paid']),
            'pending_amount' => strval($payout_info['pending_amount']),
            'order_count' => strval($payout_info['order_count'])
        ];
        print_r(json_encode($this->response));
    }

    // 21. Send withdrawal request
    public function send_withdrawal_request()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('payment_address', 'Payment Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric|greater_than[0]');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $payment_address = $this->input->post('payment_address', true);
        $amount = floatval($this->input->post('amount', true));

        $user_data = fetch_details('users', ['id' => $user_id], 'balance');
        $current_balance = isset($user_data[0]['balance']) ? floatval($user_data[0]['balance']) : 0;

        if ($amount > $current_balance) {
            $this->response['error'] = true;
            $this->response['message'] = 'Insufficient balance for withdrawal';
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $data = [
            'user_id' => $user_id,
            'payment_address' => $payment_address,
            'payment_type' => 'shipping_company',
            'amount_requested' => $amount,
        ];

        if (insert_details($data, 'payment_requests')) {
            $this->Shipping_company_model->update_balance($amount, $user_id, 'deduct');
            $new_balance = fetch_details('users', ['id' => $user_id], 'balance');

            $this->response['error'] = false;
            $this->response['message'] = 'Withdrawal Request Sent Successfully';
            $this->response['data'] = ['new_balance' => $new_balance[0]['balance']];
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Failed to submit withdrawal request';
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    // 22. Get withdrawal requests
    public function get_withdrawal_request()
    {
        if (!$this->verify_token()) {
            return false;
        }

        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }

        $user_id = isset($this->user_details['id']) ? $this->user_details['id'] : '';
        $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : 25;
        $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : 0;

        $requests = fetch_details('payment_requests', ['user_id' => $user_id, 'payment_type' => 'shipping_company'], '*', $limit, $offset, 'id', 'DESC');

        $this->response['error'] = false;
        $this->response['message'] = 'Withdrawal Requests Retrieved Successfully';
        $this->response['data'] = $requests;
        $this->response['total'] = strval(count($requests));
        print_r(json_encode($this->response));
    }

    // Helper function to format user data
    private function format_user_data($row)
    {
        $tempRow = [];
        $keys = ['id', 'ip_address', 'username', 'email', 'mobile', 'balance', 'created_on', 'last_login', 'active', 'company', 'address', 'bonus', 'cash_received', 'dob', 'country_code', 'city', 'area', 'street', 'pincode', 'serviceable_zipcodes', 'serviceable_cities', 'apikey', 'fcm_id', 'latitude', 'longitude', 'status', 'is_shipping_company', 'created_at'];

        foreach ($keys as $key) {
            $tempRow[$key] = isset($row[$key]) && !empty($row[$key]) ? $row[$key] : '';
        }

        $tempRow['image'] = empty($row['image']) || !file_exists(FCPATH . $row['image']) ? base_url() . NO_USER_IMAGE : base_url() . $row['image'];

        return $tempRow;
    }

    /**
     * Apply language transformation to products array
     * When lang=ar, swaps Arabic content into main fields with English fallback
     *
     * @param array $products Array of products
     * @param string $lang Language code (ar for Arabic)
     * @return array Transformed products array
     */
    private function apply_product_language($products, $lang)
    {
        if ($lang != 'ar') {
            return $products;
        }

        foreach ($products as &$product) {
            // Store English values with _en suffix
            $product['name_en'] = isset($product['name']) ? $product['name'] : '';
            $product['short_description_en'] = isset($product['short_description']) ? $product['short_description'] : '';
            $product['description_en'] = isset($product['description']) ? $product['description'] : '';
            $product['category_name_en'] = isset($product['category_name']) ? $product['category_name'] : '';

            // Use Arabic if available, else fallback to English
            if (!empty($product['name_ar'])) {
                $product['name'] = $product['name_ar'];
            }
            if (!empty($product['short_description_ar'])) {
                $product['short_description'] = $product['short_description_ar'];
            }
            if (!empty($product['description_ar'])) {
                $product['description'] = $product['description_ar'];
            }
            if (!empty($product['category_name_ar'])) {
                $product['category_name'] = $product['category_name_ar'];
            }
        }

        return $products;
    }

    /**
     * Apply language transformation to categories array (recursive for children)
     * When lang=ar, swaps Arabic content into main fields with English fallback
     *
     * @param array $categories Array of categories
     * @param string $lang Language code (ar for Arabic)
     * @return array Transformed categories array
     */
    private function apply_category_language($categories, $lang)
    {
        if ($lang != 'ar') {
            return $categories;
        }

        // Ensure categories is an array
        if (!is_array($categories)) {
            return $categories;
        }

        foreach ($categories as &$category) {
            // Skip if category is not an array or object (could be string, null, etc.)
            if (!is_array($category) && !is_object($category)) {
                continue;
            }

            // Handle both object and array format
            if (is_object($category)) {
                // Store English value
                $category->name_en = isset($category->name) ? $category->name : '';

                // Use Arabic if available, else fallback to English
                if (!empty($category->name_ar)) {
                    $category->name = $category->name_ar;
                    $category->text = output_escaping($category->name_ar);
                }

                // Recursively process children (sub-categories)
                if (isset($category->children) && !empty($category->children)) {
                    $category->children = $this->apply_category_language($category->children, $lang);
                }

                // Also handle products if they exist inside category
                if (isset($category->products) && is_array($category->products) && !empty($category->products)) {
                    $category->products = $this->apply_product_language($category->products, $lang);
                }
            } else {
                // Array format
                $category['name_en'] = isset($category['name']) ? $category['name'] : '';

                if (!empty($category['name_ar'])) {
                    $category['name'] = $category['name_ar'];
                    if (isset($category['text'])) {
                        $category['text'] = output_escaping($category['name_ar']);
                    }
                }

                // Recursively process children (sub-categories)
                if (isset($category['children']) && !empty($category['children'])) {
                    $category['children'] = $this->apply_category_language($category['children'], $lang);
                }

                // Also handle products if they exist inside category
                if (isset($category['products']) && is_array($category['products']) && !empty($category['products'])) {
                    $category['products'] = $this->apply_product_language($category['products'], $lang);
                }
            }
        }

        return $categories;
    }
}
