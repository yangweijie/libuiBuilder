# 计算器示例

本示例展示了如何使用 libuiBuilder 创建一个功能完整的计算器应用，支持两种实现方式：

## 功能特性

- **基础运算**: 加法 (+)、减法 (-)、乘法 (×)、除法 (÷)
- **数字输入**: 0-9 数字和小数点支持
- **清除功能**: 
  - `C`: 清除所有状态
  - `CE`: 清除当前输入
  - `⌫`: 退格删除
- **连续运算**: 支持链式计算
- **状态管理**: 使用 StateManager 管理计算器状态
- **响应式界面**: 基于 Grid 布局的响应式设计

## 文件结构

```
example/
├── calculator.php              # Builder 模式实现
├── calculator_html.php         # HTML 模板模式实现
├── views/
│   └── calculator.ui.html      # HTML 模板文件
└── README_CALCULATOR.md        # 说明文档

根目录/
└── run_calculator.php          # 统一运行脚本
```

## 运行方式

### 1. 使用统一运行脚本 (推荐)

```bash
# 运行 Builder 模式版本
php run_calculator.php builder

# 运行 HTML 模板版本
php run_calculator.php html

# 同时尝试两种版本
php run_calculator.php both
```

### 2. 直接运行单个文件

```bash
# Builder 模式
php example/calculator.php

# HTML 模板
php example/calculator_html.php
```

## 实现对比

### Builder 模式 (`calculator.php`)

**优点**:
- 完全使用 PHP 代码构建界面
- 类型安全，IDE 支持更好
- 逻辑和界面构建紧密集成
- 适合复杂交互逻辑

**特点**:
- 使用 `Builder::grid()` 创建网格布局
- 每个按钮都有独立的 `onClick` 处理器
- 状态管理直接嵌入到按钮逻辑中

### HTML 模板模式 (`calculator_html.php`)

**优点**:
- 界面结构清晰，易于维护
- 设计和逻辑分离
- 支持可视化设计
- 适合界面复杂的应用

**特点**:
- 使用 HTML 描述界面结构
- 通过 `data-*` 属性传递参数
- 事件处理器通过 `onclick` 属性绑定

## 状态管理

计算器使用以下状态变量：

```php
$stateManager->set('display', '0');              // 当前显示值
$stateManager->set('previousValue', null);        // 前一个操作数
$stateManager->set('operation', null);            // 当前运算符
$stateManager->set('waitingForNewValue', false);  // 是否等待新输入
```

## 核心算法

### 运算逻辑

```php
function calculate($a, $b, $operation) {
    switch ($operation) {
        case '+': return $a + $b;
        case '-': return $a - $b;
        case '*': return $a * $b;
        case '/': return $b != 0 ? $a / $b : 0;
        default: return $b;
    }
}
```

### 数字输入处理

```php
if ($waitingForNewValue || $display === '0') {
    $stateManager->set('display', $number);
    $stateManager->set('waitingForNewValue', false);
} else {
    $stateManager->set('display', $display . $number);
}
```

### 运算符处理

```php
if ($previousValue !== null && $operation !== null) {
    // 先计算前一个运算的结果
    $result = calculate($previousValue, $currentValue, $operation);
    // 然后设置新的运算符
    $stateManager->update([
        'display' => formatNumber($result),
        'previousValue' => $result,
        'operation' => $newOperation,
        'waitingForNewValue' => true
    ]);
} else {
    // 直接设置运算符
    $stateManager->update([
        'previousValue' => $currentValue,
        'operation' => $newOperation,
        'waitingForNewValue' => true
    ]);
}
```

## Grid 布局详解

计算器使用 5×4 的网格布局：

```
┌─────┬─────┬─────┬─────┐
│  C  │  CE │ ⌫   │  ÷  │
├─────┼─────┼─────┼─────┤
│  7  │  8  │  9  │  ×  │
├─────┼─────┼─────┼─────┤
│  4  │  5  │  6  │  -  │
├─────┼─────┼─────┼─────┤
│  1  │  2  │  3  │  +  │
├─────┼─────┼─────┼─────┤
│  0      │  .  │  =  │
└─────┴─────┴─────┴─────┘
```

### Builder 模式布局

```php
Builder::grid()->padded(true)->contains([
    // 每个按钮通过 row/col 属性定位
    Builder::button()->text('C')->row(0)->col(0),
    Builder::button()->text('0')->row(4)->col(0)->span(2), // 跨列
    // ...
])
```

### HTML 模板布局

```html
<grid padded="true">
    <button text="C" row="0" col="0"/>
    <button text="0" row="4" col="0" colspan="2"/>
    <!-- ... -->
</grid>
```

## 扩展建议

1. **添加科学计算功能**: 三角函数、对数等
2. **历史记录**: 保存计算历史
3. **主题支持**: 多种界面主题
4. **键盘支持**: 支持键盘输入
5. **内存功能**: M+, M-, MR 等内存操作

## 技术要点

- **响应式设计**: 使用 Grid 布局自动适配窗口大小
- **状态同步**: 显示值与内部状态实时同步
- **错误处理**: 除零保护、格式化处理
- **用户体验**: 连续运算、小数点处理等细节

这个计算器示例展示了 libuiBuilder 在构建复杂交互应用方面的能力，是学习状态管理和事件处理的绝佳案例。