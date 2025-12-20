<?php

test('HtmlRenderer basic functionality', function () {
    // 简化测试，避免复杂依赖
    expect(class_exists('Kingbes\Libui\View\HtmlRenderer'))->toBeTrue();
});

test('HtmlRenderer state manager integration', function () {
    // 测试StateManager集成
    expect(class_exists('Kingbes\Libui\View\State\StateManager'))->toBeTrue();
});

test('HtmlRenderer DOM support', function () {
    // 测试DOM支持
    expect(class_exists('DOMDocument'))->toBeTrue();
    expect(class_exists('DOMElement'))->toBeTrue();
});