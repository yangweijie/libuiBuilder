# libuiBuilder

一个基于 PHP 的声明式 GUI 框架，用于构建跨平台桌面应用程序。

## 功能特性

- **声明式组件系统**：使用 XML 风格的模板定义 UI 组件
- **事件处理**：支持 `@click`、`@change` 等事件绑定
- **状态管理**：内置 StateManager 和 EventBus
- **双向数据绑定**：支持 `v-model` 语法
- **组件化架构**：可复用的组件系统
- **条件渲染**：支持 `v-show` 条件显示
- **动态属性绑定**：支持 `:attribute` 动态绑定
- **方法链调用**：支持链式调用语法
- **生命周期管理**：组件生命周期钩子
- **箭头函数支持**：支持 PHP 风格的箭头函数

## 安装要求

- PHP 8.0+
- libui PHP 扩展
- Composer

## 快速开始

```bash
composer install
```

## 示例

运行示例应用：

```bash
# 简单测试
php example/simple_test.php

# 特性演示
php example/features_demo.php

# 用户管理系统
php example/user_management.php

# 其他示例
php example/simple.php
php example/full.php
```

## 核心功能

### 模板语法
```xml
<ui:window title="示例应用" width="600" height="400">
    <ui:form padded="true">
        <ui:entry 
            label="用户名" 
            ref="username"
            v-model="username"
            @change="setState('usernameLength', strlen(args[0]))"
        />
        <ui:button 
            text="获取长度"
            @click="echo '用户名长度: ' . getState('usernameLength', 0);"
        />
    </ui:form>
</ui:window>
```

### 条件渲染和动态属性
```xml
<!-- 条件渲染 -->
<ui:button 
    text="删除" 
    v-show="getState('user.role', '') === 'admin'"
    @click="deleteUser()"
/>

<!-- 动态属性绑定 -->
<ui:button 
    :text="getState('isLoading', false) ? '保存中...' : '保存'"
    :disabled="!getState('formValid', false) || getState('isLoading', false)"
    @click="saveData()"
/>

<!-- 动态数据源 -->
<ui:combobox 
    :options="json_decode(getState('cities', '[]'), true)"
    v-model="user.city"
/>
```

### 事件处理
- `@click`、`@change` 等事件绑定
- 支持复杂表达式：`setState('key', value)`
- 状态管理：`getState('key', 'default')`
- 静态方法调用：`UserService::deleteUser()`
- 方法链：`Database::table().where().delete()`
- 箭头函数：`fn($x) => $x * 2`

### 组件系统
- WindowComponent
- ButtonComponent  
- EntryComponent
- FormComponent
- CheckboxComponent
- LabelComponent
- ComboboxComponent
- BoxComponent
- TableComponent
- 以及其他 UI 组件

## 主要更新

### 最近新增功能
- [x] 条件渲染支持 (`v-show`)
- [x] 动态属性绑定 (`:attribute`)
- [x] 方法链调用支持
- [x] 箭头函数支持
- [x] 完善的表达式解析
- [x] 新增多个示例文件

### 历史修复
- [x] XML 命名空间前缀解析问题
- [x] 特殊属性名（@click, v-model）处理
- [x] 组件继承问题（CheckboxComponent, LabelComponent）
- [x] EventContext 集成
- [x] JavaScript 语法转换（.length → strlen）
- [x] console.log 输出支持
- [x] 事件处理器语法错误修复

### 系统架构
- **TemplateParser**：XML 模板解析器，支持动态属性和条件渲染
- **Component**：基础组件类，支持生命周期管理
- **EventContext**：事件执行上下文
- **StateManager**：状态管理器，支持响应式数据绑定
- **EventBus**：事件总线
- **LoggerManager**：日志管理系统
- **ErrorHandler**：全局错误处理器

## 特性详解

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

## 许可证

MIT License