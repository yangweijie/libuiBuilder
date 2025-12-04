<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\DataGrid;

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

// 从 HTML 模板渲染
$app = HtmlRenderer::render(__DIR__ . '/views/advanced_datagrid_demo.ui.html', $handlers);
$app->show();
App::main();