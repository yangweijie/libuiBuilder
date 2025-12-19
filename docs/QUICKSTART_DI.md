# 快速开始 - 依赖注入集成

## 安装

```bash
composer install
```

## 基础使用

### 1. 最小化示例

```php
<?php

require_once 'vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Core\Container\ContainerFactory;

// 创建容器（自动配置所有服务）
$container = ContainerFactory::create();

// 获取 Builder（已注入所有依赖）
$builder = $container->get(Builder::class);

// 构建 UI
$app = Builder::window()
    ->title('Hello DI')
    ->size(400, 300)
    ->contains(
        Builder::button()
            ->text('Click Me')
            ->onClick(function() {
                echo "Clicked!\n";
            })
    )
    ->show();
```

### 2. 配置管理

```php
use Kingbes\Libui\View\Core\Config\ConfigManager;

// 创建配置
$config = new ConfigManager([
    'app' => [
        'title' => 'My App',
        'width' => 800,
        'height' => 600,
    ],
]);

// 或从文件加载
$config->loadFromFile('config.php');

// 使用配置
$app = Builder::window()
    ->title($config->get('app.title'))
    ->size($config->get('app.width'), $config->get('app.height'));
```

### 3. 事件系统

```php
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Event\ButtonClickEvent;

$events = new EventDispatcher();

// 注册全局监听器
$events->on(ButtonClickEvent::class, function($event) {
    echo "按钮 {$event->getComponentId()} 被点击\n";
});

// 在 Builder 中使用
Builder::setEventDispatcher($events);

$button = Builder::button()
    ->id('my_button')
    ->onClick(function($component, $state) {
        // 这个回调也会触发全局事件
    });
```

### 4. 状态管理

```php
use Kingbes\Libui\View\State\StateManager;

$state = StateManager::instance();

// 设置状态
$state->set('username', 'John');

// 监听变化
$state->watch('username', function($new, $old) {
    echo "用户名从 $old 变为 $new\n";
});

// 绑定组件
$entry = Builder::entry()
    ->bind('username')
    ->onChange(function($value, $component, $state) {
        // 值自动同步到状态
    });
```

## 完整示例

### 计数器应用

```php
<?php

require_once 'vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Core\Container\ContainerFactory;

// 1. 创建容器
$container = ContainerFactory::create([
    'app' => ['title' => '计数器'],
]);

// 2. 获取服务
$builder = $container->get(Builder::class);
$state = $container->get(StateManager::class);
$events = $container->get(EventDispatcher::class);

// 3. 配置事件
$events->on(ButtonClickEvent::class, function($event) {
    echo "操作: {$event->getComponentId()}\n";
});

// 4. 构建 UI
$counterLabel = Builder::label()
    ->id('counter')
    ->text('计数: 0');

$app = Builder::window()
    ->title('计数器示例')
    ->size(300, 200)
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                $counterLabel,
                Builder::hbox()
                    ->contains([
                        Builder::button()
                            ->text('+1')
                            ->id('inc')
                            ->onClick(function() use ($state) {
                                $current = $state->get('counter', 0);
                                $state->set('counter', $current + 1);
                            }),
                        Builder::button()
                            ->text('-1')
                            ->id('dec')
                            ->onClick(function() use ($state) {
                                $current = $state->get('counter', 0);
                                $state->set('counter', max(0, $current - 1);
                            }),
                    ])
            ])
    );

// 5. 连接状态更新
$state->watch('counter', function($new) use ($counterLabel) {
    $counterLabel->setText("计数: $new");
});

// 6. 显示
$app->show();
```

### 登录表单

```php
<?php

require_once 'vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Core\Container\ContainerFactory;

$container = ContainerFactory::create();
$builder = $container->get(Builder::class);
$state = $container->get(StateManager::class);

$app = Builder::window()
    ->title('登录')
    ->size(400, 300)
    ->contains(
        Builder::group()
            ->title('用户登录')
            ->margined(true)
            ->contains(
                Builder::vbox()
                    ->padded(true)
                    ->contains([
                        Builder::label()->text('用户名:'),
                        Builder::entry()
                            ->id('username')
                            ->bind('username')
                            ->placeholder('输入用户名'),
                        
                        Builder::label()->text('密码:'),
                        Builder::entry()
                            ->id('password')
                            ->bind('password')
                            ->password()
                            ->placeholder('输入密码'),
                        
                        Builder::separator(),
                        
                        Builder::button()
                            ->text('登录')
                            ->onClick(function($component, $state) {
                                $username = $state->get('username');
                                $password = $state->get('password');
                                
                                if ($username && $password) {
                                    echo "登录尝试: $username\n";
                                    // 验证逻辑...
                                }
                            })
                    ])
            )
    )
    ->show();
```

