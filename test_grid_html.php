<?php
require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\Libui;

try {
    // 渲染HTML模板
    $component = HtmlRenderer::render(__DIR__ . '/grid_test.ui.html');
    
    // 显示组件
    $component->show();
    
    // 运行主循环
    Libui::run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}