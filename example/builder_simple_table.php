<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

// 初始化应用
App::init();

// 创建窗口
$window = Builder::window()
    ->title('Simple Table with Builder Pattern (No Data Binding)')
    ->size(800, 600)
    ->margined(true)
    ->onClosing(function ($window) {
        App::quit();
        return 1;
    });

// 创建简单的表格（不使用数据绑定，避免内存问题）
$table = Builder::table()
    ->id('simpleTable')
    ->columns([
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
        ['title' => 'Email', 'type' => 'text'],
        ['title' => 'Department', 'type' => 'text'],
        ['title' => 'Salary', 'type' => 'text'],
    ]);

// 创建按钮
$buttonBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()
            ->text('Add Row')
            ->onClick(function() {
                echo "Add Row clicked\n";
            }),
        Builder::button()
            ->text('Edit Row')
            ->onClick(function() {
                echo "Edit Row clicked\n";
            }),
        Builder::button()
            ->text('Delete Row')
            ->onClick(function() {
                echo "Delete Row clicked\n";
            }),
    ]);

// 创建主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $buttonBox,
        $table,
    ]);

// 设置窗口内容
$window->contains([$mainContainer]);

// 显示窗口
$window->show();

// 运行主事件循环
App::main();