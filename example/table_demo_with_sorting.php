<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\SortIndicator;

App::init();

// 创建一个更完整的表格示例
$originalEmployees = [
    ['ID' => '3', 'Name' => 'Bob Johnson', 'Position' => 'Manager', 'Salary' => '85000'],
    ['ID' => '1', 'Name' => 'John Doe', 'Position' => 'Developer', 'Salary' => '75000'],
    ['ID' => '5', 'Name' => 'Charlie Brown', 'Position' => 'Tester', 'Salary' => '60000'],
    ['ID' => '2', 'Name' => 'Jane Smith', 'Position' => 'Designer', 'Salary' => '65000'],
    ['ID' => '4', 'Name' => 'Alice Williams', 'Position' => 'Analyst', 'Salary' => '70000']
];

$headers = ['ID', 'Name', 'Position', 'Salary'];

// 创建窗口
$window = Builder::window()
    ->title('Employee Table Example - Click headers to sort')
    ->size(800, 500)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()
                ->text('Employee Information Table - Click headers to sort'),
            Builder::table()
                ->headers($headers)
                ->data($originalEmployees)
                ->options([
                    'sortable' => true,
                    'multiSelect' => false,
                    'headerVisible' => true,
                    'columnWidths' => [50, 200, 150, 100]  // 设置列宽
                ])
                ->onEvent('onHeaderClicked', function($table, $column, $sortColumn, $sortDirection) use($headers) {
                    echo "Header clicked: $headers[$column]\n";
                    echo "Sorted by column $headers[$column] in $sortDirection order\n";
                    
                    // TableBuilder 内部已经处理了排序逻辑
                    // 只是打印信息供用户查看
                })
        ])
    ]);

$window->show();

