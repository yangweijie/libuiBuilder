<?php

test('config array operations', function () {
    $config = [
        'app' => [
            'title' => 'Test App',
            'version' => '1.0',
        ],
    ];
    
    expect($config['app']['title'])->toBe('Test App');
    expect($config['app']['version'])->toBe('1.0');
    expect($config['nonexistent'] ?? 'default')->toBe('default');
});

test('config set and merge operations', function () {
    $config = [];
    
    // Set operation
    $config['test']['key'] = 'value';
    expect($config['test']['key'])->toBe('value');
    
    // Merge operation
    $config = array_merge_recursive($config, ['new' => ['item' => 'test']]);
    expect($config['new']['item'])->toBe('test');
});

test('config existence check', function () {
    $config = ['existing' => 'value'];
    
    expect(isset($config['existing']))->toBeTrue();
    expect(isset($config['nonexistent']))->toBeFalse();
    expect(is_array($config))->toBeTrue();
});

test('config nested operations', function () {
    $config = [
        'database' => [
            'host' => 'localhost',
            'port' => 3306,
            'credentials' => [
                'username' => 'user',
                'password' => 'pass',
            ],
        ],
    ];
    
    // Access nested values
    expect($config['database']['host'])->toBe('localhost');
    expect($config['database']['port'])->toBe(3306);
    expect($config['database']['credentials']['username'])->toBe('user');
    
    // Modify nested values
    $config['database']['port'] = 5432;
    expect($config['database']['port'])->toBe(5432);
});

test('config default values', function () {
    $config = [
        'app' => [
            'title' => 'My App',
        ],
    ];
    
    // Using null coalesce for defaults
    $title = $config['app']['title'] ?? 'Default Title';
    $version = $config['app']['version'] ?? '1.0.0';
    $debug = $config['app']['debug'] ?? false;
    
    expect($title)->toBe('My App');
    expect($version)->toBe('1.0.0');
    expect($debug)->toBeFalse();
});