<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

echo "测试直接数据表格\n";

// 直接使用数据的表格测试
$window = Builder::window()
    ->title('直接数据表格测试')
    ->size(800, 600)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()->text('直接数据表格测试'),
            Builder::table()
                ->headers(['学号', '姓名', '成绩'])
                ->data([
                    ['001', '张三', '85'],
                    ['002', '李四', '92'],
                    ['003', '王五', '78']
                ]),
            Builder::button()->text('退出')->onClick(function() {
                App::quit();
            })
        ])
    ]);

echo "开始显示窗口\n";
$window->show();
App::main();