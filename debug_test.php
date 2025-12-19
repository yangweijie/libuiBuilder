<?php

require_once 'vendor/autoload.php';

echo "Testing basic functionality...\n";

try {
    // Test StateManager
    echo "1. Testing StateManager...\n";
    $sm = \Kingbes\Libui\View\State\StateManager::instance();
    echo "StateManager instance created successfully\n";
    
    // Test basic set/get
    $sm->set('test_key', 'test_value');
    $value = $sm->get('test_key');
    echo "Set/Get works: $value\n";
    
    // Test EventDispatcher
    echo "2. Testing EventDispatcher...\n";
    $ed = new \Kingbes\Libui\View\Core\Event\EventDispatcher();
    echo "EventDispatcher created successfully\n";
    
    // Test ConfigManager
    echo "3. Testing ConfigManager...\n";
    $cm = new \Kingbes\Libui\View\Core\Config\ConfigManager();
    echo "ConfigManager created successfully\n";
    
    // Test Builder
    echo "4. Testing Builder...\n";
    $button = \Kingbes\Libui\View\Builder\Builder::button();
    echo "Button builder created successfully\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}