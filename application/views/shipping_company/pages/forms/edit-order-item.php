<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>View Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('shipping-company/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- ✅ OVERALL ORDER STATUS CARD -->
                    <?php if (isset($overall_status)) { ?>
                        <div class="card card-<?= $overall_status['badge'] ?> mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Overall Order Status
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4>
                                            <span class="badge badge-<?= $overall_status['badge'] ?> p-2">
                                                <?= $overall_status['label'] ?>
                                            </span>
                                        </h4>
                                        <p class="mb-0"><?= $overall_status['message'] ?></p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <small class="text-muted">
                                            <i class="fas fa-boxes mr-1"></i>
                                            Total Items: <?= count($items) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="card card-info overflow-auto">
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <input type="hidden" name="order_id" id="order_id" value="<?php echo $order_detls['id']; ?>">
                                    <th class="w-10px">Order ID</th>
                                    <td><?php echo $order_detls['id']; ?></td>
                                </tr>
                                <tr>
                                    <th class="w-10px">Buyer Name</th>
                                    <td><?php echo $order_detls['uname'] ?? 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th class="w-10px">Email</th>
                                    <td><?= (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) ? str_repeat("X", strlen($order_detls['email']) - 3) . substr($order_detls['email'], -3) : $order_detls['email'] ?></td>
                                </tr>
                                <tr>
                                    <th class="w-10px">Contact</th>
                                    <td><?= (!defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($order_detls['mobile']) - 3) . substr($order_detls['mobile'], -3) : $order_detls['mobile']; ?></td>
                                </tr>

                                <?php if (!empty($order_detls['notes'])) { ?>
                                    <tr>
                                        <th class="w-10px">Order Note</th>
                                        <td><?php echo $order_detls['notes']; ?></td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <th class="w-10px">Items</th>
                                    <td></td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <div class="card card-info mb-3 mt-2">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th scope="col">#</th>
                                                                <th scope="col">Product</th>
                                                                <th scope="col">Image</th>
                                                                <th scope="col">Qty</th>
                                                                <th scope="col">Seller (Pickup Location)</th>
                                                                <th scope="col">Type</th>
                                                                <th scope="col">Price</th>
                                                                <th scope="col">Subtotal</th>
                                                                <th scope="col">Current Status</th>
                                                                <th scope="col">Update Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $badges = [
                                                                "draft" => "secondary",
                                                                "awaiting" => "secondary",
                                                                "received" => "primary",
                                                                "processed" => "info",
                                                                "shipped" => "warning",
                                                                "delivered" => "success",
                                                                "returned" => "danger",
                                                                "cancelled" => "danger"
                                                            ];

                                                            $total = 0;
                                                            foreach ($items as $index => $item) {
                                                                $item['discounted_price'] = ($item['discounted_price'] == '') ? 0 : $item['discounted_price'];
                                                                $subtotal = ($item['quantity'] != 0 && ($item['discounted_price'] != '' && $item['discounted_price'] > 0) && $item['price'] > $item['discounted_price']) ? ($item['price'] - $item['discounted_price']) : ($item['price'] * $item['quantity']);
                                                                $total += $subtotal;

                                                                // Check if status can be updated (not in final state)
                                                                $is_final_state = in_array($item['active_status'], ['delivered', 'cancelled', 'returned']);
                                                            ?>
                                                                <tr class="<?= $is_final_state ? 'table-secondary' : '' ?>">
                                                                    <th scope="row"><?= $index + 1 ?></th>
                                                                    <td>
                                                                        <strong><?= $item['pname'] ?></strong>
                                                                        <?php if (!empty($item['variant_name'])) { ?>
                                                                            <br><small class="text-muted"><?= $item['variant_name'] ?></small>
                                                                        <?php } ?>
                                                                    </td>
                                                                    <td>
                                                                        <a href='<?= $item['product_image'] ?>' class="image-box-100" data-toggle='lightbox' data-gallery='order-images'>
                                                                            <img src='<?= $item['product_image'] ?>' alt="<?= $item['pname'] ?>" style="max-width: 60px;">
                                                                        </a>
                                                                    </td>
                                                                    <td><strong><?= $item['quantity'] ?></strong></td>
                                                                    <td style="min-width: 200px;">
                                                                        <?php if (!empty($item['seller_info'])) { ?>
                                                                            <strong><?= $item['seller_info']['name'] ?></strong><br>
                                                                            <small class="text-muted">
                                                                                📧 <?= $item['seller_info']['email'] ?><br>
                                                                                📱 <?= $item['seller_info']['mobile'] ?>
                                                                                <?php if (!empty($item['seller_info']['address'])) { ?>
                                                                                    <br>📍 <?= $item['seller_info']['address'] ?>
                                                                                <?php } ?>
                                                                            </small>
                                                                        <?php } else { ?>
                                                                            <span class="text-muted">N/A</span>
                                                                        <?php } ?>
                                                                    </td>
                                                                    <td><?= str_replace('_', ' ', $item['product_type']) ?></td>
                                                                    <td><?= $settings['currency'] ?> <?= round($item['price'], 2) ?></td>
                                                                    <td><strong><?= $settings['currency'] ?> <?= round($subtotal, 2) ?></strong></td>
                                                                    <td>
                                                                        <span class="text-uppercase p-2 status-<?= $item['id'] ?> badge badge-<?= $badges[$item['active_status']] ?>">
                                                                            <?= str_replace('_', ' ', ($item['active_status'] == 'draft' ? "awaiting" : $item['active_status'])) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td style="min-width: 180px;">
                                                                        <?php if ($is_final_state) { ?>
                                                                            <span class="text-muted"><small>Cannot update<br>(Final state)</small></span>
                                                                        <?php } else { ?>
                                                                            <select class="form-control form-control-sm order_item_status mb-2" data-item-id="<?= $item['id'] ?>" data-current-status="<?= $item['active_status'] ?>">
                                                                                <option value=''>Select Status</option>
                                                                                <option value="received" <?= $item['active_status'] == 'received' ? 'selected' : '' ?>>Received</option>
                                                                                <option value="processed" <?= $item['active_status'] == 'processed' ? 'selected' : '' ?>>Processed</option>
                                                                                <option value="shipped" <?= $item['active_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                                                <option value="delivered" <?= $item['active_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                                            </select>
                                                                            <button type="button" class="btn btn-primary btn-sm btn-block update_shipping_status" data-item-id="<?= $item['id'] ?>">
                                                                                <i class="fas fa-sync-alt mr-1"></i> Update
                                                                            </button>
                                                                        <?php } ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-light">
                                                                <th colspan="7" class="text-right">Total:</th>
                                                                <th><?= $settings['currency'] ?> <?= round($total, 2) ?></th>
                                                                <th colspan="2"></th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Total(<?= $settings['currency'] ?>)</th>
                                    <td id='amount'><?php echo round($total, 2); ?></td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Delivery Charge(<?= $settings['currency'] ?>)</th>
                                    <td><?php echo round($order_detls['delivery_charge'], 2); ?></td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Payment Method</th>
                                    <td><?php echo $order_detls['payment_method']; ?></td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Address</th>
                                    <td><?php echo $order_detls['address']; ?></td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Delivery Date & Time</th>
                                    <td><?php echo (!empty($order_detls['delivery_date']) && $order_detls['delivery_date'] != NULL) ? date('d-M-Y', strtotime($order_detls['delivery_date'])) . " - " . $order_detls['delivery_time'] : "Anytime"; ?></td>
                                </tr>

                                <tr>
                                    <th class="w-10px">Order Date</th>
                                    <td><?php echo date('d-M-Y', strtotime($order_detls['date_added'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Update shipping status for individual items
        $(document).on('click', '.update_shipping_status', function(e) {
            e.preventDefault();

            var btn = $(this);
            var order_item_id = btn.data('item-id');
            var statusDropdown = $('.order_item_status[data-item-id="' + order_item_id + '"]');
            var status = statusDropdown.val();
            var currentStatus = statusDropdown.data('current-status');
            var order_id = $('#order_id').val();

            if (status == '' || status == null) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please select a status',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Prevent updating to same status
            if (status == currentStatus) {
                Swal.fire({
                    title: 'Info',
                    text: 'Item is already in this status',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to update this item's status to " + status.toUpperCase() + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.value == true) {
                    // Disable button to prevent double clicks
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

                    $.ajax({
                        url: '<?= base_url('shipping-company/orders/update_order_status') ?>',
                        type: 'GET',
                        data: {
                            id: order_item_id,
                            status: status
                        },
                        dataType: 'json',
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Update');

                            if (response.error == false) {
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Update');
                            Swal.fire({
                                title: 'Error',
                                text: 'Something went wrong! Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
    });
</script>