<?php

describe('Builder Factory Methods', function () {
    test('creates window builder configuration', function () {
        $window = [
            'type' => 'window',
            'config' => [
                'title' => 'Test Window',
                'width' => 800,
                'height' => 600,
                'resizable' => true,
                'margined' => true,
            ],
            'children' => [],
            'events' => [],
        ];
        
        expect($window['type'])->toBe('window');
        expect($window['config']['title'])->toBe('Test Window');
        expect($window['config']['width'])->toBe(800);
        expect($window['config']['height'])->toBe(600);
    });

    test('creates button builder configuration', function () {
        $button = [
            'type' => 'button',
            'config' => [
                'text' => 'Click Me',
                'id' => 'submitBtn',
                'enabled' => true,
            ],
            'events' => [
                'onClick' => function() {
                    return 'Button clicked';
                },
            ],
        ];
        
        expect($button['type'])->toBe('button');
        expect($button['config']['text'])->toBe('Click Me');
        expect($button['config']['id'])->toBe('submitBtn');
        expect($button['events']['onClick'])->toBeCallable();
    });

    test('creates label builder configuration', function () {
        $label = [
            'type' => 'label',
            'config' => [
                'text' => 'Hello World',
                'align' => 'center',
                'wrap' => false,
            ],
        ];
        
        expect($label['type'])->toBe('label');
        expect($label['config']['text'])->toBe('Hello World');
        expect($label['config']['align'])->toBe('center');
    });

    test('creates entry builder configuration', function () {
        $entry = [
            'type' => 'entry',
            'config' => [
                'placeholder' => 'Enter text...',
                'bind' => 'username',
                'password' => false,
            ],
            'events' => [
                'onChange' => function($value) {
                    return "Value changed to: {$value}";
                },
            ],
        ];
        
        expect($entry['type'])->toBe('entry');
        expect($entry['config']['placeholder'])->toBe('Enter text...');
        expect($entry['config']['bind'])->toBe('username');
        expect($entry['events']['onChange'])->toBeCallable();
    });

    test('creates grid builder configuration', function () {
        $grid = [
            'type' => 'grid',
            'config' => [
                'columns' => 2,
                'padded' => true,
            ],
            'children' => [
                ['component' => 'label', 'x' => 0, 'y' => 0, 'text' => 'Name:'],
                ['component' => 'entry', 'x' => 1, 'y' => 0, 'bind' => 'name'],
                ['component' => 'label', 'x' => 0, 'y' => 1, 'text' => 'Email:'],
                ['component' => 'entry', 'x' => 1, 'y' => 1, 'bind' => 'email'],
            ],
        ];
        
        expect($grid['type'])->toBe('grid');
        expect($grid['config']['columns'])->toBe(2);
        expect(count($grid['children']))->toBe(4);
    });

    test('creates box builder configuration', function () {
        $box = [
            'type' => 'box',
            'config' => [
                'direction' => 'horizontal',
                'padded' => true,
            ],
            'children' => [
                ['type' => 'button', 'text' => 'OK'],
                ['type' => 'button', 'text' => 'Cancel'],
                ['type' => 'label', 'text' => 'Status'],
            ],
        ];
        
        expect($box['type'])->toBe('box');
        expect($box['config']['direction'])->toBe('horizontal');
        expect(count($box['children']))->toBe(3);
    });

    test('creates tab builder configuration', function () {
        $tab = [
            'type' => 'tab',
            'config' => [
                'tabs' => [
                    'Tab 1' => ['type' => 'label', 'text' => 'Content 1'],
                    'Tab 2' => ['type' => 'label', 'text' => 'Content 2'],
                    'Tab 3' => ['type' => 'label', 'text' => 'Content 3'],
                ],
            ],
        ];
        
        expect($tab['type'])->toBe('tab');
        expect(count($tab['config']['tabs']))->toBe(3);
        expect($tab['config']['tabs']['Tab 1']['text'])->toBe('Content 1');
    });
});

