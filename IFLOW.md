# libuiBuilder - iFlow CLI 上下文文件

## 项目概述

libuiBuilder 是一个基于 PHP 的 GUI 应用开发框架，通过 Builder 模式和 HTML 模板系统简化了 [kingbes/libui](https://github.com/kingbes/libui) 桌面应用的开发。项目提供两种主要的开发方式：

1. **HTML 模板渲染**（推荐）- 使用熟悉的 HTML 语法定义界面
2. **Builder API** - 通过链式调用动态构建界面

### 核心特性

- 🎨 Builder 模式 - 流畅的链式调用 API
- 🌐 HTML 模板渲染 - 使用 HTML 语法定义界面，支持可视化预览
- 📊 强大的 Grid 布局 - 精确的二维布局控制
- 📐 响应式网格 - 自动适应空间的布局系统（ResponsiveGridBuilder）
- 🔄 状态管理 - 响应式数据绑定和全局状态共享
- 🎯 事件系统 - 简洁的事件处理机制
- 📦 组件复用 - 模板系统支持组件复用
- 🧪 完整测试 - Pest 测试框架覆盖
- 🎨 可视化设计 - Web-based designer for drag-and-drop UI creation
- ⌨️ 便捷函数 - Helper functions for faster development

## 技术栈

- **语言**: PHP 8+
- **GUI 框架**: kingbes/libui (基于 libui)
- **测试框架**: Pest
- **依赖管理**: Composer
- **扩展依赖**: ext-ffi, ext-dom, ext-libxml
- **前端工具**: HTML/CSS/JavaScript (for visualization designer)
- **浏览器自动化**: Puppeteer (for end-to-end testing)

## 项目结构

```
libuiBuilder/
├── composer.json           # 项目依赖配置
├── package.json            # 前端工具依赖配置
├── src/                    # 核心源代码
│   ├── Builder.php         # 视图构建器入口
│   ├── HtmlRenderer.php    # HTML 模板渲染器
│   ├── ComponentBuilder.php # 组件构建器基类
│   ├── ResponsiveGridBuilder.php # 响应式网格布局
│   ├── helper.php          # 便捷函数库
│   ├── Builder/            # 构建器扩展
│   │   └── TabBuilder.php
│   ├── Components/         # GUI 组件实现
│   │   ├── WindowBuilder.php
│   │   ├── GridBuilder.php
│   │   ├── BoxBuilder.php
│   │   ├── ButtonBuilder.php
│   │   ├── EntryBuilder.php
│   │   ├── CanvasBuilder.php
│   │   ├── CheckboxBuilder.php
│   │   ├── ComboboxBuilder.php
│   │   ├── GridItemBuilder.php
│   │   ├── LabelBuilder.php
│   │   ├── MenuBuilder.php
│   │   ├── MenuItemBuilder.php
│   │   ├── MultilineEntryBuilder.php
│   │   ├── ProgressBarBuilder.php
│   │   ├── RadioBuilder.php
│   │   ├── SeparatorBuilder.php
│   │   ├── SliderBuilder.php
│   │   ├── SpinboxBuilder.php
│   │   ├── SubMenuBuilder.php
│   │   ├── TableBuilder.php
│   │   └── DrawContext.php
│   ├── State/              # 状态管理
│   │   ├── StateManager.php
│   │   └── ComponentRef.php
│   ├── Templates/          # 内置模板
│   │   ├── FormTemplate.php
│   │   └── ResponsiveGrid.php
│   └── Validation/         # 表单验证
├── example/                # 示例代码
│   ├── htmlFull.php        # HTML 模板完整示例
│   ├── htmlLogin.php       # HTML 模板登录示例
│   ├── simple.php          # Builder API 简单示例
│   ├── calculator.php      # 计算器示例
│   ├── calculator_html.php # HTML计算器示例
│   ├── calculator_html_simple.php # 简化计算器示例
│   ├── eventAndState.php   # 事件和状态管理示例
│   ├── simple_table_demo.php # 简单表格示例
│   ├── table_demo.php      # 表格示例
│   ├── complex_table_demo.php # 复杂表格示例
│   ├── dynamic_table_demo.php # 动态表格示例
│   ├── working_table_demo.php # 工作表格示例
│   ├── form_table.php      # 表单表格示例
│   ├── form_table_builder.php # 表单构建器示例
│   ├── form_table_builder_html.php # HTML表单构建器示例
│   ├── builder_helpers_demo.php # 构建器助手演示
│   ├── helper_shortcuts_demo.php # 助手函数演示
│   ├── responseGrid.php    # 响应式网格示例
│   ├── full.php            # 完整示例
│   ├── standard_html_demo.php # 标准HTML演示
│   └── views/              # HTML 模板文件
├── tools/                  # 开发工具
│   ├── designer.html       # 可视化设计器主页面
│   ├── designer.css        # 设计器样式
│   ├── designer.js         # 设计器逻辑
│   ├── libui-ng-complete.css # 跨平台样式库
│   ├── preview.html        # 预览工具
│   └── README.md           # 工具说明文档
├── tests/                  # 测试文件
│   ├── BasicTest.php
│   ├── BuilderComponentsTest.php
│   ├── BuilderHelperTest.php
│   ├── ComponentRefTest.php
│   ├── HelperBuilderFunctionsTest.php
│   ├── HelperFunctionsTest.php
│   ├── HtmlRendererBasicTest.php
│   ├── HtmlRendererExtendedTest.php
│   ├── StateHelperTest.php
│   └── StateManagerBasicTest.php
├── docs/                   # 文档
│   └── HTML_RENDERER.md    # HTML渲染器文档
├── run_tests.sh            # 测试运行脚本
└── vendor/                 # Composer 依赖
```

## 构建和运行

### 安装依赖

```bash
composer install
npm install  # For visualization tools
```

### 运行示例

```bash
# HTML 模板完整示例
php example/htmlFull.php

# HTML 模板登录示例
php example/htmlLogin.php

# Builder API 简单示例
php example/simple.php

# 计算器示例
php example/calculator.php
php example/calculator_html.php

# 表格示例
php example/table_demo.php

# 响应式网格示例
php example/responseGrid.php
```

### 运行测试

```bash
# 运行所有测试
./vendor/bin/pest

# 运行特定测试
./vendor/bin/pest tests/HtmlRendererBasicTest.php
./vendor/bin/pest tests/StateManagerBasicTest.php
./vendor/bin/pest tests/BuilderComponentsTest.php

# 使用测试运行脚本（交互式）
bash run_tests.sh

# 运行特定测试类型
bash run_tests.sh 3 # 运行基础测试
bash run_tests.sh 4 # 运行StateManager测试
bash run_tests.sh 5 # 运行HtmlRenderer测试

# 生成测试覆盖率报告
./vendor/bin/pest --coverage
./vendor/bin/pest --coverage --coverage-html=coverage-report
```

### 运行可视化设计器

```bash
# 打开可视化设计器（在浏览器中打开 tools/designer.html）
open tools/designer.html

# 或使用预览工具
open tools/preview.html
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

### 响应式设计规范

1. 使用 `ResponsiveGridBuilder` 实现响应式布局
2. 利用 `Templates\ResponsiveGrid::create()` 工厂方法
3. 考虑控件类型设置合适的对齐和扩展方式

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

### 响应式网格 (ResponsiveGridBuilder)

自动适应可用空间的网格布局，可以设置总列数并以比例分配控件宽度：

```php
use Kingbes\Libui\View\Templates\ResponsiveGrid;

$layout = ResponsiveGrid::create(12)  // 12列网格
    ->col(Builder::label()->text('标题'), 12)  // 全宽
    ->col(Builder::label()->text('左侧'), 6)   // 半宽
    ->col(Builder::label()->text('右侧'), 6)   // 半宽
    ->col(Builder::button()->text('1/4'), 3)  // 四分之一宽
    ->build();
```

### 状态管理

响应式数据绑定系统：
```php
$state = StateManager::instance();
$state->set('username', '');
$state->watch('username', function($newValue) {
    echo "用户名变更为: {$newValue}\n";
});
```

### 便捷函数 (Helper Functions)

项目提供了一系列便捷函数来简化开发：

```php
// 状态管理辅助函数
state();                    // 获取状态管理器实例
state('key', 'value');     // 设置状态值
state('key');              // 获取状态值
watch('key', $callback);   // 监听状态变化

// 组件构建快捷函数
window(); vbox(); hbox(); grid(); tab();  // 容器组件
button(); label(); entry(); checkbox(); combobox();  // 基础控件
textarea(); spinbox(); slider(); radio();  // 输入控件
progressBar(); table(); canvas();  // 其他控件
separator(); menu(); passwordEntry();  // 特殊控件

// 表单构建辅助函数
input('用户名', 'username', 'text', '请输入用户名');
select('角色', 'role', ['管理员', '用户', '访客'], 'combobox');
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
- `ResponsiveGridBuilder` - 响应式网格布局

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
- `GroupBuilder` - 分组控件（带有标题的容器）
- `DrawContext` - 绘图上下文

## 内置模板

### FormTemplate - 表单模板
提供快速创建表单的模板系统：

```php
use Kingbes\Libui\View\Templates\FormTemplate;

$userForm = FormTemplate::create([
    ['label' => '用户名', 'type' => 'text', 'placeholder' => '请输入用户名'],
    ['label' => '密码', 'type' => 'password', 'placeholder' => '请输入密码'],
    ['label' => '记住我', 'type' => 'checkbox', 'text' => '下次自动登录'],
]);
```

### ResponsiveGrid - 响应式网格模板
提供创建响应式布局的模板：

```php
use Kingbes\Libui\View\Templates\ResponsiveGrid;

$layout = ResponsiveGrid::create(12)
    ->col(Builder::label()->text('标题'), 12)
    ->col(Builder::entry(), 6)
    ->col(Builder::button()->text('提交'), 6)
    ->build();
```

## 开发工具

### 可视化设计器
tools/ 目录包含一个基于 Web 的可视化界面设计器，支持：
- 拖拽式组件布局
- 实时预览
- 属性编辑
- 代码导出
- 平台样式切换

使用方法：
1. 在浏览器中打开 `tools/designer.html`
2. 从左侧组件面板拖拽组件到设计区域
3. 点击组件编辑属性
4. 生成符合 libuiBuilder 规范的 HTML 代码

### 预览工具
- `tools/preview.html` - 用于预览 `.ui.html` 模板文件

## 最佳实践

1. **优先使用 HTML 模板** - 更直观、易维护
2. **使用 Grid 布局** - 避免深层嵌套的 Box
3. **利用响应式网格** - 对于动态布局使用 ResponsiveGridBuilder
4. **分离事件处理逻辑** - 使用专门的处理器类
5. **合理组织项目结构** - 分离模板、处理器和状态管理
6. **利用模板复用** - 使用 `<template>` 和 `<use>` 标签
7. **使用便捷函数** - 使用 helper.php 中的快捷函数提高开发效率
8. **可视化设计** - 使用工具目录中的设计器创建界面

## 调试技巧

1. 使用 `StateManager::dump()` 查看状态
2. 通过 `ComponentRef` 直接访问组件实例
3. 查看 `example/` 目录中的示例代码
4. 运行测试确保功能正常
5. 使用 `run_tests.sh` 脚本进行交互式测试
6. 使用可视化设计器预览界面布局

## 常见问题

1. **确保安装了 ext-ffi, ext-dom, ext-libxml 扩展**
2. **HTML 模板文件必须使用 `.ui.html` 扩展名**
3. **事件处理器必须在渲染时传入**
4. **Grid 布局中的行列索引从 0 开始**
5. **使用 ResponsiveGridBuilder 时注意控件对齐方式**
6. **在使用 helper 函数前确保已加载 src/helper.php**

## 贡献指南

1. Fork 项目
2. 创建功能分支
3. 编写测试
4. 提交 Pull Request

## 许可证

MIT License