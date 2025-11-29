<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

$stateManager = StateManager::instance();

// 定义事件处理器
$handlers = [
    'handleRadioChange' => function($index) {
        echo "选择了单选项: $index\n";
    },
    
    'handleSliderChange' => function($value) {
        echo "滑动到: $value\n";
    },
    
    'handleGetAllValues' => function($button) use ($stateManager) {
        echo "=== 表单数据 ===\n";
        
        // 获取单行输入值
        $singleLineValue = $stateManager->getComponent('singleLineInput')?->getValue() ?? 'N/A';
        echo "单行输入: $singleLineValue\n";
        
        // 获取多行输入值
        $multiLineValue = $stateManager->getComponent('multiLineInput')?->getValue() ?? 'N/A';
        echo "多行输入: $multiLineValue\n";
        
        // 获取复选框值
        $checkbox1Value = $stateManager->getComponent('checkbox1')?->getValue() ?? false;
        $checkbox2Value = $stateManager->getComponent('checkbox2')?->getValue() ?? false;
        $checkbox3Value = $stateManager->getComponent('checkbox3')?->getValue() ?? false;
        echo "复选框1 (选项1): " . ($checkbox1Value ? '已选中' : '未选中') . "\n";
        echo "复选框2 (选项2): " . ($checkbox2Value ? '已选中' : '未选中') . "\n";
        echo "复选框3 (选项3): " . ($checkbox3Value ? '已选中' : '未选中') . "\n";
        
        // 获取数字输入值
        $spinboxValue = $stateManager->getComponent('spinboxInput')?->getValue() ?? 'N/A';
        echo "数字输入: $spinboxValue\n";
        
        // 获取滑动条值
        $sliderValue = $stateManager->getComponent('sliderInput')?->getValue() ?? 'N/A';
        echo "滑动条: $sliderValue\n";
        
        // 获取下拉选择值
        $comboboxValue = $stateManager->getComponent('comboboxInput')?->getValue();
        $comboboxText = is_array($comboboxValue) ? ($comboboxValue['item'] ?? 'N/A') : 'N/A';
        echo "下拉选择: $comboboxText\n";
        
        echo "功能演示完成\n";
    },
    
    'handleResetForm' => function($button) use ($stateManager) {
        echo "表单已重置\n";
        
        // 重置所有控件值
        $stateManager->getComponent('singleLineInput')?->setValue('');
        $stateManager->getComponent('multiLineInput')?->setValue('');
        $stateManager->getComponent('checkbox1')?->setValue(false);
        $stateManager->getComponent('checkbox2')?->setValue(false);
        $stateManager->getComponent('checkbox3')?->setValue(false);
        $stateManager->getComponent('spinboxInput')?->setValue(50);
        $stateManager->getComponent('sliderInput')?->setValue(30);
        $stateManager->getComponent('comboboxInput')?->setValue(0);
    }
];

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/full.ui.html', $handlers);
$app->show();
