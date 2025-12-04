<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 数据 - 与原始 table.php 类似的结构，但使用数据网格数据
$users = [
    ['name' => 'Employee A', 'age' => 25, 'btn' => '编辑', 'checked' => 1, 'checkboxText' => 0, 'progress' => 75],
    ['name' => 'Employee B', 'age' => 30, 'btn' => '编辑', 'checked' => 0, 'checkboxText' => 1, 'progress' => 85],
    ['name' => 'Employee C', 'age' => 35, 'btn' => '编辑', 'checked' => 1, 'checkboxText' => 0, 'progress' => 90],
];

// 创建表格数据模型
$tableData = [];
foreach ($users as $index => $user) {
    $tableData[] = [
        0 => $user['name'],      // 姓名列（实际是Name）
        1 => (string)$user['age'],       // 年龄列（实际是Salary）
        2 => $user['btn'],       // 操作列（实际是Email）
        3 => $user['checked'],   // 选择（复选框）（实际是ID）
        4 => $user['checkboxText'], // 选择列（复选框文本）（实际是Department）
        5 => $user['progress'],  // 进度列
    ];
}

// 定义列配置 - 与原始示例相同结构，但标题改为数据网格相关
$columns = [
    ['title' => 'Name', 'type' => 'text'],
    ['title' => 'Salary', 'type' => 'text', 'editable' => true],
    ['title' => 'Email', 'type' => 'button', 'editable' => true],
    ['title' => 'ID', 'type' => 'checkbox', 'editable' => true],
    ['title' => 'Department', 'type' => 'checkbox', 'editable' => true], // 使用普通复选框
    ['title' => 'Progress', 'type' => 'progress'],
];

// 创建表格
$table = Builder::table([
    'columns' => $columns,
    'data' => $tableData,
    'editable' => true
]);

// 创建窗口
$window = Builder::window([
    'title' => "Advanced DataGrid Demo (Builder API)",
    'width' => 1000,
    'height' => 600,
    'margined' => true,
    'onClosing' => function ($window) {
        echo "窗口关闭\n";
        App::quit();
        return 1;
    }
])->contains([$table]);  // 使用 contains 方法添加子组件

// 初始化应用
App::init();

// 显示窗口
$window->show();

// 运行主事件循环
App::main();