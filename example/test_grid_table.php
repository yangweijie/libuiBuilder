<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

echo "测试Grid中的表格绑定\n";

// 测试数据
$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3']
];

// 创建表格
$table = Builder::table()
    ->headers(['列1', '列2', '列3'])
    ->id('testTable');

// 创建窗口
$window = Builder::window()
    ->title('Grid表格测试')
    ->size(800, 600)
    ->contains([
        Builder::grid()->padded(true)->contains([
            Builder::label()
                ->text('Grid中的表格测试')
                ->row(0)->col(0)->colspan(3)
                ->align('center'),
            
            Builder::separator()
                ->row(1)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            $table
                ->row(2)->col(0)->colspan(3)
                ->expand('both'),
            
            Builder::separator()
                ->row(3)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            Builder::button()
                ->text('设置数据')
                ->row(4)->col(0)
                ->onClick(function($btn, $state) use ($table) {
                    echo "手动设置表格数据\n";
                    $table->data([
                        ['X1', 'Y1', 'Z1'],
                        ['X2', 'Y2', 'Z2']
                    ]);
                }),
            
            Builder::button()
                ->text('退出')
                ->row(4)->col(1)
                ->onClick(function() {
                    App::quit();
                })
        ])
    ]);

// 先设置状态数据
$state = StateManager::instance();
$state->set('tableData', $testData);

// 然后绑定表格
echo "绑定表格到状态\n";
$table->bind('tableData');

echo "开始显示窗口\n";
$window->show();
App::main();