<?php
// Debug information
header('Content-Type: application/json');
http_response_code(200);

$debug_info = [
    'php_version' => phpversion(),
    'server_info' => [
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'not set',
        'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'not set',
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'not set',
    ],
    'environment' => [
        'CI_ENVIRONMENT' => $_ENV['CI_ENVIRONMENT'] ?? 'not set',
        'PORT' => $_ENV['PORT'] ?? 'not set',
    ],
    'file_checks' => [
        'current_file' => __FILE__,
        'env_exists' => file_exists('../.env') ? 'yes' : 'no',
        'index_exists' => file_exists('./index.php') ? 'yes' : 'no',
        'writable_exists' => is_dir('../writable') ? 'yes' : 'no',
        'writable_permissions' => is_dir('../writable') ? substr(sprintf('%o', fileperms('../writable')), -4) : 'n/a',
    ],
    'loaded_extensions' => get_loaded_extensions(),
    'timestamp' => date('c')
];

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
