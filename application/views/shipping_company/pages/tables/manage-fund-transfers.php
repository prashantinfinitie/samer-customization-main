<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Shipping Company Fund Transfers</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Fund Transfers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card content-area p-4">
                <div class="card-innr">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Filter By Shipping Company</label>
                            <select id="filter_company" class="form-control">
                                <option value="">All</option>
                                <?php if (isset($shipping_companies) && !empty($shipping_companies)) : ?>
                                    <?php foreach ($shipping_companies as $sc) : ?>
                                        <option value="<?= $sc['user_id'] ?>"><?= $sc['username'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Filter By Date Range</label>
                            <input type="text" class="form-control" id="ft_datepicker" placeholder="Select Date Range">
                            <input type="hidden" id="ft_start_date">
                            <input type="hidden" id="ft_end_date">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-outline-primary" onclick="refreshFundTransfers()">Apply Filter</button>
                        </div>
                    </div>

                    <table id="fund_transfers_table"
                        class="table table-striped"
                        data-toggle="table"
                        data-url="<?= base_url('admin/shipping_companies/get_fund_transfers_list') ?>"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-page-list="[5,10,20,50]"
                        data-search="true"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="fundTransfersQueryParams"
                        data-show-refresh="true"
                        data-show-export="true"
                        data-export-types='["txt","excel"]'
                        data-export-options='{"fileName": "shipping-company-fund-transfers"}'>
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="name">Company</th>
                                <th data-field="mobile" data-visible="false">Mobile</th>
                                <th data-field="opening_balance">Opening Balance</th>
                                <th data-field="amount">Amount</th>
                                <th data-field="closing_balance">Closing Balance</th>
                                <th data-field="status">Type</th>
                                <th data-field="message">Message</th>
                                <th data-field="date_created">Date</th>
                            </tr>
                        </thead>
                    </table>

                </div>
            </div>
        </div>
    </section>
</div>

<!-- Fund Transfer Modal -->
<div class="modal fade" id="fund_transfer_shipping_company" tabindex="-1" role="dialog" aria-labelledby="fundTransferLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <form id="fund_transfer_form" class="form-horizontal" action="<?= base_url('admin/shipping_companies/manage_fund_transfer'); ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pay Shipping Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="shipping_company_id" id="ft_shipping_company_id" value="">
                    <input type="hidden" name="order_id" id="ft_order_id" value="">
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" id="ft_company_name" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Admin Balance</label>
                        <input type="text" id="ft_admin_balance" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" id="ft_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Transaction Note (optional)</label>
                        <input type="text" name="txn_note" id="ft_txn_note" class="form-control" placeholder="internal note">
                    </div>
                    <div class="form-group">
                        <label>Message (to company)</label>
                        <textarea name="message" id="ft_message" class="form-control">Admin payout for delivery charges</textarea>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="datetime-local" name="date" id="ft_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <!-- CSRF -->
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="ft_submit_btn" class="btn btn-success">Pay</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JS: uses jQuery, bootstrap-table, daterangepicker, toastr (must already be included in your template) -->
<script>
    // Query params for the bootstrap-table
    function fundTransfersQueryParams(params) {
        var q = params;
        q.filter_company = $('#filter_company').val() || '';
        q.start_date = $('#ft_start_date').val() || '';
        q.end_date = $('#ft_end_date').val() || '';
        return q;
    }

    // Refresh table
    function refreshFundTransfers() {
        $('#fund_transfers_table').bootstrapTable('refresh');
    }

    $(document).ready(function() {

        // date range picker (requires daterangepicker)
        if (typeof $.fn.daterangepicker !== 'undefined') {
            $('#ft_datepicker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                },
                opens: 'left'
            });

            $('#ft_datepicker').on('apply.daterangepicker', function(ev, picker) {
                $('#ft_start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#ft_end_date').val(picker.endDate.format('YYYY-MM-DD'));
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $('#ft_datepicker').on('cancel.daterangepicker', function(ev, picker) {
                $('#ft_start_date').val('');
                $('#ft_end_date').val('');
                $(this).val('');
            });
        }

        // Open Fund Transfer modal on click of buttons (the buttons are generated by model listing)
        $(document).on('click', '.fund_transfer_shipping_company', function(e) {
            e.preventDefault();
            var companyId = $(this).data('id');
            if (!companyId) {
                toastr.error('Company ID missing');
                return;
            }

            // Reset
            $('#fund_transfer_form')[0].reset();
            $('#ft_shipping_company_id').val(companyId);
            $('#ft_company_name').val('');
            $('#ft_admin_balance').val('Loading...');

            // fetch company details
            $.ajax({
                url: "<?= base_url('admin/shipping_companies/get_company_details') ?>/" + companyId,
                method: "GET",
                dataType: "json",
                success: function(res) {
                    if (res && Object.keys(res).length) {
                        $('#ft_company_name').val(res.username + ' (ID: ' + res.id + ')');
                    } else {
                        $('#ft_company_name').val('Unknown');
                    }
                },
                error: function() {
                    $('#ft_company_name').val('Unknown');
                }
            });

            // fetch admin details (balance)
            $.ajax({
                url: "<?= base_url('admin/shipping_companies/get_admin_details') ?>",
                method: "GET",
                dataType: "json",
                success: function(ares) {
                    if (ares && ares.balance !== undefined) {
                        $('#ft_admin_balance').val(parseFloat(ares.balance).toFixed(2));
                    } else {
                        $('#ft_admin_balance').val('N/A');
                    }
                },
                error: function() {
                    $('#ft_admin_balance').val('N/A');
                }
            });

            $('#fund_transfer_shipping_company').modal('show');
        });

        // Ajax submit fund transfer
        $('#fund_transfer_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#ft_submit_btn');
            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: $(this).attr('action'),
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function(res) {
                    if (res && res.error === false) {
                        toastr.success(res.message);
                        $('#fund_transfer_shipping_company').modal('hide');
                        // refresh tables
                        $('#fund_transfers_table').bootstrapTable('refresh');
                        // refresh companies table if exists
                        $('table[data-url="<?= base_url('admin/shipping_companies/get_shipping_companies_list') ?>"]').bootstrapTable('refresh');
                    } else {
                        var msg = (res && res.message) ? res.message : 'Failed to transfer';
                        toastr.error(msg);
                    }
                },
                error: function(xhr) {
                    toastr.error('Request failed. Check console for details.');
                    console.error(xhr.responseText);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Pay');
                }
            });
        });

    });
</script>
