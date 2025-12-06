# HTML 渲染器文档

## 概述

HtmlRenderer 是 libuiBuilder 的 HTML 模板渲染系统，允许开发者使用熟悉的 HTML 语法来定义 GUI 界面，然后自动渲染为 Builder 组件树。

## 核心优势

### 1. **熟悉的语法**
使用 HTML 标签，无需学习新的 API：
```html
<window title="我的应用" size="800,600">
  <grid padded="true">
    <label row="0" col="0">用户名:</label>
    <input row="0" col="1" bind="username"/>
  </grid>
</window>
```

### 2. **属性灵活性**
属性顺序无关，IDE 友好：
```html
<!-- 这两种写法完全等价 -->
<input bind="username" row="0" col="1" expand="horizontal"/>
<input expand="horizontal" col="1" bind="username" row="0"/>
```

### 3. **可视化预览**
HTML 文件可以被工具解析，生成布局预览图。

### 4. **组件复用**
通过模板系统实现真正的组件复用：
```html
<template id="form-field">
  <label>{{label}}</label>
  <input bind="{{name}}"/>
</template>

<use template="form-field"/>
```

---

## 快速开始

### 1. 创建 HTML 模板

创建 `views/login.ui.html`：

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
      
      <label row="1" col="0" align="end,center">密码:</label>
      <input 
        id="passwordInput"
        row="1" 
        col="1" 
        type="password"
        bind="password"
        placeholder="请输入密码"
        expand="horizontal"
      />
      
      <hbox row="2" col="0" colspan="2">
        <button onclick="handleLogin">登录</button>
        <button onclick="handleReset">清空</button>
      </hbox>
    </grid>
  </window>
</ui>
```

### 2. 编写 PHP 代码

```php
<?php
use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态
$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');

// 定义事件处理器
$handlers = [
    'handleLogin' => function($button, $state) {
        $username = $state->get('username');
        $password = $state->get('password');
        
        if ($username === 'admin' && $password === 'admin') {
            echo "登录成功！\n";
        }
    },
    
    'handleReset' => function($button, $state) {
        $state->update(['username' => '', 'password' => '']);
    }
];

// 渲染 HTML
$app = HtmlRenderer::render('views/login.ui.html', $handlers);
$app->show();
```

---

## 支持的标签

### 窗口和容器

#### `<window>`
主窗口容器

**属性：**
- `title`: 窗口标题
- `size`: 窗口尺寸，格式 `"width,height"`
- `centered`: 是否居中，`"true"` 或 `"false"`
- `margined`: 是否有边距，`"true"` 或 `"false"`

**示例：**
```html
<window title="我的应用" size="800,600" centered="true" margined="true">
  <!-- 内容 -->
</window>
```

#### `<vbox>`
垂直盒子布局

**属性：**
- `padded`: 是否添加内边距

**示例：**
```html
<vbox padded="true">
  <label>第一行</label>
  <label>第二行</label>
</vbox>
```

#### `<hbox>`
水平盒子布局

**属性：**
- `padded`: 是否添加内边距

**示例：**
```html
<hbox padded="true">
  <button>按钮1</button>
  <button>按钮2</button>
</hbox>
```

#### `<grid>`
网格布局（核心布局组件）

**属性：**
- `padded`: 是否添加内边距

**子元素布局属性：**
- `row`: 行位置（从 0 开始）
- `col`: 列位置（从 0 开始）
- `rowspan`: 跨越的行数
- `colspan`: 跨越的列数
- `align`: 对齐方式，格式 `"horizontal,vertical"`
  - 可选值：`fill`, `start`, `center`, `end`
- `expand`: 扩展方式
  - `"true"`: 水平和垂直都扩展
  - `"horizontal"` 或 `"h"`: 仅水平扩展
  - `"vertical"` 或 `"v"`: 仅垂直扩展

**示例：**
```html
<grid padded="true">
  <!-- 标签：右对齐，垂直居中 -->
  <label row="0" col="0" align="end,center">用户名:</label>
  
  <!-- 输入框：水平扩展 -->
  <input row="0" col="1" expand="horizontal"/>
  
  <!-- 按钮：跨2列，居中 -->
  <button row="1" col="0" colspan="2" align="center">提交</button>
</grid>
```

#### `<tab>`
标签页容器

**子元素：**
- `<tabpage>`: 单个标签页，需要 `title` 属性

**示例：**
```html
<tab>
  <tabpage title="第一页">
    <label>内容1</label>
  </tabpage>
  <tabpage title="第二页">
    <label>内容2</label>
  </tabpage>
</tab>
```

#### `<group>`
分组容器（带有标题的容器）

**属性：**
- `title`: 分组标题
- `margined`: 是否有边距，`"true"` 或 `"false"`

**示例：**
```html
<group title="用户信息" margined="true">
  <grid padded="true">
    <label row="0" col="0">用户名:</label>
    <input row="0" col="1" bind="username"/>
  </grid>
