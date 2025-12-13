# libuiBuilder Examples

欢迎来到 libuiBuilder 示例集合！这里包含了展示 libuiBuilder 各种功能和特性的示例代码。

## 📚 目录结构

示例按复杂度和功能分为以下几个目录：

### 01_basics - 基础示例
**适合初学者学习基本概念**

- **`builder_basic_button.php`** - Builder API 基础按钮示例
- **`html_basic_button.php`** - HTML 模板基础按钮示例  
- **`html_tag_aliases.php`** - HTML 标签别名演示
- **`group_test.php`** - 分组容器示例 (Builder API)
- **`html_group_test.php`** - 分组容器示例 (HTML 模板)
- **`simple.php`** - 完整的基础控件演示
- **`standard_html_demo.php`** - 标准 HTML 标签演示

### 02_layouts - 布局系统示例
**学习各种布局方式**

- **`responseGrid.php`** - 响应式网格布局示例
- **`full.php`** - 完整布局演示 (Builder API)
- **`htmlFull.php`** - 完整布局演示 (HTML 模板)

### 03_components - 组件示例
**深入学习各种 GUI 组件**

- **`builder_form_validation.php`** - 表单验证示例
- **`builder_progress_demo.php`** - 进度条演示
- **`builder_selection_controls.php`** - 选择控件演示 (Combobox/Radio/Checkbox)
- **`table_demo_with_sorting.php`** - 带排序的表格示例
- **`datagrid.php`** - 数据网格示例 (高级 CRUD 操作)
- **`builder_crud_datagrid.php`** - Builder 模式的 CRUD 数据网格
- **`simple_crud_datagrid.php`** - 简单 CRUD 数据网格

### 04_advanced - 高级功能示例
**学习复杂功能和模式**

- **`eventAndState.php`** - 事件处理和状态管理示例
- **`helper_shortcuts_demo.php`** - 便捷函数演示

### 05_applications - 完整应用示例
**实际应用案例**

- **`calculator.php`** - 计算器示例 (Builder API)
- **`calculator_html.php`** - 计算器示例 (HTML 模板)
- **`htmlLogin.php`** - 登录表单示例

## 🎯 学习路径

### 初学者路径
1. **开始**: `01_basics/builder_basic_button.php` - 学习基础按钮和事件
2. **对比**: `01_basics/html_basic_button.php` - 了解两种开发模式
3. **扩展**: `01_basics/html_tag_aliases.php` - 学习 HTML 模板特性
4. **布局**: `02_layouts/responseGrid.php` - 学习响应式布局

### 进阶路径  
1. **组件**: `03_components/builder_form_validation.php` - 学习表单处理
2. **交互**: `03_components/builder_selection_controls.php` - 学习选择控件
3. **数据**: `03_components/table_demo_with_sorting.php` - 学习数据展示
4. **状态**: `04_advanced/eventAndState.php` - 学习状态管理

### 专家路径
1. **CRUD**: `03_components/datagrid.php` - 学习完整的数据管理
2. **应用**: `05_applications/calculator.php` - 学习实际应用开发

## 🔧 运行示例

### 基础运行
```bash
# 运行 Builder API 示例
php example/01_basics/builder_basic_button.php

# 运行 HTML 模板示例  
php example/01_basics/html_basic_button.php
```

### 指定目录运行
```bash
# 运行所有基础示例
cd example/01_basics
php builder_basic_button.php
php html_basic_button.php
php html_tag_aliases.php
```

### 交互式运行
使用项目提供的测试脚本：
```bash
bash run_tests.sh
```

## 🏗️ 开发模式对比

### Builder API 模式
```php
// 链式调用构建界面
$app = Builder::window()
    ->title('示例')
    ->size(400, 300)
    ->contains([
        Builder::button()
            ->text('点击我')
            ->onClick(function($button) {
                echo "被点击了！\n";
            })
    ]);
```

### HTML 模板模式
```php
// 使用 HTML 语法定义界面
$handlers = [
    'handleClick' => function($button) {
        echo "被点击了！\n";
    }
];

$app = HtmlRenderer::render('template.ui.html', $handlers);
```

```html
<!-- template.ui.html -->
<window title="示例" size="400,300">
    <button onclick="handleClick">点击我</button>
</window>
```

## 🎨 核心特性演示

### HTML 标签别名
libuiBuilder 支持标准 HTML 标签别名：

| HTML 标签 | 映射组件 | 说明 |
|----------|----------|------|
| `<select>` | Combobox | 下拉选择框 |
| `<progress>` | ProgressBar | 进度条 |
| `<hr>` | Separator | 分隔线 |
| `<textarea>` | MultilineEntry | 多行文本 |
| `input type="number"` | Spinbox | 数字输入 |
| `input type="range"` | Slider | 滑动条 |
| `input type="password"` | PasswordEntry | 密码输入 |

### 布局系统
- **Grid** - 二维网格布局，支持行列定位和跨度
- **ResponsiveGrid** - 响应式网格，自动适应空间
- **Box** - 水平/垂直盒子布局
- **Group** - 分组容器，带标题的容器

### 组件类型
- **基础控件**: Button, Label, Entry, Separator
- **输入控件**: Text, Password, Number, Slider, Spinbox
- **选择控件**: Combobox, Radio, Checkbox
- **数据展示**: Table, DataGrid
- **容器组件**: Group, Tab, Box, Grid

### 状态管理
```php
$state = StateManager::instance();
$state->set('username', '');
$state->get('username');

// 监听状态变化
$state->watch('username', function($newValue) {
    echo "用户名变更为: {$newValue}\n";
});
```

## 📖 相关文档

- [HTML 渲染器文档](../docs/HTML_RENDERER.md)
- [表格组件文档](../docs/TableBuilder.md)
- [HTML 标签别名文档](../HTML_TAGS_ALIASES.md)
- [预览工具文档](../PREVIEW_TOOL.md)

## 🤝 贡献指南

如果您想添加新的示例：

1. **遵循命名规范**: `{pattern}_{feature}_{complexity}.php`
2. **添加文档注释**: 包含演示内容和功能说明
3. **放置到合适目录**: 根据复杂度选择目录
4. **测试运行**: 确保示例可以正常运行

### 示例文件结构
```php
<?php
/**
 * 功能描述 - 开发模式 模式
 * 
 * 演示内容：
 * - 要点1
 * - 要点2
 * - 要点3
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder; // 或 HtmlRenderer
use Kingbes\Libui\View\State\StateManager;

// 初始化
App::init();
$state = StateManager::instance();

// 构建界面
$app = Builder::window() // 或 HtmlRenderer::render()
    ->title('示例标题')
    // ... 其他代码
    ;

$app->show();
```

## 🐛 常见问题

### Q: 运行示例时出现错误？
A: 确保已安装所有依赖：
```bash
composer install
```

### Q: GUI 窗口无法显示？
A: 检查系统是否支持 libui 库，确保在图形界面环境下运行。

### Q: HTML 模板不生效？
A: 确保模板文件使用 `.ui.html` 扩展名，并且路径正确。

### Q: 状态管理不工作？
A: 确保正确导入 `StateManager` 类并初始化状态。

---

**祝您学习愉快！** 🎉

如有问题，请查看项目文档或在 GitHub 上提交 Issue。