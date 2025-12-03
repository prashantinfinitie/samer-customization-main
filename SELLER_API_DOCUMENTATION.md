# Seller API Documentation - Arabic Language Support

## Base URL
```
https://samer.infinitietech.in/
```

## Authentication
All seller APIs require authentication via Bearer token. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

## Language Support
All APIs support language detection via:
- **HTTP Header**: `Accept-Language: ar` or `X-Language: ar` or `lang: ar`
- **Query Parameter**: `?lang=ar`
- **POST Parameter**: `lang=ar`

When `lang=ar` is provided, Arabic fields are returned in the main response keys (e.g., `name` contains Arabic text). When `lang=en` or not provided, English fields are returned.

---

## 1. Get Categories API

### Endpoint
```
POST /seller/app/v1/api/get_categories
```

### Description
Retrieves hierarchical category list for the seller with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "seller_id": 175
}
```

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_categories" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{
    "seller_id": 175
  }'
```

### Response (English - lang not provided)
```json
{
  "error": false,
  "message": "Category retrieved successfully",
  "total": "10",
  "data": [
    {
      "id": "1",
      "name": "Electronics",
      "name_ar": "",
      "parent_id": "0",
      "slug": "electronics",
      "image": "uploads/images/categories/electronics.jpg",
      "status": "1"
    }
  ]
}
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Category retrieved successfully",
  "total": "10",
  "data": [
    {
      "id": "1",
      "name": "إلكترونيات",
      "name_ar": "إلكترونيات",
      "name_en": "Electronics",
      "parent_id": "0",
      "slug": "electronics",
      "image": "uploads/images/categories/electronics.jpg",
      "status": "1"
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
- `status`: Category status (1 = active, 0 = inactive)

---

## 2. Get Products API

### Endpoint
```
POST /seller/app/v1/api/get_products
```

### Description
Retrieves list of products for the seller with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "seller_id": 175,
  "id": 101,
  "category_id": 29,
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
| seller_id | integer | Yes | Seller ID (automatically used from token) |
| id | integer | No | Specific product ID |
| category_id | integer | No | Filter by category |
| search | string | No | Search keyword |
| tags | string | No | Comma-separated tags |
| limit | integer | No | Records per page (default: 25) |
| offset | integer | No | Records to skip (default: 0) |
| sort | string | No | Sort field (default: p.row_order) |
| order | string | No | Sort order: ASC/DESC (default: ASC) |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_products?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "seller_id": 175,
    "limit": 25,
    "offset": 0
  }'
```

### Response (English - lang not provided)
```json
{
  "error": false,
  "message": "Products retrieved successfully !",
  "filters": [],
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
  "filters": [],
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

## 3. Add Products API

### Endpoint
```
POST /seller/app/v1/api/add_products
```

### Description
Adds a new product with support for Arabic fields.

### Headers
```
Content-Type: multipart/form-data
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### Request Body (Form Data)
```
seller_id: 175
pro_input_name: Product Name
pro_input_name_ar: اسم المنتج (optional)
short_description: Short description
short_description_ar: الوصف المختصر (optional)
pro_input_description: Full product description
pro_input_description_ar: وصف المنتج الكامل (optional)
category_id: 99
pro_input_image: [file]
product_type: simple_product
simple_price: 100
simple_special_price: 90
```

### Required Fields
- `pro_input_name`: Product name (English) - **Required**
- `short_description`: Short description - **Required**
- `category_id`: Category ID - **Required**
- `pro_input_image`: Product image - **Required**
- `product_type`: Product type (simple_product/variable_product/digital_product) - **Required**

### Optional Arabic Fields
- `pro_input_name_ar`: Product name in Arabic
- `short_description_ar`: Short description in Arabic
- `pro_input_description_ar`: Full description in Arabic

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/add_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -F "seller_id=175" \
  -F "pro_input_name=Samsung Galaxy A23 5G" \
  -F "pro_input_name_ar=سامسونج جالاكسي A23 5G" \
  -F "short_description=Latest 5G smartphone" \
  -F "short_description_ar=أحدث هاتف ذكي 5G" \
  -F "pro_input_description=Full product description here" \
  -F "pro_input_description_ar=وصف كامل للمنتج هنا" \
  -F "category_id=29" \
  -F "product_type=simple_product" \
  -F "simple_price=5800" \
  -F "simple_special_price=5800" \
  -F "pro_input_image=@/path/to/image.jpg"
```

### Response
```json
{
  "error": false,
  "message": "Product Added Successfully"
}
```

### Error Response
```json
{
  "error": true,
  "message": "Validation errors here",
  "data": []
}
```

---

## 4. Update Products API

### Endpoint
```
POST /seller/app/v1/api/update_products
```

### Description
Updates an existing product with support for Arabic fields.

### Headers
```
Content-Type: multipart/form-data
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### Request Body (Form Data)
```
edit_product_id: 74
pro_input_name: Updated Product Name
pro_input_name_ar: اسم المنتج المحدث (optional)
short_description: Updated short description
short_description_ar: الوصف المختصر المحدث (optional)
pro_input_description: Updated full description
pro_input_description_ar: وصف المنتج الكامل المحدث (optional)
category_id: 99
product_type: simple_product
simple_price: 100
simple_special_price: 90
```

### Required Fields
- `edit_product_id`: Product ID to update - **Required**
- `pro_input_name`: Product name (English) - **Required**
- `short_description`: Short description - **Required**
- `category_id`: Category ID - **Required**
- `product_type`: Product type - **Required**

