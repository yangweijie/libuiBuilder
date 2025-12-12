<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 先初始化状态数据
$state = StateManager::instance();
$state->set('employeeData', [
    ['001', '张三', '前端工程师', '技术部'],
    ['002', '李四', '后端工程师', '技术部'],
    ['003', '王五', 'UI设计师', '设计部']
]);

// 使用Builder API直接创建表格，测试数据绑定
$window = Builder::window()
    ->title('表格数据绑定测试')
    ->size(800, 400)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()->text('员工信息表格 - 共' . count($state->get('employeeData')) . '条记录'),
            Builder::table()
                ->id('table')
                ->headers(['ID', '姓名', '职位', '部门'])
                ->bind('employeeData')  // 绑定到状态
                ->options([
                    'sortable' => true,
                    'multiSelect' => false
                ]),
            Builder::hbox()->contains([
                Builder::button()->text('添加员工')->onClick(function($btn, $state) {
                    echo "添加员工\n";
                    $currentData = $state->get('employeeData', []);
                    $newId = count($currentData) + 1;
                    $currentData[] = [
                        "ID{$newId}", 
                        "员工{$newId}", 
                        "职位{$newId}", 
                        "部门{$newId}"
                    ];
                    $state->set('employeeData', $currentData);
                    echo "现在有 " . count($currentData) . " 条记录\n";

                }),
                Builder::button()->text('清空表格')->onClick(function($btn, $state) {
                    echo "清空表格\n";
                    $state->set('employeeData', []);
                }),
                Builder::button()->text('加载数据')->onClick(function($btn, $state) {
                    echo "加载示例数据\n";
                    $state->set('employeeData', [
                        ['001', '张三', '前端工程师', '技术部'],
                        ['002', '李四', '后端工程师', '技术部'],
                        ['003', '王五', 'UI设计师', '设计部'],
                        ['004', '赵六', '产品经理', '产品部'],
                        ['005', '钱七', '测试工程师', '测试部']
                    ]);
                })
            ])
        ])
    ]);

$window->show();
App::main();


