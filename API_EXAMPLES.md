# API Examples - Complete Request/Response Guide

## 🔐 Authentication Examples

### 1. User Login

**Note:** Password is optional. Authentication happens in the commerce platform before this call is made.

**Request:**
```bash
curl -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com"
  }'
```

**Request (with optional password):**
```bash
curl -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USR1234",
      "email": "john.doe@example.com",
      "name": "Test User",
      "clientId": "CLI5678",
      "roles": ["customer"],
      "allowedClients": [
        {
          "clientId": "CLI5678",
          "name": "Main Client",
          "canCreateOrders": true
        }
      ]
    }
  }
}
```

### 2. Guest Registration

**Request:**
```bash
curl -X POST http://localhost:8090/auth/guest-register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guest@example.com",
    "name": "Guest User",
    "phone": "+34612345678"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USR9876",
      "email": "guest@example.com",
      "name": "Guest User",
      "phone": "+34612345678",
      "clientId": "CLI9999",
      "roles": ["customer"]
    }
  }
}
```

---

## 🎫 Token Management Examples

### 3. Force Login (Impersonation)

**Request:**
```bash
curl -X POST http://localhost:8090/token/force-login \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "userId": "USR1234",
    "clientId": "CLI5678"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USR1234",
      "clientId": "CLI5678",
      "email": "userUSR1234@example.com",
      "name": "Impersonated User",
      "roles": ["customer"]
    }
  }
}
```

### 4. Refresh Token

**Request:**
```bash
curl -X POST http://localhost:8090/token/refresh \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "tokenType": "Bearer",
    "expiresIn": 3600
  }
}
```

---

## 👤 User/Client Information Examples

### 5. Get Personalized Prices

**Description:** Called asynchronously by 3rd party systems after user login to retrieve personalized pricing. Users with `@konex.com` or `@konex.es` email addresses receive a 15% discount on all products. Returns minimalistic product data to optimize payload size.

**Request:**
```bash
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer <your-user-token>"
```

**Response (with @konex.com discount):**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI1234",
    "currency": "EUR",
    "totalProducts": 50,
    "prices": [
      {
        "id": "1",
        "sku": "32065",
        "price": 75.0,
        "finalPrice": 63.75
      },
      {
        "id": "2",
        "sku": "35281",
        "price": 75.0,
        "finalPrice": 63.75
      },
      {
        "id": "3",
        "sku": "46064",
        "price": 75.0,
        "finalPrice": 63.75
      }
    ]
  }
}
```

**Response (without discount):**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI5678",
    "currency": "EUR",
    "totalProducts": 50,
    "prices": [
      {
        "id": "1",
        "sku": "32065",
        "price": 75.0,
        "finalPrice": 75.0
      },
      {
        "id": "2",
        "sku": "35281",
        "price": 75.0,
        "finalPrice": 75.0
      }
    ]
  }
}
```

### 6. Get Filtered Catalog

**Request:**
```bash
curl -X GET http://localhost:8090/me/catalog \
  -H "Authorization: Bearer <your-user-token>"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI5678",
    "allowedCategories": ["electronics", "computers", "tablets"],
    "products": [
      {
        "id": "PROD001",
        "sku": "IPAD-PRO-128-GRAY",
        "name": "iPad Pro 128GB Space Gray",
        "available": true,
        "categories": ["tablets", "electronics"]
      }
    ]
  }
}
```

### 7. Get Client Addresses

**Request:**
```bash
curl -X GET http://localhost:8090/me/addresses \
  -H "Authorization: Bearer <your-user-token>"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI5678",
    "addresses": [
      {
        "id": "ADDR001",
        "type": "shipping",
        "firstName": "John",
        "lastName": "Doe",
        "company": "Test Company",
        "street": "Calle Mayor 123",
        "city": "Madrid",
        "postalCode": "28001",
        "country": "ES",
        "phone": "+34600000000",
        "isDefault": true
      },
      {
        "id": "ADDR002",
        "type": "billing",
        "firstName": "John",
        "lastName": "Doe",
        "company": "Test Company",
        "street": "Calle Mayor 123",
        "city": "Madrid",
        "postalCode": "28001",
        "country": "ES",
        "phone": "+34600000000",
        "taxId": "B12345678",
        "isDefault": true
      }
    ]
  }
}
```

