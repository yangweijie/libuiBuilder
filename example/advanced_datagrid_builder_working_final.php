<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 创建一个简单布局，避免复杂的嵌套 - 参考 table_builder_final.php 的成功模式
// 仅创建表格部分，以确保稳定性

// 使用与原始 table_builder_final.php 相同的列类型模式
$employees = [
    ['name' => 'Employee A', 'salary' => 75000, 'action' => 'Edit', 'active' => true, 'reviewed' => false, 'performance' => 85],
    ['name' => 'Employee B', 'salary' => 65000, 'action' => 'Edit', 'active' => false, 'reviewed' => true, 'performance' => 92],
    ['name' => 'Employee C', 'salary' => 80000, 'action' => 'Edit', 'active' => true, 'reviewed' => true, 'performance' => 78],
];

// 创建表格数据模型 - 与原始示例相同格式
$tableData = [];
foreach ($employees as $index => $emp) {
    $tableData[] = [
        0 => $emp['name'],          // Name列
        1 => (string)$emp['salary'], // Salary列 - 转换为字符串
        2 => $emp['action'],         // Action列 - 按钮文本
        3 => $emp['active'],         // Active列 - 复选框
        4 => $emp['reviewed'],       // Reviewed列 - 复选框
        5 => $emp['performance'],    // Performance列 - 进度条
    ];
}

// 定义列配置 - 与原始示例相同的列类型以确保稳定性
$columns = [
    ['title' => 'Name', 'type' => 'text'],
    ['title' => 'Salary', 'type' => 'text', 'editable' => true],
    ['title' => 'Action', 'type' => 'button', 'editable' => true],
    ['title' => 'Active', 'type' => 'checkbox', 'editable' => true],
    ['title' => 'Reviewed', 'type' => 'checkbox', 'editable' => true],
    ['title' => 'Performance', 'type' => 'progress'],
];

// 创建表格 - 直接作为窗口内容（这是已验证能工作的方式）
$table = Builder::table([
    'columns' => $columns,
    'data' => $tableData,
    'editable' => true
]);

// 创建窗口
$window = Builder::window([
    'title' => "Advanced DataGrid Demo (Builder API Implementation)",
    'width' => 1000,
    'height' => 600,
    'margined' => true,
    'onClosing' => function ($window) {
        echo "窗口关闭\n";
        App::quit();
        return 1;
    }
])->contains([$table]);  // 直接包含表格，避免复杂的嵌套

// 显示窗口
$window->show();

// 运行主事件循环
App::main();