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

// 使用Grid布局，插入真实表格
$window = Builder::window()
    ->title('简单表格示例 - Grid布局（真实表格）')
    ->size(900, 600)
    ->contains([
        Builder::grid()->padded(true)->contains([
            // 标题 - 占整行
            Builder::label()
                ->text('学生成绩表')
                ->row(0)->col(0)->colspan(7)
                ->align('center'),
            
            // 分隔线 - 占整行
            Builder::separator()
                ->row(1)->col(0)->colspan(7)
                ->expand('horizontal'),
            
            // 真实表格 - 占整行
            Builder::table()
                ->headers(['学号', '姓名', '语文', '数学', '英语', '总分', '操作'])
                ->bind('studentData')
                ->row(2)->col(0)->colspan(7)
                ->expand('both')
                ->options([
                    'sortable' => true,
                    'multiSelect' => false
                ]),
            
            // 分隔线 - 占整行
            Builder::separator()
                ->row(3)->col(0)->colspan(7)
                ->expand('horizontal'),
            
            // 按钮容器 - 占整行
            Builder::hbox()
                ->contains([
                    Builder::button()->text('添加学生')->onClick(function($btn, $state) {
                        echo "添加新学生\n";
                        $currentData = $state->get('studentData', []);
                        $newId = count($currentData) + 1;
                        $currentData[] = [
                            "202300" . $newId,
                            "学生{$newId}",
                            rand(60, 100),
                            rand(60, 100),
                            rand(60, 100),
                            rand(180, 300),
                            '详情'
                        ];
                        $state->set('studentData', $currentData);
                        echo "现在有 " . count($currentData) . " 名学生\n";
                    }),
                    Builder::button()->text('计算平均分')->onClick(function($btn, $state) {
                        echo "计算平均分\n";
                        $currentData = $state->get('studentData', []);
                        if (!empty($currentData)) {
                            $totalChinese = array_sum(array_column($currentData, 2));
                            $totalMath = array_sum(array_column($currentData, 3));
                            $totalEnglish = array_sum(array_column($currentData, 4));
                            $count = count($currentData);
                            
                            $avgChinese = round($totalChinese / $count, 1);
                            $avgMath = round($totalMath / $count, 1);
                            $avgEnglish = round($totalEnglish / $count, 1);
                            
                            echo "平均分 - 语文:{$avgChinese} 数学:{$avgMath} 英语:{$avgEnglish}\n";
                        }
                    }),
                    Builder::button()->text('退出')->onClick(function($btn, $state) {
                        echo "退出程序\n";
                        App::quit();
                    })
                ])
                ->row(4)->col(0)->colspan(7)
                ->align('center'),
            
            // 状态文本 - 占整行
            Builder::label()
                ->text('共4名学生')
                ->bind('statusText')
                ->row(5)->col(0)->colspan(7)
                ->align('center')
        ])
    ]);

// 初始化状态
$state = StateManager::instance();
$state->set('studentData', $studentData);
$state->set('statusText', '共' . count($studentData) . '名学生');

$window->show();
App::main();