<?php

test('basic test', function () {
    expect(true)->toBeTrue();
});

test('array test', function () {
    $array = [1, 2, 3];
    expect($array)->toHaveCount(3);
    expect($array)->toContain(2);
});

test('string test', function () {
    $string = 'Hello World';
    expect($string)->toBe('Hello World');
    expect($string)->toContain('World');
});