<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 先初始化状态数据
$state = StateManager::instance();
$state->set('tableData', [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3']
]);

echo "状态数据已设置，有 " . count($state->get('tableData')) . " 行数据\n";

// 最简单的表格测试
$window = Builder::window()
    ->title('简单表格测试')
    ->size(600, 300)
    ->contains([
        Builder::table()
            ->headers(['列1', '列2', '列3'])
            ->bind('tableData')
    ]);

echo "窗口已创建，开始运行...\n";
$window->show();
App::main();

