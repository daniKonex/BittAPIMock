<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * GET /
     * Health check and API information
     */
    public function index()
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Biit API Mock - KonexCommerce Integration',
            'version' => '1.0.0',
            'status' => 'running',
            'timestamp' => date('c'),
            'endpoints' => [
                'authentication' => [
                    'POST /auth/login',
                    'POST /auth/guest-register'
                ],
                'token_management' => [
                    'POST /token/force-login',
                    'POST /token/refresh'
                ],
                'user_client' => [
                    'GET /me/prices',
                    'GET /me/catalog',
                    'GET /me/addresses',
                    'GET /me/orders'
                ],
                'cart' => [
                    'POST /cart/item/validate',
                    'POST /cart/validate',
                    'GET /cart/shipping-options',
                    'POST /cart/confirm'
                ],
                'catalog' => [
                    'GET /catalog/export'
                ]
            ],
            'documentation' => 'See README.md for full API documentation'
        ]);
    }
}
