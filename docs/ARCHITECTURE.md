# libuiBuilder 架构设计文档

## 概述

libuiBuilder 是一个现代化的 GUI 开发框架，基于 kingbes/libui 库，采用链式构建器模式，并深度集成了三个核心依赖包：

- **league/event** - 事件系统
- **league/config** - 配置管理  
- **php-di/php-di** - 依赖注入容器

## 架构图

```
┌─────────────────────────────────────────────────────────────┐
│                    应用层 (Application)                     │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Builder::window() → Builder::vbox() → ...            │  │
│  │  链式调用 + 配置驱动                                   │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    工厂层 (Factory)                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Builder (静态工厂)                                    │  │
│  │  - setStateManager()                                  │  │
│  │  - setEventDispatcher()                               │  │
│  │  - setConfigManager()                                 │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    构建层 (Builder)                         │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  ComponentBuilder (抽象基类)                           │  │
│  │  - StateManager (依赖注入)                             │  │
│  │  - EventDispatcher (依赖注入)                          │  │
│  │  - ConfigManager (依赖注入)                            │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  ButtonBuilder, EntryBuilder, BoxBuilder 等            │  │
│  │  - id(), onClick(), onChange(), bind()                │  │
│  │  - build() 生成 kingbes/libui 控件                    │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    核心服务层 (Core)                        │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  ConfigManager (league/config)                        │  │
│  │  - 类型安全配置                                       │  │
│  │  - 支持 PHP/JSON/YAML                                 │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  EventDispatcher (league/event)                       │  │
│  │  - 事件监听 & 分发                                    │  │
│  │  - 解耦业务逻辑                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  ContainerFactory (php-di)                            │  │
│  │  - 服务生命周期管理                                   │  │
│  │  - 自动依赖解析                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  StateManager (自定义)                                │  │
│  │  - 全局状态管理                                       │  │
│  │  - 双向数据绑定                                       │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                  基础库层 (kingbes/libui)                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  App, Window, Button, Entry, Box 等                   │  │
│  │  PHP FFI 绑定 libui 原生库                            │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## 核心组件详解

### 1. ConfigManager (league/config)

**作用**: 类型安全的配置管理

```php
use Kingbes\Libui\View\Core\Config\ConfigManager;

$config = new ConfigManager([
    'app' => [
        'title' => 'My App',
        'width' => 800,
        'height' => 600,
    ],
]);

// 获取配置
$title = $config->get('app.title');

// 从文件加载
$config->loadFromFile('config.php');
```

**特性**:
- ✅ 类型验证 (Nette Schema)
- ✅ 多格式支持 (PHP/JSON/YAML)
- ✅ 配置合并
- ✅ 运行时修改

### 2. EventDispatcher (league/event)

**作用**: 事件驱动的异步通信

```php
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Event\ButtonClickEvent;

$dispatcher = new EventDispatcher();

// 注册监听器
$dispatcher->on(ButtonClickEvent::class, function($event) {
    echo "按钮 {$event->getComponentId()} 被点击\n";
});

// 触发事件
$dispatcher->dispatch(new ButtonClickEvent($button, $stateManager));
```

**内置事件**:
- `ButtonClickEvent` - 按钮点击
- `ValueChangeEvent` - 值改变
- `StateChangeEvent` - 状态改变

### 3. ContainerFactory (php-di)

**作用**: 依赖注入容器管理

```php
use Kingbes\Libui\View\Core\Container\ContainerFactory;

// 创建容器
$container = ContainerFactory::create([
    'app' => ['title' => 'My App'],
]);

// 获取服务
$config = $container->get(ConfigManager::class);
$events = $container->get(EventDispatcher::class);
$builder = $container->get(Builder::class);
```

**自动注入的服务**:
- ConfigManager
- EventDispatcher
- StateManager
- Builder (自动注入前三个)

### 4. StateManager (自定义)

**作用**: 全局状态管理和数据绑定

```php
use Kingbes\Libui\View\State\StateManager;

$state = StateManager::instance();
$state->setEventDispatcher($dispatcher);

// 设置状态
$state->set('username', 'John');

// 监听变化
$state->watch('username', function($new, $old) {
    echo "用户名从 $old 变为 $new\n";
});

// 绑定组件
$entry = Builder::entry()->bind('username');
```

**特性**:
- ✅ 单例模式
- ✅ 双向数据绑定
- ✅ 事件通知
- ✅ 组件自动更新

### 5. Builder (链式构建器)

**作用**: 声明式 UI 构建

```php
use Kingbes\Libui\View\Builder\Builder;

// 依赖注入自动完成
$app = Builder::window()
    ->title('Hello')
    ->size(600, 400)
    ->contains(
        Builder::vbox()
            ->contains([
                Builder::label()->text('Welcome'),
                Builder::button()
                    ->text('Click me')
                    ->onClick(function($component, $state, $events) {
                        // 处理逻辑
                    })
            ])
    )
    ->show();
