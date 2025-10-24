<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        
        // Get authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        
        // DEBUG: Log what we're receiving
        log_message('debug', 'AuthFilter: Authorization Header = ' . ($authHeader ?: 'EMPTY'));
        log_message('debug', 'AuthFilter: Arguments = ' . json_encode($arguments));
        log_message('debug', 'AuthFilter: INTEGRATION_TOKEN env = ' . (getenv('INTEGRATION_TOKEN') ?: 'NOT SET'));
        
        if (empty($authHeader)) {
            return $response->setJSON([
                'success' => false,
                'message' => 'Authorization header not found'
            ])->setStatusCode(401);
        }

        // Extract token
        $token = null;
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return $response->setJSON([
                'success' => false,
                'message' => 'Invalid authorization header format'
            ])->setStatusCode(401);
        }

        // Check if integration token is allowed and if this is one
        if (in_array('integration', $arguments ?? [])) {
            log_message('debug', 'AuthFilter: Integration token is allowed');
            log_message('debug', 'AuthFilter: Received token = ' . substr($token, 0, 20) . '...');
            log_message('debug', 'AuthFilter: Expected token = ' . substr(getenv('INTEGRATION_TOKEN'), 0, 20) . '...');
            log_message('debug', 'AuthFilter: Tokens match = ' . ($token === getenv('INTEGRATION_TOKEN') ? 'YES' : 'NO'));
            
            if ($token === getenv('INTEGRATION_TOKEN')) {
                log_message('debug', 'AuthFilter: Integration token validated successfully');
                // Mark request as integration token for controllers to check
                $request->tokenType = 'integration';
                return $request;
            }
            // If integration token doesn't match, continue to try JWT validation
            log_message('debug', 'AuthFilter: Not an integration token, trying JWT validation');
        }

        // Validate JWT token
        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET_KEY'), 'HS256'));
            
            // Check token type if arguments are provided
            if (!empty($arguments)) {
                if (in_array('user', $arguments) && $decoded->type !== 'user') {
                    return $response->setJSON([
                        'success' => false,
                        'message' => 'User token required'
                    ])->setStatusCode(403);
                }
                if (in_array('guest', $arguments) && !in_array($decoded->type, ['user', 'guest'])) {
                    return $response->setJSON([
                        'success' => false,
                        'message' => 'User or guest token required'
                    ])->setStatusCode(403);
                }
            }
            
            // Store user info in request
            $request->user = $decoded;
            $request->tokenType = 'user';
            
        } catch (\Exception $e) {
            return $response->setJSON([
                'success' => false,
                'message' => 'Invalid or expired token',
                'error' => $e->getMessage()
            ])->setStatusCode(401);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
