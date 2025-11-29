# Bug 修复：HtmlRenderer Grid rowspan/colspan 默认值错误

## 问题描述

运行 `htmlLogin.php` 和 `htmlFull.php` 时触发段错误（segmentation fault）：

```
*** Terminating app due to uncaught exception 'NSInvalidArgumentException', 
reason: 'NSLayoutConstraint for <gridView>: Constraint items must each be a view or layout guide.'
```

## 根本原因

**PHP `DOMElement::getAttribute()` 行为陷阱**：

```php
// 当属性不存在时，getAttribute() 返回空字符串 ""，而不是 null
$element->getAttribute('notexist');  // 返回 "" 而不是 null
```

因此在 `HtmlRenderer.php` 第 296-297 行：

```php
// 错误代码
$rowspan = (int)($child->getAttribute('rowspan') ?? 1);  // 永远得到 0
$colspan = (int)($child->getAttribute('colspan') ?? 1);  // 永远得到 0
```

由于 `??` 操作符只处理 `null`，当属性不存在时：
1. `getAttribute('rowspan')` 返回 `""`
2. `"" ?? 1` 得到 `""`（因为 `""` 不是 null）
3. `(int)""` 得到 `0`

这导致所有没有显式设置 `rowspan`/`colspan` 的 Grid 子元素都使用 0 作为跨度值，触发 macOS 自动布局约束错误。

## 解决方案

使用 `?:` 操作符替代 `??`，因为 `?:` 会将空字符串视为 falsy：

```php
// 正确代码
$rowspan = (int)($child->getAttribute('rowspan') ?: 1);  // 空字符串时得到 1
$colspan = (int)($child->getAttribute('colspan') ?: 1);  // 空字符串时得到 1
```

## 修复位置

文件：`src/HtmlRenderer.php`  
行号：293-297

```php
// 读取网格布局属性
$row = (int)($child->getAttribute('row') ?: 0);
$col = (int)($child->getAttribute('col') ?: 0);
$rowspan = (int)($child->getAttribute('rowspan') ?: 1);  // 修复
$colspan = (int)($child->getAttribute('colspan') ?: 1);  // 修复
```

## 验证

```bash
# 两个文件现在都能正常运行
php example/htmlLogin.php
php example/htmlFull.php

# 测试通过
vendor/bin/pest tests/ --filter=Grid
```

## 经验教训

**PHP 陷阱**：`DOMElement::getAttribute()` 对不存在的属性返回 `""`，不是 `null`

**最佳实践**：
- 处理 DOM 属性时使用 `?:` 而不是 `??`
- 或者使用 `|| 'default'`
- 或者显式检查：`$attr = $element->getAttribute('x'); $value = ($attr !== '') ? $attr : 'default';`
