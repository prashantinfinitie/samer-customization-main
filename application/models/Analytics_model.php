<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Analytics_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['analytics_helper', 'function_helper']);
    }

    /**
     * Get Sales Overview - Time-series data
     *
     * @param string $period 'daily', 'weekly', 'monthly'
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @param int|null $seller_id Seller ID (null for admin/all sellers)
     * @return array Sales overview data
     */
    public function get_sales_overview($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Normalize seller_id - treat empty string as null (must do this FIRST)
        if ($seller_id === '' || $seller_id === null || $seller_id === false) {
            $seller_id = null;
        } else {
            // Ensure seller_id is an integer if provided
            $seller_id = intval($seller_id);
        }

        // When showing all sellers, get seller IDs that match dropdown criteria (group_id = 4 for sellers, status = 1 for approved)
        $seller_id_array = null;
        if ($seller_id === null) {
            // Use a fresh query to get seller IDs - match the dropdown query exactly
            $seller_ids = $this->db->select('u.id')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)  // Sellers use group_id = 4, not 2
                ->where('sd.status', 1)     // Only approved sellers
                ->get()
                ->result_array();

            // Reset query builder after fetching seller IDs
            $this->db->reset_query();

            if (!empty($seller_ids)) {
                $seller_id_array = array_map('intval', array_column($seller_ids, 'id'));
            }

            // If seller_id_array is empty, we'll show all order_items (no filter)
            // This handles edge cases where sellers might not be properly set up
        }

        // Get gross revenue (all orders) - Match home page: only include credited orders
        $this->db->select('SUM(oi.sub_total) as gross_revenue, COUNT(DISTINCT o.id) as total_orders, SUM(oi.quantity) as total_units')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'awaiting']);

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            // Filter by seller IDs that match dropdown criteria
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }
        // If seller_id_array is null or empty, show all order_items (no additional filter)

        $sales_data = $this->db->get()->row_array();

        // Fallback: If showing all sellers with filter but got 0, try without filter
        // This handles cases where seller IDs might not match or seller_id_array is wrong
        if ($seller_id === null && isset($sales_data['gross_revenue']) && floatval($sales_data['gross_revenue']) == 0
            && $seller_id_array !== null && !empty($seller_id_array)) {
            // Test if there's any data at all in this date range
            $this->db->reset_query();
            $test_data = $this->db->select('SUM(oi.sub_total) as gross_revenue')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id', 'left')
                ->where('DATE(o.date_added) >=', $start_date)
                ->where('DATE(o.date_added) <=', $end_date)
                ->where_not_in('oi.active_status', ['cancelled', 'awaiting'])
                ->get()
                ->row_array();

            // If there's data without seller filter, use it (show all sellers' data)
            if (isset($test_data['gross_revenue']) && floatval($test_data['gross_revenue']) > 0) {
                $this->db->reset_query();
                $this->db->select('SUM(oi.sub_total) as gross_revenue, COUNT(DISTINCT o.id) as total_orders, SUM(oi.quantity) as total_units')
                    ->from('order_items oi')
                    ->join('orders o', 'o.id = oi.order_id', 'left')
                    ->where('DATE(o.date_added) >=', $start_date)
                    ->where('DATE(o.date_added) <=', $end_date)
                    ->where('oi.is_credited', 1)
                    ->where_not_in('oi.active_status', ['cancelled', 'awaiting']);
                $sales_data = $this->db->get()->row_array();
                // Clear seller_id_array to prevent filtering in other queries
                $seller_id_array = null;
            }
        }

        // Get net revenue (subtract returns)
        // Note: Returns should NOT filter by is_credited (returns are separate)
        $this->db->select('SUM(oi.sub_total) as returns_amount')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.active_status', 'returned');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            // Filter by seller IDs that match dropdown criteria
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }
        // If seller_id_array is null or empty, show all returns (no additional filter)

        $returns_data = $this->db->get()->row_array();
        $returns_amount = isset($returns_data['returns_amount']) ? floatval($returns_data['returns_amount']) : 0;

        // Get cart additions (approximate from cart table)
        $this->db->select('COUNT(DISTINCT c.user_id) as cart_additions')
            ->from('cart c')
            ->join('product_variants pv', 'pv.id = c.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->where('DATE(c.date_created) >=', $start_date)
            ->where('DATE(c.date_created) <=', $end_date);

        if ($seller_id !== null) {
            $this->db->where('p.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            // Filter by seller IDs that match dropdown criteria
            $this->db->where_in('p.seller_id', $seller_id_array);
        }
        // If seller_id_array is null or empty, show all cart additions (no additional filter)

        $cart_data = $this->db->get()->row_array();
        $cart_additions = isset($cart_data['cart_additions']) ? intval($cart_data['cart_additions']) : 0;

        // Calculate conversion rate
        $total_orders = isset($sales_data['total_orders']) ? intval($sales_data['total_orders']) : 0;
        $conversion_rate = calculate_conversion_rate($total_orders, $cart_additions);

        // Get new vs returning customers
        // Pass null/empty seller_id properly to customer metrics
        $customer_seller_id = ($seller_id !== null) ? $seller_id : null;
        $customer_data = $this->get_customer_metrics($start_date, $end_date, $customer_seller_id, $seller_id_array);

        $final_data = [
            'gross_revenue' => isset($sales_data['gross_revenue']) ? floatval($sales_data['gross_revenue']) : 0,
            'net_revenue' => isset($sales_data['gross_revenue']) ? floatval($sales_data['gross_revenue']) - $returns_amount : 0,
            'total_orders' => $total_orders,
            'total_units' => isset($sales_data['total_units']) ? intval($sales_data['total_units']) : 0,
            'conversion_rate' => round($conversion_rate, 2),
            'cart_additions' => $cart_additions,
            'returns_amount' => $returns_amount,
            'new_customers' => isset($customer_data['new_customers']) ? $customer_data['new_customers'] : 0,
            'returning_customers' => isset($customer_data['returning_customers']) ? $customer_data['returning_customers'] : 0,
        ];

        return $final_data;
    }

    /**
     * Get Sales Time Series Data for Charts
     *
     * @param string $period 'daily', 'weekly', 'monthly'
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @param int|null $seller_id Seller ID (null for admin/all sellers)
     * @return array Time series data with labels, revenue, orders, and units
     */
    public function get_sales_time_series($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Normalize seller_id - treat empty string as null (must do this FIRST)
        if ($seller_id === '' || $seller_id === null || $seller_id === false) {
            $seller_id = null;
        } else {
            // Ensure seller_id is an integer if provided
            $seller_id = intval($seller_id);
        }

        // When showing all sellers, get seller IDs that match dropdown criteria (group_id = 4 for sellers, status = 1 for approved)
        $seller_id_array = null;
        if ($seller_id === null) {
            // Use a fresh query to get seller IDs - match the dropdown query exactly
            $seller_ids = $this->db->select('u.id')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)  // Sellers use group_id = 4, not 2
                ->where('sd.status', 1)     // Only approved sellers
                ->get()
                ->result_array();

            // Reset query builder after fetching seller IDs
            $this->db->reset_query();

            if (!empty($seller_ids)) {
                $seller_id_array = array_map('intval', array_column($seller_ids, 'id'));
            }
            // If seller_id_array is empty, we'll show all order_items (no filter)
            // This handles edge cases where sellers might not be properly set up
        }

        $labels = [];
        $revenue_data = [];
        $orders_data = [];
        $units_data = [];

        // Determine date format and grouping based on period
        switch ($period) {
            case 'daily':
                $date_format = '%Y-%m-%d';
                $group_by = 'DATE(o.date_added)';
                $date_label_format = 'M d';

                // Generate all dates in range
                $current = strtotime($start_date);
                $end = strtotime($end_date);
                while ($current <= $end) {
                    $date_key = date('Y-m-d', $current);
                    $labels[] = date($date_label_format, $current);
                    $revenue_data[$date_key] = 0;
                    $orders_data[$date_key] = 0;
                    $units_data[$date_key] = 0;
                    $current = strtotime('+1 day', $current);
                }
                break;

            case 'weekly':
                $group_by = 'YEARWEEK(o.date_added, 1)';

                // Generate all weeks in range
                $current = strtotime($start_date);
                $end = strtotime($end_date);
                while ($current <= $end) {
                    $week_num = date('W', $current);
                    $year = date('Y', $current);
                    $week_key = $year . $week_num; // Format: YYYYWW
                    $labels[] = 'Week ' . $week_num . ', ' . $year;
                    $revenue_data[$week_key] = 0;
                    $orders_data[$week_key] = 0;
                    $units_data[$week_key] = 0;
                    $current = strtotime('+1 week', $current);
                }
                break;

            case 'monthly':
            default:
                $date_format = '%Y-%m';
                $group_by = 'DATE_FORMAT(o.date_added, "%Y-%m")';
                $date_label_format = '%b %Y';

                // Generate all months in range
                $current = strtotime($start_date);
                $end = strtotime($end_date);
                while ($current <= $end) {
                    $month_key = date('Y-m', $current);
                    $labels[] = date('M Y', $current);
                    $revenue_data[$month_key] = 0;
                    $orders_data[$month_key] = 0;
                    $units_data[$month_key] = 0;
                    $current = strtotime('+1 month', $current);
                }
                break;
        }

        // Get revenue data - Match home page: only include credited orders
        $this->db->select('SUM(oi.sub_total) as revenue, COUNT(DISTINCT o.id) as orders, SUM(oi.quantity) as units, ' . $group_by . ' as period_key')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'awaiting'])
            ->group_by('period_key')
            ->order_by('period_key', 'ASC');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            // Filter by seller IDs that match dropdown criteria
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }
        // If seller_id_array is null or empty, show all order_items (no additional filter)

        $results = $this->db->get()->result_array();

        // Map results to data arrays
        foreach ($results as $row) {
            $key = $row['period_key'];

            if ($period === 'weekly') {
                // YEARWEEK returns a number like 202501, ensure it's a 6-digit string
                $key = (string)$key;
                if (strlen($key) < 6) {
                    $key = str_pad($key, 6, '0', STR_PAD_LEFT);
                }
            } elseif ($period === 'daily') {
                // DATE() returns Y-m-d format
                $key = date('Y-m-d', strtotime($key));
            } else {
                // Monthly - already in Y-m format
                $key = (string)$key;
            }

            if (isset($revenue_data[$key])) {
                $revenue_data[$key] = floatval($row['revenue']);
                $orders_data[$key] = intval($row['orders']);
                $units_data[$key] = intval($row['units']);
            }
        }

        return [
            'labels' => $labels,
            'revenue' => array_values($revenue_data),
            'orders' => array_values($orders_data),
            'units' => array_values($units_data)
        ];
    }

    /**
     * Get Profit Report
     *
     * @param string $period 'daily', 'weekly', 'monthly'
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @return array Profit data
     */
    public function get_profit_report($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        $this->db->select('oi.id, oi.sub_total, oi.quantity, oi.product_variant_id, pv.cost_price')
            ->from('order_items oi')
            ->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting']);

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $order_items = $this->db->get()->result_array();

        $total_profit = 0;
        $total_revenue = 0;
        $item_count = 0;

        foreach ($order_items as $item) {
            $sale_price = floatval($item['sub_total']) / floatval($item['quantity']); // Price per unit
            $cost = !empty($item['cost_price']) ? floatval($item['cost_price']) : (isset($item['product_cost']) ? floatval($item['product_cost']) : 0);

            $profit_per_unit = calculate_profit($sale_price, $cost);
            $profit = $profit_per_unit * floatval($item['quantity']);

            $total_profit += $profit;
            $total_revenue += floatval($item['sub_total']);
            $item_count++;
        }

        $avg_margin = $total_revenue > 0 ? calculate_margin($total_profit, $total_revenue) : 0;

        return [
            'total_profit' => round($total_profit, 2),
            'total_revenue' => round($total_revenue, 2),
            'average_margin' => round($avg_margin, 2),
            'item_count' => $item_count
        ];
    }

    /**
     * Get Product-wise Report
     *
     * @param string $period Period type
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @param int $limit Limit results
     * @param int $offset Offset
     * @return array Product report data
     */
    public function get_product_wise_report($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null, $limit = 50, $offset = 0)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Normalize seller_id
        if ($seller_id === '' || $seller_id === null || $seller_id === false) {
            $seller_id = null;
        } else {
            $seller_id = intval($seller_id);
        }

        // When showing all sellers, get seller IDs that match dropdown criteria (group_id = 4, status = 1)
        $seller_id_array = null;
        if ($seller_id === null) {
            $seller_ids = $this->db->select('u.id')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->where('sd.status', 1)
                ->get()
                ->result_array();

            $this->db->reset_query();

            if (!empty($seller_ids)) {
                $seller_id_array = array_map('intval', array_column($seller_ids, 'id'));
            }
        }

        $this->db->select('oi.product_variant_id, pv.product_id, p.name as product_name, p.name_ar as product_name_ar, p.sku, pv.sku as variant_sku,
            SUM(oi.quantity) as total_sold, SUM(oi.sub_total) as total_revenue,
            AVG(oi.sub_total / oi.quantity) as avg_sale_price,
            pv.cost_price')
            ->from('order_items oi')
            ->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting'])
            ->group_by('oi.product_variant_id, pv.product_id');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }

        $this->db->order_by('total_revenue', 'DESC')
            ->limit($limit, $offset);

        $products = $this->db->get()->result_array();

        // Apply locale transformation
        $locale = get_current_locale();
        $result = [];
        foreach ($products as $product) {
            $cost = !empty($product['cost_price']) ? floatval($product['cost_price']) : (isset($product['product_cost']) ? floatval($product['product_cost']) : 0);
            $avg_sale_price = floatval($product['avg_sale_price']);
            $profit_per_unit = calculate_profit($avg_sale_price, $cost);
            $total_profit = $profit_per_unit * floatval($product['total_sold']);

            // Calculate average weekly sales
            $avg_weekly_sales = calculate_avg_weekly_sales($product['product_variant_id'], 4);

            // Apply locale transformation for product name
            $has_arabic = !empty($product['product_name_ar']);
            $product_name = $product['product_name']; // Default to English
            if ($locale === 'ar' && $has_arabic) {
                $product_name = $product['product_name_ar'];
            }

            // Conditionally wrap in notranslate if Arabic locale and Arabic field exists
            $use_notranslate = ($locale === 'ar' && $has_arabic);
            $product_name_wrapper = $use_notranslate ? '<span class="notranslate">' . $product_name . '</span>' : $product_name;

            $result[] = [
                'product_id' => $product['product_id'],
                'product_variant_id' => $product['product_variant_id'],
                'product_name' => $product_name_wrapper,
                'sku' => !empty($product['variant_sku']) ? $product['variant_sku'] : $product['sku'],
                'total_sold' => intval($product['total_sold']),
                'total_revenue' => round(floatval($product['total_revenue']), 2),
                'total_profit' => round($total_profit, 2),
                'average_weekly_sales' => round($avg_weekly_sales, 2),
                'cost_price' => $cost,
                'avg_sale_price' => round($avg_sale_price, 2),
                'margin' => round(calculate_margin($profit_per_unit, $avg_sale_price), 2)
            ];
        }

        return $result;
    }

    /**
     * Get Category-wise Report
     *
     * @param string $period Period type
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @return array Category report data
     */
    public function get_category_wise_report($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Normalize seller_id
        if ($seller_id === '' || $seller_id === null || $seller_id === false) {
            $seller_id = null;
        } else {
            $seller_id = intval($seller_id);
        }

        // When showing all sellers, get seller IDs that match dropdown criteria (group_id = 4, status = 1)
        $seller_id_array = null;
        if ($seller_id === null) {
            $seller_ids = $this->db->select('u.id')
                ->from('users u')
                ->join('users_groups ug', 'ug.user_id = u.id', 'left')
                ->join('seller_data sd', 'sd.user_id = u.id', 'left')
                ->where('ug.group_id', 4)
                ->where('sd.status', 1)
                ->get()
                ->result_array();

            $this->db->reset_query();

            if (!empty($seller_ids)) {
                $seller_id_array = array_map('intval', array_column($seller_ids, 'id'));
            }
        }

        $this->db->select('c.id as category_id, c.name as category_name, c.name_ar as category_name_ar,
            SUM(oi.quantity) as total_units, SUM(oi.sub_total) as total_revenue,
            COUNT(DISTINCT pv.product_id) as product_count')
            ->from('order_items oi')
            ->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting'])
            ->group_by('c.id')
            ->order_by('total_revenue', 'DESC');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }

        $categories = $this->db->get()->result_array();

        // Apply locale transformation
        $locale = get_current_locale();
        foreach ($categories as &$category) {
            $has_arabic = !empty($category['category_name_ar']);
            $category_name = $category['category_name']; // Default to English
            if ($locale === 'ar' && $has_arabic) {
                $category_name = $category['category_name_ar'];
            }

            // Conditionally wrap in notranslate if Arabic locale and Arabic field exists
            $use_notranslate = ($locale === 'ar' && $has_arabic);
            $category['category_name'] = $use_notranslate ? '<span class="notranslate">' . $category_name . '</span>' : $category_name;

            // Remove Arabic field from response
            unset($category['category_name_ar']);
        }

        return $categories;
    }

    /**
     * Get Seller-wise Report (Admin only)
     *
     * @param string $period Period type
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Seller comparison data
     */
    public function get_seller_wise_report($period = 'monthly', $start_date = null, $end_date = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Get seller IDs that match dropdown criteria (group_id = 4, status = 1)
        $seller_ids = $this->db->select('u.id')
            ->from('users u')
            ->join('users_groups ug', 'ug.user_id = u.id', 'left')
            ->join('seller_data sd', 'sd.user_id = u.id', 'left')
            ->where('ug.group_id', 4)  // Sellers use group_id = 4
            ->where('sd.status', 1)     // Only approved sellers
            ->get()
            ->result_array();

        $seller_id_array = !empty($seller_ids) ? array_map('intval', array_column($seller_ids, 'id')) : [];
        $this->db->reset_query();

        $this->db->select('oi.seller_id, u.username as seller_name, sd.store_name,
            SUM(oi.sub_total) as total_revenue, COUNT(DISTINCT o.id) as total_orders,
            SUM(oi.quantity) as total_units')
            ->from('order_items oi')
            ->join('users u', 'u.id = oi.seller_id', 'left')
            ->join('seller_data sd', 'sd.user_id = oi.seller_id', 'left')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting']);

        // Only show approved sellers
        if (!empty($seller_id_array)) {
            $this->db->where_in('oi.seller_id', $seller_id_array);
        } else {
            // If no approved sellers, return empty result
            $this->db->where('1', '0');
        }

        $this->db->group_by('oi.seller_id')
            ->order_by('total_revenue', 'DESC');

        $sellers = $this->db->get()->result_array();

        // Calculate profit for each seller
        $result = [];
        foreach ($sellers as $seller) {
            $profit_data = $this->get_profit_report($period, $start_date, $end_date, $seller['seller_id']);

            $result[] = [
                'seller_id' => $seller['seller_id'],
                'seller_name' => $seller['seller_name'],
                'store_name' => $seller['store_name'],
                'total_revenue' => round(floatval($seller['total_revenue']), 2),
                'total_profit' => $profit_data['total_profit'],
                'total_orders' => intval($seller['total_orders']),
                'total_units' => intval($seller['total_units']),
                'average_margin' => $profit_data['average_margin']
            ];
        }

        return $result;
    }

    /**
     * Get Inventory Health Report
     *
     * @param int|null $seller_id Seller ID
     * @return array Inventory metrics
     */
    public function get_inventory_health($seller_id = null)
    {
        $this->db->select('pv.id, pv.product_id, pv.stock, pv.cost_price,
            p.name as product_name, p.name_ar as product_name_ar, pv.price, pv.special_price, p.seller_id')
            ->from('product_variants pv')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->where('p.status', 1); // Active products only

        if ($seller_id !== null) {
            $this->db->where('p.seller_id', $seller_id);
        }

        $products = $this->db->get()->result_array();

        // Apply locale transformation
        $locale = get_current_locale();
        foreach ($products as &$product) {
            $has_arabic = !empty($product['product_name_ar']);
            $product_name = $product['product_name']; // Default to English
            if ($locale === 'ar' && $has_arabic) {
                $product_name = $product['product_name_ar'];
            }

            // Conditionally wrap in notranslate if Arabic locale and Arabic field exists
            $use_notranslate = ($locale === 'ar' && $has_arabic);
            $product['product_name'] = $use_notranslate ? '<span class="notranslate">' . $product_name . '</span>' : $product_name;

            // Remove Arabic field from response
            unset($product['product_name_ar']);
        }

        $total_products = 0;
        $total_cost_value = 0;
        $total_expected_profit = 0;
        $low_stock_count = 0;
        $out_of_stock_count = 0;

        foreach ($products as $product) {
            $stock = intval($product['stock']);
            $cost = !empty($product['cost_price']) ? floatval($product['cost_price']) : (isset($product['product_cost']) ? floatval($product['product_cost']) : 0);
            $sale_price = !empty($product['special_price']) ? floatval($product['special_price']) : floatval($product['price']);

            $total_products++;
            $total_cost_value += $cost * $stock;
            $total_expected_profit += calculate_profit($sale_price, $cost) * $stock;

            if ($stock == 0) {
                $out_of_stock_count++;
            } elseif ($stock <= 10) { // Low stock threshold
                $low_stock_count++;
            }
        }

        // Calculate inventory turnover (simplified - using last 30 days COGS)
        $cogs = $this->calculate_cogs(30, $seller_id);
        $avg_inventory = $total_cost_value / 2; // Simplified average
        $turnover_rate = calculate_inventory_turnover($cogs, $avg_inventory);

        return [
            'total_products' => $total_products,
            'total_cost_value' => round($total_cost_value, 2),
            'total_expected_profit' => round($total_expected_profit, 2),
            'low_stock_count' => $low_stock_count,
            'out_of_stock_count' => $out_of_stock_count,
            'inventory_turnover' => round($turnover_rate, 2),
            'average_inventory_value' => round($avg_inventory, 2)
        ];
    }

    /**
     * Get Purchase Suggestions
     *
     * @param int|null $seller_id Seller ID
     * @param int $weeks_ahead Weeks to project ahead
     * @return array Products that need restocking
     */
    public function get_purchase_suggestions($seller_id = null, $weeks_ahead = 4)
    {
        $this->db->select('pv.id as variant_id, p.id as product_id, p.name as product_name, p.name_ar as product_name_ar,
            pv.stock, pv.cost_price,
            pv.price, pv.special_price')
            ->from('product_variants pv')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->where('p.status', 1);

        if ($seller_id !== null) {
            $this->db->where('p.seller_id', $seller_id);
        }

        $products = $this->db->get()->result_array();

        // Apply locale transformation
        $locale = get_current_locale();
        $suggestions = [];
        foreach ($products as $product) {
            $current_stock = intval($product['stock']);
            $avg_weekly_sales = calculate_avg_weekly_sales($product['variant_id'], 4);
            $expected_sales = $avg_weekly_sales * $weeks_ahead;

            if ($current_stock < $expected_sales && $avg_weekly_sales > 0) {
                $suggested_quantity = ceil($expected_sales - $current_stock);
                $cost = !empty($product['cost_price']) ? floatval($product['cost_price']) : (isset($product['product_cost']) ? floatval($product['product_cost']) : 0);

                // Apply locale transformation for product name
                $has_arabic = !empty($product['product_name_ar']);
                $product_name = $product['product_name']; // Default to English
                if ($locale === 'ar' && $has_arabic) {
                    $product_name = $product['product_name_ar'];
                }

                // Conditionally wrap in notranslate if Arabic locale and Arabic field exists
                $use_notranslate = ($locale === 'ar' && $has_arabic);
                $product_name_wrapper = $use_notranslate ? '<span class="notranslate">' . $product_name . '</span>' : $product_name;

                $suggestions[] = [
                    'product_id' => $product['product_id'],
                    'variant_id' => $product['variant_id'],
                    'product_name' => $product_name_wrapper,
                    'current_stock' => $current_stock,
                    'average_weekly_sales' => round($avg_weekly_sales, 2),
                    'expected_sales_weeks' => round($expected_sales, 2),
                    'suggested_quantity' => $suggested_quantity,
                    'estimated_cost' => round($cost * $suggested_quantity, 2),
                    'urgency' => $current_stock < ($avg_weekly_sales * 2) ? 'high' : 'medium'
                ];
            }
        }

        // Sort by urgency and expected sales
        usort($suggestions, function($a, $b) {
            if ($a['urgency'] != $b['urgency']) {
                return $a['urgency'] == 'high' ? -1 : 1;
            }
            return $b['expected_sales_weeks'] <=> $a['expected_sales_weeks'];
        });

        return $suggestions;
    }

    /**
     * Get Returns Dashboard
     *
     * @param string $period Period type
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @return array Returns analytics
     */
    public function get_returns_dashboard($period = 'monthly', $start_date = null, $end_date = null, $seller_id = null)
    {
        $date_range = get_analytics_date_range($period, $start_date, $end_date);
        $start_date = $date_range['start'];
        $end_date = $date_range['end'];

        // Get total returns
        $this->db->select('COUNT(rr.id) as returns_count, SUM(oi.sub_total) as returns_amount')
            ->from('return_requests rr')
            ->join('order_items oi', 'oi.id = rr.order_item_id', 'left')
            ->join('orders o', 'o.id = rr.order_id', 'left')
            ->where('DATE(rr.date_created) >=', $start_date)
            ->where('DATE(rr.date_created) <=', $end_date)
            ->where('rr.status', 'approved');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $returns_summary = $this->db->get()->row_array();

        // Get returns by reason
        $this->db->select('rr.reason, COUNT(rr.id) as count, SUM(oi.sub_total) as amount')
            ->from('return_requests rr')
            ->join('order_items oi', 'oi.id = rr.order_item_id', 'left')
            ->join('orders o', 'o.id = rr.order_id', 'left')
            ->where('DATE(rr.date_created) >=', $start_date)
            ->where('DATE(rr.date_created) <=', $end_date)
            ->where('rr.status', 'approved')
            ->group_by('rr.reason');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $returns_by_reason = $this->db->get()->result_array();

        // Get returns by product
        $this->db->select('pv.product_id, oi.product_name, p.name as current_product_name, p.name_ar as current_product_name_ar, COUNT(rr.id) as returns_count, SUM(oi.sub_total) as returns_amount')
            ->from('return_requests rr')
            ->join('order_items oi', 'oi.id = rr.order_item_id', 'left')
            ->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->join('orders o', 'o.id = rr.order_id', 'left')
            ->where('DATE(rr.date_created) >=', $start_date)
            ->where('DATE(rr.date_created) <=', $end_date)
            ->where('rr.status', 'approved')
            ->group_by('pv.product_id')
            ->order_by('returns_count', 'DESC')
            ->limit(20);

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $returns_by_product = $this->db->get()->result_array();

        // Apply locale transformation
        $locale = get_current_locale();
        foreach ($returns_by_product as &$return_product) {
            $has_arabic = !empty($return_product['current_product_name_ar']);
            $product_name = $return_product['product_name']; // Default to snapshot
            if ($locale === 'ar' && $has_arabic) {
                $product_name = $return_product['current_product_name_ar'];
            } elseif ($locale === 'ar' && isset($return_product['current_product_name']) && !empty($return_product['current_product_name'])) {
                $product_name = $return_product['current_product_name'];
            }

            // Conditionally wrap in notranslate if Arabic locale and Arabic field exists
            $use_notranslate = ($locale === 'ar' && $has_arabic);
            $return_product['product_name'] = $use_notranslate ? '<span class="notranslate">' . $product_name . '</span>' : $product_name;

            // Remove fields not needed in response
            unset($return_product['current_product_name']);
            unset($return_product['current_product_name_ar']);
        }

        // Get total orders for return rate calculation
        $this->db->select('COUNT(DISTINCT o.id) as total_orders')
            ->from('orders o')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where_not_in('oi.active_status', ['cancelled', 'awaiting']);

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $total_orders_data = $this->db->get()->row_array();
        $total_orders = isset($total_orders_data['total_orders']) ? intval($total_orders_data['total_orders']) : 0;
        $returns_count = isset($returns_summary['returns_count']) ? intval($returns_summary['returns_count']) : 0;
        $return_rate = calculate_return_rate($returns_count, $total_orders);

        return [
            'total_returns' => $returns_count,
            'returns_amount' => isset($returns_summary['returns_amount']) ? round(floatval($returns_summary['returns_amount']), 2) : 0,
            'return_rate' => round($return_rate, 2),
            'total_orders' => $total_orders,
            'returns_by_reason' => $returns_by_reason,
            'returns_by_product' => $returns_by_product
        ];
    }

    /**
     * Get Customer Metrics (New vs Returning)
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @param array|null $seller_id_array Array of seller IDs (for "All Sellers" filtering)
     * @return array Customer metrics
     */
    public function get_customer_metrics($start_date, $end_date, $seller_id = null, $seller_id_array = null)
    {
        // Normalize seller_id
        if ($seller_id === '' || $seller_id === null || $seller_id === false) {
            $seller_id = null;
        } else {
            $seller_id = intval($seller_id);
        }

        // Get all customers who ordered in this period - Match home page: only include credited orders
        $this->db->select('o.user_id, MIN(o.date_added) as first_order_date')
            ->from('orders o')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where('oi.is_credited', 1)
            ->where_not_in('oi.active_status', ['cancelled', 'awaiting'])
            ->group_by('o.user_id');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        } elseif ($seller_id_array !== null && !empty($seller_id_array)) {
            // Filter by seller IDs that match dropdown criteria
            $this->db->where_in('oi.seller_id', $seller_id_array);
        }
        // If seller_id_array is null or empty, show all customers (no additional filter)

        $period_customers = $this->db->get()->result_array();

        $new_customers = 0;
        $returning_customers = 0;

        foreach ($period_customers as $customer) {
            // Check if this customer had orders before this period
            $this->db->select('COUNT(*) as order_count')
                ->from('orders')
                ->where('user_id', $customer['user_id'])
                ->where('DATE(date_added) <', $start_date);

            $previous_orders = $this->db->get()->row_array();

            if (isset($previous_orders['order_count']) && intval($previous_orders['order_count']) > 0) {
                $returning_customers++;
            } else {
                $new_customers++;
            }
        }

        return [
            'new_customers' => $new_customers,
            'returning_customers' => $returning_customers,
            'total_customers' => $new_customers + $returning_customers
        ];
    }

    /**
     * Get Repeat Purchase Metrics
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $seller_id Seller ID
     * @return array Repeat purchase metrics
     */
    public function get_repeat_purchase_metrics($start_date, $end_date, $seller_id = null)
    {
        $customer_metrics = $this->get_customer_metrics($start_date, $end_date, $seller_id);

        // Calculate average reorder interval
        $this->db->select('o.user_id, o.date_added')
            ->from('orders o')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where('DATE(o.date_added) <=', $end_date)
            ->where_not_in('oi.active_status', ['cancelled', 'awaiting'])
            ->order_by('o.user_id', 'ASC')
            ->order_by('o.date_added', 'ASC');

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $orders = $this->db->get()->result_array();

        $intervals = [];
        $current_user = null;
        $last_order_date = null;

        foreach ($orders as $order) {
            if ($current_user != $order['user_id']) {
                $current_user = $order['user_id'];
                $last_order_date = $order['date_added'];
            } else {
                $date1 = strtotime($last_order_date);
                $date2 = strtotime($order['date_added']);
                $days = ($date2 - $date1) / (60 * 60 * 24);
                if ($days > 0) {
                    $intervals[] = $days;
                }
                $last_order_date = $order['date_added'];
            }
        }

        $avg_reorder_interval = count($intervals) > 0 ? round(array_sum($intervals) / count($intervals)) : null;

        return [
            'new_customers' => $customer_metrics['new_customers'],
            'returning_customers' => $customer_metrics['returning_customers'],
            'average_reorder_interval_days' => $avg_reorder_interval,
            'retention_rate' => $customer_metrics['total_customers'] > 0
                ? round(($customer_metrics['returning_customers'] / $customer_metrics['total_customers']) * 100, 2)
                : 0
        ];
    }

    /**
     * Calculate Cost of Goods Sold (COGS)
     *
     * @param int $days Number of days to look back
     * @param int|null $seller_id Seller ID
     * @return float COGS amount
     */
    private function calculate_cogs($days, $seller_id = null)
    {
        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $this->db->select('oi.quantity, pv.cost_price')
            ->from('order_items oi')
            ->join('product_variants pv', 'pv.id = oi.product_variant_id', 'left')
            ->join('products p', 'p.id = pv.product_id', 'left')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('DATE(o.date_added) >=', $start_date)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting']);

        if ($seller_id !== null) {
            $this->db->where('oi.seller_id', $seller_id);
        }

        $items = $this->db->get()->result_array();

        $cogs = 0;
        foreach ($items as $item) {
            $cost = !empty($item['cost_price']) ? floatval($item['cost_price']) : (isset($item['product_cost']) ? floatval($item['product_cost']) : 0);
            $cogs += $cost * floatval($item['quantity']);
        }

        return $cogs;
    }
}

