<?php
// Simple health check that always returns 200
header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'message' => 'Health check passed',
    'timestamp' => date('c'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
    'php_version' => phpversion()
]);
exit;
?>
