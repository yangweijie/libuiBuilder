<?php

require_once 'vendor/autoload.php';

use Tests\TestCase;

echo "Running simple Pest test...\n";

try {
    // Create a simple test case
    $testCase = new TestCase();
    $testCase->setUp();
    
    // Test basic functionality
    $sm = \Kingbes\Libui\View\State\StateManager::instance();
    $sm->set('test', 'value');
    $result = $sm->get('test');
    
    if ($result === 'value') {
        echo "✓ StateManager basic test passed\n";
    } else {
        echo "✗ StateManager test failed\n";
    }
    
    $testCase->tearDown();
    echo "Simple test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}