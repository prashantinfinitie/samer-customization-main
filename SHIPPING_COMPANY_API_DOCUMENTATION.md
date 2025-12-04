# Shipping Company API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URLs](#base-urls)
4. [Admin Panel APIs](#admin-panel-apis)
5. [Shipping Company Panel APIs](#shipping-company-panel-apis)
6. [Customer/Web APIs](#customerweb-apis)
7. [Common Response Format](#common-response-format)
8. [Error Codes](#error-codes)

---

## Overview

This documentation covers all shipping company related APIs across three panels:
- **Admin Panel**: APIs for managing shipping companies, assigning orders, fund transfers, and cash collection
- **Shipping Company Panel**: APIs for shipping companies to manage orders, view statistics, and handle transactions
- **Customer/Web Panel**: APIs for customers to get shipping quotes and place orders with shipping companies

---

## Authentication

All APIs (except login/register endpoints) require JWT Bearer token authentication.

### Getting a Token

**Admin Panel:**
- Login via `admin/app/v1/api/login` to get a token
- Token is returned in the response

**Shipping Company Panel:**
- Login via `shipping_company/app/v1/api/login` to get a token
- Token is returned in the response

**Customer/Web Panel:**
- Login via `app/v1/api/login` to get a token
- Token is returned in the response

### Using the Token

Include the token in the Authorization header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

---

## Base URLs

- **Admin Panel**: `http://localhost/samer-customisation-main/admin/app/v1/api/`
- **Shipping Company Panel**: `http://localhost/samer-customisation-main/shipping_company/app/v1/api/`
- **Customer/Web Panel**: `http://localhost/samer-customisation-main/app/v1/api/`

---

## Admin Panel APIs

### 1. Get Shipping Companies

**Endpoint:** `get_shipping_companies`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | No | Specific company ID |
| search | string | No | Search keyword (username, email, mobile, address) |
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| sort | string | No | Sort field (default: 'u.id') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'DESC') |
| status | integer | No | Filter by status: 0=pending, 1=approved |

**Response:**
```json
{
  "error": false,
  "message": "Shipping companies retrieved successfully",
  "total": "10",
  "data": [
    {
      "id": "104",
      "username": "Fast Delivery Co",
      "email": "fast@delivery.com",
      "mobile": "1234567890",
      "address": "123 Main St",
      "status": "1",
      "balance": "5000.00",
      "cash_received": "2500.00",
      "serviceable_zipcodes_list": [
        {"id": "1", "zipcode": "12345"}
      ],
      "serviceable_cities_list": [
        {"id": "1", "name": "New York"}
      ],
      "image": "http://localhost/samer-customisation-main/uploads/users/..."
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/get_shipping_companies" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0&status=1"
```

---

### 2. Update Shipping Company Status

**Endpoint:** `update_shipping_company_status`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| company_id | integer | Yes | Shipping company ID |
| status | integer | Yes | Status: 0=pending, 1=approved |

**Response:**
```json
{
  "error": false,
  "message": "Shipping company approved",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/update_shipping_company_status" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "company_id=104&status=1"
```

---

### 3. Assign Shipping Company to Order

**Endpoint:** `assign_shipping_company`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | integer | Yes | Order ID |
| shipping_company_id | integer | Yes | Shipping company ID |

**Response:**
```json
{
  "error": false,
  "message": "Shipping company assigned successfully",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/assign_shipping_company" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "order_id=123&shipping_company_id=104"
```

---

### 4. Collect Cash from Shipping Company

**Endpoint:** `collect_shipping_company_cash`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Description:** Collects COD cash collected by shipping company from admin

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | Yes | Shipping company ID |
| amount | float | Yes | Amount to collect (must be > 0) |
| message | string | No | Optional message (default: "Cash collected by admin") |

**Response:**
```json
{
  "error": false,
  "message": "Cash collected successfully",
  "data": {
    "new_cash_balance": "1500.00"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/collect_shipping_company_cash" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104&amount=1000.00&message=Cash collection for week 1"
```

---

### 5. Transfer Funds to Shipping Company

**Endpoint:** `transfer_to_shipping_company`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Description:** Transfers funds from admin to shipping company (for prepaid orders)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | Yes | Shipping company ID |
| amount | float | Yes | Amount to transfer (must be > 0) |
| message | string | No | Optional message (default: "Fund transfer from admin") |

**Response:**
```json
{
  "error": false,
  "message": "Fund transferred successfully",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/transfer_to_shipping_company" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104&amount=5000.00&message=Payout for delivered orders"
```

---

### 6. Get Shipping Company Payout Info

**Endpoint:** `get_shipping_company_payout_info`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Description:** Gets pending payout information for a shipping company (prepaid orders)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | Yes | Shipping company ID |

**Response:**
```json
{
  "error": false,
  "message": "Payout info retrieved successfully",
  "data": {
    "company_name": "Fast Delivery Co",
    "current_balance": "5000.00",
    "cash_in_hand": "2500.00",
    "total_earnings": "15000.00",
    "total_paid": "10000.00",
    "pending_amount": "5000.00",
    "order_count": "25"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/get_shipping_company_payout_info" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104"
```

---

### 7. Get Shipping Company Cash Collection History

**Endpoint:** `get_shipping_company_cash_collection`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | No | Filter by company (all if not provided) |
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |

**Response:**
```json
{
  "error": false,
  "message": "Cash collection history retrieved",
  "total": "50",
  "data": [
    {
      "id": "1",
      "user_id": "104",
      "username": "Fast Delivery Co",
      "mobile": "1234567890",
      "order_id": "123",
      "amount": "1000.00",
      "type": "shipping_company_cash",
      "type_label": "Received by Company",
      "message": "COD collected by shipping company for order 123",
      "transaction_date": "2025-01-15 10:30:00"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/get_shipping_company_cash_collection" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104&limit=25&offset=0"
```

---

### 8. Get Shipping Company Fund Transfers

**Endpoint:** `get_shipping_company_fund_transfers`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | No | Filter by company (all if not provided) |
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |

**Response:**
```json
{
  "error": false,
  "message": "Fund transfers retrieved",
  "total": "30",
  "data": [
    {
      "id": "1",
      "shipping_company_id": "104",
      "opening_balance": "10000.00",
      "closing_balance": "5000.00",
      "amount": "5000.00",
      "status": "success",
      "message": "Payout for delivered orders"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/get_shipping_company_fund_transfers" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104&limit=25&offset=0"
```

---

### 9. Get Shipping Company Orders

**Endpoint:** `get_shipping_company_orders`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| shipping_company_id | integer | No | Filter by company (all if not provided) |
| status | string | No | Filter by order status |
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |

**Response:**
```json
{
  "error": false,
  "message": "Orders retrieved",
  "total": "100",
  "data": [
    {
      "id": "123",
      "user_id": "5",
      "customer_name": "John Doe",
      "customer_mobile": "9876543210",
      "shipping_company_name": "Fast Delivery Co",
      "shipping_company_mobile": "1234567890",
      "address": "123 Main St",
      "address_city": "New York",
      "address_pincode": "12345",
      "total": "500.00",
      "delivery_charge": "50.00",
      "final_total": "550.00",
      "payment_method": "COD",
      "active_status": "received"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/admin/app/v1/api/get_shipping_company_orders" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "shipping_company_id=104&status=received&limit=25&offset=0"
```

---

## Shipping Company Panel APIs

### 1. Login

**Endpoint:** `login`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile | string | Yes* | Mobile number (if identity_column = 'mobile') |
| email | string | Yes* | Email (if identity_column = 'email') |
| password | string | Yes | Password |
| fcm_id | string | No | FCM token for push notifications |

*Depends on system configuration

**Response:**
```json
{
  "error": false,
  "message": "Logged in successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "data": [
    {
      "id": "104",
      "username": "Fast Delivery Co",
      "email": "fast@delivery.com",
      "mobile": "1234567890",
      "status": "1",
      "balance": "5000.00",
      "cash_received": "2500.00"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile=1234567890&password=yourpassword&fcm_id=your_fcm_token"
```

---

### 2. Register

**Endpoint:** `register`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| company_name | string | Yes | Company name |
| mobile | string | Yes | Mobile number (unique) |
| email | string | Yes | Email address (unique) |
| password | string | Yes | Password |
| confirm_password | string | Yes | Must match password |
| address | string | Yes | Company address |
| serviceable_zipcodes | array | No | Array of zipcode IDs |
| serviceable_cities | array | No | Array of city IDs |
| kyc_documents | file[] | No | KYC document files (multiple) |

**Response:**
```json
{
  "error": false,
  "message": "Shipping Company registered successfully. Please wait for admin approval."
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/register" \
  -H "Content-Type: multipart/form-data" \
  -F "company_name=Fast Delivery Co" \
  -F "mobile=1234567890" \
  -F "email=fast@delivery.com" \
  -F "password=securepassword" \
  -F "confirm_password=securepassword" \
  -F "address=123 Main St" \
  -F "serviceable_zipcodes[]=1" \
  -F "serviceable_zipcodes[]=2"
```

---

### 3. Get Shipping Company Details

**Endpoint:** `get_shipping_company_details`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:** None (uses token to identify user)

**Response:**
```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": "104",
      "username": "Fast Delivery Co",
      "email": "fast@delivery.com",
      "mobile": "1234567890",
      "address": "123 Main St",
      "balance": "5000.00",
      "cash_received": "2500.00",
      "status": "1",
      "kyc_documents": [
        "http://localhost/samer-customisation-main/uploads/shipping_company/doc1.pdf"
      ],
      "serviceable_zipcodes": "1,2,3",
      "serviceable_cities": "1,2"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_shipping_company_details" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

---

### 4. Get Orders

**Endpoint:** `get_orders`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| active_status | string | No | Filter by status (awaiting, received, processed, shipped, delivered, cancelled, returned) |
| order_id | integer | No | Get specific order |
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| sort | string | No | Sort field (default: 'o.id') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'DESC') |

**Response:**
```json
{
  "error": false,
  "message": "Orders retrieved successfully",
  "total": "50",
  "awaiting": "5",
  "received": "10",
  "processed": "8",
  "shipped": "12",
  "delivered": "10",
  "cancelled": "3",
  "returned": "2",
  "data": [
    {
      "id": "123",
      "user_id": "5",
      "customer_name": "John Doe",
      "customer_mobile": "9876543210",
      "customer_email": "john@example.com",
      "address": "123 Main St",
      "landmark": "Near Park",
      "pincode": "12345",
      "city": "New York",
      "total": "500.00",
      "delivery_charge": "50.00",
      "final_total": "550.00",
      "payment_method": "COD",
      "order_note": "Handle with care",
      "date_added": "2025-01-15 10:00:00",
      "otp": "1234",
      "order_items": [
        {
          "order_item_id": "456",
          "product_name": "Product Name",
          "product_image": "http://localhost/...",
          "quantity": "2",
          "price": "250.00",
          "sub_total": "500.00",
          "status": "received",
          "current_status": "received",
          "status_history": [
            {
              "status": "received",
              "label": "Order Received",
              "timestamp": "15-01-2025 10:30:00am",
              "formatted_date": "15-Jan-2025 10:30 AM"
            }
          ],
          "measurement": "1 kg"
        }
      ]
    }
  ]
}
```

**Note:** The `get_orders` response now includes `status_history` array for each order item, showing the complete timeline of status changes. Each status entry includes:
- `status`: Status code (received, processed, shipped, delivered, etc.)
- `label`: Human-readable status label
- `timestamp`: Original timestamp from status update
- `formatted_date`: Formatted date string for display

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_orders" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "active_status=received&limit=25&offset=0"
```

---

### 5. Update Order Status

**Endpoint:** `update_order_status`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_item_id | integer | Yes | Order item ID |
| status | string | Yes | New status: received, processed, shipped, delivered, cancelled, returned |
| otp | integer | No | OTP for delivery (required if OTP setting is enabled) |

**Response:**
```json
{
  "error": false,
  "message": "Status Updated Successfully",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/update_order_status" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "order_item_id=456&status=delivered&otp=1234"
```

**Note:** When status is set to "delivered" for COD orders, the system automatically:
- Updates `cash_received` for the shipping company
- Creates a transaction record of type `shipping_company_cash`

**Cancellation Handling:**
- When an order is cancelled by customer or admin, the shipping company receives a notification
- Cancelled orders are automatically removed from active order lists
- Status history will show the cancellation entry with timestamp

---

### 6. Get Fund Transfers

**Endpoint:** `get_fund_transfers`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| sort | string | No | Sort field (default: 'id') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'DESC') |

**Response:**
```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "20",
  "data": [
    {
      "id": "1",
      "shipping_company_id": "104",
      "opening_balance": "10000.00",
      "closing_balance": "15000.00",
      "amount": "5000.00",
      "status": "success",
      "message": "Payout for delivered orders"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_fund_transfers" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0"
```

---

### 7. Get Cash Collection

**Endpoint:** `get_cash_collection`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| status | string | No | Filter by type: 'shipping_company_cash' or 'shipping_company_cash_collection' |
| sort | string | No | Sort field (default: 'id') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'DESC') |

**Response:**
```json
{
  "error": false,
  "message": "Cash collection retrieved successfully",
  "total": "30",
  "cash_in_hand": "2500.00",
  "data": [
    {
      "id": "1",
      "order_id": "123",
      "amount": "550.00",
      "type": "shipping_company_cash",
      "type_label": "Received",
      "message": "COD collected by shipping company for order 123",
      "transaction_date": "2025-01-15 10:30:00",
      "cash_received": "2500.00"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_cash_collection" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0&status=shipping_company_cash"
```

---

### 8. Get Statistics

**Endpoint:** `get_statistics`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:** None

**Response:**
```json
{
  "error": false,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_orders": "100",
    "balance": "5000.00",
    "cash_in_hand": "2500.00",
    "today_delivered": "5",
    "awaiting": "5",
    "received": "10",
    "processed": "8",
    "shipped": "12",
    "delivered": "50",
    "cancelled": "3",
    "returned": "2",
    "total_earnings": "15000.00",
    "total_paid": "10000.00",
    "pending_payout": "5000.00"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_statistics" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

---

### 9. Get Payout Summary

**Endpoint:** `get_payout_summary`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:** None

**Response:**
```json
{
  "error": false,
  "message": "Payout summary retrieved successfully",
  "data": {
    "total_earnings": "15000.00",
    "total_paid": "10000.00",
    "pending_amount": "5000.00",
    "order_count": "25"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_payout_summary" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded"
```

---

### 10. Send Withdrawal Request

**Endpoint:** `send_withdrawal_request`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| payment_address | string | Yes | Payment address (bank account, wallet, etc.) |
| amount | float | Yes | Amount to withdraw (must be > 0 and <= balance) |

**Response:**
```json
{
  "error": false,
  "message": "Withdrawal Request Sent Successfully",
  "data": {
    "new_balance": "0.00"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/send_withdrawal_request" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "payment_address=AC123456789&amount=5000.00"
```

---

### 11. Get Withdrawal Requests

**Endpoint:** `get_withdrawal_request`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |

**Response:**
```json
{
  "error": false,
  "message": "Withdrawal Requests Retrieved Successfully",
  "total": "5",
  "data": [
    {
      "id": "1",
      "user_id": "104",
      "payment_address": "AC123456789",
      "payment_type": "shipping_company",
      "amount_requested": "5000.00",
      "status": "pending",
      "created_at": "2025-01-15 10:00:00"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_withdrawal_request" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0"
```

---

### 12. Update User Profile

**Endpoint:** `update_user`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| username | string | No | Company name |
| email | string | No | Email (must be unique) |
| mobile | string | No | Mobile (must be unique) |
| address | string | No | Address |
| image | file | No | Profile image |
| old | string | No* | Old password (required if changing password) |
| new | string | No* | New password (required if changing password) |

*Required together if changing password

**Response:**
```json
{
  "error": false,
  "message": "Profile Updated Successfully"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/update_user" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: multipart/form-data" \
  -F "username=Updated Company Name" \
  -F "address=456 New St"
```

---

### 13. Update FCM Token

**Endpoint:** `update_fcm`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| fcm_id | string | Yes | FCM token |
| device_type | string | No | Device type (android/ios) |

**Response:**
```json
{
  "error": false,
  "message": "FCM Updated Successfully"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/update_fcm" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "fcm_id=your_fcm_token&device_type=android"
```

---

### 14. Reset Password

**Endpoint:** `reset_password`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile_no | string | Yes | Mobile number |
| new | string | Yes | New password |

**Response:**
```json
{
  "error": false,
  "message": "Password Reset Successfully",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/reset_password" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile_no=1234567890&new=newpassword123"
```

---

### 15. Get Notifications

**Endpoint:** `get_notifications`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| sort | string | No | Sort field (default: 'id') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'DESC') |

**Response:**
```json
{
  "error": false,
  "message": "Notifications Retrieved Successfully",
  "total": "10",
  "data": [
    {
      "id": "1",
      "title": "New Order Assigned",
      "message": "Order #123 has been assigned to you",
      "type": "order_assigned",
      "date_created": "2025-01-15 10:00:00"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_notifications" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0"
```

---

### 16. Verify User (OTP)

**Endpoint:** `verify_user`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile | string | Yes | Mobile number |
| country_code | string | No | Country code |
| is_forgot_password | integer | No | 1 if for password reset (default: 0) |

**Response:**
```json
{
  "error": false,
  "message": "OTP sent successfully!"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/verify_user" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile=1234567890&country_code=+1"
```

---

### 17. Verify OTP

**Endpoint:** `verify_otp`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile | string | Yes | Mobile number |
| otp | string | Yes | OTP code |

**Response:**
```json
{
  "error": false,
  "message": "OTP Verified Successfully",
  "data": []
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/verify_otp" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile=1234567890&otp=123456"
```

---

### 18. Resend OTP

**Endpoint:** `resend_otp`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile | string | Yes | Mobile number |
| country_code | string | No | Country code |

**Response:**
```json
{
  "error": false,
  "message": "OTP sent successfully"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/resend_otp" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile=1234567890&country_code=+1"
```

---

### 19. Get Settings

**Endpoint:** `get_settings`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | Yes | Setting type: 'shipping_company_terms_conditions', 'shipping_company_privacy_policy', 'terms_conditions', 'privacy_policy', 'currency', 'authentication_settings', 'shipping_method' |

**Response:**
```json
{
  "error": false,
  "message": "Settings retrieved successfully",
  "data": {
    "terms_conditions": "Terms and conditions text..."
  },
  "currency": {
    "currency": "USD",
    "currency_symbol": "$"
  },
  "system_settings": {
    "app_name": "eShop"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_settings" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "type=shipping_company_terms_conditions"
```

---

### 20. Get Zipcodes

**Endpoint:** `get_zipcodes`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 25) |
| offset | integer | No | Pagination offset (default: 0) |
| search | string | No | Search keyword |

**Response:**
```json
{
  "error": false,
  "message": "Zipcodes Retrieved Successfully",
  "total": "100",
  "data": [
    {
      "id": "1",
      "zipcode": "12345",
      "city_id": "1",
      "city_name": "New York"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_zipcodes" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=25&offset=0&search=123"
```

---

### 21. Get Cities

**Endpoint:** `get_cities`

**Method:** POST

**Authentication:** Not Required

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of records (default: 10) |
| offset | integer | No | Pagination offset (default: 0) |
| search | string | No | Search keyword |
| sort | string | No | Sort field (default: 'c.name') |
| order | string | No | Sort order: 'ASC' or 'DESC' (default: 'ASC') |

**Response:**
```json
{
  "error": false,
  "message": "Cities Retrieved Successfully",
  "total": "50",
  "data": [
    {
      "id": "1",
      "name": "New York",
      "state_id": "1"
    }
  ]
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/get_cities" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "limit=10&offset=0&search=New"
```

---

### 22. Delete Shipping Company Account

**Endpoint:** `delete_shipping_company`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| mobile | string | Yes | Mobile number |
| password | string | Yes | Password for verification |

**Response:**
```json
{
  "error": false,
  "message": "Account Deleted Successfully"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/shipping_company/app/v1/api/delete_shipping_company" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "mobile=1234567890&password=yourpassword"
```

**Note:** Account can only be deleted if there are no pending orders (orders not in delivered/cancelled/returned status).

---

## Customer/Web APIs

### 1. Get Delivery Info

**Endpoint:** `get_delivery_info`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Description:** Returns delivery information based on address. For shipping companies, returns available quotes. For delivery boys, returns standard delivery charges.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| address_id | integer | Yes | Customer's delivery address ID |

**Response (Shipping Company - provider_type = 'company'):**
```json
{
  "error": false,
  "message": "Shipping quotes retrieved successfully.",
  "delivery_available": true,
  "provider_type": "company",
  "quotes": [
    {
      "id": "6",
      "shipping_company_id": "104",
      "company_name": "Fast Delivery Co",
      "company_email": "fast@delivery.com",
      "company_phone": "1234567890",
      "price": "100.00",
      "cod_available": 1,
      "estimated_days": "5 Days",
      "additional_charges": {
        "GST": "100"
      },
      "description": "Fast delivery service"
    }
  ]
}
```

**Response (Delivery Boy - provider_type = 'delivery_boy'):**
```json
{
  "error": false,
  "message": "Delivery info retrieved successfully.",
  "delivery_available": true,
  "provider_type": "delivery_boy",
  "delivery_charge_with_cod": "50.00",
  "delivery_charge_without_cod": "40.00",
  "estimate_date": "27-Nov-2025"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/app/v1/api/get_delivery_info" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "address_id=10"
```

---

### 2. Place Order

**Endpoint:** `place_order`

**Method:** POST

**Authentication:** Required (Bearer Token)

**Description:** Places an order. If shipping company is selected, includes shipping_company_id, selected_quote_id, and shipping_quote_snapshot.

**Parameters (Shipping Company Related):**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| provider_type | string | Yes* | Must be 'company' for shipping company |
| selected_quote_id | integer | Yes* | Selected shipping quote ID |
| shipping_company_id | integer | Yes* | Shipping company ID |
| shipping_quote_snapshot | string | Auto | JSON snapshot of quote (auto-generated) |

*Required when using shipping company

**Note:** The system automatically generates `shipping_quote_snapshot` from the selected quote when `provider_type='company'` and `selected_quote_id` is provided.

**Response:**
```json
{
  "error": false,
  "message": "Order placed successfully",
  "data": {
    "order_id": "123",
    "order_number": "ORD-123",
    "total": "500.00",
    "delivery_charge": "100.00",
    "final_total": "600.00"
  }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost/samer-customisation-main/app/v1/api/place_order" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "address_id=10&payment_method=COD&provider_type=company&selected_quote_id=6&shipping_company_id=104&product_variant_id=4&quantity=1"
```

---

## Common Response Format

All APIs follow a standard response format:

### Success Response
```json
{
  "error": false,
  "message": "Operation successful",
  "data": [...],
  "total": "10"  // Optional, for paginated responses
}
```

### Error Response
```json
{
  "error": true,
  "message": "Error description",
  "data": []
}
```

---

## Error Codes

Common error scenarios:

1. **Authentication Errors:**
   - `"Unauthorized access not allowed"` - Missing or invalid token
   - `"Invalid Hash"` - Token signature invalid
   - `"No Client(s) Data Found !"` - API keys not configured

2. **Validation Errors:**
   - `"The {field} field is required"` - Missing required parameter
   - `"Invalid {field}"` - Invalid parameter format
   - `"You don't have access to update this order"` - Unauthorized operation

3. **Business Logic Errors:**
   - `"Shipping company not found or not approved"` - Company doesn't exist or not approved
   - `"Insufficient admin balance"` - Admin doesn't have enough balance for transfer
   - `"Amount exceeds cash in hand"` - Trying to collect more cash than available
   - `"Invalid OTP supplied!"` - Wrong OTP for delivery
   - `"Cannot delete account. You have pending orders to deliver."` - Cannot delete with pending orders

---

## Financial Operations Flow

### COD Order Flow:
1. Customer places order with COD payment
2. Order assigned to shipping company
3. Shipping company delivers order and updates status to "delivered"
4. System automatically:
   - Updates `cash_received` for shipping company
   - Creates transaction record (`shipping_company_cash`)
5. Admin collects cash from shipping company via `collect_shipping_company_cash`
6. System:
   - Deducts from shipping company's `cash_received`
   - Creates transaction record (`shipping_company_cash_collection`)

### Prepaid Order Flow:
1. Customer places order with online payment (Razorpay, Paytm, etc.)
2. Order assigned to shipping company
3. Shipping company delivers order and updates status to "delivered"
4. System calculates pending payout (delivery charges from delivered prepaid orders)
5. Admin transfers funds to shipping company via `transfer_to_shipping_company`
6. System:
   - Deducts from admin balance
   - Adds to shipping company balance
   - Creates fund transfer record
   - Creates transaction records for both admin and company

---

## Testing Notes

1. **Base URL:** Replace `http://localhost/samer-customisation-main` with your actual domain
2. **JWT Token:** Get token from login endpoint and use in Authorization header
3. **Test Data:** Ensure test shipping companies are created and approved (status=1)
4. **Orders:** Create test orders before testing assignment and status update APIs
5. **Financial Operations:** Ensure sufficient balances before testing fund transfers

---

## Verification Summary

All APIs have been verified for:
- ✅ Authentication and authorization mechanisms
- ✅ Input validation and sanitization
- ✅ Database operations and transaction handling
- ✅ Error handling and response formats
- ✅ Business logic correctness (fund transfers, cash collection, COD handling)
- ✅ Order assignment and status update flows

---

**Document Version:** 1.0  
**Last Updated:** 2025-01-15  
**Maintained By:** Development Team

