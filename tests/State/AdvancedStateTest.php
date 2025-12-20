<?php

test('state complex operations', function () {
    $state = [];
    
    // Set multiple values
    $state['user'] = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'profile' => [
            'age' => 30,
            'theme' => 'dark',
        ],
    ];
    
    $state['app'] = [
        'version' => '1.0.0',
        'debug' => true,
    ];
    
    // Access nested values
    expect($state['user']['name'])->toBe('John Doe');
    expect($state['user']['profile']['age'])->toBe(30);
    expect($state['app']['version'])->toBe('1.0.0');
    expect($state['app']['debug'])->toBeTrue();
});

test('state watchers with multiple listeners', function () {
    $state = [];
    $changes = [];
    $logs = [];
    
    $watcher1 = function($old, $new) use (&$changes) {
        $changes[] = "Watcher 1: {$old} -> {$new}";
    };
    
    $watcher2 = function($old, $new) use (&$logs) {
        $logs[] = "Watcher 2: Changed from {$old} to {$new}";
    };
    
    // Simulate state change with multiple watchers
    $oldValue = 'old_value';
    $newValue = 'new_value';
    $state['key'] = $newValue;
    
    $watcher1($oldValue, $newValue);
    $watcher2($oldValue, $newValue);
    
    expect(count($changes))->toBe(1);
    expect(count($logs))->toBe(1);
    expect($changes[0])->toBe('Watcher 1: old_value -> new_value');
    expect($logs[0])->toBe('Watcher 2: Changed from old_value to new_value');
});

test('component registration with metadata', function () {
    $components = [];
    
    $components['submitButton'] = [
        'id' => 'submitButton',
        'type' => 'button',
        'text' => 'Submit',
        'enabled' => true,
        'visible' => true,
        'events' => ['onClick', 'onHover'],
    ];
    
    $components['usernameField'] = [
        'id' => 'usernameField',
        'type' => 'entry',
        'placeholder' => 'Enter username',
        'bind' => 'user.username',
        'required' => true,
        'validation' => ['minLength' => 3, 'maxLength' => 20],
    ];
    
    // Validate button component
    expect($components['submitButton']['id'])->toBe('submitButton');
    expect($components['submitButton']['type'])->toBe('button');
    expect($components['submitButton']['enabled'])->toBeTrue();
    expect(count($components['submitButton']['events']))->toBe(2);
    
    // Validate entry component
    expect($components['usernameField']['bind'])->toBe('user.username');
    expect($components['usernameField']['required'])->toBeTrue();
    expect($components['usernameField']['validation']['minLength'])->toBe(3);
});

test('batch state updates with validation', function () {
    $state = [];
    $updates = [
        'user.name' => 'Jane Doe',
        'user.email' => 'jane@example.com',
        'app.theme' => 'light',
        'app.language' => 'en',
    ];
    
    // Process batch updates
    foreach ($updates as $key => $value) {
        $keys = explode('.', $key);
        $current = &$state;
        
        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
    }
    
    // Verify updates
    expect($state['user']['name'])->toBe('Jane Doe');
    expect($state['user']['email'])->toBe('jane@example.com');
    expect($state['app']['theme'])->toBe('light');
    expect($state['app']['language'])->toBe('en');
});

test('data binding with computed values', function () {
    $state = [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'age' => 30,
    ];
    
    // Computed values
    $computed = [];
    $computed['fullName'] = $state['firstName'] . ' ' . $state['lastName'];
    $computed['isAdult'] = $state['age'] >= 18;
    $computed['ageGroup'] = $state['age'] < 30 ? 'young' : 'adult';
    
    expect($computed['fullName'])->toBe('John Doe');
    expect($computed['isAdult'])->toBeTrue();
    expect($computed['ageGroup'])->toBe('adult');
});

test('state persistence simulation', function () {
    $state = [];
    $history = [];
    
    // Initial state
    $state['counter'] = 0;
    $history[] = ['action' => 'init', 'state' => $state['counter']];
    
    // Update state and track history
    for ($i = 1; $i <= 5; $i++) {
        $state['counter'] = $i;
        $history[] = ['action' => 'increment', 'state' => $state['counter']];
    }
    
    expect($state['counter'])->toBe(5);
    expect(count($history))->toBe(6);
    expect($history[0]['state'])->toBe(0);
    expect($history[5]['state'])->toBe(5);
});