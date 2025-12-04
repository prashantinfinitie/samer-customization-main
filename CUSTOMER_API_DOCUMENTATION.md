# Customer API Documentation - Arabic Language Support

## Base URL
```
https://samer.infinitietech.in/
```

## Authentication
Most customer APIs require authentication via Bearer token. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

**Note**: Some APIs like `get_categories`, `get_products`, `get_sections`, and `get_slider_images` may not require authentication depending on your configuration.

## Language Support
All APIs support language detection via:
- **HTTP Header**: `Accept-Language: ar` or `X-Language: ar` or `lang: ar`
- **Query Parameter**: `?lang=ar`
- **POST Parameter**: `lang=ar`

When `lang=ar` is provided, Arabic fields are returned in the main response keys (e.g., `product_name` contains Arabic text). When `lang=en` or not provided, English fields are returned.

---

## 1. Get Categories API

### Endpoint
```
POST /app/v1/api/get_categories
```

### Description
Retrieves hierarchical category list with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN (optional)
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "id": 1
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | No | Specific category ID to retrieve |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_categories?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "id": 1
  }'
```

### Response (English - lang not provided)
```json
{
  "error": false,
  "message": "Category(s) retrieved successfully!",
  "data": [
    {
      "id": "1",
      "name": "Electronics",
      "name_ar": "",
      "parent_id": "0",
      "slug": "electronics",
      "image": "uploads/images/categories/electronics.jpg",
      "status": "1",
      "children": []
    }
  ]
}
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Category(s) retrieved successfully!",
  "data": [
    {
      "id": "1",
      "name": "إلكترونيات",
      "name_ar": "إلكترونيات",
      "name_en": "Electronics",
      "parent_id": "0",
      "slug": "electronics",
      "image": "uploads/images/categories/electronics.jpg",
      "status": "1",
      "children": []
    }
  ]
}
```

### Response Fields
- `name`: Category name (English or Arabic based on lang parameter)
- `name_ar`: Original Arabic name field (always included)
- `name_en`: English name (included when lang=ar)
- `id`: Category ID
- `parent_id`: Parent category ID
- `slug`: URL-friendly category identifier
- `image`: Category image path
- `status`: Category status
- `children`: Array of sub-categories (recursively translated)

---

## 2. Get Products API

### Endpoint
```
POST /app/v1/api/get_products
```

### Description
Retrieves list of products with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN (optional)
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "id": 101,
  "category_id": 29,
  "user_id": 15,
  "search": "keyword",
  "limit": 25,
  "offset": 0,
  "sort": "p.id",
  "order": "DESC"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | No | Specific product ID |
| category_id | integer | No | Filter by category |
| user_id | integer | No | User ID for personalized results |
| search | string | No | Search keyword |
| limit | integer | No | Records per page (default: 25) |
| offset | integer | No | Records to skip (default: 0) |
| sort | string | No | Sort field |
| order | string | No | Sort order: ASC/DESC |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_products" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{
    "category_id": 29,
    "limit": 25,
    "offset": 0
  }'
```

### Response (English - lang not provided)
```json
{
  "error": false,
  "message": "Products retrieved successfully !",
  "total": "50",
  "offset": "0",
  "data": [
    {
      "id": "101",
      "name": "Samsung Galaxy A23 5G",
      "name_ar": "",
      "short_description": "Latest 5G smartphone",
      "short_description_ar": "",
      "description": "Full product description",
      "description_ar": "",
      "category_name": "Mobile",
      "category_name_ar": "",
      "price": "5800",
      "special_price": "5800",
      "image": "uploads/images/products/samsung.jpg",
      "status": "1"
    }
  ]
}
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Products retrieved successfully !",
  "total": "50",
  "offset": "0",
  "data": [
    {
      "id": "101",
      "name": "سامسونج جالاكسي A23 5G",
      "name_ar": "سامسونج جالاكسي A23 5G",
      "name_en": "Samsung Galaxy A23 5G",
      "short_description": "أحدث هاتف ذكي 5G",
      "short_description_ar": "أحدث هاتف ذكي 5G",
      "short_description_en": "Latest 5G smartphone",
      "description": "وصف كامل للمنتج",
      "description_ar": "وصف كامل للمنتج",
      "description_en": "Full product description",
      "category_name": "موبايل",
      "category_name_ar": "موبايل",
      "category_name_en": "Mobile",
      "price": "5800",
      "special_price": "5800",
      "image": "uploads/images/products/samsung.jpg",
      "status": "1"
    }
  ]
}
```

