<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\Components\WindowBuilder;

// 创建一个简单的测试 HTML
$html = <<<HTML
<!DOCTYPE html>
<ui version="1.0">
  <window title="测试窗口" size="400,300">
    <grid padded="true">
      <label row="0" col="0">用户名:</label>
      <input row="0" col="1" bind="username" expand="horizontal"/>
      <button row="1" col="0" colspan="2" onclick="handleClick">点击我</button>
    </grid>
  </window>
</ui>
HTML;

// 写入临时文件
$tempFile = tempnam(sys_get_temp_dir(), 'test') . '.ui.html';
file_put_contents($tempFile, $html);

try {
    // 测试渲染器
    $handlers = [
        'handleClick' => function() {
            echo "按钮被点击了！\n";
        }
    ];
    
    $result = HtmlRenderer::render($tempFile, $handlers);
    
    echo "渲染成功！\n";
    echo "返回类型: " . get_class($result) . "\n";
    echo "是否为 WindowBuilder: " . ($result instanceof WindowBuilder ? "是" : "否") . "\n";
    
    // 清理临时文件
    unlink($tempFile);
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    // 清理临时文件
    unlink($tempFile);
}