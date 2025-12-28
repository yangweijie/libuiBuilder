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
- 📋 表格组件 - 功能丰富的表格组件，支持多种列类型
- 🗂️ 表单模板 - 快速创建表单的模板系统
- 🎭 标签页组件 - 支持多标签页界面设计

## 技术栈

### 后端技术
- **语言**: PHP 8+
- **GUI 框架**: kingbes/libui (基于 libui)
- **测试框架**: Pest
- **依赖管理**: Composer
- **扩展依赖**: ext-ffi, ext-dom, ext-libxml

### 前端工具
- **基础技术**: HTML/CSS/JavaScript
- **样式框架**: libui-ng-complete.css (跨平台样式库)
- **浏览器自动化**: Puppeteer (for end-to-end testing)
- **可视化工具**: 原生 JavaScript 实现的设计器和预览工具

## 项目结构

```
libuiBuilder/
├── composer.json           # PHP 项目依赖配置
├── package.json            # 前端工具依赖配置
├── pest.php               # Pest 测试配置
├── run_tests.sh           # Unix/Linux 测试运行脚本
├── run_tests.ps1          # PowerShell 测试运行脚本
├── LICENSE                # MIT 许可证文件
├── README.md              # 项目说明文档
├── IFLOW.md               # iFlow CLI 上下文文件
├── src/                   # 核心源代码
│   ├── Builder.php        # 视图构建器入口
│   ├── HtmlRenderer.php   # HTML 模板渲染器
│   ├── ComponentBuilder.php # 组件构建器基类 (移至 Validation)
│   ├── ResponsiveGridBuilder.php # 响应式网格布局
│   ├── helper.php         # 便捷函数库
│   ├── Builder/           # 构建器扩展
│   │   └── TabBuilder.php
│   ├── Components/        # GUI 组件实现
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
│   │   ├── TabBuilder.php
│   │   └── DrawContext.php
│   ├── Data/              # 数据处理组件
│   ├── State/             # 状态管理
│   │   ├── StateManager.php
│   │   └── ComponentRef.php
│   ├── Templates/         # 内置模板
│   │   ├── FormTemplate.php
│   │   └── ResponsiveGrid.php
│   └── Validation/        # 表单验证
│       └── ComponentBuilder.php # 组件构建器基类
├── example/               # 示例代码（按复杂度分类）
│   ├── README.md          # 示例说明文档
│   ├── libui.png          # 示例截图
│   ├── 01_basics/         # 基础示例
│   │   ├── simple.php
│   │   └── htmlLogin.php
│   ├── 02_layouts/        # 布局示例
│   │   ├── calculator.php
│   │   ├── calculator_html.php
│   │   ├── calculator_html_simple.php
│   │   └── responseGrid.php
│   ├── 03_components/     # 组件示例
│   │   ├── htmlFull.php
│   │   ├── eventAndState.php
│   │   ├── builder_helpers_demo.php
│   │   └── helper_shortcuts_demo.php
│   ├── 04_advanced/       # 高级示例
│   │   ├── simple_table_demo.php
│   │   ├── table_demo.php
│   │   ├── complex_table_demo.php
│   │   ├── dynamic_table_demo.php
│   │   ├── working_table_demo.php
│   │   ├── form_table.php
│   │   ├── form_table_builder.php
│   │   └── form_table_builder_html.php
│   ├── 05_applications/   # 完整应用示例
│   │   ├── full.php
│   │   └── standard_html_demo.php
│   └── views/             # HTML 模板文件
├── tools/                 # 开发工具
│   ├── README.md          # 工具说明文档
│   ├── QUICKSTART.md      # 快速开始指南
│   ├── designer.html      # 可视化设计器主页面
│   ├── designer.css        # 设计器样式
│   ├── designer.js         # 设计器逻辑
│   ├── libui-ng-complete.css # 跨平台样式库
│   ├── preview.html       # 预览工具
│   └── modules/           # 工具模块
├── tests/                 # 测试文件
│   ├── README.md          # 测试说明文档
│   ├── Pest.php           # Pest 测试配置
│   ├── BasicTest.php
│   ├── BuilderComponentsTest.php
│   ├── BuilderHelperTest.php
│   ├── ComponentRefTest.php
│   ├── HelperBuilderFunctionsTest.php
│   ├── HelperFunctionsTest.php
│   ├── HtmlRendererBasicTest.php
│   ├── HtmlRendererExtendedTest.php
│   ├── StateHelperTest.php
│   ├── StateManagerBasicTest.php
│   ├── TableBuilderTest.php
│   └── Integration/       # 集成测试
├── docs/                  # 文档
│   ├── HTML_RENDERER.md   # HTML渲染器文档
│   └── TableBuilder.md    # 表格组件文档
├── coverage-report/       # 测试覆盖率报告
├── logs/                  # 日志目录
└── vendor/                # Composer 依赖
```

