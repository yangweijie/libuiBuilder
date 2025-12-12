<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 先初始化状态数据为空
$state = StateManager::instance();
$state->set('tableData', []);

echo "初始状态：空数据\n";

// 使用Builder API直接创建表格，测试空数据
$window = Builder::window()
    ->title('空表格测试')
    ->size(600, 300)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()->text('空表格测试 - 共' . count($state->get('tableData')) . '条记录'),
            Builder::table()
                ->id('emptyTable')
                ->headers(['列1', '列2', '列3'])
                ->bind('tableData')
                ->options([
                    'sortable' => true,
                    'multiSelect' => false
                ]),
            Builder::hbox()->contains([
                Builder::button()->text('添加数据')->onClick(function($btn, $state) {
                    echo "添加数据\n";
                    $state->set('tableData', [
                        ['A1', 'B1', 'C1'],
                        ['A2', 'B2', 'C2'],
                        ['A3', 'B3', 'C3']
                    ]);
                }),
                Builder::button()->text('清空数据')->onClick(function($btn, $state) {
                    echo "清空数据\n";
                    $state->set('tableData', []);
                }),
                Builder::button()->text('添加一行')->onClick(function($btn, $state) {
                    echo "添加一行\n";
                    $currentData = $state->get('tableData', []);
                    $rowCount = count($currentData) + 1;
                    $currentData[] = ["A{$rowCount}", "B{$rowCount}", "C{$rowCount}"];
                    $state->set('tableData', $currentData);
                })
            ])
        ])
    ]);

echo "窗口创建完成\n";
$window->show();
App::main();