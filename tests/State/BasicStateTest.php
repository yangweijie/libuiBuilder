<?php

test('state can store values', function () {
    $state = ['key' => 'value'];
    expect($state['key'])->toBe('value');
});

test('state can check existence', function () {
    $state = ['exists' => true];
    expect(isset($state['exists']))->toBeTrue();
    expect(isset($state['missing']))->toBeFalse();
});