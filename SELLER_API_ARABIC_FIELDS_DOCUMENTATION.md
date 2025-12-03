# Seller API - Arabic Fields Documentation

## Overview

The Seller API now includes Arabic language fields alongside English fields in all product and category responses. These Arabic fields are always included in the API response, allowing mobile app developers to display bilingual content or use Arabic content when the user's language preference is set to Arabic.

**No additional parameters are required** - Arabic fields are automatically included in all API responses for products and categories.

## Affected API Endpoints

### 1. Get Products API
- **Endpoint**: `POST /seller/app/v1/api/get_products`
- **Method**: POST
- **Description**: Retrieves list of products for the seller
- **Arabic Fields Added**:
  - `name_ar` - Product name in Arabic
  - `short_description_ar` - Short description in Arabic
  - `description_ar` - Full description in Arabic
  - `category_name_ar` - Category name in Arabic

### 2. Get Categories API
- **Endpoint**: `POST /seller/app/v1/api/get_categories`
- **Method**: POST
- **Description**: Retrieves hierarchical category list for the seller
- **Arabic Fields Added**:
  - `name_ar` - Category name in Arabic

### 3. Get Orders API
- **Endpoint**: `POST /seller/app/v1/api/get_orders`
- **Method**: POST
- **Description**: Retrieves list of orders with order items containing product information
- **Arabic Fields Added**:
  - `name_ar` - Product name in Arabic (within order items)

### 4. Get Order Items API
- **Endpoint**: `POST /seller/app/v1/api/get_order_items`
- **Method**: POST
- **Description**: Retrieves detailed list of order items with product information
- **Arabic Fields Added**:
  - `name_ar` - Product name in Arabic

### 5. Create Shiprocket Order API
- **Endpoint**: `POST /seller/app/v1/api/create_shiprocket_order`
- **Method**: POST
- **Description**: Creates a Shiprocket shipping order with product details
- **Arabic Fields Added**:
  - `name_ar` - Product name in Arabic (in items array)

## Response Structure

### Products API Response

Each product object in the response now includes both English and Arabic fields side-by-side:

```json
{
  "error": false,
  "message": "Products retrieved successfully !",
  "total": "10",
  "offset": "0",
  "data": [
    {
      "id": "101",
      "name": "Product Name in English",
      "name_ar": "اسم المنتج بالعربية",
      "short_description": "Short description in English",
      "short_description_ar": "الوصف المختصر بالعربية",
      "description": "Full product description in English",
      "description_ar": "الوصف الكامل للمنتج بالعربية",
      "category_name": "Category Name",
      "category_name_ar": "اسم الفئة بالعربية",
      "image": "https://example.com/product.jpg",
      "price": "100.00",
      "variants": [...]
    }
  ]
}
```

### Categories API Response

Each category object in the response includes both English and Arabic name fields:

```json
{
  "error": false,
  "message": "Category retrieved successfully",
  "total": "5",
  "data": [
    {
      "id": "29",
      "name": "Electronics",
      "name_ar": "الإلكترونيات",
      "text": "Electronics",
      "image": "https://example.com/category.jpg",
      "children": [
        {
          "id": "30",
          "name": "Mobile Phones",
          "name_ar": "الهواتف المحمولة",
          "text": "Mobile Phones"
        }
      ]
    }
  ]
}
```

### Get Orders API Response

Each order contains an array of items, and each item includes product name fields in both languages:

```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "5",
  "awaiting": "2",
  "received": "1",
  "processed": "1",
  "shipped": "1",
  "delivered": "0",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "991",
      "order_id": "991",
      "user_id": "123",
      "username": "John Doe",
      "mobile": "1234567890",
      "items": [
        {
          "id": "1501",
          "name": "Product Name in English",
          "name_ar": "اسم المنتج بالعربية",
          "product_id": "101",
          "quantity": "2",
          "price": "500.00",
          "discounted_price": "450.00",
          "image": "https://example.com/product.jpg"
        }
      ],
      "total": "900.00",
      "delivery_charge": "50.00",
      "total_payable": "950.00",
      "active_status": "received",
      "date_added": "2024-01-15 10:30:00"
    }
  ]
}
```