```

## 依赖注入流程

### 1. 容器配置

```php
$container = ContainerFactory::create([
    'app' => ['title' => 'My App'],
    'logging' => ['enabled' => true],
]);
```

### 2. 服务解析

```php
// 自动创建并注入依赖
$builder = $container->get(Builder::class);
// 等同于:
// $builder = new Builder();
// $builder->setStateManager($container->get(StateManager::class));
// $builder->setEventDispatcher($container->get(EventDispatcher::class));
// $builder->setConfigManager($container->get(ConfigManager::class));
```

### 3. 组件构建

```php
// Builder 工厂方法自动应用依赖
$button = Builder::button();
// button 实例已包含所有依赖服务
```

## 事件流

### 1. 用户交互

```
用户点击按钮
    ↓
ButtonBuilder 触发 onClick
    ↓
EventDispatcher 分发 ButtonClickEvent
    ↓
全局监听器处理
    ↓
StateManager 更新状态
    ↓
StateChangeEvent 触发
    ↓
绑定组件自动更新
```

### 2. 状态更新

```
状态改变 (StateManager::set())
    ↓
触发 StateChangeEvent
    ↓
EventDispatcher 分发事件
    ↓
更新所有绑定组件
    ↓
UI 自动刷新
```

## 配置管理流程

### 1. 定义配置

```php
// config.php
return [
    'app' => [
        'title' => 'My App',
        'width' => 800,
    ],
    'builder' => [
        'auto_register' => true,
    ],
];
```

### 2. 加载配置

```php
$config = new ConfigManager();
$config->loadFromFile('config.php');
```

### 3. 使用配置

```php
$app = Builder::window()
    ->title($config->get('app.title'))
    ->size($config->get('app.width'), $config->get('app.height'));
```

## 最佳实践

### 1. 依赖注入优先

```php
// ✅ 推荐：使用容器
$container = ContainerFactory::create();
$builder = $container->get(Builder::class);

// ❌ 避免：手动创建
$builder = new Builder(); // 缺少依赖
```

### 2. 事件驱动

```php
// ✅ 推荐：事件解耦
$dispatcher->on(ValueChangeEvent::class, $handler);

// ❌ 避免：硬编码
$entry->onChange(function($v) {
    // 业务逻辑耦合在 UI 层
});
```

### 3. 配置化

```php
// ✅ 推荐：配置驱动
$width = $config->get('app.width');

// ❌ 避免：硬编码
$width = 800;
```

### 4. 状态管理

```php
// ✅ 推荐：状态集中管理
$state->set('user', $userData);
$builder->bind('user');

// ❌ 避免：分散存储
$builder->setConfig('user', $userData);
```

## 目录结构

```
src/
├── Builder/              # 构建器
│   ├── ComponentBuilder.php
│   ├── Builder.php
│   ├── ButtonBuilder.php
│   └── ...
├── State/               # 状态管理
│   └── StateManager.php
├── Core/                # 核心服务
│   ├── Config/
│   │   └── ConfigManager.php
│   ├── Event/
│   │   ├── EventDispatcher.php
│   │   ├── ButtonClickEvent.php
│   │   ├── ValueChangeEvent.php
│   │   └── StateChangeEvent.php
│   └── Container/
│       └── ContainerFactory.php
└── helper.php
```

## 扩展性

### 1. 自定义事件

```php
class UserLoginEvent {
    public function __construct(
        public string $username,
        public \DateTime $time
    ) {}
}

$dispatcher->on(UserLoginEvent::class, $handler);
```

### 2. 自定义服务

```php
$container = ContainerFactory::create([
    'dependencies' => [
        MyService::class => function($c) {
            return new MyService(
                $c->get(ConfigManager::class)
            );
        },
    ],
]);
```

### 3. 自定义构建器

```php
class CustomBuilder extends ComponentBuilder {
    public function build(): CData {
        // 自定义构建逻辑
    }
    
    public function getType(): string {
        return 'custom';
    }
}
```

## 性能考虑

### 1. 容器缓存

```php
// 生产环境启用编译缓存
$container = ContainerFactory::create($config, true);
```

### 2. 事件优化

```php
// 一次性监听器
$dispatcher->once('event', $handler);

// 移除监听器
$dispatcher->removeListener('event', $handler);
```

### 3. 状态更新

```php
// 批量更新（单次触发）
$state->update([
    'name' => 'John',
    'age' => 30,
]);
```

## 测试

所有组件都支持单元测试：

```php
// 测试配置管理
$config = new ConfigManager(['test' => 'value']);
$this->assertEquals('value', $config->get('test'));

// 测试事件
$dispatcher = new EventDispatcher();
$fired = false;
$dispatcher->on('test', fn() => $fired = true);
$dispatcher->dispatch((object)['type' => 'test']);
$this->assertTrue($fired);

// 测试状态管理
$state = StateManager::instance();
$state->set('key', 'value');
$this->assertEquals('value', $state->get('key'));
```

## 总结

libuiBuilder 通过深度集成三大依赖包，提供了：

1. **配置管理** - 类型安全，多格式支持
2. **事件系统** - 解耦业务逻辑，支持异步
3. **依赖注入** - 自动化服务管理
4. **状态管理** - 双向绑定，响应式更新
5. **链式构建** - 声明式 UI，易于维护

这套架构使得 GUI 开发更加现代化、可维护、可测试。