</group>
```

### 控件

#### `<label>`
文本标签

**内容：**
- 标签文本内容

**示例：**
```html
<label>这是一个标签</label>
```

#### `<input>`
输入控件

**属性：**
- `type`: 输入类型
  - `"text"` 或省略: 单行文本（默认）
  - `"password"`: 密码输入
  - `"multiline"` 或 `"textarea"`: 多行文本
- `placeholder`: 占位符文本
- `readonly`: 是否只读，`"true"` 或 `"false"`
- `wordwrap`: 多行文本是否自动换行（仅 `type="multiline"`）

**示例：**
```html
<!-- 单行输入 -->
<input placeholder="请输入文本"/>

<!-- 密码输入 -->
<input type="password" placeholder="请输入密码"/>

<!-- 多行输入 -->
<input type="multiline" wordwrap="true"/>
```

#### `<button>`
按钮

**内容：**
- 按钮文本

**示例：**
```html
<button onclick="handleClick">点击我</button>
```

#### `<checkbox>`
复选框

**属性：**
- `checked`: 是否选中，`"true"` 或 `"false"`

**内容：**
- 复选框文本

**示例：**
```html
<checkbox checked="true">记住我</checkbox>
```

#### `<radio>`
单选框组

**属性：**
- `selected`: 默认选中项的索引（从 0 开始）

**子元素：**
- `<option>`: 单选项

**示例：**
```html
<radio selected="0">
  <option>选项A</option>
  <option>选项B</option>
  <option>选项C</option>
</radio>
```

#### `<combobox>`
下拉选择框

**属性：**
- `selected`: 默认选中项的索引（从 0 开始）

**子元素：**
- `<option>`: 选项

**示例：**
```html
<combobox selected="0">
  <option>请选择</option>
  <option>北京</option>
  <option>上海</option>
  <option>广州</option>
</combobox>
```

#### `<spinbox>`
数字输入框

**属性：**
- `min`: 最小值
- `max`: 最大值
- `value`: 初始值

**示例：**
```html
<spinbox min="0" max="100" value="50"/>
```

#### `<slider>`
滑动条

**属性：**
- `min`: 最小值
- `max`: 最大值
- `value`: 初始值

**示例：**
```html
<slider min="0" max="100" value="30"/>
```

#### `<progressbar>`
进度条

**属性：**
- `value`: 进度值（0-100）

**示例：**
```html
<progressbar value="75"/>
```

#### `<separator>`
分隔符

**属性：**
- `orientation`: 方向，`"horizontal"` 或 `"vertical"`

**示例：**
```html
<separator orientation="horizontal"/>
```

#### `<table>`
表格

**子元素：**
- `<column>`: 列定义

**示例：**
```html
<table id="userTable" bind="userList">
  <column>ID</column>
  <column>姓名</column>
  <column>邮箱</column>
</table>
```

---

## 通用属性

所有组件都支持以下属性：

### `id`
组件的唯一标识符，用于在 PHP 代码中引用：

```html
<input id="usernameInput"/>
```

在 PHP 中访问：
```php
$input = StateManager::instance()->getComponent('usernameInput');
```

### `bind`
数据绑定，将组件值与状态管理器中的某个键绑定：

```html
<input bind="username"/>
```

在 PHP 中读取/设置：
```php
$state = StateManager::instance();
$username = $state->get('username');
$state->set('username', 'newValue');
```

### 事件属性

#### `onclick`
点击事件（按钮）：

```html
<button onclick="handleClick">点击</button>
```

PHP 处理器：
```php
$handlers = [
    'handleClick' => function($button, $stateManager) {
        echo "按钮被点击\n";
    }
];
```

#### `onchange`
值改变事件（输入框、滑动条等）：

```html
<input onchange="handleChange"/>
```

PHP 处理器：
```php
$handlers = [
    'handleChange' => function($value, $component) {
        echo "新值: {$value}\n";
    }
];
```

#### `onselected`
选择事件（单选框、下拉框）：

```html
<radio onselected="handleSelect">
  <option>A</option>
  <option>B</option>
</radio>
```

PHP 处理器：
```php
$handlers = [
    'handleSelect' => function($index) {
        echo "选择了索引: {$index}\n";
    }
];
```

---

## 模板系统

### 定义模板

使用 `<template>` 标签定义可复用的模板：

```html
<template id="form-field">
  <label row="{{row}}" col="0">{{label}}</label>
  <input row="{{row}}" col="1" bind="{{bind}}"/>
</template>
```

### 使用模板

使用 `<use>` 标签引用模板：

```html
<use template="form-field"/>
```

### 模板变量

在 HTML 中使用 `{{variableName}}` 语法定义变量：

```html
<window title="{{windowTitle}}">
  <label>欢迎, {{username}}!</label>
</window>
```

在 PHP 中传入变量值：

```php
$variables = [
    'windowTitle' => '我的应用',
    'username' => 'John'
];

