<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Templates\ResponsiveGrid;

App::init();

// 学生成绩数据
$studentData = [
    ['2023001', '小明', '85', '92', '88', '265', '详情'],
    ['2023002', '小红', '90', '85', '92', '267', '详情'],
    ['2023003', '小刚', '78', '95', '80', '253', '详情'],
    ['2023004', '小丽', '88', '88', '90', '266', '详情']
];

// 使用ResponsiveGrid布局
$window = Builder::window()
    ->title('简单表格示例 - ResponsiveGrid布局')
    ->size(900, 600)
    ->contains([
        ResponsiveGrid::create(12)
            ->col(Builder::label()->text('学生成绩表'), 12)  // 标题 - 占12列
            ->col(Builder::separator(), 12)                    // 分隔线 - 占12列
            ->col(Builder::table()
                ->headers(['学号', '姓名', '语文', '数学', '英语', '总分', '操作'])
                ->bind('studentData')
                ->options([
                    'sortable' => true,
                    'multiSelect' => false
                ]), 12)  // 表格 - 占12列
            ->col(Builder::separator(), 12)                    // 分隔线 - 占12列
            ->col(Builder::button()
                ->text('添加学生')
                ->onClick(function($btn, $state) {
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
                }), 4)  // 按钮占4列
            ->col(Builder::button()
                ->text('计算平均分')
                ->onClick(function($btn, $state) {
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
                }), 4)  // 按钮占4列
            ->col(Builder::button()
                ->text('退出')
                ->onClick(function($btn, $state) {
                    echo "退出程序\n";
                    App::quit();
                }), 4)  // 按钮占4列
            ->col(Builder::label()
                ->text('共4名学生')
                ->bind('statusText'), 12)  // 状态文本 - 占12列
            ->build()
    ]);

// 初始化状态
$state = StateManager::instance();
$state->set('studentData', $studentData);
$state->set('statusText', '共' . count($studentData) . '名学生');

$window->show();
App::main();