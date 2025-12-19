<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Core\Container\ContainerFactory;
use Kingbes\Libui\View\Core\Config\ConfigManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\App;
use Kingbes\Libui\View\Core\Event\ButtonClickEvent;
use Kingbes\Libui\View\Core\Event\ValueChangeEvent;
use Kingbes\Libui\View\Core\Event\StateChangeEvent;

App::init();
/**
 * 依赖注入集成示例
 * 
 * 展示如何使用：
 * 1. league/config - 配置管理
 * 2. league/event - 事件系统  
 * 3. php-di - 依赖注入容器
 */

echo "=== libui Builder 依赖注入集成示例 ===\n\n";

// 1. 配置管理 (league/config)
echo "1. 配置管理器初始化...\n";
$configManager = new ConfigManager([
    'app' => [
        'title' => 'DI 集成示例',
        'width' => 500,
        'height' => 400,
        'margined' => true,
    ],
    'builder' => [
        'auto_register' => true,
        'enable_logging' => true,
    ],
    'events' => [
        'enabled' => true,
        'namespace' => 'builder.integrated',
    ],
]);

// 从文件加载配置（如果存在）
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    $configManager->loadFromFile($configFile);
    echo "   ✓ 从文件加载配置: $configFile\n";
}

echo "   ✓ 应用标题: " . $configManager->get('app.title') . "\n";
echo "   ✓ 事件命名空间: " . $configManager->get('events.namespace') . "\n\n";

// 2. 事件系统 (league/event)
echo "2. 事件分发器初始化...\n";
$eventDispatcher = new EventDispatcher();

// 注册全局事件监听器
$eventDispatcher->on(ButtonClickEvent::class, function(ButtonClickEvent $event) {
    $componentId = $event->getComponentId() ?? 'unknown';
    echo "   📢 全局事件: 按钮 '$componentId' 被点击 (时间: {$event->getTimestamp()})\n";
});

$eventDispatcher->on(ValueChangeEvent::class, function(ValueChangeEvent $event) {
    $componentId = $event->getComponentId() ?? 'unknown';
    echo "   📢 全局事件: 组件 '$componentId' 值改变 [{$event->getOldValue()} → {$event->getNewValue()}]\n";
});

$eventDispatcher->on(StateChangeEvent::class, function(StateChangeEvent $event) {
    echo "   📢 全局事件: 状态 '{$event->getKey()}' 改变 [{$event->getOldValue()} → {$event->getNewValue()}]\n";
});

echo "   ✓ 事件监听器已注册\n\n";

// 3. 状态管理器
echo "3. 状态管理器初始化...\n";
$stateManager = StateManager::instance();
$stateManager->setEventDispatcher($eventDispatcher);

// 注册状态监听器
$stateManager->watch('counter', function($newValue, $oldValue) {
    echo "   👀 状态监听: counter 从 $oldValue 变为 $newValue\n";
});

echo "   ✓ 状态管理器已连接事件系统\n\n";

// 4. 依赖注入容器 (php-di)
echo "4. 依赖注入容器初始化...\n";
$container = ContainerFactory::create([
    'app' => [
        'title' => $configManager->get('app.title'),
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'path' => __DIR__ . '/../../logs/di_example.log',
    ],
]);

// 从容器获取服务
$containerStateManager = $container->get(StateManager::class);
$containerEventDispatcher = $container->get(EventDispatcher::class);
$containerConfig = $container->get(ConfigManager::class);
$containerBuilder = $container->get(Builder::class);

echo "   ✓ 从容器获取 StateManager\n";
echo "   ✓ 从容器获取 EventDispatcher\n";
echo "   ✓ 从容器获取 ConfigManager\n";
echo "   ✓ 从容器获取 Builder (已自动注入依赖)\n\n";

// 5. 使用 Builder 构建 UI（展示集成效果）
echo "5. 使用集成的 Builder 构建 UI...\n\n";

// 设置全局服务（Builder 会自动使用）
Builder::setStateManager($stateManager);
Builder::setEventDispatcher($eventDispatcher);
Builder::setConfigManager($configManager);

