<?php

use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\Declarative\ComponentRegistry;
use Kingbes\Libui\Declarative\Components\ButtonComponent;
use Kingbes\Libui\Declarative\Components\EntryComponent;
use Kingbes\Libui\Declarative\Components\FormComponent;
use Kingbes\Libui\Declarative\Components\TemplateParser;
use Kingbes\Libui\Declarative\Components\WindowComponent;
use Kingbes\Libui\Declarative\Components\CheckboxComponent;
use Kingbes\Libui\Declarative\Components\LabelComponent;
use Kingbes\Libui\Declarative\Components\ComboboxComponent;
use Kingbes\Libui\Declarative\Components\BoxComponent;
use Kingbes\Libui\Declarative\Components\TableComponent;
use Kingbes\Libui\Declarative\ErrorHandler;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;

require_once __DIR__.'/../vendor/autoload.php';

// 注册全局错误处理器
ErrorHandler::register();

// 用户数据管理类
class UserDatabase
{
    private static array $users = [
        1 => ['id' => 1, 'name' => '张三', 'email' => 'zhangsan@example.com', 'role' => 'user', 'active' => true],
        2 => ['id' => 2, 'name' => '李四', 'email' => 'lisi@example.com', 'role' => 'admin', 'active' => true],
        3 => ['id' => 3, 'name' => '王五', 'email' => 'wangwu@example.com', 'role' => 'user', 'active' => false],
    ];
    
    public static function all(): array
    {
        return self::$users;
    }
    
    public static function find(int $id): ?array
    {
        return self::$users[$id] ?? null;
    }
    
    public static function where(string $field, string $operator, $value): array
    {
        $results = [];
        foreach (self::$users as $user) {
            $userValue = $user[$field] ?? null;
            $match = false;
            
            switch ($operator) {
                case '=':
                    $match = $userValue === $value;
                    break;
                case '!=':
                    $match = $userValue !== $value;
                    break;
                case 'like':
                    $match = stripos($userValue, $value) !== false;
                    break;
            }
            
            if ($match) {
                $results[] = $user;
            }
        }
        return $results;
    }
    
    public static function delete(int $id): bool
    {
        if (isset(self::$users[$id])) {
            unset(self::$users[$id]);
            return true;
        }
        return false;
    }
    
    public static function create(array $data): array
    {
        $id = max(array_keys(self::$users)) + 1;
        $user = array_merge(['id' => $id], $data);
        self::$users[$id] = $user;
        return $user;
    }
}

// 用户服务类
class UserService
{
    public static function getAllUsers(): array
    {
        return UserDatabase::all();
    }
    
    public static function getUserById(int $id): ?array
    {
        return UserDatabase::find($id);
    }
    
    public static function getUsersByRole(string $role): array
    {
        return UserDatabase::where('role', '=', $role);
    }
    
    public static function deleteById(int $id): bool
    {
        echo "删除用户 ID: {$id}\n";
        $result = UserDatabase::delete($id);
        if ($result) {
            // 更新UI状态
            StateManager::set('users', UserDatabase::all());
            EventBus::emit('user:deleted', $id);
        }
        return $result;
    }
    
    public static function batchDelete(array $userIds): int
    {
        echo "批量删除用户: " . implode(', ', $userIds) . "\n";
        $successCount = 0;
        foreach ($userIds as $userId) {
            if (self::deleteById($userId)) {
                $successCount++;
            }
        }
        return $successCount;
    }
    
    public static function validateDeletePermission(int $userId): bool
    {
        $user = self::getUserById($userId);
        return $user && $user['role'] !== 'admin';
    }
}

// 用户管理模板
$userManagementTemplate = '
<ui:window title="用户管理系统" width="1000" height="700" ref="mainWindow">
    <ui:box direction="vertical">
        <ui:label text="用户管理系统 - 展示高级特性" style="font-size: 16px; font-weight: bold;" />
        
        <!-- 搜索和筛选 -->
        <ui:box direction="horizontal">
            <ui:entry label="搜索" v-model="search.query" @change="searchUsers()" />
            <ui:combobox label="角色筛选" v-model="search.role" :options="json_decode(getState(\'roles\', \'[]\'), true)" @change="searchUsers()" />
            <ui:button text="搜索" @click="searchUsers()" />
            <ui:button text="重置" @click="resetSearch()" />
        </ui:box>
        
        <!-- 用户表格 -->
        <ui:table ref="userTable" title="用户列表" :data="json_decode(getState(\'users\', \'[]\'), true)">
            <!-- 表格会显示数据，但目前我们使用简单的布局 -->
            <ui:box direction="vertical" ref="userList">
                <!-- 动态生成用户列表项 -->
            </ui:box>
        </ui:table>
        
        <!-- 操作按钮 -->
        <ui:box direction="horizontal">
            <ui:button text="新增用户" @click="openUserDialog()" />
            <ui:button text="编辑选中" @click="editSelectedUser()" :disabled="!hasSelectedUser()" />
            <ui:button text="删除选中" @click="deleteSelectedUser()" :disabled="!hasSelectedUser()" />
            <ui:button text="批量删除" @click="UserService::batchDelete(getSelectedUserIds())" :disabled="count(getSelectedUserIds()) === 0" />
            <ui:button text="刷新数据" @click="loadUsers()" />
        </ui:box>
        
        <!-- 状态栏 -->
        <ui:label :text="\'总计: \'. count(json_decode(getState(\'users\', \'[]\'), true)) . \' 个用户, 选中: \'. count(getSelectedUserIds()) . \' 个\'" />
    </ui:box>
