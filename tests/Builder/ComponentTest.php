<?php

test('button component configuration', function () {
    $button = [
        'type' => 'button',
        'text' => 'Click Me',
        'id' => 'submitBtn',
        'enabled' => true,
        'onClick' => function() {
            return 'clicked';
        },
    ];
    
    expect($button['type'])->toBe('button');
    expect($button['text'])->toBe('Click Me');
    expect($button['id'])->toBe('submitBtn');
    expect($button['enabled'])->toBeTrue();
    expect($button['onClick'])->toBeCallable();
});

test('label component configuration', function () {
    $label = [
        'type' => 'label',
        'text' => 'Hello World',
        'align' => 'center',
        'wrap' => false,
    ];
    
    expect($label['type'])->toBe('label');
    expect($label['text'])->toBe('Hello World');
    expect($label['align'])->toBe('center');
    expect($label['wrap'])->toBeFalse();
});

test('entry component configuration', function () {
    $entry = [
        'type' => 'entry',
        'placeholder' => 'Enter text...',
        'bind' => 'username',
        'password' => false,
        'onChange' => function($value) {
            return "Changed to: {$value}";
        },
    ];
    
    expect($entry['type'])->toBe('entry');
    expect($entry['placeholder'])->toBe('Enter text...');
    expect($entry['bind'])->toBe('username');
    expect($entry['password'])->toBeFalse();
    expect($entry['onChange'])->toBeCallable();
});

test('grid layout configuration', function () {
    $grid = [
        'type' => 'grid',
        'columns' => 2,
        'padded' => true,
        'children' => [
            ['component' => 'label', 'text' => 'Name:', 'x' => 0, 'y' => 0],
            ['component' => 'entry', 'bind' => 'name', 'x' => 1, 'y' => 0],
            ['component' => 'label', 'text' => 'Email:', 'x' => 0, 'y' => 1],
            ['component' => 'entry', 'bind' => 'email', 'x' => 1, 'y' => 1],
        ],
    ];
    
    expect($grid['type'])->toBe('grid');
    expect($grid['columns'])->toBe(2);
    expect($grid['padded'])->toBeTrue();
    expect(count($grid['children']))->toBe(4);
    expect($grid['children'][0]['component'])->toBe('label');
    expect($grid['children'][0]['text'])->toBe('Name:');
    expect($grid['children'][1]['component'])->toBe('entry');
    expect($grid['children'][1]['bind'])->toBe('name');
});

test('box layout configuration', function () {
    $box = [
        'type' => 'box',
        'direction' => 'horizontal',
        'padded' => true,
        'children' => [
            ['type' => 'button', 'text' => 'OK', 'stretchy' => false],
            ['type' => 'button', 'text' => 'Cancel', 'stretchy' => false],
            ['type' => 'label', 'text' => 'Status', 'stretchy' => true],
        ],
    ];
    
    expect($box['type'])->toBe('box');
    expect($box['direction'])->toBe('horizontal');
    expect($box['padded'])->toBeTrue();
    expect(count($box['children']))->toBe(3);
    expect($box['children'][0]['stretchy'])->toBeFalse();
    expect($box['children'][2]['stretchy'])->toBeTrue();
});

test('component event handling', function () {
    $events = [];
    
    $component = [
        'type' => 'button',
        'id' => 'testBtn',
        'events' => [
            'onClick' => function() use (&$events) {
                $events[] = 'Button clicked';
            },
            'onHover' => function() use (&$events) {
                $events[] = 'Button hovered';
            },
        ],
    ];
    
    // Trigger events
    if (isset($component['events']['onClick'])) {
        $component['events']['onClick']();
    }
    if (isset($component['events']['onHover'])) {
        $component['events']['onHover']();
    }
    
    expect($events)->toHaveCount(2);
    expect($events[0])->toBe('Button clicked');
    expect($events[1])->toBe('Button hovered');
});

test('component data binding', function () {
    $state = [
        'username' => 'john_doe',
        'email' => 'john@example.com',
    ];
    
    $components = [
        ['type' => 'entry', 'bind' => 'username'],
        ['type' => 'entry', 'bind' => 'email'],
        ['type' => 'label', 'bind' => 'username'],
    ];
    
    // Simulate data binding
    foreach ($components as &$component) {
        if (isset($component['bind']) && isset($state[$component['bind']])) {
            $component['value'] = $state[$component['bind']];
        }
    }
    
    expect($components[0]['value'])->toBe('john_doe');
    expect($components[1]['value'])->toBe('john@example.com');
    expect($components[2]['value'])->toBe('john_doe');
});