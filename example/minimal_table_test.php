<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

echo "开始创建最简单的表格测试\n";

// 最简单的表格测试
$window = Builder::window()
    ->title('最简单表格测试')
    ->size(800, 600)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()->text('表格测试'),
            Builder::table()
                ->headers(['列1', '列2', '列3'])
                ->data([
                    ['A1', 'B1', 'C1'],
                    ['A2', 'B2', 'C2'],
                    ['A3', 'B3', 'C3']
                ]),
            Builder::button()->text('退出')->onClick(function() {
                App::quit();
            })
        ])
    ]);

echo "窗口创建完成，开始显示\n";
$window->show();
App::main();