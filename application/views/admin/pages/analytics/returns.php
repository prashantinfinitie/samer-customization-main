<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Returns Analytics</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/analytics') ?>">Analytics</a></li>
                        <li class="breadcrumb-item active">Returns</li>
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
                                    <select id="seller_filter" class="form-control">
                                        <option value="">All Sellers</option>
                                        <?php foreach ($sellers as $seller) { ?>
                                            <option value="<?= $seller['seller_id'] ?>"><?= $seller['seller_name'] ?> - <?= $seller['store_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" onclick="loadReturns()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Returns Overview -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="total_returns">0</h3>
                            <p>Total Returns</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-undo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="returns_amount"><?= $currency ?> 0.00</h3>
                            <p>Returns Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="return_rate">0%</h3>
                            <p>Return Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Returns by Reason -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Returns by Reason</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="returnsReasonChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Returned Products</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Returns</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="returns-by-product-body">
                                    <tr>
                                        <td colspan="3" class="text-center">Loading...</td>
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

<script src="<?= base_url('assets/admin/js/Chart.min.js') ?>"></script>
<script>
let returnsReasonChart;

$(document).ready(function() {
    loadReturns();

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

function loadReturns() {
    const period = $('#period_filter').val();
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const sellerId = $('#seller_filter').val();

    $.ajax({
        url: '<?= base_url("admin/analytics/get_returns_dashboard") ?>',
        type: 'GET',
        data: {
            period: period,
            start_date: startDate,
            end_date: endDate,
            seller_id: sellerId
        },
        dataType: 'json',
        success: function(response) {
            if (!response.error && response.data) {
                const data = response.data;
                $('#total_returns').text(data.total_returns);
                $('#returns_amount').text('<?= $currency ?> ' + parseFloat(data.returns_amount).toFixed(2));
                $('#return_rate').text(parseFloat(data.return_rate).toFixed(2) + '%');

                // Update returns by product table
                let html = '';
                if (data.returns_by_product && data.returns_by_product.length > 0) {
                    data.returns_by_product.forEach(function(item) {
                        html += '<tr>';
                        html += '<td>' + item.product_name + '</td>';
                        html += '<td>' + item.returns_count + '</td>';
                        html += '<td><?= $currency ?> ' + parseFloat(item.returns_amount).toFixed(2) + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="3" class="text-center">No returns data</td></tr>';
                }
                $('#returns-by-product-body').html(html);

                // Update chart
                if (data.returns_by_reason && data.returns_by_reason.length > 0) {
                    const labels = data.returns_by_reason.map(item => item.reason || 'Unknown');
                    const counts = data.returns_by_reason.map(item => parseInt(item.count));

                    if (returnsReasonChart) {
                        returnsReasonChart.destroy();
                    }

                    const ctx = document.getElementById('returnsReasonChart').getContext('2d');
                    returnsReasonChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: counts,
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            }
        }
    });
}
</script>

