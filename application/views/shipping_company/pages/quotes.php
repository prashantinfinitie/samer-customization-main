<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Manage Shipping Quotes</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQuoteModal">
            <i class="fas fa-plus"></i> Add Quote
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="quotes_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Weight Range</th>
                        <th>Price</th>
                        <th>Delivery Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox"></i> No quotes available
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Quote Modal -->
<div class="modal fade" id="addQuoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quote_form">
                <div class="modal-body">
                    <input type="hidden" name="quote_id" id="quote_id" value="">
                    
                    <div class="form-group mb-3">
                        <label for="weight_from" class="form-label">Weight From (kg)</label>
                        <input type="number" class="form-control" id="weight_from" name="weight_from" placeholder="0" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="weight_to" class="form-label">Weight To (kg)</label>
                        <input type="number" class="form-control" id="weight_to" name="weight_to" placeholder="0" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" placeholder="0.00" step="0.01" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="delivery_days" class="form-label">Delivery Days</label>
                        <input type="number" class="form-control" id="delivery_days" name="delivery_days" placeholder="1" min="1" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="quote_status" class="form-label">Status</label>
                        <select class="form-control" id="quote_status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Quote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load quotes - TODO: Implement API endpoint
    loadQuotes();

    function loadQuotes() {
        // TODO: Call API to load quotes
    }

    $('#quote_form').on('submit', function(e) {
        e.preventDefault();
        
        // TODO: Submit quote data via AJAX
        alert('Quote functionality coming soon!');
    });
});
</script>