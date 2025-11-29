#!/usr/bin/env php
<?php

/**
 * 计算器示例运行脚本
 * 
 * 使用方法:
 * php run_calculator.php [type]
 * 
 * type 参数:
 * - builder: 使用 Builder 模式 (默认)
 * - html: 使用 HTML 模板
 * - both: 同时运行两个版本
 */

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

$type = $argv[1] ?? 'builder';

echo "=== LibUI 计算器示例 ===\n";
echo "运行模式: $type\n";
echo "使用说明:\n";
echo "- 数字按钮 (0-9): 输入数字\n";
echo "- 运算符 (+, -, ×, ÷): 执行数学运算\n";
echo "- C: 清除所有\n";
echo "- CE: 清除当前输入\n";
echo "- ⌫: 退格\n";
echo "- .: 小数点\n";
echo "- =: 计算结果\n";
echo "========================\n\n";

App::init();

if ($type === 'html' || $type === 'both') {
    echo "启动 HTML 模板版本计算器...\n";
    
    try {
        // 使用修复后的简化版本
        $app = HtmlRenderer::render(__DIR__ . '/example/views/calculator_simple.ui.html', []);
        $app->show();
    } catch (Exception $e) {
        echo "HTML 模板版本错误: " . $e->getMessage() . "\n";
        if ($type === 'both') {
            echo "尝试运行 Builder 版本...\n";
            $type = 'builder';
        }
    }
}

if ($type === 'builder' || ($type === 'both' && !isset($app))) {
    echo "启动 Builder 模式版本计算器...\n";
    
    // 这里可以调用 calculator.php 中的代码
    include __DIR__ . '/example/calculator.php';
}

echo "计算器已启动！\n";
