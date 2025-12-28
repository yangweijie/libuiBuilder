<?php


use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\TableBuilder;

test('TableBuilder can be instantiated', function() {
    $table = new TableBuilder();
    expect($table)->toBeInstanceOf(TableBuilder::class);
});

test('TableBuilder can set headers', function() {
    $table = new TableBuilder();
    $result = $table->headers(['Name', 'Age', 'City']);
    
    expect($result)->toBe($table);
    expect($table->getConfig('headers'))->toBe(['Name', 'Age', 'City']);
});

test('TableBuilder can set data', function() {
    $data = [
        ['John', '30', 'NYC'],
        ['Jane', '25', 'LA']
    ];
    
    $table = new TableBuilder();
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
    
    $table = new TableBuilder();
    $result = $table->options($options);
    
    expect($result)->toBe($table);
    expect($table->getConfig('options'))->toEqual(array_merge($table->getDefaultConfig()['options'], $options));
});

test('TableBuilder has correct default config', function() {
    $table = new TableBuilder();
    $defaultConfig = $table->getDefaultConfig();
    
    expect($defaultConfig)->toHaveKey('headers');
    expect($defaultConfig)->toHaveKey('data');
    expect($defaultConfig)->toHaveKey('options');
    expect($defaultConfig['headers'])->toBeArray();
    expect($defaultConfig['data'])->toBeArray();
    expect($defaultConfig['options'])->toBeArray();
});

test('TableBuilder handles empty data correctly', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->data([]);
    
    expect($table->getConfig('data'))->toBeArray()->toBeEmpty();
    expect($table->getConfig('headers'))->toBeArray()->toHaveCount(2);
});

test('TableBuilder handles mixed data types', function() {
    $data = [
        ['Alice', 25, true],
        ['Bob', 30.5, false],
        ['Charlie', '28', null]
    ];
    
    $table = new TableBuilder();
    $table->headers(['Name', 'Age', 'Active']);
    $table->data($data);
    
    expect($table->getConfig('data'))->toBe($data);
    expect($table->getConfig('data'))->toHaveCount(3);
});

test('TableBuilder setValue processes data correctly', function() {
    $data = [
        ['Test1', 'Value1'],
        ['Test2', 'Value2']
    ];
    
    $table = new TableBuilder();
    $table->headers(['Test', 'Value']);
    
    // 测试setValue方法
    $reflection = new ReflectionClass($table);
    $method = $reflection->getMethod('setValue');
    $method->setAccessible(true);
    $method->invoke($table, $data);
    
    expect($table->getConfig('data'))->toBe($data);
    expect($table->displayData)->toBe($data);
});

test('TableBuilder refresh method works correctly', function() {
    $data = [
        ['Initial', 'Data']
    ];
    
    $table = new TableBuilder();
    $table->headers(['Column1', 'Column2']);
    $table->data($data);
    
    // 测试refresh方法
    $reflection = new ReflectionClass($table);
    $method = $reflection->getMethod('refresh');
    $method->setAccessible(true);
    
    expect($method->invoke($table))->toBe($table);
});

test('TableBuilder handles large datasets', function() {
    $data = [];
    for ($i = 0; $i < 1000; $i++) {
        $data[] = ["Row{$i}", "Value{$i}", "Data{$i}"];
    }
    
    $table = new TableBuilder();
    $table->headers(['Row', 'Value', 'Data']);
    $table->data($data);
    
    expect($table->getConfig('data'))->toHaveCount(1000);
    expect($table->getConfig('data')[0])->toBe(['Row0', 'Value0', 'Data0']);
    expect($table->getConfig('data')[999])->toBe(['Row999', 'Value999', 'Data999']);
});

test('TableBuilder handles state binding', function() {
    $state = StateManager::instance();
    $state->set('testData', [['Bound', 'Data']]);
    
    $table = new TableBuilder();
    $table->headers(['Column1', 'Column2']);
    $table->bind('testData');
    
    expect($table->getConfig('bind'))->toBe('testData');
});

test('TableBuilder handles column configuration', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age', 'City']);
    $table->columns([
        ['type' => 'text', 'width' => 100],
        ['type' => 'number', 'width' => 50],
        ['type' => 'text', 'width' => 150]
    ]);
    
    expect($table->getConfig('columns'))->toBeArray();
    expect($table->getConfig('columns'))->toHaveCount(3);
});

test('TableBuilder handles selection events', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->data([['Alice', 25]]);
    
    $callbackCalled = false;
    $table->onSelected(function($row, $data) use (&$callbackCalled) {
        $callbackCalled = true;
        expect($row)->toBe(0);
        expect($data)->toBe(['Alice', 25]);
    });
    
    expect($table->getConfig('onSelected'))->toBeCallable();
});

test('TableBuilder handles row click events', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->data([['Alice', 25]]);
    
    $callbackCalled = false;
    $table->onRowClick(function($row, $data) use (&$callbackCalled) {
        $callbackCalled = true;
        expect($row)->toBe(0);
        expect($data)->toBe(['Alice', 25]);
    });
    
    expect($table->getConfig('onRowClick'))->toBeCallable();
});

test('TableBuilder handles cell click events', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->data([['Alice', 25]]);
    
    $callbackCalled = false;
    $table->onCellClick(function($row, $column, $value) use (&$callbackCalled) {
        $callbackCalled = true;
        expect($row)->toBe(0);
        expect($column)->toBe(0);
        expect($value)->toBe('Alice');
    });
    
    expect($table->getConfig('onCellClick'))->toBeCallable();
});

test('TableBuilder handles sorting configuration', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->data([['Alice', 25], ['Bob', 30]]);
    $table->options(['sortable' => true]);
    
    expect($table->getConfig('options')['sortable'])->toBeTrue();
});

test('TableBuilder handles multi-select configuration', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->options(['multiSelect' => true]);
    
    expect($table->getConfig('options')['multiSelect'])->toBeTrue();
});

test('TableBuilder handles header visibility', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    $table->options(['headerVisible' => false]);
    
    expect($table->getConfig('options')['headerVisible'])->toBeFalse();
});

test('TableBuilder handles invalid data gracefully', function() {
    $table = new TableBuilder();
    $table->headers(['Name', 'Age']);
    
    // 测试null数据
    $table->data(null);
    expect($table->getConfig('data'))->toBeArray()->toBeEmpty();
    
    // 测试非数组数据
    $table->data('invalid');
    expect($table->getConfig('data'))->toBeArray()->toBeEmpty();
});

test('TableBuilder method chaining works correctly', function() {
    $data = [['Alice', 25]];
    
    $table = new TableBuilder();
    $result = $table
        ->headers(['Name', 'Age'])
        ->data($data)
        ->options(['sortable' => true])
        ->id('testTable');
    
    expect($result)->toBe($table);
    expect($table->getConfig('headers'))->toBe(['Name', 'Age']);
    expect($table->getConfig('data'))->toBe($data);
    expect($table->getConfig('options')['sortable'])->toBeTrue();
    expect($table->getConfig('id'))->toBe('testTable');
});

test('TableBuilder handles empty headers', function() {
    $table = new TableBuilder();
    $table->headers([]);
    $table->data([['Alice', 25]]);
    
    expect($table->getConfig('headers'))->toBeArray()->toBeEmpty();
    expect($table->getConfig('data'))->toBeArray()->toHaveCount(1);
});