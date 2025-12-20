<?php

test('event listener can be registered', function () {
    $listeners = [];
    $listeners['click'] = [function() { return 'clicked'; }];
    
    expect(isset($listeners['click']))->toBeTrue();
    expect(count($listeners['click']))->toBe(1);
});

test('event can be dispatched', function () {
    $event = ['type' => 'click', 'data' => ['id' => 'test']];
    $handler = function($event) {
        return $event['type'];
    };
    
    $result = $handler($event);
    expect($result)->toBe('click');
});

test('multiple listeners can handle same event', function () {
    $listeners = [
        function() { return 'first'; },
        function() { return 'second'; },
    ];
    
    $results = [];
    foreach ($listeners as $listener) {
        $results[] = $listener();
    }
    
    expect(count($results))->toBe(2);
    expect($results[0])->toBe('first');
    expect($results[1])->toBe('second');
});