<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();

// 表格数据
$initialData = [
    ['id' => 1, 'name' => '张三', 'age' => 25, 'email' => 'zhangsan@example.com', 'status' => '活跃'],
    ['id' => 2, 'name' => '李四', 'age' => 30, 'email' => 'lisi@example.com', 'status' => '活跃'],
    ['id' => 3, 'name' => '王五', 'age' => 28, 'email' => 'wangwu@example.com', 'status' => '非活跃'],
    ['id' => 4, 'name' => '赵六', 'age' => 35, 'email' => 'zhaoliu@example.com', 'status' => '活跃'],
    ['id' => 5, 'name' => '钱七', 'age' => 22, 'email' => 'qianqi@example.com', 'status' => '非活跃'],
    ['id' => 6, 'name' => '孙八', 'age' => 40, 'email' => 'sunba@example.com', 'status' => '活跃'],
    ['id' => 7, 'name' => '周九', 'age' => 33, 'email' => 'zhoujiu@example.com', 'status' => '非活跃'],
    ['id' => 8, 'name' => '吴十', 'age' => 27, 'email' => 'wushi@example.com', 'status' => '活跃'],
];

$stateManager->set('tableData', $initialData);
$stateManager->set('currentPage', 1);
$stateManager->set('pageSize', 3);
$stateManager->set('selectedRows', []);
$stateManager->set('editingRow', null);
$stateManager->set('newRow', ['name' => '', 'age' => '', 'email' => '', 'status' => '活跃']);

// 分页计算函数
function calculatePagination($data, $page, $pageSize) {
    $total = count($data);
    $totalPages = ceil($total / $pageSize);
    $startIndex = ($page - 1) * $pageSize;
    $endIndex = min($startIndex + $pageSize, $total);
    $pageData = array_slice($data, $startIndex, $pageSize);
    
    return [
        'data' => $pageData,
        'total' => $total,
        'totalPages' => $totalPages,
        'startIndex' => $startIndex + 1,
        'endIndex' => $endIndex
    ];
}

// 事件处理器
$handlers = [
    'handlePageChange' => function($button) use ($stateManager) {
        $page = $stateManager->get('currentPage');
        $pageSize = $stateManager->get('pageSize');
        $data = $stateManager->get('tableData');
        
        // 简单的分页逻辑
        if ($page < ceil(count($data) / $pageSize)) {
            $stateManager->set('currentPage', $page + 1);
            echo "切换到第 " . ($page + 1) . " 页\n";
        }
    },
    
    'handlePrevPage' => function($button) use ($stateManager) {
        $page = $stateManager->get('currentPage');
        if ($page > 1) {
            $stateManager->set('currentPage', $page - 1);
            echo "切换到第 " . ($page - 1) . " 页\n";
        }
    },
    
    'handleNextPage' => function($button) use ($stateManager) {
        $page = $stateManager->get('currentPage');
        $pageSize = $stateManager->get('pageSize');
        $data = $stateManager->get('tableData');
        
        if ($page < ceil(count($data) / $pageSize)) {
            $stateManager->set('currentPage', $page + 1);
            echo "切换到第 " . ($page + 1) . " 页\n";
        }
    },
    
    'handleSelectAll' => function($button) use ($stateManager) {
        $page = $stateManager->get('currentPage');
        $pageSize = $stateManager->get('pageSize');
        $data = $stateManager->get('tableData');
        $pagination = calculatePagination($data, $page, $pageSize);
        
        $selectedIds = array_map(fn($row) => $row['id'], $pagination['data']);
        $stateManager->set('selectedRows', $selectedIds);
        echo "全选当前页，选中 " . count($selectedIds) . " 行\n";
    },
    
    'handleSelectRow' => function($button) use ($stateManager) {
        // 这里简化处理，实际应该根据具体行来选择
        echo "选择行\n";
    },
    
    'handleAddNew' => function($button) use ($stateManager) {
        $newRow = $stateManager->get('newRow');
        $data = $stateManager->get('tableData');
        
        if (!empty($newRow['name']) && !empty($newRow['age']) && !empty($newRow['email'])) {
            $newId = max(array_column($data, 'id')) + 1;
            $newRow['id'] = $newId;
            $data[] = $newRow;
            $stateManager->set('tableData', $data);
            $stateManager->set('newRow', ['name' => '', 'age' => '', 'email' => '', 'status' => '活跃']);
            echo "新增记录: {$newRow['name']}\n";
        } else {
            echo "请填写完整的新增信息\n";
        }
    },
    
    'handleEdit' => function($button) use ($stateManager) {
        $selected = $stateManager->get('selectedRows');
        if (!empty($selected)) {
            $data = $stateManager->get('tableData');
            $editRow = null;
            
            foreach ($data as $row) {
                if (in_array($row['id'], $selected)) {
                    $editRow = $row;
                    break;
                }
            }
            
            if ($editRow) {
                $stateManager->set('editingRow', $editRow);
                echo "开始编辑: {$editRow['name']}\n";
            }
        } else {
            echo "请先选择要编辑的行\n";
        }
    },
    
    'handleSaveEdit' => function($button) use ($stateManager) {
        $editingRow = $stateManager->get('editingRow');
        if ($editingRow) {
            $data = $stateManager->get('tableData');
            foreach ($data as &$row) {
                if ($row['id'] === $editingRow['id']) {
                    $row = $editingRow;
                    break;
                }
            }
            $stateManager->set('tableData', $data);
            $stateManager->set('editingRow', null);
            echo "保存编辑: {$editingRow['name']}\n";
        }
    },
    
    'handleDelete' => function($button) use ($stateManager) {
        $selected = $stateManager->get('selectedRows');
        if (!empty($selected)) {
            $data = $stateManager->get('tableData');
            $data = array_filter($data, fn($row) => !in_array($row['id'], $selected));
            $stateManager->set('tableData', array_values($data));
            $stateManager->set('selectedRows', []);
            echo "删除 " . count($selected) . " 条记录\n";
        } else {
            echo "请先选择要删除的行\n";
        }
    },
    
    'handleRefresh' => function($button) use ($stateManager) {
        echo "刷新数据\n";
    }
];

// 监听数据变化
$stateManager->watch('currentPage', function($page) use ($stateManager) {
    echo "当前页码: {$page}\n";
});

$stateManager->watch('selectedRows', function($selected) {
    echo "选中行数: " . count($selected) . "\n";
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/table_demo.ui.html', $handlers);
$app->show();
