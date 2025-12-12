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

echo "开始创建Grid布局表格测试\n";

// 直接使用Grid布局，直接设置数据
$table = Builder::table()
    ->headers(['学号', '姓名', '语文', '数学', '英语', '总分', '操作'])
    ->data($studentData)  // 直接设置数据
    ->id('studentTable')
    ->options([
        'sortable' => true,
        'multiSelect' => false
    ]);

// 混合布局：Grid + VBox，VBox包裹Table并占据多行
$window = Builder::window()
    ->title('简单表格示例 - Grid+VBox混合布局（修复版）')
    ->size(900, 600)
    ->contains([
        Builder::grid()->padded(true)->contains([
            // 标题 - Row 0, Col 0, Span 7
            Builder::label()
                ->text('学生成绩表')
                ->row(0)->col(0)->colspan(7),
            
            // VBox包裹分隔线+表格+分隔线 - Row 1, rowspan=10, 占据多行
            Builder::vbox()->padded(false)
                ->row(1)->col(0)->colspan(7)
                ->rowspan(10)  // VBox占10行！
                ->expand('both')
                ->contains([
                    Builder::separator(),
                    $table->stretchy(true),  // 表格在VBox中占据剩余空间
                    Builder::separator()
                ]),
            
            // 按钮区域 - Row 11
            Builder::button()
                ->text('添加学生')
                ->row(11)->col(0)
                ->onClick(function($btn, $state) {
                    echo "添加新学生\n";
                    $tableRef = $state->getComponent('studentTable');
                    if ($tableRef) {
                        $tableObj = $tableRef->getComponent();
                        $currentData = $tableObj->getConfig('data', []);
                        if (is_array($currentData)) {
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
                            $tableObj->data($currentData);
                            $state->set('statusText', '共' . count($currentData) . '名学生');
                            echo "现在有 " . count($currentData) . " 名学生\n";
                        }
                    }
                }),
                
            Builder::button()
                ->text('计算平均分')
                ->row(11)->col(1)
                ->onClick(function($btn, $state) {
                    echo "计算平均分\n";
                    $tableRef = $state->getComponent('studentTable');
                    if ($tableRef) {
                        $tableObj = $tableRef->getComponent();
                        $currentData = $tableObj->getConfig('data', []);
                        if (is_array($currentData) && !empty($currentData)) {
                            $totalChinese = array_sum(array_column($currentData, 2));
                            $totalMath = array_sum(array_column($currentData, 3));
                            $totalEnglish = array_sum(array_column($currentData, 4));
                            $count = count($currentData);
                            
                            $avgChinese = round($totalChinese / $count, 1);
                            $avgMath = round($totalMath / $count, 1);
                            $avgEnglish = round($totalEnglish / $count, 1);
                            
                            echo "平均分 - 语文:{$avgChinese} 数学:{$avgMath} 英语:{$avgEnglish}\n";
                        }
                    }
                }),
                
            Builder::button()
                ->text('退出')
                ->row(11)->col(2)
                ->onClick(function($btn, $state) {
                    echo "退出程序\n";
                    App::quit();
                }),
            
            // 状态文本 - Row 12, Col 0, Span 7
            Builder::label()
                ->text('共4名学生')
                ->bind('statusText')
                ->row(12)->col(0)->colspan(7)
        ])
    ]);

// 初始化状态（用于状态文本）
$state = StateManager::instance();
$state->set('statusText', '共' . count($studentData) . '名学生');

echo "表格数据直接设置完成\n";

echo "窗口创建完成，开始显示\n";
$window->show();
App::main();