$app = HtmlRenderer::render('view.ui.html', $handlers, $variables);
```

---

## Grid 布局完整示例

```html
<grid padded="true">
  <!-- 第一行：标签 + 输入框 -->
  <label row="0" col="0" align="end,center">姓名:</label>
  <input row="0" col="1" expand="horizontal"/>
  
  <!-- 第二行：标签 + 多列输入 -->
  <label row="1" col="0" align="end,center">地址:</label>
  <input row="1" col="1" colspan="2" expand="horizontal"/>
  
  <!-- 第三行：跨列按钮 -->
  <hbox row="2" col="0" colspan="3" align="center">
    <button>提交</button>
    <button>取消</button>
  </hbox>
  
  <!-- 第四行：状态标签 -->
  <label row="3" col="0" colspan="3" align="center">
    就绪
  </label>
</grid>
```

**渲染结果：**
```
行0: [姓名:     ] [输入框________________]
行1: [地址:     ] [输入框_____________________]
行2:         [提交] [取消]
行3:            就绪
```

---

## 最佳实践

### 1. 文件组织

```
project/
├── views/
│   ├── login.ui.html
│   ├── dashboard.ui.html
│   └── components/
│       ├── form-field.ui.html
│       └── user-card.ui.html
├── src/
│   └── handlers/
│       ├── LoginHandlers.php
│       └── DashboardHandlers.php
└── app.php
```

### 2. 事件处理器分离

```php
// handlers/LoginHandlers.php
class LoginHandlers {
    public static function getHandlers() {
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

// app.php
$app = HtmlRenderer::render(
    'views/login.ui.html', 
    LoginHandlers::getHandlers()
);
```

### 3. 状态管理

```php
// 初始化状态
$state = StateManager::instance();
$state->set('form', [
    'username' => '',
    'password' => '',
    'rememberMe' => false
]);

// 监听状态变化
$state->watch('form.username', function($value) {
    echo "用户名变更: {$value}\n";
});
```

### 4. 组件复用

创建通用组件模板：

```html
<!-- components/labeled-input.ui.html -->
<template id="labeled-input">
  <label row="{{row}}" col="0" align="end,center">{{label}}:</label>
  <input 
    id="{{id}}"
    row="{{row}}" 
    col="1" 
    type="{{type}}"
    bind="{{bind}}"
    placeholder="{{placeholder}}"
    expand="horizontal"
  />
</template>
```

---

## 性能优化

### 1. 避免深度嵌套

❌ **不好：**
```html
<vbox>
  <hbox>
    <vbox>
      <hbox>
        <label>深度嵌套</label>
      </hbox>
    </vbox>
  </hbox>
</vbox>
```

✅ **好：**
```html
<grid>
  <label row="0" col="0">扁平结构</label>
</grid>
```

### 2. 合理使用 Grid

✅ **推荐：**
```html
<grid padded="true">
  <label row="0" col="0">字段1:</label>
  <input row="0" col="1"/>
  <label row="1" col="0">字段2:</label>
  <input row="1" col="1"/>
</grid>
```

### 3. 事件处理器优化

```php
// ❌ 不好：每次都创建新闭包
$handlers = [
    'handleClick' => function() {
        // 重复逻辑
    }
];

// ✅ 好：复用函数
function handleClick($button, $state) {
    // 逻辑
}

$handlers = ['handleClick' => 'handleClick'];
```

---

## 常见问题

### Q: HTML 模板和 Builder API 可以混用吗？

A: 可以！HtmlRenderer 返回的是标准的 Builder 组件，可以继续使用 Builder API：

```php
$app = HtmlRenderer::render('view.ui.html', $handlers);

// 可以继续用 Builder API 修改
$app->size(1024, 768)
    ->centered(true);
```

### Q: 如何处理复杂的事件逻辑？

A: 使用类方法组织事件处理器：

```php
class FormHandlers {
    private StateManager $state;
    
    public function __construct() {
        $this->state = StateManager::instance();
    }
    
    public function handleSubmit($button) {
        $data = $this->state->get('form');
        // 复杂的验证和提交逻辑
    }
    
    public function getHandlers(): array {
        return [
            'handleSubmit' => [$this, 'handleSubmit'],
        ];
    }
}
```

### Q: 支持哪些浏览器预览？

A: HTML 模板是用于 libui 原生控件的，不是 Web 页面。但可以创建预览工具将布局渲染为 SVG 或 PNG。

---

## 完整示例

查看 `example/` 目录下的示例：

- `htmlLogin.php` + `views/login.ui.html` - 登录表单
- `htmlFull.php` + `views/full.ui.html` - 所有控件演示

运行示例：

```bash
php example/htmlLogin.php
php example/htmlFull.php
```

---

## API 参考

### HtmlRenderer::render()

```php
public static function render(
    string $htmlFile,     // HTML 模板文件路径
    array $handlers = [], // 事件处理器映射
    array $variables = [] // 模板变量
): ComponentBuilder
```

**返回值：**
- 根组件（通常是 `WindowBuilder`）

**异常：**
- `Exception`: HTML 文件不存在
- `Exception`: 缺少根元素
- `Exception`: 未知标签

---

## 贡献

欢迎提交 Issue 和 Pull Request！

## 许可证

MIT License
