<?php
/**
 * 基础按钮示例 - HTML 模板模式
 * 
 * 演示内容：
 * - HTML 模板语法
 * - 事件处理器绑定
 * - 状态管理
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('clickCount', 0);
$state->set('buttonValue', '点击次数: 0');

// 定义事件处理器
$handlers = [
    'handleBasicClick' => function($button, $state) {
        echo "按钮被点击了！\n";
    },
    
    'handleCounterClick' => function($button, $state) {
        $count = $state->get('clickCount') + 1;
        $state->set('clickCount', $count);
        $state->set('buttonValue', "点击次数: {$count}");
        
        echo "计数器: {$count}\n";
    },
    
    'handleReset' => function($button, $state) {
        $state->set('clickCount', 0);
        $state->set('buttonValue', '点击次数: 0');
        echo "计数器已重置\n";
    },
    
    'handleQuit' => function($button) {
        App::quit();
    }
];

// 渲染 HTML 模板
$app = HtmlRenderer::render(__DIR__ . '/../views/basic_button.ui.html', $handlers);
$app->show();
