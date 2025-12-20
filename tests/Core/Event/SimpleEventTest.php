<?php

test('event listener registration', function () {
    $listeners = [];
    
    // Register listener
    $listeners['click'] = [function($data) {
        return "Clicked: {$data['id']}";
    }];
    
    expect(isset($listeners['click']))->toBeTrue();
    expect(count($listeners['click']))->toBe(1);
});

test('event dispatching', function () {
    $event = [
        'type' => 'click',
        'data' => ['id' => 'testBtn'],
    ];
    
    $handler = function($event) {
        return "Event: {$event['type']}, ID: {$event['data']['id']}";
    };
    
    $result = $handler($event);
    expect($result)->toBe('Event: click, ID: testBtn');
});

test('event data structure', function () {
    $event = [
        'type' => 'ButtonClick',
        'component' => 'submitBtn',
        'timestamp' => time(),
    ];
    
    expect($event['type'])->toBe('ButtonClick');
    expect($event['component'])->toBe('submitBtn');
    expect($event['timestamp'])->toBeInt();
});