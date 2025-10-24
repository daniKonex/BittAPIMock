# Biit API Mock – Integration Guide

This guide describes how to integrate with the Biit Mock API for KonexCommerce. Use it as context for implementing HTTP clients, mapping payloads, and building integration tests.

## Base URL and Authentication

- Base URL: http://localhost:8090
- Authorization:
  - Integration token (catalog export only):
    - Header: Authorization: Bearer konex-integration-token-12345
  - User token (everything else):
    - Obtain from POST /auth/login
    - Header: Authorization: Bearer <userToken>
- Content-Type: application/json for request bodies

## Important Notes

- The server expects Authorization headers; ensure your client sends them.
- Field names in catalog export use kebab-case (e.g., created-at, internal-name).
- Catalog full vs incremental behavior is controlled by header X-Full-Sync.

---

# Authentication

## Login
POST /auth/login

Request:
```json
{
  "email": "test@example.com",
  "password": "password"
}
```

Response (200):
```json
{
  "success": true,
  "data": {
    "token": "<JWT>",
    "tokenType": "Bearer",
    "expiresIn": 3600,
    "user": {
      "id": "USRxxxx",
      "email": "test@example.com",
      "name": "Test User"
    }
  }
}
```

Use data.token as your Bearer token for subsequent requests.

---

# Catalog Export (Integration)

Endpoint: GET /catalog/export  
Auth: Authorization: Bearer konex-integration-token-12345  
Header switch:
- Full export: add X-Full-Sync: true
- Incremental export: omit header

## Full Export
- Returns entire catalog sourced from rogen.json
- Updates metadata.created-at

Curl:
```bash
curl -s http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "X-Full-Sync: true"
```

Example top-level (truncated):
```json
{
  "konex-import": {
    "version": "1.0",
    "metadata": {
      "client": "Rogen",
      "created-at": "2025-10-01T00:00:00Z",
      "description": "...",
      "total-collections": 239,
      "total-configurable-metadatas": 0,
      "total-products": 3680,
      "total-configurable-products": 0
    },
    "collections": [...],
    "configurable-metadatas": [],
    "products": [...],
    "configurable-products": []
  }
}
```

## Incremental Export
- Picks 10 random products from rogen.json
- Appends “(Updated <ISO8601 timestamp>)” to each product name
- Preserves structure; only products array is reduced to 10
- Updates metadata.created-at and description

Curl:
```bash
curl -s http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345"
```

---

# Cart

## Shipping Options (GET-only)

Endpoint: GET /cart/shipping-options  
Auth: Authorization: Bearer <userToken>  
Inputs:
- Prefer query parameters:
  - cartTotal (number)
  - itemCount (integer)
- Alternatively (optional), GET with JSON body:
  - items[] with qty and price, and/or totals.total

Pricing rules:
- standard: 0 if total > 100, else 5
- express: 10 + (2 × itemCount)
- overnight: 20 + (2 × itemCount)

Examples:
- High total, 1 item (standard = 0):
```bash
curl -s "http://localhost:8090/cart/shipping-options?cartTotal=2650.45&itemCount=1" \
  -H "Authorization: Bearer <userToken>"
```

- Low total, 3 items (standard = 5):
```bash
curl -s "http://localhost:8090/cart/shipping-options?cartTotal=50&itemCount=3" \
  -H "Authorization: Bearer <userToken>"
```

Response:
```json
{
  "shipping_options": [
    { "id": "standard",  "name": "Standard Shipping",  "price": 0 },
    { "id": "express",   "name": "Express Shipping",   "price": 12 },
    { "id": "overnight", "name": "Overnight Shipping", "price": 22 }
  ]
}
```

## Item Validation

Endpoint: POST /cart/item/validate  
Auth: Authorization: Bearer <userToken>

Business rules:
- If sku contains “NOSTOCK” → 422 OUT_OF_STOCK
- If price < 100 → 422 PRICE_TOO_LOW
- Otherwise returns success with mock price/stock

Success request:
```json
{ "sku": "ELEC-0001", "qty": 1, "price": 2650.45 }
```

