<?php

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Templates\ResponsiveGrid;

require_once __DIR__ . '/../../vendor/autoload.php';
App::init();
// 使用响应式网格
$responsiveLayout = ResponsiveGrid::create(12)
    ->col(Builder::label()->text('标题'), 12)  // 全宽
    ->col(Builder::label()->text('左侧'), 6)   // 半宽
    ->col(Builder::label()->text('右侧'), 6)   // 半宽
    ->col(Builder::button()->text('1/4'), 3)  // 四分之一宽
    ->col(Builder::button()->text('1/4'), 3)
    ->col(Builder::button()->text('1/4'), 3)
    ->col(Builder::button()->text('1/4'), 3)
    ->build();


$app = Builder::window()
    ->title('完整的基础控件示例')
    ->size(700, 500)
    ->contains([$responsiveLayout]);
$app->show();