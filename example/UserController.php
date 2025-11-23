<?php
namespace  example;
use function LibUI\{view, share};

class UserController
{
    private array $users = [];
    private ?array $selectedUser = null;

    public function showUserManagement(): void
    {
        $this->loadUsers();

        view('user-management', [
            'users' => $this->users,
            'selectedUser' => $this->selectedUser,
            'selectedUserId' => $this->selectedUser['id'] ?? null,
            'isDirty' => false
        ], [
            'addUser' => [$this, 'addUser'],
            'editUser' => [$this, 'editUser'],
            'deleteUser' => [$this, 'deleteUser'],
            'selectUser' => [$this, 'selectUser'],
            'saveUser' => [$this, 'saveUser'],
            'resetUser' => [$this, 'resetUser'],
            'searchUsers' => [$this, 'searchUsers'],
            'markDirty' => [$this, 'markDirty']
        ]);
    }

    public function showLogin(): void
    {
        view('dialogs.login', [
            'loginForm' => [
                'username' => '',
                'password' => '',
                'remember' => false
            ]
        ], [
            'doLogin' => [$this, 'doLogin'],
            'cancelLogin' => [$this, 'cancelLogin'],
            'showRegister' => [$this, 'showRegister'],
            'forgotPassword' => [$this, 'forgotPassword']
        ]);
    }

    private function loadUsers(): void
    {
        // 加载用户数据
        $this->users = [
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'status' => '激活'],
            ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'status' => '禁用'],
            ['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com', 'status' => '待审核'],
        ];
    }

    public function selectUser(int $row): void
    {
        $this->selectedUser = $this->users[$row] ?? null;
        echo "选中用户: " . ($this->selectedUser['name'] ?? 'None') . "\n";
    }

    public function addUser(): void
    {
        echo "添加用户\n";
        // 显示添加用户对话框
    }

    public function doLogin(): void
    {
        echo "执行登录\n";
        // 登录逻辑
    }

    // ... 其他方法
}