### 8. Get Order History

**Request:**
```bash
curl -X GET http://localhost:8090/me/orders \
  -H "Authorization: Bearer <your-user-token>"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "clientId": "CLI5678",
    "orders": [
      {
        "orderId": "ORD2024001",
        "orderDate": "2024-01-15T10:30:00Z",
        "status": "delivered",
        "total": 1854.99,
        "currency": "EUR",
        "itemCount": 2
      },
      {
        "orderId": "ORD2024002",
        "orderDate": "2024-02-20T14:15:00Z",
        "status": "shipped",
        "total": 799.99,
        "currency": "EUR",
        "itemCount": 1
      }
    ]
  }
}
```

---

## 🛒 Cart Management Examples (Model 2)

### 9. Validate Cart Item

**Request:**
```bash
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer <your-user-token>" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "IPAD-PRO-128-GRAY",
    "qty": 2
  }'
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "sku": "IPAD-PRO-128-GRAY",
    "qty": 2,
    "price": 854.99,
    "available": true,
    "stockAvailable": 50
  }
}
```

**Error Response (Out of Stock):**
```json
{
  "success": false,
  "message": "Product not available or insufficient stock",
  "data": {
    "sku": "IPHONE-15-128-BLACK",
    "requestedQty": 1,
    "availableStock": 0
  }
}
```

### 10. Validate Entire Cart

**Request:**
```bash
curl -X POST http://localhost:8090/cart/validate \
  -H "Authorization: Bearer <your-user-token>" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"sku": "IPAD-PRO-128-GRAY", "qty": 1},
      {"sku": "AIRPODS-PRO-2", "qty": 2}
    ],
    "shippingAddressId": "ADDR001",
    "shippingMethodId": "STANDARD"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "items": [
      {
        "sku": "IPAD-PRO-128-GRAY",
        "qty": 1,
        "price": 99.99,
        "subtotal": 99.99,
        "valid": true
      },
      {
        "sku": "AIRPODS-PRO-2",
        "qty": 2,
        "price": 99.99,
        "subtotal": 199.98,
        "valid": true
      }
    ],
    "subtotal": 299.97,
    "shippingCost": 5.99,
    "taxAmount": 62.99,
    "total": 368.95,
    "currency": "EUR",
    "shippingAddressValid": true,
    "shippingMethodValid": true
  }
}
```

### 11. Get Shipping Options

**Request:**
```bash
curl -X GET "http://localhost:8090/cart/shipping-options?cartTotal=150&shippingAddressId=ADDR001" \
  -H "Authorization: Bearer <your-user-token>"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "shippingMethods": [
      {
        "id": "STANDARD",
        "name": "Standard Shipping",
        "description": "Delivery in 3-5 business days",
        "cost": 5.99,
        "estimatedDays": 5,
        "available": true
      },
      {
        "id": "EXPRESS",
        "name": "Express Shipping",
        "description": "Delivery in 1-2 business days",
        "cost": 12.99,
        "estimatedDays": 2,
        "available": true
      },
      {
        "id": "FREE",
        "name": "Free Shipping",
        "description": "Free delivery on orders over €100",
        "cost": 0,
        "estimatedDays": 7,
        "available": true
      }
    ],
    "currency": "EUR"
  }
}
```

### 12. Confirm Order

