<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Analytics Helper Functions
 *
 * Helper functions for calculating analytics metrics
 */

/**
 * Calculate profit from sale price and cost
 *
 * @param float $sale_price The selling price
 * @param float $cost The cost price
 * @return float Profit amount
 */
if (!function_exists('calculate_profit')) {
    function calculate_profit($sale_price, $cost)
    {
        $sale_price = floatval($sale_price);
        $cost = floatval($cost);
        return max(0, $sale_price - $cost);
    }
}

/**
 * Calculate profit margin percentage
 *
 * @param float $profit The profit amount
 * @param float $sale_price The selling price
 * @return float Margin percentage (0-100)
 */
if (!function_exists('calculate_margin')) {
    function calculate_margin($profit, $sale_price)
    {
        $profit = floatval($profit);
        $sale_price = floatval($sale_price);

        if ($sale_price <= 0) {
            return 0;
        }

        return ($profit / $sale_price) * 100;
    }
}

/**
 * Calculate conversion rate
 *
 * @param int $orders Number of completed orders
 * @param int $cart_adds Number of cart additions
 * @return float Conversion rate percentage (0-100)
 */
if (!function_exists('calculate_conversion_rate')) {
    function calculate_conversion_rate($orders, $cart_adds)
    {
        $orders = intval($orders);
        $cart_adds = intval($cart_adds);

        if ($cart_adds <= 0) {
            return 0;
        }

        return ($orders / $cart_adds) * 100;
    }
}

/**
 * Calculate inventory turnover rate
 *
 * @param float $cogs Cost of goods sold
 * @param float $avg_inventory Average inventory value
 * @return float Turnover rate
 */
if (!function_exists('calculate_inventory_turnover')) {
    function calculate_inventory_turnover($cogs, $avg_inventory)
    {
        $cogs = floatval($cogs);
        $avg_inventory = floatval($avg_inventory);

        if ($avg_inventory <= 0) {
            return 0;
        }

        return $cogs / $avg_inventory;
    }
}

/**
 * Calculate average weekly sales for a product
 *
 * @param int $product_id Product ID
 * @param int $weeks Number of weeks to calculate average
 * @return float Average weekly sales quantity
 */
if (!function_exists('calculate_avg_weekly_sales')) {
    function calculate_avg_weekly_sales($product_id, $weeks = 4)
    {
        $CI = &get_instance();
        $CI->load->database();

        $weeks = max(1, intval($weeks));
        $start_date = date('Y-m-d', strtotime("-{$weeks} weeks"));

        $result = $CI->db->select('SUM(oi.quantity) as total_quantity')
            ->from('order_items oi')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->where('oi.product_variant_id', $product_id)
            ->where('DATE(o.date_added) >=', $start_date)
            ->where_not_in('oi.active_status', ['cancelled', 'returned', 'awaiting'])
            ->get()
            ->row_array();

        $total_quantity = isset($result['total_quantity']) ? floatval($result['total_quantity']) : 0;

        return $total_quantity / $weeks;
    }
}

/**
 * Calculate return rate percentage
 *
 * @param int $returns_count Number of returns
 * @param int $total_orders Total number of orders
 * @return float Return rate percentage (0-100)
 */
if (!function_exists('calculate_return_rate')) {
    function calculate_return_rate($returns_count, $total_orders)
    {
        $returns_count = intval($returns_count);
        $total_orders = intval($total_orders);

        if ($total_orders <= 0) {
            return 0;
        }

        return ($returns_count / $total_orders) * 100;
    }
}

/**
 * Get date range for period type
 *
 * @param string $period 'daily', 'weekly', 'monthly', or 'custom'
 * @param string $start_date Custom start date (for custom period)
 * @param string $end_date Custom end date (for custom period)
 * @return array Array with 'start' and 'end' dates
 */
if (!function_exists('get_analytics_date_range')) {
    function get_analytics_date_range($period = 'monthly', $start_date = null, $end_date = null)
    {
        $today = date('Y-m-d');

        switch ($period) {
            case 'daily':
                return [
                    'start' => $today,
                    'end' => $today
                ];

            case 'weekly':
                $start = date('Y-m-d', strtotime('monday this week'));
                $end = date('Y-m-d', strtotime('sunday this week'));
                return [
                    'start' => $start,
                    'end' => $end
                ];

            case 'monthly':
                $start = date('Y-m-01');
                $end = date('Y-m-t');
                return [
                    'start' => $start,
                    'end' => $end
                ];

            case 'custom':
                return [
                    'start' => $start_date ? date('Y-m-d', strtotime($start_date)) : $today,
                    'end' => $end_date ? date('Y-m-d', strtotime($end_date)) : $today
                ];

            default:
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-t')
                ];
        }
    }
}

/**
 * Format currency for display
 *
 * @param float $amount Amount to format
 * @param string $currency Currency code (default from settings)
 * @return string Formatted currency string
 */
if (!function_exists('format_analytics_currency')) {
    function format_analytics_currency($amount, $currency = null)
    {
        $CI = &get_instance();
        $CI->load->database();

        if ($currency === null) {
            $settings = get_settings('system_settings', true);
            $currency = isset($settings['currency']) ? $settings['currency'] : '$';
        }

        $amount = floatval($amount);
        return $currency . ' ' . number_format($amount, 2);
    }
}

/**
 * Calculate reorder interval in days
 *
 * @param int $user_id User ID
 * @return int|null Average days between orders, or null if less than 2 orders
 */
if (!function_exists('calculate_reorder_interval')) {
    function calculate_reorder_interval($user_id)
    {
        $CI = &get_instance();
        $CI->load->database();

        $orders = $CI->db->select('DATE(date_added) as order_date')
            ->from('orders')
            ->where('user_id', $user_id)
            ->where_not_in('active_status', ['cancelled', 'awaiting'])
            ->order_by('date_added', 'ASC')
            ->get()
            ->result_array();

        if (count($orders) < 2) {
            return null;
        }

        $intervals = [];
        for ($i = 1; $i < count($orders); $i++) {
            $date1 = strtotime($orders[$i - 1]['order_date']);
            $date2 = strtotime($orders[$i]['order_date']);
            $intervals[] = ($date2 - $date1) / (60 * 60 * 24); // Convert to days
        }

        return count($intervals) > 0 ? round(array_sum($intervals) / count($intervals)) : null;
    }
}

