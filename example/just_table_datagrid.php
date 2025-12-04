<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 数据 - 与数据网格相同的字段，但只有3行
$employees = [
    ['id' => 1, 'name' => 'Employee A', 'email' => 'emp1@company.com', 'department' => 'Engineering', 'salary' => 75000],
    ['id' => 2, 'name' => 'Employee B', 'email' => 'emp2@company.com', 'department' => 'Sales', 'salary' => 65000],
    ['id' => 3, 'name' => 'Employee C', 'email' => 'emp3@company.com', 'department' => 'Marketing', 'salary' => 70000],
];

// 创建表格数据模型
$tableData = [];
foreach ($employees as $index => $emp) {
    $tableData[] = [
        0 => $emp['id'],         // ID列
        1 => $emp['name'],       // Name列
        2 => $emp['email'],      // Email列
        3 => $emp['department'], // Department列
        4 => $emp['salary'],     // Salary列
    ];
}

// 定义列配置
$columns = [
    ['title' => 'ID', 'type' => 'text'],
    ['title' => 'Name', 'type' => 'text'],
    ['title' => 'Email', 'type' => 'text'],
    ['title' => 'Department', 'type' => 'text'],
    ['title' => 'Salary', 'type' => 'text'],
];

// 创建表格
$table = Builder::table([
    'columns' => $columns,
    'data' => $tableData,
]);

// 创建窗口 - 直接包含表格，没有其他组件
$window = Builder::window([
    'title' => "Simple DataGrid (Builder API)",
    'width' => 900,
    'height' => 600,
    'margined' => true,
    'onClosing' => function ($window) {
        \Kingbes\Libui\App::quit();
        return 1;
    }
])->contains([$table]);  // 直接将表格作为窗口内容

// 初始化应用
\Kingbes\Libui\App::init();

// 显示窗口
$window->show();

// 运行主事件循环
\Kingbes\Libui\App::main();