Success response:
```json
{
  "success": true,
  "data": {
    "sku": "ELEC-0001",
    "qty": 1,
    "price": 2650.45,
    "available": true,
    "stockAvailable": 100
  }
}
```

No stock example:
```json
{ "sku": "ELEC-NOSTOCK-001", "qty": 1, "price": 200 }
```

Response (422):
```json
{
  "success": false,
  "message": "Product is out of stock for the requested SKU",
  "error": { "code": "OUT_OF_STOCK", "details": { "sku": "ELEC-NOSTOCK-001", "requestedQty": 1 } }
}
```

Price too low example:
```json
{ "sku": "ELEC-0001", "qty": 1, "price": 99.99 }
```

Response (422):
```json
{
  "success": false,
  "message": "Price below the minimum allowed (100)",
  "error": { "code": "PRICE_TOO_LOW", "details": { "sku": "ELEC-0001", "price": 99.99, "minPrice": 100 } }
}
```

## Cart Validation

Endpoint: POST /cart/validate  
Auth: Authorization: Bearer <userToken>

Required:
- items[] with sku/qty (and price if you want totals computed)
- addresses.billing.country and addresses.shipping.country
- payment.payment_reference

Business rules:
- Missing payment_reference → 400 MISSING_PAYMENT_REFERENCE
- Country must be ES for both billing and shipping; otherwise → 422 UNSUPPORTED_COUNTRY

Success example:
```json
{
  "items": [{ "sku": "ELEC-0001", "qty": 24, "price": 2650.45 }],
  "totals": { "total": 2650.45, "discount": 265.045 },
  "addresses": {
    "billing":  { "country": "ES" },
    "shipping": { "country": "ES" }
  },
  "payment": { "method": "credit_card", "payment_reference": "1234567890" }
}
```

Unsupported country example:
```json
{
  "items": [{ "sku": "ELEC-0001", "qty": 24, "price": 2650.45 }],
  "addresses": {
    "billing":  { "country": "US" },
    "shipping": { "country": "US" }
  },
  "payment": { "method": "credit_card", "payment_reference": "ABC" }
}
```

Response (422):
```json
{
  "success": false,
  "message": "Only ES country is allowed for billing and shipping addresses",
  "error": {
    "code": "UNSUPPORTED_COUNTRY",
    "details": { "billingCountry": "US", "shippingCountry": "US", "allowed": "ES" }
  }
}
```

## Confirm Order

Endpoint: POST /cart/confirm  
Auth: Authorization: Bearer <userToken>  
Required: items[], shippingAddressId, paymentMethod

Response (201):
```json
{
  "success": true,
  "data": {
    "orderId": "ORD20251001xxxx",
    "orderNumber": "ORD20251001xxxx",
    "status": "confirmed",
    "createdAt": "2025-10-01T00:00:00Z",
    "total": 899.99,
    "currency": "EUR",
    "message": "Order confirmed successfully"
  }
}
```

---

# Quick Start Script (Local)

```bash
# 1) Login
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{ "email": "test@example.com", "password": "password" }' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")

# 2) Shipping options
curl -s "http://localhost:8090/cart/shipping-options?cartTotal=2650.45&itemCount=1" \
  -H "Authorization: Bearer $TOKEN" | python3 -m json.tool

# 3) Item validate (no stock)
curl -s -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{ "sku": "ELEC-NOSTOCK-001", "qty": 1, "price": 200 }' | python3 -m json.tool

# 4) Catalog incremental (integration token)
curl -s http://localhost:8090/catalog/export \
  -H "Authorization: Bearer konex-integration-token-12345" | python3 -m json.tool
```

---

# Postman Collection Variables

- baseUrl: http://localhost:8090
- integrationToken: konex-integration-token-12345
- userToken: set programmatically from Login response

The collection can include examples for:
- Catalog export (full/incremental)
- Cart shipping options (high total/low total)
- Item validation (success + two error cases)
- Cart validation (success + missing payment + unsupported country)
- Confirm order
