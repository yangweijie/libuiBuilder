<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

echo "测试Grid布局 - Label垂直对齐\n";

$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3'],
    ['A4', 'B4', 'C4'],
    ['A5', 'B5', 'C5']
];

$table = Builder::table()
    ->headers(['列A', '列B', '列C'])
    ->data($testData);

$window = Builder::window()
    ->title('Grid布局测试 - 修复后')
    ->size(600, 400)
    ->contains([
        Builder::grid()->padded(true)->contains([
            // 标题 - 应该只占一行高度
            Builder::label()
                ->text('这是标题 - 应该只有一行高')
                ->row(0)->col(0)->colspan(3)
                ->expand('horizontal')
                ->align('center'),
            
            // 表格 - 应该占据主要空间
            $table
                ->row(1)->col(0)->colspan(3)
                ->expand('both'),
            
            // 底部按钮 - 应该只占一行高度
            Builder::button()
                ->text('退出')
                ->row(2)->col(0)
                ->onClick(function() {
                    echo "退出\n";
                    App::quit();
                })
        ])
    ]);

echo "窗口创建完成\n";
$window->show();
App::main();
