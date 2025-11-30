# 可视化预览工具快速上手

## 🎬 5分钟快速体验

### 步骤 1: 打开预览工具

```bash
cd /path/to/libuiBuilder
open tools/preview.html
```

或者直接在浏览器中打开该文件。

### 步骤 2: 加载示例文件

有两种方式加载文件：

**方式一：点击文件选择器**

1. 点击左侧 "选择 .ui.html 文件" 按钮
2. 导航到 `example/views/`
3. 选择任意 `.ui.html` 文件（推荐从 `login.ui.html` 开始）

**方式二：拖拽文件**

直接将 `.ui.html` 文件拖拽到预览区域。

### 步骤 3: 查看效果

加载文件后，你会看到：

- 🖼️ **中央预览区**: 渲染后的界面效果
- 📊 **左侧面板**: 
  - 状态绑定列表
  - 事件处理器列表
  - 布局统计信息

### 步骤 4: 使用工具栏

| 功能 | 说明 |
|------|------|
| 🔄 重新加载 | 修改文件后重新解析 |
| 📏 显示网格 | 显示/隐藏 Grid 边界线 |
| 75%-150% | 调整预览缩放比例 |

## 📝 实战示例

### 示例 1: 预览登录表单

```bash
# 加载文件
example/views/login.ui.html
```

**你会看到**:
- 2×4 Grid 布局
- 标签右对齐（`align="end,center"`）
- 输入框水平扩展（`expand="horizontal"`）
- 状态绑定: `username` 和 `password`
- 事件处理器: `handleLogin`、`handleReset`

**实验**: 点击 "显示网格" 查看 Grid 布局边界。

### 示例 2: 预览计算器

```bash
# 加载文件
example/views/calculator.ui.html
```

**你会看到**:
- 4×5 按钮网格
- 显示屏跨4列（`colspan="4"`）
- 所有按钮居中对齐（`align="center"`）
- 按钮扩展填充（`expand="true"`）

**实验**: 
1. 点击 "显示网格" 查看网格结构
2. 调整缩放到 125% 查看细节

### 示例 3: 预览完整控件

```bash
# 加载文件
example/views/full.ui.html
```

**你会看到**:
- 混合使用 Grid 和 Box 布局
- 多种控件类型（input/checkbox/radio/combobox/slider/progressbar）
- 模板复用（`<template>` 标签）

## 🎯 常见使用场景

### 场景 1: 调试 Grid 布局

```html
<!-- 你的 .ui.html -->
<grid padded="true">
  <label row="0" col="0" align="end,center">字段1:</label>
  <input row="0" col="1" expand="horizontal"/>
  
  <label row="1" col="0" align="end,center">字段2:</label>
  <input row="1" col="1" expand="horizontal"/>
</grid>
```

**使用预览工具**:
1. 加载文件
2. 点击 "显示网格" 查看布局
3. 检查对齐方式是否正确
4. 检查扩展属性是否生效

### 场景 2: 检查状态绑定

```html
<input id="username" bind="username"/>
<input id="password" bind="password"/>
```

**使用预览工具**:
1. 加载文件
2. 查看左侧 "状态绑定" 面板
3. 绿色圆点指示器显示已绑定的组件
4. 确认所有需要的绑定都已设置

### 场景 3: 验证响应式布局

```html
<grid padded="true">
  <!-- 跨列布局 -->
  <label row="0" col="0" colspan="3" align="center">标题</label>
  
  <!-- 不同宽度的列 -->
  <input row="1" col="0" expand="horizontal"/>
  <button row="1" col="1">按钮1</button>
  <button row="1" col="2">按钮2</button>
</grid>
```

**使用预览工具**:
1. 加载文件
2. 使用缩放功能（75%-150%）查看不同尺寸下的效果
3. 点击 "显示网格" 检查跨列是否正确

## 💡 最佳实践

### ✅ 推荐的工作流

