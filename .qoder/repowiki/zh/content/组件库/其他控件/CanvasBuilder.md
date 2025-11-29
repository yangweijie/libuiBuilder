# CanvasBuilder深度技术文档

<cite>
**本文档中引用的文件**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php)
- [DrawContext.php](file://src/Components/DrawContext.php)
- [ComponentBuilder.php](file://src/ComponentBuilder.php)
- [Builder.php](file://src/Builder.php)
- [calculator.php](file://example/calculator.php)
- [builder_helpers_demo.php](file://example/builder_helpers_demo.php)
</cite>

## 目录
1. [简介](#简介)
2. [项目结构](#项目结构)
3. [核心组件](#核心组件)
4. [架构概览](#架构概览)
5. [详细组件分析](#详细组件分析)
6. [依赖关系分析](#依赖关系分析)
7. [性能考虑](#性能考虑)
8. [故障排除指南](#故障排除指南)
9. [结论](#结论)

## 简介

CanvasBuilder是libuiBuilder框架中的一个强大组件，专门用于创建自定义绘图区域。它提供了丰富的链式API来绘制各种图形元素，包括矩形、圆形、线条和文本，并支持事件处理和自定义绘制回调。CanvasBuilder采用命令模式设计，通过指令队列实现高效的图形渲染。

## 项目结构

CanvasBuilder位于libuiBuilder项目的组件层次结构中，作为自定义绘图区域的核心实现：

```mermaid
graph TB
subgraph "libuiBuilder项目结构"
Root[根目录]
Src[src/]
Example[example/]
Tests[tests/]
Root --> Src
Root --> Example
Root --> Tests
subgraph "src/Components/"
CanvasBuilder[CanvasBuilder.php]
DrawContext[DrawContext.php]
ComponentBuilder[ComponentBuilder.php]
end
subgraph "src/"
Builder[Builder.php]
Helper[helper.php]
end
Src --> Components
Components --> CanvasBuilder
Components --> DrawContext
Components --> ComponentBuilder
Builder --> CanvasBuilder
end
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L1-L181)
- [DrawContext.php](file://src/Components/DrawContext.php#L1-L35)
- [ComponentBuilder.php](file://src/ComponentBuilder.php#L1-L234)

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L1-L181)
- [ComponentBuilder.php](file://src/ComponentBuilder.php#L1-L234)

## 核心组件

CanvasBuilder的核心功能围绕以下几个关键组件构建：

### CanvasBuilder类
CanvasBuilder是主要的绘图控制器，继承自ComponentBuilder，负责：
- 管理绘图命令队列
- 处理绘制回调和事件响应
- 提供链式API接口
- 协调底层绘图操作

### DrawContext类
DrawContext提供了一个更友好的绘制API包装器，封装了底层的绘图操作。

### 绘图命令系统
CanvasBuilder使用命令模式来管理绘图操作，每个绘图命令都被存储在数组中，在绘制回调中按顺序执行。

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L10-L181)
- [DrawContext.php](file://src/Components/DrawContext.php#L1-L35)

## 架构概览

CanvasBuilder采用了分层架构设计，实现了清晰的关注点分离：

```mermaid
classDiagram
class ComponentBuilder {
+array config
+CData handle
+array children
+string id
+getConfig(key) mixed
+setConfig(key, value) self
+bind(stateKey) self
+on(event, handler) self
}
class CanvasBuilder {
-array drawCommands
+rect(x, y, w, h, color) self
+circle(cx, cy, radius, color) self
+line(x1, y1, x2, y2, color, width) self
+text(text, x, y, color) self
+clear() self
+redraw() self
+onDraw(area, params) void
+onMouseEvent(area, event) void
+onKeyEvent(area, event) bool
-executeDrawCommand(context, command) void
-fillRect(context, x, y, w, h, r, g, b, a) void
}
class DrawContext {
-CData context
+fillRect(x, y, w, h, color) void
+strokeRect(x, y, w, h, color, width) void
+fillCircle(cx, cy, radius, color) void
+drawText(text, x, y, color) void
}
class Area {
+createHandler(onDraw, onMouse, onMouseCrossed, onDragBroken, onKey) CData
+create(handler) CData
+setSize(handle, width, height) void
+queueRedrawAll(handle) void
}
class Draw {
+createPath(fillMode) CData
+createPathFigure(path, x, y) void
+pathLineTo(path, x, y) void
+pathCloseFigure(path) void
+pathEnd(path) void
+createSolidBrush(r, g, b, a) CData
+fill(context, path, brush) void
+freePath(path) void
}
ComponentBuilder <|-- CanvasBuilder
CanvasBuilder --> DrawContext : "创建"
CanvasBuilder --> Area : "使用"
CanvasBuilder --> Draw : "使用"
DrawContext --> Draw : "委托"
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L10-L181)
- [DrawContext.php](file://src/Components/DrawContext.php#L7-L35)
- [ComponentBuilder.php](file://src/ComponentBuilder.php#L11-L234)

## 详细组件分析

### CanvasBuilder核心功能

#### 链式绘图API

CanvasBuilder提供了直观的链式API来创建各种图形元素：

```mermaid
sequenceDiagram
participant Client as "客户端代码"
participant Canvas as "CanvasBuilder"
participant Commands as "绘图命令队列"
participant Renderer as "渲染引擎"
Client->>Canvas : rect(10, 10, 100, 50, [1, 0, 0, 1])
Canvas->>Commands : 添加矩形命令
Client->>Canvas : circle(50, 50, 20, [0, 1, 0, 1])
Canvas->>Commands : 添加圆形命令
Client->>Canvas : line(0, 0, 100, 100, [0, 0, 1, 1], 2)
Canvas->>Commands : 添加线条命令
Client->>Canvas : text("Hello", 20, 20, [0, 0, 0, 1])
Canvas->>Commands : 添加文本命令
Note over Canvas,Renderer : 绘制回调触发
Canvas->>Renderer : onDraw(area, params)
Renderer->>Commands : 遍历命令队列
Commands->>Renderer : 执行每个命令
Renderer->>Renderer : drawRect(context, command)
Renderer->>Renderer : drawCircle(context, command)
Renderer->>Renderer : drawLine(context, command)
Renderer->>Renderer : drawText(context, command)
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L108-L146)
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L49-L70)

#### 绘图命令执行机制

CanvasBuilder使用命令模式来管理绘图操作，每个绘图命令都是一个包含类型和参数的数组：

```mermaid
flowchart TD
Start([开始绘制]) --> ClearBackground["清除背景"]
ClearBackground --> ExecuteCommands["遍历绘图命令"]
ExecuteCommands --> HasCommand{"还有命令?"}
HasCommand --> |是| GetCommand["获取下一个命令"]
GetCommand --> SwitchType{"命令类型"}
SwitchType --> |rect| DrawRect["执行drawRect"]
SwitchType --> |circle| DrawCircle["执行drawCircle"]
SwitchType --> |line| DrawLine["执行drawLine"]
SwitchType --> |text| DrawText["执行drawText"]
DrawRect --> HasCommand
DrawCircle --> HasCommand
DrawLine --> HasCommand
DrawText --> HasCommand
HasCommand --> |否| UserCallback["执行用户自定义回调"]
UserCallback --> End([绘制完成])
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L49-L70)
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L73-L88)

#### 事件处理系统

CanvasBuilder集成了完整的事件处理系统，支持鼠标和键盘事件：

```mermaid
sequenceDiagram
participant User as "用户交互"
participant Canvas as "CanvasBuilder"
participant EventHandlers as "事件处理器"
participant Application as "应用程序"
User->>Canvas : 鼠标点击
Canvas->>Canvas : onMouseEvent(area, event)
Canvas->>EventHandlers : 检查onMouseEvent配置
EventHandlers->>Application : 调用用户回调
Application-->>EventHandlers : 处理结果
EventHandlers-->>Canvas : 返回处理状态
User->>Canvas : 键盘输入
Canvas->>Canvas : onKeyEvent(area, event)
Canvas->>EventHandlers : 检查onKeyEvent配置
EventHandlers->>Application : 调用用户回调
Application-->>EventHandlers : 返回布尔值
EventHandlers-->>Canvas : 返回处理状态
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L163-L175)
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L29-L34)

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L108-L175)

### DrawContext类分析

DrawContext提供了更高层次的绘制API，封装了底层的绘图操作：

| 方法 | 参数 | 功能描述 |
|------|------|----------|
| `fillRect()` | `(float $x, float $y, float $w, float $h, array $color)` | 填充矩形区域 |
| `strokeRect()` | `(float $x, float $y, float $w, float $h, array $color, float $width)` | 描边矩形轮廓 |
| `fillCircle()` | `(float $cx, float $cy, float $radius, array $color)` | 填充圆形区域 |
| `drawText()` | `(string $text, float $x, float $y, array $color)` | 绘制文本 |

虽然DrawContext的实现目前是占位符，但它为未来的扩展提供了良好的接口设计。

**章节来源**
- [DrawContext.php](file://src/Components/DrawContext.php#L16-L34)

### 配置和初始化

CanvasBuilder提供了灵活的配置系统，支持以下配置选项：

| 配置项 | 类型 | 默认值 | 描述 |
|--------|------|--------|------|
| `width` | int | 400 | 画布宽度 |
| `height` | int | 300 | 画布高度 |
| `onDraw` | callable | null | 自定义绘制回调 |
| `onMouseEvent` | callable | null | 鼠标事件回调 |
| `onKeyEvent` | callable | null | 键盘事件回调 |
| `backgroundColor` | array | [1.0, 1.0, 1.0, 1.0] | 背景颜色(RGBA) |

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L15-L23)

## 依赖关系分析

CanvasBuilder的依赖关系体现了清晰的分层架构：

```mermaid
graph LR
subgraph "外部依赖"
LibUI[libui库]
FFI[FFI扩展]
end
subgraph "内部依赖"
ComponentBuilder[ComponentBuilder基类]
Area[Area类]
Draw[Draw类]
end
subgraph "CanvasBuilder"
CanvasBuilder[CanvasBuilder类]
DrawContext[DrawContext类]
end
CanvasBuilder --> ComponentBuilder
CanvasBuilder --> Area
CanvasBuilder --> Draw
CanvasBuilder --> DrawContext
Area --> FFI
Draw --> FFI
ComponentBuilder --> FFI
LibUI --> FFI
```

**图表来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L1-L10)
- [ComponentBuilder.php](file://src/ComponentBuilder.php#L1-L10)

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L1-L10)
- [ComponentBuilder.php](file://src/ComponentBuilder.php#L1-L10)

## 性能考虑

### 绘制优化策略

1. **命令缓存**: CanvasBuilder使用命令队列缓存所有绘图操作，避免重复计算
2. **批量渲染**: 所有绘图命令在单次绘制回调中批量执行
3. **增量更新**: `redraw()`方法仅重新绘制受影响区域
4. **内存管理**: 及时释放绘图路径和画刷资源

### 性能最佳实践

- **避免在onDraw中执行耗时操作**: 绘制回调应该保持轻量级
- **合理使用clear()**: 仅在需要完全重绘时才调用clear()
- **优化命令数量**: 合并相似的绘图操作
- **控制事件频率**: 避免过于频繁的事件回调

### 内存管理

CanvasBuilder通过以下方式管理内存：
- 使用FFI的资源管理机制
- 在绘制完成后及时释放临时资源
- 利用PHP的垃圾回收机制

## 故障排除指南

### 常见问题及解决方案

#### 绘制不显示
**问题**: CanvasBuilder创建后没有显示任何内容
**解决方案**: 
- 检查CanvasBuilder是否正确添加到窗口中
- 验证onDraw回调是否正确定义
- 确认画布尺寸设置正确

#### 性能问题
**问题**: 绘制操作过于缓慢
**解决方案**:
- 减少绘图命令的数量
- 避免在onDraw中执行复杂计算
- 使用适当的缓存策略

#### 事件无响应
**问题**: 鼠标或键盘事件没有触发
**解决方案**:
- 检查事件处理器是否正确注册
- 验证事件回调函数的签名
- 确认CanvasBuilder具有焦点

**章节来源**
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L49-L70)
- [CanvasBuilder.php](file://src/Components/CanvasBuilder.php#L163-L175)

## 结论

CanvasBuilder是一个功能强大且设计精良的自定义绘图组件，它通过以下特性为开发者提供了优秀的绘图体验：

1. **链式API设计**: 直观易用的绘图接口
2. **命令模式实现**: 高效的绘图命令管理
3. **事件系统集成**: 完整的用户交互支持
4. **性能优化**: 通过命令缓存和批量渲染提升性能
5. **扩展性良好**: 清晰的架构便于功能扩展

CanvasBuilder特别适合需要自定义图形界面的应用场景，如数据可视化、游戏开发、图像编辑器等。其模块化的设计使得开发者可以轻松地集成到现有的libuiBuilder项目中，同时保持代码的可维护性和可扩展性。

通过合理的使用CanvasBuilder，开发者可以创建出既美观又高性能的图形用户界面，充分发挥PHP在桌面应用开发中的潜力。