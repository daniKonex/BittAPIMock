#!/bin/bash

API_URL="http://localhost:8090"
INTEGRATION_TOKEN="konex-integration-token-12345"

echo "=== Testing Cart Item Validation with Different Token Types ==="
echo ""

# Test 1: User token with valid price (should pass)
echo "Test 1: User Token + Valid Price (should PASS)"
USER_TOKEN=$(curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@konex.com"}' \
  | jq -r '.data.token')

curl -s -X POST $API_URL/cart/item/validate \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 1,
    "price": 0.20
  }' | jq '{success, message: .message // "OK", sku: .data.sku // null, price: .data.price // null}'

echo ""
echo "---"
echo ""

# Test 2: User token with price below cost (should FAIL)
echo "Test 2: User Token + Price Below Cost (should FAIL)"
curl -s -X POST $API_URL/cart/item/validate \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 1,
    "price": 0.05
  }' | jq '{success, message, error}'

echo ""
echo "---"
echo ""

# Test 3: Integration token with price below cost (should PASS - no price check)
echo "Test 3: Integration Token + Price Below Cost (should PASS - stock only)"
curl -s -X POST $API_URL/cart/item/validate \
  -H "Authorization: Bearer $INTEGRATION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 1,
    "price": 0.05
  }' | jq '{success, message: .message // "OK", sku: .data.sku // null, price: .data.price // null}'

echo ""
echo "---"
echo ""

# Test 4: Integration token with out of stock (should FAIL)
echo "Test 4: Integration Token + Out of Stock (should FAIL)"
curl -s -X POST $API_URL/cart/item/validate \
  -H "Authorization: Bearer $INTEGRATION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 1000,
    "price": 0.05
  }' | jq '{success, message, error}'

echo ""
echo "=== Tests Complete ==="
echo ""
echo "Summary:"
echo "- User tokens: Full validation (stock + price)"
echo "- Integration tokens: Stock-only validation (no price check)"
