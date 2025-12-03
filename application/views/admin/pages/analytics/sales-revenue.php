<div class="content-wrapper" data-currency="<?= $currency ?>" data-base-url="<?= base_url() ?>">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Sales & Revenue Analytics</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Sales & Revenue</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
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
                                    <label>Seller (Optional)</label>
                                    <div class="input-group">
                                        <select id="seller_filter" class="form-control">
                                            <option value="">All Sellers</option>
                                            <?php foreach ($sellers as $seller) { ?>
                                                <option value="<?= $seller['seller_id'] ?>"><?= $seller['seller_name'] ?> - <?= $seller['store_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-secondary" id="clear_seller_filter" title="Clear Seller Filter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadSalesRevenue()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="gross_revenue"><?= $currency ?> 0.00</h3>
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
                            <h3 id="net_revenue"><?= $currency ?> 0.00</h3>
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
                            <h3 id="total_orders">0</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="total_units">0</h3>
                            <p>Total Units</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Orders & Units</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="ordersChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Metrics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Customer Metrics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-plus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">New Customers</span>
                                            <span class="info-box-number" id="new_customers">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-redo"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Returning Customers</span>
                                            <span class="info-box-number" id="returning_customers">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Conversion Rate</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Conversion Rate</span>
                                    <span class="info-box-number" id="conversion_rate">0%</span>
                                    <span class="info-box-text">Cart Additions: <span id="cart_additions">0</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabular Reports -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Selling Products</h3>
                        </div>
                        <div class="card-body">
                            <table id="top_products_table" class="table table-striped table-bordered"
                                data-toggle="table"
                                data-url="<?= base_url('admin/analytics/get_top_products_table') ?>"
                                data-side-pagination="server"
                                data-pagination="true"
                                data-page-list="[10, 20, 50]"
                                data-search="true"
                                data-show-refresh="true"
                                data-sort-name="total_revenue"
                                data-sort-order="desc"
                                data-mobile-responsive="true"
                                data-show-export="true"
                                data-export-types='["txt","excel"]'
                                data-query-params="topProductsTableParams">
                                <thead>
                                    <tr>
                                        <th data-field="product_name" data-sortable="true">Product Name</th>
                                        <th data-field="sku" data-sortable="true">SKU</th>
                                        <th data-field="total_sold" data-sortable="true">Units Sold</th>
                                        <th data-field="total_revenue" data-sortable="true" data-formatter="currencyFormatter">Revenue</th>
                                        <th data-field="total_profit" data-sortable="true" data-formatter="currencyFormatter">Profit</th>
                                        <th data-field="margin" data-sortable="true">Margin %</th>
                                        <th data-field="avg_sale_price" data-sortable="true" data-formatter="currencyFormatter">Avg Price</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Sellers</h3>
                        </div>
                        <div class="card-body">
                            <table id="top_sellers_table" class="table table-striped table-bordered"
                                data-toggle="table"
                                data-url="<?= base_url('admin/analytics/get_top_sellers_table') ?>"
                                data-side-pagination="server"
                                data-pagination="true"
                                data-page-list="[10, 20, 50]"
                                data-search="true"
                                data-show-refresh="true"
                                data-sort-name="total_revenue"
                                data-sort-order="desc"
                                data-mobile-responsive="true"
                                data-show-export="true"
                                data-export-types='["txt","excel"]'
                                data-query-params="topSellersTableParams">
                                <thead>
                                    <tr>
                                        <th data-field="seller_id" data-sortable="true">ID</th>
                                        <th data-field="seller_name" data-sortable="true">Seller Name</th>
                                        <th data-field="store_name" data-sortable="true">Store Name</th>
                                        <th data-field="total_revenue" data-sortable="true" data-formatter="currencyFormatter">Revenue</th>
                                        <th data-field="total_profit" data-sortable="true" data-formatter="currencyFormatter">Profit</th>
                                        <th data-field="total_orders" data-sortable="true">Orders</th>
                                        <th data-field="total_units" data-sortable="true">Units</th>
                                        <th data-field="average_margin" data-sortable="true">Margin %</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/admin/js/Chart.min.js') ?>"></script>
<script src="<?= base_url('assets/admin/custom/analytics-sales-revenue.js') ?>"></script>