### Get Order Items API Response

Each order item includes product information with Arabic fields:

```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "10",
  "awaiting": "3",
  "received": "2",
  "processed": "2",
  "shipped": "2",
  "delivered": "1",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "1501",
      "order_id": "991",
      "product_id": "101",
      "name": "Product Name in English",
      "name_ar": "اسم المنتج بالعربية",
      "quantity": "2",
      "price": "500.00",
      "discounted_price": "450.00",
      "tax_amount": "50.00",
      "sub_total": "900.00",
      "active_status": "received",
      "image": "https://example.com/product.jpg",
      "product_slug": "product-name",
      "sku": "PROD-101",
      "status": [
        ["received", "2024-01-15 10:30:00"]
      ]
    }
  ]
}
```

### Create Shiprocket Order API Response

The response includes items array with product names in both languages:

```json
{
  "error": false,
  "message": "Shiprocket order created successfully",
  "data": {
    "shipment_id": "123456",
    "order_id": "SR-991-1501",
    "status": "pending",
    "items": [
      {
        "name": "Product Name in English",
        "name_ar": "اسم المنتج بالعربية",
        "sku": "PROD-101",
        "total_units": "2",
        "units": "2",
        "selling_price": "500.00",
        "discount": "50.00",
        "tax": "50.00"
      }
    ],
    "awb_code": "AWB123456789",
    "label_url": "https://example.com/label.pdf"
  }
}
```

## Field Descriptions

### Product Fields

| Field Name | Type | Description | Notes |
|------------|------|-------------|-------|
| `name` | string | Product name in English | Always present |
| `name_ar` | string | Product name in Arabic | Empty string if not available |
| `short_description` | string | Short description in English | Always present |
| `short_description_ar` | string | Short description in Arabic | Empty string if not available |
| `description` | string | Full description in English | May be empty |
| `description_ar` | string | Full description in Arabic | Empty string if not available |
| `category_name` | string | Category name in English | Always present |
| `category_name_ar` | string | Category name in Arabic | Empty string if not available |

### Category Fields

| Field Name | Type | Description | Notes |
|------------|------|-------------|-------|
| `name` | string | Category name in English | Always present |
| `name_ar` | string | Category name in Arabic | Empty string if not available |

### Order Item Fields

| Field Name | Type | Description | Notes |
|------------|------|-------------|-------|
| `name` | string | Product name in English | Always present in order items |
| `name_ar` | string | Product name in Arabic | Empty string if not available |

## Empty Field Handling

- All Arabic fields will be **empty strings (`""`)** if no Arabic content exists in the database
- Arabic fields are **never null** - they always return an empty string if missing
- This ensures consistent response structure and prevents null reference errors in mobile apps


### Get Products Request

```bash
POST /seller/app/v1/api/get_products
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN

{
  "seller_id": 175,
  "limit": 25,
  "offset": 0
}
```

### Get Categories Request

```bash
POST /seller/app/v1/api/get_categories
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN

{
  "seller_id": 175
}
```

### Get Orders Request

```bash
POST /seller/app/v1/api/get_orders
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN

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

**Request Parameters:**
- `seller_id` (optional): Seller ID (automatically used from auth token)
- `active_status` (optional): Filter by status (`awaiting`, `received`, `processed`, `shipped`, `delivered`, `cancelled`, `returned`)
- `order_type` (optional): Filter by order type (`simple` or `digital`)
- `limit` (optional): Number of records per page (default: 25)
- `offset` (optional): Number of records to skip (default: 0)
- `sort` (optional): Field to sort by (default: `o.date_added`)
- `order` (optional): Sort order (`ASC` or `DESC`, default: `DESC`)
- `start_date` (optional): Filter orders from this date
- `end_date` (optional): Filter orders until this date
- `search` (optional): Search keyword

### Get Order Items Request

```bash
POST /seller/app/v1/api/get_order_items
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN

{
  "seller_id": 175,
  "order_id": 991,
  "active_status": "received",
  "limit": 25,
  "offset": 0,
  "sort": "oi.id",
  "order": "DESC"
}
```

**Request Parameters:**
- `seller_id` (optional): Seller ID (automatically used from auth token)
- `order_id` (optional): Filter by specific order ID
- `active_status` (optional): Filter by status (comma-separated values allowed)
- `limit` (optional): Number of records per page (default: 25)
- `offset` (optional): Number of records to skip (default: 0)
- `sort` (optional): Field to sort by (default: `oi.date_added`)
- `order` (optional): Sort order (`ASC` or `DESC`, default: `DESC`)
- `start_date` (optional): Filter items from this date
- `end_date` (optional): Filter items until this date
- `search` (optional): Search keyword

### Create Shiprocket Order Request

```bash
POST /seller/app/v1/api/create_shiprocket_order
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN

{
  "consignment_id": 120,
  "pickup_location": "Croma Digital",
  "parcel_weight": 1,
  "parcel_height": 10,
  "parcel_breadth": 20,
  "parcel_length": 30
}
```

**Request Parameters:**
- `consignment_id` (required): Consignment ID for which to create Shiprocket order
- `pickup_location` (required): Name of the pickup location
- `parcel_weight` (required): Weight of the parcel in kg
- `parcel_height` (required): Height of the parcel in cm
- `parcel_breadth` (required): Breadth of the parcel in cm
- `parcel_length` (required): Length of the parcel in cm

## Response Examples

### Products Response with Arabic Fields

```json
{
  "error": false,
  "message": "Products retrieved successfully !",
  "total": "2",
  "offset": "0",
  "data": [
    {
      "id": "101",
      "name": "Air Conditioner",
      "name_ar": "مكيف الهواء",
      "short_description": "Energy efficient air conditioner",
      "short_description_ar": "مكيف هواء موفر للطاقة",
      "description": "Full description here...",
      "description_ar": "الوصف الكامل هنا...",
      "category_name": "Electronics",
      "category_name_ar": "الإلكترونيات",
      "image": "https://example.com/ac.jpg",
      "price": "500.00"
    },
    {
      "id": "102",
      "name": "Laptop",
      "name_ar": "",
      "short_description": "High performance laptop",
      "short_description_ar": "",
      "description": "Full description...",
      "description_ar": "",
      "category_name": "Electronics",
      "category_name_ar": "الإلكترونيات",
      "image": "https://example.com/laptop.jpg",
      "price": "1200.00"
    }
  ]
}
```

**Note**: Product ID 102 has empty Arabic fields, which means Arabic content is not available. The app should fall back to English fields.

### Get Orders Response with Arabic Fields

```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "2",
  "awaiting": "1",
  "received": "1",
  "processed": "0",
  "shipped": "0",
  "delivered": "0",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "991",
      "order_id": "991",
      "user_id": "123",
      "username": "John Doe",
      "mobile": "1234567890",
      "email": "john@example.com",
      "items": [
        {
          "id": "1501",
          "product_id": "101",
          "name": "Air Conditioner",
          "name_ar": "مكيف الهواء",
          "quantity": "2",
          "price": "500.00",
          "discounted_price": "450.00",
          "image": "https://example.com/ac.jpg",
          "sku": "AC-101"
        },
        {
          "id": "1502",
          "product_id": "102",
          "name": "Laptop",
          "name_ar": "",
          "quantity": "1",
          "price": "1200.00",
          "discounted_price": "1200.00",
          "image": "https://example.com/laptop.jpg",
          "sku": "LAP-102"
        }
      ],
      "total": "2100.00",
      "delivery_charge": "50.00",
      "total_payable": "2150.00",
      "active_status": "received",
      "date_added": "2024-01-15 10:30:00"
    }
  ]
}
```

**Note**: The second item (Laptop) has an empty `name_ar` field, so the app should fall back to the English `name` field.

### Get Order Items Response with Arabic Fields

```json
{
  "error": false,
  "message": "Data retrieved successfully",
  "total": "3",
  "awaiting": "0",
  "received": "1",
  "processed": "1",
  "shipped": "1",
  "delivered": "0",
  "cancelled": "0",
  "returned": "0",
  "data": [
    {
      "id": "1501",
      "order_id": "991",
      "product_id": "101",
      "name": "Air Conditioner",
      "name_ar": "مكيف الهواء",
      "quantity": "2",
      "price": "500.00",
      "discounted_price": "450.00",
      "tax_amount": "45.00",
      "sub_total": "900.00",
      "active_status": "received",
      "image": "https://example.com/ac.jpg",
      "product_slug": "air-conditioner",
      "sku": "AC-101",
      "status": [
        ["received", "15-01-2024 10:30:00am"]
      ],
      "is_returnable": "1",
      "is_cancelable": "1"
    },
    {
      "id": "1502",
      "order_id": "991",
      "product_id": "102",
      "name": "Laptop",
      "name_ar": "",
      "quantity": "1",
      "price": "1200.00",
      "discounted_price": "1200.00",
      "tax_amount": "120.00",
      "sub_total": "1200.00",
      "active_status": "processed",
      "image": "https://example.com/laptop.jpg",
      "product_slug": "laptop",
      "sku": "LAP-102",
      "status": [
        ["received", "15-01-2024 10:30:00am"],
        ["processed", "15-01-2024 11:00:00am"]
      ],
      "is_returnable": "1",
      "is_cancelable": "0"
    }
  ]
}
```

### Create Shiprocket Order Response with Arabic Fields

```json
{
  "error": false,
  "message": "Shiprocket order created successfully",
  "data": {
    "shipment_id": "123456",
    "order_id": "SR-991-1501-1502",
    "status": "pending",
    "awb_code": "AWB123456789",
    "label_url": "https://example.com/label.pdf",
    "invoice_url": "https://example.com/invoice.pdf",
    "items": [
      {
        "name": "Air Conditioner",
        "name_ar": "مكيف الهواء",
        "sku": "AC-101",
        "total_units": "2",
        "units": "2",
        "selling_price": "500.00",
        "discount": "50.00",
        "tax": "45.00"
      },
      {
        "name": "Laptop",
        "name_ar": "",
        "sku": "LAP-102",
        "total_units": "1",
        "units": "1",
        "selling_price": "1200.00",
        "discount": "0.00",
        "tax": "120.00"
      }
    ]
  }
}
```

### Categories Response with Arabic Fields

```json
{
  "error": false,
  "message": "Category retrieved successfully",
  "total": "3",
  "data": [
    {
      "id": "29",
      "name": "Electronics",
      "name_ar": "الإلكترونيات",
      "text": "Electronics",
      "image": "https://example.com/electronics.jpg",
      "children": [
        {
          "id": "30",
          "name": "Mobile Phones",
          "name_ar": "الهواتف المحمولة",
          "text": "Mobile Phones"
        },
        {
          "id": "31",
          "name": "Computers",
          "name_ar": "",
          "text": "Computers"
        }
      ]
    }
  ]
}
```

## Best Practices

1. **Always Check for Empty Strings**: Before using Arabic fields, check if they're not empty:
   ```javascript
   const displayText = (lang === 'ar' && arabicField && arabicField.trim() !== '') 
     ? arabicField 
     : englishField;
   ```

2. **Fallback to English**: If Arabic field is empty, always fall back to the English field. Never show empty strings to users.

3. **Right-to-Left (RTL) Text Direction**: When displaying Arabic text, set text direction to RTL:
   ```javascript
   <Text style={{ direction: language === 'ar' ? 'rtl' : 'ltr' }}>
     {displayText}
   </Text>
   ```

4. **Consistent Handling**: Apply the same logic across all product/category/order item fields (name, description, etc.) in all APIs

5. **Test with Empty Fields**: Always test your app with products/categories/order items that don't have Arabic content to ensure graceful fallback

6. **Helper Functions**: Create reusable helper functions for language-aware field selection:
   ```javascript
   // Helper function for selecting language-appropriate field
   const getLocalizedField = (englishField, arabicField, language) => {
     if (language === 'ar' && arabicField && arabicField.trim() !== '') {
       return arabicField;
     }
     return englishField;
   };
   
   // Usage
   const productName = getLocalizedField(product.name, product.name_ar, userLanguage);
   const categoryName = getLocalizedField(category.name, category.name_ar, userLanguage);
   const orderItemName = getLocalizedField(item.name, item.name_ar, userLanguage);
   ```

7. **Order Items**: When displaying order history or order details, always check for `name_ar` in order items, not just in product listings

8. **API Consistency**: All APIs return Arabic fields in the same format - empty string if not available, never null

## Complete API Reference

### Summary of All APIs with Arabic Fields

| API Endpoint | Arabic Fields | Use Case |
|--------------|---------------|----------|
| `get_products` | `name_ar`, `short_description_ar`, `description_ar`, `category_name_ar` | Product listing, product details |
| `get_categories` | `name_ar` | Category selection, category navigation |
| `get_orders` | `name_ar` (in order items) | Order history, order management |
| `get_order_items` | `name_ar` | Order item details, order processing |
| `create_shiprocket_order` | `name_ar` (in items array) | Shipping order creation |

### When to Use Each API

1. **get_products**: Use when you need to display a list of products or product catalog
2. **get_categories**: Use when building category filters, category navigation, or category selection UI
3. **get_orders**: Use when displaying order lists with product names in order items
4. **get_order_items**: Use when showing detailed order item information or processing individual order items
5. **create_shiprocket_order**: Use when creating shipping orders - Arabic product names are included in the shipping label data

## Migration Notes

- **No Breaking Changes**: Existing API responses remain valid - Arabic fields are added alongside existing fields
- **Backward Compatible**: Apps using only English fields will continue to work without modifications
- **No Additional Parameters**: No need to pass any new parameters - Arabic fields are automatically included
- **Order APIs Updated**: Order-related APIs (`get_orders`, `get_order_items`, `create_shiprocket_order`) now include Arabic fields in product names

## Quick Reference: Field Availability by API

### Products API (`get_products`)
- ✅ `name_ar`
- ✅ `short_description_ar`
- ✅ `description_ar`
- ✅ `category_name_ar`

### Categories API (`get_categories`)
- ✅ `name_ar` (at all hierarchy levels)

### Orders API (`get_orders`)
- ✅ `name_ar` (in `items[]` array within each order)

### Order Items API (`get_order_items`)
- ✅ `name_ar`

### Shiprocket Order API (`create_shiprocket_order`)
- ✅ `name_ar` (in `items[]` array)

## Testing Checklist

Before deploying your app with Arabic field support:

- [ ] Test product listing with Arabic products
- [ ] Test product listing with English-only products (should fallback to English)
- [ ] Test category selection in Arabic language
- [ ] Test order history display with Arabic product names
- [ ] Test order item details with Arabic product names
- [ ] Test Shiprocket order creation with Arabic product names
- [ ] Verify RTL text direction when displaying Arabic content
- [ ] Test with mixed scenarios (some products with Arabic, some without)
- [ ] Verify empty string handling (never show empty strings to users)

## Support

For questions or issues related to Arabic fields in the Seller API, please contact the development team or refer to the main API documentation.

## Changelog

### Version 1.1 (Latest)
- Added Arabic field support to `get_orders` API
- Added Arabic field support to `get_order_items` API
- Added Arabic field support to `create_shiprocket_order` API
- Updated documentation with comprehensive examples for all APIs

### Version 1.0
- Initial release with Arabic field support for `get_products` and `get_categories` APIs