**Request:**
```bash
curl -X POST http://localhost:8090/cart/confirm \
  -H "Authorization: Bearer <your-user-token>" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "sku": "IPAD-PRO-128-GRAY",
        "qty": 1,
        "price": 854.99
      }
    ],
    "shippingAddressId": "ADDR001",
    "billingAddressId": "ADDR002",
    "shippingMethodId": "STANDARD",
    "paymentMethod": "credit_card",
    "paymentReference": "PAY-20240930-123456"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "orderId": "ORD202409305432",
    "orderNumber": "ORD202409305432",
    "status": "confirmed",
    "createdAt": "2024-09-30T06:35:18+02:00",
    "total": 899.99,
    "currency": "EUR",
    "message": "Order confirmed successfully"
  }
}
```

---

## 📦 Catalog Synchronization Examples

### 13. Export Full Catalog

**Request:**
```bash
curl -X GET http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "X-Full-Sync: true"
```

**Response:** (Abbreviated)
```json
{
  "metadata": {
    "client": "CLIENT001",
    "createdAt": "2024-09-30T06:35:18+02:00",
    "description": "Full catalog export",
    "syncType": "full",
    "totals": {
      "collections": 4,
      "configurableMetadatas": 2,
      "products": 3,
      "configurableProducts": 1
    }
  },
  "collections": [
    {
      "id": "COL001",
      "internalName": "electronics",
      "externalName": {
        "es": "Electrónica",
        "en": "Electronics"
      },
      "parentId": null,
      "active": true
    }
  ],
  "configurableMetadatas": [
    {
      "id": "color",
      "label": {
        "es": "Color",
        "en": "Color"
      },
      "options": [
        {
          "id": "gray",
          "value": {
            "es": "Gris Espacial",
            "en": "Space Gray"
          }
        }
      ]
    }
  ],
  "products": [...],
  "configurableProducts": [...]
}
```

### 14. Export Incremental Catalog

**Request:**
```bash
curl -X GET http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345"
```

**Response:**
```json
{
  "metadata": {
    "client": "CLIENT001",
    "createdAt": "2024-09-30T06:35:18+02:00",
    "description": "Incremental catalog export",
    "syncType": "incremental",
    "totals": {...}
  },
  ...
}
```

---

## ❌ Error Response Examples

### Invalid Token
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "error": "Expired token"
}
```

### Missing Authorization
```json
{
  "success": false,
  "message": "Authorization header not found"
}
```

### Invalid Request Data
```json
{
  "success": false,
  "message": "SKU is required"
}
```

### Product Not Available
```json
{
  "success": false,
  "message": "Product not available or insufficient stock",
  "data": {
    "sku": "PRODUCT-SKU",
    "requestedQty": 5,
    "availableStock": 2
  }
}
```

---

## 🔄 Complete Workflow Example

### Step-by-Step: From Login to Order

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.data.token')

# 2. Get personalized prices
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer $TOKEN"

# 3. Validate cart item
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sku":"IPAD-PRO-128-GRAY","qty":1}'

# 4. Get shipping options
curl -X GET "http://localhost:8090/cart/shipping-options?cartTotal=854.99" \
  -H "Authorization: Bearer $TOKEN"

# 5. Validate cart
curl -X POST http://localhost:8090/cart/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items":[{"sku":"IPAD-PRO-128-GRAY","qty":1}],
    "shippingAddressId":"ADDR001",
    "shippingMethodId":"STANDARD"
  }'

# 6. Confirm order
curl -X POST http://localhost:8090/cart/confirm \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items":[{"sku":"IPAD-PRO-128-GRAY","qty":1,"price":854.99}],
    "shippingAddressId":"ADDR001",
    "billingAddressId":"ADDR002",
    "shippingMethodId":"STANDARD",
    "paymentMethod":"credit_card",
    "paymentReference":"PAY123"
  }'
```

---

## 📝 Notes

- All timestamps are in ISO 8601 format
- Currency is always EUR in mock responses
- User tokens expire after 3600 seconds (1 hour)
- Integration token never expires
- All validation is mock - any reasonable data is accepted
- Multi-language fields support `es`, `en`, or simple strings
