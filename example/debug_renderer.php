<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

echo "=== HtmlRenderer 调试信息 ===\n";

// 定义事件处理器
$handlers = [
    'handleLogin' => function($button, $stateManager) {
        echo "登录按钮被点击\n";
    }
];

try {
    // 从 HTML 渲染
    echo "1. 正在解析 HTML 模板...\n";
    $app = HtmlRenderer::render(__DIR__ . '/views/login.ui.html', $handlers);
    echo "   ✓ HTML 模板解析成功！\n";
    
    // 验证返回类型
    echo "2. 验证返回类型...\n";
    if ($app instanceof \Kingbes\Libui\View\Components\WindowBuilder) {
        echo "   ✓ 返回正确的 WindowBuilder 实例\n";
    } else {
        echo "   ✗ 返回类型错误: " . get_class($app) . "\n";
    }
    
    // 检查所有配置
    echo "3. 检查配置信息...\n";
    $reflection = new ReflectionClass($app);
    $configProperty = $reflection->getProperty('config');
    $configProperty->setAccessible(true);
    $config = $configProperty->getValue($app);
    
    echo "   配置内容:\n";
    foreach ($config as $key => $value) {
        echo "     $key: " . (is_scalar($value) ? $value : gettype($value)) . "\n";
    }
    
    echo "\n=== 调试完成 ===\n";
    echo "HtmlRenderer 工作正常！\n";
    
} catch (Exception $e) {
    echo "   ✗ 发生错误: " . $e->getMessage() . "\n";
    echo "   错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>