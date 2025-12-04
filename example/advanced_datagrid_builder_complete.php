<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 数据 - 使用混合列类型以确保稳定性
$employees = [
    ['id' => 1, 'name' => 'Employee A', 'email' => 'Edit', 'dept' => true, 'active' => true, 'perf' => 75],
    ['id' => 2, 'name' => 'Employee B', 'email' => 'Edit', 'dept' => false, 'active' => false, 'perf' => 85],
    ['id' => 3, 'name' => 'Employee C', 'email' => 'Edit', 'dept' => true, 'active' => true, 'perf' => 90],
];

// 创建表格数据模型
$tableData = [];
foreach ($employees as $index => $emp) {
    $tableData[] = [
        0 => $emp['id'],         // ID列 - 数字
        1 => $emp['name'],       // Name列 - 文本
        2 => $emp['email'],      // Email列 - 按钮文本
        3 => $emp['dept'],       // Department列 - 复选框
        4 => $emp['active'],     // Active列 - 复选框
        5 => $emp['perf'],       // Performance列 - 进度条
    ];
}

// 定义列配置 - 使用多种列类型
$columns = [
    ['title' => 'ID', 'type' => 'text'],
    ['title' => 'Name', 'type' => 'text', 'editable' => true],
    ['title' => 'Email', 'type' => 'button', 'editable' => true],
    ['title' => 'Department', 'type' => 'checkbox', 'editable' => true],
    ['title' => 'Active', 'type' => 'checkbox', 'editable' => true],
    ['title' => 'Performance', 'type' => 'progress'],
];

// 创建上方面板（过滤和排序）
$topPanel = Builder::hbox()
    ->padded(true)
    ->contains([
        // 过滤区
        Builder::vbox()
            ->padded(true)
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
            ->padded(true)
            ->contains([
                Builder::label()->text('Sort by Column:'),
                Builder::spinbox()->min(0)->max(5)->value(0),
                Builder::button()->text('Toggle Sort'),
            ])
    ]);

// 创建表格
$table = Builder::table([
    'columns' => $columns,
    'data' => $tableData,
    'editable' => true
]);

// 创建下方面板（分页和操作）
$bottomPanel = Builder::hbox()
    ->padded(true)
    ->contains([
        // 分页控制
        Builder::hbox()
            ->contains([
                Builder::button()->text('Previous'),
                Builder::label()->text('Page 1 of 10'),
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
    'width' => 1000,
    'height' => 700,
    'margined' => true,
    'onClosing' => function ($window) {
        echo "窗口关闭\n";
        \Kingbes\Libui\App::quit();
        return 1;
    }
])->contains([$mainContainer]);  // 使用容器包含所有组件

// 初始化应用
\Kingbes\Libui\App::init();

// 显示窗口
$window->show();

// 运行主事件循环
\Kingbes\Libui\App::main();