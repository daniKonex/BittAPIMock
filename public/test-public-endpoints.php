<?php
// Test public endpoints that don't require authentication
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "Testing PUBLIC endpoints (no auth required):\n\n";

$baseUrl = 'https://bittapimock-production.up.railway.app';
$endpoints = [
    'GET /' => '/',
    'POST /auth/login' => '/auth/login',
    'POST /auth/guest-register' => '/auth/guest-register',
    'POST /token/force-login' => '/token/force-login',
    'POST /token/refresh' => '/token/refresh'
];

foreach ($endpoints as $name => $path) {
    echo "Testing $name:\n";
    
    $method = explode(' ', $name)[0];
    $url = $baseUrl . $path;
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "Content-Type: application/json\r\n",
            'content' => $method === 'POST' ? '{"test": "data"}' : ''
        ]
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        
        $statusLine = $headers[0] ?? 'Unknown';
        echo "  Status: $statusLine\n";
        
        if ($response) {
            $shortResponse = substr($response, 0, 200);
            echo "  Response: $shortResponse\n";
            if (strlen($response) > 200) {
                echo "  ... (truncated)\n";
            }
        } else {
            echo "  No response body\n";
        }
        
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== PROTECTED ENDPOINTS (should return 401) ===\n\n";

$protectedEndpoints = [
    'GET /me/prices' => '/me/prices',
    'GET /catalog/export' => '/catalog/export',
    'POST /cart/validate' => '/cart/validate'
];

foreach ($protectedEndpoints as $name => $path) {
    echo "Testing $name:\n";
    
    $method = explode(' ', $name)[0];
    $url = $baseUrl . $path;
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "Content-Type: application/json\r\n",
            'content' => $method === 'POST' ? '{"test": "data"}' : ''
        ]
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        
        $statusLine = $headers[0] ?? 'Unknown';
        echo "  Status: $statusLine\n";
        
        if ($response) {
            echo "  Response: $response\n";
        }
        
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Test complete.\n";
?>
