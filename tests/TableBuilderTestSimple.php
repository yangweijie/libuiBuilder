<?php

use Kingbes\Libui\View\Builder;

test('TableBuilder can be instantiated', function() {
    $table = Builder::table();
    expect($table)->toBeObject();
});

test('TableBuilder can set headers', function() {
    $table = Builder::table();
    $result = $table->headers(['Name', 'Age', 'City']);
    
    expect($result)->toBe($table);
    expect($table->getConfig('headers'))->toBe(['Name', 'Age', 'City']);
});

test('TableBuilder can set data', function() {
    $data = [
        ['John', '30', 'NYC'],
        ['Jane', '25', 'LA']
    ];
    
    $table = Builder::table();
    $result = $table->data($data);
    
    expect($result)->toBe($table);
    expect($table->getConfig('originalData'))->toBe($data);
});

test('TableBuilder can set options', function() {
    $options = [
        'sortable' => true,
        'multiSelect' => false,
        'headerVisible' => true
    ];
    
    $table = Builder::table();
    $result = $table->options($options);
    
    expect($result)->toBe($table);
    expect($table->getConfig('options'))->toEqual(array_merge($table->getDefaultConfig()['options'], $options));
});

test('TableBuilder has correct default config', function() {
    $table = Builder::table();
    $defaultConfig = $table->getDefaultConfig();
    
    expect($defaultConfig)->toHaveKey('headers');
    expect($defaultConfig)->toHaveKey('data');
    expect($defaultConfig)->toHaveKey('options');
    expect($defaultConfig['headers'])->toBeArray();
    expect($defaultConfig['data'])->toBeArray();
    expect($defaultConfig['options'])->toBeArray();
});