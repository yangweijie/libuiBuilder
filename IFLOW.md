# libuiBuilder - iFlow CLI 上下文文件

## 项目概述

libuiBuilder 是一个基于 PHP 的 GUI 应用开发框架，通过 Builder 模式和 HTML 模板系统简化了 [kingbes/libui](https://github.com/kingbes/libui) 桌面应用的开发。项目提供两种主要的开发方式：

1. **HTML 模板渲染**（推荐）- 使用熟悉的 HTML 语法定义界面
2. **Builder API** - 通过链式调用动态构建界面

### 核心特性

- 🎨 Builder 模式 - 流畅的链式调用 API
- 🌐 HTML 模板渲染 - 使用 HTML 语法定义界面，支持可视化预览
- 📊 强大的 Grid 布局 - 精确的二维布局控制
- 🔄 状态管理 - 响应式数据绑定和全局状态共享
- 🎯 事件系统 - 简洁的事件处理机制
- 📦 组件复用 - 模板系统支持组件复用
- 🧪 完整测试 - Pest 测试框架覆盖

## 技术栈

- **语言**: PHP 8+
- **GUI 框架**: kingbes/libui (基于 libui)
- **测试框架**: Pest
- **依赖管理**: Composer
- **扩展依赖**: ext-ffi

## 项目结构

```
libuiBuilder/
├── src/                    # 核心源代码
│   ├── Builder.php         # 视图构建器入口
│   ├── HtmlRenderer.php    # HTML 模板渲染器
│   ├── ComponentBuilder.php # 组件构建器基类
│   ├── Components/         # GUI 组件实现
│   │   ├── WindowBuilder.php
│   │   ├── GridBuilder.php
│   │   ├── BoxBuilder.php
│   │   ├── ButtonBuilder.php
│   │   ├── EntryBuilder.php
│   │   └── ...
│   ├── State/              # 状态管理
│   │   ├── StateManager.php
│   │   └── ComponentRef.php
│   ├── Templates/          # 内置模板
│   └── Validation/         # 表单验证
├── example/                # 示例代码
│   ├── htmlFull.php        # HTML 模板完整示例
│   ├── htmlLogin.php       # HTML 模板登录示例
│   ├── simple.php          # Builder API 简单示例
│   └── views/              # HTML 模板文件
├── tests/                  # 测试文件
├── docs/                   # 文档
└── vendor/                 # Composer 依赖
```

## 构建和运行

### 安装依赖

```bash
composer install
```

### 运行示例

```bash
# HTML 模板完整示例
php example/htmlFull.php

# HTML 模板登录示例
php example/htmlLogin.php

# Builder API 简单示例
php example/simple.php
```

### 运行测试

```bash
# 运行所有测试
./vendor/bin/pest

# 运行特定测试
./vendor/bin/pest tests/HtmlRendererTest.php
./vendor/bin/pest tests/StateManagerTest.php
```

## 开发约定

### 代码风格

- 遵循 PSR-4 自动加载规范
- 使用驼峰命名法（camelCase）
- 类名使用 PascalCase
- 方法名使用 camelCase
- 私有属性使用下划线前缀

### 组件开发规范

1. 所有组件继承自 `ComponentBuilder` 基类
2. 实现链式调用方法
3. 提供便捷的工厂方法
4. 支持事件绑定和数据绑定

### HTML 模板规范

1. 使用 `.ui.html` 扩展名
2. 根元素必须是 `<window>`
3. 支持 Grid 布局属性：`row`, `col`, `rowspan`, `colspan`
4. 支持事件属性：`onclick`, `onchange`, `onselected`
5. 支持数据绑定：`bind` 属性

### 状态管理规范

1. 使用 `StateManager::instance()` 获取单例
2. 通过 `set()` 和 `get()` 方法管理状态
3. 使用 `watch()` 方法监听状态变化
4. 通过 `ComponentRef` 访问组件实例

## 核心概念

### HTML 模板系统

使用 HTML 标签定义界面，自动渲染为原生 GUI 组件：

```html
<window title="登录窗口" size="400,300">
  <grid padded="true">
    <label row="0" col="0">用户名:</label>
    <input row="0" col="1" bind="username" expand="horizontal"/>
    <button row="1" col="0" colspan="2" onclick="handleLogin">登录</button>
  </grid>
</window>
```

### Grid 布局

精确的二维布局系统，支持：
- 位置定位：`row`, `col`
- 跨度控制：`rowspan`, `colspan`
- 对齐方式：`align` (`fill`, `start`, `center`, `end`)
- 扩展控制：`expand` (`true`, `horizontal`, `vertical`)

### 状态管理

响应式数据绑定系统：
```php
$state = StateManager::instance();
$state->set('username', '');
$state->watch('username', function($newValue) {
    echo "用户名变更为: {$newValue}\n";
});
```

### 事件系统

```php
$handlers = [
    'handleLogin' => function($button, $state) {
        echo "登录: " . $state->get('username') . "\n";
    }
];
```

## 支持的组件

### 容器组件
- `WindowBuilder` - 主窗口
- `BoxBuilder` - 水平/垂直盒子
- `GridBuilder` - 网格布局
- `TabBuilder` - 标签页

### 基础控件
- `LabelBuilder` - 文本标签
- `ButtonBuilder` - 按钮
- `EntryBuilder` - 单行输入
- `MultilineEntryBuilder` - 多行输入
- `CheckboxBuilder` - 复选框
- `RadioBuilder` - 单选框组

### 选择控件
- `ComboboxBuilder` - 下拉选择
- `SpinboxBuilder` - 数字输入
- `SliderBuilder` - 滑动条
- `ProgressBarBuilder` - 进度条

### 其他控件
- `SeparatorBuilder` - 分隔符
- `TableBuilder` - 表格
- `CanvasBuilder` - 画布
- `MenuBuilder` - 菜单

## 最佳实践

1. **优先使用 HTML 模板** - 更直观、易维护
2. **使用 Grid 布局** - 避免深层嵌套的 Box
3. **分离事件处理逻辑** - 使用专门的处理器类
4. **合理组织项目结构** - 分离模板、处理器和状态管理
5. **利用模板复用** - 使用 `<template>` 和 `<use>` 标签

## 调试技巧

1. 使用 `StateManager::dump()` 查看状态
2. 通过 `ComponentRef` 直接访问组件实例
3. 查看 `example/` 目录中的示例代码
4. 运行测试确保功能正常

## 常见问题

1. **确保安装了 ext-ffi 扩展**
2. **HTML 模板文件必须使用 `.ui.html` 扩展名**
3. **事件处理器必须在渲染时传入**
4. **Grid 布局中的行列索引从 0 开始**

## 贡献指南

1. Fork 项目
2. 创建功能分支
3. 编写测试
4. 提交 Pull Request

## 许可证

MIT License