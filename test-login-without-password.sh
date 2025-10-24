#!/bin/bash

API_URL="http://localhost:8090"

echo "=== Testing Login Without Password ==="
echo ""

# Test 1: Login with only email (no password)
echo "Test 1: Login with email only (password optional)"
curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"daniel@konex.es"}' \
  | jq '.'

echo ""
echo "---"
echo ""

# Test 2: Login with email and password (still works)
echo "Test 2: Login with email and password (backward compatible)"
curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@konex.com","password":"password123"}' \
  | jq '.'

echo ""
echo "---"
echo ""

# Test 3: Use token from email-only login to get prices
echo "Test 3: Use token from email-only login to call /me/prices"
TOKEN=$(curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"daniel@konex.es"}' \
  | jq -r '.data.token')

echo "Token obtained: ${TOKEN:0:50}..."
echo ""

curl -s -X POST $API_URL/me/prices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  | jq '{
      clientId: .data.clientId,
      totalProducts: .data.totalProducts,
      firstProduct: .data.prices[0]
    }'

echo ""
echo "=== Tests Complete ==="
