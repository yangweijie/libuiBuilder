<?php
use Kingbes\Libui\App;

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

require_once __DIR__ . '/../../vendor/autoload.php';


App::init();

// 初始化状态
$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');
$state->set('isLoggedIn', false);
$state->set('userList', [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
]);

$app = Builder::window()
    ->title('事件处理和数据共享示例')
    ->size(800, 600)
    ->contains([
        Builder::tab()->tabs([
            // 登录表单标签页
            '登录' => Builder::grid()->form([
                [
                    'label' => Builder::label()->text('用户名:'),
                    'control' => Builder::entry()
                        ->id('usernameInput')  // 设置ID用于引用
                        ->bind('username')     // 绑定到状态
                        ->placeholder('请输入用户名')
                        ->onChange(function($value, $component) {
                            echo "用户名输入: {$value}\n";

                            // 访问其他组件
                            $loginBtn = StateManager::instance()->getComponent('loginBtn');
                            if ($loginBtn) {
                                // 根据输入启用/禁用登录按钮
                                $passwordInput = StateManager::instance()->getComponent('passwordInput');
                                $canLogin = !empty($value) && !empty($passwordInput?->getValue());
                                // Button::setEnabled($loginBtn->getHandle(), $canLogin);
                            }
                        })
                ],
                [
                    'label' => Builder::label()->text('密码:'),
                    'control' => Builder::entry()
                        ->id('passwordInput')
                        ->bind('password')
                        ->placeholder('请输入密码')
                        ->onChange(function($value, $component) {
                            // 实时验证密码强度
                            $strength = calculateStrength($value);

                            $statusLabel = StateManager::instance()->getComponent('statusLabel');

                            if ($statusLabel) {

                                $statusLabel->setValue("密码强度: {$strength}");

                            }

                        })
                ],
            ])->append([
                Builder::hbox()->contains([
                    Builder::button()
                        ->id('loginBtn')
                        ->text('登录')
                        ->onClick(function($button, $stateManager) {
                            $username = $stateManager->get('username');
                            $password = $stateManager->get('password');

                            if (empty($username) || empty($password)) {
                                echo "用户名和密码不能为空\n";
                                return;
                            }

                            // 模拟登录
                            if ($username === 'admin' && $password === 'admin') {
                                $stateManager->set('isLoggedIn', true);
                                echo "登录成功！\n";

                                // 切换到用户管理标签页
                                // Note: Tab switching would require a specific method in the TabBuilder
                                echo "已登录，切换到用户管理标签页\n";
                            } else {
                                echo "用户名或密码错误\n";
                            }
                        }),

                    Builder::button()
                        ->text('清空')
                        ->onClick(function($button, $stateManager) {
                            // 清空所有输入
                            $stateManager->update([
                                'username' => '',
                                'password' => ''
                            ]);
                            // 直接操作组件
                            StateManager::instance()->getComponent('usernameInput')?->setValue('');
                            StateManager::instance()->getComponent('passwordInput')?->setValue('');
                        }),
                ]),
                Builder::label()
                    ->id('statusLabel')
                    ->text('请输入登录信息'),
            ]),

            // 用户管理标签页
            '用户管理' => [
                Builder::hbox()->contains([
                    Builder::label()->text('搜索:'),
                    Builder::entry()
                        ->id('searchInput')
                        ->placeholder('输入用户名搜索')
                        ->onChange(function($value, $component) {
                            // 实时搜索
                            $userTable = StateManager::instance()->getComponent('userTable');
                            $allUsers = StateManager::instance()->get('userList');

                            if (empty($value)) {
                                $filteredUsers = $allUsers;
                            } else {
                                $filteredUsers = array_filter($allUsers, function($user) use ($value) {
                                    return stripos($user['name'], $value) !== false;
                                });
                            }

                            // 更新表格数据 - since there's no direct way to update table data in this example
                            // we just log the action for now
                            echo "搜索完成，找到 " . count($filteredUsers) . " 个结果\n";
                        }),

                    Builder::button()
                        ->text('添加用户')
                        ->onClick(function($button, $stateManager) {
                            // 打开添加用户对话框
                            echo "打开添加用户对话框\n";
                        }),
                    Builder::table()
                        ->id('userTable')
                        ->columns(['ID', '姓名', '邮箱'])
                        ->bind('userList')  // 绑定到用户列表状态
                        ->onRowSelected(function($row, $component) {
                            $users = StateManager::instance()->get('userList');
                            $selectedUser = $users[$row] ?? null;
                            if ($selectedUser) {
                                echo "选中用户: {$selectedUser['name']}";

                                // 填充编辑表单
                                StateManager::instance()->getComponent('editNameInput')?->setValue($selectedUser['name']);
                                StateManager::instance()->getComponent('editEmailInput')?->setValue($selectedUser['email']);
                                StateManager::instance()->set('selectedUserId', $selectedUser['id']);
                            }
                        }),

                    // 编辑区域
                    Builder::separator(),

                    Builder::grid()->form([
                        [
                            'label' => Builder::label()->text('编辑姓名:'),
                            'control' => Builder::entry()->id('editNameInput')
                        ],
                        [
                            'label' => Builder::label()->text('编辑邮箱:'),
                            'control' => Builder::entry()->id('editEmailInput')
                        ],
                    ])->append([
                        Builder::hbox()->contains([
                            Builder::button()
                                ->text('保存修改')
                                ->onClick(function($button, $stateManager) {
                                    $userId = $stateManager->get('selectedUserId');
                                    $newName = StateManager::instance()->getComponent('editNameInput')?->getValue();
                                    $newEmail = StateManager::instance()->getComponent('editEmailInput')?->getValue();

                                    if ($userId && $newName && $newEmail) {
                                        // 更新用户数据
                                        $users = $stateManager->get('userList');
                                        foreach ($users as &$user) {
                                            if ($user['id'] === $userId) {
                                                $user['name'] = $newName;
                                                $user['email'] = $newEmail;
                                                break;
                                            }
                                        }

                                        // 更新状态，表格会自动刷新
                                        $stateManager->set('userList', $users);
                                        echo "用户信息已更新\n";
                                    }
                                }),

                            Builder::button()
                                ->text('删除用户')
                                ->onClick(function($button, $stateManager) {
                                    $userId = $stateManager->get('selectedUserId');
                                    if ($userId) {
                                        $users = $stateManager->get('userList');
                                        $users = array_filter($users, fn($user) => $user['id'] !== $userId);
                                        $stateManager->set('userList', array_values($users));
                                        echo "用户已删除\n";
                                    }
                                }),
                        ])
                    ]),
                ]),
            ],
        ])->id('mainTabs')
            ->onTabSelected(function($tab) {
                echo "切换到标签页: Tab event received
";
            }),
    ]);

// 监听登录状态变化
$state->watch('isLoggedIn', function($isLoggedIn) {
    if ($isLoggedIn) {
        echo "用户已登录，显示管理界面\n";
    } else {
        echo "用户已登出\n";
    }
});

$app->show();