<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 创建表格（使用链式调用，不使用配置数组）
$table = Builder::table()
    ->columns([
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
    ])
    ->data([
        [1, 'Employee A'],
        [2, 'Employee B'],
    ]);

// 创建主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $table,
    ]);

// 创建窗口
$window = Builder::window()
    ->title('Minimal DataGrid Demo')
    ->size(600, 400)
    ->margined(true)
    ->onClosing(function ($window) {
        App::quit();
        return 1;
    })
    ->contains([$mainContainer]);

// 显示窗口
$window->show();

// 运行主事件循环
App::main();