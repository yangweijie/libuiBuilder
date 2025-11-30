# libuiBuilder 可视化预览工具

## 🎯 简介

这是一个 **零依赖、纯浏览器** 的可视化预览工具，用于在不运行 PHP 代码的情况下实时预览 `.ui.html` 模板的布局效果。

**Linus 的评价**: ✅ "好品味" - 单文件解决方案，没有特殊情况，直接用浏览器。

## 🚀 快速开始

### 方式一：直接打开

```bash
# 在浏览器中打开预览工具
open tools/preview.html
```

### 方式二：本地服务器（可选）

```bash
# 使用 PHP 内置服务器
cd /path/to/libuiBuilder
php -S localhost:8080

# 然后在浏览器中访问
open http://localhost:8080/tools/preview.html
```

## 📖 使用方法

### 1. 加载文件

- **点击左侧文件选择框**，选择任意 `.ui.html` 文件
- **或拖拽文件**到预览区域

### 2. 查看布局

预览工具会自动渲染以下内容：

- ✅ Grid 布局（完整支持 row/col/rowspan/colspan/align/expand）
- ✅ Box 布局（vbox/hbox）
- ✅ 所有基础组件（button/input/label/checkbox 等）
- ✅ 状态绑定关系展示
- ✅ 事件处理器展示

### 3. 工具栏功能

| 按钮 | 功能 |
|------|------|
| 🔄 重新加载 | 重新解析当前文件 |
| 📏 显示网格 | 切换网格线显示 |
| 75%/100%/125%/150% | 缩放预览视图 |

### 4. 侧边栏信息

- **状态绑定**: 显示所有 `bind="xxx"` 属性
- **事件处理器**: 显示所有 `onclick/onchange` 等事件
- **布局信息**: 统计容器数量

## 🎨 支持的组件

### 容器组件
- ✅ `<window>` - 主窗口（含标题栏和尺寸）
- ✅ `<grid>` - 网格布局（完整支持所有属性）
- ✅ `<vbox>` - 垂直盒子
- ✅ `<hbox>` - 水平盒子

### 基础控件
- ✅ `<label>` - 文本标签
- ✅ `<input>` - 单行输入（支持 type="password"）
- ✅ `<textarea>` - 多行文本
- ✅ `<button>` - 按钮
- ✅ `<checkbox>` - 复选框
- ✅ `<radio>` - 单选框组

### 选择控件
- ✅ `<combobox>` / `<select>` - 下拉选择
- ✅ `<spinbox>` - 数字输入
- ✅ `<slider>` - 滑动条
- ✅ `<progressbar>` / `<progress>` - 进度条

### 其他
- ✅ `<separator>` / `<hr>` - 分隔线

## 📐 Grid 布局映射

libuiBuilder Grid 属性完美映射到 CSS Grid:

| libuiBuilder | CSS Grid | 说明 |
|--------------|----------|------|
| `row="0"` | `grid-row-start: 1` | 行索引（从0开始 → 从1开始） |
| `col="1"` | `grid-column-start: 2` | 列索引 |
| `rowspan="2"` | `grid-row-end: span 2` | 跨行数 |
| `colspan="3"` | `grid-column-end: span 3` | 跨列数 |
| `align="center"` | `justify-self: center; align-self: center` | 对齐方式 |
| `expand="horizontal"` | `width: 100%` | 水平扩展 |
| `padded="true"` | `gap: 10px` | 网格间距 |

### 对齐方式说明

```html
<!-- 单值：同时应用水平和垂直 -->
<button align="center">居中</button>

<!-- 双值：水平,垂直 -->
<label align="end,center">右对齐且垂直居中</label>
```

支持的值：
- `fill` - 填充
- `start` - 起始对齐
- `center` - 居中
- `end` - 结束对齐

### 扩展控制说明

```html
<!-- true: 双向扩展 -->
<input expand="true"/>

<!-- horizontal: 仅水平扩展 -->
<input expand="horizontal"/>

<!-- vertical: 仅垂直扩展 -->
<textarea expand="vertical"/>
```

## 🔍 示例预览

### 预览登录表单

```bash
# 在预览工具中加载
example/views/login.ui.html
```

效果：
- 2列网格布局
- 标签右对齐
- 输入框水平扩展
- 按钮居中显示

### 预览计算器

```bash
# 在预览工具中加载
example/views/calculator.ui.html
```

效果：
- 4×5 按钮网格
- 显示屏跨列
- 所有按钮居中

## 🎯 核心特性

### 1. 零依赖

- ✅ 单个 HTML 文件
- ✅ 不需要 Node.js
- ✅ 不需要构建工具
- ✅ 直接用浏览器打开

### 2. 实时预览

