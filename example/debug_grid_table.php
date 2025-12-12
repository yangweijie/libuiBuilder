<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 测试数据
$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3']
];

echo "开始调试Grid中的表格\n";

// 创建表格
$table = Builder::table()
    ->headers(['列1', '列2', '列3'])
    ->data($testData)
    ->id('debugTable');

echo "表格创建完成\n";

// 创建窗口
$window = Builder::window()
    ->title('Grid表格调试')
    ->size(800, 600)
    ->contains([
        Builder::grid()->padded(true)->contains([
            Builder::label()
                ->text('调试标题')
                ->row(0)->col(0)->colspan(3)
                ->align('center'),
            
            Builder::separator()
                ->row(1)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            // 表格 - 尝试不同的设置
            $table
                ->row(2)->col(0)->colspan(3)
                ->expand('both'),
            
            Builder::separator()
                ->row(3)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            Builder::button()
                ->text('设置新数据')
                ->row(4)->col(0)
                ->onClick(function($btn, $state) use ($table) {
                    echo "手动设置新数据\n";
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

echo "窗口创建完成，开始显示\n";
$window->show();
App::main();