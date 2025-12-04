<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\DataGrid;

// 初始化应用和状态管理器
App::init();
$sm = StateManager::instance();

// 定义列结构
$columns = [
    ['title' => 'ID', 'sortable' => true],
    ['title' => 'Name', 'sortable' => true],
    ['title' => 'Email', 'sortable' => true],
    ['title' => 'Department', 'sortable' => true],
    ['title' => 'Salary', 'sortable' => true],
];

// 创建 DataGrid 实例
$grid = new DataGrid('employeeGrid', $columns, [
    'pageSize' => 10,
    'sortable' => true,
    'filterable' => true,
]);

// 模拟数据
$sampleData = [];
for ($i = 1; $i <= 100; $i++) {
    $sampleData[] = [
        $i,
        "Employee " . chr(65 + ($i % 26)),
        "emp{$i}@company.com",
        ['Engineering', 'Sales', 'Marketing', 'HR'][($i - 1) % 4],
        rand(50000, 150000),
    ];
}

$grid->setData($sampleData);

// 定义事件处理器
$handlers = array_merge($grid->getHandlers(), [
    'addRow' => function() use ($sm, $grid) {
        $all = $sm->get('employeeGrid_all', []);
        $newId = count($all) + 1;
        $all[] = [$newId, 'New User', 'new@example.com', 'Engineering', 80000];
        $sm->set('employeeGrid_all', $all);
        $grid->refresh();
        echo "Added new row\n";
    },
]);

// 创建过滤栏
$filterBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Filter:'),
        Builder::entry()
            ->id('employeeGrid_filterInput')
            ->bind('employeeGrid_filterInput'),
        Builder::button()
            ->text('Search')
            ->onClick($handlers['filter']),
        Builder::button()
            ->text('Clear')
            ->onClick($handlers['clearFilter']),
    ]);

// 创建排序控制栏
$sortBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Sort by Column:'),
        Builder::spinbox()
            ->min(0)
            ->max(4)
            ->bind('employeeGrid_sortColumnIndex'),
        Builder::button()
            ->text('Toggle Sort')
            ->onClick($handlers['sort']),
    ]);

// 创建表格
$table = Builder::table()
    ->id('table')
    ->bind('employeeGrid_paged')
    ->columns([
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
        ['title' => 'Email', 'type' => 'text'],
        ['title' => 'Department', 'type' => 'text'],
        ['title' => 'Salary', 'type' => 'text'],
    ]);

// 创建分页控制
$paginationBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()
            ->text('Previous')
            ->onClick($handlers['prevPage']),
        Builder::label()
            ->id('employeeGrid_pageInfo')
            ->bind('employeeGrid_pageInfo'),
        Builder::button()
            ->text('Next')
            ->onClick($handlers['nextPage']),
        Builder::button()
            ->text('Refresh')
            ->onClick($handlers['refresh']),
    ]);

// 创建操作按钮
$actionBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()
            ->text('Add Row')
            ->onClick($handlers['addRow']),
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