<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;

// 初始化应用
App::init();

// 模拟数据 - 前10条
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

// 创建表格
$table = Builder::table()
    ->columns([
        ['title' => 'ID', 'type' => 'text'],
        ['title' => 'Name', 'type' => 'text'],
        ['title' => 'Email', 'type' => 'text'],
        ['title' => 'Department', 'type' => 'text'],
        ['title' => 'Salary', 'type' => 'text'],
    ])
    ->data($sampleData);

// 创建主容器
$mainContainer = Builder::vbox()
    ->padded(true)
    ->contains([
        $table,
    ]);

// 创建窗口
$window = Builder::window()
    ->title('Simple DataGrid Demo')
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