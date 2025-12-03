# Arabic Language Support - API Documentation

## Overview

This document describes the Arabic language support implementation for the eShop API. When the language is set to Arabic (`lang=ar`), the API will return product and category names, descriptions, and other text fields in Arabic from the database fields (`name_ar`, `description_ar`, `short_description_ar`, `category_name_ar`). If Arabic fields are empty, the API will fallback to English fields.

## Important Notes

- **No Breaking Changes**: All existing API endpoints continue to work as before. The `lang` parameter is optional and defaults to `en`.
- **Fallback Behavior**: If an Arabic field is empty or null, the API automatically falls back to the English field.
- **Backward Compatibility**: Existing API clients that don't pass the `lang` parameter will continue to receive English content.

## Updated API Endpoints

### 1. get_products

**Endpoint**: `POST /app/v1/api/get_products`

**New Parameter**:
- `lang` (optional): Language code. Accepts `en` (default) or `ar`. When set to `ar`, product names, descriptions, and category names will be returned in Arabic if available.

**Example Request**:
```json
{
    "id": 101,
    "category_id": 29,
    "limit": 25,
    "offset": 0,
    "lang": "ar"
}
```

**Response Changes**:
- When `lang=ar`, the following fields will contain Arabic values (if available):
  - `name`: Product name in Arabic (falls back to English if `name_ar` is empty)
  - `short_description`: Short description in Arabic (falls back to English if `short_description_ar` is empty)
  - `description`: Full description in Arabic (falls back to English if `description_ar` is empty)
  - `category_name`: Category name in Arabic (falls back to English if `category_name_ar` is empty)

**Additional Response Fields** (when `lang=ar`):
- `name_en`: Original English product name (for reference)
- `short_description_en`: Original English short description (for reference)
- `description_en`: Original English description (for reference)
- `category_name_en`: Original English category name (for reference)

**Example Response** (when `lang=ar`):
```json
{
    "error": false,
    "message": "Product(s) retrieved successfully!",
    "total": 10,
    "data": [
        {
            "id": 101,
            "name": "اسم المنتج بالعربية",
            "name_en": "Product Name in English",
            "short_description": "وصف قصير بالعربية",
            "short_description_en": "Short description in English",
            "description": "وصف كامل بالعربية",
            "description_en": "Full description in English",
            "category_name": "اسم الفئة بالعربية",
            "category_name_en": "Category Name in English",
            ...
        }
    ]
}
```

### 2. get_categories

**Endpoint**: `POST /app/v1/api/get_categories`

**New Parameter**:
- `lang` (optional): Language code. Accepts `en` (default) or `ar`. When set to `ar`, category names will be returned in Arabic if available.

**Example Request**:
```json
{
    "id": 15,
    "limit": 25,
    "offset": 0,
    "lang": "ar"
}
```

**Response Changes**:
- When `lang=ar`, the following fields will contain Arabic values (if available):
  - `name`: Category name in Arabic (falls back to English if `name_ar` is empty)
  - `text`: Category text in Arabic (used for display, falls back to English)

**Additional Response Fields** (when `lang=ar`):
- `name_en`: Original English category name (for reference)

**Example Response** (when `lang=ar`):
```json
{
    "error": false,
    "message": "Category retrieved successfully",
    "total": 5,
    "data": [
        {
            "id": 15,
            "name": "اسم الفئة بالعربية",
            "name_en": "Category Name in English",
            "text": "اسم الفئة بالعربية",
            ...
        }
    ],
    "popular_categories": [...]
}
```

## Implementation Details

### Locale Detection Priority

The API detects the language in the following order:
1. `lang` parameter in POST request (highest priority)
2. `locale` parameter in GET request (for backward compatibility)
3. `language` cookie (for web requests)
4. Default: `en`

### Fallback Logic

For all Arabic fields, the following fallback logic applies:
- If `name_ar` is not empty → use `name_ar`
- Else → use `name` (English)

This applies to:
- Product `name`, `short_description`, `description`
- Category `name`

### Search Functionality

Search queries now search in both English and Arabic fields:
- Product name: searches in both `name` and `name_ar`
- Product description: searches in both `description` and `description_ar`
- Product short description: searches in both `short_description` and `short_description_ar`

This means users can search for products using either English or Arabic text.

## Migration Guide for App Developers

### Step 1: Update API Calls

Add the `lang` parameter to your API requests when the user selects Arabic language:

```javascript
// Example: JavaScript/React Native
const fetchProducts = async (lang = 'en') => {
    const response = await fetch(API_BASE_URL + '/app/v1/api/get_products', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            limit: 25,
            offset: 0,
            lang: lang // 'en' or 'ar'
        })
    });
    return response.json();
};
```

### Step 2: Handle Response Fields

When `lang=ar`, the main fields (`name`, `description`, etc.) will already contain Arabic values. You can use them directly:

```javascript
// The 'name' field will contain Arabic text when lang=ar
product.name // "اسم المنتج بالعربية" (when lang=ar and name_ar exists)
```

If you need to access the English version for any reason, use the `_en` suffixed fields:

```javascript
product.name_en // Always contains English name
```

### Step 3: Update UI Components

No changes needed to your UI components if you're already using the `name`, `description`, and `category_name` fields. They will automatically display Arabic text when `lang=ar` is passed.

### Step 4: Search Implementation

Search now works with both English and Arabic. Users can search using either language:

```javascript
// Search will match both English and Arabic fields
const searchProducts = async (searchTerm, lang = 'en') => {
    const response = await fetch(API_BASE_URL + '/app/v1/api/get_products', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            search: searchTerm,
            lang: lang
        })
    });
    return response.json();
};
```

## Testing Checklist

Before deploying to production, test the following:

1. ✅ **English Language (Default)**
   - Call API without `lang` parameter → Should return English content
   - Call API with `lang=en` → Should return English content

2. ✅ **Arabic Language**
   - Call API with `lang=ar` → Should return Arabic content if available
   - Verify fallback: Product with empty `name_ar` → Should return English `name`

3. ✅ **Search Functionality**
   - Search with English text → Should find products
   - Search with Arabic text → Should find products
   - Search with mixed text → Should find products

4. ✅ **Categories**
   - Get categories with `lang=ar` → Should return Arabic category names
   - Verify nested categories (children) also return Arabic names

5. ✅ **Backward Compatibility**
   - Existing API calls without `lang` parameter → Should work as before
   - No breaking changes to response structure

## Error Handling

The API maintains the same error response format:

```json
{
    "error": true,
    "message": "Error message here",
    "data": []
}
```

If an invalid `lang` value is provided (other than `en` or `ar`), the API will default to `en`.

## Additional Notes

1. **Performance**: The Arabic language support does not add significant overhead. Database queries are optimized to fetch both English and Arabic fields in a single query.

2. **Caching**: If you're implementing client-side caching, consider caching products separately by language to avoid mixing English and Arabic content.

3. **Database Fields**: Ensure that Arabic content is properly stored in the database fields:
   - `products.name_ar`
   - `products.short_description_ar`
   - `products.description_ar`
   - `categories.name_ar`

4. **Character Encoding**: All responses use UTF-8 encoding. Ensure your app properly handles UTF-8 for Arabic text display.

## Support

For questions or issues related to Arabic language support, please contact the development team or refer to the main API documentation.

---

**Last Updated**: 2025-01-XX
**API Version**: v1
**Compatibility**: Backward compatible with existing API clients

