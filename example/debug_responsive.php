<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Templates\ResponsiveGrid;

App::init();

// 调试ResponsiveGrid布局
$window = Builder::window()
    ->title('ResponsiveGrid 调试')
    ->size(800, 600)
    ->contains([
        ResponsiveGrid::create(12)
            ->col(Builder::label()->text('标题 - 应该占整行'), 12)  // Row 0, Col 0, Span 12
            ->col(Builder::separator(), 12)                         // Row 1, Col 0, Span 12
            ->col(Builder::label()->text('标签1 - 占4列'), 4)        // Row 2, Col 0, Span 4
            ->col(Builder::label()->text('标签2 - 占4列'), 4)        // Row 2, Col 4, Span 4
            ->col(Builder::label()->text('标签3 - 占4列'), 4)        // Row 2, Col 8, Span 4
            ->newRow()                                                // 强制换行
            ->col(Builder::label()->text('新行标签1 - 占6列'), 6)     // Row 3, Col 0, Span 6
            ->col(Builder::label()->text('新行标签2 - 占6列'), 6)     // Row 3, Col 6, Span 6
            ->col(Builder::button()->text('按钮1'), 3)                // Row 4, Col 0, Span 3
            ->col(Builder::button()->text('按钮2'), 3)                // Row 4, Col 3, Span 3
            ->col(Builder::button()->text('按钮3'), 3)                // Row 4, Col 6, Span 3
            ->col(Builder::button()->text('按钮4'), 3)                // Row 4, Col 9, Span 3
            ->col(Builder::separator(), 12)                         // Row 5, Col 0, Span 12
            ->col(Builder::button()->text('退出')->onClick(function() {
                App::quit();
            }), 12)                                                   // Row 6, Col 0, Span 12
            ->build()
    ]);

echo "ResponsiveGrid 调试信息：\n";
echo "- 总列数: 12\n";

$window->show();
App::main();