</ui:window>';

// 注册所有组件
App::init();
ComponentRegistry::register('ui:window', WindowComponent::class);
ComponentRegistry::register('ui:form', FormComponent::class);
ComponentRegistry::register('ui:entry', EntryComponent::class);
ComponentRegistry::register('ui:button', ButtonComponent::class);
ComponentRegistry::register('ui:label', LabelComponent::class);
ComponentRegistry::register('ui:combobox', ComboboxComponent::class);
ComponentRegistry::register('ui:checkbox', CheckboxComponent::class);
ComponentRegistry::register('ui:box', BoxComponent::class);
ComponentRegistry::register('ui:table', TableComponent::class);

// 初始化状态
StateManager::set('users', UserDatabase::all());
StateManager::set('selectedUsers', []);
StateManager::set('search', ['query' => '', 'role' => '']);
StateManager::set('roles', json_encode(['', 'user', 'admin']));

// 全局辅助函数
function openUserDialog() {
    echo "打开用户对话框\n";
    StateManager::set('dialogVisible', true);
    StateManager::set('dialogMode', 'create');
}

function editSelectedUser() {
    $selectedIds = getSelectedUserIds();
    if (count($selectedIds) > 0) {
        $userId = $selectedIds[0];
        echo "编辑用户 ID: {$userId}\n";
        StateManager::set('dialogVisible', true);
        StateManager::set('dialogMode', 'edit');
        StateManager::set('editingUserId', $userId);
    }
}

function deleteSelectedUser() {
    $selectedIds = getSelectedUserIds();
    if (count($selectedIds) > 0) {
        $userId = $selectedIds[0];
        if (UserService::validateDeletePermission($userId)) {
            UserService::deleteById($userId);
            echo "用户删除成功\n";
        } else {
            echo "没有删除权限或用户不存在\n";
        }
    }
}

function hasSelectedUser(): bool {
    return count(getSelectedUserIds()) > 0;
}

function getSelectedUserIds(): array {
    return StateManager::get('selectedUsers', []);
}

function loadUsers() {
    $allUsers = UserService::getAllUsers();
    StateManager::set('users', $allUsers);
    echo "用户数据已刷新，共 " . count($allUsers) . " 个用户\n";
}

function searchUsers() {
    $query = StateManager::get('search.query', '');
    $role = StateManager::get('search.role', '');
    
    if ($role) {
        $users = UserDatabase::where('role', '=', $role);
    } else {
        $users = UserDatabase::all();
    }
    
    // 简单的名称搜索
    if ($query) {
        $filteredUsers = [];
        foreach ($users as $user) {
            if (stripos($user['name'], $query) !== false || stripos($user['email'], $query) !== false) {
                $filteredUsers[] = $user;
            }
        }
        $users = $filteredUsers;
    }
    
    StateManager::set('users', $users);
    echo "搜索完成，找到 " . count($users) . " 个用户\n";
}

function resetSearch() {
    StateManager::set('search', ['query' => '', 'role' => '']);
    loadUsers(); // 重新加载所有用户
}

// 注册服务类到状态管理器
StateManager::set('userService', new UserService());

// 启用调试模式
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 渲染界面
$parser = new TemplateParser();
$app = $parser->parse($userManagementTemplate);
$window = $app->render();

// 监听全局事件
EventBus::on('button:clicked', function($ref, $result) {
    echo "[GLOBAL] Button {$ref} clicked with result: " . print_r($result, true) . "\n";
});

EventBus::on('entry:changed', function($ref, $value, $result) {
    echo "[GLOBAL] Entry {$ref} changed to: {$value}, result: " . print_r($result, true) . "\n";
});

echo "=== 用户管理系统启动 ===\n";
echo "支持以下高级特性:\n";
echo "- 方法链调用 (UserDatabase::where()...)\n";
echo "- 静态方法调用 (UserService::batchDelete())\n";
echo "- 状态管理\n";
echo "- 条件渲染\n";
echo "- 动态属性绑定\n";

// 初始加载用户数据
loadUsers();

Control::show($window);
App::main();
