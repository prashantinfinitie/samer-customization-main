/**
 * Analytics Sales Revenue Page JavaScript - Seller Panel
 * Handles chart rendering and data loading for sales revenue analytics
 */

(function() {
    'use strict';

    let revenueChart, ordersChart;
    let currency = '₹'; // Default currency, will be set from data attribute
    let salesOverviewUrl = '';
    let timeSeriesUrl = '';

    // Initialize on document ready
    $(document).ready(function() {
        // Get URLs and currency from data attributes
        const container = $('.content-wrapper').first();
        if (container.length) {
            currency = container.data('currency') || currency;
            salesOverviewUrl = container.data('sales-overview-url') || '';
            timeSeriesUrl = container.data('time-series-url') || '';
        }

        // Fallback URLs if data attributes not set
        if (!salesOverviewUrl && window.base_url) {
            salesOverviewUrl = window.base_url + 'seller/analytics/get_sales_overview';
        }
        if (!timeSeriesUrl && window.base_url) {
            timeSeriesUrl = window.base_url + 'seller/analytics/get_sales_time_series';
        }

        // Initialize tables
        initTopProductsTable();

        // Initialize
        loadSalesRevenue();

        // Period filter change handler
        $('#period_filter').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#custom_date_range').show();
                $('#custom_date_range_end').show();
            } else {
                $('#custom_date_range').hide();
                $('#custom_date_range_end').hide();
            }
            loadSalesRevenue();
        });
    });

    /**
     * Load sales revenue overview and chart data
     */
    function loadSalesRevenue() {
        const period = $('#period_filter').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        // Load overview data
        $.ajax({
            url: salesOverviewUrl,
            type: 'GET',
            data: {
                period: period,
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (!response.error && response.data) {
                    const data = response.data;
                    $('#gross_revenue').text(currency + ' ' + parseFloat(data.gross_revenue).toFixed(2));
                    $('#net_revenue').text(currency + ' ' + parseFloat(data.net_revenue).toFixed(2));
                    $('#total_orders').text(data.total_orders);
                    $('#total_units').text(data.total_units);
                    $('#conversion_rate').text(parseFloat(data.conversion_rate).toFixed(2) + '%');
                    $('#cart_additions').text(data.cart_additions);
                    $('#new_customers').text(data.new_customers);
                    $('#returning_customers').text(data.returning_customers);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading sales overview:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText,
                    url: salesOverviewUrl
                });
            }
        });

        // Load time series data for charts
        $.ajax({
            url: timeSeriesUrl,
            type: 'GET',
            data: {
                period: period,
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (!response.error && response.data) {
                    const chartData = response.data;
                    updateCharts(chartData);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading time series data:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText,
                    url: timeSeriesUrl
                });
            }
        });

        // Refresh tables after a short delay to ensure data is loaded
        setTimeout(function() {
            refreshTables();
        }, 500);
    }

    /**
     * Update charts with new data
     * @param {Object} data - Chart data with labels, revenue, orders, and units
     */
    function updateCharts(data) {
        // Destroy existing charts if they exist
        if (revenueChart) {
            revenueChart.destroy();
        }
        if (ordersChart) {
            ordersChart.destroy();
        }

        // Revenue Trend Chart
        const revenueCanvas = document.getElementById('revenueChart');
        if (!revenueCanvas) {
            console.error('Revenue chart canvas not found');
            return;
        }

        const revenueCtx = revenueCanvas.getContext('2d');
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Gross Revenue',
                    data: data.revenue,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return currency + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ' + currency + parseFloat(context.parsed.y).toFixed(2);
                            }
                        }
                    }
                }
            }
        });

        // Orders & Units Chart
        const ordersCanvas = document.getElementById('ordersChart');
        if (!ordersCanvas) {
            console.error('Orders chart canvas not found');
            return;
        }

        const ordersCtx = ordersCanvas.getContext('2d');
        ordersChart = new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Orders',
                    data: data.orders,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: 'Units',
                    data: data.units,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }

    /**
     * Query parameters function for Top Products Table
     */
    function topProductsTableParams(params) {
        const period = $('#period_filter').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        return {
            limit: params.limit || 20,
            offset: params.offset || 0,
            period: period,
            start_date: startDate,
            end_date: endDate
        };
    }

    /**
     * Currency formatter for Bootstrap Table
     */
    function currencyFormatter(value) {
        if (value === null || value === undefined || value === '') {
            return currency + ' 0.00';
        }
        return currency + ' ' + parseFloat(value).toFixed(2);
    }

    /**
     * Initialize Top Products Table
     */
    function initTopProductsTable() {
        if ($('#top_products_table').length) {
            $('#top_products_table').bootstrapTable({
                onRefresh: function() {
                    // Table will automatically reload with current filter params
                }
            });
        }
    }

    /**
     * Refresh tables when filters change
     */
    function refreshTables() {
        if ($('#top_products_table').length && $('#top_products_table').bootstrapTable) {
            $('#top_products_table').bootstrapTable('refresh');
        }
    }

    // Make functions available globally
    window.loadSalesRevenue = loadSalesRevenue;
    window.topProductsTableParams = topProductsTableParams;
    window.currencyFormatter = currencyFormatter;

})();

