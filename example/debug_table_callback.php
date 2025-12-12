<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

echo "开始测试表格回调函数\n";

// 简单的测试数据
$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3']
];

// 创建表格
$table = Builder::table()
    ->headers(['列A', '列B', '列C'])
    ->data($testData)
    ->id('testTable');

// 创建简单窗口 - 使用VBox布局（最适合垂直堆叠场景）
$window = Builder::window()
    ->title('表格回调测试')
    ->size(400, 300)
    ->contains([
        Builder::vbox()->padded(true)->contains([
            Builder::label()->text('表格回调测试'),
            $table->stretchy(true),  // 表格自动占据剩余空间
            Builder::button()
                ->text('退出')
                ->onClick(function($btn, $state) {
                    echo "退出程序\n";
                    App::quit();
                })
        ])
    ]);

echo "窗口创建完成，开始显示\n";
$window->show();
App::main();