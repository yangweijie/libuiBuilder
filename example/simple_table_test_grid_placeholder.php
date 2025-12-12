<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 学生成绩数据
$studentData = [
    ['2023001', '小明', '85', '92', '88', '265', '详情'],
    ['2023002', '小红', '90', '85', '92', '267', '详情'],
    ['2023003', '小刚', '78', '95', '80', '253', '详情'],
    ['2023004', '小丽', '88', '88', '90', '266', '详情']
];

// 使用Grid布局，表格用标签占位（优化版 - VBox包裹主内容）
$window = Builder::window()
    ->title('简单表格示例 - Grid布局（占位）')
    ->size(900, 600)
    ->contains([
        Builder::grid()->padded(true)->contains([
            // 标题 - Row 0
            Builder::label()
                ->text('学生成绩表')
                ->row(0)->col(0)->colspan(7)
                ->rowspan(1)
                ->align('center'),
            
            // VBox包裹主要内容区域 - Row 1, rowspan=10
            Builder::vbox()->padded(false)
                ->row(1)->col(0)->colspan(7)
                ->rowspan(10)  // VBox占10行
                ->expand('both')
                ->contains([
                    Builder::separator(),
                    
                    // 用标签模拟表格位置和大小
                    Builder::label()
                        ->text('表格区域（占位）')
                        ->align('center'),
                    
                    // 模拟表格数据的标签
                    Builder::label()
                        ->text('学号: 2023001 | 姓名: 小明 | 语文: 85 | 数学: 92 | 英语: 88 | 总分: 265 | 操作: 详情'),
                    
                    Builder::label()
                        ->text('学号: 2023002 | 姓名: 小红 | 语文: 90 | 数学: 85 | 英语: 92 | 总分: 267 | 操作: 详情'),
                    
                    Builder::label()
                        ->text('学号: 2023003 | 姓名: 小刚 | 语文: 78 | 数学: 95 | 英语: 80 | 总分: 253 | 操作: 详情'),
                    
                    Builder::label()
                        ->text('学号: 2023004 | 姓名: 小丽 | 语文: 88 | 数学: 88 | 英语: 90 | 总分: 266 | 操作: 详情'),
                    
                    Builder::separator(),
                    
                    // 按钮容器
                    Builder::hbox()
                        ->contains([
                            Builder::button()->text('添加学生'),
                            Builder::button()->text('计算平均分'),
                            Builder::button()->text('退出')
                        ])
                ]),
            
            // 状态文本 - Row 11
            Builder::label()
                ->text('共4名学生')
                ->row(11)->col(0)->colspan(7)
                ->align('center')
        ])
    ]);

$window->show();
App::main();