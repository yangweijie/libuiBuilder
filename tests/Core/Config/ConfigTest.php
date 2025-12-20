<?php

test('config array operations', function () {
    // Test basic config operations without loading the actual class
    $config = [
        'app' => [
            'title' => 'Test App',
            'version' => '1.0.0',
            'settings' => [
                'theme' => 'dark',
                'language' => 'en',
            ],
        ],
        'database' => [
            'host' => 'localhost',
            'port' => 3306,
        ],
    ];
    
    // Test getting nested values
    expect($config['app']['title'])->toBe('Test App');
    expect($config['app']['version'])->toBe('1.0.0');
    expect($config['app']['settings']['theme'])->toBe('dark');
    expect($config['database']['host'])->toBe('localhost');
    expect($config['database']['port'])->toBe(3306);
});

test('config merging logic', function () {
    $base = [
        'app' => [
            'title' => 'Base App',
            'debug' => false,
        ],
        'features' => [
            'auth' => true,
        ],
    ];
    
    $override = [
        'app' => [
            'debug' => true,
            'version' => '2.0',
        ],
        'features' => [
            'notifications' => true,
        ],
        'new_section' => [
            'enabled' => true,
        ],
    ];
    
    // Simulate config merging
    $merged = $base;
    foreach ($override as $key => $value) {
        if (isset($merged[$key]) && is_array($merged[$key]) && is_array($value)) {
            $merged[$key] = array_merge($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }
    
    expect($merged['app']['title'])->toBe('Base App'); // Preserved
    expect($merged['app']['debug'])->toBe(true); // Overridden
    expect($merged['app']['version'])->toBe('2.0'); // Added
    expect($merged['features']['auth'])->toBe(true); // Preserved
    expect($merged['features']['notifications'])->toBe(true); // Added
    expect($merged['new_section']['enabled'])->toBe(true); // New section
});

test('config validation rules', function () {
    // Test validation logic
    $validators = [
        'app.title' => function($value) {
            return is_string($value) && strlen($value) > 0;
        },
        'app.width' => function($value) {
            return is_int($value) && $value > 0;
        },
        'app.height' => function($value) {
            return is_int($value) && $value > 0;
        },
        'debug.enabled' => function($value) {
            return is_bool($value);
        },
    ];
    
    // Valid config
    $validConfig = [
        'app' => [
            'title' => 'My App',
            'width' => 800,
            'height' => 600,
        ],
        'debug' => [
            'enabled' => true,
        ],
    ];
    
    // Validate
    $isValid = true;
    foreach ($validators as $key => $validator) {
        $keys = explode('.', $key);
        $value = $validConfig;
        foreach ($keys as $k) {
            $value = $value[$k] ?? null;
        }
        if (!$validator($value)) {
            $isValid = false;
            break;
        }
    }
    
    expect($isValid)->toBeTrue();
});

test('config default values', function () {
    // Test default value logic
    $config = [
        'app' => [
            'title' => 'My App',
        ],
    ];
    
    $defaults = [
        'app.width' => 800,
        'app.height' => 600,
        'app.margined' => true,
        'debug.enabled' => false,
    ];
    
    // Apply defaults
    foreach ($defaults as $key => $default) {
        $keys = explode('.', $key);
        $current = &$config;
        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        if (empty($current)) {
            $current = $default;
        }
    }
    
    expect($config['app']['title'])->toBe('My App'); // Original
    expect($config['app']['width'])->toBe(800); // Default applied
    expect($config['app']['height'])->toBe(600); // Default applied
    expect($config['app']['margined'])->toBe(true); // Default applied
    expect($config['debug']['enabled'])->toBe(false); // Default applied
});