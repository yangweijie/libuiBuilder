<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();
$stateManager->set('display', '0');
$stateManager->set('previousValue', null);
$stateManager->set('operation', null);
$stateManager->set('waitingForNewValue', false);

// 数学运算函数
function calculate($a, $b, $operation) {
    switch ($operation) {
        case '+': return $a + $b;
        case '-': return $a - $b;
        case '*': return $a * $b;
        case '/': return $b != 0 ? $a / $b : 0;
        default: return $b;
    }
}

function formatNumber($number) {
    // 格式化显示，避免科学计数法
    if (is_float($number) && abs($number) < 0.0001 && $number != 0) {
        return number_format($number, 8);
    }
    return $number;
}

// 定义事件处理器
$handlers = [
    'handleClear' => function() use ($stateManager) {
        $stateManager->update([
            'display' => '0',
            'previousValue' => null,
            'operation' => null,
            'waitingForNewValue' => false
        ]);
    },
    
    'handleClearEntry' => function() use ($stateManager) {
        $stateManager->set('display', '0');
        $stateManager->set('waitingForNewValue', false);
    },
    
    'handleBackspace' => function() use ($stateManager) {
        $display = $stateManager->get('display');
        if (strlen($display) > 1) {
            $stateManager->set('display', substr($display, 0, -1));
        } else {
            $stateManager->set('display', '0');
        }
    },
    
    'handleNumber' => function() use ($stateManager) {
        // 需要从按钮的 data-number 属性获取数字，但这里无法直接访问
        // 暂时使用一个通用的处理方式
        $display = $stateManager->get('display');
        $waitingForNewValue = $stateManager->get('waitingForNewValue');
        
        // 这里应该根据具体的按钮来确定数字
        // 由于 HTML 模板的限制，我们简化处理
        $number = '1'; // 默认值
        
        if ($waitingForNewValue || $display === '0') {
            $stateManager->set('display', $number);
            $stateManager->set('waitingForNewValue', false);
        } else {
            $stateManager->set('display', $display . $number);
        }
    },
    
    'handleDecimal' => function() use ($stateManager) {
        $display = $stateManager->get('display');
        $waitingForNewValue = $stateManager->get('waitingForNewValue');
        
        if ($waitingForNewValue) {
            $stateManager->set('display', '0.');
            $stateManager->set('waitingForNewValue', false);
        } elseif (strpos($display, '.') === false) {
            $stateManager->set('display', $display . '.');
        }
    },
    
    'handleOperation' => function() use ($stateManager) {
        $operation = '+'; // 默认值，实际应该从 data-operation 获取
        $currentValue = floatval($stateManager->get('display'));
        $previousValue = $stateManager->get('previousValue');
        $currentOperation = $stateManager->get('operation');
        
        if ($previousValue !== null && $currentOperation !== null) {
            $result = calculate($previousValue, $currentValue, $currentOperation);
            $stateManager->update([
                'display' => formatNumber($result),
                'previousValue' => $result,
                'operation' => $operation,
                'waitingForNewValue' => true
            ]);
        } else {
            $stateManager->update([
                'previousValue' => $currentValue,
                'operation' => $operation,
                'waitingForNewValue' => true
            ]);
        }
    },
    
    'handleEquals' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $previousValue = $stateManager->get('previousValue');
        $operation = $stateManager->get('operation');
        
        if ($previousValue !== null && $operation !== null) {
            $result = calculate($previousValue, $currentValue, $operation);
            $stateManager->update([
                'display' => formatNumber($result),
                'previousValue' => null,
                'operation' => null,
                'waitingForNewValue' => true
            ]);
        }
    }
];

// 监听显示值变化，确保格式正确
$stateManager->watch('display', function($newValue) {
    // 确保显示值始终是有效的数字格式
    if ($newValue === '' || $newValue === '-') {
        StateManager::instance()->set('display', '0');
    }
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/calculator.ui.html', $handlers);
$app->show();