### Response Fields
- `name`: Product name (English or Arabic based on lang)
- `name_ar`: Original Arabic name (always included)
- `name_en`: English name (included when lang=ar)
- `short_description`: Short description (English or Arabic based on lang)
- `short_description_ar`: Original Arabic short description
- `short_description_en`: English short description (included when lang=ar)
- `description`: Full description (English or Arabic based on lang)
- `description_ar`: Original Arabic description
- `description_en`: English description (included when lang=ar)
- `category_name`: Category name (English or Arabic based on lang)
- `category_name_ar`: Original Arabic category name
- `category_name_en`: English category name (included when lang=ar)

---

## 3. Get Sections API

### Endpoint
```
POST /app/v1/api/get_sections
```

### Description
Retrieves product sections with nested products, all with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN (optional)
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "user_id": 15,
  "zipcode": "370001",
  "limit": 10,
  "offset": 0
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | No | User ID for personalized results |
| zipcode | string | No | Zipcode for location-based products |
| limit | integer | No | Number of sections (default: 10) |
| offset | integer | No | Sections to skip (default: 0) |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_sections?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "user_id": 15,
    "zipcode": "370001",
    "limit": 10,
    "offset": 0
  }'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Sections retrived successfully",
  "min_price": "50",
  "max_price": "60000",
  "data": [
    {
      "id": "1",
      "title": "Featured Products",
      "short_description": "Best selling products",
      "total": "10",
      "slug": "featured-products",
      "filters": [],
      "product_details": [
        {
          "id": "101",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "name_en": "Samsung Galaxy A23 5G",
          "short_description": "أحدث هاتف ذكي 5G",
          "price": "5800",
          "special_price": "5800",
          "image": "uploads/images/products/samsung.jpg"
        }
      ]
    }
  ]
}
```

### Response Fields
- `title`: Section title
- `short_description`: Section description
- `total`: Total products in section
- `product_details`: Array of products (all translated based on lang parameter)
- Each product in `product_details` follows the same structure as Get Products API

---

## 4. Get User Cart API

### Endpoint
```
POST /app/v1/api/get_user_cart
```

### Description
Retrieves user's shopping cart with product details in Arabic or English.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "user_id": 15,
  "zipcode": "370001"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | User ID (from token) |
| zipcode | string | No | Zipcode for delivery calculation |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_user_cart" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "X-Language: ar" \
  -d '{
    "user_id": 15,
    "zipcode": "370001"
  }'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Cart items retrieved successfully",
  "data": [
    {
      "id": "123",
      "user_id": "15",
      "product_variant_id": "456",
      "quantity": "2",
      "net_amount": "11600",
      "product_details": [
        {
          "id": "101",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "name_en": "Samsung Galaxy A23 5G",
          "short_description": "أحدث هاتف ذكي 5G",
          "price": "5800",
          "special_price": "5800",
          "image": "uploads/images/products/samsung.jpg",
          "category_name": "موبايل",
          "category_name_ar": "موبايل"
        }
      ]
    }
  ],
  "total": "11600"
}
```

### Response Fields
- `id`: Cart item ID
- `quantity`: Product quantity
- `net_amount`: Total amount for this item
- `product_details`: Array containing product information (translated based on lang)
- Each product in `product_details` follows the same structure as Get Products API

---

## 5. Get Favorites API

### Endpoint
```
POST /app/v1/api/get_favorites
```

### Description
Retrieves user's favorite products with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "user_id": 15,
  "limit": 25,
  "offset": 0
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | User ID (from token) |
| limit | integer | No | Records per page (default: 25) |
| offset | integer | No | Records to skip (default: 0) |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_favorites" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "lang: ar" \
  -d '{
    "user_id": 15,
    "limit": 25,
    "offset": 0
  }'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Data Retrieved Successfully",
  "total": "5",
  "data": [
    {
      "id": "101",
      "name": "سامسونج جالاكسي A23 5G",
      "name_ar": "سامسونج جالاكسي A23 5G",
      "name_en": "Samsung Galaxy A23 5G",
      "short_description": "أحدث هاتف ذكي 5G",
      "price": "5800",
      "special_price": "5800",
      "image": "uploads/images/products/samsung.jpg",
      "category_name": "موبايل"
    }
  ]
}
```

---

## 6. Get Slider Images API

### Endpoint
```
POST /app/v1/api/get_slider_images
```

### Description
Retrieves slider images with linked categories or products, all with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN (optional)
Accept-Language: ar (optional)
```

