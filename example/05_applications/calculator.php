<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
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

// 创建显示屏
$displayGrid = Builder::grid()->padded(false);
$displayGrid->place(
    Builder::entry()
        ->id('display')
        ->readOnly(true)
        ->align('end')
        ->text('0')
        ->bind('display'),
    0, 0
);

// 创建按钮网格
$buttonGrid = Builder::grid()->padded(true);

// 定义按钮配置
$buttons = [
    // 第一行
    ['C', 0, 0, 'clear'],
    ['CE', 0, 1, 'clearEntry'], 
    ['⌫', 0, 2, 'backspace'],
    ['÷', 0, 3, 'divide'],
    
    // 第二行
    ['7', 1, 0, 'seven'],
    ['8', 1, 1, 'eight'],
    ['9', 1, 2, 'nine'],
    ['×', 1, 3, 'multiply'],
    
    // 第三行
    ['4', 2, 0, 'four'],
    ['5', 2, 1, 'five'],
    ['6', 2, 2, 'six'],
    ['-', 2, 3, 'subtract'],
    
    // 第四行
    ['1', 3, 0, 'one'],
    ['2', 3, 1, 'two'],
    ['3', 3, 2, 'three'],
    ['+', 3, 3, 'add'],
    
    // 第五行
    ['0', 4, 0, 'zero', 2], // 跨2列
    ['.', 4, 2, 'decimal'],
    ['=', 4, 3, 'equals']
];

// 添加所有按钮到网格
foreach ($buttons as $buttonInfo) {
    $text = $buttonInfo[0];
    $row = $buttonInfo[1];
    $col = $buttonInfo[2];
    $id = $buttonInfo[3];
    $colSpan = $buttonInfo[4] ?? 1;
    
    $button = Builder::button()
        ->id($id)
        ->text($text);
    
    // 添加事件处理器
    switch ($id) {
        case 'clear':
            $button->onClick(function($button) use ($stateManager) {
                $stateManager->update([
                    'display' => '0',
                    'previousValue' => null,
                    'operation' => null,
                    'waitingForNewValue' => false
                ]);
            });
            break;
            
        case 'clearEntry':
            $button->onClick(function($button) use ($stateManager) {
                $stateManager->set('display', '0');
                $stateManager->set('waitingForNewValue', false);
            });
            break;
            
        case 'backspace':
            $button->onClick(function($button) use ($stateManager) {
                $display = $stateManager->get('display');
                if (strlen($display) > 1) {
                    $stateManager->set('display', substr($display, 0, -1));
                } else {
                    $stateManager->set('display', '0');
                }
            });
            break;
            
        case 'divide':
        case 'multiply':
        case 'subtract':
        case 'add':
            $operation = ['divide' => '/', 'multiply' => '*', 'subtract' => '-', 'add' => '+'][$id];
            $button->onClick(function($button) use ($stateManager, $operation) {
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
            });
            break;
            
        case 'zero':
        case 'one':
        case 'two':
        case 'three':
        case 'four':
        case 'five':
        case 'six':
        case 'seven':
        case 'eight':
        case 'nine':
            $number = $text;
            $button->onClick(function($button) use ($stateManager, $number) {
                $display = $stateManager->get('display');
                $waitingForNewValue = $stateManager->get('waitingForNewValue');
                
                if ($waitingForNewValue || $display === '0') {
                    $stateManager->set('display', $number);
                    $stateManager->set('waitingForNewValue', false);
                } else {
                    $stateManager->set('display', $display . $number);
                }
            });
            break;
            
        case 'decimal':
            $button->onClick(function($button) use ($stateManager) {
                $display = $stateManager->get('display');
                $waitingForNewValue = $stateManager->get('waitingForNewValue');
                
                if ($waitingForNewValue) {
                    $stateManager->set('display', '0.');
                    $stateManager->set('waitingForNewValue', false);
                } elseif (strpos($display, '.') === false) {
                    $stateManager->set('display', $display . '.');
                }
            });
            break;
            
        case 'equals':
            $button->onClick(function($button) use ($stateManager) {
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
            });
            break;
    }
    
    $buttonGrid->place($button, $row, $col, 1, $colSpan);
}

$app = Builder::window()
    ->title('计算器')
    ->size(150, 200)
    ->margined(true)
    ->centered(true)
    ->contains([
        Builder::vbox()->padded(true)->contains([
            // 显示屏
            $displayGrid,
            
            Builder::separator(),
            
            // 按钮区域
            $buttonGrid,
        ]),
    ]);

// 监听显示值变化，确保格式正确
$stateManager->watch('display', function($newValue) {
    if ($newValue === '' || $newValue === '-') {
        StateManager::instance()->set('display', '0');
    }
});

$app->show();