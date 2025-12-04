<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 数据 - 与原始 table_builder_final.php 相同的结构，但使用数据网格字段
$employees = [
    ['id' => 1, 'name' => 'Employee A', 'email' => 'emp1@company.com', 'department' => 'Engineering', 'salary' => 75000],
    ['id' => 2, 'name' => 'Employee B', 'email' => 'emp2@company.com', 'department' => 'Sales', 'salary' => 65000],
    ['id' => 3, 'name' => 'Employee C', 'email' => 'emp3@company.com', 'department' => 'Marketing', 'salary' => 70000],
];

// 创建表格数据模型 - 与原始示例相同的结构，但使用数据网格数据
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

// 定义列配置 - 使用文本类型的列
$columns = [
    ['title' => 'ID', 'type' => 'text'],
    ['title' => 'Name', 'type' => 'text'],
    ['title' => 'Email', 'type' => 'text'],
    ['title' => 'Department', 'type' => 'text'],
    ['title' => 'Salary', 'type' => 'text'],
];

// 创建上方面板（过滤和排序）
$topPanel = Builder::hbox()
    ->padded(true)
    ->contains([
        // 过滤区
        Builder::vbox()
            ->contains([
                Builder::label()->text('Filter:'),
                Builder::entry()->id('filterInput'),
                Builder::hbox()
                    ->contains([
                        Builder::button()->text('Search'),
                        Builder::button()->text('Clear'),
                    ])
            ]),
        
        // 排序区
        Builder::vbox()
            ->contains([
                Builder::label()->text('Sort by Column:'),
                Builder::spinbox()->min(0)->max(4)->value(0),
                Builder::button()->text('Toggle Sort'),
            ])
    ]);

// 创建表格
$table = Builder::table([
    'columns' => $columns,
    'data' => $tableData,
]);

// 创建下方面板（分页和操作）
$bottomPanel = Builder::hbox()
    ->padded(true)
    ->contains([
        // 分页控制
        Builder::hbox()
            ->contains([
                Builder::button()->text('Previous'),
                Builder::label()->text('Page 1 of 1'),
                Builder::button()->text('Next'),
                Builder::button()->text('Refresh'),
            ]),
        
        // 操作按钮
        Builder::hbox()
            ->contains([
                Builder::button()->text('Add Row'),
            ])
    ]);

// 创建主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $topPanel,
        $table,
        $bottomPanel,
    ]);

// 创建窗口
$window = Builder::window([
    'title' => "Advanced DataGrid Demo (Builder API)",
    'width' => 900,
    'height' => 600,
    'margined' => true,
    'onClosing' => function ($window) {
        \Kingbes\Libui\App::quit();
        return 1;
    }
])->contains([$mainContainer]);

// 初始化应用
\Kingbes\Libui\App::init();

// 显示窗口
$window->show();

// 运行主事件循环
\Kingbes\Libui\App::main();