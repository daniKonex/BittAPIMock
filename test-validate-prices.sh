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

curl -s -X POST $API_URL/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
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

curl -s -X POST $API_URL/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  | jq '{
      clientId: .data.clientId,
      totalProducts: .data.totalProducts,
      samplePrice: .data.prices[0]
    }'

echo ""
echo "=== Tests Complete ==="
