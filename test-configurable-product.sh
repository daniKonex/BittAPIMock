#!/bin/bash

API_URL="http://localhost:8090"

echo "=== Testing Configurable Product Variant SKU ==="
echo ""

# Login first
echo "Step 1: Login"
TOKEN=$(curl -s -X POST $API_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@konex.com","password":"password"}' \
  | jq -r '.data.token')

echo "Token: ${TOKEN:0:50}..."
echo ""

# Test validating a configurable product variant
echo "Step 2: Validate configurable product variant SKU (NUT-HEX-M6-125)"
curl -s -X POST $API_URL/cart/item/validate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "sku": "NUT-HEX-M6-125",
    "qty": 1,
    "price": 0.2
  }' | jq '.'

echo ""
echo "=== Test Complete ==="
