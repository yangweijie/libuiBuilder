<?php
/**
 * 基础按钮示例 - Builder API 模式
 * 
 * 演示内容：
 * - Button 组件的基本使用
 * - 点击事件处理
 * - 按钮状态管理
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('clickCount', 0);
$state->set('buttonValue', '点击次数: 0');

$app = Builder::window()
    ->title('基础按钮示例 - Builder API')
    ->size(400, 300)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 标题
                Builder::label()
                    ->text('按钮组件演示')
                    ->align('center'),
                
                Builder::separator(),
                
                // 基础按钮
                Builder::label()->text('基础按钮:'),
                Builder::button()
                    ->text('点击我')
                    ->onClick(function($button) {
                        echo "按钮被点击了！\n";
                    }),
                
                // 计数器按钮
                Builder::label()->text('计数器按钮:'),
                Builder::button()
                    ->id('counterBtn')
                    ->bind('buttonValue')
                    ->onClick(function($button, $state) {
                        $count = $state->get('clickCount') + 1;
                        $state->set('clickCount', $count);
                        
                        echo "计数器: {$count}\n";
                        
                        // 更新按钮文本
                        $counterBtn = StateManager::instance()->getComponent('counterBtn');
                        if ($counterBtn) {
                            $counterBtn->setValue("点击次数: {$count}");
                        }
                    }),
                
                // 重置按钮
                Builder::label()->text('控制按钮:'),
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::button()
                            ->text('重置计数器')
                            ->onClick(function($button, $state) {
                                $state->set('clickCount', 0);
                                echo "计数器已重置\n";
                                
                                // 更新计数器按钮
                                $counterBtn = StateManager::instance()->getComponent('counterBtn');
                                if ($counterBtn) {
                                    $counterBtn->setValue('点击次数: 0');
                                }
                            }),
                        
                        Builder::button()
                            ->text('退出')
                            ->onClick(function($button) {
                                App::quit();
                            }),
                    ]),
            ])
    ]);

$app->show();