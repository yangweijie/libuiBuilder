<?php
/**
 * 进度条示例 - Builder API 模式
 * 
 * 演示内容：
 * - ProgressBar 组件使用
 * - 进度更新和动画效果
 * - 定时器模拟长时间任务
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('progress', 0);
$state->set('isRunning', false);
$state->set('taskName', '');

// 模拟长时间任务
function simulateLongTask($state, $duration = 5000) {
    if ($state->get('isRunning')) {
        return; // 任务已在运行
    }
    
    $state->set('isRunning', true);
    $state->set('progress', 0);
    
    echo "开始模拟任务，持续时间: {$duration}ms\n";
    
    // 模拟进度更新
    $steps = 20;
    $stepDuration = $duration / $steps;
    
    for ($i = 1; $i <= $steps; $i++) {
        usleep($stepDuration * 1000); // 转换为微秒
        
        $progress = ($i / $steps) * 100;
        $state->set('progress', $progress);
        
        echo "进度: " . round($progress, 1) . "%\n";
        
        // 更新UI
        $progressBar = StateManager::instance()->getComponent('progressBar');
        $progressLabel = StateManager::instance()->getComponent('progressLabel');
        $statusLabel = StateManager::instance()->getComponent('statusLabel');
        
        if ($progressBar) {
            $progressBar->setValue($progress);
        }
        
        if ($progressLabel) {
            $progressLabel->setValue("进度: " . round($progress, 1) . "%");
        }
        
        if ($statusLabel && $i === $steps) {
            $statusLabel->setValue("任务完成！");
        }
    }
    
    $state->set('isRunning', false);
    echo "任务完成\n";
}

$app = Builder::window()
    ->title('进度条演示 - Builder API')
    ->size(500, 400)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 标题
                Builder::label()
                    ->text('进度条组件演示')
                    ->align('center')
                    ->id('titleLabel'),
                
                Builder::separator(),
                
                // 进度显示区域
                Builder::vbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()
                            ->text('当前任务: 无')
                            ->id('taskLabel'),
                        
                        Builder::progressBar()
                            ->id('progressBar')
                            ->value(0),
                        
                        Builder::label()
                            ->text('进度: 0%')
                            ->id('progressLabel'),
                        
                        Builder::label()
                            ->text('就绪')
                            ->id('statusLabel'),
                    ]),
                
                Builder::separator(),
                
                // 任务选择
                Builder::label()->text('选择任务:'),
                Builder::combobox()
                    ->id('taskSelector')
                    ->items(['文件下载', '数据处理', '图像渲染', '计算任务', '网络请求'])
                    ->selected(0)
                    ->onSelected(function($index, $item, $component) use ($state) {
                        $state->set('taskName', $item);
                        echo "选择了任务: {$item}\n";
                    }),
                
                Builder::separator(),
                
                // 控制按钮
                Builder::grid()->form([
                    [
                        'label' => Builder::label()->text('任务时长:'),
                        'control' => Builder::spinbox()
                            ->id('durationInput')
                            ->range(1000, 30000)
                            ->value(5000)
                            ->onChange(function($value, $component) use ($state) {
                                echo "设置任务时长: {$value}ms\n";
                            })
                    ],
                    [
                        'label' => Builder::label()->text('操作:'),
                        'control' => Builder::hbox()
                            ->padded(true)
                            ->contains([
                                Builder::button()
                                    ->text('开始任务')
                                    ->id('startBtn')
                                    ->onClick(function($button, $state) {
                                        if ($state->get('isRunning')) {
                                            echo "任务正在运行中\n";
                                            return;
                                        }
                                        
                                        $taskName = $state->get('taskName');
                                        $duration = StateManager::instance()->getComponent('durationInput')?->getValue() ?? 5000;
                                        
                                        // 更新UI状态
                                        $taskLabel = StateManager::instance()->getComponent('taskLabel');
                                        $statusLabel = StateManager::instance()->getComponent('statusLabel');
                                        
                                        if ($taskLabel) {
                                            $taskLabel->setValue("当前任务: {$taskName}");
                                        }
                                        
                                        if ($statusLabel) {
                                            $statusLabel->setValue("任务进行中...");
                                        }
                                        
                                        // 开始任务（这里使用同步方式，实际应用中可能需要异步）
                                        simulateLongTask($state, $duration);
                                    }),
                                
                                Builder::button()
                                    ->text('重置')
                                    ->onClick(function($button, $state) {
                                        $state->set('progress', 0);
                                        $state->set('isRunning', false);
                                        $state->set('taskName', '');
                                        
                                        // 重置UI
                                        $progressBar = StateManager::instance()->getComponent('progressBar');
                                        $progressLabel = StateManager::instance()->getComponent('progressLabel');
                                        $taskLabel = StateManager::instance()->getComponent('taskLabel');
                                        $statusLabel = StateManager::instance()->getComponent('statusLabel');
                                        
                                        if ($progressBar) {
                                            $progressBar->setValue(0);
                                        }
                                        
                                        if ($progressLabel) {
                                            $progressLabel->setValue('进度: 0%');
                                        }
                                        
                                        if ($taskLabel) {
                                            $taskLabel->setValue('当前任务: 无');
                                        }
                                        
                                        if ($statusLabel) {
                                            $statusLabel->setValue('就绪');
                                        }
                                        
                                        echo "已重置\n";
                                    }),
                                
                                Builder::button()
                                    ->text('退出')
                                    ->onClick(function($button) {
                                        App::quit();
                                    }),
                            ])
                    ],
                ]),
                
                // 批量任务
                Builder::separator(),
                Builder::label()->text('批量任务:'),
                Builder::button()
                    ->text('连续执行3个任务')
                    ->onClick(function($button, $state) {
                        if ($state->get('isRunning')) {
                            echo "请等待当前任务完成\n";
                            return;
                        }
                        
                        echo "开始批量任务\n";
                        
                        $tasks = ['任务A', '任务B', '任务C'];
                        $taskLabel = StateManager::instance()->getComponent('taskLabel');
                        $statusLabel = StateManager::instance()->getComponent('statusLabel');
                        
                        foreach ($tasks as $index => $taskName) {
                            $state->set('taskName', $taskName);
                            
                            if ($taskLabel) {
                                $taskLabel->setValue("当前任务: {$taskName} (" . ($index + 1) . "/3)");
                            }
                            
                            if ($statusLabel) {
                                $statusLabel->setValue("执行第" . ($index + 1) . "个任务...");
                            }
                            
                            // 模拟短任务
                            simulateLongTask($state, 2000);
                            
                            echo "完成: {$taskName}\n";
                        }
                        
                        if ($statusLabel) {
                            $statusLabel->setValue("所有任务完成！");
                        }
                        
                        echo "批量任务完成\n";
                    }),
            ])
    ]);

$app->show();