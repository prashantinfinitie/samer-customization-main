<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage Quotes</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('shipping_company/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Quotes</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <button class="btn btn-sm btn-primary" id="addQuoteBtn">Add Quote</button>
                        </div>
                    </div>

                    <table id="quotesTable" class="table table-striped"
                        data-toggle="table"
                        data-url="<?= base_url('shipping_company/quotes/list') ?>"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-page-list="[10,25,50]"
                        data-search="true"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="quotes_query_params">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="zipcode" data-sortable="true">Zipcode</th>
                                <th data-field="price" data-sortable="true">Price (<?= isset($currency) ? $currency : '' ?>)</th>
                                <th data-field="eta_text">ETA</th>
                                <th data-field="cod_available">COD</th>
                                <th data-field="additional_charges">Add. Charges</th>
                                <th data-field="is_active" data-sortable="true">Active</th>
                                <th data-field="operate">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="quoteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <form id="quoteForm" class="form-submit-event">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quote</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="id" id="quote_id" value="">
                    <div class="form-group">
                        <label>Zipcode</label>
                        <select name="zipcode" id="zipcode" class="form-control" required>
                            <option value="">Select Zipcode</option>
                            <?php if (!empty($zipcodes)) {
                                foreach ($zipcodes as $z) { ?>
                                    <option value="<?= ($z) ?>"><?= ($z) ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (<?= isset($currency) ? $currency : '' ?>)</label>
                        <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>ETA (e.g. 2-3 days)</label>
                        <input type="text" name="eta_text" id="eta_text" class="form-control" maxlength="50" required>
                    </div>
                    <div class="form-group">
                        <label>Additional Charges</label>
                        <input type="number" step="0.01" min="0" name="additional_charges" id="additional_charges" class="form-control">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="cod_available" name="cod_available" checked>
                        <label class="form-check-label" for="cod_available">COD Available</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-success" id="saveQuoteBtn" type="button">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- include script -->
<script>
    (function($) {
        window.quotes_query_params = function(params) {
            return {
                offset: params.offset,
                limit: params.limit,
                search: params.search,
                sort: params.sort,
                order: params.order
            };
        };

        // actions column formatter for quotes table
        window.operateFormatter = function(value, row, index) {
            var edit = '<button class="btn btn-sm btn-primary edit-quote mr-1" data-id="' + row.id + '"><i class="fa fa-pen"></i></button>';
            var del = '<button class="btn btn-sm btn-danger delete-quote" data-id="' + row.id + '"><i class="fa fa-trash"></i></button>';
            return edit + del;
        };

        $(function() {
            var $table = $('#quotesTable');

            // open add modal
            $(document).on('click', '#addQuoteBtn', function() {
                $('#quoteForm')[0].reset();
                $('#quote_id').val('');
                $('#quoteModal').modal('show');
            });

            // edit: fetch via list endpoint filtered by id
            $(document).on('click', '.edit-quote', function() {
                var id = $(this).data('id');
                // fetch one quote by searching id
                $.get(base_url + 'shipping_company/quotes/list', {
                    offset: 0,
                    limit: 10,
                    search: id
                }, function(resp) {
                    if (!resp || !resp.rows) {
                        alert('Failed to fetch');
                        return;
                    }
                    var item = resp.rows.find(function(it) {
                        return it.id == id;
                    });
                    if (!item) {
                        alert('Quote not found');
                        return;
                    }
                    $('#quote_id').val(item.id);
                    $('#zipcode').val(item.zipcode);
                    $('#price').val(item.price);
                    $('#eta_text').val(item.eta_text);
                    $('#additional_charges').val(item.additional_charges);
                    $('#cod_available').prop('checked', item.cod_available == 1 || item.cod_available === '1' || item.cod_available === 'Yes');
                    $('#quoteModal').modal('show');
                }, 'json').fail(function() {
                    alert('Failed to fetch quote');
                });
            });

            // delete
            $(document).on('click', '.delete-quote', function() {
                if (!confirm('Delete this quote?')) return;
                var id = $(this).data('id');
                var data = {};
                data.id = id;
                data[$('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').attr('name')] = $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val();
                $.post(base_url + 'shipping_company/quotes/delete', data, function(resp) {
                    if (!resp) {
                        alert('No response');
                        return;
                    }
                    if (resp.error) {
                        alert(resp.message);
                    } else {
                        alert(resp.message);
                        $table.bootstrapTable('refresh');
                    }
                    // update CSRF tokens if provided
                    if (resp.csrfHash) $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val(resp.csrfHash);
                }, 'json').fail(function() {
                    alert('Delete failed');
                });
            });

            // save (create/update)
            $(document).on('click', '#saveQuoteBtn', function(e) {
                e.preventDefault();
                var url = $('#quote_id').val() ? base_url + 'shipping_company/quotes/update' : base_url + 'shipping_company/quotes/create';
                var data = $('#quoteForm').serialize();
                $.post(url, data, function(resp) {
                    if (!resp) {
                        alert('No response');
                        return;
                    }
                    if (resp.error) {
                        alert(resp.message);
                    } else {
                        $('#quoteModal').modal('hide');
                        $table.bootstrapTable('refresh');
                    }
                    if (resp.csrfHash) $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val(resp.csrfHash);
                }, 'json').fail(function() {
                    alert('Save failed');
                });
            });

            // Initialize tables with operate formatter if not done automatically
            window.operateEvents = {
                // placeholder for bootstrap-table operate events if needed
            };
        });
    })(jQuery);
</script>
