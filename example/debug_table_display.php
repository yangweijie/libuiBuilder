<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

echo "开始调试表格显示问题\n";

// 测试数据
$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3']
];

echo "创建表格组件\n";
$table = Builder::table()
    ->headers(['列1', '列2', '列3'])
    ->data($testData)
    ->id('debugTable');

echo "创建窗口\n";
$window = Builder::window()
    ->title('表格显示调试')
    ->size(1000, 700)
    ->contains([
        Builder::grid()->padded(true)->contains([
            // 标题 - 确保能看到
            Builder::label()
                ->text('=== 表格显示调试 ===')
                ->row(0)->col(0)->colspan(3)
                ->align('center')
                ->expand('horizontal'),
            
            Builder::separator()
                ->row(1)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            // 表格 - 尝试不同的位置和大小设置
            $table
                ->row(2)->col(0)->colspan(3)
                ->expand('both'),
            
            // 添加一些占位符来验证Grid布局
            Builder::label()
                ->text('表格应该在上方')
                ->row(3)->col(0)
                ->align('center'),
            
            Builder::label()
                ->text('表格应该在中间')
                ->row(4)->col(1)
                ->align('center'),
            
            Builder::label()
                ->text('表格应该在下方')
                ->row(5)->col(2)
                ->align('center'),
            
            Builder::separator()
                ->row(6)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            Builder::button()
                ->text('测试按钮1')
                ->row(7)->col(0),
            
            Builder::button()
                ->text('测试按钮2')
                ->row(7)->col(1),
            
            Builder::button()
                ->text('测试按钮3')
                ->row(7)->col(2),
            
            Builder::separator()
                ->row(8)->col(0)->colspan(3)
                ->expand('horizontal'),
            
            Builder::label()
                ->text('状态：表格应该显示3行数据')
                ->row(9)->col(0)->colspan(3)
                ->align('center')
                ->bind('statusText'),
            
            Builder::button()
                ->text('退出')
                ->row(10)->col(0)->colspan(3)
                ->align('center')
                ->onClick(function() {
                    App::quit();
                })
        ])
    ]);

// 初始化状态
$state = StateManager::instance();
$state->set('statusText', '初始状态：等待测试');

echo "开始显示窗口\n";
$window->show();
App::main();