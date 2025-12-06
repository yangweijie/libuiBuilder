<?php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

require_once __DIR__ . '/vendor/autoload.php';

App::init();

$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');

$app = Builder::window()
    ->title('GroupBuilder 测试')
    ->size(400, 300)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 用户信息分组
                Builder::group()
                    ->title('用户信息')
                    ->margined(true)
                    ->contains([
                        Builder::grid()
                            ->padded(true)
                            ->contains([
                                Builder::label()->text('用户名:'),
                                Builder::entry()
                                    ->placeholder('请输入用户名')
                                    ->bind('username'),
                                Builder::label()->text('密码:'),
                                Builder::entry()
                                    ->placeholder('请输入密码')
                                    ->bind('password')
                                    ->setConfig('password', true)
                            ])
                    ]),
                
                // 操作按钮分组
                Builder::group()
                    ->title('操作')
                    ->margined(true)
                    ->contains([
                        Builder::hbox()
                            ->padded(true)
                            ->contains([
                                Builder::button()
                                    ->text('登录')
                                    ->onClick(function($button, $state) {
                                        echo "登录: " . $state->get('username') . "\n";
                                    }),
                                Builder::button()
                                    ->text('重置')
                                    ->onClick(function($button, $state) {
                                        $state->update([
                                            'username' => '',
                                            'password' => ''
                                        ]);
                                    })
                            ])
                    ])
            ])
    ]);

$app->show();