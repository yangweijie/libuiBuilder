<?php
/**
 * 响应式网格布局示例 - HTML 模板模式
 * 
 * 演示内容：
 * - HTML 模板语法中的 Grid 布局
 * - 12列网格系统的比例分配
 * - colspan 属性控制列跨度
 * - 不同组件在网格中的布局
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;

App::init();

// 定义事件处理器
$handlers = [
    'handleSmallBtn' => function($button) {
        echo "小按钮被点击！\n";
    },
    
    'handleMediumBtn' => function($button) {
        echo "中等按钮被点击！\n";
    },
    
    'handleLargeBtn' => function($button) {
        echo "大按钮被点击！\n";
    },
    
    'handleSave' => function($button) {
        echo "保存操作执行\n";
    },
    
    'handleCancel' => function($button) {
        echo "取消操作执行\n";
    },
    
    'handleQuit' => function($button) {
        App::quit();
    }
];

// 渲染 HTML 模板
$renderer = new HtmlRenderer();
$app = $renderer->render(__DIR__ . '/../views/layouts/responsive_grid.ui.html', $handlers);
$app->show();
App::main();