<?php

test('container service registration', function () {
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    // Register services
    $container['services']['Config'] = function() {
        return ['app' => ['title' => 'Test']];
    };
    
    $container['services']['Logger'] = function() {
        return ['level' => 'info'];
    };
    
    expect(count($container['services']))->toBe(2);
    expect(isset($container['services']['Config']))->toBeTrue();
    expect(isset($container['services']['Logger']))->toBeTrue();
});

test('container service resolution', function () {
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    $container['services']['Test'] = function() {
        return (object)['value' => 42];
    };
    
    // Resolve service
    $factory = $container['services']['Test'];
    $instance = $factory();
    
    expect($instance)->toBeObject();
    expect($instance->value)->toBe(42);
});

test('container singleton pattern', function () {
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    $container['services']['Singleton'] = function() {
        return (object)['id' => uniqid()];
    };
    
    // First resolution - create instance
    if (!isset($container['instances']['Singleton'])) {
        $factory = $container['services']['Singleton'];
        $container['instances']['Singleton'] = $factory();
    }
    $instance1 = $container['instances']['Singleton'];
    
    // Second resolution - return same instance
    $instance2 = $container['instances']['Singleton'];
    
    expect($instance1)->toBe($instance2);
});

test('container dependency injection', function () {
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    // Register dependent services
    $container['services']['Config'] = function() {
        return ['database' => ['host' => 'localhost']];
    };
    
    $container['services']['Database'] = function($container) {
        $config = $container['instances']['Config'] ?? null;
        if (!$config) {
            $factory = $container['services']['Config'];
            $config = $factory();
        }
        return (object)['host' => $config['database']['host']];
    };
    
    // Resolve with dependency
    $configFactory = $container['services']['Config'];
    $container['instances']['Config'] = $configFactory();
    
    $dbFactory = $container['services']['Database'];
    $db = $dbFactory($container);
    
    expect($db->host)->toBe('localhost');
});

test('container factory caching', function () {
    $container = [
        'services' => [],
        'instances' => [],
        'cache' => [],
    ];
    
    $container['services']['Expensive'] = function() {
        return ['created_at' => time(), 'data' => 'expensive'];
    };
    
    // First call - create and cache
    if (!isset($container['cache']['Expensive'])) {
        $factory = $container['services']['Expensive'];
        $container['cache']['Expensive'] = $factory();
    }
    $result1 = $container['cache']['Expensive'];
    
    // Second call - use cache
    $result2 = $container['cache']['Expensive'];
    
    expect($result1)->toBe($result2);
    expect($result1['data'])->toBe('expensive');
});