# libuiBuilder

基于 kingbes/libui 的现代化 GUI 开发框架，采用链式构建器模式，深度集成了依赖注入、事件系统和配置管理。

## ✨ 核心特性

### 1. 链式构建器模式
```php
$app = Builder::window()
    ->title('My App')
    ->size(600, 400)
    ->contains(
        Builder::vbox()
            ->contains([
                Builder::label()->text('Hello'),
                Builder::button()->text('Click')
            ])
    )
    ->show();
```

### 2. 依赖注入 (PHP-DI)
```php
$container = ContainerFactory::create();
$builder = $container->get(Builder::class);
// Builder 自动注入 StateManager, EventDispatcher, ConfigManager
```

### 3. 事件系统 (league/event)
```php
$events = new EventDispatcher();
$events->on(ButtonClickEvent::class, function($event) {
    echo "按钮 {$event->getComponentId()} 被点击\n";
});
```

### 4. 配置管理 (league/config)
```php
$config = new ConfigManager([
    'app' => ['title' => 'My App', 'width' => 800],
]);
$title = $config->get('app.title');
```

### 5. 状态管理
```php
$state = StateManager::instance();
$state->set('count', 0);
$state->watch('count', fn($new, $old) => echo "变化: $old → $new");
```

## 🚀 快速开始

### 安装
```bash
composer install
```

### 基础使用
```php
require_once 'vendor/autoload.php';

use Kingbes\Libui\View\Core\Container\ContainerFactory;

// 1. 创建容器（自动配置所有服务）
$container = ContainerFactory::create();

// 2. 获取 Builder（已注入依赖）
$builder = $container->get(Builder::class);

// 3. 构建 UI
$app = Builder::window()
    ->title('Hello DI')
    ->size(400, 300)
    ->contains(
        Builder::button()
            ->text('Click Me')
            ->onClick(function($component, $state, $events) {
                echo "Clicked!\n";
            })
    )
    ->show();
```

## 📁 项目结构

```
src/
├── Builder/              # 构建器模式
│   ├── ComponentBuilder.php    # 基类
│   ├── Builder.php             # 工厂类
│   ├── ButtonBuilder.php       # 按钮构建器
│   ├── EntryBuilder.php        # 输入框构建器
│   └── ...                     # 17+ 组件构建器
├── State/               # 状态管理
│   └── StateManager.php
├── Core/                # 核心服务
│   ├── Config/
│   │   └── ConfigManager.php   # 配置管理 (league/config)
│   ├── Event/
│   │   ├── EventDispatcher.php # 事件分发 (league/event)
│   │   ├── ButtonClickEvent.php
│   │   ├── ValueChangeEvent.php
│   │   └── StateChangeEvent.php
│   └── Container/
│       └── ContainerFactory.php # DI 容器 (php-di)
└── helper.php
```

## 🎯 架构概览

```
应用层 (Builder 链式调用)
    ↓
工厂层 (Builder 静态工厂)
    ↓
构建层 (ComponentBuilder + 依赖注入)
    ↓
核心服务 (Config/Event/Container/State)
    ↓
基础库 (kingbes/libui)
```

## 📚 文档

### 核心文档
- **[架构设计](docs/ARCHITECTURE.md)** - 完整架构说明
- **[快速开始](docs/QUICKSTART_DI.md)** - 依赖注入快速指南
- **[构建器文档](docs/BUILDER_README.md)** - 组件构建器使用

### 示例代码
```bash
# 基础示例
php example/04_advanced/builder_example.php

# DI 集成示例
php example/04_advanced/di_integration_example.php

# 验证集成
php test_verify.php
```

## 🔧 依赖包集成

### ✅ league/event
- 事件驱动架构
- 解耦业务逻辑
- 支持全局/局部监听

### ✅ league/config  
- 类型安全配置
- 多格式支持 (PHP/JSON/YAML)
- 配置验证

### ✅ php-di/php-di
- 依赖注入容器
- 自动依赖解析
- 服务生命周期管理

### ✅ kingbes/libui
- PHP FFI GUI 库
- 原生控件绑定
- 跨平台支持

## 🎨 使用场景

### 简单应用
```php
$container = ContainerFactory::create();
$builder = $container->get(Builder::class);

$app = Builder::window()
    ->title('计算器')
    ->contains(/* ... */)
    ->show();
```

### 复杂应用
```php
// 1. 配置
$config = new ConfigManager(require 'config.php');

// 2. 事件系统
$events = new EventDispatcher();
$events->on(UserLoginEvent::class, $loginHandler);

// 3. 状态管理
$state = StateManager::instance();
$state->setEventDispatcher($events);

// 4. 依赖注入
$container = ContainerFactory::create($config->getAll());

// 5. 构建 UI
Builder::setStateManager($state);
Builder::setEventDispatcher($events);
Builder::setConfigManager($config);

$app = Builder::window()
    ->title($config->get('app.title'))
    ->contains(/* 复杂布局 */)
    ->show();
```

## 🧪 测试

```bash
# 验证集成
php test_verify.php

# 运行单元测试
./vendor/bin/pest
```

## 📊 功能对比

| 功能 | 传统方式 | libuiBuilder |
|------|----------|--------------|
| UI 构建 | 命令式 | 声明式链式调用 |
| 配置管理 | 硬编码 | 类型安全配置 |
| 事件处理 | 回调嵌套 | 事件驱动 |
| 依赖管理 | 手动创建 | 自动注入 |
| 状态同步 | 手动更新 | 双向绑定 |
| 代码量 | 多 | 少 50%+ |

## 🎯 设计优势

1. **解耦性** - 事件系统分离业务逻辑
2. **可维护** - 配置驱动，易于修改
3. **可测试** - 依赖注入支持单元测试
4. **可扩展** - 模块化设计，易于扩展
5. **现代化** - 依赖注入 + 事件驱动 + 状态管理

## 🔗 依赖关系

```
libuiBuilder
├── league/event       ^3.0
├── league/config      ^1.2
├── php-di/php-di      ^7.0
├── kingbes/libui      0.1.*
└── 其他标准库...
```

## 🚀 下一步

1. 阅读 [架构设计](docs/ARCHITECTURE.md) 了解完整设计
2. 查看 [快速开始](docs/QUICKSTART_DI.md) 实践示例
3. 运行 [DI 集成示例](example/04_advanced/di_integration_example.php)
4. 探索 [组件构建器](src/Builder/) 源码

## 📝 许可证

MIT License