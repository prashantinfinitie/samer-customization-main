<div class="card">
    <div class="card-header">
        <h5 class="card-title">Transaction History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="transactions_table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox"></i> No transactions found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load transactions - TODO: Implement API endpoint
    loadTransactions();

    function loadTransactions() {
        // TODO: Call API to load transactions
        // $.ajax({
        //     url: '<?php echo base_url("shipping_company/home/get_transactions"); ?>',
        //     type: 'GET',
        //     dataType: 'json',
        //     success: function(response) {
        //         // Process response and populate table
        //     }
        // });
    }
});
</script>