### Request Body
```json
{}
```

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_slider_images?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{}'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "data": [
    {
      "id": "1",
      "title": "Electronics Sale",
      "image": "https://samer.infinitietech.in/uploads/sliders/sale.jpg",
      "link": "",
      "type": "categories",
      "type_id": "1",
      "data": [
        {
          "id": "1",
          "name": "إلكترونيات",
          "name_ar": "إلكترونيات",
          "name_en": "Electronics",
          "slug": "electronics",
          "image": "uploads/images/categories/electronics.jpg"
        }
      ]
    },
    {
      "id": "2",
      "title": "Featured Product",
      "image": "https://samer.infinitietech.in/uploads/sliders/product.jpg",
      "link": "",
      "type": "products",
      "type_id": "101",
      "data": [
        {
          "id": "101",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "name_en": "Samsung Galaxy A23 5G",
          "price": "5800",
          "special_price": "5800",
          "image": "uploads/images/products/samsung.jpg"
        }
      ]
    }
  ]
}
```

### Response Fields
- `type`: Type of slider item (`categories` or `products`)
- `type_id`: ID of the category or product
- `data`: Array containing category or product data (translated based on lang)
  - If `type` is `categories`: Contains category objects (same structure as Get Categories API)
  - If `type` is `products`: Contains product objects (same structure as Get Products API)

---

## 7. Get Orders API

### Endpoint
```
POST /app/v1/api/get_orders
```

### Description
Retrieves user's order history with order items containing product information in Arabic or English.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "user_id": 15,
  "active_status": "received",
  "limit": 25,
  "offset": 0,
  "sort": "id",
  "order": "DESC"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| user_id | integer | Yes | User ID (from token) |
| active_status | string | No | Filter by status (received, delivered, cancelled, processed, returned) |
| limit | integer | No | Records per page (default: 25) |
| offset | integer | No | Records to skip (default: 0) |
| sort | string | No | Sort field (default: id) |
| order | string | No | Sort order: ASC/DESC (default: DESC) |
| download_invoice | integer | No | Include invoice HTML (0/1, default: 0) |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{
    "user_id": 15,
    "active_status": "received",
    "limit": 25,
    "offset": 0
  }'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "10",
  "data": [
    {
      "id": "991",
      "order_id": "ORD-991",
      "user_id": "15",
      "mobile": "1234567890",
      "address": "Customer Address",
      "total": "11600",
      "active_status": "received",
      "date_added": "2024-01-15 10:30:00",
      "order_items": [
        {
          "id": "1234",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "product_id": "101",
          "quantity": "2",
          "price": "5800",
          "sub_total": "11600",
          "category_name": "موبايل",
          "category_name_ar": "موبايل"
        }
      ]
    }
  ]
}
```

### Response Fields
- `id`: Order ID
- `order_id`: Order reference number
- `total`: Total order amount
- `active_status`: Order status
- `date_added`: Order date
- `order_items`: Array of order items with product information (translated based on lang)
  - `name`: Product name (English or Arabic based on lang)
  - `name_ar`: Original Arabic product name
  - `category_name`: Category name (English or Arabic based on lang)
  - `category_name_ar`: Original Arabic category name

---

## Get Order Tracking

### Endpoint
```
POST /app/v1/api/get_order_tracking
```

### Description
Retrieves order tracking information for shipping company orders, including status history timeline and shipping company details.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### Request Body
```json
{
  "order_id": 123
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | integer | Yes | Order ID to track |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_order_tracking" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "order_id": 123
  }'
```

### Response
```json
{
  "error": false,
  "message": "Order tracking retrieved successfully",
  "data": {
    "order_id": 123,
    "order_total": "500.00",
    "delivery_charge": "50.00",
    "final_total": "550.00",
    "payment_method": "COD",
    "order_date": "2025-01-15 10:00:00",
    "address": "123 Main St, City",
    "shipping_company": {
      "id": 104,
      "name": "Fast Delivery Co",
      "email": "fast@delivery.com",
      "mobile": "1234567890",
      "address": "Company Address"
    },
    "order_items": [
      {
        "id": 456,
        "product_name": "Product Name",
        "variant_name": "Variant",
        "quantity": 2,
        "sub_total": "500.00",
        "active_status": "processed",
        "current_status": "processed",
        "status_history": [
          {
            "status": "received",
            "label": "Order Received",
            "timestamp": "15-01-2025 10:30:00am",
            "formatted_date": "15-Jan-2025 10:30 AM"
          },
          {
            "status": "processed",
            "label": "Order Processed",
            "timestamp": "15-01-2025 11:00:00am",
            "formatted_date": "15-Jan-2025 11:00 AM"
          }
        ]
      }
    ]
  }
}
```