## 快速开始

### 环境要求

- PHP 8.0 或更高版本
- Composer
- 扩展：ext-ffi, ext-dom, ext-libxml
- 现代浏览器（用于可视化工具）

### 安装依赖

```bash
# 安装 PHP 依赖
composer install

# 安装前端工具（可选，用于开发工具）
npm install
```

### 运行第一个示例

```bash
# 基础示例
php example/01_basics/simple.php

# HTML 模板示例
php example/01_basics/htmlLogin.php

# 计算器示例
php example/02_layouts/calculator.php
```

## 开发工具

### 可视化设计器

基于 Web 的可视化界面设计器，提供拖拽式组件布局功能：

**启动方式：**
```bash
# 在浏览器中打开设计器
open tools/designer.html
# 或者
start tools/designer.html  # Windows
```

**主要功能：**
- 🎨 拖拽式组件布局
- 👀 实时预览
- ⚙️ 属性编辑面板
- 📋 代码生成（HTML 格式）
- 🎭 平台样式切换
- 📐 Grid 布局支持
- 🗂️ 组件树管理

**使用步骤：**
1. 打开 `tools/designer.html`
2. 从左侧组件面板拖拽组件到设计区域
3. 点击组件查看和编辑属性
4. 实时预览界面效果
5. 生成符合规范的 HTML 代码

### 预览工具

用于预览 `.ui.html` 模板文件的独立工具：

```bash
# 打开预览工具
open tools/preview.html
```

**功能特性：**
- 📁 文件选择器
- 🔄 实时预览
- 📱 响应式支持
- 🎨 样式主题切换

## 测试

### 运行测试

```bash
# 运行所有测试
./vendor/bin/pest

# 运行特定测试文件
./vendor/bin/pest tests/BasicTest.php
./vendor/bin/pest tests/HtmlRendererBasicTest.php

# 生成覆盖率报告
./vendor/bin/pest --coverage

# 生成 HTML 覆盖率报告
./vendor/bin/pest --coverage --coverage-html=coverage-report
```

### 测试脚本

**Unix/Linux/macOS:**
```bash
# 交互式测试运行
bash run_tests.sh

# 运行特定类型测试
bash run_tests.sh 3  # 基础测试
bash run_tests.sh 4  # StateManager 测试
bash run_tests.sh 5  # HtmlRenderer 测试
```

**Windows PowerShell:**
```powershell
# 交互式测试运行
.\run_tests.ps1

# 运行特定类型测试
.\run_tests.ps1 3  # 基础测试
.\run_tests.ps1 4  # StateManager 测试
.\run_tests.ps1 5  # HtmlRenderer 测试
```

## 示例指南

### 学习路径

项目示例按难度分级，建议按以下顺序学习：

#### 1️⃣ 基础示例 (01_basics/)
- `simple.php` - Builder API 基础用法
- `htmlLogin.php` - HTML 模板登录界面

#### 2️⃣ 布局示例 (02_layouts/)
- `calculator.php` - Builder API 计算器
- `calculator_html.php` - HTML 模板计算器
- `calculator_html_simple.php` - 简化版计算器
- `responseGrid.php` - 响应式网格布局

#### 3️⃣ 组件示例 (03_components/)
- `htmlFull.php` - HTML 模板完整功能
- `eventAndState.php` - 事件和状态管理
- `builder_helpers_demo.php` - 构建器助手演示
- `helper_shortcuts_demo.php` - 便捷函数演示

#### 4️⃣ 高级示例 (04_advanced/)
- 表格系列示例（simple、complex、dynamic、working）
- 表单表格示例（form_table 系列）

#### 5️⃣ 应用示例 (05_applications/)
- `full.php` - 完整功能演示
- `standard_html_demo.php` - 标准 HTML 演示

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

### Grid 布局系统

