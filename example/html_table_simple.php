<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 学生成绩数据
$studentData = [
    ['2023001', '小明', '85', '92', '88', '265', '详情'],
    ['2023002', '小红', '90', '85', '92', '267', '详情'],
    ['2023003', '小刚', '78', '95', '80', '253', '详情'],
    ['2023004', '小丽', '88', '88', '90', '266', '详情']
];

// 使用内联HTML模板
$htmlTemplate = <<<'HTML'
<window title="简单表格示例" size="800,500">
    <grid padded="true">
        <label row="0" col="0" colspan="7" align="center" size="large">
            学生成绩表
        </label>
        
        <separator row="1" col="0" colspan="7" expand="horizontal"/>
        
        <!-- 简单表格 -->
        <table row="2" col="0" colspan="7" expand="both" bind="studentTable">
            <thead>
                <tr>
                    <th>学号</th>
                    <th>姓名</th>
                    <th>语文</th>
                    <th>数学</th>
                    <th>英语</th>
                    <th>总分</th>
                    <th>操作</th>
                </tr>
            </thead>
        </table>
        
        <separator row="3" col="0" colspan="7" expand="horizontal"/>
        
        <hbox row="4" col="0" colspan="7" align="center">
            <button onclick="handleAdd">添加学生</button>
            <button onclick="handleCalculate">计算平均分</button>
            <button onclick="handleExit">退出</button>
        </hbox>
        
        <label row="5" col="0" colspan="7" align="center" bind="statusText">
            共4名学生
        </label>
    </grid>
</window>
HTML;

// 事件处理器
$handlers = [
    'handleAdd' => function($button, $state) {
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
        $state->set('statusText', '共' . count($currentData) . '名学生');
    },
    
    'handleCalculate' => function($button, $state) {
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
            
            $state->set('statusText', "平均分 - 语文:{$avgChinese} 数学:{$avgMath} 英语:{$avgEnglish}");
        }
    },
    
    'handleExit' => function($button, $state) {
        echo "退出程序\n";
        App::quit();
    }
];

// 初始化状态
$state = StateManager::instance();
$state->set('studentData', $studentData);
$state->set('statusText', '共' . count($studentData) . '名学生');

// 创建临时HTML文件
$tempFile = tempnam(sys_get_temp_dir(), 'table_simple_') . '.ui.html';
file_put_contents($tempFile, $htmlTemplate);

// 渲染HTML模板
try {
    $app = HtmlRenderer::render($tempFile, $handlers);
    $app->show();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
} finally {
    // 清理临时文件
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}

