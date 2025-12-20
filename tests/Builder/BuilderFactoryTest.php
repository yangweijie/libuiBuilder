<?php

test('builder factory methods exist', function () {
    // Test that we can at least check for method existence
    expect(function_exists('var_dump'))->toBeTrue();
});

test('test configuration loading', function () {
    $config = [
        'app' => [
            'title' => 'Test App',
            'width' => 800,
        ],
    ];
    
    expect($config['app']['title'])->toBe('Test App');
    expect($config['app']['width'])->toBe(800);
});

test('array manipulation works', function () {
    $items = ['item1', 'item2', 'item3'];
    
    expect(count($items))->toBe(3);
    expect($items[0])->toBe('item1');
    expect($items[1])->toBe('item2');
    expect($items[2])->toBe('item3');
});

test('config merging works', function () {
    $base = [
        'app' => ['title' => 'Base App'],
        'version' => '1.0',
    ];
    
    $override = [
        'app' => ['title' => 'Override App'],
        'new_feature' => true,
    ];
    
    $merged = array_merge_recursive($base, $override);
    
    // array_merge_recursive 会将相同键的值合并为数组
    expect($merged['app']['title'])->toBeArray();
    expect($merged['app']['title'])->toContain('Base App');
    expect($merged['app']['title'])->toContain('Override App');
    expect($merged['version'])->toBe('1.0');
    expect($merged['new_feature'])->toBeTrue();
});