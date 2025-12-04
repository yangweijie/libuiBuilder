<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 模拟少量数据用于表格显示（仅当前页数据）
$pageData = [];
for ($i = 1; $i <= 10; $i++) {
    $pageData[] = [
        $i,
        "Employee " . chr(65 + ($i % 26)),
        "emp{$i}@company.com",
        ['Engineering', 'Sales', 'Marketing', 'HR'][($i - 1) % 4],
        rand(50000, 150000),
    ];
}

// 创建表格 - 只设置少量数据
$table = Builder::table()
    ->columns([
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
        ['title' => 'Email', 'type' => 'text'],
        ['title' => 'Department', 'type' => 'text'],
        ['title' => 'Salary', 'type' => 'text'],
    ])
    ->data($pageData);  // 设置数据

// 创建过滤栏
$filterBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Filter:'),
        Builder::entry()->id('filterInput'),
        Builder::button()->text('Search'),
        Builder::button()->text('Clear'),
    ]);

// 创建排序控制栏
$sortBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Sort by Column:'),
        Builder::spinbox()->min(0)->max(4),
        Builder::button()->text('Toggle Sort'),
    ]);

// 创建分页控制
$paginationBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()->text('Previous'),
        Builder::label()->text('Page 1 of 10'),
        Builder::button()->text('Next'),
        Builder::button()->text('Refresh'),
    ]);

// 创建操作按钮
$actionBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()->text('Add Row'),
    ]);

// 创建主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $filterBox,
        $sortBox,
        $table,
        $paginationBox,
        $actionBox,
    ]);

// 创建窗口
$window = Builder::window()
    ->title('Advanced DataGrid Demo (Builder API)')
    ->size(900, 600)
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