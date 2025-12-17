<?php
require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\Libui;

// 创建一个测试窗口，包含Grid布局，验证colspan的宽度分配
$window = Builder::window()
    ->title('Grid ColSpan 测试')
    ->size(800, 600)
    ->contains([
        Builder::grid()
            ->padded(true)
            ->contains([
                // 第一个VBox：位置(0,0)，占用3列
                Builder::vbox()
                    ->place(0, 0, 1, 3)  // row=0, col=0, rowspan=1, colspan=3
                    ->contains([
                        Builder::label()->text('VBox 1 (colspan=3)')
                    ]),
                
                // 第二个VBox：位置(0,4)，占用50列
                Builder::vbox()
                    ->place(0, 4, 1, 50)  // row=0, col=4, rowspan=1, colspan=50
                    ->contains([
                        Builder::label()->text('VBox 2 (colspan=50)')
                    ])
            ])
    ]);

// 显示窗口
$window->show();

// 运行主循环
Libui::run();