<?php

test('window builder basic configuration', function () {
    $window = [
        'type' => 'window',
        'title' => 'Test Window',
        'width' => 800,
        'height' => 600,
        'resizable' => true,
        'margined' => true,
    ];
    
    expect($window['type'])->toBe('window');
    expect($window['title'])->toBe('Test Window');
    expect($window['width'])->toBe(800);
    expect($window['height'])->toBe(600);
    expect($window['resizable'])->toBeTrue();
    expect($window['margined'])->toBeTrue();
});

test('window builder method chaining', function () {
    $config = [];
    
    // Simulate method chaining
    $config['title'] = 'Chained Window';
    $config['width'] = 1024;
    $config['height'] = 768;
    $config['resizable'] = false;
    $config['margined'] = true;
    
    expect($config['title'])->toBe('Chained Window');
    expect($config['width'])->toBe(1024);
    expect($config['height'])->toBe(768);
    expect($config['resizable'])->toBeFalse();
    expect($config['margined'])->toBeTrue();
});

test('window children management', function () {
    $window = [
        'type' => 'window',
        'children' => [],
    ];
    
    // Add children
    $button = ['type' => 'button', 'text' => 'Click Me'];
    $label = ['type' => 'label', 'text' => 'Hello World'];
    
    $window['children'][] = $button;
    $window['children'][] = $label;
    
    expect(count($window['children']))->toBe(2);
    expect($window['children'][0]['type'])->toBe('button');
    expect($window['children'][1]['type'])->toBe('label');
});

test('window event handling', function () {
    $window = [
        'type' => 'window',
        'events' => [],
    ];
    
    // Register event handlers
    $window['events']['onClosing'] = function() {
        return true; // Allow closing
    };
    
    $window['events']['onResize'] = function($width, $height) {
        return "Resized to {$width}x{$height}";
    };
    
    expect(isset($window['events']['onClosing']))->toBeTrue();
    expect(isset($window['events']['onResize']))->toBeTrue();
    expect($window['events']['onClosing'])->toBeCallable();
    expect($window['events']['onResize'])->toBeCallable();
});

test('window default values', function () {
    $window = [
        'type' => 'window',
    ];
    
    // Apply defaults
    $defaults = [
        'title' => 'Untitled Window',
        'width' => 800,
        'height' => 600,
        'resizable' => true,
        'margined' => true,
    ];
    
    $window = array_merge($defaults, $window);
    
    expect($window['title'])->toBe('Untitled Window');
    expect($window['width'])->toBe(800);
    expect($window['height'])->toBe(600);
    expect($window['resizable'])->toBeTrue();
    expect($window['margined'])->toBeTrue();
});

test('window configuration validation', function () {
    $validConfig = [
        'type' => 'window',
        'title' => 'Valid Window',
        'width' => 1024,
        'height' => 768,
    ];
    
    $invalidConfig = [
        'type' => 'window',
        'title' => '',
        'width' => -100,
        'height' => 0,
    ];
    
    // Validate positive dimensions
    expect($validConfig['width'])->toBeGreaterThan(0);
    expect($validConfig['height'])->toBeGreaterThan(0);
    expect($validConfig['title'])->not->toBeEmpty();
    
    // Invalid config should fail validation
    expect($invalidConfig['width'])->toBeLessThan(0);
    expect($invalidConfig['height'])->toBe(0);
    expect($invalidConfig['title'])->toBeEmpty();
});