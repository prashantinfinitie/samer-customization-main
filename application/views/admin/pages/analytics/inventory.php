<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Inventory & Purchasing Analytics</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Inventory</li>
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
                                    <label>Seller (Optional)</label>
                                    <select id="seller_filter" class="form-control">
                                        <option value="">All Sellers</option>
                                        <?php foreach ($sellers as $seller) { ?>
                                            <option value="<?= $seller['seller_id'] ?>"><?= $seller['seller_name'] ?> - <?= $seller['store_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Weeks Ahead (Purchase Suggestions)</label>
                                    <input type="number" id="weeks_ahead" class="form-control" value="4" min="1" max="12">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadInventory()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Health Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total_products">0</h3>
                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="total_cost_value"><?= $currency ?> 0.00</h3>
                            <p>Total Cost Value</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="total_expected_profit"><?= $currency ?> 0.00</h3>
                            <p>Expected Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="inventory_turnover">0</h3>
                            <p>Turnover Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Status -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Low Stock Items</span>
                            <span class="info-box-number" id="low_stock_count">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Out of Stock</span>
                            <span class="info-box-number" id="out_of_stock_count">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-warehouse"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Avg Inventory Value</span>
                            <span class="info-box-number" id="avg_inventory_value"><?= $currency ?> 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Suggestions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Purchase Suggestions</h3>
                            <p class="text-muted small">Products with low inventory and expected sales in the next <span id="suggested_weeks">4</span> weeks</p>
                        </div>
                        <div class="card-body">
                            <table id="purchase-suggestions-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Current Stock</th>
                                        <th>Avg Weekly Sales</th>
                                        <th>Expected Sales</th>
                                        <th>Suggested Quantity</th>
                                        <th>Estimated Cost</th>
                                        <th>Urgency</th>
                                    </tr>
                                </thead>
                                <tbody id="purchase-suggestions-body">
                                    <tr>
                                        <td colspan="7" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    loadInventory();
});

function loadInventory() {
    const sellerId = $('#seller_filter').val();
    const weeksAhead = $('#weeks_ahead').val() || 4;

    // Load inventory health
    $.ajax({
        url: '<?= base_url("admin/analytics/get_inventory_health") ?>',
        type: 'GET',
        data: {
            seller_id: sellerId
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error && response.data) {
                const data = response.data;
                $('#total_products').text(data.total_products);
                $('#total_cost_value').text('<?= $currency ?> ' + parseFloat(data.total_cost_value).toFixed(2));
                $('#total_expected_profit').text('<?= $currency ?> ' + parseFloat(data.total_expected_profit).toFixed(2));
                $('#inventory_turnover').text(parseFloat(data.inventory_turnover).toFixed(2));
                $('#low_stock_count').text(data.low_stock_count);
                $('#out_of_stock_count').text(data.out_of_stock_count);
                $('#avg_inventory_value').text('<?= $currency ?> ' + parseFloat(data.average_inventory_value).toFixed(2));
            }
        }
    });

    // Load purchase suggestions
    $.ajax({
        url: '<?= base_url("admin/analytics/get_purchase_suggestions") ?>',
        type: 'GET',
        data: {
            seller_id: sellerId,
            weeks_ahead: weeksAhead
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error && response.data) {
                $('#suggested_weeks').text(weeksAhead);
                let html = '';
                if (response.data.length === 0) {
                    html = '<tr><td colspan="7" class="text-center">No purchase suggestions at this time.</td></tr>';
                } else {
                    response.data.forEach(function(item) {
                        const urgencyClass = item.urgency === 'high' ? 'badge-danger' : 'badge-warning';
                        html += '<tr>';
                        html += '<td>' + item.product_name + '</td>';
                        html += '<td>' + item.current_stock + '</td>';
                        html += '<td>' + parseFloat(item.average_weekly_sales).toFixed(2) + '</td>';
                        html += '<td>' + parseFloat(item.expected_sales_weeks).toFixed(2) + '</td>';
                        html += '<td><strong>' + item.suggested_quantity + '</strong></td>';
                        html += '<td><?= $currency ?> ' + parseFloat(item.estimated_cost).toFixed(2) + '</td>';
                        html += '<td><span class="badge ' + urgencyClass + '">' + item.urgency.toUpperCase() + '</span></td>';
                        html += '</tr>';
                    });
                }
                $('#purchase-suggestions-body').html(html);
            }
        }
    });
}
</script>

