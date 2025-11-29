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
    if (is_float($number) && abs($number) < 0.0001 && $number != 0) {
        return number_format($number, 8);
    }
    return $number;
}

// 定义事件处理器 - 每个按钮独立的处理器
$handlers = [
    // 方向键组
    'handleBack' => function() use ($stateManager) {
        $display = $stateManager->get('display');
        if (strlen($display) > 1) {
            $stateManager->set('display', substr($display, 0, -1));
        } else {
            $stateManager->set('display', '0');
        }
    },
    
    'handleUp' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $stateManager->set('display', (string)($currentValue + 1));
    },
    
    'handleDown' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $stateManager->set('display', (string)($currentValue - 1));
    },
    
    'handleOne' => function() use ($stateManager) {
        $display = $stateManager->get('display');
        $waitingForNewValue = $stateManager->get('waitingForNewValue');
        
        if ($waitingForNewValue || $display === '0') {
            $stateManager->set('display', '1');
            $stateManager->set('waitingForNewValue', false);
        } else {
            $stateManager->set('display', $display . '1');
        }
    },
    
    // 符号键组
    'handleTilde' => function() use ($stateManager) {
        $stateManager->set('display', '~');
        $stateManager->set('waitingForNewValue', true);
    },
    
    'handleCaret' => function() use ($stateManager) {
        $stateManager->set('display', '^');
        $stateManager->set('waitingForNewValue', true);
    },
    
    'handleApprox' => function() use ($stateManager) {
        $stateManager->set('display', '≈');
        $stateManager->set('waitingForNewValue', true);
    },
    
    'handleZero' => function() use ($stateManager) {
        $display = $stateManager->get('display');
        $waitingForNewValue = $stateManager->get('waitingForNewValue');
        
        if ($waitingForNewValue || $display === '0') {
            $stateManager->set('display', '0');
            $stateManager->set('waitingForNewValue', false);
        } else {
            $stateManager->set('display', $display . '0');
        }
    },
    
    // 运算符组
    'handleSubtract' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $previousValue = $stateManager->get('previousValue');
        $currentOperation = $stateManager->get('operation');
        
        if ($previousValue !== null && $currentOperation !== null) {
            $result = calculate($previousValue, $currentValue, $currentOperation);
            $stateManager->update([
                'display' => formatNumber($result),
                'previousValue' => $result,
                'operation' => '-',
                'waitingForNewValue' => true
            ]);
        } else {
            $stateManager->update([
                'previousValue' => $currentValue,
                'operation' => '-',
                'waitingForNewValue' => true
            ]);
        }
    },
    
    'handleAdd' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $previousValue = $stateManager->get('previousValue');
        $currentOperation = $stateManager->get('operation');
        
        if ($previousValue !== null && $currentOperation !== null) {
            $result = calculate($previousValue, $currentValue, $currentOperation);
            $stateManager->update([
                'display' => formatNumber($result),
                'previousValue' => $result,
                'operation' => '+',
                'waitingForNewValue' => true
            ]);
        } else {
            $stateManager->update([
                'previousValue' => $currentValue,
                'operation' => '+',
                'waitingForNewValue' => true
            ]);
        }
    },
    
    'handleMultiply' => function() use ($stateManager) {
        $currentValue = floatval($stateManager->get('display'));
        $previousValue = $stateManager->get('previousValue');
        $currentOperation = $stateManager->get('operation');
        
        if ($previousValue !== null && $currentOperation !== null) {
            $result = calculate($previousValue, $currentValue, $currentOperation);
            $stateManager->update([
                'display' => formatNumber($result),
                'previousValue' => $result,
                'operation' => '*',
                'waitingForNewValue' => true
            ]);
        } else {
            $stateManager->update([
                'previousValue' => $currentValue,
                'operation' => '*',
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
    if ($newValue === '' || $newValue === '-') {
        StateManager::instance()->set('display', '0');
    }
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/calculator_simple.ui.html', $handlers);
$app->show();