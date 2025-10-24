<?php

namespace App\Controllers;

class Cart extends BaseController
{
    /**
     * POST /cart/item/validate
     * Validates a product before adding to cart (stock, price, restrictions)
     * 
     * Accepts both User tokens and Integration tokens:
     * - User token: Full validation (stock + pricing checks)
     * - Integration token: Stock-only validation (no pricing checks)
     */
    public function validateItem()
    {
        $data = $this->request->getJSON(true);
        
        $sku = $data['sku'] ?? '';
        $qty = $data['qty'] ?? 1;
        $price = isset($data['price']) ? (float) $data['price'] : null;

        if (empty($sku)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'SKU is required'
            ], 400);
        }
        
        // Determine token type
        $isIntegrationToken = $this->isIntegrationToken();
        log_message('info', 'Cart item validation - Token type: ' . ($isIntegrationToken ? 'Integration' : 'User'));

        // Business rules
        if (stripos($sku, 'NOSTOCK') !== false) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Product is out of stock for the requested SKU',
                'error' => [
                    'code' => 'OUT_OF_STOCK',
                    'details' => [
                        'sku' => $sku,
                        'requestedQty' => $qty
                    ]
                ]
            ], 422);
        }

        // Load catalog data and validate against real stock/cost
        $catalogPath = ROOTPATH . 'rogen.json';
        if (!is_file($catalogPath)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Catalog file not found',
                'error' => [
                    'code' => 'CATALOG_NOT_FOUND'
                ]
            ], 500);
        }

        $raw = @file_get_contents($catalogPath);
        $cat = @json_decode($raw, true);
        $found = null;
        
        // First, search in simple products
        if (is_array($cat) && isset($cat['konex-import']['products']) && is_array($cat['konex-import']['products'])) {
            foreach ($cat['konex-import']['products'] as $p) {
                if (($p['sku'] ?? '') === $sku) {
                    $found = $p;
                    break;
                }
            }
        }
        
        // If not found in simple products, search in configurable products' options
        if ($found === null && is_array($cat) && isset($cat['konex-import']['configurable-products']) && is_array($cat['konex-import']['configurable-products'])) {
            foreach ($cat['konex-import']['configurable-products'] as $configurableProduct) {
                if (isset($configurableProduct['options']) && is_array($configurableProduct['options'])) {
                    foreach ($configurableProduct['options'] as $option) {
                        if (($option['sku'] ?? '') === $sku) {
                            $found = $option;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
        }

        if ($found === null) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'SKU not found in catalog',
                'error' => [
                    'code' => 'SKU_NOT_FOUND',
                    'details' => [ 'sku' => $sku ]
                ]
            ], 404);
        }

        $availableStock = (int)($found['qty'] ?? 0);
        $catalogPrice = isset($found['price']) ? (float)$found['price'] : null;
        $catalogCost = isset($found['cost']) ? (float)$found['cost'] : null;

        if ($availableStock < $qty || $availableStock <= 0) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Product not available or insufficient stock',
                'error' => [
                    'code' => 'OUT_OF_STOCK',
                    'details' => [
                        'sku' => $sku,
                        'requestedQty' => $qty,
                        'availableStock' => $availableStock
                    ]
                ]
            ], 422);
        }

        // Enforce price >= cost when a price is provided in request and cost exists
        // Skip price validation for integration tokens (stock-only mode)
        if (!$isIntegrationToken && $price !== null && $catalogCost !== null && $price < $catalogCost) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Price below product cost',
                'error' => [
                    'code' => 'PRICE_TOO_LOW',
                    'details' => [
                        'sku' => $sku,
                        'price' => (float)$price,
                        'cost' => $catalogCost
                    ]
                ]
            ], 422);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'sku' => $sku,
                'qty' => $qty,
                'price' => $catalogPrice ?? ($price ?? 0),
                'available' => true,
                'stockAvailable' => $availableStock
            ]
        ]);
    }

    /**
     * POST /cart/validate
     * Validates the entire cart before checkout (stock, prices, shipping)
     */
    public function validateCart()
    {
        $data = $this->request->getJSON(true);
        
        $items = $data['items'] ?? [];
        $addresses = $data['addresses'] ?? [];
        $payment = $data['payment'] ?? [];
        $shippingAddressId = $data['shippingAddressId'] ?? '';
        $shippingMethodId = $data['shippingMethodId'] ?? '';

        if (empty($items)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Business rules (field-optional)
        // - Payment reference: only require if payment object is present in payload
        if (array_key_exists('payment', $data)) {
            $paymentRef = $payment['payment_reference'] ?? '';
            if ($paymentRef === '') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Payment reference is required when payment is provided',
                    'error' => [
                        'code' => 'MISSING_PAYMENT_REFERENCE'
                    ]
                ], 400);
            }
        }

        // - Country validation: only validate countries that are provided
        if (isset($addresses['billing']['country'])) {
            $billingCountry = strtoupper((string)$addresses['billing']['country']);
            if ($billingCountry !== 'ES') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Only ES country is allowed for billing address',
                    'error' => [
                        'code' => 'UNSUPPORTED_COUNTRY',
                        'details' => [
                            'billingCountry' => $billingCountry,
                            'allowed' => 'ES'
                        ]
                    ]
                ], 422);
            }
        }
        if (isset($addresses['shipping']['country'])) {
            $shippingCountry = strtoupper((string)$addresses['shipping']['country']);
            if ($shippingCountry !== 'ES') {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Only ES country is allowed for shipping address',
                    'error' => [
                        'code' => 'UNSUPPORTED_COUNTRY',
                        'details' => [
                            'shippingCountry' => $shippingCountry,
                            'allowed' => 'ES'
                        ]
                    ]
                ], 422);
            }
        }

        // Build cost map from catalog (rogen.json) to validate prices against cost
        $costBySku = [];
        $catalogPath = ROOTPATH . 'rogen.json';
        if (is_file($catalogPath)) {
            $raw = @file_get_contents($catalogPath);
            $cat = @json_decode($raw, true);
            
            // Add simple products to cost map
            if (is_array($cat) && isset($cat['konex-import']['products'])) {
                foreach ($cat['konex-import']['products'] as $p) {
                    $sku = $p['sku'] ?? null;
                    $cost = isset($p['cost']) ? (float)$p['cost'] : null;
                    if ($sku && $cost !== null) {
                        $costBySku[$sku] = $cost;
                    }
                }
            }
            
            // Add configurable product options to cost map
            if (is_array($cat) && isset($cat['konex-import']['configurable-products'])) {
                foreach ($cat['konex-import']['configurable-products'] as $configurableProduct) {
                    if (isset($configurableProduct['options']) && is_array($configurableProduct['options'])) {
                        foreach ($configurableProduct['options'] as $option) {
                            $sku = $option['sku'] ?? null;
                            $cost = isset($option['cost']) ? (float)$option['cost'] : null;
                            if ($sku && $cost !== null) {
                                $costBySku[$sku] = $cost;
                            }
                        }
                    }
                }
            }
        }

        // Mock validation - check all items
        $validatedItems = [];
        $subtotal = 0;
        $errors = [];

        foreach ($items as $item) {
            $sku = $item['sku'] ?? '';
            $qty = $item['qty'] ?? 1;
            $price = isset($item['price']) ? (float)$item['price'] : null;

            if (empty($sku)) {
                $errors[] = 'SKU is required for all items';
                continue;
            }

            // Business rule: never allow prices under cost
            $cost = $costBySku[$sku] ?? null;
            if ($price !== null && $cost !== null && $price < $cost) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Price below product cost',
                    'error' => [
                        'code' => 'PRICE_TOO_LOW',
                        'details' => [
                            'sku' => $sku,
                            'price' => $price,
                            'cost' => $cost
                        ]
                    ]
                ], 422);
            }

            // Mock product validation
            $validatedItems[] = [
                'sku' => $sku,
                'qty' => $qty,
                'price' => $price !== null ? $price : 99.99,
                'subtotal' => ($price !== null ? $price : 99.99) * $qty,
                'valid' => true
            ];
            $subtotal += ($price !== null ? $price : 99.99) * $qty;
        }

        if (!empty($errors)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Cart validation failed',
                'errors' => $errors
            ], 422);
        }

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'valid' => true,
                'items' => $validatedItems,
                'subtotal' => $subtotal,
                'shippingCost' => 5.99,
                'taxAmount' => $subtotal * 0.21,
                'total' => $subtotal + 5.99 + ($subtotal * 0.21),
                'currency' => 'EUR',
                'shippingAddressValid' => !empty($shippingAddressId),
                'shippingMethodValid' => !empty($shippingMethodId)
            ]
        ]);
    }

    /**
     * GET /cart/shipping-options
     * Returns available shipping methods based on cart and client
     */
    public function shippingOptions()
    {
        // Support both GET (query params) and POST (JSON body)
        $payload = $this->request->getJSON(true) ?? [];

        // Derive item count
        $items = $payload['items'] ?? [];
        $itemCount = 0;
        foreach ($items as $it) {
            $itemCount += (int)($it['qty'] ?? 0);
        }
        if ($itemCount === 0) {
            // fallback from GET query (single item)
            $itemCount = (int)($this->request->getGet('itemCount') ?? 0);
            if ($itemCount <= 0) {
                $itemCount = 1; // sensible default
            }
        }

        // Derive total
        $total = 0.0;
        if (isset($payload['totals']['total'])) {
            $total = (float)$payload['totals']['total'];
        } elseif (!empty($items)) {
            // compute from items if totals not provided
            foreach ($items as $it) {
                $qty = (int)($it['qty'] ?? 0);
                $price = (float)($it['price'] ?? 0);
                $total += $qty * $price;
            }
        } else {
            $total = (float)($this->request->getGet('cartTotal') ?? 0);
        }

        // Pricing rules
        $standardPrice  = $total > 100 ? 0 : 5;
        $expressPrice   = 10 + (2 * $itemCount);
        $overnightPrice = 20 + (2 * $itemCount);

        // Response aligned to provided sample get_shipping_options_response.json
        $response = [
            'shipping_options' => [
                [
                    'id' => 'standard',
                    'name' => 'Standard Shipping',
                    'price' => $standardPrice,
                ],
                [
                    'id' => 'express',
                    'name' => 'Express Shipping',
                    'price' => $expressPrice,
                ],
                [
                    'id' => 'overnight',
                    'name' => 'Overnight Shipping',
                    'price' => $overnightPrice,
                ],
            ],
        ];

        return $this->jsonResponse($response);
    }

    /**
     * POST /cart/confirm
     * Confirms and creates the order in Sage after payment
     */
    public function confirm()
    {
        $data = $this->request->getJSON(true);
        
        $items = $data['items'] ?? [];
        $shippingAddressId = $data['shippingAddressId'] ?? '';
        $billingAddressId = $data['billingAddressId'] ?? '';
        $shippingMethodId = $data['shippingMethodId'] ?? '';
        $paymentMethod = $data['paymentMethod'] ?? '';
        $paymentReference = $data['paymentReference'] ?? '';

        // Map alternative fields from external payloads
        if ($paymentMethod === '' && isset($data['payment']['method'])) {
            $paymentMethod = (string)$data['payment']['method'];
        }
        if ($paymentReference === '' && isset($data['payment']['payment_reference'])) {
            $paymentReference = (string)$data['payment']['payment_reference'];
        }
        if ($shippingMethodId === '' && isset($data['shipping_option'])) {
            $shippingMethodId = (string)$data['shipping_option'];
        }

        // Allow inline shipping address object as an alternative to shippingAddressId
        $hasInlineShippingAddress = isset($data['addresses']['shipping']) && is_array($data['addresses']['shipping']);

        // Collect missing fields with guidance
        $missing = [];
        if (empty($items)) {
            $missing[] = 'items (array)';
        }
        if (empty($shippingAddressId) && !$hasInlineShippingAddress) {
            $missing[] = 'shippingAddressId (string) or addresses.shipping (object)';
        }
        if ($paymentMethod === '') {
            $missing[] = 'paymentMethod (string) or payment.method (string)';
        }

        if (!empty($missing)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Missing required fields',
                'error' => [
                    'code' => 'MISSING_FIELDS',
                    'details' => [
                        'missing' => $missing,
                        'expected' => [
                            'items' => 'Array of { sku, qty, price }',
                            'shippingAddressId' => 'String identifier or provide addresses.shipping object',
                            'paymentMethod' => 'String (e.g., credit_card, transferencia)'
                        ],
                        'alternatives' => [
                            'payment.method -> paymentMethod',
                            'payment.payment_reference -> paymentReference',
                            'shipping_option -> shippingMethodId'
                        ]
                    ]
                ]
            ], 400);
        }

        // Generate mock order
        $orderId = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'orderId' => $orderId,
                'orderNumber' => $orderId,
                'status' => 'confirmed',
                'createdAt' => date('c'),
                'total' => 899.99,
                'currency' => 'EUR',
                'message' => 'Order confirmed successfully'
            ]
        ], 201);
    }

    /**
     * Helper method to determine if the current request uses an integration token
     * @return bool
     */
    private function isIntegrationToken(): bool
    {
        // Check if tokenType was set by AuthFilter
        return isset($this->request->tokenType) && $this->request->tokenType === 'integration';
    }
}
