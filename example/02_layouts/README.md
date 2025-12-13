# 布局示例 - 02_layouts

本目录包含各种布局相关的示例，演示 libuiBuilder 的布局系统。

## 示例文件

### Builder API 模式

- **`builder_responsive_grid.php`** - 响应式网格布局
  - 演示 12 列网格系统的比例分配
  - 展示不同比例的布局组合
  - 包含输入控件和按钮组演示

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

### ResponsiveGridBuilder (Builder API)

```php
$grid = ResponsiveGrid::create(12)
    ->col(Builder::label()->text('标题'), 12)     // 全宽
    ->col(Builder::label()->text('左侧'), 6)      // 半宽
    ->col(Builder::label()->text('右侧'), 6)      // 半宽
    ->build();
```

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