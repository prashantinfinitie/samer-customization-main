<?php
defined('BASEPATH') or exit('No direct script access allowed');
$settings = $this->data['settings'];
$cart = $this->data['cart'];
$currency = $this->data['currency'];
?>

<div class="main">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="pt-3 pb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo base_url('cart'); ?>">Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="checkout-form">
                <form id="checkout_form" method="POST">
                    <!-- Hidden Fields for CSRF and System Data -->
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                    <input type="hidden" name="csrf_token_name" value="<?php echo $this->security->get_csrf_token_name(); ?>" />
                    <input type="hidden" name="csrf_token_hash" value="<?php echo $this->security->get_csrf_hash(); ?>" />
                    <input type="hidden" id="user_id" value="<?php echo $this->data['user']->id; ?>" />
                    <input type="hidden" id="username" value="<?php echo $this->data['user']->username; ?>" />
                    <input type="hidden" id="user_email" value="<?php echo $this->data['user']->email; ?>" />
                    <input type="hidden" id="user_contact" value="<?php echo $this->data['user']->mobile ?? ''; ?>" />
                    <input type="hidden" id="app_name" value="<?php echo $settings['app_name'] ?? 'Shop'; ?>" />
                    <input type="hidden" id="logo" value="<?php echo $this->data['web_logo']; ?>" />
                    <input type="hidden" id="currency" value="<?php echo $currency; ?>" />
                    <input type="hidden" id="mobile" name="mobile" value="<?php echo $this->data['user']->mobile ?? ''; ?>" />

                    <!-- Payment Method Hidden Fields -->
                    <input type="hidden" id="razorpay_key_id" value="<?php echo $this->data['auth_settings']['razorpay_key_id'] ?? ''; ?>" />
                    <input type="hidden" id="stripe_key_id" value="<?php echo $this->data['auth_settings']['stripe_key_id'] ?? ''; ?>" />
                    <input type="hidden" id="paystack_key_id" value="<?php echo $this->data['auth_settings']['paystack_key_id'] ?? ''; ?>" />
                    <input type="hidden" id="flutterwave_public_key" value="<?php echo $this->data['auth_settings']['flutterwave_public_key'] ?? ''; ?>" />
                    <input type="hidden" id="flutterwave_currency" value="<?php echo $this->data['auth_settings']['flutterwave_currency'] ?? ''; ?>" />
                    <input type="hidden" id="paypal_client_id" value="<?php echo $this->data['auth_settings']['paypal_client_id'] ?? ''; ?>" />
                    <input type="hidden" id="amount" name="amount" value="<?php echo $cart['overall_amount']; ?>" />
                    <input type="hidden" id="sub_total" value="<?php echo $cart['sub_total']; ?>" />
                    <input type="hidden" id="temp_total" value="<?php echo $cart['overall_amount']; ?>" />
                    <input type="hidden" id="total" name="total" value="<?php echo $cart['overall_amount']; ?>" />
                    <input type="hidden" id="product_type" name="product_type" value="<?php echo (isset($cart[0]['type']) && $cart[0]['type'] == 'digital_product') ? 'digital_product' : 'physical_product'; ?>" />
                    <input type="hidden" id="download_allowed" value="<?php echo (isset($cart[0]['download_allowed'])) ? $cart[0]['download_allowed'] : 0; ?>" />
                    <input type="hidden" id="digital_product_email" name="email" value="" />
                    <input type="hidden" id="current_wallet_balance" value="<?php echo isset($this->data['wallet_balance'][0]['balance']) ? $this->data['wallet_balance'][0]['balance'] : 0; ?>" />
                    <input type="hidden" id="is_time_slots_enabled" value="0" />
                    <input type="hidden" id="start_date" name="delivery_date" value="" />
                    <input type="hidden" id="delivery_date" name="delivery_date" value="" />
                    <input type="hidden" id="promo_set" value="0" />
                    <input type="hidden" id="promo_is_cashback" value="0" />
                    <input type="hidden" id="wallet_used" name="wallet_used" value="0" />
                    <input type="hidden" id="delivery_charge_with_cod" name="delivery_charge_with_cod" value="0" />
                    <input type="hidden" id="delivery_charge_without_cod" name="delivery_charge_without_cod" value="0" />
                    <input type="hidden" id="is_shiprocket_order" name="is_shiprocket_order" value="0" />
                    <input type="hidden" id="razorpay_order_id" name="razorpay_order_id" value="" />
                    <input type="hidden" id="razorpay_payment_id" name="razorpay_payment_id" value="" />
                    <input type="hidden" id="razorpay_signature" name="razorpay_signature" value="" />
                    <input type="hidden" id="paystack_reference" name="paystack_reference" value="" />
                    <input type="hidden" id="stripe_payment_id" name="stripe_payment_id" value="" />
                    <input type="hidden" id="stripe_client_secret" name="stripe_client_secret" value="" />
                    <input type="hidden" id="flutterwave_transaction_id" name="flutterwave_transaction_id" value="" />
                    <input type="hidden" id="flutterwave_transaction_ref" name="flutterwave_transaction_ref" value="" />
                    <input type="hidden" id="paytm_transaction_token" name="paytm_transaction_token" value="" />
                    <input type="hidden" id="paytm_order_id" name="paytm_order_id" value="" />
                    <input type="hidden" id="midtrans_transaction_token" name="midtrans_transaction_token" value="" />
                    <input type="hidden" id="midtrans_order_id" name="midtrans_order_id" value="" />
                    <input type="hidden" id="my_fatoorah_order_id" name="my_fatoorah_order_id" value="" />
                    <input type="hidden" id="instamojo_payment_id" name="instamojo_payment_id" value="" />
                    <input type="hidden" id="instamojo_order_id" name="instamojo_order_id" value="" />
                    <input type="hidden" id="paypal_order_id" name="paypal_order_id" value="" />
                    <input type="hidden" id="phonepe_transaction_id" name="phonepe_transaction_id" value="" />

                    <!-- Delivery Method for Pickup/Doorstep -->
                    <?php if ((isset($cart[0]['type']) && $cart[0]['type'] != 'digital_product') || !isset($cart[0]['type'])): ?>
                        <div class="checkout-section">
                            <h4 class="section-title">
                                <i class="fa fa-truck"></i> Delivery Method
                            </h4>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="door_step" value="door_step" checked>
                                <label class="form-check-label" for="door_step">
                                    Door Step Delivery
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="pickup_from_store" value="pickup_from_store">
                                <label class="form-check-label" for="pickup_from_store">
                                    Pick up from Store
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Address Selection Section -->
                    <div class="checkout-section address">
                        <h4 class="section-title">
                            <i class="fa fa-map-marker"></i> Delivery Address
                        </h4>

                        <div id="address-selection" class="address-list mb-4">
                            <?php if (!empty($this->data['default_address'])): ?>
                                <?php foreach ($this->data['default_address'] as $address): ?>
                                    <div class="address-option mb-3">
                                        <label class="address-card" for="address_<?php echo $address['id']; ?>">
                                            <input type="radio" name="delivery_address"
                                                   id="address_<?php echo $address['id']; ?>"
                                                   value="<?php echo $address['id']; ?>"
                                                   class="address-radio"
                                                   data-zipcode="<?php echo $address['pincode']; ?>"
                                                   <?php echo ($address['is_default'] == 1) ? 'checked' : ''; ?>>
                                            <div class="address-content">
                                                <strong><?php echo ucfirst($address['address_type']); ?></strong>
                                                <p><?php echo $address['address']; ?></p>
                                                <small class="text-muted"><?php echo $address['city']; ?> - <?php echo $address['pincode']; ?></small>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Add New Address Button -->
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fa fa-plus"></i> Add New Address
                        </button>
                    </div>

                    <!-- Delivery Options Section (Shipping Company / Delivery Boy) -->
                    <div class="checkout-section delivery_charge" id="delivery-section" style="display: none;">
                        <h4 class="section-title">
                            <i class="fa fa-truck"></i> Delivery Method
                        </h4>

                        <div id="delivery-loading" class="text-center" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading delivery options...</p>
                        </div>

                        <div id="delivery-options-container" class="delivery-options-list">
                            <!-- Delivery options will be populated here -->
                        </div>

                        <div id="no-delivery-message" class="alert alert-danger" style="display: none;">
                            <!-- Error message will be shown here -->
                        </div>
                    </div>

                    <!-- Time Slots Section (if enabled) -->
                    <?php if ($this->data['time_slot_config']['is_enable'] == 1 && (isset($cart[0]['type']) && $cart[0]['type'] != 'digital_product' || !isset($cart[0]['type']))): ?>
                        <div class="checkout-section date-time-section">
                            <h4 class="section-title date-time-label">
                                <i class="fa fa-calendar"></i> Preferred Delivery Date & Time
                            </h4>

                            <div class="date-time-picker">
                                <input type="text" id="datepicker" class="form-control" placeholder="Select Delivery Date" />
                            </div>

                            <div class="time-slot mt-3">
                                <?php if (!empty($this->data['time_slots'])): ?>
                                    <?php foreach ($this->data['time_slots'] as $slot): ?>
                                        <div class="form-check">
                                            <input class="form-check-input time-slot-inputs" type="radio" name="delivery_time"
                                                   id="time_slot_<?php echo $slot['id']; ?>"
                                                   value="<?php echo $slot['id']; ?>"
                                                   data-last_order_time="<?php echo $slot['last_order_time']; ?>">
                                            <label class="form-check-label" for="time_slot_<?php echo $slot['id']; ?>">
                                                <?php echo $slot['slot_name']; ?> (<?php echo $slot['start_time']; ?> - <?php echo $slot['end_time']; ?>)
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Product Documents Upload (for digital products) -->
                    <?php if (isset($cart[0]['type']) && $cart[0]['type'] == 'digital_product'): ?>
                        <div class="checkout-section">
                            <h4 class="section-title">
                                <i class="fa fa-file"></i> Required Documents
                            </h4>
                            <input type="file" id="documents" name="documents[]" class="form-control" multiple />
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="documents" name="documents[]" value="" />
                    <?php endif; ?>

                    <!-- Order Summary Section -->
                    <div class="checkout-section">
                        <h4 class="section-title">
                            <i class="fa fa-box"></i> Order Summary
                        </h4>

                        <div id="cart-items-summary">
                            <?php if (!empty($cart)): ?>
                                <?php foreach ($cart as $item): ?>
                                    <div class="cart-item-summary mb-3 pb-3 border-bottom">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <p class="mb-1"><strong><?php echo isset($item['product_name']) ? $item['product_name'] : 'Product'; ?></strong></p>
                                                <small class="text-muted">Qty: <?php echo isset($item['quantity']) ? $item['quantity'] : '0'; ?></small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <p class="mb-0"><strong>₹<?php echo number_format(isset($item['sub_total']) ? $item['sub_total'] : 0, 2); ?></strong></p>
                                            </div>
                                        </div>
                                        <input type="hidden" name="product_variant_id" value="<?php echo isset($item['product_variant_id']) ? $item['product_variant_id'] : ''; ?>" />
                                        <input type="hidden" name="quantity" value="<?php echo isset($item['quantity']) ? $item['quantity'] : '0'; ?>" />
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Delivery Charge Display -->
                    <div class="checkout-section all-delivery-charges">
                        <div class="row">
                            <div class="col-6">Delivery Charges:</div>
                            <div class="col-6 text-end">
                                <span class="delivery-charge">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Promo Code Section -->
                    <div class="checkout-section">
                        <h4 class="section-title">
                            <i class="fa fa-tag"></i> Promo Code
                        </h4>

                        <div class="input-group">
                            <input type="text" class="form-control" id="promocode_input" placeholder="Enter promo code" />
                            <button class="btn btn-outline-primary" type="button" id="redeem_btn">Redeem</button>
                            <button class="btn btn-outline-danger d-none" type="button" id="clear_promo_btn">Clear</button>
                        </div>

                        <div id="promocode_div" class="alert alert-success mt-3 d-none">
                            <small>Promo Code <span id="promocode"></span> Applied!</small>
                            <p class="mb-0">Discount: ₹<span id="promocode_amount">0.00</span></p>
                        </div>
                    </div>

                    <!-- Wallet Balance Section -->
                    <div class="checkout-section">
                        <h4 class="section-title">
                            <i class="fa fa-wallet"></i> Wallet Balance
                        </h4>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="wallet_balance" name="wallet_used" value="1">
                            <label class="form-check-label" for="wallet_balance">
                                Use Wallet Balance (Available: ₹<span class="wallet-balance-amount"><?php echo isset($this->data['wallet_balance'][0]['balance']) ? number_format($this->data['wallet_balance'][0]['balance'], 2) : '0.00'; ?></span>)
                            </label>
                        </div>

                        <div class="wallet-section d-none mt-3">
                            <small class="text-muted">Amount Used: ₹<span class="wallet_used">0.00</span></small>
                            <small class="text-muted d-block">Balance After: ₹<span id="available_balance"><?php echo isset($this->data['wallet_balance'][0]['balance']) ? number_format($this->data['wallet_balance'][0]['balance'], 2) : '0.00'; ?></span></small>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="checkout-section">
                        <h4 class="section-title">
                            <i class="fa fa-credit-card"></i> Payment Method
                        </h4>

                        <div class="payment-methods">
                            <?php if (!empty($this->data['payment_methods'])): ?>
                                <?php foreach ($this->data['payment_methods'] as $method): ?>
                                    <?php if ($method['status'] == 1): ?>
                                        <label class="payment-option mb-3">
                                            <input type="radio" name="payment_method"
                                                   id="<?php echo strtolower($method['name']); ?>"
                                                   value="<?php echo $method['name']; ?>"
                                                   class="payment-radio"
                                                   <?php echo ($method['id'] == 1) ? 'checked' : ''; ?>>
                                            <span class="payment-name"><?php echo $method['name']; ?></span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stripe Card Element -->
                    <div class="checkout-section" id="stripe_div" style="display: none;">
                        <h4 class="section-title">
                            <i class="fa fa-lock"></i> Card Details
                        </h4>
                        <div id="stripe-card-element" class="form-control"></div>
                        <div id="card-error" class="alert alert-danger mt-2" style="display: none;"></div>
                    </div>

                    <!-- Bank Transfer Section -->
                    <div class="checkout-section" id="bank_transfer_slide" style="display: none;">
                        <h4 class="section-title">
                            <i class="fa fa-university"></i> Bank Transfer Details
                        </h4>
                        <div id="account_data" style="display: none;">
                            <?php
                            $bank_details = get_settings('payment_method', true);
                            if (isset($bank_details['bank_account_holder']) && !empty($bank_details['bank_account_holder'])):
                            ?>
                                <div class="alert alert-info">
                                    <strong>Account Holder:</strong> <?php echo $bank_details['bank_account_holder']; ?><br>
                                    <strong>Account Number:</strong> <?php echo $bank_details['bank_account_number']; ?><br>
                                    <strong>Bank Name:</strong> <?php echo $bank_details['bank_name']; ?><br>
                                    <strong>IFSC Code:</strong> <?php echo $bank_details['bank_ifsc_code']; ?><br>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Upload Bank Transfer Proof</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple />
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Notes Section -->
                    <div class="checkout-section">
                        <h4 class="section-title">
                            <i class="fa fa-comment"></i> Special Notes
                        </h4>
                        <textarea class="form-control" name="order_note" id="order_note" rows="3" placeholder="Add any special instructions for your order..."></textarea>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="checkout-section">
                        <label class="form-check">
                            <input type="checkbox" id="agree_terms" name="agree_terms" class="form-check-input" required>
                            <span class="form-check-label">I agree to the terms and conditions</span>
                        </label>
                    </div>

                    <!-- Place Order Button -->
                    <div class="checkout-section">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="place_order_btn">
                            Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="col-lg-4">
            <div class="order-summary-card sticky-top">
                <h5 class="card-title">Order Summary</h5>

                <div class="summary-details mb-3">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<span id="subtotal-amount"><?php echo number_format($cart['sub_total'], 2); ?></span></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>₹<span id="shipping-amount">0.00</span></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>₹<span id="tax-amount"><?php echo isset($cart['tax_amount']) ? number_format($cart['tax_amount'], 2) : '0.00'; ?></span></span>
                    </div>
                    <div class="summary-row">
                        <span>Promo Discount:</span>
                        <span>₹<span id="promo-discount-amount">0.00</span></span>
                    </div>
                    <div class="summary-row">
                        <span>Wallet Used:</span>
                        <span>-₹<span class="wallet_used">0.00</span></span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>₹<span id="final_total"><?php echo number_format($cart['overall_amount'], 2); ?></span></span>
                    </div>
                </div>

                <div id="order-info-card" class="info-card">
                    <div class="info-item">
                        <small class="text-muted">Selected Address</small>
                        <div id="address-name-type"></div>
                        <div id="address-full" class="small"></div>
                        <div id="address-country" class="small"></div>
                        <div id="address-mobile" class="small"></div>
                    </div>
                    <div class="info-item mt-3">
                        <small class="text-muted">Delivery Method</small>
                        <p id="selected-delivery" class="mb-0 small">Not selected</p>
                    </div>
                    <div class="info-item mt-3">
                        <small class="text-muted">Payment Method</small>
                        <p id="selected-payment" class="mb-0 small">Not selected</p>
                    </div>
                    <div class="info-item mt-3">
                        <small class="text-muted">Estimate Delivery</small>
                        <p class="mb-0 small estimate_date"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="new-address-form">
                    <div class="mb-3">
                        <label class="form-label">Address Type</label>
                        <select class="form-control" name="address_type" required>
                            <option value="">Select Type</option>
                            <option value="home">Home</option>
                            <option value="office">Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Address</label>
                        <textarea class="form-control" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" class="form-control" name="pincode" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Address</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .checkout-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }

    .section-title {
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .address-option {
        margin-bottom: 10px;
    }

    .address-card {
        display: block;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .address-card:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }

    .address-card input[type="radio"]:checked + .address-content {
        color: #007bff;
        font-weight: 600;
    }

    .address-content p {
        margin: 5px 0;
        font-size: 14px;
    }

    .delivery-options-list {
        display: grid;
        gap: 12px;
    }

    .delivery-option {
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .delivery-option:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }

    .delivery-option input[type="radio"]:checked + .delivery-content {
        color: #007bff;
        font-weight: 600;
    }

    .delivery-option input[type="radio"]:checked ~ .check-mark {
        opacity: 1;
    }

    .delivery-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .delivery-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .delivery-info {
        flex: 1;
    }

    .delivery-name {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .delivery-details {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: #666;
        margin-top: 8px;
    }

    .delivery-price {
        font-size: 18px;
        font-weight: 700;
        color: #28a745;
        text-align: right;
        min-width: 80px;
    }

    .delivery-type-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 5px;
    }

    .check-mark {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 24px;
        height: 24px;
        background: #007bff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .payment-option {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }

    .payment-option:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }

    .payment-option input[type="radio"]:checked + .payment-name {
        color: #007bff;
        font-weight: 600;
    }

    .payment-option input[type="radio"] {
        margin-right: 10px;
    }

    .payment-name {
        margin: 0;
    }

    .order-summary-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        top: 80px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .summary-row.total {
        font-size: 18px;
        font-weight: 700;
        color: #333;
    }

    .info-card {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-top: 15px;
    }

    .info-item {
        padding: 10px 0;
    }

    .cart-item-summary {
        padding: 12px 0;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-check-input {
        margin-top: 0;
    }

    @media (max-width: 768px) {
        .order-summary-card {
            position: static;
            margin-top: 20px;
        }

        .delivery-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .delivery-price {
            align-self: flex-end;
            margin-top: 10px;
        }

        .checkout-section {
            padding: 15px;
        }
    }
</style>

<?php $this->load->view('front-end/' . THEME . '/paypal_form'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

<?php if ($this->data['auth_settings']['razorpay_key_id']): ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php endif; ?>

<?php if ($this->data['auth_settings']['stripe_key_id']): ?>
    <script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<?php if ($this->data['auth_settings']['paystack_key_id']): ?>
    <script src="https://js.paystack.co/v1/inline.js"></script>
<?php endif; ?>

<?php if (isset($this->data['auth_settings']['flutterwave_public_key'])): ?>
    <script src="https://checkout.flutterwave.com/v3.js"></script>
<?php endif; ?>

<?php if (isset($this->data['auth_settings']['instamojo_key_id'])): ?>
    <script src="https://www.instamojo.com/@js/iframe.js"></script>
<?php endif; ?>

<?php if (isset($this->data['auth_settings']['midtrans_client_key'])): ?>
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?php echo $this->data['auth_settings']['midtrans_client_key']; ?>"></script>
<?php endif; ?>

<?php if (isset($this->data['auth_settings']['phonepe_app_id'])): ?>
    <script src="https://web.phonepe.com/v1/lib/js/checkout.js"></script>
<?php endif; ?>

<script src="<?php echo base_url('assets/front_end/' . THEME . '/js/checkout.js'); ?>"></script>