```text
1️⃣ 在 VS Code 中编写 .ui.html
   ↓
2️⃣ 在预览工具中查看布局效果
   ↓
3️⃣ 调整 Grid 属性（row/col/align/expand）
   ↓
4️⃣ 保存文件，点击 "重新加载"
   ↓
5️⃣ 满意后编写 PHP 事件处理器
   ↓
6️⃣ 运行 php example/xxx.php 测试逻辑
```

### ❌ 常见误区

**误区 1**: "预览工具应该能运行我的 PHP 代码"
- ❌ 错误：预览工具只负责布局预览
- ✅ 正确：使用 `php example/xxx.php` 测试逻辑

**误区 2**: "我的事件处理器为什么不工作？"
- ❌ 错误：预览工具不执行事件处理器
- ✅ 正确：查看左侧面板确认事件已注册，然后在 PHP 中测试

**误区 3**: "状态绑定怎么没有效果？"
- ❌ 错误：预览工具不运行 StateManager
- ✅ 正确：查看绿色圆点确认绑定关系，然后在 PHP 中测试

## 🐛 故障排除

### 问题 1: 文件加载失败

**症状**: 选择文件后显示 "解析错误"

**原因**: 
- XML 格式错误（标签未闭合）
- 缺少 `<window>` 根元素

**解决**:
```html
<!-- ❌ 错误 -->
<ui>
  <grid>...</grid>
</ui>

<!-- ✅ 正确 -->
<ui>
  <window title="我的窗口" size="400,300">
    <grid>...</grid>
  </window>
</ui>
```

### 问题 2: Grid 布局混乱

**症状**: 组件位置不对

**原因**:
- row/col 索引从 0 开始
- 缺少 rowspan/colspan 导致重叠

**解决**:
```html
<!-- ❌ 错误 -->
<grid>
  <label row="1" col="1">标签</label>
  <input row="1" col="1"><!-- 重叠了！ -->
</grid>

<!-- ✅ 正确 -->
<grid>
  <label row="0" col="0">标签</label>
  <input row="0" col="1">
</grid>
```

### 问题 3: 组件不显示

**症状**: 某些组件没有渲染

**原因**:
- 使用了不支持的标签
- 标签名拼写错误

**支持的标签**:
- ✅ `<window>`, `<grid>`, `<vbox>`, `<hbox>`
- ✅ `<label>`, `<input>`, `<button>`, `<checkbox>`, `<radio>`
- ✅ `<combobox>`, `<select>`, `<spinbox>`, `<slider>`, `<progressbar>`, `<progress>`
- ✅ `<separator>`, `<hr>`, `<textarea>`

## 🎓 进阶技巧

### 技巧 1: 使用浏览器开发者工具

按 F12 打开开发者工具，然后：

```javascript
// 查看当前状态绑定
console.log(stateBindings);

// 查看 Grid 布局
document.querySelectorAll('.ui-grid').forEach(grid => {
    console.log(grid.style.gridTemplateRows);
    console.log(grid.style.gridTemplateColumns);
});

// 查看某个组件的样式
const input = document.querySelector('#username');
console.log(getComputedStyle(input));
```

### 技巧 2: 自定义样式

直接在浏览器中编辑 `preview.html` 的 `<style>` 标签：

```css
/* 修改按钮颜色 */
.ui-button {
    background: #e74c3c;  /* 红色 */
}

/* 增大 Grid 间距 */
.ui-grid.padded {
    gap: 20px;
}

/* 修改输入框样式 */
.ui-input {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
```

### 技巧 3: 对比不同布局

在两个浏览器窗口中分别打开预览工具：

- 窗口1: 加载旧版本布局
- 窗口2: 加载新版本布局
- 并排对比差异

## 📞 获取帮助

- [预览工具文档](README.md)
- [libuiBuilder 主文档](../README.md)
- [Grid 布局详解](../IFLOW.md#grid-布局)

---

**记住**: 这是个预览工具，用于快速迭代布局设计。如果你需要测试逻辑，请运行 PHP 代码。

> "好工具应该做好一件事，并且只做这一件事。" - Linus Torvalds
