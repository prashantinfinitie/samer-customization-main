<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Product Analytics</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Products</li>
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
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadProductAnalytics()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Report Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Product Performance</h3>
                        </div>
                        <div class="card-body">
                            <table id="product-table" class="table table-bordered table-striped" data-toggle="table" data-pagination="true" data-page-size="25">
                                <thead>
                                    <tr>
                                        <th data-sortable="true">Product Name</th>
                                        <th data-sortable="true">SKU</th>
                                        <th data-sortable="true">Units Sold</th>
                                        <th data-sortable="true">Revenue</th>
                                        <th data-sortable="true">Profit</th>
                                        <th data-sortable="true">Avg Weekly Sales</th>
                                        <th data-sortable="true">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody id="product-table-body">
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
    loadProductAnalytics();

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

function loadProductAnalytics() {
    const period = $('#period_filter').val();
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();

    // Load product report
    $.ajax({
        url: '<?= base_url("seller/analytics/get_product_report") ?>',
        type: 'GET',
        data: {
            period: period,
            start_date: startDate,
            end_date: endDate,
            limit: 100
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
                    html += '<td><?= $currency ?> ' + parseFloat(item.total_profit).toFixed(2) + '</td>';
                    html += '<td>' + parseFloat(item.average_weekly_sales).toFixed(2) + '</td>';
                    html += '<td>' + parseFloat(item.margin).toFixed(2) + '%</td>';
                    html += '</tr>';
                });
                $('#product-table-body').html(html);
            }
        }
    });
}
</script>

