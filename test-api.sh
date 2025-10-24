#!/bin/bash

# Biit API Mock - Quick Test Script
# Usage: ./test-api.sh

BASE_URL="http://localhost:8090"
INTEGRATION_TOKEN="konex-integration-token-12345"

echo "=========================================="
echo "Biit API Mock - Quick Test"
echo "=========================================="
echo ""

# Test 1: Login
echo "1. Testing Login..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}')

echo "$LOGIN_RESPONSE" | jq '.'

# Extract token
USER_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')

if [ "$USER_TOKEN" != "null" ] && [ -n "$USER_TOKEN" ]; then
  echo "✅ Login successful!"
  echo "Token: ${USER_TOKEN:0:50}..."
else
  echo "❌ Login failed!"
  exit 1
fi

echo ""
echo "=========================================="
echo ""

# Test 2: Get Prices
echo "2. Testing Get Prices..."
curl -s -X GET "$BASE_URL/me/prices" \
  -H "Authorization: Bearer $USER_TOKEN" | jq '.'

echo ""
echo "=========================================="
echo ""

# Test 3: Validate Cart Item
echo "3. Testing Cart Item Validation..."
curl -s -X POST "$BASE_URL/cart/item/validate" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sku":"IPAD-PRO-128-GRAY","qty":1}' | jq '.'

echo ""
echo "=========================================="
echo ""

# Test 4: Export Catalog
echo "4. Testing Catalog Export..."
curl -s -X GET "$BASE_URL/catalog/export" \
  -H "Authorization: Bearer $INTEGRATION_TOKEN" \
  -H "X-Full-Sync: true" | jq '.metadata'

echo ""
echo "=========================================="
echo ""

# Test 5: Get Shipping Options
echo "5. Testing Shipping Options..."
curl -s -X GET "$BASE_URL/cart/shipping-options?cartTotal=150&shippingAddressId=ADDR001" \
  -H "Authorization: Bearer $USER_TOKEN" | jq '.'

echo ""
echo "=========================================="
echo ""

echo "✅ All tests completed!"
echo ""
echo "For more tests, import the postman_collection.json file into Postman"
