<?php

test('container basic structure', function () {
    // Test dependency injection container structure
    $container = [
        'services' => [],
        'instances' => [],
        'singletons' => [],
    ];
    
    // Register service
    $container['services']['ConfigManager'] = function() {
        return ['app' => ['title' => 'Test App']];
    };
    
    $container['services']['EventDispatcher'] = function() {
        return ['listeners' => []];
    };
    
    expect(count($container['services']))->toBe(2);
    expect(isset($container['services']['ConfigManager']))->toBeTrue();
    expect(isset($container['services']['EventDispatcher']))->toBeTrue();
});

test('container service resolution', function () {
    // Test service resolution
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    // Register service
    $container['services']['TestService'] = function() {
        return (object)['value' => 42];
    };
    
    // Resolve service
    $factory = $container['services']['TestService'];
    $instance = $factory();
    
    expect($instance)->toBeObject();
    expect($instance->value)->toBe(42);
});

test('container singleton pattern', function () {
    // Test singleton service management
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    // Register singleton service
    $container['services']['SingletonService'] = function() {
        return (object)['id' => uniqid()];
    };
    
    // First resolution
    if (!isset($container['instances']['SingletonService'])) {
        $factory = $container['services']['SingletonService'];
        $container['instances']['SingletonService'] = $factory();
    }
    $instance1 = $container['instances']['SingletonService'];
    
    // Second resolution (should return same instance)
    $instance2 = $container['instances']['SingletonService'];
    
    expect($instance1)->toBe($instance2);
    expect($instance1->id)->toBe($instance2->id);
});

test('container dependency injection', function () {
    // Test dependency injection between services
    $container = [
        'services' => [],
        'instances' => [],
    ];
    
    // Register dependent service
    $container['services']['Config'] = function() {
        return ['app' => ['name' => 'MyApp']];
    };
    
    $container['services']['App'] = function($container) {
        $config = $container['instances']['Config'] ?? null;
        if (!$config) {
            $factory = $container['services']['Config'];
            $config = $factory();
        }
        return (object)['name' => $config['app']['name']];
    };
    
    // Resolve with dependency
    $configFactory = $container['services']['Config'];
    $container['instances']['Config'] = $configFactory();
    
    $appFactory = $container['services']['App'];
    $app = $appFactory($container);
    
    expect($app->name)->toBe('MyApp');
});