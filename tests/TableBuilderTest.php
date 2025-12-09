<?php

test('TableBuilder can be instantiated', function() {
    $table = new \Kingbes\Libui\View\Components\TableBuilder();
    expect($table)->toBeInstanceOf(\Kingbes\Libui\View\Components\TableBuilder::class);
});

test('TableBuilder can set headers', function() {
    $table = new \Kingbes\Libui\View\Components\TableBuilder();
    $result = $table->headers(['Name', 'Age', 'City']);
    
    expect($result)->toBe($table);
    expect($table->getConfig('headers'))->toBe(['Name', 'Age', 'City']);
});

test('TableBuilder can set data', function() {
    $data = [
        ['Name' => 'John', 'Age' => '30', 'City' => 'NYC'],
        ['Name' => 'Jane', 'Age' => '25', 'City' => 'LA']
    ];
    
    $table = new \Kingbes\Libui\View\Components\TableBuilder();
    $result = $table->data($data);
    
    expect($result)->toBe($table);
    expect($table->getConfig('data'))->toBe($data);
});

test('TableBuilder can set options', function() {
    $options = [
        'sortable' => true,
        'multiSelect' => false,
        'headerVisible' => true
    ];
    
    $table = new \Kingbes\Libui\View\Components\TableBuilder();
    $result = $table->options($options);
    
    expect($result)->toBe($table);
    expect($table->getConfig('options'))->toEqual(array_merge($table->getDefaultConfig()['options'], $options));
});

test('TableBuilder has correct default config', function() {
    $table = new \Kingbes\Libui\View\Components\TableBuilder();
    $defaultConfig = $table->getDefaultConfig();
    
    expect($defaultConfig)->toHaveKey('headers');
    expect($defaultConfig)->toHaveKey('data');
    expect($defaultConfig)->toHaveKey('options');
    expect($defaultConfig['headers'])->toBeArray();
    expect($defaultConfig['data'])->toBeArray();
    expect($defaultConfig['options'])->toBeArray();
});