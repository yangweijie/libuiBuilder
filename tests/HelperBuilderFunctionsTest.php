<?php

use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;
use Kingbes\Libui\View\Components\LabelBuilder;
use Kingbes\Libui\View\Components\EntryBuilder;
use Kingbes\Libui\View\Components\GridBuilder;

require_once __DIR__ . '/../src/helper.php';

describe('Builder 快捷函数测试', function () {
    
    test('window() 快捷函数', function () {
        $win = window(['title' => 'Test']);
        expect($win)->toBeInstanceOf(WindowBuilder::class);
    });
    
    test('容器快捷函数', function () {
        expect(vbox())->toBeInstanceOf(BoxBuilder::class);
        expect(hbox())->toBeInstanceOf(BoxBuilder::class);
        expect(grid())->toBeInstanceOf(GridBuilder::class);
    });
    
    test('基础控件快捷函数', function () {
        expect(button())->toBeInstanceOf(ButtonBuilder::class);
        expect(label())->toBeInstanceOf(LabelBuilder::class);
        expect(entry())->toBeInstanceOf(EntryBuilder::class);
    });
    
    test('input() 快捷函数创建文本输入', function () {
        $field = input('用户名', 'username', placeholder: '请输入用户名');
        
        expect($field)->toBeArray();
        expect($field)->toHaveKey('label');
        expect($field)->toHaveKey('control');
        expect($field['label'])->toBeInstanceOf(LabelBuilder::class);
        expect($field['control'])->toBeInstanceOf(EntryBuilder::class);
    });
    
    test('input() 快捷函数创建密码输入', function () {
        $field = input('密码', 'password', type: 'password');
        
        expect($field)->toBeArray();
        expect($field['control'])->toBeInstanceOf(EntryBuilder::class);
    });
    
    test('input() 快捷函数创建多行文本', function () {
        $field = input('描述', 'description', type: 'textarea');
        
        expect($field)->toBeArray();
        expect($field['control'])->toBeInstanceOf(\Kingbes\Libui\View\Components\MultilineEntryBuilder::class);
    });
    
    test('select() 快捷函数创建下拉框', function () {
        $field = select('性别', 'gender', ['男', '女']);
        
        expect($field)->toBeArray();
        expect($field)->toHaveKey('label');
        expect($field)->toHaveKey('control');
        expect($field['control'])->toBeInstanceOf(\Kingbes\Libui\View\Components\ComboboxBuilder::class);
    });
    
    test('select() 快捷函数创建单选按钮', function () {
        $field = select('选项', 'option', ['A', 'B', 'C'], type: 'radio');
        
        expect($field)->toBeArray();
        expect($field['control'])->toBeInstanceOf(\Kingbes\Libui\View\Components\RadioBuilder::class);
    });
    
    test('快捷函数组合使用', function () {
        $form = grid()->form([
            input('用户名', 'username', placeholder: '请输入'),
            input('密码', 'password', type: 'password'),
            select('性别', 'gender', ['男', '女']),
        ]);
        
        expect($form)->toBeInstanceOf(GridBuilder::class);
    });
    
    test('分隔符快捷函数', function () {
        expect(separator())->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
        expect(hSeparator())->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
        expect(vSeparator())->toBeInstanceOf(\Kingbes\Libui\View\Components\SeparatorBuilder::class);
    });
    
    test('其他控件快捷函数', function () {
        expect(slider())->toBeInstanceOf(\Kingbes\Libui\View\Components\SliderBuilder::class);
        expect(progressBar())->toBeInstanceOf(\Kingbes\Libui\View\Components\ProgressBarBuilder::class);
        expect(spinbox())->toBeInstanceOf(\Kingbes\Libui\View\Components\SpinboxBuilder::class);
    });
});
