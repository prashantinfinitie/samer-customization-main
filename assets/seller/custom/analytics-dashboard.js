/**
 * Analytics Dashboard JavaScript - Seller Panel
 * Handles chart rendering and data loading for analytics dashboard
 */

(function() {
    'use strict';

    let revenueChart, ordersChart;
    let currency = '₹'; // Default currency, will be set from data attribute
    let salesOverviewUrl = '';
    let profitReportUrl = '';
    let timeSeriesUrl = '';

    // Initialize on document ready
    $(document).ready(function() {
        // Get URLs and currency from data attributes
        const container = $('.content-wrapper').first();
        if (container.length) {
            currency = container.data('currency') || currency;
            salesOverviewUrl = container.data('sales-overview-url') || '';
            profitReportUrl = container.data('profit-report-url') || '';
            timeSeriesUrl = container.data('time-series-url') || '';
        }

        // Fallback URLs if data attributes not set
        if (!salesOverviewUrl && window.base_url) {
            salesOverviewUrl = window.base_url + 'seller/analytics/get_sales_overview';
        }
        if (!profitReportUrl && window.base_url) {
            profitReportUrl = window.base_url + 'seller/analytics/get_profit_report';
        }
        if (!timeSeriesUrl && window.base_url) {
            timeSeriesUrl = window.base_url + 'seller/analytics/get_sales_time_series';
        }

        // Initialize
        loadAnalytics();

        // Period filter change handler
        $('#period_filter').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#custom_date_range').show();
                $('#custom_date_range_end').show();
            } else {
                $('#custom_date_range').hide();
                $('#custom_date_range_end').hide();
            }
            loadAnalytics();
        });
    });

    /**
     * Load analytics overview and chart data
     */
    function loadAnalytics() {
        const period = $('#period_filter').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        // Load sales overview
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
                    $('#metric_gross_revenue').text(currency + ' ' + parseFloat(data.gross_revenue).toFixed(2));
                    $('#metric_net_revenue').text(currency + ' ' + parseFloat(data.net_revenue).toFixed(2));
                    $('#metric_total_orders').text(data.total_orders);
                    $('#metric_total_units').text(data.total_units);
                    $('#metric_conversion_rate').text(parseFloat(data.conversion_rate).toFixed(2) + '%');
                    $('#metric_new_customers').text(data.new_customers);
                    $('#metric_returning_customers').text(data.returning_customers);
                } else {
                    console.error('Sales overview error:', response.message || 'Unknown error');
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

        // Load profit report
        $.ajax({
            url: profitReportUrl,
            type: 'GET',
            data: {
                period: period,
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (!response.error && response.data) {
                    $('#metric_total_profit').text(currency + ' ' + parseFloat(response.data.total_profit).toFixed(2));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading profit report:', error);
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
                } else {
                    console.error('Time series error:', response.message || 'Unknown error');
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

        // Orders Trend Chart
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

    // Make functions available globally
    window.loadAnalytics = loadAnalytics;

})();

