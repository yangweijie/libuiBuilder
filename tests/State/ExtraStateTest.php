<?php

test('state can store simple values', function () {
    $state = ['key' => 'value'];
    expect($state['key'])->toBe('value');
});

test('state can handle numbers', function () {
    $state = ['count' => 42];
    expect($state['count'])->toBe(42);
});

test('state can handle booleans', function () {
    $state = ['enabled' => true];
    expect($state['enabled'])->toBeTrue();
});