<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 模拟数据 - 与 table_builder_final.php 相同的结构，但使用数据网格的列
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

// 定义列配置 - 只使用文本类型的列（安全类型）
$columns = [
    ['title' => 'ID', 'type' => 'text'],
    ['title' => 'Name', 'type' => 'text'],
    ['title' => 'Email', 'type' => 'text'],
    ['title' => 'Department', 'type' => 'text'],
    ['title' => 'Salary', 'type' => 'text'],
];

// 创建表格 - 与 table_builder_final.php 相同的方式
$table = Builder::table([
    'columns' => $columns,
    'data' => $sampleData,
    'editable' => false  // 禁用编辑功能以减少复杂性
]);

// 创建主容器（只包含表格，没有其他控件）
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $table,
    ]);

// 创建窗口
$window = Builder::window()
    ->title('Simple DataGrid (Builder API)')
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