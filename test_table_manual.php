<?php

// 简单测试TableBuilder是否可以加载
require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\Builder;

echo "Testing TableBuilder instantiation...\n";

try {
    $table = Builder::table();
    echo "TableBuilder instantiated successfully\n";
    
    $headers = ['Name', 'Age'];
    $table->headers($headers);
    echo "Headers set successfully\n";
    
    $data = [['Alice', 25]];
    $table->data($data);
    echo "Data set successfully\n";
    
    $config = $table->getConfig('headers');
    echo "Headers from config: " . json_encode($config) . "\n";
    
    echo "All tests passed!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}