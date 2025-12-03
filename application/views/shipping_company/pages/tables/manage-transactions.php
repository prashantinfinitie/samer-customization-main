<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>All Transactions</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('shipping-company/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Transactions</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-innr">
                            <div class="row pt-4">
                                <div class="col-xl-4 col-lg-6 col-md-6 col-12">
                                    <div class="card pull-up">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="align-self-center text-primary">
                                                        <i class="far fa-money-bill-alt fa-3x"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h5 class="text-muted text-bold-500">Cash In Hand</h5>
                                                        <h3 class="text-bold-600"><?= $curreny ?> <?= (isset($cash_in_hand) && !empty($cash_in_hand[0]['cash_received'])) ? number_format($cash_in_hand[0]['cash_received'], 2) : "0.00" ?></h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-6 col-md-6 col-12">
                                    <div class="card pull-up">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="align-self-center text-success">
                                                        <i class="fas fa-check-circle fa-3x"></i>
                                                    </div>
                                                    <div class="media-body text-right">
                                                        <h5 class="text-muted text-bold-500">Cash Collected</h5>
                                                        <h3 class="text-bold-600"><?= $curreny ?> <?= (isset($cash_collected) && !empty($cash_collected[0]['total_amt'])) ? number_format($cash_collected[0]['total_amt'], 2) : "0.00" ?></h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row col-md-12 mt-3">
                                <div class="form-group col-md-3">
                                    <label>Date Range:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="datepicker">
                                        <input type="hidden" id="start_date">
                                        <input type="hidden" id="end_date">
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Filter By Type</label>
                                    <select id="filter_type" name="filter_type" class="form-control">
                                        <option value="">All</option>
                                        <option value="credit">Credit</option>
                                        <option value="debit">Debit</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-center pt-4">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="transactions_filter()">Filter</button>
                                </div>
                            </div>

                            <input type="hidden" value="<?= $curreny ?>" name="store_currency">
                            <table class='table table-striped'
                                data-toggle="table"
                                data-url="<?= base_url('shipping-company/fund-transfer/get_transactions') ?>"
                                data-side-pagination="server"
                                data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100]"
                                data-search="true"
                                data-show-columns="true"
                                data-show-refresh="true"
                                data-sort-name="id"
                                data-sort-order="desc"
                                data-mobile-responsive="true"
                                data-show-export="true"
                                data-export-types='["txt","excel"]'
                                data-export-options='{"fileName": "shipping-company-transactions"}'
                                data-query-params="transactions_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">ID</th>
                                        <th data-field="order_id" data-sortable="false">Order ID</th>
                                        <th data-field="txn_id" data-sortable="false">Txn ID</th>
                                        <th data-field="amount" data-sortable="true">Amount (<?= $curreny ?>)</th>
                                        <th data-field="type" data-sortable="false">Type</th>
                                        <th data-field="message" data-sortable="false">Message</th>
                                        <th data-field="date" data-sortable="true">Date</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function transactions_query_params(params) {
    params.start_date = $('#start_date').val() || '';
    params.end_date = $('#end_date').val() || '';
    params.filter_type = $('#filter_type').val() || '';
    return params;
}

function transactions_filter() {
    $('table').bootstrapTable('refresh');
}
</script>

