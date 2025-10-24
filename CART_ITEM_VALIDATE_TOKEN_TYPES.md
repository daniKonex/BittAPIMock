# Cart Item Validation - Token Types

## Overview

The `/cart/item/validate` endpoint accepts **two types of tokens** to support different validation flows:

1. **User/Guest Token** (JWT): Full validation including stock and pricing checks
2. **Integration Token**: Stock-only validation (no pricing checks)

The system automatically detects which token type is provided and applies the appropriate validation logic.

## Token Types

### 1. User/Guest Token (Full Validation)

**When to use:** When a logged-in user or guest is adding items to their cart

**Validation performed:**
- ✅ Stock availability check
- ✅ Price vs cost validation (prevents selling below cost)
- ✅ Product existence check

**Example:**
```bash
# Login first
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@konex.com"}' \
  | jq -r '.data.token')

# Validate cart item with user token
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 10,
    "price": 0.20
  }'
```

**Response (success):**
```json
{
  "success": true,
  "data": {
    "sku": "NUT-HEX-M6-125",
    "qty": 10,
    "price": 0.20,
    "available": true,
    "stockAvailable": 5
  }
}
```

**Response (price too low):**
```json
{
  "success": false,
  "message": "Price below product cost",
  "error": {
    "code": "PRICE_TOO_LOW",
    "details": {
      "sku": "NUT-HEX-M6-125",
      "price": 0.05,
      "cost": 0.10
    }
  }
}
```

### 2. Integration Token (Stock-Only Validation)

**When to use:** When external systems need to check stock availability before user login

**Validation performed:**
- ✅ Stock availability check
- ✅ Product existence check
- ❌ NO price validation (allows any price)

**Example:**
```bash
# Use integration token directly
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 10,
    "price": 0.05
  }'
```

**Response (success - even with low price):**
```json
{
  "success": true,
  "data": {
    "sku": "NUT-HEX-M6-125",
    "qty": 10,
    "price": 0.05,
    "available": true,
    "stockAvailable": 5
  }
}
```

## How It Works

The system automatically detects the token type:

1. **Request arrives** with Authorization header
2. **AuthFilter checks** if it's an integration token
   - If yes → marks request as `tokenType: 'integration'`
   - If no → validates as JWT and marks as `tokenType: 'user'`
3. **Cart controller** checks token type
   - Integration token → Skip price validation
   - User token → Full validation including price checks

## Use Cases

### Use Case 1: Guest Browsing (Integration Token)
```
User browses products → System checks stock → No login required
```

### Use Case 2: Logged-in User Adding to Cart (User Token)
```
User logs in → Gets user token → Adds to cart → Full validation (stock + price)
```

### Use Case 3: External System Integration (Integration Token)
```
External system → Checks stock availability → No user context needed
```

## Testing

### Test 1: User Token with Valid Price
```bash
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@konex.com"}' \
  | jq -r '.data.token')

curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sku":"NUT-HEX-M6-125","qty":1,"price":0.20}' \
  | jq '.'
```

### Test 2: User Token with Price Below Cost (Should Fail)
```bash
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sku":"NUT-HEX-M6-125","qty":1,"price":0.05}' \
  | jq '.'
```

### Test 3: Integration Token with Price Below Cost (Should Pass)
```bash
curl -X POST http://localhost:8090/cart/item/validate \
  -H "Authorization: Bearer konex-integration-token-12345" \
  -H "Content-Type: application/json" \
  -d '{"sku":"NUT-HEX-M6-125","qty":1,"price":0.05}' \
  | jq '.'
```

## Configuration

Integration token is configured in `.env`:
```
INTEGRATION_TOKEN=konex-integration-token-12345
```

## Logs

The system logs which token type is being used:
```
INFO - Cart item validation - Token type: Integration
INFO - Cart item validation - Token type: User
```
