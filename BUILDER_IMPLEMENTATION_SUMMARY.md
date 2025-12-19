# 链式构建器模式 GUI 框架 - 实现总结

## 项目概述

基于 kingbes/libui 包实现的链式构建器模式 GUI 开发框架，提供了现代化、易用的 API 来创建跨平台桌面应用。

## 已实现的核心组件

### 1. 核心构建器类 (src/Builder/)

| 文件 | 类名 | 描述 |
|------|------|------|
| `ComponentBuilder.php` | 抽象类 | 所有构建器的基类，提供链式调用、事件处理、状态绑定 |
| `WindowBuilder.php` | 窗口构建器 | 窗口创建、大小设置、子组件管理、事件绑定 |
| `Builder.php` | 工厂类 | 静态工厂方法，创建各种组件构建器 |

### 2. UI 组件构建器 (src/Builder/)

| 文件 | 类名 | 主要方法 |
|------|------|----------|
| `ButtonBuilder.php` | 按钮构建器 | text(), onClick() |
| `LabelBuilder.php` | 标签构建器 | text(), align() |
| `EntryBuilder.php` | 输入框构建器 | placeholder(), bind(), password(), onChange() |
| `GridBuilder.php` | 网格布局构建器 | columns(), padded(), append(), form() |
| `BoxBuilder.php` | 盒子容器构建器 | direction(), padded(), contains(), append() |
| `TabBuilder.php` | 标签页构建器 | tabs(), addTab(), onTabSelected() |
| `TableBuilder.php` | 表格构建器 | columns(), data(), onRowSelected() |
| `CheckboxBuilder.php` | 复选框构建器 | text(), checked(), bind(), onChange() |
| `ComboboxBuilder.php` | 组合框构建器 | items(), selected(), bind(), onChange() |
| `SeparatorBuilder.php` | 分隔线构建器 | 无额外方法 |
| `ProgressBarBuilder.php` | 进度条构建器 | value(), bind(), setValue() |
| `SliderBuilder.php` | 滑块构建器 | range(), value(), bind(), onChange() |
| `SpinboxBuilder.php` | 数字输入框构建器 | range(), value(), bind(), onChange() |
| `GroupBuilder.php` | 组容器构建器 | title(), margined(), contains() |

### 3. 状态管理系统 (src/State/)

| 文件 | 类名 | 描述 |
|------|------|------|
| `StateManager.php` | 状态管理器 | 单例模式，提供状态存储、监听、组件注册、状态绑定 |

### 4. 测试和示例

| 文件 | 描述 |
|------|------|
| `tests/BuilderTest.php` | 完整的单元测试套件 |
| `example/04_advanced/builder_example.php` | 完整的功能演示示例 |

## 核心特性

### 1. 链式调用
```php
Builder::window()
    ->title('应用')
    ->size(800, 600)
    ->contains(
        Builder::button()->text('点击')
    )
```

### 2. 状态绑定
```php
Builder::entry()
    ->bind('username')  // 自动同步到状态管理器
    ->onChange(function($value, $component, $stateManager) {
        // 值改变时自动更新状态
    })
```

### 3. 组件注册
```php
Builder::button()
    ->id('myButton')  // 注册到状态管理器
    ->onClick(function($button, $stateManager) {
        $other = $stateManager->getComponent('otherButton');
    })
```

### 4. 事件处理
```php
Builder::window()
    ->onClosing(function() {
        // 窗口关闭前处理
        return 0; // 允许关闭
    })
```

### 5. 快速表单
```php
Builder::grid()
    ->form([
        [
            'label' => Builder::label()->text('用户名:'),
            'control' => Builder::entry()->bind('username')
        ]
    ])
```

## 设计模式应用

### 1. 建造者模式 (Builder Pattern)
- 每个构建器类提供链式方法
- 最后调用 `build()` 创建实际控件
- 延迟实例化，支持复杂配置

