<?php

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;

describe('Builder 链式辅助函数测试', function () {
    
    test('create() 返回 Builder 实例', function () {
        $builder = Builder::create();
        expect($builder)->toBeInstanceOf(Builder::class);
    });
    
    test('newWindow() 支持链式调用', function () {
        $builder = Builder::create()
            ->newWindow(['title' => 'Test Window']);
        
        $component = $builder->get();
        expect($component)->toBeInstanceOf(WindowBuilder::class);
    });
    
    test('withId() 设置组件ID', function () {
        $builder = Builder::create()
            ->newButton()
            ->withId('testButton');
        
        $component = $builder->get();
        expect($component)->toBeInstanceOf(ButtonBuilder::class);
    });
    
    test('config() 配置组件属性', function () {
        $builder = Builder::create()
            ->newLabel()
            ->config('text', 'Hello World');
        
        $component = $builder->get();
        expect($component)->toBeInstanceOf(\Kingbes\Libui\View\Components\LabelBuilder::class);
    });
    
    test('children() 添加子组件', function () {
        $button1 = Builder::button();
        $button2 = Builder::button();
        
        $builder = Builder::create()
            ->newVbox()
            ->children([$button1, $button2]);
        
        $component = $builder->get();
        expect($component)->toBeInstanceOf(BoxBuilder::class);
    });
    
    test('完整链式调用示例', function () {
        $builder = Builder::create()
            ->newWindow(['title' => 'Chain Test'])
            ->config('size', [800, 600])
            ->children([
                Builder::vbox()->contains([
                    Builder::label()->text('Label 1'),
                    Builder::button()->text('Button 1')
                ])
            ]);
        
        $window = $builder->get();
        expect($window)->toBeInstanceOf(WindowBuilder::class);
    });
    
    test('newVbox() 和 newHbox() 创建容器', function () {
        $vbox = Builder::create()->newVbox()->get();
        $hbox = Builder::create()->newHbox()->get();
        
        expect($vbox)->toBeInstanceOf(BoxBuilder::class);
        expect($hbox)->toBeInstanceOf(BoxBuilder::class);
    });
    
    test('newGrid() 创建网格', function () {
        $grid = Builder::create()
            ->newGrid(['padded' => true])
            ->get();
        
        expect($grid)->toBeInstanceOf(\Kingbes\Libui\View\Components\GridBuilder::class);
    });
    
    test('newEntry() 和 newTextarea() 创建输入控件', function () {
        $entry = Builder::create()->newEntry()->get();
        $textarea = Builder::create()->newTextarea()->get();
        
        expect($entry)->toBeInstanceOf(\Kingbes\Libui\View\Components\EntryBuilder::class);
        expect($textarea)->toBeInstanceOf(\Kingbes\Libui\View\Components\MultilineEntryBuilder::class);
    });
    
    test('newSlider() 和 newProgressBar() 创建进度控件', function () {
        $slider = Builder::create()->newSlider()->get();
        $progressBar = Builder::create()->newProgressBar()->get();
        
        expect($slider)->toBeInstanceOf(\Kingbes\Libui\View\Components\SliderBuilder::class);
        expect($progressBar)->toBeInstanceOf(\Kingbes\Libui\View\Components\ProgressBarBuilder::class);
    });
    
    test('addEvent() 添加事件处理器', function () {
        $called = false;
        
        $builder = Builder::create()
            ->newButton()
            ->addEvent('click', function() use (&$called) {
                $called = true;
            });
        
        $button = $builder->get();
        expect($button)->toBeInstanceOf(ButtonBuilder::class);
    });
    
    test('newTab() 创建标签页', function () {
        $tab = Builder::create()->newTab()->get();
        expect($tab)->toBeInstanceOf(\Kingbes\Libui\View\Builder\TabBuilder::class);
    });
    
    test('newTable() 创建表格', function () {
        $table = Builder::create()->newTable()->get();
        expect($table)->toBeInstanceOf(\Kingbes\Libui\View\Components\TableBuilder::class);
    });
    
    test('newCanvas() 创建画布', function () {
        $canvas = Builder::create()->newCanvas()->get();
        expect($canvas)->toBeInstanceOf(\Kingbes\Libui\View\Components\CanvasBuilder::class);
    });
    
    test('newSeparator() 和分隔符变体', function () {
        $separator = Builder::create()->newSeparator()->get();
        $hSeparator = Builder::create()->newHSeparator()->get();
        $vSeparator = Builder::create()->newVSeparator()->get();
        
        expect($separator)->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
        expect($hSeparator)->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
        expect($vSeparator)->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
    });
    
    test('newSpinbox() 创建数字输入框', function () {
        $spinbox = Builder::create()->newSpinbox()->get();
        expect($spinbox)->toBeInstanceOf(\Kingbes\Libui\View\Components\SpinboxBuilder::class);
    });
    
    test('newRadio() 创建单选按钮组', function () {
        $radio = Builder::create()->newRadio()->get();
        expect($radio)->toBeInstanceOf(\Kingbes\Libui\View\Components\RadioBuilder::class);
    });
    
    test('newPasswordEntry() 创建密码输入框', function () {
        $passwordEntry = Builder::create()->newPasswordEntry()->get();
        expect($passwordEntry)->toBeInstanceOf(\Kingbes\Libui\View\Components\EntryBuilder::class);
    });
    
    test('newEditableCombobox() 创建可编辑下拉框', function () {
        $editableCombo = Builder::create()->newEditableCombobox()->get();
        expect($editableCombo)->toBeInstanceOf(\Kingbes\Libui\View\Components\ComboboxBuilder::class);
    });
});
