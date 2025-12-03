<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Stores</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Stores</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card content-area p-4">
                        <div class="card-head">
                            <h4 class="card-title">Manage Stores</h4>
                            <a href="<?= base_url('seller/store/create_store') ?>" class="btn btn-primary float-right"><i class="fas fa-plus"></i> Add New Store</a>
                        </div>
                        <div class="gaps-1-5x"></div>
                        <table class='table-striped' id='store_table' data-toggle="table" data-url="<?= base_url('seller/store/get_stores') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]' data-export-options='{
                            "fileName": "stores-list",
                            "ignoreColumn": ["state"] 
                            }'>
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true" data-visible='false' data-align='center'>ID</th>
                                    <th data-field="store_name" data-sortable="true" data-align='center'>Store Name</th>
                                    <th data-field="logo" data-sortable="false" data-align='center'>Logo</th>
                                    <th data-field="product_count" data-sortable="true" data-align='center'>Products</th>
                                    <th data-field="rating" data-sortable="true" data-align='center'>Rating</th>
                                    <th data-field="is_default" data-sortable="true" data-align='center'>Default</th>
                                    <th data-field="status" data-sortable="true" data-align='center'>Status</th>
                                    <th data-field="date" data-sortable="true" data-align='center'>Date Added</th>
                                    <th data-field="operate" data-sortable="false" data-align='center'>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Delete store
        $(document).on('click', '.delete-store', function() {
            var id = $(this).data('id');
            if (confirm('Are you sure you want to delete this store? This action cannot be undone.')) {
                $.ajax({
                    url: '<?= base_url('seller/store/delete_store') ?>',
                    type: 'POST',
                    data: {
                        id: id,
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(result) {
                        if (result.error == false) {
                            $('#store_table').bootstrapTable('refresh');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: result.message,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: result.message
                            });
                        }
                    }
                });
            }
        });

        // Set default store
        $(document).on('click', '.set-default-store', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '<?= base_url('seller/store/set_default_store') ?>',
                type: 'POST',
                data: {
                    id: id,
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(result) {
                    if (result.error == false) {
                        $('#store_table').bootstrapTable('refresh');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: result.message
                        });
                    }
                }
            });
        });
    });
</script>

