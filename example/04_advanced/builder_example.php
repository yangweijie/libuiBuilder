<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Config\ConfigManager;

// 创建依赖注入容器
$stateManager = StateManager::instance();
$eventDispatcher = new EventDispatcher();
$configManager = new ConfigManager([
    'app' => [
        'title' => '高级 Builder 示例',
        'version' => '1.0.0'
    ]
]);

// 设置全局依赖
Builder::setStateManager($stateManager);
Builder::setEventDispatcher($eventDispatcher);
Builder::setConfigManager($configManager);

// 创建主窗口
$app = Builder::window()
    ->title('高级 Builder 示例 - 统一API')
    ->size(800, 600)
    ->margined(true)
    ->onClosing(function() {
        echo "应用关闭\n";
        return false; // 允许关闭
    })
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 欢迎标签
                Builder::label()
                    ->id('welcomeLabel')
                    ->text('欢迎使用 libuiBuilder 高级示例！')
                    ->align('center'),
                
                Builder::separator(),
                
                // 表单部分
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()->text('用户名:'),
                        Builder::entry()
                            ->id('usernameInput')
                            ->placeholder('请输入用户名')
                            ->bind('username')
                            ->onChange(function($value, $component, $stateManager) {
                                echo "用户名输入: $value\n";
                            }),
                    ]),
                
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()->text('密码:'),
                        Builder::entry()
                            ->id('passwordInput')
                            ->placeholder('请输入密码')
                            ->password()
                            ->bind('password')
                            ->onChange(function($value, $component, $stateManager) {
                                echo "密码输入: " . str_repeat('*', strlen($value)) . "\n";
                            }),
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
                                
                                echo "登录尝试: 用户名=$username, 密码=" . str_repeat('*', strlen($password)) . "\n";
                                
                                // 更新欢迎标签
                                $stateManager->getComponent('welcomeLabel')?->setText("欢迎, $username!");
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
                
                Builder::separator(),
                
                // 进度条和滑块
                Builder::label()->text('进度控制:'),
                
                Builder::slider()
                    ->id('progressSlider')
                    ->range(0, 100)
                    ->value(50)
                    ->bind('progress')
                    ->onChange(function($value, $component, $stateManager) {
                        $stateManager->getComponent('progressBar')?->setValue((int)$value);
                        echo "进度更新: $value%\n";
                    }),
                
                Builder::progress()
                    ->id('progressBar')
                    ->value(50)
                    ->bind('progress'),
                
                Builder::separator(),
                
                // 选择框
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::checkbox()
                            ->id('agreeCheckbox')
                            ->text('同意条款')
                            ->checked(false)
                            ->bind('agreed')
                            ->onChange(function($checked, $component, $stateManager) {
                                echo "条款状态: " . ($checked ? '已同意' : '未同意') . "\n";
                            }),
                        
                        Builder::combobox()
                            ->id('languageCombo')
                            ->items(['中文', 'English', '日本語'])
                            ->selected(0)
                            ->bind('language')
                            ->onChange(function($index, $value, $component, $stateManager) {
                                echo "语言选择: $value\n";
                            }),
                    ]),
            ])
    );

// 显示应用
$app->show();

// 运行主循环
\Kingbes\Libui\App::main();

echo "应用已退出\n";