<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();

// 示例数据
$initialData = [
    ['id' => 1, 'name' => '张三', 'age' => 25, 'department' => '技术部'],
    ['id' => 2, 'name' => '李四', 'age' => 30, 'department' => '设计部'],
    ['id' => 3, 'name' => '王五', 'age' => 28, 'department' => '产品部'],
];

$stateManager->set('tableData', $initialData);
$stateManager->set('showModal', false);
$stateManager->set('modalMode', 'add'); // 'add' or 'edit'
$stateManager->set('editingRow', null);

// 事件处理器
$handlers = [
    'handleAddNew' => function() use ($stateManager) {
        $stateManager->set('modalMode', 'add');
        $stateManager->set('showModal', true);
        echo "打开新增弹窗\n";
    },
    
    'handleEdit' => function() use ($stateManager) {
        $data = $stateManager->get('tableData');
        if (!empty($data)) {
            $stateManager->set('modalMode', 'edit');
            $stateManager->set('editingRow', $data[0]);
            $stateManager->set('showModal', true);
            echo "打开编辑弹窗: {$data[0]['name']}\n";
        }
    },
    
    'handleSave' => function() use ($stateManager) {
        $modalMode = $stateManager->get('modalMode');
        $data = $stateManager->get('tableData');
        
        if ($modalMode === 'add') {
            // 模拟新增数据
            $newRow = [
                'id' => max(array_column($data, 'id')) + 1,
                'name' => '新用户',
                'age' => 25,
                'department' => '新部门'
            ];
            $data[] = $newRow;
            echo "新增用户完成\n";
        } else {
            // 模拟编辑数据
            if (!empty($data)) {
                $data[0]['name'] = '已编辑用户';
                echo "编辑用户完成\n";
            }
        }
        
        $stateManager->set('tableData', $data);
        $stateManager->set('showModal', false);
    },
    
    'handleCancel' => function() use ($stateManager) {
        $stateManager->set('showModal', false);
        echo "取消操作\n";
    },
    
    'handleDelete' => function() use ($stateManager) {
        $data = $stateManager->get('tableData');
        if (!empty($data)) {
            array_pop($data);
            $stateManager->set('tableData', $data);
            echo "删除用户完成\n";
        }
    }
];

// 监听状态变化
$stateManager->watch('showModal', function($show) {
    echo "弹窗状态: " . ($show ? "显示" : "隐藏") . "\n";
});

$stateManager->watch('tableData', function($data) {
    echo "表格数据更新，共 " . count($data) . " 条记录\n";
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/simple_modal_demo.ui.html', $handlers);
$app->show();