<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 模拟数据 - 前10条
$sampleData = [];
for ($i = 1; $i <= 10; $i++) {
    $sampleData[] = [
        0 => $i,                                    // ID
        1 => "Employee " . chr(65 + ($i % 26)),     // Name
        2 => "emp{$i}@company.com",                 // Email
        3 => ['Engineering', 'Sales', 'Marketing', 'HR'][($i - 1) % 4], // Department
        4 => rand(50000, 150000),                   // Salary
    ];
}

// 创建表格数据
$tableRows = [];
foreach ($sampleData as $row) {
    $tableRows[] = [
        'id' => $row[0],
        'name' => $row[1],
        'email' => $row[2],
        'department' => $row[3],
        'salary' => $row[4],
    ];
}

// 直接创建表格（不使用绑定）
$table = Builder::table([
    'columns' => [
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
        ['title' => 'Email', 'type' => 'text'],
        ['title' => 'Department', 'type' => 'text'],
        ['title' => 'Salary', 'type' => 'text'],
    ],
    'data' => $sampleData,  // 使用原始格式
]);

// 创建过滤栏
$filterBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Filter:'),
        Builder::entry()
            ->id('filterInput'),
        Builder::button()
            ->text('Search')
            ->onClick(function() {
                echo "Search clicked\n";
            }),
        Builder::button()
            ->text('Clear')
            ->onClick(function() {
                echo "Clear clicked\n";
            }),
    ]);

// 创建排序控制栏
$sortBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Sort by Column:'),
        Builder::spinbox()
            ->min(0)
            ->max(4),
        Builder::button()
            ->text('Toggle Sort')
            ->onClick(function() {
                echo "Sort clicked\n";
            }),
    ]);

// 创建分页控制
$paginationBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()
            ->text('Previous')
            ->onClick(function() {
                echo "Previous page clicked\n";
            }),
        Builder::label()
            ->text('Page 1 of 10'),
        Builder::button()
            ->text('Next')
            ->onClick(function() {
                echo "Next page clicked\n";
            }),
        Builder::button()
            ->text('Refresh')
            ->onClick(function() {
                echo "Refresh clicked\n";
            }),
    ]);

// 创建操作按钮
$actionBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()
            ->text('Add Row')
            ->onClick(function() {
                echo "Add Row clicked\n";
            }),
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
    ->title('Advanced DataGrid Demo')
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