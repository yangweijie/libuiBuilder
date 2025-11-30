<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();

// 示例数据 - 第一列是文本（复选框列当前不支持）
$sampleData = [
    ['☐', 1, '张三', '开发者', '在职'],
    ['☐', 2, '李四', '设计师', '在职'],
    ['☐', 3, '王五', '产品经理', '离职'],
    ['☐', 4, '赵六', '测试工程师', '在职'],
    ['☐', 5, '钱七', '运维工程师', '在职'],
];

$stateManager->set('tableData', $sampleData);
$stateManager->set('selectedCount', 0);

// 事件处理器
$handlers = [
    'handleRefresh' => function() use ($stateManager) {
        echo "刷新表格数据\n";
        $data = $stateManager->get('tableData');
        echo "当前数据: " . count($data) . " 条记录\n";
    },
    
    'handleSelectAll' => function() use ($stateManager) {
        $data = $stateManager->get('tableData');
        $stateManager->set('selectedCount', count($data));
        echo "全选 " . count($data) . " 条记录\n";
    },
    
    'handleClearSelection' => function() use ($stateManager) {
        $stateManager->set('selectedCount', 0);
        echo "清除选择\n";
    },
    
    'handleDelete' => function() use ($stateManager) {
        $selected = $stateManager->get('selectedCount');
        if ($selected > 0) {
            echo "删除选中的 {$selected} 条记录\n";
            $stateManager->set('selectedCount', 0);
        } else {
            echo "请先选择要删除的记录\n";
        }
    },
    
    'handleExport' => function() use ($stateManager) {
        $data = $stateManager->get('tableData');
        echo "导出 " . count($data) . " 条记录\n";
    }
];

// 监听选择变化
$stateManager->watch('selectedCount', function($count) {
    echo "当前选中: {$count} 条记录\n";
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/simple_table_demo.ui.html', $handlers);
echo "渲染完成\n";

// 先构建组件
$app->build();
echo "构建完成\n";

// WindowBuilder::show() 会自动调用 build() 和 App::main()
$app->show();
echo "窗口已关闭\n";