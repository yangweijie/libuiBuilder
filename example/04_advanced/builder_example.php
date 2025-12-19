<?php
/**
 * 链式构建器模式示例
 * 
 * 使用新的 Builder 类创建 GUI 应用
 */

use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;

require_once __DIR__ . '/../../vendor/autoload.php';

// 初始化 libui
App::init();

// 初始化状态管理器
$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');
$state->set('counter', 0);
$state->set('progress', 0);

// 设置全局状态管理器（可选，Builder 会自动使用）
Builder::setStateManager($state);

// 创建窗口
$app = Builder::window()
    ->title('Builder 示例 - 链式调用')
    ->size(600, 400)
    ->margined(true)
    ->onClosing(function() {
        App::quit();
        return 0;
    })
    ->contains(
        Builder::tab()
            ->tabs([
                // 标签页 1: 基础组件
                '基础组件' => Builder::vbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()
                            ->id('welcomeLabel')
                            ->text('欢迎使用 Builder 模式！')
                            ->align('center'),
                        
                        Builder::separator(),
                        
                        // 用户名输入
                        Builder::grid()
                            ->columns(2)
                            ->padded(true)
                            ->form([
                                [
                                    'label' => Builder::label()->text('用户名:'),
                                    'control' => Builder::entry()
                                        ->id('usernameInput')
                                        ->bind('username')
                                        ->placeholder('请输入用户名')
                                        ->onChange(function($value, $component, $stateManager) {
                                            echo "用户名输入: {$value}\n";
                                            $stateManager->set('username', $value);
                                        })
                                ],
                                [
                                    'label' => Builder::label()->text('密码:'),
                                    'control' => Builder::entry()
                                        ->id('passwordInput')
                                        ->bind('password')
                                        ->placeholder('请输入密码')
                                        ->password()
                                        ->onChange(function($value, $component, $stateManager) {
                                            echo "密码输入: " . str_repeat('*', strlen($value)) . "\n";
                                        })
                                ],
                            ]),
                        
                        // 按钮组
                        Builder::hbox()
                            ->padded(true)
                            ->contains([
                                Builder::button()
                                    ->id('loginBtn')
                                    ->text('登录')
                                    ->onClick(function($button, $stateManager) {
                                        $username = $stateManager->get('username');
                                        $password = $stateManager->get('password');
                                        
                                        if (empty($username) || empty($password)) {
                                            echo "用户名和密码不能为空！\n";
                                            return;
                                        }
                                        
                                        echo "登录成功！用户名: {$username}\n";
                                        
                                        // 更新欢迎标签
                                        $label = $stateManager->getComponent('welcomeLabel');
                                        if ($label) {
                                            $label->setText("欢迎, {$username}！");
                                        }
                                    }),
                                
                                Builder::button()
                                    ->text('清空')
                                    ->onClick(function($button, $stateManager) {
                                        $stateManager->update([
                                            'username' => '',
                                            'password' => ''
                                        ]);
                                        
                                        // 手动清空输入框
                                        $stateManager->getComponent('usernameInput')?->setValue('');
                                        $stateManager->getComponent('passwordInput')?->setValue('');
                                        
                                        echo "已清空表单\n";
                                    }),
                            ]),
                    ]),
                
                // 标签页 2: 计数器和进度条
                '计数器和进度' => Builder::vbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()
                            ->text('计数器演示')
                            ->align('center'),
                        
                        Builder::label()
                            ->id('counterLabel')
                            ->text('当前计数: 0')
                            ->align('center'),
                        
                        Builder::hbox()
                            ->padded(true)
                            ->contains([
                                Builder::button()
                                    ->text('增加 +1')
                                    ->onClick(function($button, $stateManager) {
                                        $counter = $stateManager->get('counter', 0) + 1;
                                        $stateManager->set('counter', $counter);
                                        
                                        $label = $stateManager->getComponent('counterLabel');
                                        if ($label) {
                                            $label->setText("当前计数: {$counter}");
                                        }
                                        echo "计数器: {$counter}\n";
                                    }),
                                
                                Builder::button()
                                    ->text('重置')
                                    ->onClick(function($button, $stateManager) {
                                        $stateManager->set('counter', 0);
                                        $label = $stateManager->getComponent('counterLabel');
                                        if ($label) {
                                            $label->setText("当前计数: 0");
                                        }
                                        echo "计数器已重置\n";
                                    }),
                            ]),
                        
                        Builder::separator(),
                        
                        Builder::label()
                            ->text('滑块控制进度条')
                            ->align('center'),
                        
                        Builder::slider()
                            ->range(0, 100)
                            ->value(0)
                            ->bind('progress')
                            ->onChange(function($value, $component, $stateManager) {
                                $progressBar = $stateManager->getComponent('progressBar');
                                if ($progressBar) {
                                    $progressBar->setValue($value);
                                }
                                echo "滑块值: {$value}\n";
                            }),
                        
                        Builder::progress()
                            ->id('progressBar')
                            ->value(0),
                        
                        Builder::spinbox()
                            ->range(0, 100)
                            ->value(0)
                            ->bind('progress')
                            ->onChange(function($value, $component, $stateManager) {
                                $progressBar = $stateManager->getComponent('progressBar');
                                if ($progressBar) {
                                    $progressBar->setValue($value);
                                }
                                echo "数字输入: {$value}\n";
                            }),
                    ]),
                
                // 标签页 3: 选择控件
                '选择控件' => Builder::vbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()
                            ->text('复选框演示')
                            ->align('center'),
                        
                        Builder::checkbox()
                            ->text('启用高级功能')
                            ->bind('advanced')
                            ->onChange(function($checked, $component, $stateManager) {
                                echo "高级功能: " . ($checked ? '启用' : '禁用') . "\n";
                            }),
                        
                        Builder::separator(),
                        
                        Builder::label()
                            ->text('下拉选择演示')
                            ->align('center'),
                        
                        Builder::combobox()
                            ->id('colorSelect')
                            ->items(['红色', '绿色', '蓝色', '黄色'])
                            ->selected(0)
                            ->bind('color')
                            ->onChange(function($index, $value, $component, $stateManager) {
                                echo "选中颜色: {$value} (索引: {$index})\n";
                            }),
                        
                        Builder::separator(),
                        
                        Builder::label()
                            ->text('状态显示')
                            ->align('center'),
                        
                        Builder::group()
                            ->title('当前状态')
                            ->margined(true)
                            ->contains(
                                Builder::vbox()
                                    ->padded(true)
                                    ->contains([
                                        Builder::label()
                                            ->id('statusUsername')
                                            ->text('用户名: 未设置')
                                            ->align('start'),
                                        
                                        Builder::label()
                                            ->id('statusProgress')
                                            ->text('进度: 0%')
                                            ->align('start'),
                                        
                                        Builder::label()
                                            ->id('statusColor')
                                            ->text('颜色: 红色')
                                            ->align('start'),
                                    ])
                            ),
                    ]),
            ])
            ->onTabSelected(function($index, $tab, $stateManager) {
                echo "切换到标签页 {$index}\n";
                
                // 当切换到标签页 3 时，更新状态显示
                if ($index === 2) {
                    $stateManager->watch('username', function($newValue) use ($stateManager) {
                        $label = $stateManager->getComponent('statusUsername');
                        if ($label) {
                            $label->setText('用户名: ' . ($newValue ?: '未设置'));
                        }
                    });
                    
                    $stateManager->watch('progress', function($newValue) use ($stateManager) {
                        $label = $stateManager->getComponent('statusProgress');
                        if ($label) {
                            $label->setText("进度: {$newValue}%");
                        }
                    });
                    
                    $stateManager->watch('color', function($newValue) use ($stateManager) {
                        $label = $stateManager->getComponent('statusColor');
                        if ($label) {
                            $label->setText("颜色: {$newValue}");
                        }
                    });
                }
            })
    );

// 监听状态变化（示例）
$state->watch('counter', function($newValue, $oldValue) {
    echo "计数器变化: {$oldValue} → {$newValue}\n";
});

// 显示窗口
echo "应用启动成功！\n";
echo "请在控制台查看事件日志。\n";

$app->show();
App::main();
