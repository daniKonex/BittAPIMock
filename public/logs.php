<?php
header('Content-Type: text/plain');

echo "=== STARTUP LOG ===\n";
if (file_exists('/var/log/startup.log')) {
    echo file_get_contents('/var/log/startup.log');
} else {
    echo "No startup log found\n";
}

echo "\n=== APACHE ERROR LOG ===\n";
if (file_exists('/var/log/apache2/error.log')) {
    echo "Apache error log exists\n";
    $log = file_get_contents('/var/log/apache2/error.log');
    echo substr($log, -2000); // Last 2000 chars
} else {
    echo "No Apache error log found\n";
}

echo "\n=== PHP ERROR LOG ===\n";
$php_error_log = ini_get('error_log');
echo "PHP error_log setting: $php_error_log\n";
if ($php_error_log && file_exists($php_error_log)) {
    echo "PHP error log exists\n";
    $log = file_get_contents($php_error_log);
    echo substr($log, -1000); // Last 1000 chars
} else {
    echo "No PHP error log found or configured\n";
}

echo "\n=== DIRECTORY LISTING ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "Files in public:\n";
$files = scandir('/var/www/html/public/');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "$file\n";
    }
}
?>
