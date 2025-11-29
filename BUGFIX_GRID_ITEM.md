# Bug 修复：Grid 布局属性未生效

## 问题描述

使用 HtmlRenderer 渲染的 HTML 模板运行时会触发段错误（Segmentation Fault），而直接使用 Builder 模式的代码可以正常运行。

## 根本原因

**【Linus 评价】** 🔴 **垃圾代码** - 典型的"过早优化"错误

在 `GridBuilder.php` 的第 62 行，`place()` 方法立即调用 `$item->getConfig()` 并将配置数组存储到 `$this->gridItems`：

```php
// 修复前的错误代码
public function place(ComponentBuilder $component, int $row, int $col,
                      int $rowSpan = 1, int $colSpan = 1): GridItemBuilder
{
    $item = new GridItemBuilder($component, $col, $row, $colSpan, $rowSpan);
    $this->gridItems[] = $item->getConfig();  // ❌ 立即复制配置
    return $item;
}
```

这导致以下问题：

1. **配置被过早复制**：配置数组在 `place()` 调用时就被复制到 `$gridItems`
2. **后续修改无效**：返回的 `GridItemBuilder` 对象的 `align()` 和 `expand()` 方法修改的是对象内部配置，但不会影响已复制到数组中的配置
3. **HtmlRenderer 失效**：HtmlRenderer 在调用 `place()` 后继续调用 `align()` 和 `expand()`，但这些修改完全无效！

### 具体表现

```php
// HtmlRenderer 中的代码（修复前无效）
$gridItem = $builder->place($childBuilder, $row, $col, $rowspan, $colspan);

// ❌ 这些调用完全无效，因为配置已经被复制到数组中了
$gridItem->align('end', 'center');
$gridItem->expand(true, false);
```

## 修复方案

**【Linus式方案】** 存储对象引用而不是配置数组，在 `buildChildren()` 时再获取最终配置。

### 修改点 1：存储对象而不是配置

```php
// 修复后的正确代码
public function place(ComponentBuilder $component, int $row, int $col,
                      int $rowSpan = 1, int $colSpan = 1): GridItemBuilder
{
    $item = new GridItemBuilder($component, $col, $row, $colSpan, $rowSpan);
    $this->gridItems[] = $item;  // ✅ 存储对象引用
    return $item;
}
```

### 修改点 2：构建时获取最终配置

```php
protected function buildChildren(): void
{
    foreach ($this->gridItems as $item) {
        // ✅ 现在从对象获取最终配置（包含所有链式调用的修改）
        $config = $item->getConfig();
        $childHandle = $config['component']->build();

        Grid::append(
            $this->handle,
            $childHandle,
            $config['left'],
            $config['top'],
            $config['xspan'],
            $config['yspan'],
            $config['hexpand'] ? 1 : 0,
            $config['halign']->value,
            $config['vexpand'] ? 1 : 0,
            $config['valign']
        );
    }
}
```

### 修改点 3：修复依赖 `array_column` 的代码

因为 `$gridItems` 现在存储的是对象而不是数组，需要修改使用 `array_column` 的地方：

```php
// row() 方法
$currentRow = count($this->gridItems) > 0
    ? max(array_map(fn($item) => $item->getConfig()['top'], $this->gridItems)) + 1
    : 0;

// append() 方法
$nextRow = count($this->gridItems) > 0
    ? max(array_map(fn($item) => $item->getConfig()['top'], $this->gridItems)) + 1
    : 0;
```

## 修复效果

### 修复前
```bash
$ php example/htmlLogin.php
zsh: segmentation fault  php example/htmlLogin.php
```

### 修复后
```bash
$ php example/htmlLogin.php
# 正常运行，窗口正确显示

$ php example/htmlFull.php
# 正常运行，所有控件正确布局

$ ./vendor/bin/pest
Tests:    25 passed (38 assertions)
Duration: 0.26s
```

## 为什么会导致段错误？

当 Grid 布局属性（align、expand）未正确设置时，传递给 libui 底层的参数可能是未初始化的值（如默认的 Fill），在某些情况下可能导致：

1. **内存访问错误**：libui 的 C 代码可能期望特定的对齐值，传入错误的值可能导致越界访问
2. **FFI 调用失败**：PHP FFI 层传递了错误的枚举值给 C 函数
3. **组件状态不一致**：某些组件的布局参数相互冲突

## 经验教训

1. **不要过早优化**：立即复制配置看起来"高效"，但破坏了链式调用的语义
2. **保持引用语义**：Builder 模式依赖对象的可变性和链式调用
3. **延迟求值**：配置应该在真正需要时（`buildChildren()`）才获取
4. **简单即是美**：存储对象引用比存储配置数组更简单、更正确

## 相关文件

- `src/Components/GridBuilder.php` - Grid 构建器
- `src/Components/GridItemBuilder.php` - Grid 项配置
- `src/HtmlRenderer.php` - HTML 渲染器
- `tests/HtmlRendererTest.php` - 测试用例
- `example/htmlLogin.php` - 登录示例
- `example/htmlFull.php` - 完整控件演示
