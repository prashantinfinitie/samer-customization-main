<!-- e5aef895-5f2b-4dcf-9f52-3cabb2445e02 81ebe147-11c4-4a67-b4af-7471d0c90170 -->
# Shipping Company Order Tracking & Cancellation Implementation Plan

## Overview

This plan implements order tracking for shipping company orders (which don't use consignments) and adds proper cancellation handling when customers cancel orders assigned to shipping companies.

## Current State Analysis

### Existing Implementation:

- Shipping companies update order status via `update_order_status` API
- Status history is stored in `order_items.status` as JSON: `[["received", "timestamp"], ["processed", "timestamp"], ...]`
- `order_items.active_status` stores current status
- Orders have `shipping_company_id` in `orders` table
- Order items have `shipping_company_id` in `order_items` table
- No consignments for shipping company orders (unlike delivery boy orders)

### Missing Features:

1. **Order Tracking APIs**: No endpoints for customers/admins to view shipping company order tracking
2. **Status History Display**: Status history exists but not exposed via APIs
3. **Cancellation Flow**: Customer cancellation doesn't notify shipping company
4. **Tracking UI**: No tracking display for shipping company orders in admin/customer panels

## Implementation Plan

### Phase 1: Order Tracking APIs

#### 1.1 Customer API - Get Order Tracking

**File**: `application/controllers/app/v1/Api.php`

- **New Method**: `get_order_tracking()`
- **Purpose**: Return order status history for customer's shipping company orders
- **Parameters**: `order_id` (required)
- **Response**: 
  - Order details with shipping company info
  - Status history from `order_items.status` JSON
  - Current status from `order_items.active_status`
  - Shipping company name and contact info
- **Logic**: 
  - Verify order belongs to logged-in customer
  - Check if order has `shipping_company_id`
  - Parse `order_items.status` JSON to return formatted history
  - Include shipping company details from `users` table

#### 1.2 Admin API - Get Shipping Company Order Tracking

**File**: `application/controllers/admin/app/v1/Api.php`

- **New Method**: `get_shipping_company_order_tracking()`
- **Purpose**: Admin view of shipping company order tracking
- **Parameters**: `order_id` (required), `shipping_company_id` (optional filter)
- **Response**: 
  - Order details
  - All order items with status history
  - Shipping company assignment info
  - Status timeline for each item
- **Logic**:
  - Filter by `orders.shipping_company_id` if provided
  - Return all order items with parsed status history
  - Include customer and shipping company details

#### 1.3 Shipping Company API - Get Order Tracking (Enhancement)

**File**: `application/controllers/shipping_company/app/v1/Api.php`

- **Enhance**: `get_orders()` method
- **Add**: Status history parsing in response
- **Purpose**: Shipping company can see full status history for their orders
- **Changes**: 
  - Parse `order_items.status` JSON when returning order details
  - Include formatted status timeline in response

### Phase 2: Status History Helper Functions

#### 2.1 Create Status History Parser

**File**: `application/helpers/function_helper.php`

- **New Function**: `parse_order_status_history($status_json)`
- **Purpose**: Parse JSON status array into formatted timeline
- **Input**: JSON string from `order_items.status`
- **Output**: Array of status entries with:
  - Status name
  - Timestamp
  - Formatted date/time
  - Status label (human-readable)
- **Handle**: Double-encoded JSON, empty/null values, malformed data

#### 2.2 Create Status Timeline Formatter

**File**: `application/helpers/function_helper.php`

- **New Function**: `format_order_tracking_timeline($order_items)`
- **Purpose**: Format order items with status history for display
- **Input**: Array of order items
- **Output**: Formatted array with status timeline for each item

### Phase 3: Cancellation Flow Implementation

#### 3.1 Customer Cancellation Handler

**File**: `application/controllers/app/v1/Api.php` or `application/controllers/My_account.php`

- **Enhance**: Existing cancellation method
- **Add Logic**: 
  - Check if order has `shipping_company_id`
  - If yes, notify shipping company before/after cancellation
  - Update order status to 'cancelled'
  - Handle refunds (existing logic)
  - Send notification to shipping company

#### 3.2 Shipping Company Cancellation Notification

**File**: `application/controllers/shipping_company/app/v1/Api.php`

- **Enhance**: `update_order_status()` method
- **Add**: Handle cancellation initiated by customer
- **Logic**:
  - When status becomes 'cancelled', check if initiated by customer
  - Update shipping company's order list
  - Handle COD refund if order was already in transit

#### 3.3 Admin Cancellation Handler

**File**: `application/controllers/admin/Orders.php` or `application/controllers/admin/app/v1/Api.php`

- **Enhance**: Existing cancellation method
- **Add**: 
  - Check for shipping company assignment
  - Notify shipping company when admin cancels
  - Update shipping company statistics

#### 3.4 Cancellation Notification Helper

**File**: `application/helpers/function_helper.php`

- **New Function**: `notify_shipping_company_cancellation($order_id, $reason = '')`
- **Purpose**: Send notification to shipping company when order is cancelled
- **Logic**:
  - Get shipping company FCM ID
  - Send push notification
  - Create notification record
  - Update shipping company order counts

### Phase 4: Database Considerations

#### 4.1 Status History Storage (Already Exists)

- `order_items.status` - JSON array of status history
- `order_items.active_status` - Current status
- No new tables needed

#### 4.2 Indexes (Verify)

- Ensure indexes exist on:
  - `orders.shipping_company_id`
  - `order_items.shipping_company_id`
  - `order_items.order_id`
  - `order_items.active_status`

### Phase 5: Frontend/View Updates

#### 5.1 Customer Order Details View

**File**: `application/views/front-end/modern/pages/order-details.php`

- **Add**: Shipping company order tracking display
- **Show**: 
  - Status timeline if order has shipping company
  - Shipping company name and contact
  - Current status with visual timeline
  - Status history with timestamps

#### 5.2 Admin Order Details View

**File**: `application/views/admin/pages/forms/edit-orders.php`

- **Add**: Shipping company tracking section
- **Show**:
  - Order assignment to shipping company
  - Status history for all items
  - Shipping company contact info
  - Timeline visualization

### Phase 6: API Documentation Updates

#### 6.1 Update Customer API Documentation

**File**: `CUSTOMER_API_DOCUMENTATION.md`

- **Add**: `get_order_tracking` endpoint documentation
- Include request/response examples
- Document status history format

#### 6.2 Update Shipping Company API Documentation

**File**: `SHIPPING_COMPANY_API_DOCUMENTATION.md`

- **Update**: `get_orders` to show status history in response
- **Add**: Cancellation handling notes

#### 6.3 Update Admin API Documentation

**File**: `admin-api-doc.txt`

- **Add**: `get_shipping_company_order_tracking` endpoint

## Implementation Details

### Status History Format

```json
{
  "status_history": [
    {
      "status": "received",
      "label": "Order Received",
      "timestamp": "2025-01-15 10:30:00",
      "formatted_date": "15-Jan-2025 10:30 AM"
    },
    {
      "status": "processed",
      "label": "Order Processed",
      "timestamp": "2025-01-15 11:00:00",
      "formatted_date": "15-Jan-2025 11:00 AM"
    }
  ],
  "current_status": "processed",
  "shipping_company": {
    "id": 104,
    "name": "Fast Delivery Co",
    "mobile": "1234567890",
    "email": "fast@delivery.com"
  }
}
```

### Cancellation Flow

1. Customer/Admin initiates cancellation
2. System checks if order has `shipping_company_id`
3. If yes:

   - Check current status (can't cancel if already delivered)
   - Notify shipping company via FCM/notification
   - Update order status to 'cancelled'
   - Process refunds (existing logic)
   - Update inventory (existing logic)
   - Update shipping company statistics

4. If no shipping company, use existing cancellation flow

## Testing Checklist

1. ✅ Customer can view tracking for shipping company orders
2. ✅ Admin can view tracking for all shipping company orders
3. ✅ Shipping company sees status history in their order list
4. ✅ Customer cancellation notifies shipping company
5. ✅ Admin cancellation notifies shipping company
6. ✅ Status history is correctly parsed from JSON
7. ✅ Cancellation prevents status updates after cancellation
8. ✅ Refunds work correctly for cancelled shipping company orders
9. ✅ COD handling works for cancelled orders
10. ✅ Status timeline displays correctly in views
11. ⚠️ Delivery charge displays correctly in shipping company edit order view
12. ⚠️ Order tracking timeline displays in shipping company edit order view
13. ⚠️ Order tracking timeline displays in admin edit order view

## Files to Modify/Create

### Controllers:

- `application/controllers/app/v1/Api.php` - Add `get_order_tracking()`
- `application/controllers/admin/app/v1/Api.php` - Add `get_shipping_company_order_tracking()`
- `application/controllers/shipping_company/app/v1/Api.php` - Enhance `get_orders()`
- `application/controllers/My_account.php` - Enhance cancellation
- `application/controllers/admin/Orders.php` - Enhance cancellation

### Helpers:

- `application/helpers/function_helper.php` - Add status parsing functions

### Views:

- `application/views/front-end/modern/pages/order-details.php` - Add tracking display
- `application/views/admin/pages/forms/edit-orders.php` - Add tracking section

### Documentation:

- `CUSTOMER_API_DOCUMENTATION.md` - Update
- `SHIPPING_COMPANY_API_DOCUMENTATION.md` - Update
- `admin-api-doc.txt` - Update

## Notes

- Shipping company orders don't use consignments, so tracking is based on `order` and `order_item` status
- Status history is already being stored in `order_items.status` JSON field
- Need to parse and format this JSON for display
- Cancellation must respect order status (can't cancel delivered orders)
- Shipping company should be notified of cancellations to update their workflow

### To-dos

- [ ] Create customer API endpoint get_order_tracking() to return shipping company order status history
- [ ] Create admin API endpoint get_shipping_company_order_tracking() for admin order tracking view
- [ ] Enhance shipping company get_orders() API to include parsed status history in response
- [ ] Create parse_order_status_history() helper function to parse JSON status array into formatted timeline
- [ ] Create format_order_tracking_timeline() helper function to format order items with status history
- [ ] Enhance customer cancellation to notify shipping company when order is cancelled
- [ ] Enhance admin cancellation to notify shipping company when order is cancelled
- [ ] Create notify_shipping_company_cancellation() helper function to send notifications
- [ ] Update customer order details view to display shipping company order tracking timeline
- [ ] Update admin order details view to display shipping company tracking information
- [ ] Update API documentation files with new tracking endpoints and cancellation flow