### 2. 单例模式 (Singleton Pattern)
- `StateManager` 使用单例确保全局状态一致性
- 通过 `instance()` 方法获取实例

### 3. 工厂模式 (Factory Pattern)
- `Builder` 类提供静态工厂方法
- 统一的创建接口，隐藏具体实现

### 4. 观察者模式 (Observer Pattern)
- `StateManager::watch()` 监听状态变化
- 自动更新绑定的组件

## 与现有代码的集成

### 兼容性
- 完全基于 kingbes/libui 的底层 API
- 使用相同的命名空间 `Kingbes\Libui\View\Builder`
- 保持与现有示例代码的兼容性

### 扩展性
- 所有构建器继承自 `ComponentBuilder`
- 易于添加新的组件类型
- 支持自定义事件和配置

## 使用示例对比

### 传统方式 (kingbes/libui)
```php
use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Button;

App::init();

$window = Window::create('标题', 800, 600, 0);
Window::setMargined($window, true);

$button = Button::create('点击');
Button::onClicked($button, function() {
    echo "点击\n";
});

Window::setChild($window, $button);
Window::setContentSize($window, 800, 600);
Control::show($window);
App::main();
```

### 构建器方式 (本框架)
```php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;

App::init();

Builder::window()
    ->title('标题')
    ->size(800, 600)
    ->margined(true)
    ->contains(
        Builder::button()
            ->text('点击')
            ->onClick(function() {
                echo "点击\n";
            })
    )
    ->show();

App::main();
```

## 优势

1. **代码更简洁**: 减少重复的变量赋值和方法调用
2. **可读性更强**: 链式调用接近自然语言描述
3. **状态管理**: 集中管理组件状态，便于数据共享
4. **组件复用**: 通过 ID 注册和检索组件
5. **类型安全**: 完整的类型提示和 IDE 支持
6. **易于测试**: 组件可独立测试，状态可模拟

## 文件清单

### 核心框架 (17个文件)
```
src/
├── Builder/
│   ├── ComponentBuilder.php      (4,736 字节)
│   ├── WindowBuilder.php         (6,883 字节)
│   ├── Builder.php               (6,292 字节)
│   ├── ButtonBuilder.php         (2,056 字节)
│   ├── LabelBuilder.php          (2,057 字节)
│   ├── EntryBuilder.php          (4,566 字节)
│   ├── GridBuilder.php           (4,777 字节)
│   ├── BoxBuilder.php            (3,447 字节)
│   ├── TabBuilder.php            (2,942 字节)
│   ├── TableBuilder.php          (3,710 字节)
│   ├── CheckboxBuilder.php       (3,716 字节)
│   ├── ComboboxBuilder.php       (4,596 字节)
│   ├── SeparatorBuilder.php        (763 字节)
│   ├── ProgressBarBuilder.php    (2,482 字节)
│   ├── SliderBuilder.php         (3,691 字节)
│   ├── SpinboxBuilder.php        (3,732 字节)
│   └── GroupBuilder.php          (2,101 字节)
└── State/
    └── StateManager.php          (4,989 字节)
```

### 文档和测试
```
docs/
└── BUILDER_README.md             (详细使用文档)

tests/
└── BuilderTest.php               (8,240 字节，完整测试套件)

example/04_advanced/
└── builder_example.php           (13,861 字节，完整示例)
```

## 未来扩展建议

1. **更多组件**: 可添加 MenuBuilder, DialogBuilder 等
2. **样式系统**: 支持 CSS-like 样式配置
3. **路由系统**: 单页应用路由管理
4. **国际化**: 多语言支持
5. **主题系统**: 支持明暗主题切换

## 总结

本框架成功实现了链式构建器模式，为 kingbes/libui 提供了现代化的开发体验。通过抽象基类和工厂模式，实现了代码复用和统一接口。状态管理系统增强了组件间的数据共享能力，使复杂应用的开发更加简单。

所有代码遵循 PHP 标准，支持类型提示，易于维护和扩展。
