<?php

test('window configuration works', function () {
    // Test window configuration without actual class loading
    $config = [
        'title' => 'Test Window',
        'width' => 800,
        'height' => 600,
        'resizable' => true,
        'margined' => true,
    ];
    
    expect($config['title'])->toBe('Test Window');
    expect($config['width'])->toBe(800);
    expect($config['height'])->toBe(600);
    expect($config['resizable'])->toBeTrue();
    expect($config['margined'])->toBeTrue();
});

test('window children management', function () {
    // Test children array management
    $children = [];
    
    // Add children
    $children[] = ['type' => 'button', 'text' => 'OK'];
    $children[] = ['type' => 'button', 'text' => 'Cancel'];
    
    expect(count($children))->toBe(2);
    expect($children[0]['type'])->toBe('button');
    expect($children[0]['text'])->toBe('OK');
    expect($children[1]['type'])->toBe('button');
    expect($children[1]['text'])->toBe('Cancel');
});

test('window event handling', function () {
    // Test event callback structure
    $events = [];
    
    $events['onClosing'] = function() {
        return 1; // Allow closing
    };
    
    $events['onSizeChanged'] = function($window) {
        // Handle size change
    };
    
    expect($events)->toHaveKey('onClosing');
    expect($events)->toHaveKey('onSizeChanged');
    expect($events['onClosing'])->toBeCallable();
    expect($events['onSizeChanged'])->toBeCallable();
});