### Response Fields
- `order_id`: Order ID
- `order_total`: Order subtotal
- `delivery_charge`: Delivery charge
- `final_total`: Final order total
- `payment_method`: Payment method used
- `order_date`: Order placement date
- `address`: Delivery address
- `shipping_company`: Shipping company details (if order is assigned to shipping company)
  - `id`: Shipping company ID
  - `name`: Company name
  - `email`: Company email
  - `mobile`: Company mobile
  - `address`: Company address
- `order_items`: Array of order items with tracking information
  - `id`: Order item ID
  - `product_name`: Product name
  - `variant_name`: Product variant name
  - `quantity`: Quantity ordered
  - `sub_total`: Item subtotal
  - `active_status`: Current status
  - `current_status`: Current status (same as active_status)
  - `status_history`: Array of status history entries
    - `status`: Status code
    - `label`: Human-readable status label
    - `timestamp`: Original timestamp
    - `formatted_date`: Formatted date string

### Error Response
```json
{
  "error": true,
  "message": "Order not found or access denied",
  "data": []
}
```

### Notes
- This endpoint is only available for orders assigned to shipping companies
- Status history shows the complete timeline of order status changes
- Each status entry includes both the original timestamp and a formatted date for display

---

## Language Detection Priority

The API detects language in the following priority order:

1. **HTTP Headers** (highest priority):
   - `Accept-Language: ar`
   - `X-Language: ar`
   - `lang: ar`

2. **Query Parameter**:
   - `?lang=ar`

3. **POST Parameter**:
   - `lang=ar`

4. **Default**: English (`en`)

### Examples

**Using Header:**
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{"category_id": 29}'
```

**Using Query Parameter:**
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_products?lang=ar" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"category_id": 29}'
```

**Using POST Parameter:**
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"category_id": 29, "lang": "ar"}'
```

---

## Error Responses

### Standard Error Format
```json
{
  "error": true,
  "message": "Error message description",
  "data": []
}
```

### Common Error Codes
- **401 Unauthorized**: Invalid or missing authentication token
- **400 Bad Request**: Invalid request parameters or validation errors
- **404 Not Found**: Resource not found
- **500 Internal Server Error**: Server error

### Example Error Response
```json
{
  "error": true,
  "message": "No Order(s) Found !",
  "data": []
}
```

---

## Notes

1. **Clean Text Responses**: All API responses contain clean text without HTML tags, perfect for mobile app display.

2. **Fallback Behavior**: If Arabic content is not available for a product or category, English content is returned in the main fields.

3. **Field Preservation**: When `lang=ar`, both Arabic and English fields are included in the response:
   - Main fields (`name`, `description`, etc.) contain Arabic text
   - Fields with `_en` suffix contain English text
   - Fields with `_ar` suffix contain original Arabic text

4. **Nested Data Translation**: All nested product/category data in sections, cart, favorites, orders, and sliders are properly translated based on the language parameter.

5. **Recursive Translation**: Category children are recursively translated, so all sub-categories in a category tree will be in the requested language.

6. **Authentication**: Some APIs may work without authentication (like public product listings), but user-specific APIs (cart, favorites, orders) require valid authentication tokens.

---

## Complete cURL Examples

### Get Products in Arabic
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_products?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "category_id": 29,
    "limit": 25,
    "offset": 0
  }'
```

### Get User Cart in Arabic
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_user_cart" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{
    "user_id": 15,
    "zipcode": "370001"
  }'
```

### Get Orders in Arabic
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "X-Language: ar" \
  -d '{
    "user_id": 15,
    "limit": 25,
    "offset": 0
  }'
```

### Get Categories in Arabic
```bash
curl -X POST "https://samer.infinitietech.in/app/v1/api/get_categories" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "lang: ar" \
  -d '{}'
```

---

## Support

For API support and questions, please contact:
- **Email**: [email protected]
- **Website**: https://samer.infinitietech.in/

---

**Last Updated**: January 2024
**API Version**: v1

