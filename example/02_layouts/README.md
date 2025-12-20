# 布局示例 - 02_layouts

本目录包含各种布局相关的示例，演示 libuiBuilder 的布局系统。

## 示例文件

### Builder API 模式

- **`builder_responsive_grid.php`** - 响应式网格布局
  - 演示 12 列网格系统的比例分配
  - 展示不同比例的布局组合 (6+6, 4+4+4, 3+6+3, 2+3+4+3, 3+4+5)
  - 包含输入控件和按钮组演示
  - 使用 `append()` 方法正确设置扩展属性
  - 按钮使用 hbox 容器包装以更好地展示比例

### HTML 模板模式

- **`html_responsive_grid.php`** - 响应式网格布局 (HTML 模板版)
  - 使用 HTML 模板语法实现相同功能
  - 演示 colspan 属性控制列跨度
  - 展示事件处理器绑定

## 运行示例

```bash
# 运行 Builder API 版本
php example/02_layouts/builder_responsive_grid.php

# 运行 HTML 模板版本  
php example/02_layouts/html_responsive_grid.php
```

## 核心概念

### 12 列网格系统

libuiBuilder 使用 12 列网格系统，允许你将页面划分为 12 等份的列：

- **全宽**：使用 12 列 (`colspan="12"`)
- **半屏**：使用 6 列 (`colspan="6"`)
- **三等分**：使用 4 列 (`colspan="4"`)
- **四等分**：使用 3 列 (`colspan="3"`)
- **其他比例**：可以组合不同列数实现各种布局

### GridBuilder 使用要点

```php
// 创建网格容器
$mainGrid = Builder::grid()->padded(true);
$mainGrid->columns(12);

// 使用 append() 方法添加组件，设置扩展属性
$mainGrid->append(
    $component,           // 组件对象
    $row,                 // 行号
    $col,                 // 起始列
    $rowspan,             // 行跨度
    $colspan,             // 列跨度
    $hexpand,             // 水平扩展 (true/false)
    $halign,              // 水平对齐 ('fill', 'start', 'center', 'end')
    $vexpand,             // 垂直扩展 (true/false)
    $valign               // 垂直对齐 ('fill', 'start', 'center', 'end')
);

// 示例：3+4+5 按钮布局
$mainGrid->append($btn1, 7, 0, 1, 3, true, 'fill', false, 'center');
$mainGrid->append($btn2, 7, 3, 1, 4, true, 'fill', false, 'center');
$mainGrid->append($btn3, 7, 7, 1, 5, true, 'fill', false, 'center');
```

### 布局比例示例

| 行号 | 布局模式 | 列分配 | 用途 |
|------|----------|--------|------|
| 3 | 6+6 | 6列 + 6列 | 左右两栏布局 |
| 4 | 4+4+4 | 4列 + 4列 + 4列 | 三等分布局 |
| 5 | 3+6+3 | 3列 + 6列 + 3列 | 两侧窄，中间宽 |
| 6 | 2+3+4+3 | 2列 + 3列 + 4列 + 3列 | 复杂比例布局 |
| 7 | 3+4+5 | 3列 + 4列 + 5列 | 渐进式布局 |

### HTML 模板 Grid 布局

```html
<grid padded="true">
    <label row="0" col="0" colspan="12">全宽标题</label>
    <label row="1" col="0" colspan="6">左侧内容</label>
    <label row="1" col="6" colspan="6">右侧内容</label>
</grid>
```

## 布局特点

- **响应式**：组件根据可用空间自动调整
- **灵活**：支持任意比例组合
- **一致**：两种开发模式提供相同的布局能力
- **直观**：HTML 模板语法更接近 Web 标准

## 关键技术要点

### 扩展属性设置

- **`hexpand: true`**：组件水平扩展填充分配的空间
- **`halign: 'fill'`**：组件水平填充整个分配区域
- **`vexpand: false`**：组件不垂直扩展（默认高度）
- **`valign: 'center'`**：组件在垂直方向居中对齐

### 容器包装技巧

对于按钮等有最小宽度限制的组件，可以使用容器包装：

```php
// 为按钮创建 hbox 容器
$btnContainer = Builder::hbox()->padded(false);
$btnContainer->contains([$button]);

// 将容器添加到网格
$mainGrid->append($btnContainer, $row, $col, 1, $colspan, true, 'fill', false, 'center');
```

### 常见布局模式

1. **表单布局**：标签使用 `end` 对齐，输入框使用 `fill` 填充
2. **按钮组**：使用 `center` 对齐，均匀分布
3. **分隔线**：使用 `fill` 对齐，横跨整个宽度
4. **标题文本**：使用 `center` 对齐，突出显示

## 注意事项

- 使用 `append()` 方法而不是 `place()` 方法来设置扩展属性
- 确保列跨度总和不超过 12
- 按钮等组件可能需要容器包装来正确显示比例
- 标签组件建议使用 `start` 或 `end` 对齐而非 `fill`