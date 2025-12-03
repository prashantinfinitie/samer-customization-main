<div class="card">
    <div class="card-header">
        <h5 class="card-title">Cash Collection</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-6">
                    <h6>Total Cash Received</h6>
                    <h3><?php echo isset($settings['currency']) ? $settings['currency'] : '$'; ?><?php echo number_format($cash_received, 2); ?></h3>
                </div>
            </div>
        </div>

        <hr>

        <h6 class="mt-4 mb-3">Cash Collection History</h6>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="cash_collection_table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Collection Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-inbox"></i> No cash collections found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load cash collection history - TODO: Implement API endpoint
    loadCashCollectionHistory();

    function loadCashCollectionHistory() {
        // TODO: Call API to load cash collection data
        // $.ajax({
        //     url: '<?php echo base_url("shipping_company/home/get_cash_collection"); ?>',
        //     type: 'GET',
        //     dataType: 'json',
        //     success: function(response) {
        //         // Process response and populate table
        //     }
        // });
    }
});
</script>