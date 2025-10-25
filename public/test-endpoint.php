<?php
// Test specific CodeIgniter endpoints
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

echo "Testing CodeIgniter endpoints:\n\n";

// Test 1: Try to access catalog export directly
echo "1. Testing /catalog/export:\n";
try {
    $url = 'https://bittapimock-production.up.railway.app/catalog/export';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $headers = $http_response_header ?? [];
    
    echo "Response headers:\n";
    foreach ($headers as $header) {
        echo "  $header\n";
    }
    echo "Response body:\n";
    echo substr($response, 0, 500) . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Check routes configuration
echo "2. Checking if routes file is accessible:\n";
$routesFile = '/var/www/html/app/Config/Routes.php';
if (file_exists($routesFile)) {
    echo "Routes file exists\n";
    echo "File size: " . filesize($routesFile) . " bytes\n";
} else {
    echo "Routes file NOT found\n";
}

// Test 3: Check controllers
echo "\n3. Checking controllers:\n";
$controllersDir = '/var/www/html/app/Controllers/';
if (is_dir($controllersDir)) {
    $controllers = scandir($controllersDir);
    foreach ($controllers as $controller) {
        if (str_ends_with($controller, '.php')) {
            echo "  Found: $controller\n";
        }
    }
} else {
    echo "Controllers directory NOT found\n";
}

echo "\nTest complete.\n";
?>
