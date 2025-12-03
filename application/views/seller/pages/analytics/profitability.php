<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Profitability Analytics</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Profitability</li>
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
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadProfitability()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Metrics -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="total_profit"><?= $currency ?> 0.00</h3>
                            <p>Total Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total_revenue"><?= $currency ?> 0.00</h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="average_margin">0%</h3>
                            <p>Average Margin</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Profit by Product</h3>
                        </div>
                        <div class="card-body">
                            <table id="profit-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Units Sold</th>
                                        <th>Revenue</th>
                                        <th>Cost</th>
                                        <th>Profit</th>
                                        <th>Margin %</th>
                                    </tr>
                                </thead>
                                <tbody id="profit-table-body">
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
    loadProfitability();

    $('#period_filter').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom_date_range').show();
            $('#custom_date_range_end').show();
        } else {
            $('#custom_date_range').hide();
            $('#custom_date_range_end').hide();
        }
    });
});

function loadProfitability() {
    const period = $('#period_filter').val();
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();

    // Load profit report
    $.ajax({
        url: '<?= base_url("seller/analytics/get_profit_report") ?>',
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
                $('#total_profit').text('<?= $currency ?> ' + parseFloat(data.total_profit).toFixed(2));
                $('#total_revenue').text('<?= $currency ?> ' + parseFloat(data.total_revenue).toFixed(2));
                $('#average_margin').text(parseFloat(data.average_margin).toFixed(2) + '%');
            }
        }
    });

    // Load product-wise profit
    $.ajax({
        url: '<?= base_url("seller/analytics/get_product_report") ?>',
        type: 'GET',
        data: {
            period: period,
            start_date: startDate,
            end_date: endDate,
            limit: 50
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error && response.data) {
                let html = '';
                response.data.forEach(function(item) {
                    html += '<tr>';
                    html += '<td>' + item.product_name + '</td>';
                    html += '<td>' + (item.sku || 'N/A') + '</td>';
                    html += '<td>' + item.total_sold + '</td>';
                    html += '<td><?= $currency ?> ' + parseFloat(item.total_revenue).toFixed(2) + '</td>';
                    html += '<td><?= $currency ?> ' + parseFloat(item.cost_price).toFixed(2) + '</td>';
                    html += '<td><?= $currency ?> ' + parseFloat(item.total_profit).toFixed(2) + '</td>';
                    html += '<td>' + parseFloat(item.margin).toFixed(2) + '%</td>';
                    html += '</tr>';
                });
                $('#profit-table-body').html(html);
            }
        }
    });
}
</script>

