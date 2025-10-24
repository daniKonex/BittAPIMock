<?php
// Test CodeIgniter bootstrap step by step
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$result = [
    'step' => 'starting',
    'errors' => [],
    'info' => []
];

try {
    // Step 1: Check paths
    $result['step'] = 'checking_paths';
    $result['info']['FCPATH'] = __DIR__ . DIRECTORY_SEPARATOR;
    $result['info']['SYSTEMPATH'] = realpath(__DIR__ . '/../vendor/codeigniter4/framework/system') . DIRECTORY_SEPARATOR;
    $result['info']['APPPATH'] = realpath(__DIR__ . '/../app') . DIRECTORY_SEPARATOR;
    
    // Step 2: Check if paths exist
    $result['step'] = 'validating_paths';
    $result['info']['system_exists'] = is_dir($result['info']['SYSTEMPATH']);
    $result['info']['app_exists'] = is_dir($result['info']['APPPATH']);
    
    // Step 3: Try to define constants like CodeIgniter does
    $result['step'] = 'defining_constants';
    if (!defined('FCPATH')) {
        define('FCPATH', $result['info']['FCPATH']);
    }
    if (!defined('SYSTEMPATH')) {
        define('SYSTEMPATH', $result['info']['SYSTEMPATH']);
    }
    if (!defined('APPPATH')) {
        define('APPPATH', $result['info']['APPPATH']);
    }
    
    // Step 4: Check autoloader
    $result['step'] = 'checking_autoloader';
    $autoloader_path = realpath(__DIR__ . '/../vendor/autoload.php');
    $result['info']['autoloader_path'] = $autoloader_path;
    $result['info']['autoloader_exists'] = file_exists($autoloader_path);
    
    if (file_exists($autoloader_path)) {
        require_once $autoloader_path;
        $result['info']['autoloader_loaded'] = true;
    }
    
    // Step 5: Try to load CodeIgniter bootstrap
    $result['step'] = 'loading_bootstrap';
    $bootstrap_path = SYSTEMPATH . 'bootstrap.php';
    $result['info']['bootstrap_path'] = $bootstrap_path;
    $result['info']['bootstrap_exists'] = file_exists($bootstrap_path);
    
    if (file_exists($bootstrap_path)) {
        // This is where it might fail
        ob_start();
        require_once $bootstrap_path;
        $bootstrap_output = ob_get_clean();
        $result['info']['bootstrap_output'] = $bootstrap_output;
        $result['info']['bootstrap_loaded'] = true;
    }
    
    $result['step'] = 'success';
    $result['status'] = 'completed';
    
} catch (Throwable $e) {
    $result['errors'][] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    $result['status'] = 'failed';
}

http_response_code(200);
echo json_encode($result, JSON_PRETTY_PRINT);
?>
