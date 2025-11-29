<?php

use Kingbes\Libui\View\Builder;

describe('Builder 组件详细测试', function () {
    
    test('WindowBuilder 完整功能', function () {
        $window = Builder::window()
            ->title('Test Window')
            ->size(800, 600)
            ->centered(true)
            ->margined(true);
        
        expect($window)->toBeObject();
        
        // 测试链式调用返回值
        $result = $window->title('New Title');
        expect($result)->toBe($window);
    });
    
    test('GridBuilder 完整功能', function () {
        $grid = Builder::grid()->padded(true);
        
        $label1 = Builder::label()->text('Label 1');
        $label2 = Builder::label()->text('Label 2');
        
        $grid->place($label1, 0, 0);
        $grid->place($label2, 0, 1);
        
        expect($grid)->toBeObject();
    });
    
    test('BoxBuilder 垂直和水平', function () {
        $vbox = Builder::vbox()->padded(true);
        $hbox = Builder::hbox()->padded(true);
        
        $vbox->contains([Builder::label()->text('VBox Item')]);
        $hbox->contains([Builder::label()->text('HBox Item')]);
        
        expect($vbox)->toBeObject();
        expect($hbox)->toBeObject();
    });
    
    test('LabelBuilder 链式调用', function () {
        $label = Builder::label()
            ->text('Test Label')
            ->align('center')
            ->color([255, 0, 0]);
        
        expect($label)->toBeObject();
    });
    
    test('ButtonBuilder 事件和文本', function () {
        $clicked = false;
        
        $button = Builder::button()
            ->text('Click Me')
            ->onClick(function() use (&$clicked) {
                $clicked = true;
            });
        
        expect($button)->toBeObject();
        expect($clicked)->toBeFalse(); // 事件未触发
    });
    
    test('EntryBuilder 各种类型', function () {
        $entry = Builder::entry()
            ->placeholder('Enter text')
            ->readonly(false)
            ->bind('testField');
        
        $passwordEntry = Builder::passwordEntry()
            ->placeholder('Enter password');
        
        $multilineEntry = Builder::multilineEntry()
            ->placeholder('Enter multiple lines')
            ->wordWrap(true);
        
        expect($entry)->toBeObject();
        expect($passwordEntry)->toBeObject();
        expect($multilineEntry)->toBeObject();
    });
    
    test('CheckboxBuilder 和 RadioBuilder', function () {
        $checkbox = Builder::checkbox()
            ->text('Check me')
            ->checked(true)
            ->onToggled(function($checked) {
                return $checked;
            });
        
        $radio = Builder::radio()
            ->items(['Option 1', 'Option 2', 'Option 3'])
            ->selected(0)
            ->onSelected(function($index) {
                return $index;
            });
        
        expect($checkbox)->toBeObject();
        expect($radio)->toBeObject();
    });
    
    test('ComboboxBuilder 和 SpinboxBuilder', function () {
        $combobox = Builder::combobox()
            ->items(['Item 1', 'Item 2', 'Item 3'])
            ->selected(0);
        
        $editableCombobox = Builder::editableCombobox()
            ->items(['Editable 1', 'Editable 2']);
        
        $spinbox = Builder::spinbox()
            ->range(0, 100)
            ->value(50);
        
        expect($combobox)->toBeObject();
        expect($editableCombobox)->toBeObject();
        expect($spinbox)->toBeObject();
    });
    
    test('SliderBuilder 和 ProgressBarBuilder', function () {
        $slider = Builder::slider()
            ->range(0, 100)
            ->value(30)
            ->onChange(function($value) {
                return $value;
            });
        
        $progressBar = Builder::progressBar()
            ->value(75);
        
        expect($slider)->toBeObject();
        expect($progressBar)->toBeObject();
    });
    
    test('Separator 各种方向', function () {
        $separator = Builder::separator();
        $hSeparator = Builder::hSeparator();
        $vSeparator = Builder::vSeparator();
        
        expect($separator)->toBeObject();
        expect($hSeparator)->toBeObject();
        expect($vSeparator)->toBeObject();
    });
    
    test('TableBuilder 和 CanvasBuilder', function () {
        $table = Builder::table()
            ->columns(['Name', 'Age', 'City']);
        
        $canvas = Builder::canvas();
        
        expect($table)->toBeObject();
        expect($canvas)->toBeObject();
    });
    
    test('TabBuilder 标签页', function () {
        $tab = Builder::tab();
        
        $tabs = [
            'Tab 1' => [Builder::label()->text('Content 1')],
            'Tab 2' => [Builder::label()->text('Content 2')],
            'Tab 3' => [Builder::label()->text('Content 3')]
        ];
        
        $tab->tabs($tabs);
        
        expect($tab)->toBeObject();
    });
    
    test('MenuBuilder 和 MenuItemBuilder', function () {
        $menu = Builder::menu();
        
        expect($menu)->toBeObject();
    });
    
    test('组件 ID 和数据绑定', function () {
        $components = [
            Builder::entry()->id('username')->bind('username'),
            Builder::checkbox()->id('agree')->bind('agree'),
            Builder::radio()->id('choice')->bind('choice'),
            Builder::combobox()->id('option')->bind('option'),
            Builder::spinbox()->id('number')->bind('number'),
            Builder::slider()->id('range')->bind('range')
        ];
        
        foreach ($components as $component) {
            expect($component)->toBeObject();
        }
    });
});