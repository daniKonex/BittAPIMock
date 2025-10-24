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
    echo "Last 50 lines:\n";
    echo shell_exec('tail -50 /var/log/apache2/error.log');
} else {
    echo "No Apache error log found\n";
}

echo "\n=== PHP ERROR LOG ===\n";
$php_error_log = ini_get('error_log');
if ($php_error_log && file_exists($php_error_log)) {
    echo "Last 20 lines from: $php_error_log\n";
    echo shell_exec("tail -20 '$php_error_log'");
} else {
    echo "No PHP error log found or configured\n";
}

echo "\n=== DIRECTORY LISTING ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "Files in public:\n";
echo shell_exec('ls -la /var/www/html/public/');
?>
