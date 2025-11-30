# libuiBuilder

Builder 方式开发 [kingbes/libui](https://github.com/kingbes/libui) GUI 应用，提供直观、灵活的 PHP 桌面应用开发体验。

## ✨ 特性

- 🎨 **Builder 模式** - 流畅的链式调用 API
- 🌐 **HTML 模板渲染** - 使用熟悉的 HTML 语法定义界面
- 📊 **强大的 Grid 布局** - 精确的二维布局控制
- 🔄 **状态管理** - 响应式数据绑定
- 🎯 **事件系统** - 简洁的事件处理
- 📦 **组件复用** - 模板系统支持
- 🧪 **完整测试** - Pest 测试覆盖
- 👁️ **可视化预览工具** - 浏览器实时预览 `.ui.html` 布局

## 🚀 快速开始

### 安装

```bash
composer require yangweijie/libui-builder
```

### 方式一：Builder API

```php
<?php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('username', '');

$app = Builder::window()
    ->title('登录窗口')
    ->size(400, 300)
    ->contains([
        Builder::grid()->padded(true)->form([
            [
                'label' => Builder::label()->text('用户名:'),
                'control' => Builder::entry()
                    ->id('usernameInput')
                    ->bind('username')
                    ->placeholder('请输入用户名')
            ]
        ])->append([
            Builder::button()
                ->text('登录')
                ->onClick(function($button, $state) {
                    echo "登录: " . $state->get('username') . "\n";
                })
        ])
    ]);

$app->show();
```

### 方式二：HTML 模板（推荐）

**views/login.ui.html:**
```html
<!DOCTYPE html>
<ui version="1.0">
  <window title="登录窗口" size="400,300" centered="true">
    <grid padded="true">
      <label row="0" col="0" align="end,center">用户名:</label>
      <input 
        id="usernameInput"
        row="0" 
        col="1" 
        bind="username"
        placeholder="请输入用户名"
        expand="horizontal"
      />
      
      <button row="1" col="0" colspan="2" onclick="handleLogin">
        登录
      </button>
    </grid>
  </window>
</ui>
```

**app.php:**
```php
<?php
use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('username', '');

$handlers = [
    'handleLogin' => function($button, $state) {
        echo "登录: " . $state->get('username') . "\n";
    }
];

$app = HtmlRenderer::render('views/login.ui.html', $handlers);
$app->show();
```

## 🛠️ 开发工具

### 可视化预览工具

在编写 `.ui.html` 模板时，使用可视化预览工具实时查看布局效果：

```bash
# 在浏览器中打开预览工具
open tools/preview.html

# 然后加载任意 .ui.html 文件
# 例如: example/views/login.ui.html
```

**特性**:
- ✅ 零依赖，纯浏览器运行
- ✅ 完整支持 Grid 布局属性
- ✅ 可视化显示状态绑定和事件处理器
- ✅ 缩放控制和网格线显示

详细使用说明: [tools/README.md](tools/README.md)

## 📚 文档

- [HTML 渲染器完整文档](docs/HTML_RENDERER.md)
- [Builder API 参考](docs/BUILDER_API.md)
- [状态管理指南](docs/STATE_MANAGEMENT.md)
- [Grid 布局详解](docs/GRID_LAYOUT.md)
- [可视化预览工具](tools/README.md) 🆕

## 🎯 核心概念

### HTML 模板系统

使用 HTML 标签定义界面，自动渲染为原生 GUI 组件：

```html
<grid padded="true">
  <!-- 网格布局：row/col 定位 -->
  <label row="0" col="0">姓名:</label>
  <input row="0" col="1" bind="name" expand="horizontal"/>
  
  <!-- 跨列布局 -->
  <button row="1" col="0" colspan="2" align="center">
    提交
  </button>
</grid>
```

**支持的标签：**
- 容器: `<window>`, `<vbox>`, `<hbox>`, `<grid>`, `<tab>`
- 控件: `<input>`, `<button>`, `<label>`, `<checkbox>`, `<radio>`
- 选择: `<combobox>`, `<spinbox>`, `<slider>`, `<progressbar>`
- 其他: `<separator>`, `<table>`, `<canvas>`

### Grid 布局

精确的二维布局系统：

```html
<grid padded="true">
  <!-- 基础定位 -->
  <label row="0" col="0">字段1:</label>
  <input row="0" col="1"/>
  
  <!-- 跨行列 -->
  <label row="1" col="0" rowspan="2">多行标签</label>
  <input row="1" col="1" colspan="2"/>
  
  <!-- 对齐和扩展 -->
  <button 
    row="2" 
    col="0" 
    colspan="3" 
    align="center"
    expand="horizontal"
  >提交</button>
</grid>
```

**布局属性：**
- `row`, `col`: 位置
- `rowspan`, `colspan`: 跨度
- `align`: 对齐（`fill`, `start`, `center`, `end`）
- `expand`: 扩展（`true`, `horizontal`, `vertical`）

### 状态管理

响应式数据绑定：

```php
// 初始化状态
$state = StateManager::instance();
$state->set('username', '');
$state->set('count', 0);

// 监听变化
$state->watch('count', function($newValue) {
    echo "Count 变更为: {$newValue}\n";
});

// 批量更新
$state->update([
    'username' => 'admin',
    'count' => 10
]);
```

HTML 中绑定：
```html
<input bind="username"/>
<label>{{username}}</label>
```

### 事件系统

```html
<!-- HTML 中定义事件 -->
<button onclick="handleClick">点击</button>
<input onchange="handleChange"/>
<radio onselected="handleSelect">
  <option>A</option>
  <option>B</option>
</radio>
```

```php
// PHP 中处理事件
$handlers = [
    'handleClick' => function($button, $state) {
        echo "按钮被点击\n";
    },
    
    'handleChange' => function($value, $component) {
        echo "新值: {$value}\n";
    },
    
    'handleSelect' => function($index) {
        echo "选择了索引: {$index}\n";
    }
];
```

### 模板复用

```html
<!-- 定义模板 -->
<template id="form-field">
  <label row="{{row}}" col="0">{{label}}</label>
  <input row="{{row}}" col="1" bind="{{bind}}"/>
</template>

<!-- 使用模板 -->
<grid>
  <use template="form-field"/>
</grid>
```

## 📦 支持的组件

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

## 🧪 测试

```bash
# 运行所有测试
./vendor/bin/pest

# 运行 HTML 渲染器测试
./vendor/bin/pest tests/HtmlRendererTest.php

# 运行状态管理测试
./vendor/bin/pest tests/StateManagerTest.php
```

## 📖 示例

查看 `example/` 目录：

- `simple.php` - 简单示例
- `full.php` - 完整控件演示
- `eventAndState.php` - 事件和状态管理
- `htmlLogin.php` - HTML 模板登录表单
- `htmlFull.php` - HTML 模板完整示例

运行示例：

```bash
php example/htmlLogin.php
php example/htmlFull.php
```

## 🎨 最佳实践

### 1. 使用 HTML 模板作为主要开发方式

✅ **推荐：**
```html
<window title="我的应用" size="800,600">
  <grid padded="true">
    <!-- 清晰的界面定义 -->
  </grid>
</window>
```

❌ **不推荐（除非需要动态构建）：**
```php
Builder::window()
    ->title('我的应用')
    ->size(800, 600)
    ->contains([
        Builder::grid()->...
    ]);
```

### 2. 组织项目结构

```
project/
├── views/              # HTML 模板
│   ├── login.ui.html
│   └── dashboard.ui.html
├── handlers/           # 事件处理器
│   ├── LoginHandlers.php
│   └── DashboardHandlers.php
├── state/              # 状态管理
│   └── AppState.php
└── app.php             # 主入口
```

### 3. 分离事件处理逻辑

```php
class LoginHandlers {
    public static function getHandlers(): array {
        return [
            'handleLogin' => [self::class, 'login'],
            'handleReset' => [self::class, 'reset'],
        ];
    }
    
    public static function login($button, $state) {
        // 登录逻辑
    }
    
    public static function reset($button, $state) {
        // 重置逻辑
    }
}
```

### 4. 使用 Grid 布局

优先使用 Grid 而不是嵌套的 Box：

✅ **好：**
```html
<grid>
  <label row="0" col="0">字段1:</label>
  <input row="0" col="1"/>
  <label row="1" col="0">字段2:</label>
  <input row="1" col="1"/>
</grid>
```

❌ **不好：**
```html
<vbox>
  <hbox>
    <label>字段1:</label>
    <input/>
  </hbox>
  <hbox>
    <label>字段2:</label>
    <input/>
  </hbox>
</vbox>
```

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

MIT License

## 🙏 致谢

基于 [kingbes/libui](https://github.com/kingbes/libui) 构建。

---

**注意**: 本项目主要提供两种开发方式：
1. **HTML 模板渲染**（推荐） - 熟悉的语法、可视化预览、组件复用
2. **Builder API** - 动态构建、编程灵活性

两种方式可以混合使用，选择最适合你的工作流！
