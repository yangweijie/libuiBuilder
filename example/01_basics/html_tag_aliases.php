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

// 保存表单组件引用
$formComponents = [];

// 定义事件处理器
$handlers = [
    'handleSubmit' => function($button) use (&$formComponents) {
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
    
    'handleReset' => function($button) use (&$formComponents) {
        echo "表单已重置\n";
        
        // 重置姓名输入框
        if (isset($formComponents['name'])) {
            echo "[RESET] 重置姓名输入框\n";
            $formComponents['name']->setValue('');
        }
        
        // 重置年龄 (spinbox)
        if (isset($formComponents['age'])) {
            echo "[RESET] 重置年龄为 25\n";
            $formComponents['age']->setValue(25); // 重置为默认值25
        }
        
        // 重置技能水平 (slider)
        if (isset($formComponents['skill'])) {
            echo "[RESET] 重置技能水平为 3\n";
            $formComponents['skill']->setValue(3); // 重置为中间值3
        }
        
        // 重置部门 (combobox)
        if (isset($formComponents['department'])) {
            echo "[RESET] 重置部门为第一项\n";
            $formComponents['department']->setSelected(0); // 重置为第一项
        }
        
        // 重置任务进度 (progressbar)
        if (isset($formComponents['progress'])) {
            echo "[RESET] 重置任务进度为 60\n";
            $formComponents['progress']->setValue(60); // 重置为默认值60
        }
        
        // 重置备注 (textarea)
        if (isset($formComponents['remarks'])) {
            echo "[RESET] 重置备注输入框\n";
            $formComponents['remarks']->setValue('');
        }
        
        echo "[RESET] 所有表单字段重置完成\n";
    },
    
    'handleClose' => function($button) {
        App::quit();
    }
];

// 渲染 HTML 模板
$renderer = new HtmlRenderer();
$app = $renderer->render(__DIR__ . '/../views/html_tag_aliases.ui.html', $handlers);

// 收集表单组件引用
function collectFormComponents($component, &$components) {
    // 确保 $component 是对象，不是数组
    if (!is_object($component)) {
        return;
    }
    
    if (method_exists($component, 'getId') && $component->getId()) {
        $components[$component->getId()] = $component;
        echo "[DEBUG] 收集组件: " . $component->getId() . " (" . get_class($component) . ")\n";
    }
    
    // 递归收集子组件 - GridBuilder 有 getItems() 方法
    if (method_exists($component, 'getItems')) {
        foreach ($component->getItems() as $item) {
            if (isset($item['component']) && is_object($item['component'])) {
                collectFormComponents($item['component'], $components);
            }
        }
    }
    
    // 其他容器类型的处理（如果有）
    if (method_exists($component, 'getChildren')) {
        foreach ($component->getChildren() as $child) {
            if (is_object($child)) {
                collectFormComponents($child, $components);
            }
        }
    }
}

collectFormComponents($app, $formComponents);
echo "已收集 " . count($formComponents) . " 个表单组件\n";

echo "已收集表单组件: " . implode(', ', array_keys($formComponents)) . "\n";

$app->show();
App::main();