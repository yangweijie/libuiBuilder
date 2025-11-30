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
    ['id' => 1, 'name' => '张三', 'age' => 25, 'department' => '技术部', 'salary' => 8000],
    ['id' => 2, 'name' => '李四', 'age' => 30, 'department' => '设计部', 'salary' => 7500],
    ['id' => 3, 'name' => '王五', 'age' => 28, 'department' => '产品部', 'salary' => 9000],
    ['id' => 4, 'name' => '赵六', 'age' => 35, 'department' => '市场部', 'salary' => 8500],
    ['id' => 5, 'name' => '钱七', 'age' => 22, 'department' => '人事部', 'salary' => 7000],
];

$stateManager->set('tableData', $initialData);
$stateManager->set('showForm', false);
$stateManager->set('formTitle', '新增用户');
$stateManager->set('formData', ['name' => '', 'age' => '', 'department' => '', 'salary' => '']);

// 表单区域引用
$formAreaRef = null;
// 表格组件引用
$tableRef = null;
// 表单字段组件引用
$formNameRef = null;
$formAgeRef = null;
$formDeptRef = null;
$formSalaryRef = null;

// 添加表单区域控制函数
function updateFormVisibility($showForm) {
    global $formAreaRef;
    if ($formAreaRef) {
        if ($showForm) {
            $formAreaRef->show();
        } else {
            $formAreaRef->hide();
        }
    }
}

// 事件处理器
$handlers = [
    'handleAdd' => function() use ($stateManager) {
        $stateManager->set('showForm', true);
        $stateManager->set('formTitle', '新增用户');
        $stateManager->set('formData', ['name' => '', 'age' => '', 'department' => '', 'salary' => '']);
        updateFormVisibility(true);
        echo "打开新增表单\n";
    },
    
    'handleEdit' => function() use ($stateManager) {
        global $tableRef;
        if ($tableRef) {
            $tableRef->validateSelectedRow(); // 确保选中行有效
            $selectedRow = $tableRef->getSelectedRow();
            $selectedRowData = $tableRef->getSelectedRowData();
            
            echo "编辑前状态:\n";
            echo "- 选中的行: {$selectedRow}\n";
            echo "- 选中行数据: " . json_encode($selectedRowData) . "\n";
            
            if ($selectedRowData && $selectedRow >= 0) {
                // 先隐藏表单，然后重新设置内容并显示
                $stateManager->set('showForm', false);
                $stateManager->set('formTitle', '编辑用户');
                $stateManager->set('formData', $selectedRowData);
                $stateManager->set('showForm', true);
                updateFormVisibility(true);
                echo "切换到编辑表单: {$selectedRowData['name']}\n";
            } else {
                echo "请先选择要编辑的行\n";
            }
        } else {
            echo "表格组件引用失败\n";
        }
    },
    
    'handleSave' => function() use ($stateManager) {
        // 从StateManager获取表单数据（使用点路径）
        $formData = [
            'name' => $stateManager->get('formData.name', ''),
            'age' => $stateManager->get('formData.age', ''),
            'department' => $stateManager->get('formData.department', ''),
            'salary' => $stateManager->get('formData.salary', '')
        ];
        
        // 尝试获取完整的formData对象
        $completeFormData = $stateManager->get('formData');
        
        $data = $stateManager->get('tableData');
        $formTitle = $stateManager->get('formTitle');
        
        echo "操作前数据数量: " . count($data) . "\n";
        echo "完整formData对象: " . json_encode($completeFormData) . "\n";
        echo "重新组装的数据: " . json_encode($formData) . "\n";
        
        if ($formTitle === '新增用户') {
            // 新增用户
            $formData['id'] = max(array_column($data, 'id')) + 1;
            $data[] = $formData;
            echo "新增用户: {$formData['name']}, ID: {$formData['id']}\n";
        } else {
            // 编辑用户
            foreach ($data as &$row) {
                if ($row['id'] === $formData['id']) {
                    $row = $formData;
                    break;
                }
            }
            echo "编辑用户: {$formData['name']}\n";
        }
        
        echo "操作后数据数量: " . count($data) . "\n";
        
        $stateManager->set('tableData', $data);
        $stateManager->set('showForm', false);
        updateFormVisibility(false);
        
        // 确保表格组件更新
        global $tableRef;
        if ($tableRef) {
            $tableRef->refreshData();
        }
    },
    
    'handleCancel' => function() use ($stateManager) {
        $stateManager->set('showForm', false);
        updateFormVisibility(false);
        echo "取消操作\n";
    },
    
    'handleDelete' => function() use ($stateManager) {
        global $tableRef;
        if ($tableRef) {
            $tableRef->validateSelectedRow(); // 确保选中行有效
            $selectedRow = $tableRef->getSelectedRow();
            $data = $stateManager->get('tableData');
            
            echo "删除前状态:\n";
            echo "- 选中的行: {$selectedRow}\n";
            echo "- 数据总数: " . count($data) . "\n";
            
            if ($selectedRow >= 0 && $selectedRow < count($data)) {
                $deletedUser = $data[$selectedRow]['name'];
                echo "- 要删除的用户: {$deletedUser}\n";
                
                // 删除操作
                array_splice($data, $selectedRow, 1);
                
                echo "删除后状态:\n";
                echo "- 数据总数: " . count($data) . "\n";
                
                $stateManager->set('tableData', $data);
                $tableRef->setSelectedRow(-1); // 重置选择
                $tableRef->refreshData(); // 确保表格刷新
                
                echo "删除用户成功: {$deletedUser}\n";
            } else {
                echo "请先选择要删除的行\n";
            }
        } else {
            echo "表格组件引用失败\n";
        }
    },
    
    'handleRefresh' => function() use ($stateManager) {
        $data = $stateManager->get('tableData');
        echo "刷新数据，当前共 " . count($data) . " 条记录\n";
    }
];

// 监听状态变化
$stateManager->watch('tableData', function($data) {
    echo "表格数据更新，共 " . count($data) . " 条记录\n";
    // 确保表格组件也能接收到数据更新
    global $tableRef;
    if ($tableRef) {
        echo "表格组件接收到数据更新\n";
    }
});

$stateManager->watch('showForm', function($show) {
    echo "表单状态: " . ($show ? "显示" : "隐藏") . "\n";
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/working_table_demo.ui.html', $handlers);

// 先构建应用确保所有组件都创建
$app->build();

// 获取表单区域引用
$formAreaRef = $app->getComponentById('formArea');
// 获取表格组件引用
$tableRef = $app->getComponentById('table');
// 获取表单字段组件引用
$formNameRef = $app->getComponentById('formName');
$formAgeRef = $app->getComponentById('formAge');
$formDeptRef = $app->getComponentById('formDept');
$formSalaryRef = $app->getComponentById('formSalary');

// 调试输出
echo "组件引用获取结果:\n";
echo "- 表单区域: " . ($formAreaRef ? "成功" : "失败") . "\n";
echo "- 表格组件: " . ($tableRef ? "成功" : "失败") . "\n";
echo "- 姓名输入框: " . ($formNameRef ? "成功" : "失败") . "\n";
echo "- 年龄输入框: " . ($formAgeRef ? "成功" : "失败") . "\n";
echo "- 部门输入框: " . ($formDeptRef ? "成功" : "失败") . "\n";
echo "- 薪资输入框: " . ($formSalaryRef ? "成功" : "失败") . "\n";

// 初始隐藏表单
if ($formAreaRef) {
    $formAreaRef->hide();
}


$app->show();