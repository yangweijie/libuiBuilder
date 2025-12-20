<?php
/**
 * 调试按钮事件触发
 */

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

echo "=== 按钮事件调试 ===\n";

App::init();

$state = StateManager::instance();
$state->set('clickCount', 0);
$state->set('buttonValue', '点击次数: 0');

// 定义事件处理器
$handlers = [
    'handleBasicClick' => function($button, $state) {
        echo "✅ 基础按钮事件处理器被调用了！\n";
        echo "   按钮 ID: " . ($button->getId() ?? 'unknown') . "\n";
        echo "   按钮类型: " . $button->getType() . "\n";
    },
    
    'handleCounterClick' => function($button, $state) {
        $count = $state->get('clickCount') + 1;
        $state->set('clickCount', $count);
        $state->set('buttonValue', "点击次数: {$count}");
        
        echo "✅ 计数器事件处理器被调用了！当前计数: {$count}\n";
    },
];

echo "\n=== 开始渲染 HTML ===\n";

// 渲染 HTML 模板
$renderer = new HtmlRenderer();
$app = $renderer->render(__DIR__ . '/example/views/basic_button.ui.html', $handlers);

echo "\n=== 渲染完成，显示窗口 ===\n";
echo "请点击窗口中的按钮来测试事件触发...\n";
echo "按 Ctrl+C 退出程序\n\n";

$app->show();
App::main();
}