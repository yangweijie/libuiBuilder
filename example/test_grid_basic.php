<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

echo "测试Grid基本功能\n";

// 最简单的Grid测试
$window = Builder::window()
    ->title('Grid基本测试')
    ->size(400, 300)
    ->contains([
        Builder::grid()->padded(true)->contains([
            Builder::label()
                ->text('标签1')
                ->row(0)->col(0),
            
            Builder::label()
                ->text('标签2')
                ->row(0)->col(1),
            
            Builder::button()
                ->text('按钮')
                ->row(1)->col(0)->colspan(2),
            
            Builder::button()
                ->text('退出')
                ->row(2)->col(0)->colspan(2)
                ->onClick(function() {
                    App::quit();
                })
        ])
    ]);

echo "开始显示窗口\n";
$window->show();
App::main();