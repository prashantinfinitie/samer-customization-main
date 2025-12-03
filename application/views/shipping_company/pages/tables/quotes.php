<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Shipping Quotes</h5>
                    <div>
                        <button id="addQuoteBtn" class="btn btn-primary btn-sm">Add Quote</button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <table id="quotesTable"
                        data-toggle="table"
                        data-url="<?= base_url('shipping_company/quotes/list') ?>"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-page-list="[10,25,50]"
                        data-search="true"
                        data-query-params="quotes_query_params"
                        class="table table-striped">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="zipcode" data-sortable="true">Zipcode</th>
                                <th data-field="price" data-sortable="true">Price</th>
                                <th data-field="eta_text" data-sortable="false">ETA</th>
                                <th data-field="cod_available" data-sortable="false">COD</th>
                                <th data-field="additional_charges" data-sortable="false">Add. Charges</th>
                                <th data-field="is_active" data-sortable="true">Active</th>
                                <th data-field="operate" data-formatter="operateFormatter">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add / Edit Modal -->
<div id="quoteModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <form id="quoteForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quote</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="quote_id" value="">
                    <div class="form-group">
                        <label for="zipcode">Zipcode</label>
                        <select name="zipcode" id="zipcode" class="form-control" required>
                            <option value="">Select Zipcode</option>
                            <?php foreach ($zipcodes as $zc) : ?>
                                <option value="<?= ($zc) ?>"><?= ($zc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input name="price" id="price" class="form-control" required type="number" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="eta_text">ETA (e.g. 2-3 days)</label>
                        <input name="eta_text" id="eta_text" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="additional_charges">Additional Charges</label>
                        <input name="additional_charges" id="additional_charges" class="form-control" type="number" step="0.01" min="0">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="cod_available" id="cod_available" class="form-check-input" checked>
                        <label for="cod_available" class="form-check-label">COD Available</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="quoteSaveBtn" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- include JS file -->
<script>
    // requires jQuery, bootstrap, bootstrap-table
    (function($) {
        // build query params for bootstrap-table
        window.quotes_query_params = function(params) {
            return {
                offset: params.offset,
                limit: params.limit,
                search: params.search,
                sort: params.sort,
                order: params.order
            };
        };

        // action buttons
        window.operateFormatter = function(value, row, index) {
            var edit = '<button data-id="' + row.id + '" class="btn btn-sm btn-primary edit-quote mr-1">Edit</button>';
            var del = '<button data-id="' + row.id + '" class="btn btn-sm btn-danger delete-quote">Delete</button>';
            return edit + del;
        };

        // start
        $(function() {
            var $table = $('#quotesTable');

            // open add modal
            $('#addQuoteBtn').on('click', function() {
                $('#quoteForm')[0].reset();
                $('#quote_id').val('');
                $('#quoteModal').modal('show');
            });

            // open edit
            $table.on('click', '.edit-quote', function() {
                var id = $(this).data('id');
                // fetch quote data then populate modal
                $.get('<?= base_url("shipping_company/quotes/list") ?>', {
                    offset: 0,
                    limit: 1,
                    search: '',
                    sort: 'id',
                    order: 'DESC'
                }, function() {})
                // simpler: get detail via dedicated endpoint (not implemented) - we can reuse existing list logic by filtering id
                $.get('<?= base_url("shipping_company/quotes/list") ?>', {
                    offset: 0,
                    limit: 10,
                    search: id
                }, function(resp) {
                    if (resp && resp.rows && resp.rows.length) {
                        var item = resp.rows.find(function(it) {
                            return it.id == id;
                        });
                        if (!item) {
                            toastr.error('Quote not found');
                            return;
                        }
                        $('#quote_id').val(item.id);
                        $('#zipcode').val(item.zipcode);
                        $('#price').val(item.price);
                        $('#eta_text').val(item.eta_text);
                        $('#additional_charges').val(item.additional_charges);
                        $('#cod_available').prop('checked', item.cod_available == 1 || item.cod_available === '1' || item.cod_available === 'Yes');
                        $('#quoteModal').modal('show');
                    } else {
                        toastr.error('Quote not found');
                    }
                }, 'json').fail(function() {
                    toastr.error('Failed to fetch quote');
                });
            });

            // delete
            $table.on('click', '.delete-quote', function() {
                if (!confirm('Delete quote?')) return;
                var id = $(this).data('id');
                $.post('<?= base_url("shipping_company/quotes/delete") ?>', {
                    id: id,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                }, function(resp) {
                    if (resp.error) {
                        toastr.error(resp.message);
                    } else {
                        toastr.success(resp.message);
                        $table.bootstrapTable('refresh');
                    }
                }, 'json').fail(function() {
                    toastr.error('Delete failed');
                });
            });

            // save (create/update)
            $('#quoteSaveBtn').on('click', function(e) {
                e.preventDefault();
                var id = $('#quote_id').val();
                var url = id ? '<?= base_url("shipping_company/quotes/update") ?>' : '<?= base_url("shipping_company/quotes/create") ?>';
                var data = $('#quoteForm').serialize();

                $.post(url, data, function(resp) {
                    if (!resp) {
                        toastr.error('No response');
                        return;
                    }
                    if (resp.error) {
                        toastr.error(resp.message);
                    } else {
                        toastr.success(resp.message);
                        $('#quoteModal').modal('hide');
                        $table.bootstrapTable('refresh');
                    }
                }, 'json').fail(function() {
                    toastr.error('Save failed');
                });
            });

        });
    })(jQuery);
</script>