// 构建演示界面
$app = Builder::window()
    ->title($configManager->get('app.title'))
    ->size($configManager->get('app.width'), $configManager->get('app.height'))
    ->margined($configManager->get('app.margined'))
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 标题
                Builder::label()
                    ->text("=== 依赖注入集成演示 ===")
                    ->id('title'),
                
                Builder::separator(),
                
                // 计数器区域（展示状态管理和事件）
                Builder::group()
                    ->title("状态管理 + 事件系统")
                    ->margined(true)
                    ->contains(
                        Builder::vbox()
                            ->padded(true)
                            ->contains([
                                Builder::label()
                                    ->text("计数器: 0")
                                    ->id('counter_label'),
                                
                                Builder::hbox()
                                    ->contains([
                                        Builder::button()
                                            ->text("增加 (+)")
                                            ->id('btn_inc')
                                            ->onClick(function($component, $stateManager, $eventDispatcher) {
                                                $current = $stateManager->get('counter', 0);
                                                $stateManager->set('counter', $current + 1);
                                                echo "   🖱️ 点击: 增加按钮\n";
                                            }),
                                        
                                        Builder::button()
                                            ->text("减少 (-)")
                                            ->id('btn_dec')
                                            ->onClick(function($component, $stateManager) {
                                                $current = $stateManager->get('counter', 0);
                                                $stateManager->set('counter', max(0, $current - 1));
                                                echo "   🖱️ 点击: 减少按钮\n";
                                            }),
                                        
                                        Builder::button()
                                            ->text("重置")
                                            ->id('btn_reset')
                                            ->onClick(function($component, $stateManager) {
                                                $stateManager->set('counter', 0);
                                                echo "   🖱️ 点击: 重置按钮\n";
                                            }),
                                    ]),
                            ])
                    ),
                
                // 输入绑定区域
                Builder::group()
                    ->title("数据绑定 + 配置")
                    ->margined(true)
                    ->contains(
                        Builder::vbox()
                            ->padded(true)
                            ->contains([
                                Builder::label()
                                    ->text("输入框内容将实时同步到状态"),
                                
                                Builder::entry()
                                    ->id('input_demo')
                                    ->placeholder("在此输入文本...")
                                    ->bind('user_input')
                                    ->onChange(function($value, $component, $stateManager) {
                                        echo "   ✏️ 输入改变: '$value'\n";
                                    }),
                                
                                Builder::label()
                                    ->text("当前输入: ")
                                    ->id('input_display'),
                                
                                Builder::separator(),
                                
                                Builder::label()
                                    ->text("配置驱动的滑块"),
                                
                                Builder::slider()
                                    ->range(0, 100)
                                    ->value($configManager->get('app.width', 640) / 10)
                                    ->bind('slider_value')
                                    ->onChange(function($value, $component, $stateManager) {
                                        echo "   🎚️ 滑块改变: $value\n";
                                    }),
                                
                                Builder::label()
                                    ->text("滑块值: ")
                                    ->id('slider_display'),
                            ])
                    ),
                
                Builder::separator(),
                
                // 状态展示区域
                Builder::group()
                    ->title("实时状态监控")
                    ->margined(true)
                    ->contains(
                        Builder::vbox()
                            ->padded(true)
                            ->contains([
                                Builder::label()
                                    ->text("状态管理器中的所有数据:"),
                                
                                Builder::label()
                                    ->text("")
                                    ->id('status_display'),
                            ])
                    ),
                
                Builder::separator(),
                
                // 说明文本
                Builder::label()
                    ->text("💡 请查看控制台输出，观察事件和状态变化"),
            ])
    );

echo "   ✓ UI 构建完成\n\n";

// 6. 连接状态更新逻辑
echo "6. 连接状态更新逻辑...\n";

// 监听状态变化并更新 UI
$stateManager->watch('counter', function($newValue, $oldValue) use ($container, $stateManager) {
    // 获取 UI 组件
    $counterLabel = $stateManager->getComponent('counter_label');
    if ($counterLabel && method_exists($counterLabel, 'setText')) {
        $counterLabel->setText("计数器: $newValue");
    }
});

$stateManager->watch('user_input', function($newValue, $oldValue) use ($stateManager) {
    $inputDisplay = $stateManager->getComponent('input_display');
    if ($inputDisplay && method_exists($inputDisplay, 'setText')) {
        $inputDisplay->setText("当前输入: $newValue");
    }
});

$stateManager->watch('slider_value', function($newValue, $oldValue) use ($stateManager) {
    $sliderDisplay = $stateManager->getComponent('slider_display');
    if ($sliderDisplay && method_exists($sliderDisplay, 'setText')) {
        $sliderDisplay->setText("滑块值: $newValue");
    }
});

// 定时更新状态显示
$stateManager->watch('counter', function() use ($stateManager) {
    $statusDisplay = $stateManager->getComponent('status_display');
    if ($statusDisplay && method_exists($statusDisplay, 'setText')) {
        $allStates = $stateManager->getAll();
        $formatted = json_encode($allStates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $statusDisplay->setText($formatted);
    }
});

// 初始状态显示
$allStates = $stateManager->getAll();
$formatted = json_encode($allStates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$stateManager->getComponent('status_display')?->setValue($formatted);

echo "   ✓ 状态监听器已连接\n\n";

// 7. 总结
echo "=== 集成总结 ===\n";
echo "✓ league/config: 配置管理 (类型安全)\n";
echo "✓ league/event: 事件分发 (解耦)\n";
echo "✓ php-di: 依赖注入 (容器管理)\n";
echo "✓ Builder: 链式调用 + 自动依赖注入\n\n";

echo "演示功能:\n";
echo "- 按钮点击触发事件 (全局 + 局部)\n";
echo "- 输入框双向数据绑定\n";
echo "- 滑块值同步到状态\n";
echo "- 状态变化实时更新 UI\n";
echo "- 所有操作记录到控制台\n\n";

echo "按 Ctrl+C 退出程序\n";
echo "或关闭窗口结束应用\n\n";

// 显示窗口（实际运行时取消注释）
$app->show();
App::main();

echo "⚠️  注意: 这是演示模式，不启动 GUI\n";
echo "要运行完整应用，请取消 example/04_advanced/di_integration_example.php 最后一行的注释\n";
