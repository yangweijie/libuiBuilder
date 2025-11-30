<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();

// 初始化数据（将每行作为数值数组以匹配 TableBuilder 的索引访问）
$stateManager->set('users', [
    [1, '张三', '25', '技术部', '8000'],
    [2, '李四', '30', '设计部', '7500'],
    [3, '王五', '28', '产品部', '9000'],
]);
$stateManager->set('newUser', ['name' => '', 'age' => '', 'department' => '', 'salary' => '']);
$stateManager->set('editingUser', null);

// 事件处理器
$handlers = [
    'handleAddUser' => function() use ($stateManager) {
        $newUser = $stateManager->get('newUser');
        if (!empty($newUser['name']) && !empty($newUser['age'])) {
            $users = $stateManager->get('users');
            $nextId = 1;
            if (!empty($users)) {
                $ids = array_column($users, 0);
                $nextId = max($ids) + 1;
            }
            $users[] = [$nextId, $newUser['name'], (string)$newUser['age'], $newUser['department'], (string)$newUser['salary']];
            $stateManager->set('users', $users);
            $stateManager->set('newUser', ['name' => '', 'age' => '', 'department' => '', 'salary' => '']);
            echo "新增用户: {$newUser['name']}\n";
        } else {
            echo "请填写姓名和年龄\n";
        }
    },
    
    'handleEditUser' => function() use ($stateManager) {
        $users = $stateManager->get('users');
        if (!empty($users)) {
            // 这里简单地把第一个用户设置为 editingUser
            $stateManager->set('editingUser', $users[0]);
            echo "开始编辑: " . $users[0][1] . "\n";
        }
    },
    
    'handleSaveEdit' => function() use ($stateManager) {
        $editingUser = $stateManager->get('editingUser');
        if ($editingUser) {
            $users = $stateManager->get('users');
            foreach ($users as &$user) {
                if ($user[0] === $editingUser[0]) {
                    $user = $editingUser;
                    break;
                }
            }
            $stateManager->set('users', $users);
            $stateManager->set('editingUser', null);
            echo "保存编辑: " . $editingUser[1] . "\n";
        }
    },
    
    'handleDeleteUser' => function() use ($stateManager) {
        $users = $stateManager->get('users');
        if (!empty($users)) {
            array_pop($users); // 删除最后一个用户
            $stateManager->set('users', $users);
            echo "删除用户成功\n";
        }
    },
    
    'handleRefresh' => function() use ($stateManager) {
        $users = $stateManager->get('users');
        echo "当前用户数: " . count($users) . "\n";
    }
];

// 监听数据变化
$stateManager->watch('users', function($users) {
    echo "用户列表更新，共 " . count($users) . " 个用户\n";
});

$stateManager->watch('newUser', function($newUser) {
    if (!empty($newUser['name'])) {
        echo "新增用户表单: {$newUser['name']}\n";
    }
});

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/dynamic_table_demo.ui.html', $handlers);

// 构建以创建组件引用
$app->build();

// 获取表格引用以备后用
$userTableRef = $app->getComponentById('userTable');
if ($userTableRef) {
    echo "表格组件已获取引用\n";
}

// 初始隐藏或其他 UI 设置如果需要

// 仅在环境变量 RUN_GUI=1 时显示窗口并进入 GUI 主循环，避免在自动化/无头环境触发 segfault
if (getenv('RUN_GUI') === '1') {
    $app->show();
    App::main();
} else {
    echo "注意：未进入 GUI。要打开 GUI，请使用: RUN_GUI=1 php example/dynamic_table_demo.php\n";
}