精确的二维布局系统，支持：
- **位置定位**：`row`, `col`（从 0 开始）
- **跨度控制**：`rowspan`, `colspan`
- **对齐方式**：`align` (`fill`, `start`, `center`, `end`)
- **扩展控制**：`expand` (`true`, `horizontal`, `vertical`)

### 响应式网格 (ResponsiveGridBuilder)

自动适应可用空间的网格布局：

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

### 便捷函数

项目提供大量便捷函数来简化开发：

```php
// 状态管理
state();                    // 获取状态管理器
state('key', 'value');     // 设置状态值
state('key');              // 获取状态值
watch('key', $callback);   // 监听状态变化

// 容器组件
window(); vbox(); hbox(); grid(); tab();

// 基础控件
button(); label(); entry(); checkbox(); combobox();
textarea(); spinbox(); slider(); radio();

// 表单辅助
input('用户名', 'username', 'text', '请输入用户名');
select('角色', 'role', ['管理员', '用户'], 'combobox');
```

## 支持的组件

### 容器组件
- `WindowBuilder` - 主窗口
- `BoxBuilder` - 水平/垂直盒子
- `GridBuilder` - 网格布局
- `TabBuilder` - 标签页
- `ResponsiveGridBuilder` - 响应式网格布局
- `GroupBuilder` - 分组容器（带标题）

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

### 高级组件
- `TableBuilder` - 表格（支持多种列类型）
- `DataGridBuilder` - 数据网格
- `CanvasBuilder` - 画布
- `MenuBuilder` - 菜单
- `SeparatorBuilder` - 分隔符

## 表格组件详解

### 功能特性
- **多种列类型**：text、image、checkbox、progress、button、imageText
- **数据管理**：可视化编辑，支持增删行
- **按钮自定义**：支持 "文本:值" 格式
- **实时预览**：属性修改即时反映
- **HTML 生成**：生成语义化表格代码

### 使用示例

```html
<table columns="姓名,状态,进度,操作" columnTypes="text,checkbox,progress,button">
  <thead>
    <tr>
      <th>姓名</th>
      <th>状态</th>
      <th>进度</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>任务A</td>
      <td><input type="checkbox" checked></td>
      <td><progress value="75" max="100"></progress>
      <td><button value="complete">完成</button></td>
    </tr>
  </tbody>
</table>
```

## 开发约定

### 代码风格
- 遵循 PSR-4 自动加载规范
- 使用驼峰命名法（camelCase）
- 类名使用 PascalCase
- 私有属性使用下划线前缀

### 组件开发规范
1. 继承自 `ComponentBuilder` 基类（在 `Validation` 命名空间）
2. 实现链式调用方法
3. 提供便捷的工厂方法
4. 支持事件绑定和数据绑定

### HTML 模板规范
1. 使用 `.ui.html` 扩展名
2. 根元素必须是 `<window>`
3. 支持 Grid 布局属性
4. 支持事件和数据绑定属性

## 最佳实践

1. **优先使用 HTML 模板** - 更直观、易维护
2. **使用 Grid 布局** - 避免深层嵌套的 Box
3. **利用响应式网格** - 动态布局使用 ResponsiveGridBuilder
4. **分离事件处理** - 使用专门的处理器类
5. **合理组织项目结构** - 分离模板、处理器和状态管理
6. **使用便捷函数** - 提高开发效率
7. **利用可视化设计器** - 快速原型设计

## 调试技巧

1. 使用 `StateManager::dump()` 查看状态
2. 通过 `ComponentRef` 访问组件实例
3. 查看示例代码学习最佳实践
4. 运行测试确保功能正常
5. 使用可视化设计器预览布局
6. 检查浏览器控制台输出（前端工具）

## 常见问题

1. **扩展依赖**：确保安装了 ext-ffi, ext-dom, ext-libxml
2. **模板扩展名**：HTML 模板必须使用 `.ui.html`
3. **事件处理**：事件处理器必须在渲染时传入
4. **Grid 索引**：行列索引从 0 开始
5. **helper 函数**：使用前确保已加载 src/helper.php

## 文档资源

- [HTML 渲染器文档](docs/HTML_RENDERER.md)
- [表格组件文档](docs/TableBuilder.md)
- [工具快速开始](tools/QUICKSTART.md)
- [工具使用说明](tools/README.md)

## 贡献指南

1. Fork 项目
2. 创建功能分支
3. 编写测试
4. 确保测试通过
5. 提交 Pull Request

## 许可证

MIT License
