<?php

require_once __DIR__ . '/../vendor/autoload.php';


use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();


$stateManager = StateManager::instance();
$app = Builder::window()
    ->title('完整的控件演示')
    ->size(800, 700)
    ->contains([

        Builder::vbox()->contains([

            Builder::label()->text('基础控件演示'),
            Builder::hSeparator(), // 水平分隔符

            // 输入类控件

            Builder::grid()->form([

                [

                    'label' => Builder::label()->text('单行输入:'),

                    'control' => Builder::entry()

                        ->id('singleLineInput')

                        ->placeholder('输入文本')

                ],

                [

                    'label' => Builder::label()->text('多行输入:'),

                    'control' => Builder::multilineEntry()

                        ->id('multiLineInput')

                        ->placeholder('输入多行文本...')

                        ->wordWrap(true)

                ],

            ]),

            Builder::separator(),

            // 选择类控件
            Builder::hbox()->contains([
                Builder::vbox()->contains([
                    Builder::label()->text('复选框:'),
                    Builder::checkbox()
                        ->id('checkbox1')
                        ->text('选项1')
                        ->checked(true),
                    Builder::checkbox()
                        ->id('checkbox2')
                        ->text('选项2'),
                    Builder::checkbox()
                        ->id('checkbox3')
                        ->text('选项3'),
                ]),

                Builder::vSeparator(), // 垂直分隔符

                Builder::vbox()->contains([
                    Builder::label()->text('单选框:'),
                    Builder::radio()
                        ->id('radioGroup')
                        ->items(['选项A', '选项B', '选项C'])
                        ->selected(0)
                        ->onSelected(function($index) { echo "选择了: $index\n"; }),
                ]),
            ]),

            Builder::separator(),

            // 数值控件
            Builder::grid()->form([
                [
                    'label' => Builder::label()->text('数字输入:'),
                    'control' => Builder::spinbox()
                        ->id('spinboxInput')
                        ->range(0, 100)
                        ->value(50)
                ],
                [
                    'label' => Builder::label()->text('滑动条:'),
                    'control' => Builder::slider()
                        ->id('sliderInput')
                        ->range(0, 100)
                        ->value(30)
                        ->onChange(function($value) { echo "滑动到: $value\n"; })
                ],
                [
                    'label' => Builder::label()->text('下拉选择:'),
                    'control' => Builder::combobox()
                        ->id('comboboxInput')
                        ->items(['选择...', '北京', '上海', '广州', '深圳'])
                        ->selected(0)
                ],
            ]),

            Builder::separator(),

            // 进度条
            Builder::label()->text('进度演示:'),
            Builder::progressBar()->value(75),

            Builder::separator(),

            // 控制按钮
            Builder::hbox()->contains([
                Builder::button()
                    ->text('获取所有值')
                    ->onClick(function($button)use($stateManager) {
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
                    }),

                Builder::button()
                    ->text('重置表单')
                    ->onClick(function($button)use($stateManager) {
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
                    }),
            ]),
        ]),
    ]);

$app->show();