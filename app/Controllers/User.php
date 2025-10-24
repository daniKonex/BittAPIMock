<?php

namespace App\Controllers;

class User extends BaseController
{
    /**
     * GET /me/prices
     * Returns personalized prices for the authenticated client
     * Called asynchronously by 3rd party system after user login
     * Returns minimalistic product data (id, sku, price, finalPrice)
     * 
     * Special discount: Users with @konex.com or @konex.es email get 15% discount
     */
    public function prices()
    {
        $user = $this->request->user;
        
        // Load Rogen catalog
        $catalogPath = ROOTPATH . 'rogen.json';
        $catalog = json_decode(file_get_contents($catalogPath), true);
        $allProducts = $catalog['konex-import']['products'] ?? [];
        
        // Determine if user gets special discount
        // Check for @konex.com or @konex.es domains
        $userEmail = strtolower($user->email);
        $hasSpecialDiscount = str_ends_with($userEmail, '@konex.com') || str_ends_with($userEmail, '@konex.es');
        $discountPercent = $hasSpecialDiscount ? 15 : 0;
        
        // Log for debugging
        log_message('info', 'Prices endpoint called - Email: ' . $user->email . ', Discount applied: ' . ($hasSpecialDiscount ? 'YES' : 'NO'));
        
        // Select a subset of products (first 50 for performance)
        $selectedProducts = array_slice($allProducts, 0, 50);
        
        // Build minimalistic price list
        $priceList = [];
        foreach ($selectedProducts as $product) {
            $basePrice = (float)($product['price'] ?? 0);
            $finalPrice = $hasSpecialDiscount 
                ? round($basePrice * 0.85, 2) // 15% discount
                : $basePrice;
            
            $priceList[] = [
                'id' => $product['id'],
                'sku' => $product['sku'],
                'price' => $basePrice,
                'finalPrice' => $finalPrice
            ];
        }
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'clientId' => $user->clientId,
                'currency' => 'EUR',
                'totalProducts' => count($priceList),
                'prices' => $priceList
            ]
        ]);
    }

    /**
     * GET /me/catalog
     * Returns filtered catalog based on client permissions
     */
    public function catalog()
    {
        $user = $this->request->user;
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'clientId' => $user->clientId,
                'allowedCategories' => ['electronics', 'computers', 'tablets'],
                'products' => [
                    [
                        'id' => 'PROD001',
                        'sku' => 'IPAD-PRO-128-GRAY',
                        'name' => 'iPad Pro 128GB Space Gray',
                        'available' => true,
                        'categories' => ['tablets', 'electronics']
                    ],
                    [
                        'id' => 'PROD002',
                        'sku' => 'IPAD-PRO-256-GRAY',
                        'name' => 'iPad Pro 256GB Space Gray',
                        'available' => true,
                        'categories' => ['tablets', 'electronics']
                    ]
                ]
            ]
        ]);
    }

    /**
     * GET /me/addresses
     * Returns shipping and billing addresses for the client
     */
    public function addresses()
    {
        $user = $this->request->user;
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'clientId' => $user->clientId,
                'addresses' => [
                    [
                        'id' => 'ADDR001',
                        'type' => 'shipping',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'company' => 'Test Company',
                        'street' => 'Calle Mayor 123',
                        'city' => 'Madrid',
                        'postalCode' => '28001',
                        'country' => 'ES',
                        'phone' => '+34600000000',
                        'isDefault' => true
                    ],
                    [
                        'id' => 'ADDR002',
                        'type' => 'billing',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'company' => 'Test Company',
                        'street' => 'Calle Mayor 123',
                        'city' => 'Madrid',
                        'postalCode' => '28001',
                        'country' => 'ES',
                        'phone' => '+34600000000',
                        'taxId' => 'B12345678',
                        'isDefault' => true
                    ]
                ]
            ]
        ]);
    }

    /**
     * GET /me/orders
     * Returns order history for the client
     */
    public function orders()
    {
        $user = $this->request->user;
        
        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'clientId' => $user->clientId,
                'orders' => [
                    [
                        'orderId' => 'ORD2024001',
                        'orderDate' => '2024-01-15T10:30:00Z',
                        'status' => 'delivered',
                        'total' => 1854.99,
                        'currency' => 'EUR',
                        'itemCount' => 2
                    ],
                    [
                        'orderId' => 'ORD2024002',
                        'orderDate' => '2024-02-20T14:15:00Z',
                        'status' => 'shipped',
                        'total' => 799.99,
                        'currency' => 'EUR',
                        'itemCount' => 1
                    ],
                    [
                        'orderId' => 'ORD2024003',
                        'orderDate' => '2024-03-10T09:00:00Z',
                        'status' => 'processing',
                        'total' => 2499.99,
                        'currency' => 'EUR',
                        'itemCount' => 3
                    ]
                ]
            ]
        ]);
    }

}
