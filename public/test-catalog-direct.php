<?php
// Test catalog controller directly without CodeIgniter routing
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== DIRECT CATALOG CONTROLLER TEST ===\n\n";

try {
    // Step 1: Test autoloader
    echo "1. Testing autoloader...\n";
    require_once '/var/www/html/vendor/autoload.php';
    echo "   ✓ Autoloader OK\n\n";
    
    // Step 2: Test if we can load the controller class
    echo "2. Testing controller class loading...\n";
    
    // Set up minimal CodeIgniter constants
    if (!defined('FCPATH')) {
        define('FCPATH', '/var/www/html/public/');
    }
    if (!defined('APPPATH')) {
        define('APPPATH', '/var/www/html/app/');
    }
    if (!defined('SYSTEMPATH')) {
        define('SYSTEMPATH', '/var/www/html/vendor/codeigniter4/framework/system/');
    }
    
    // Try to include the controller file directly
    $controllerFile = '/var/www/html/app/Controllers/Catalog.php';
    echo "   Controller file exists: " . (file_exists($controllerFile) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($controllerFile)) {
        echo "   Controller file size: " . filesize($controllerFile) . " bytes\n";
        
        // Read and show first few lines
        $content = file_get_contents($controllerFile);
        $lines = explode("\n", $content);
        echo "   First 10 lines of controller:\n";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo "     " . ($i + 1) . ": " . $lines[$i] . "\n";
        }
    }
    
    echo "\n3. Testing PHP syntax of controller...\n";
    $syntaxCheck = shell_exec("php -l /var/www/html/app/Controllers/Catalog.php 2>&1");
    echo "   Syntax check result: $syntaxCheck\n";
    
    echo "\n4. Testing BaseController...\n";
    $baseControllerFile = '/var/www/html/app/Controllers/BaseController.php';
    echo "   BaseController exists: " . (file_exists($baseControllerFile) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($baseControllerFile)) {
        $baseSyntaxCheck = shell_exec("php -l /var/www/html/app/Controllers/BaseController.php 2>&1");
        echo "   BaseController syntax: $baseSyntaxCheck\n";
    }
    
    echo "\n5. Testing sample data files...\n";
    $catalogFile = '/var/www/html/sample_catalog.json';
    echo "   sample_catalog.json exists: " . (file_exists($catalogFile) ? 'YES' : 'NO') . "\n";
    if (file_exists($catalogFile)) {
        echo "   File size: " . filesize($catalogFile) . " bytes\n";
        $jsonContent = file_get_contents($catalogFile);
        $jsonValid = json_decode($jsonContent) !== null;
        echo "   JSON valid: " . ($jsonValid ? 'YES' : 'NO') . "\n";
        if (!$jsonValid) {
            echo "   JSON error: " . json_last_error_msg() . "\n";
        }
    }
    
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
