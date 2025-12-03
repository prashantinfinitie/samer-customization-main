<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Seller Comparison</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Seller Comparison</li>
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
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadSellerComparison()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seller Comparison Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Seller Performance Comparison</h3>
                        </div>
                        <div class="card-body">
                            <table id="seller-comparison-table" class="table table-bordered table-striped" data-toggle="table" data-pagination="true" data-page-size="25">
                                <thead>
                                    <tr>
                                        <th data-sortable="true">Seller Name</th>
                                        <th data-sortable="true">Store Name</th>
                                        <th data-sortable="true">Revenue</th>
                                        <th data-sortable="true">Profit</th>
                                        <th data-sortable="true">Orders</th>
                                        <th data-sortable="true">Units</th>
                                        <th data-sortable="true">Avg Margin %</th>
                                    </tr>
                                </thead>
                                <tbody id="seller-comparison-body">
                                    <tr>
                                        <td colspan="7" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Chart -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue Comparison by Seller</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="sellerComparisonChart" style="min-height: 400px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/admin/js/Chart.min.js') ?>"></script>
<script>
let sellerComparisonChart;

$(document).ready(function() {
    loadSellerComparison();

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

function loadSellerComparison() {
    const period = $('#period_filter').val();
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();

    $.ajax({
        url: '<?= base_url("admin/analytics/get_seller_comparison") ?>',
        type: 'GET',
        data: {
            period: period,
            start_date: startDate,
            end_date: endDate
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error && response.data) {
                let html = '';
                response.data.forEach(function(item) {
                    html += '<tr>';
                    html += '<td>' + item.seller_name + '</td>';
                    html += '<td>' + (item.store_name || 'N/A') + '</td>';
                    html += '<td><?= $currency ?> ' + parseFloat(item.total_revenue).toFixed(2) + '</td>';
                    html += '<td><?= $currency ?> ' + parseFloat(item.total_profit).toFixed(2) + '</td>';
                    html += '<td>' + item.total_orders + '</td>';
                    html += '<td>' + item.total_units + '</td>';
                    html += '<td>' + parseFloat(item.average_margin).toFixed(2) + '%</td>';
                    html += '</tr>';
                });
                $('#seller-comparison-body').html(html);

                // Update chart
                if (response.data.length > 0) {
                    const labels = response.data.map(item => item.seller_name);
                    const revenues = response.data.map(item => parseFloat(item.total_revenue));
                    const profits = response.data.map(item => parseFloat(item.total_profit));

                    if (sellerComparisonChart) {
                        sellerComparisonChart.destroy();
                    }

                    const ctx = document.getElementById('sellerComparisonChart').getContext('2d');
                    sellerComparisonChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Revenue',
                                data: revenues,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }, {
                                label: 'Profit',
                                data: profits,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
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
                            }
                        }
                    });
                }
            }
        }
    });
}
</script>

