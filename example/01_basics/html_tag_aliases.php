<?php
/**
 * HTML 标签别名演示 - HTML 模板模式
 * 
 * 演示内容：
 * - 标准 HTML 标签别名功能
 * - input type 属性映射
 * - 标准 HTML 标签映射
 * - 完整的 HTML 表单语法
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;

App::init();

// 定义事件处理器
$handlers = [
    'handleSubmit' => function($button) {
        echo "表单已提交！\n";
        echo "这是一个 HTML 标签别名演示，展示了 libuiBuilder 如何支持标准 HTML 语法。\n";
        echo "支持的标签别名包括：\n";
        echo "- <select> → Combobox\n";
        echo "- <progress> → ProgressBar\n";
        echo "- <hr> → Separator\n";
        echo "- <textarea> → MultilineEntry\n";
        echo "- input type=\"number\" → Spinbox\n";
        echo "- input type=\"range\" → Slider\n";
        echo "- input type=\"password\" → PasswordEntry\n";
    },
    
    'handleReset' => function($button) {
        echo "表单已重置\n";
        // 在实际应用中，这里应该重置所有表单字段
    },
    
    'handleClose' => function($button) {
        App::quit();
    }
];

// 渲染 HTML 模板
$renderer = new HtmlRenderer();
$app = $renderer->render(__DIR__ . '/../views/html_tag_aliases.ui.html', $handlers);
$app->show();