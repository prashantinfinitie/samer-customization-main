<div class="content-wrapper"
    data-currency="<?= $currency ?>"
    data-base-url="<?= base_url() ?>"
    data-sales-overview-url="<?= base_url('seller/analytics/get_sales_overview') ?>"
    data-profit-report-url="<?= base_url('seller/analytics/get_profit_report') ?>"
    data-time-series-url="<?= base_url('seller/analytics/get_sales_time_series') ?>">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Analytics Dashboard</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Period Filter -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Period</label>
                                    <select id="period_filter" class="form-control">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Monthly</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3" id="custom_date_range" style="display: none;">
                                    <label>Start Date</label>
                                    <input type="date" id="start_date" class="form-control">
                                </div>
                                <div class="form-group col-md-3" id="custom_date_range_end" style="display: none;">
                                    <label>End Date</label>
                                    <input type="date" id="end_date" class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadAnalytics()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="metric_gross_revenue"><?= $currency ?> 0.00</h3>
                            <p>Gross Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="metric_net_revenue"><?= $currency ?> 0.00</h3>
                            <p>Net Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="metric_total_profit"><?= $currency ?> 0.00</h3>
                            <p>Total Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="metric_total_orders">0</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-box"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Units</span>
                            <span class="info-box-number" id="metric_total_units">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Conversion Rate</span>
                            <span class="info-box-number" id="metric_conversion_rate">0%</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">New Customers</span>
                            <span class="info-box-number" id="metric_new_customers">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-redo"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Returning Customers</span>
                            <span class="info-box-number" id="metric_returning_customers">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Orders Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="ordersChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Reports</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="<?= base_url('seller/analytics/sales_revenue') ?>" class="btn btn-info btn-block">
                                        <i class="fas fa-chart-bar"></i> Sales & Revenue
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('seller/analytics/profitability') ?>" class="btn btn-success btn-block">
                                        <i class="fas fa-coins"></i> Profitability
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('seller/analytics/products') ?>" class="btn btn-warning btn-block">
                                        <i class="fas fa-box"></i> Products
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= base_url('seller/analytics/inventory') ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-warehouse"></i> Inventory
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <a href="<?= base_url('seller/analytics/returns') ?>" class="btn btn-danger btn-block">
                                        <i class="fas fa-undo"></i> Returns
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/admin/js/Chart.min.js') ?>"></script>
<script src="<?= base_url('assets/seller/custom/analytics-dashboard.js') ?>"></script>
