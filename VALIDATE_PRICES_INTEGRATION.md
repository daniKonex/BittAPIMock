# Prices Endpoint - Integration Guide

## Overview
This endpoint is designed to be called **asynchronously** by 3rd party systems after user login to retrieve personalized pricing with special discounts based on user email domain.

## Endpoint Details

- **URL:** `GET /me/prices`
- **Authentication:** Required (User Access Token)

## Special Discount Rules

- Users with email ending in `@konex.com` or `@konex.es` receive **15% discount** on all products
- Other users receive standard pricing (0% discount)

## Integration Flow

```
1. User logs in via /auth/login
2. Your system receives the user token and email
3. Your system makes async call to /me/prices
4. System receives minimalistic price list with applied discounts
5. Cache/store prices for the user session
```

## cURL Examples

### Example 1: Login and Get Prices (Complete Flow)

```bash
# Step 1: Login with @konex.com email (gets 15% discount)
curl -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@konex.com",
    "password": "password123"
  }'

# Response will include token:
# {
#   "success": true,
#   "data": {
#     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
#     "user": { ... }
#   }
# }

# Step 2: Use the token to get prices
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### Example 2: One-Liner with Token Extraction (Bash)

```bash
# Login and extract token, then get prices
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@konex.com","password":"pass123"}' \
  | jq -r '.data.token')

# Call prices endpoint with the token
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'
```

### Example 3: User WITHOUT Discount

```bash
# Login with non-konex email
TOKEN=$(curl -s -X POST http://localhost:8090/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"pass123"}' \
  | jq -r '.data.token')

# Get prices (no discount applied)
curl -X GET http://localhost:8090/me/prices \
  -H "Authorization: Bearer $TOKEN"
```

## Response Format

### Success Response (with discount)

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
      }
    ]
  }
}
```

### Success Response (without discount)

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
      }
    ]
  }
}
```

## Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if request was successful |
| `data.clientId` | string | Client identifier |
| `data.currency` | string | Currency code (always "EUR") |
| `data.totalProducts` | number | Number of products in response |
| `data.prices` | array | Array of product prices |
| `data.prices[].id` | string | Product ID |
| `data.prices[].sku` | string | Product SKU |
| `data.prices[].price` | number | Base price |
| `data.prices[].finalPrice` | number | Final price (with discount applied if user has @konex.com email) |

## Error Responses

### Invalid/Missing Token

```json
{
  "success": false,
  "message": "Authorization header not found"
}
```

### Expired Token

```json
{
  "success": false,
  "message": "Invalid or expired token",
  "error": "Expired token"
}
```

## Implementation Notes

### Performance Optimization
- Response returns **50 products** by default to keep payload size manageable
- Only essential fields returned: `id`, `sku`, `price`, `finalPrice`
- No product descriptions, images, or metadata included

### Caching Recommendations
- Cache the response for the user session
- Refresh when user logs in again
- Consider TTL of 1 hour to match token expiration

### Async Implementation Example (Node.js)

```javascript
async function getUserPrices(userToken, userEmail) {
  try {
    const response = await fetch('http://localhost:8090/me/prices', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${userToken}`
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store prices in cache/session
      await cacheUserPrices(userEmail, data.data.prices);
      
      console.log(`Total products: ${data.data.totalProducts}`);
    }
    
    return data;
  } catch (error) {
    console.error('Failed to get prices:', error);
    throw error;
  }
}
```

### Async Implementation Example (Python)

```python
import requests
import asyncio

async def get_user_prices(user_token: str, user_email: str):
    """
    Asynchronously retrieve user-specific prices
    """
    url = "http://localhost:8090/me/prices"
    headers = {
        "Authorization": f"Bearer {user_token}"
    }
    
    try:
        response = requests.get(url, headers=headers)
        data = response.json()
        
        if data.get("success"):
            # Cache prices for user session
            prices = data["data"]["prices"]
            
            print(f"Total products: {len(prices)}")
            
            return data
    except Exception as e:
        print(f"Failed to get prices: {e}")
        raise
```

### Async Implementation Example (PHP)

```php
<?php

function getUserPrices($userToken, $userEmail) {
    $url = 'http://localhost:8090/me/prices';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if ($data['success']) {
            // Cache prices in session or Redis
            $_SESSION['user_prices'] = $data['data']['prices'];
            
            return $data;
        }
    }
    
    return null;
}
```

## Testing

### Quick Test Script

Save this as `test-prices.sh`:

```bash
#!/bin/bash

API_URL="http://localhost:8090"

echo "=== Testing Prices Endpoint ==="
echo ""

# Test 1: User with @konex.com (should get 15% discount)
echo "Test 1: User with @konex.com email (15% discount expected)"
TOKEN=$(curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@konex.com","password":"password"}' \
  | jq -r '.data.token')

echo "Token: ${TOKEN:0:50}..."
echo ""

curl -s -X GET $API_URL/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  | jq '{
      clientId: .data.clientId,
      totalProducts: .data.totalProducts,
      samplePrice: .data.prices[0]
    }'

echo ""
echo "---"
echo ""

# Test 2: User without @konex.com (no discount)
echo "Test 2: User with regular email (no discount expected)"
TOKEN=$(curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.data.token')

echo "Token: ${TOKEN:0:50}..."
echo ""

curl -s -X GET $API_URL/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  | jq '{
      clientId: .data.clientId,
      totalProducts: .data.totalProducts,
      samplePrice: .data.prices[0]
    }'

echo ""
echo "=== Tests Complete ==="
```

Make it executable and run:
```bash
chmod +x test-prices.sh
./test-prices.sh
```

## Support

For questions or issues, refer to:
- Main API documentation: `API_EXAMPLES.md`
- Sample response: `sample_validate_prices_response.json`
- Project README: `README.md`
