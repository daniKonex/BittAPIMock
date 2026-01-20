<?php

namespace App\Controllers;

use Firebase\JWT\JWT;
use App\Data\CommercialClientsMapping;

class Auth extends BaseController
{
    /**
     * POST /auth/login
     * Authenticates user and returns User Access Token
     * Note: Password is optional - authentication happens in commerce platform before this call
     */
    public function login()
    {
        $data = $this->request->getJSON(true);
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? ''; // Optional - auth already done in commerce platform

        // Only email is required - password check happens in commerce platform
        if (empty($email)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        // Generate mock user data
        $userId = 'USR' . rand(1000, 9999);
        $clientId = 'CLI' . rand(1000, 9999);
        
        // Check if email contains "comercial" to determine if it's a commercial user
        $isCommercial = stripos($email, 'comercial') !== false;
        
        // Generate allowed clients based on user type
        if ($isCommercial) {
            // Commercial users get 3 clients with consistent mapping
            $allowedClients = CommercialClientsMapping::getClientsForEmail($email);
            // Use first client's ID as default clientId
            $clientId = $allowedClients[0]['clientId'];
        } else {
            // Regular users get single client
            $allowedClients = [
                [
                    'clientId' => $clientId,
                    'name' => 'Main Client',
                    'canCreateOrders' => true
                ]
            ];
        }
        
        // Generate JWT token
        $token = $this->generateToken([
            'userId' => $userId,
            'clientId' => $clientId,
            'email' => $email,
            'type' => 'user'
        ]);

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => (int)getenv('JWT_TIME_TO_LIVE'),
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'name' => $isCommercial ? 'Usuario Comercial' : 'Test User',
                    'clientId' => $clientId,
                    'roles' => ['customer'],
                    'allowedClients' => $allowedClients
                ]
            ]
        ]);
    }

    /**
     * POST /auth/guest-register
     * Registers a guest user and returns User Access Token
     */
    public function guestRegister()
    {
        $data = $this->request->getJSON(true);
        
        $email = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';

        if (empty($email) || empty($name)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email and name are required'
            ], 400);
        }

        // Generate mock user data
        $userId = 'USR' . rand(1000, 9999);
        $clientId = 'CLI' . rand(1000, 9999);
        
        // Generate JWT token
        $token = $this->generateToken([
            'userId' => $userId,
            'clientId' => $clientId,
            'email' => $email,
            'type' => 'user'
        ]);

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => (int)getenv('JWT_TIME_TO_LIVE'),
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                    'clientId' => $clientId,
                    'roles' => ['customer']
                ]
            ]
        ]);
    }

    private function generateToken($payload)
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + (int)getenv('JWT_TIME_TO_LIVE');
        
        return JWT::encode($payload, getenv('JWT_SECRET_KEY'), 'HS256');
    }
}
