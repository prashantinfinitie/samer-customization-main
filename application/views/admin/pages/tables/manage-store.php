<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage Stores</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Stores</li>
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
                            <div class="align-items-center d-flex justify-content-between mb-2">
                                <div class="align-items-center d-flex">
                                    <div class="mx-4 w-75">
                                        <label for="store_status_filter" class="col-form-label p-0">Filter By Store Status</label>
                                        <select id="store_status_filter" name="store_status_filter" placeholder="Select Status" required="" class="form-control">
                                            <option value="">All</option>
                                            <option value="approved">Approved</option>
                                            <option value="not_approved">Not Approved</option>
                                            <option value="deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="gaps-1-5x"></div>
                            <table class='table-striped' id='store_table' data-toggle="table" data-url="<?= base_url('admin/stores/view_stores') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="s.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="store_status_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">ID</th>
                                        <th data-field="store_name" data-sortable="true">Store Name</th>
                                        <th data-field="vendor_name" data-sortable="true">Vendor</th>
                                        <th data-field="vendor_email" data-sortable="true" data-visible="false">Vendor Email</th>
                                        <th data-field="vendor_mobile" data-sortable="true" data-visible="false">Vendor Mobile</th>
                                        <th data-field="logo" data-sortable="false">Logo</th>
                                        <th data-field="product_count" data-sortable="true">Products</th>
                                        <th data-field="rating" data-sortable="true">Rating</th>
                                        <th data-field="is_default" data-sortable="true">Default</th>
                                        <th data-field="status" data-sortable="false">Status</th>
                                        <th data-field="date" data-sortable="true">Date Added</th>
                                        <th data-field="operate" data-sortable="false">Actions</th>
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
    function store_status_params(p) {
        return {
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search,
            store_status: $('#store_status_filter').val()
        };
    }

    $(document).ready(function() {
        // Filter by status
        $('#store_status_filter').on('change', function() {
            $('#store_table').bootstrapTable('refresh');
        });

        // Debug: Check if buttons exist
        setTimeout(function() {
            console.log('Approve buttons found:', $('.approve-store').length);
            console.log('Reject buttons found:', $('.reject-store').length);
            console.log('Deactivate buttons found:', $('.deactivate-store').length);
        }, 2000);

        // Approve store
        $(document).on('click', '.approve-store', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id') || $(this).attr('data-id');
            console.log('Approve button clicked, ID:', id);
            if (!id) {
                console.error('No ID found for approve button');
                return false;
            }
            console.log('Showing Swal dialog...');
            Swal.fire({
                title: 'Approve Store?',
                text: 'Are you sure you want to approve this store?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                console.log('Swal result:', result);
                if (result && (result.isConfirmed || result.value === true)) {
                    console.log('User confirmed, making AJAX call...');
                    $.ajax({
                        url: '<?= base_url('admin/stores/approve-store') ?>',
                        type: 'POST',
                        data: {
                            id: id,
                            <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            console.log('AJAX request starting...');
                        },
                        success: function(result) {
                            console.log('AJAX success:', result);
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
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred: ' + error
                            });
                        }
                    });
                }
            });
        });

        // Reject store
        $(document).on('click', '.reject-store', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id') || $(this).attr('data-id');
            console.log('Reject button clicked, ID:', id);
            if (!id) {
                console.error('No ID found for reject button');
                return false;
            }
            Swal.fire({
                title: 'Reject Store?',
                text: 'Are you sure you want to reject this store?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reject it!'
            }).then((result) => {
                if (result && (result.isConfirmed || result.value === true)) {
                    $.ajax({
                        url: '<?= base_url('admin/stores/reject-store') ?>',
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
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred: ' + error
                            });
                        }
                    });
                }
            });
        });

        // Deactivate store
        $(document).on('click', '.deactivate-store', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id') || $(this).attr('data-id');
            console.log('Deactivate button clicked, ID:', id);
            if (!id) {
                console.error('No ID found for deactivate button');
                return false;
            }
            Swal.fire({
                title: 'Deactivate Store?',
                text: 'Are you sure you want to deactivate this store?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, deactivate it!'
            }).then((result) => {
                if (result && (result.isConfirmed || result.value === true)) {
                    $.ajax({
                        url: '<?= base_url('admin/stores/deactivate-store') ?>',
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
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred: ' + error
                            });
                        }
                    });
                }
            });
        });

        // Delete store
        $(document).on('click', '.delete-store', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id') || $(this).attr('data-id');
            console.log('Delete button clicked, ID:', id);
            if (!id) {
                console.error('No ID found for delete button');
                return false;
            }
            Swal.fire({
                title: 'Delete Store?',
                text: 'Are you sure you want to delete this store? This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result && (result.isConfirmed || result.value === true)) {
                    $.ajax({
                        url: '<?= base_url('admin/stores/delete-store') ?>',
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
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred: ' + error
                            });
                        }
                    });
                }
            });
        });
    });
</script>