## 高级配置

### 自定义配置文件

```php
// config.php
return [
    'app' => [
        'title' => '我的应用',
        'width' => 1000,
        'height' => 700,
        'margined' => true,
    ],
    'builder' => [
        'auto_register' => true,
        'enable_logging' => true,
    ],
    'events' => [
        'enabled' => true,
        'namespace' => 'myapp',
    ],
    'logging' => [
        'level' => 'info',
        'path' => 'logs/app.log',
    ],
    'dependencies' => [
        // 自定义服务
        'MyService' => function($c) {
            return new MyService(
                $c->get(ConfigManager::class)
            );
        },
    ],
];

// 使用配置
$container = ContainerFactory::create(
    require 'config.php'
);
```

### 事件监听器

```php
use Kingbes\Libui\View\Core\Event\ValueChangeEvent;
use Kingbes\Libui\View\Core\Event\StateChangeEvent;

$events = $container->get(EventDispatcher::class);

// 监听所有值改变
$events->on(ValueChangeEvent::class, function($event) {
    $id = $event->getComponentId();
    $old = $event->getOldValue();
    $new = $event->getNewValue();
    echo "[$id] $old → $new\n";
});

// 监听状态改变
$events->on(StateChangeEvent::class, function($event) {
    $key = $event->getKey();
    $old = $event->getOldValue();
    $new = $event->getNewValue();
    echo "状态 '$key': $old → $new\n";
});
```

### 状态持久化

```php
use Kingbes\Libui\View\State\StateManager;

$state = StateManager::instance();

// 保存状态
function saveState($filename) {
    global $state;
    file_put_contents($filename, json_encode($state->getAll()));
}

// 加载状态
function loadState($filename) {
    global $state;
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
        $state->update($data);
    }
}

// 使用
$state->set('theme', 'dark');
saveState('state.json');

// 下次启动时
loadState('state.json');
```

## 调试技巧

### 1. 启用日志

```php
$container = ContainerFactory::create([
    'logging' => [
        'enabled' => true,
        'level' => 'debug',
        'path' => 'logs/debug.log',
    ],
]);

// 然后在代码中
$logger = $container->get('Logger');
$logger->info('应用启动');
```

### 2. 监听所有事件

```php
$events = $container->get(EventDispatcher::class);

// 调试模式：记录所有事件
$events->on('*', function($event) {
    $type = get_class($event);
    echo "事件: $type\n";
    if (method_exists($event, 'toArray')) {
        print_r($event->toArray());
    }
});
```

### 3. 检查状态

```php
$state = $container->get(StateManager::class);

// 打印所有状态
 echo "当前状态:\n";
print_r($state->getAll());

// 检查组件注册
$component = $state->getComponent('my_button');
 echo "组件: " . ($component ? $component->getType() : '未找到') . "\n";
```

## 故障排除

### 问题: 类找不到

```bash
# 重新生成自动加载
composer dump-autoload
```

### 问题: 依赖注入失败

```php
// 检查容器是否正确配置
$container = ContainerFactory::create();
var_dump($container->get(ConfigManager::class) !== null);
```

### 问题: 事件不触发

```php
// 确保事件分发器已设置
Builder::setEventDispatcher($events);

// 检查事件名称
$events->on(ButtonClickEvent::class, $handler);
// 不是 'button.click' 或其他字符串
```

## 性能优化

### 1. 编译容器（生产环境）

```php
$container = ContainerFactory::create($config, true);
```

### 2. 事件过滤

```php
// 只监听特定组件
$events->on(ButtonClickEvent::class, function($event) {
    if ($event->getComponentId() === 'critical_button') {
        // 处理重要按钮
    }
});
```

### 3. 批量状态更新

```php
// 一次触发多次更新
$state->update([
    'name' => 'John',
    'age' => 30,
    'email' => 'john@example.com',
]);
```

## 下一步

- 查看 [架构设计](ARCHITECTURE.md) 了解完整设计
- 查看 [示例代码](../example/04_advanced/di_integration_example.php) 运行完整演示
- 查看 [API 参考](API.md) 了解详细 API