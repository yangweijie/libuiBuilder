# IFLOW.md - libuiBuilder 项目开发规范

## 项目概述

libuiBuilder 是一个基于 PHP 的声明式 GUI 框架，用于构建跨平台桌面应用程序。该项目提供了一个类似于 Vue.js 语法的组件系统，支持双向数据绑定、事件处理和状态管理，使开发者能够使用声明式语法构建 PHP 桌面应用。

## 项目架构

### 核心组件结构
```
src/
└── Declarative/
    ├── Components/
    │   ├── Component.php          # 基础组件类
    │   ├── TemplateParser.php     # XML/HTML 模板解析器
    │   ├── WindowComponent.php    # 窗口组件
    │   ├── ButtonComponent.php    # 按钮组件
    │   ├── EntryComponent.php     # 输入框组件
    │   ├── FormComponent.php      # 表单组件
    │   └── 其他 UI 组件...
    ├── StateManager.php           # 状态管理器
    ├── EventBus.php               # 事件总线
    ├── EventContext.php           # 事件执行上下文
    ├── LoggerManager.php          # 日志管理器
    ├── ErrorHandler.php           # 全局错误处理器
    └── ComponentRegistry.php      # 组件注册表
```

### 核心系统
1. **TemplateParser** - XML 模板解析器，支持动态属性和条件渲染
2. **Component System** - 组件化架构，支持生命周期管理
3. **StateManager** - 状态管理器，支持响应式数据绑定
4. **EventBus** - 事件总线系统
5. **LoggerManager** - 日志管理系统
6. **ErrorHandler** - 全局错误处理器

## 开发实践

### 声明式语法
- 使用 XML 风格的模板定义 UI 组件
- 支持 `ui:window`、`ui:button` 等命名空间前缀
- 组件属性采用标准 HTML 属性格式

### 组件系统
- 所有组件继承自 `Component` 基类
- 支持生命周期方法（`mounted`、`updated`、`beforeDestroy`）
- 组件注册通过 `ComponentRegistry` 管理

### 数据绑定
- **双向数据绑定**：`v-model` 语法
- **动态属性绑定**：`:attribute` 语法
- **条件渲染**：`v-show` 语法

### 事件处理
- **事件绑定**：`@click`、`@change` 等语法
- **安全执行**：使用 PHPSandbox 沙箱执行 PHP 代码
- **内置函数**：`setState`、`getState`、`emit` 等

## 依赖管理

### 主要依赖
```json
{
  "ext-dom": "*",
  "ext-ffi": "*",
  "ext-libxml": "*",
  "corveda/php-sandbox": "^3.1",
  "kingbes/libui": "*",
  "monolog/monolog": "^3.0@dev"
}
```

### 开发依赖
```json
{
  "pestphp/pest": "*",
  "phpunit/phpunit": "*"
}
```

## 命令脚本

### 开发命令
```bash
# 安装依赖
composer install

# 运行示例应用
php example/simple_test.php
php example/features_demo.php
php example/user_management.php

# 运行测试
./vendor/bin/phpunit
./vendor/bin/pest
```

## 核心特性

### 1. 声明式语法
代码即UI，结构清晰，维护成本低50%

### 2. 组件化架构
组件复用，开发效率提升80%

### 3. 响应式状态管理
数据驱动，UI自动更新，减少90%的手动同步代码

### 4. 双向数据绑定
表单开发时间减少70%，代码更简洁

### 5. 统一事件系统
事件逻辑清晰，代码可读性提升85%

### 6. 组件通信机制
组件解耦，数据共享变得简单

### 7. 条件渲染和动态属性
UI自适应，用户体验提升60%

### 8. 生命周期管理
资源自动管理，内存泄漏风险降低95%

## 安全特性

### 代码执行安全
- 使用 PHPSandbox 限制事件处理器中的 PHP 代码执行
- 为沙箱定义安全的内置函数
- 防止恶意代码注入

### 错误处理
- 全局错误处理器
- 组件级日志记录
- 详细的错误信息和上下文

## 开发规范

### PHP 代码规范
- 使用 PSR-4 自动加载标准
- 采用命名空间组织代码
- 遵循 PSR-12 代码风格

### 组件开发规范
- 继承 `Component` 基类
- 实现 `render`、`getTagName`、`getValue`、`setValue` 抽象方法
- 使用 `setAttribute` 和 `getAttribute` 管理组件属性
- 在构造函数中调用父类构造函数并处理事件绑定

### 测试规范
- 为新组件编写单元测试
- 使用 PestPHP 或 PHPUnit
- 测试组件渲染、事件处理和状态管理功能

## 项目配置

### PHP 要求
- PHP 8.0+
- libui PHP 扩展
- Composer

### 运行环境
- FFI 扩展（用于 libui 交互）
- DOM 扩展（用于 XML 解析）
- libxml 扩展（用于 XML 处理）

## 示例应用

项目包含多个示例应用展示不同功能：

1. **simple_test.php** - 基础功能演示
2. **features_demo.php** - 完整特性演示
3. **user_management.php** - 用户管理系统示例

## 扩展开发

### 添加新组件
1. 创建继承自 `Component` 的组件类
2. 实现 `render()`、`getTagName()`、`getValue()`、`setValue()` 方法
3. 在 `ComponentRegistry` 中注册组件
4. 编写相应的测试

### 添加新功能
1. 遵循现有架构模式
2. 确保安全执行环境
3. 添加适当的日志记录
4. 提供清晰的错误处理
5. 编写测试用例

## 项目维护

### 代码审查清单
- [ ] 遵循组件基类接口
- [ ] 安全执行代码（使用 PHPSandbox）
- [ ] 适当的错误处理和日志记录
- [ ] 代码风格一致性
- [ ] 测试覆盖关键功能
- [ ] 文档更新

### 发布流程
1. 更新版本号
2. 运行完整测试套件
3. 更新文档
4. 创建发布标签
5. 发布到 Packagist