### Optional Arabic Fields
- `pro_input_name_ar`: Product name in Arabic
- `short_description_ar`: Short description in Arabic
- `pro_input_description_ar`: Full description in Arabic

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/update_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -F "edit_product_id=74" \
  -F "pro_input_name=Samsung Galaxy A23 5G Updated" \
  -F "pro_input_name_ar=سامسونج جالاكسي A23 5G محدث" \
  -F "short_description=Updated description" \
  -F "short_description_ar=وصف محدث" \
  -F "pro_input_description=Updated full description" \
  -F "pro_input_description_ar=وصف كامل محدث" \
  -F "category_id=29" \
  -F "product_type=simple_product" \
  -F "simple_price=5800"
```

### Response
```json
{
  "error": false,
  "message": "Product Update Successfully"
}
```

---

## 5. Get Orders API

### Endpoint
```
POST /seller/app/v1/api/get_orders
```

### Description
Retrieves list of orders with order items containing product information in Arabic or English.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "seller_id": 175,
  "active_status": "received",
  "order_type": "simple",
  "limit": 25,
  "offset": 0,
  "sort": "o.date_added",
  "order": "DESC"
}
```

### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| seller_id | integer | No | Seller ID (auto from token) |
| active_status | string | No | Filter by status (received, processed, shipped, delivered, cancelled, returned) |
| order_type | string | No | Filter by type (simple/digital) |
| limit | integer | No | Records per page (default: 25) |
| offset | integer | No | Records to skip (default: 0) |
| sort | string | No | Sort field (default: o.id) |
| order | string | No | Sort order: ASC/DESC (default: DESC) |

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_orders?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
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
  "awaiting": "2",
  "received": "5",
  "processed": "2",
  "shipped": "1",
  "delivered": "0",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "991",
      "order_id": "ORD-991",
      "user_id": "15",
      "mobile": "1234567890",
      "address": "Customer Address",
      "order_items": [
        {
          "id": "1234",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "product_id": "101",
          "quantity": "1",
          "price": "5800",
          "sub_total": "5800",
          "category_name": "موبايل",
          "category_name_ar": "موبايل"
        }
      ],
      "total": "5800",
      "active_status": "received",
      "date_added": "2024-01-15 10:30:00"
    }
  ]
}
```

---

## 6. Get Order Items API

### Endpoint
```
POST /seller/app/v1/api/get_order_items
```

### Description
Retrieves detailed list of order items with product information in Arabic or English.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### Request Body
```json
{
  "seller_id": 175,
  "order_id": 991,
  "active_status": "received",
  "limit": 25,
  "offset": 0
}
```

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_order_items" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "X-Language: ar" \
  -d '{
    "order_id": 991,
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
  "total": "5",
  "awaiting": "0",
  "received": "5",
  "processed": "0",
  "shipped": "0",
  "delivered": "0",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "991",
      "order_id": "ORD-991",
      "order_items": [
        {
          "id": "1234",
          "name": "سامسونج جالاكسي A23 5G",
          "name_ar": "سامسونج جالاكسي A23 5G",
          "product_id": "101",
          "quantity": "1",
          "price": "5800",
          "sub_total": "5800",
          "category_name": "موبايل",
          "category_name_ar": "موبايل"
        }
      ]
    }
  ]
}
```

---

## 7. Get Categories List API

### Endpoint
```
POST /seller/app/v1/api/get_categories_list
```

### Description
Retrieves category list (alternative format) with Arabic language support.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_categories_list?lang=ar" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{}'
```

---

## 8. Get Statistics API

### Endpoint
```
POST /seller/app/v1/api/get_statistics
```

### Description
Retrieves seller statistics including category-wise product counts with Arabic category names.

### Headers
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept-Language: ar (optional)
```

### cURL Example
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_statistics" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "lang: ar" \
  -d '{
    "seller_id": 175
  }'
```

### Response (Arabic - lang=ar)
```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "currency_symbol": "₹",
  "category_wise_product_count": {
    "cat_name": ["إلكترونيات", "موبايل", "ملابس"],
    "counter": [10, 5, 8]
  },
  "earnings": [
    {
      "overall_sale": 50000,
      "daily_earnings": {
        "total_sale": [1000, 2000, 1500],
        "day": [1, 2, 3]
      }
    }
  ]
}
```

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
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept-Language: ar" \
  -d '{"seller_id": 175}'
```

**Using Query Parameter:**
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_products?lang=ar" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"seller_id": 175}'
```

**Using POST Parameter:**
```bash
curl -X POST "https://samer.infinitietech.in/seller/app/v1/api/get_products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"seller_id": 175, "lang": "ar"}'
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

---

## Notes

1. **Arabic Fields are Optional**: All Arabic fields (`pro_input_name_ar`, `short_description_ar`, `pro_input_description_ar`) are optional when adding/updating products.

2. **Clean Text Responses**: API responses contain clean text without HTML tags, suitable for mobile app display.

3. **Fallback Behavior**: If Arabic content is not available, English content is returned in the main fields.

4. **Field Preservation**: When `lang=ar`, both Arabic and English fields are included in the response (with `_en` suffix for English values).

5. **Nested Data**: All nested product/category data in orders, consignments, and other structures are properly translated based on the language parameter.

---

## Support

For API support and questions, please contact:
- **Email**: [email protected]
- **Website**: https://samer.infinitietech.in/

---

**Last Updated**: January 2024
**API Version**: v1