- ✅ 文件选择后立即渲染
- ✅ 支持拖拽加载
- ✅ 重新加载按钮

### 3. 完整信息

- ✅ 状态绑定可视化（绿色圆点）
- ✅ 事件处理器列表
- ✅ 布局统计信息

### 4. 开发友好

- ✅ 缩放控制（75%-150%）
- ✅ 网格线显示
- ✅ 清晰的错误提示

## 🚫 已知限制

这是一个 **静态预览工具**，以下功能不支持：

- ❌ **不运行 PHP 代码** - 无法执行事件处理器
- ❌ **不运行状态管理** - 只显示绑定关系，不运行 StateManager
- ❌ **不支持模板复用** - `<template>` 和 `<use>` 标签暂未实现
- ❌ **不支持 Tab 组件** - TabBuilder 需要特殊处理
- ❌ **不支持 Table/Canvas** - 复杂组件暂未实现

**设计理念**: 
> "这个工具的目的是预览布局，不是运行应用。如果你需要测试逻辑，直接运行 PHP。" - Linus

## 📊 技术实现

### 核心架构

```javascript
// 1. DOMParser 解析 .ui.html
const parser = new DOMParser();
const doc = parser.parseFromString(htmlContent, 'text/html');

// 2. 递归渲染每个元素
function renderElement(element) {
    switch(element.tagName) {
        case 'grid': return renderGrid(element);
        case 'button': return renderButton(element);
        // ...
    }
}

// 3. CSS Grid 映射
grid.style.gridTemplateRows = `repeat(${maxRow}, auto)`;
item.style.gridRowStart = row + 1;
item.style.gridColumnStart = col + 1;
```

### CSS Grid 策略

**Linus 的洞察**: "不需要复杂的计算，直接让 CSS Grid 做它该做的事。"

```css
.ui-grid {
    display: grid;
    grid-template-rows: repeat(auto, auto);  /* 自适应行高 */
    grid-template-columns: repeat(auto, auto);  /* 自适应列宽 */
}

.ui-grid.padded {
    gap: 10px;  /* 简单！不需要复杂的 padding 计算 */
}
```

## 🛠️ 开发调试

### 查看解析结果

在浏览器中打开预览工具后，按 F12 打开开发者工具：

```javascript
// 查看当前状态绑定
console.log(stateBindings);

// 查看当前事件处理器
console.log(eventHandlers);

// 查看 Grid 布局
document.querySelectorAll('.ui-grid').forEach(grid => {
    console.log('Grid:', grid.style.gridTemplateRows, grid.style.gridTemplateColumns);
});
```

### 修改样式

所有样式都在 `<style>` 标签内，直接编辑即可：

```css
/* 修改按钮样式 */
.ui-button {
    background: #e74c3c;  /* 改成红色 */
}

/* 修改 Grid 间距 */
.ui-grid.padded {
    gap: 20px;  /* 增大间距 */
}
```

## 📝 最佳实践

### 1. 开发工作流

```text
1. 在 example/views/ 创建 .ui.html 文件
2. 用预览工具查看布局
3. 调整 Grid 属性（row/col/align/expand）
4. 保存后重新加载预览
5. 满意后编写 PHP 事件处理器
6. 运行 php example/xxx.php 测试逻辑
```

### 2. Grid 布局调试

```html
<!-- 1. 先用预览工具看整体布局 -->
<grid padded="true">
  <label row="0" col="0">字段1:</label>
  <input row="0" col="1" expand="horizontal"/>
</grid>

<!-- 2. 点击 "显示网格" 查看边界 -->
<!-- 3. 调整 align 和 expand 直到满意 -->
```

### 3. 状态绑定检查

```html
<!-- 在预览工具中，绑定的组件会显示绿色圆点 -->
<input id="username" bind="username"/>

<!-- 侧边栏会显示：username → username -->
```

## 🎓 学习资源

- [libuiBuilder 文档](../README.md)
- [Grid 布局指南](../IFLOW.md#grid-布局)
- [HTML 模板规范](../IFLOW.md#html-模板规范)

## 🤝 贡献

这个工具遵循 Linus 的设计哲学：

1. ✅ **简单优于复杂** - 单文件解决方案
2. ✅ **实用优于理论** - 只做预览需要的功能
3. ✅ **消除特殊情况** - 统一的组件渲染逻辑

如果你想添加新功能：

- 确保不破坏现有功能
- 保持单文件架构
- 遵循现有代码风格

## 📄 许可证

MIT License - 与 libuiBuilder 主项目相同

---

**记住**: 这是个预览工具，不是运行时。如果你发现自己在尝试"让它运行 PHP 代码"，你走错方向了。

**Linus 会说**: "Theory and practice sometimes clash. Theory loses. Every single time."
