<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Templates\ResponsiveGrid;

App::init();

// 简单的ResponsiveGrid测试
$window = Builder::window()
    ->title('ResponsiveGrid 简单测试')
    ->size(800, 600)
    ->contains([
        ResponsiveGrid::create(12)
            ->col(Builder::label()->text('ResponsiveGrid 布局测试'), 12)  // 标题 - 占12列
            ->col(Builder::separator(), 12)                              // 分隔线 - 占12列
            ->col(Builder::label()->text('占12列'), 12)                    // 第一行：占12列
            ->col(Builder::label()->text('占6列'), 6)                      // 第二行：占6列
            ->col(Builder::label()->text('占6列'), 6)                      // 第二行：占6列
            ->col(Builder::label()->text('占4列'), 4)                      // 第三行：占4列
            ->col(Builder::label()->text('占4列'), 4)                      // 第三行：占4列
            ->col(Builder::label()->text('占4列'), 4)                      // 第三行：占4列
            ->col(Builder::button()->text('按钮占3列'), 3)                 // 第四行：按钮占3列
            ->col(Builder::button()->text('按钮占3列'), 3)                 // 第四行：按钮占3列
            ->col(Builder::button()->text('按钮占3列'), 3)                 // 第四行：按钮占3列
            ->col(Builder::button()->text('按钮占3列'), 3)                 // 第四行：按钮占3列
            ->col(Builder::label()->text('标签占2列'), 2)                   // 第五行：标签占2列
            ->col(Builder::button()->text('按钮占4列'), 4)                 // 第五行：按钮占4列
            ->col(Builder::label()->text('标签占6列'), 6)                   // 第五行：标签占6列
            ->col(Builder::separator(), 12)                              // 分隔线 - 占12列
            ->col(Builder::button()
                ->text('退出')
                ->onClick(function() {
                    App::quit();
                }), 12)  // 底部按钮 - 占12列
            ->build()
    ]);

$window->show();
App::main();