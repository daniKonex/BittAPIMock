<?php
// Test básico sin funciones complejas
echo "Basic test start\n";

// Test 1: Variables básicas
$test = "Hello World";
echo "Test 1: $test\n";

// Test 2: Array simple
$arr = ['a', 'b', 'c'];
echo "Test 2: " . count($arr) . " items\n";

// Test 3: JSON simple
$json = json_encode(['status' => 'ok']);
echo "Test 3: $json\n";

// Test 4: File operations (esto podría fallar)
echo "Test 4: Current dir: " . getcwd() . "\n";

// Test 5: Shell exec (esto podría fallar)
echo "Test 5: Trying shell_exec...\n";
$result = shell_exec('echo "shell works"');
echo "Shell result: " . ($result ?? 'failed') . "\n";

echo "Basic test complete\n";
?>