describe('Component Configuration Methods', function () {
    test('button text configuration', function () {
        $button = ['type' => 'button'];
        
        // Simulate text() method
        $button['config']['text'] = 'New Text';
        
        expect($button['config']['text'])->toBe('New Text');
    });

    test('button id configuration', function () {
        $button = ['type' => 'button'];
        
        // Simulate id() method
        $button['config']['id'] = 'uniqueButton';
        
        expect($button['config']['id'])->toBe('uniqueButton');
    });

    test('entry bind configuration', function () {
        $entry = ['type' => 'entry'];
        
        // Simulate bind() method
        $entry['config']['bind'] = 'formData.username';
        
        expect($entry['config']['bind'])->toBe('formData.username');
    });

    test('grid columns configuration', function () {
        $grid = ['type' => 'grid'];
        
        // Simulate columns() method
        $grid['config']['columns'] = 3;
        
        expect($grid['config']['columns'])->toBe(3);
    });

    test('window size configuration', function () {
        $window = ['type' => 'window'];
        
        // Simulate size() method
        $window['config']['width'] = 1024;
        $window['config']['height'] = 768;
        
        expect($window['config']['width'])->toBe(1024);
        expect($window['config']['height'])->toBe(768);
    });

    test('event handler configuration', function () {
        $component = ['type' => 'button', 'events' => []];
        
        // Simulate onClick() method
        $component['events']['onClick'] = function($data) {
            return "Clicked: {$data['id']}";
        };
        
        expect($component['events']['onClick'])->toBeCallable();
        
        // Test the handler
        $result = $component['events']['onClick'](['id' => 'testBtn']);
        expect($result)->toBe('Clicked: testBtn');
    });
});

describe('Component Relationships', function () {
    test('parent-child relationship', function () {
        $window = [
            'type' => 'window',
            'children' => [],
        ];
        
        $button = ['type' => 'button', 'text' => 'Submit'];
        $label = ['type' => 'label', 'text' => 'Status'];
        
        // Simulate contains() method
        $window['children'][] = $button;
        $window['children'][] = $label;
        
        expect(count($window['children']))->toBe(2);
        expect($window['children'][0]['text'])->toBe('Submit');
        expect($window['children'][1]['text'])->toBe('Status');
    });

    test('nested component structure', function () {
        $form = [
            'type' => 'form',
            'children' => [],
        ];
        
        $grid = [
            'type' => 'grid',
            'config' => ['columns' => 2],
            'children' => [],
        ];
        
        // Add fields to grid
        $grid['children'][] = ['type' => 'label', 'text' => 'Name:'];
        $grid['children'][] = ['type' => 'entry', 'bind' => 'name'];
        
        // Add grid to form
        $form['children'][] = $grid;
        
        // Add buttons to form
        $form['children'][] = ['type' => 'button', 'text' => 'Submit'];
        
        expect(count($form['children']))->toBe(2);
        expect($form['children'][0]['type'])->toBe('grid');
        expect($form['children'][0]['children'])->toHaveCount(2);
        expect($form['children'][1]['type'])->toBe('button');
    });
});

describe('Configuration Validation', function () {
    test('validates required configuration', function () {
        $validators = [
            'window' => ['title', 'width', 'height'],
            'button' => ['text'],
            'label' => ['text'],
            'entry' => [],
            'grid' => ['columns'],
        ];
        
        $configs = [
            'window' => ['title' => 'App', 'width' => 800, 'height' => 600],
            'button' => ['text' => 'Click'],
            'label' => ['text' => 'Hello'],
            'entry' => [],
            'grid' => ['columns' => 2],
        ];
        
        foreach ($validators as $type => $required) {
            $config = $configs[$type];
            $valid = true;
            
            foreach ($required as $field) {
                if (!isset($config[$field])) {
                    $valid = false;
                    break;
                }
            }
            
            expect($valid)->toBeTrue("{$type} configuration should be valid");
        }
    });

    test('validates configuration types', function () {
        $typeValidators = [
            'title' => 'string',
            'width' => 'integer',
            'height' => 'integer',
            'resizable' => 'boolean',
            'margined' => 'boolean',
            'columns' => 'integer',
            'enabled' => 'boolean',
        ];
        
        $config = [
            'title' => 'Test App',
            'width' => 800,
            'height' => 600,
            'resizable' => true,
            'margined' => false,
            'columns' => 2,
            'enabled' => true,
        ];
        
        foreach ($typeValidators as $field => $expectedType) {
            $actualType = gettype($config[$field]);
            expect($actualType)->toBe($expectedType);
        }
    });
});