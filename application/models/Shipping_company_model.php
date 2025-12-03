<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_company_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    function update_shipping_company($data)
    {
        // sanitize input (you already do this with escape_array above; keep it)
        $data = escape_array($data);
        // prefer 'serviceable_zipcodes' (controller sets this), fallback to 'assign_zipcode'
        $zipcodes = NULL;
        if (isset($data['serviceable_zipcodes']) && $data['serviceable_zipcodes'] !== '') {
            // could be array or comma string
            if (is_array($data['serviceable_zipcodes'])) {
                $zipcodes = implode(',', $data['serviceable_zipcodes']);
            } else {
                $zipcodes = $data['serviceable_zipcodes'];
            }
        } elseif (isset($data['assign_zipcode']) && $data['assign_zipcode'] !== '') {
            if (is_array($data['assign_zipcode'])) {
                $zipcodes = implode(',', $data['assign_zipcode']);
            } else {
                $zipcodes = $data['assign_zipcode'];
            }
        } else {
            $zipcodes = NULL;
        }

        // same for cities if you store them
        $cities = NULL;
        if (isset($data['serviceable_cities']) && $data['serviceable_cities'] !== '') {
            if (is_array($data['serviceable_cities'])) {
                $cities = implode(',', $data['serviceable_cities']);
            } else {
                $cities = $data['serviceable_cities'];
            }
        }

        $company_data = [
            'username' => isset($data['company_name']) ? $data['company_name'] : NULL,
            'email' => isset($data['email']) ? $data['email'] : NULL,
            'mobile' => isset($data['mobile']) ? $data['mobile'] : NULL,
            'address' => isset($data['address']) ? $data['address'] : NULL,
            'serviceable_zipcodes' => $zipcodes,
            'serviceable_cities' => $cities,
            'kyc_documents' => isset($data['kyc_documents']) ? $data['kyc_documents'] : NULL,
            'status' => isset($data['status']) ? $data['status'] : NULL,
        ];

        // remove keys with NULL if you don't want them to overwrite existing DB values
        foreach ($company_data as $k => $v) {
            if ($v === NULL) {
                unset($company_data[$k]);
            }
        }

        // update
        if (isset($data['edit_shipping_company']) && !empty($data['edit_shipping_company'])) {
            $this->db->set($company_data)->where('id', $data['edit_shipping_company'])->update('users');
            return $this->db->affected_rows() !== 0;
        }

        return false;
    }


    function get_shipping_companies_list($get_company_status = "")
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'ASC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else if ($_GET['sort'] == 'date') {
                $sort = 'created_at';
            } else {
                $sort = $_GET['sort'];
            }

        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '6';
            $count_res->where($where);
        }
        if ($get_company_status == "approved") {
            $count_res->where('u.status', '1');
        }
        if ($get_company_status == "not_approved") {
            $count_res->where('u.status', '0');
        }

        $company_count = $count_res->get('users u')->result_array();

        foreach ($company_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' u.* ')->join('users_groups ug', ' ug.user_id = u.id ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '6';
            $search_res->where($where);
        }
        if ($get_company_status == "approved") {
            $search_res->where('u.status', '1');
        }
        if ($get_company_status == "not_approved") {
            $search_res->where('u.status', '0');
        }

        $company_search_res = $search_res->order_by($sort, "asc")->limit($limit, $offset)->get('users u')->result_array();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($company_search_res as $row) {
            $row = output_escaping($row);
            $operate = '<a href="javascript:void(0)" class="edit_btn btn action-btn btn-primary btn-xs mr-1 ml-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-url="admin/shipping_companies/"><i class="fa fa-pen"></i></a>';
            $operate .= '<a  href="javascript:void(0)" class="btn btn-danger action-btn btn-xs mr-1 mb-1 ml-1" title="Delete" id="delete-shipping-company"  data-id="' . $row['id'] . '" ><i class="fa fa-trash"></i></a>';
            $operate .= '<a href="javascript:void(0)" class="fund_transfer_shipping_company action-btn btn btn-info btn-xs mr-1 mb-1 ml-1" title="Fund Transfer" data-target="#fund_transfer_shipping_company"   data-toggle="modal" data-id="' . $row['id'] . '" ><i class="fa fa-arrow-alt-circle-right"></i></a>';

            $tempRow['id'] = $row['id'];
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

            // Status
            if ($row['status'] == 0) {
                $tempRow['status'] = "<label class='badge badge-warning'>Not-Approved</label>";
            } else if ($row['status'] == 1) {
                $tempRow['status'] = "<label class='badge badge-success'>Approved</label>";
            }

            $tempRow['address'] = $row['address'];
            // $tempRow['balance'] =  $row['balance'] == null || $row['balance'] == 0 || empty($row['balance']) ? "0" : number_format($row['balance'], 2);
            $tempRow['cash_received'] = $row['cash_received'];
            $tempRow['date'] = date('d-m-Y', strtotime($row['created_at']));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function update_balance($amount, $company_id, $action)
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
        return $this->db->where('id', $company_id)->update('users');
    }

    function get_cash_collection_list($user_id = '')
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $multipleWhere = '';
        $where = [];

        if (isset($_GET['filter_date']) && $_GET['filter_date'] != NULL)
            $where = ['DATE(transactions.transaction_date)' => $_GET['filter_date']];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['`transactions.id`' => $search, '`transactions.amount`' => $search, '`transactions.date_created`' => $search, 'users.username' => $search, 'users.mobile' => $search, 'users.email' => $search, 'transactions.order_id' => $search, 'transactions.type' => $search, 'transactions.status' => $search];
        }
        if (isset($_GET['filter_company']) && !empty($_GET['filter_company']) && $_GET['filter_company'] != NULL) {
            $where = ['users.id' => $_GET['filter_company']];
        }
        if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
            $where = ['transactions.type' => $_GET['filter_status']];
        }
        if (!empty($user_id)) {
            $user_where = ['users.id' => $user_id];
        }

        $count_res = $this->db->select(' COUNT(transactions.id) as `total` ')->join('users', ' transactions.user_id = users.id', 'left')->where('(transactions.status = "1" OR transactions.status = "success")')->where('(transactions.type = "shipping_company_cash" OR transactions.type = "shipping_company_cash_collection")');

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $count_res->where(" DATE(transactions.transaction_date) >= DATE('" . $_GET['start_date'] . "') ");
            $count_res->where(" DATE(transactions.transaction_date) <= DATE('" . $_GET['end_date'] . "') ");
        }

        if (isset($_GET['filter_company']) && !empty($_GET['filter_company']) && $_GET['filter_company'] != NULL) {
            $count_res->where('users.id', $_GET['filter_company']);
        }
        if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
            $count_res->where('transactions.type', $_GET['filter_status']);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $this->db->group_Start();
            $count_res->or_like($multipleWhere);
            $this->db->group_End();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        if (isset($user_where) && !empty($user_where)) {
            $count_res->where($user_where);
        }

        $txn_count = $count_res->get('transactions')->result_array();

        foreach ($txn_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' transactions.*,users.username as name,users.mobile,users.id as shipping_company_id,users.cash_received');

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $search_res->where(" DATE(transactions.transaction_date) >= DATE('" . $_GET['start_date'] . "') ");
            $search_res->where(" DATE(transactions.transaction_date) <= DATE('" . $_GET['end_date'] . "') ");
        }

        if (isset($_GET['filter_company']) && !empty($_GET['filter_company']) && $_GET['filter_company'] != NULL) {
            $search_res->where('users.id', $_GET['filter_company']);
        }
        if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
            $search_res->where('transactions.type', $_GET['filter_status']);
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $this->db->group_Start();
            $search_res->or_like($multipleWhere);
            $this->db->group_End();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }
        if (isset($user_where) && !empty($user_where)) {
            $search_res->where($user_where);
        }
        $search_res->join('users', ' transactions.user_id = users.id', 'left')->where('(transactions.status = "1" OR transactions.status = "success")')->where('(transactions.type = "shipping_company_cash" OR transactions.type = "shipping_company_cash_collection")');
        $txn_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('transactions')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($txn_search_res as $row) {
            $row = output_escaping($row);

            if ((isset($row['type']) && $row['type'] == "shipping_company_cash")) {
                $operate = '<a href="javascript:void(0)" class="edit_cash_collection_btn btn action-btn btn-primary btn-xs mr-1 ml-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-order-id="' . $row['order_id'] . '" data-amount="' . $row['amount'] . '" data-company-id="' . $row['shipping_company_id'] . '"  data-toggle="modal" data-target="#cash_collection_model"><i class="fa fa-pen"></i></a>';
            } else {
                $operate = '';
            }

            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['order_id'] = $row['order_id'];
            $tempRow['cash_received'] = $row['cash_received'];
            $tempRow['type'] = (isset($row['type']) && $row['type'] == "shipping_company_cash") ? '<label class="badge badge-danger">Received</label>' : '<label class="badge badge-success">Collected</label>';
            $tempRow['amount'] = $row['amount'];
            $tempRow['message'] = $row['message'];
            $tempRow['txn_date'] =  date('d-m-Y', strtotime($row['transaction_date']));
            $tempRow['date'] =  date('d-m-Y', strtotime($row['date_created']));
            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function get_fund_transfers_list()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $multipleWhere = '';
        $where = [];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = [
                '`transactions.id`' => $search,
                '`transactions.amount`' => $search,
                '`transactions.date_created`' => $search,
                'users.username' => $search,
                'users.mobile' => $search,
                'users.email' => $search,
                'transactions.type' => $search,
                'transactions.status' => $search
            ];
        }

        $count_res = $this->db->select(' COUNT(transactions.id) as `total` ')
            ->join('users', ' transactions.user_id = users.id', 'left')
            ->join('users_groups ug', 'ug.user_id = users.id', 'left')
            ->where('ug.group_id', '6')
            ->where('transactions.transaction_type', 'transaction')
            ->where_in('transactions.type', ['credit', 'debit']);

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $this->db->group_Start();
            $count_res->or_like($multipleWhere);
            $this->db->group_End();
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $txn_count = $count_res->get('transactions')->result_array();

        foreach ($txn_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' transactions.*, users.username as name, users.mobile, users.balance');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $this->db->group_Start();
            $search_res->or_like($multipleWhere);
            $this->db->group_End();
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $search_res->join('users', ' transactions.user_id = users.id', 'left')
            ->join('users_groups ug', 'ug.user_id = users.id', 'left')
            ->where('ug.group_id', '6')
            ->where('transactions.transaction_type', 'transaction')
            ->where_in('transactions.type', ['credit', 'debit']);

        $txn_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('transactions')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($txn_search_res as $row) {
            $row = output_escaping($row);
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['opening_balance'] = $row['balance'];

            if ($row['type'] == 'credit') {
                $tempRow['closing_balance'] = floatval($row['balance']) + floatval($row['amount']);
            } else {
                $tempRow['closing_balance'] = floatval($row['balance']) - floatval($row['amount']);
            }

            $tempRow['amount'] = $row['amount'];
            $tempRow['status'] = $row['type'] == 'credit'
                ? '<label class="badge badge-success">Credit</label>'
                : '<label class="badge badge-danger">Debit</label>';
            $tempRow['message'] = $row['message'];
            $tempRow['date_created'] = date('d-m-Y', strtotime($row['date_created']));

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }


    /**
     * Transfer funds from admin to shipping company (wrapped in DB transaction)
     *
     * @param int $company_id
     * @param float $amount
     * @param mixed $order_id (nullable)
     * @param string $message
     * @param string|null $transaction_date (YYYY-MM-DD HH:MM:SS or null)
     * @param string|null $txn_note
     * @return array ['status' => bool, 'message' => string]
     */
    public function transfer_from_admin_to_company($company_id, $amount, $order_id = null, $message = '', $transaction_date = null, $txn_note = '')
    {
        // ensure we have DB loaded
        $this->load->database();

        // find admin user (group_id = 1)
        $admin = $this->db->select('u.id, u.balance')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->where('ug.group_id', '1')
            ->limit(1)
            ->get('users u')
            ->row_array();

        if (empty($admin)) {
            return ['status' => false, 'message' => 'Admin user not found'];
        }

        $admin_balance = floatval($admin['balance'] ?? 0);
        if ($admin_balance < $amount) {
            return ['status' => false, 'message' => 'Admin balance insufficient'];
        }

        $company = fetch_details('users', ['id' => $company_id], 'id,username,balance');
        if (empty($company)) {
            return ['status' => false, 'message' => 'Shipping company not found'];
        }

        if (empty($transaction_date)) {
            $transaction_date = date('Y-m-d H:i:s');
        }

        // start DB transaction
        $this->db->trans_start();

        // deduct admin balance
        $this->db->set('balance', 'balance - ' . $amount, FALSE)
            ->where('id', $admin['id'])
            ->update('users');

        // credit company balance
        $this->db->set('balance', 'balance + ' . $amount, FALSE)
            ->where('id', $company_id)
            ->update('users');

        // create admin transaction (debit)
        $admin_txn = [
            'transaction_type' => "transaction",
            'user_id' => $admin['id'],
            'order_id' => $order_id,
            'type' => "debit",
            'txn_id' => "ADMIN_PAYOUT_" . time(),
            'amount' => $amount,
            'status' => "1",
            'message' => "Payout to shipping company: " . (isset($company[0]['username']) ? $company[0]['username'] : $company_id) . ($txn_note ? " - {$txn_note}" : ''),
            'transaction_date' => $transaction_date,
        ];
        $this->db->insert('transactions', escape_array($admin_txn));

        // create company transaction (credit)
        $company_txn = [
            'transaction_type' => "transaction",
            'user_id' => $company_id,
            'order_id' => $order_id,
            'type' => "credit",
            'txn_id' => "COMPANY_PAYOUT_" . time(),
            'amount' => $amount,
            'status' => "1",
            'message' => $message . ($txn_note ? " - {$txn_note}" : ''),
            'transaction_date' => $transaction_date,
        ];
        $this->db->insert('transactions', escape_array($company_txn));

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Database transaction failed.'];
        }

        return ['status' => true, 'message' => 'Amount successfully transferred to shipping company'];
    }

    /**
     * Calculate pending payout for a shipping company
     *
     * Pending = (Total delivery charges from delivered prepaid orders) - (Total already paid via fund transfers)
     *
     * @param int $company_id
     * @return array ['total_earnings' => float, 'total_paid' => float, 'pending_amount' => float, 'order_count' => int]
     */
    public function get_pending_payout($company_id)
    {
        $company_id = (int)$company_id;

        // 1. Calculate total delivery charges from delivered PREPAID orders assigned to this shipping company
        // We look at orders where:
        //   - shipping_company_id = this company
        //   - payment_method is NOT 'COD' (prepaid orders only - COD is handled via cash collection)
        //   - order has at least one delivered item

        $this->db->select('SUM(o.delivery_charge) as total_earnings, COUNT(DISTINCT o.id) as order_count');
        $this->db->from('orders o');
        $this->db->where('o.shipping_company_id', $company_id);
        $this->db->where('UPPER(o.payment_method) !=', 'COD');
        // Check that order has at least one delivered item
        $this->db->where('EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.active_status = "delivered")', NULL, FALSE);

        $earnings_result = $this->db->get()->row_array();

        $total_earnings = isset($earnings_result['total_earnings']) ? floatval($earnings_result['total_earnings']) : 0;
        $order_count = isset($earnings_result['order_count']) ? (int)$earnings_result['order_count'] : 0;

        // 2. Calculate total already paid to this shipping company from fund_transfers table
        $this->db->select('SUM(amount) as total_paid');
        $this->db->from('fund_transfers');
        $this->db->where('shipping_company_id', $company_id);
        $this->db->where('status', 'success');

        $paid_result = $this->db->get()->row_array();

        $total_paid = isset($paid_result['total_paid']) ? floatval($paid_result['total_paid']) : 0;

        // 3. Calculate pending amount
        $pending_amount = $total_earnings - $total_paid;
        if ($pending_amount < 0) {
            $pending_amount = 0; // Can't be negative (overpaid scenario)
        }

        return [
            'total_earnings' => round($total_earnings, 2),
            'total_paid' => round($total_paid, 2),
            'pending_amount' => round($pending_amount, 2),
            'order_count' => $order_count
        ];
    }

    /**
     * Get transactions list for shipping company panel
     *
     * @param int $user_id
     * @return void (outputs JSON)
     */
    function get_transactions_list($user_id = '')
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = [];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort'])) {
            $sort = ($_GET['sort'] == 'id') ? "transactions.id" : $_GET['sort'];
        }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) && $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = [
                'transactions.id' => $search,
                'transactions.amount' => $search,
                'transactions.order_id' => $search,
                'transactions.type' => $search,
                'transactions.txn_id' => $search,
                'transactions.message' => $search
            ];
        }

        if (!empty($user_id)) {
            $where['transactions.user_id'] = $user_id;
        }

        // Filter by type (credit/debit)
        if (isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
            $filter_type = $_GET['filter_type'];
            if ($filter_type == 'credit') {
                $where['transactions.type'] = 'credit';
            } elseif ($filter_type == 'debit') {
                $this->db->where_in('transactions.type', ['debit', 'shipping_company_cash_collection']);
            }
        }

        // Count query
        $this->db->select('COUNT(transactions.id) as total');
        $this->db->from('transactions');
        // Status can be '1', 'success', or 'received' - check for valid statuses
        $this->db->group_start();
        $this->db->where('transactions.status', '1');
        $this->db->or_where('transactions.status', 'success');
        $this->db->group_end();

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $this->db->where("DATE(transactions.transaction_date) >= DATE('" . $_GET['start_date'] . "')");
            $this->db->where("DATE(transactions.transaction_date) <= DATE('" . $_GET['end_date'] . "')");
        }

        if (!empty($multipleWhere)) {
            $this->db->group_start();
            $this->db->or_like($multipleWhere);
            $this->db->group_end();
        }
        if (!empty($where)) {
            $this->db->where($where);
        }

        $count_result = $this->db->get()->row_array();
        $total = isset($count_result['total']) ? $count_result['total'] : 0;

        // Data query
        $this->db->select('transactions.id, transactions.order_id, transactions.txn_id, transactions.amount, transactions.type, transactions.message, transactions.transaction_date as date');
        $this->db->from('transactions');
        // Status can be '1', 'success', or 'received' - check for valid statuses
        $this->db->group_start();
        $this->db->where('transactions.status', '1');
        $this->db->or_where('transactions.status', 'success');
        $this->db->group_end();

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $this->db->where("DATE(transactions.transaction_date) >= DATE('" . $_GET['start_date'] . "')");
            $this->db->where("DATE(transactions.transaction_date) <= DATE('" . $_GET['end_date'] . "')");
        }

        if (!empty($multipleWhere)) {
            $this->db->group_start();
            $this->db->or_like($multipleWhere);
            $this->db->group_end();
        }
        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);

        $transactions = $this->db->get()->result_array();

        // Format output
        $rows = [];
        $settings = get_settings('system_settings', true);
        $currency = $settings['currency'];

        foreach ($transactions as $row) {
            $type_label = ucfirst($row['type']);
            if ($row['type'] == 'credit') {
                $type_label = '<span class="badge badge-success">Credit</span>';
            } elseif ($row['type'] == 'debit' || $row['type'] == 'shipping_company_cash_collection') {
                $type_label = '<span class="badge badge-danger">Debit</span>';
            } else {
                $type_label = '<span class="badge badge-secondary">' . ucfirst(str_replace('_', ' ', $row['type'])) . '</span>';
            }

            $rows[] = [
                'id' => $row['id'],
                'order_id' => !empty($row['order_id']) ? $row['order_id'] : '-',
                'txn_id' => !empty($row['txn_id']) ? $row['txn_id'] : '-',
                'amount' => $currency . ' ' . number_format($row['amount'], 2),
                'type' => $type_label,
                'message' => !empty($row['message']) ? $row['message'] : '-',
                'date' => date('d-M-Y h:i A', strtotime($row['date']))
            ];
        }

        $bulkData = [
            'total' => $total,
            'rows' => $rows
        ];

        print_r(json_encode($bulkData));
    }
}
