<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();
$stateManager->set('name', '');
$stateManager->set('age', 25);
$stateManager->set('score', 75);
$stateManager->set('progress', 50);
$stateManager->set('comments', '');
$stateManager->set('selectedOption', 'option1');

// 定义事件处理器
$handlers = [
    'handleNameChange' => function() use ($stateManager) {
        $name = $stateManager->get('name');
        echo "姓名变更: {$name}\n";
    },
    
    'handleAgeChange' => function() use ($stateManager) {
        $age = $stateManager->get('age');
        echo "年龄变更: {$age}\n";
    },
    
    'handleScoreChange' => function() use ($stateManager) {
        $score = $stateManager->get('score');
        echo "分数变更: {$score}\n";
    },
    
    'handleProgressChange' => function() use ($stateManager) {
        $progress = $stateManager->get('progress');
        echo "进度变更: {$progress}%\n";
    },
    
    'handleCommentsChange' => function() use ($stateManager) {
        $comments = $stateManager->get('comments');
        echo "备注变更: {$comments}\n";
    },
    
    'handleOptionChange' => function() use ($stateManager) {
        $option = $stateManager->get('selectedOption');
        if (is_array($option)) {
            $option = $option['item'] ?? 'unknown';
        }
        echo "选项变更: {$option}\n";
    },
    
    'handleSubmit' => function() use ($stateManager) {
        echo "表单提交:\n";
        echo "姓名: " . $stateManager->get('name') . "\n";
        echo "年龄: " . $stateManager->get('age') . "\n";
        echo "分数: " . $stateManager->get('score') . "\n";
        echo "进度: " . $stateManager->get('progress') . "%\n";
        
        $option = $stateManager->get('selectedOption');
        if (is_array($option)) {
            $option = $option[0] ?? 'unknown';
        }
        echo "选项: " . $option . "\n";
        
        echo "备注: " . $stateManager->get('comments') . "\n";
    }
];

// 监听状态变化
$stateManager->watch('name', $handlers['handleNameChange']);
$stateManager->watch('age', $handlers['handleAgeChange']);
$stateManager->watch('score', $handlers['handleScoreChange']);
$stateManager->watch('progress', $handlers['handleProgressChange']);
$stateManager->watch('comments', $handlers['handleCommentsChange']);
$stateManager->watch('selectedOption', $handlers['handleOptionChange']);

// 从 HTML 渲染（使用标准 HTML 标签）
$app = HtmlRenderer::render(__DIR__ . '/views/standard_html_demo.ui.html', $handlers);
$app->show();