<?php

namespace App\Controllers;

use Firebase\JWT\JWT;

class Token extends BaseController
{
    /**
     * POST /token/force-login
     * Generates a User Access token for another user (used by sales rep, admin, etc)
     * Requires Integration Token
     */
    public function forceLogin()
    {
        $data = $this->request->getJSON(true);
        
        $userId = $data['userId'] ?? '';
        $clientId = $data['clientId'] ?? '';

        if (empty($userId) || empty($clientId)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'userId and clientId are required'
            ], 400);
        }

        // Generate JWT token for the specified user
        $token = $this->generateToken([
            'userId' => $userId,
            'clientId' => $clientId,
            'email' => "user{$userId}@example.com",
            'type' => 'user',
            'impersonated' => true
        ]);

        return $this->jsonResponse([
            'success' => true,
            'data' => [
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => (int)getenv('JWT_TIME_TO_LIVE'),
                'user' => [
                    'id' => $userId,
                    'clientId' => $clientId,
                    'email' => "user{$userId}@example.com",
                    'name' => 'Impersonated User',
                    'roles' => ['customer']
                ]
            ]
        ]);
    }

    /**
     * POST /token/refresh
     * Refreshes an expired User Access Token
     * Requires Integration Token
     */
    public function refresh()
    {
        $data = $this->request->getJSON(true);
        
        $expiredToken = $data['token'] ?? '';

        if (empty($expiredToken)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Token is required'
            ], 400);
        }

        try {
            // Decode without verification to get payload (in production, handle this better)
            $parts = explode('.', $expiredToken);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid token format');
            }
            
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            
            // Generate new token with same user data
            $newToken = $this->generateToken([
                'userId' => $payload['userId'] ?? 'USR0000',
                'clientId' => $payload['clientId'] ?? 'CLI0000',
                'email' => $payload['email'] ?? 'unknown@example.com',
                'type' => $payload['type'] ?? 'user'
            ]);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'token' => $newToken,
                    'tokenType' => 'Bearer',
                    'expiresIn' => (int)getenv('JWT_TIME_TO_LIVE')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Could not refresh token',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    private function generateToken($payload)
    {
        $payload['iat'] = time();
        $payload['exp'] = time() + (int)getenv('JWT_TIME_TO_LIVE');
        
        return JWT::encode($payload, getenv('JWT_SECRET_KEY'), 'HS256');
    }
}
