<?php
use Kingbes\Libui\App;

use Kingbes\Libui\Control;
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
            '登录' => Builder::grid()
                ->columns(1)
                ->form([
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
            '用户管理' => Builder::grid()
                ->columns(3)
                ->padded(true)->append([
                // 左侧搜索区 (列0，行0-2)
                Builder::label()->text('搜索:')
                    ->row(0)->col(0)
                    ->align('fill', 'center'),
                
                Builder::entry()
                    ->id('searchInput')
                    ->placeholder('输入用户名搜索')
                    ->row(1)->col(0)
                    ->expand('horizontal')
                    ->onChange(function($value, $component) {
                        $userTable = StateManager::instance()->getComponent('userTable');
                        $allUsers = StateManager::instance()->get('userList');

                        if (empty($value)) {
                            $filteredUsers = $allUsers;
                        } else {
                            $filteredUsers = array_filter($allUsers, function($user) use ($value) {
                                return stripos($user['name'], $value) !== false;
                            });
                        }

                        echo "搜索完成，找到 " . count($filteredUsers) . " 个结果\n";
                    }),

                Builder::button()
                    ->text('添加用户')
                    ->row(2)->col(0)
                    ->align('fill', 'start')
                    ->onClick(function($button, $stateManager)  {
                        // 创建添加用户对话框
                        $dialogHandle = null;
                        
                        $dialog = Builder::window()
                            ->id('addUserDialog')
                            ->title('添加新用户')
                            ->size(400, 200)
                            ->onClosing(function() {
                                // 允许关闭但不退出应用
                                return 1;
                            })
                            ->contains([
                                Builder::grid()
                                    ->columns(1)
                                    ->padded(true)
                                    ->form([
                                        [
                                            'label' => Builder::label()->text('姓名:'),
                                            'control' => Builder::entry()
                                                ->id('newUserName')
                                                ->placeholder('请输入姓名')
                                        ],
                                        [
                                            'label' => Builder::label()->text('邮箱:'),
                                            'control' => Builder::entry()
                                                ->id('newUserEmail')
                                                ->placeholder('请输入邮箱')
                                        ],
                                    ])
                                    ->append([
                                        Builder::hbox()->contains([
                                            Builder::button()
                                                ->text('保存')
                                                ->onClick(function($btn, $state) use (&$dialogHandle) {
                                                    $name = StateManager::instance()->getComponent('newUserName')?->getValue();
                                                    $email = StateManager::instance()->getComponent('newUserEmail')?->getValue();
                                                    
                                                    if (empty($name) || empty($email)) {
                                                        echo "姓名和邮箱不能为空\n";
                                                        return;
                                                    }
                                                    
                                                    // 添加新用户
                                                    $users = StateManager::instance()->get('userList');
                                                    $newId = empty($users) ? 1 : max(array_column($users, 'id')) + 1;
                                                    $users[] = [
                                                        'id' => $newId,
                                                        'name' => $name,
                                                        'email' => $email
                                                    ];
                                                    
                                                    StateManager::instance()->set('userList', $users);
                                                    
                                                    // 手动更新表格 - 转换为表格数据格式
                                                    $userTable = StateManager::instance()->getComponent('userTable');
                                                    if ($userTable) {
                                                        $tableData = array_map(function($user) {
                                                            return [$user['id'], $user['name'], $user['email']];
                                                        }, $users);
                                                        $userTable->setValue($tableData);
                                                    }
                                                    echo "用户添加成功: {$name} ({$email})\n";
                                                    // 关闭对话框
                                                    Control::destroy($state->getComponent('addUserDialog')->getHandle());
                                                }),
                                            
                                            Builder::button()
                                                ->text('取消')
                                                ->onClick(function($btn, $state) use (&$dialogHandle) {
                                                    Control::destroy($state->getComponent('addUserDialog')->getHandle());
                                                }),
                                        ])
                                    ])
                            ]);
                        
                        // 构建并获取窗口句柄
                        $dialogHandle = $dialog->build();
                        Control::show($dialogHandle);
                    }),

                // 中央列表区 (列1，行0，跨6行)
                Builder::table()
                    ->id('userTable')
                    ->columns(['ID', '姓名', '邮箱'])
                    ->data(array_map(function($user) {
                        return [$user['id'], $user['name'], $user['email']];
                    }, $state->get('userList')))
                    ->row(0)->col(1)->rowspan(6)
                    ->expand('both')
                    ->onRowSelected(function($row, $component) {
                        $users = StateManager::instance()->get('userList');
                        $selectedUser = $users[$row] ?? null;
                        if ($selectedUser) {
                            echo "选中用户: {$selectedUser['name']}\n";

                            StateManager::instance()->getComponent('editNameInput')?->setValue($selectedUser['name']);
                            StateManager::instance()->getComponent('editEmailInput')?->setValue($selectedUser['email']);
                            StateManager::instance()->set('selectedUserId', $selectedUser['id']);
                        }
                    }),

                // 右侧编辑区 (列2，行0-5)
                Builder::label()->text('编辑姓名:')
                    ->row(0)->col(2)
                    ->align('fill', 'center'),
                
                Builder::entry()
                    ->id('editNameInput')
                    ->row(1)->col(2)
                    ->expand('horizontal'),

                Builder::label()->text('编辑邮箱:')
                    ->row(2)->col(2)
                    ->align('fill', 'center'),
                
                Builder::entry()
                    ->id('editEmailInput')
                    ->row(3)->col(2)
                    ->expand('horizontal'),

                Builder::button()
                    ->text('保存修改')
                    ->row(4)->col(2)
                    ->align('fill', 'start')
                    ->onClick(function($button, $stateManager) {
                        $userId = $stateManager->get('selectedUserId');
                        $newName = StateManager::instance()->getComponent('editNameInput')?->getValue();
                        $newEmail = StateManager::instance()->getComponent('editEmailInput')?->getValue();

                        if ($userId && $newName && $newEmail) {
                            $users = $stateManager->get('userList');
                            foreach ($users as &$user) {
                                if ($user['id'] === $userId) {
                                    $user['name'] = $newName;
                                    $user['email'] = $newEmail;
                                    break;
                                }
                            }

                            $stateManager->set('userList', $users);
                            
                            // 更新表格显示
                            $userTable = StateManager::instance()->getComponent('userTable');
                            if ($userTable) {
                                $tableData = array_map(function($user) {
                                    return [$user['id'], $user['name'], $user['email']];
                                }, $users);
                                $userTable->setValue($tableData);
                            }
                            
                            echo "用户信息已更新\n";
                        }
                    }),

                Builder::button()
                    ->text('删除用户')
                    ->row(5)->col(2)
                    ->align('fill', 'start')
                    ->onClick(function($button, $stateManager) {
                        $userId = $stateManager->get('selectedUserId');
                        if ($userId) {
                            $users = $stateManager->get('userList');
                            $users = array_filter($users, fn($user) => $user['id'] !== $userId);
                            $users = array_values($users);
                            $stateManager->set('userList', $users);
                            
                            // 更新表格显示
                            $userTable = StateManager::instance()->getComponent('userTable');
                            if ($userTable) {
                                $tableData = array_map(function($user) {
                                    return [$user['id'], $user['name'], $user['email']];
                                }, $users);
                                $userTable->setValue($tableData);
                            }
                            
                            // 清空编辑框
                            StateManager::instance()->getComponent('editNameInput')?->setValue('');
                            StateManager::instance()->getComponent('editEmailInput')?->setValue('');
                            StateManager::instance()->set('selectedUserId', null);
                            
                            echo "用户已删除\n";
                        }
                    }),
            ]),
        ])->id('mainTabs')
            ->onTabSelected(function($tab) {
                echo "切换到标签页: Tab event received\n";
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