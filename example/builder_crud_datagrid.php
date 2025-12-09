<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

// 创建窗口
$window = Builder::window()
    ->title('CRUD DataGrid with Builder Pattern')
    ->size(1000, 700)
    ->margined(true)
    ->onClosing(function ($window) {
        App::quit();
        return 1;
    });

// 模拟数据
$sampleData = [];
for ($i = 1; $i <= 100; $i++) {
    $sampleData[] = [
        'id' => $i,
        'name' => "Employee " . chr(65 + ($i % 26)),
        'email' => "emp{$i}@company.com",
        'department' => ['Engineering', 'Sales', 'Marketing', 'HR'][($i - 1) % 4],
        'salary' => rand(50000, 150000),
    ];
}

// 创建控件
$filterEntry = Builder::entry();
$searchBtn = Builder::button()->text('Search');
$clearBtn = Builder::button()->text('Clear');

$newBtn = Builder::button()->text('New');
$editBtn = Builder::button()->text('Edit');
$deleteBtn = Builder::button()->text('Delete');

$prevBtn = Builder::button()->text('Previous');
$nextBtn = Builder::button()->text('Next');
$pageLabel = Builder::label()->text('Page 1 of 10');

// 过滤布局
$filterBox = Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('Filter:'),
        $filterEntry,
        $searchBtn,
        $clearBtn,
    ]);

// 按钮布局
$buttonBox = Builder::hbox()
    ->padded(true)
    ->contains([
        $newBtn,
        $editBtn,
        $deleteBtn,
    ]);

// 分页布局
$paginationBox = Builder::hbox()
    ->padded(true)
    ->contains([
        $prevBtn,
        $pageLabel,
        $nextBtn,
    ]);

// 创建简单的表格数据（只显示前10条）
$tableData = [];
for ($i = 0; $i < 10; $i++) {
    $item = $sampleData[$i];
    $tableData[] = [
        $item['id'],
        $item['name'],
        $item['email'],
        $item['department'],
        $item['salary']
    ];
}

// 创建表格
$table = Builder::table()
    ->headers(['ID', 'Name', 'Email', 'Department', 'Salary'])
    ->options(['headerVisible'=>false])
    ->data($tableData);

// 主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $filterBox,
        $buttonBox,
        $table,
        $paginationBox,
    ]);

$window->contains([$mainContainer]);

// 事件处理函数（这些事件处理器在GUI线程安全中运行）
$searchBtn->onClick(function() use ($filterEntry) {
    echo "Search clicked: " . $filterEntry->getValue() . "\n";
});

$clearBtn->onClick(function() use ($filterEntry) {
    echo "Clear clicked\n";
    // 这里不能直接修改 entry，因为 Builder 模式中的控件可能没有 setText 方法
});

$prevBtn->onClick(function() {
    echo "Previous clicked\n";
});

$nextBtn->onClick(function() {
    echo "Next clicked\n";
});

$newBtn->onClick(function() {
    echo "New clicked\n";
});

$editBtn->onClick(function() {
    echo "Edit clicked\n";
});

$deleteBtn->onClick(function() {
    echo "Delete clicked\n";
});

// 显示窗口
$window->show();

// 运行主事件循环
App::main();