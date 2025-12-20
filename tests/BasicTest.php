<?php

test('basic functionality', function () {
    expect(true)->toBeTrue();
});

test('array operations', function () {
    $array = ['a' => 1, 'b' => 2];
    expect($array['a'])->toBe(1);
    expect(count($array))->toBe(2);
});

test('string operations', function () {
    $string = 'Hello World';
    expect($string)->toBe('Hello World');
    expect(strtoupper($string))->toBe('HELLO WORLD');
});