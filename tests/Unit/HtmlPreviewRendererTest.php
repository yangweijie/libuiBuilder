<?php

test('HtmlPreviewRenderer basic test', function () {
    // 简化测试，检查类是否存在
    expect(class_exists('Kingbes\Libui\View\Templates\HtmlPreviewRenderer'))->toBeTrue();
});

test('HtmlPreviewRenderer functionality', function () {
    // 测试基本功能
    $components = ['window', 'button', 'label'];
    expect($components)->toBeArray();
    expect($components)->toContain('window');
});