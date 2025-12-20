# libuiBuilder - iFlow CLI 项目配置

## 项目概述

libuiBuilder 是一个现代化的 PHP GUI 开发框架，基于 kingbes/libui 库，采用链式构建器模式，深度集成了依赖注入、事件系统和配置管理。该项目提供了完整的声明式 UI 开发体验，支持跨平台桌面应用开发。

## 核心技术栈

### 主要依赖
- **kingbes/libui** (0.1.*) - PHP FFI GUI 库，原生控件绑定
- **league/event** (^3.0) - 事件系统，解耦业务逻辑
- **league/config** (^1.2) - 类型安全配置管理
- **php-di/php-di** (^7.0) - 依赖注入容器
- **pestphp/pest** (*) - 现代化 PHP 测试框架

### 开发工具
- **Composer** - PHP 包管理
- **Puppeteer** - 端到端测试 (Node.js)
- **PHPUnit** - 单元测试支持

## 项目架构

```
应用层 (Builder 链式调用)
    ↓
工厂层 (Builder 静态工厂)
    ↓
构建层 (ComponentBuilder + 依赖注入)
    ↓
核心服务 (Config/Event/Container/State)
    ↓
基础库 (kingbes/libui)
```

## 目录结构

```
libuiBuilder/
├── src/                      # 核心源码
│   ├── Builder/             # 构建器模式 (17个组件)
│   │   ├── ComponentBuilder.php    # 抽象基类
│   │   ├── Builder.php             # 工厂类
│   │   ├── WindowBuilder.php       # 窗口构建器
│   │   ├── ButtonBuilder.php       # 按钮构建器
│   │   └── ...                     # 其他组件构建器
│   ├── State/               # 状态管理
│   │   └── StateManager.php
│   ├── Core/                # 核心服务
│   │   ├── Config/          # 配置管理 (league/config)
│   │   ├── Event/           # 事件系统 (league/event)
│   │   └── Container/       # 依赖注入 (php-di)
│   └── Templates/           # 模板系统
├── tests/                   # 测试套件
│   ├── Builder/             # 构建器测试
│   ├── Core/                # 核心服务测试
│   ├── Feature/             # 功能测试
│   ├── Integration/         # 集成测试
│   ├── State/               # 状态管理测试
│   └── Unit/                # 单元测试
├── example/                 # 示例代码
│   ├── 01_basics/           # 基础示例
│   ├── 02_layouts/          # 布局示例
│   ├── 03_components/       # 组件示例
│   ├── 04_advanced/         # 高级示例
│   └── views/               # 视图示例
├── tools/                   # 开发工具
│   ├── designer.*           # GUI 设计器
│   ├── preview.html         # 预览工具
│   └── *.js                 # 调试脚本
├── docs/                    # 文档
│   ├── ARCHITECTURE.md      # 架构设计
│   ├── BUILDER_README.md    # 构建器文档
│   ├── HTML_RENDERER.md     # HTML 渲染器
│   └── QUICKSTART_DI.md     # 快速开始
└── coverage-report/         # 测试覆盖率报告
```

## 常用命令

### 开发命令
```bash
# 安装依赖
composer install

# 运行测试
composer test                # 运行 Pest 测试
composer test-coverage       # 运行测试并生成覆盖率报告

# 生成文档
composer docs               # 生成 API 文档

# 代码迁移
composer migrate            # 迁移遗留代码
```

### 测试命令
```bash
# Pest 测试
./vendor/bin/pest                    # 运行所有测试
./vendor/bin/pest --coverage         # 带覆盖率
./vendor/bin/pest tests/Builder/     # 运行构建器测试

# PHPUnit 备用
./vendor/bin/phpunit
```

### Node.js 工具
```bash
# 端到端测试
npm test                    # 运行 E2E 测试
npm run test:headless      # 无头模式测试
```

## 开发工作流

### 1. 功能开发
1. 在 `src/Builder/` 中创建新的组件构建器
2. 继承 `ComponentBuilder` 基类
3. 实现链式方法和 `build()` 方法
4. 添加对应测试到 `tests/Builder/`

### 2. 测试驱动开发
```bash
# 1. 创建测试
# tests/Builder/NewComponentTest.php

# 2. 运行测试 (预期失败)
./vendor/bin/pest tests/Builder/NewComponentTest.php

# 3. 实现功能
# src/Builder/NewComponentBuilder.php

# 4. 验证测试
./vendor/bin/pest --coverage
```

### 3. 文档更新
- 新组件文档添加到 `docs/BUILDER_README.md`
- 架构变更更新 `docs/ARCHITECTURE.md`
- 示例代码添加到 `example/` 相应目录

## 代码规范

### PHP 编码标准
- 遵循 PSR-4 自动加载规范
- 使用严格的类型声明
- 方法名使用驼峰命名
- 类名使用帕斯卡命名
- 常量使用全大写下划线分隔

### 组件构建器规范
```php
class NewComponentBuilder extends ComponentBuilder
{
    public function methodName($value): self
    {
        // 链式调用支持
        return $this;
    }
    
    public function build(): CData
    {
        // 创建并返回 kingbes/libui 控件
    }
}
```

### 测试规范
- 使用 Pest 描述性语法
- 每个功能对应一个测试文件
- 测试方法命名：`it_功能描述()`
- 保持测试独立性

## 核心特性

### 1. 链式构建器模式
```php
$app = Builder::window()
    ->title('My App')
    ->size(600, 400)
    ->contains(
        Builder::vbox()
            ->contains([
                Builder::label()->text('Hello'),
                Builder::button()->text('Click')
            ])
    )
    ->show();
```

### 2. 依赖注入
```php
$container = ContainerFactory::create();
$builder = $container->get(Builder::class);
// 自动注入 StateManager, EventDispatcher, ConfigManager
```

### 3. 事件系统
```php
$events = new EventDispatcher();
$events->on(ButtonClickEvent::class, function($event) {
    echo "按钮 {$event->getComponentId()} 被点击\n";
});
```

### 4. 状态管理
```php
$state = StateManager::instance();
$state->set('count', 0);
$state->watch('count', fn($new, $old) => echo "变化: $old → $new");
```

### 5. 配置管理
```php
$config = new ConfigManager([
    'app' => ['title' => 'My App', 'width' => 800],
]);
$title = $config->get('app.title');
```

## 可用组件

### 基础组件 (17个)
- **Window** - 窗口容器
- **Button** - 按钮
- **Label** - 文本标签
- **Entry** - 输入框
- **Checkbox** - 复选框
- **Combobox** - 下拉框
- **Slider** - 滑块
- **Spinbox** - 数字输入
- **ProgressBar** - 进度条
- **Separator** - 分隔线
- **Group** - 组容器

### 布局组件
- **Box** - 盒子布局 (水平/垂直)
- **Grid** - 网格布局
- **Tab** - 标签页布局
- **Table** - 表格布局

## 环境要求

### PHP 环境
- PHP >= 8.0
- 扩展：ext-ffi, ext-dom, ext-libxml
- Composer 包管理器

### 系统要求
- 支持 FFI 的操作系统
- libui 原生库依赖
- 跨平台支持 (Windows/macOS/Linux)

## 质量保证

### 测试覆盖率
- 目标覆盖率：≥ 90%
- 覆盖率报告：`coverage-report/`
- 自动化测试：Pest + PHPUnit

### 代码质量
- 静态分析：PHPStan
- 代码风格：PSR-12
- 类型检查：严格类型

### 持续集成
- 自动化测试流程
- 代码质量检查
- 覆盖率监控

## 调试工具

### GUI 设计器
- `tools/designer.html` - 可视化设计器
- `tools/designer.js` - 设计器逻辑
- `tools/designer.css` - 设计器样式

### 调试脚本
- `tools/debug_*.js` - 调试辅助脚本
- `tools/test_grid_layout.html` - 布局测试

### 预览工具
- `tools/preview.html` - 组件预览
- `tools/example_3_to_50.html` - 示例展示

## 扩展开发

### 新增组件
1. 继承 `ComponentBuilder`
2. 实现必要方法
3. 添加测试用例
4. 更新文档

### 自定义事件
1. 创建事件类
2. 注册到事件分发器
3. 在构建器中触发

### 配置扩展
1. 定义配置模式
2. 添加到 ConfigManager
3. 支持多格式加载

## 部署说明

### 生产部署
```bash
# 1. 安装生产依赖
composer install --no-dev --optimize-autoloader

# 2. 运行测试
./vendor/bin/pest

# 3. 生成文档
composer docs

# 4. 打包发布
# (根据具体部署方式)
```

### 开发环境
```bash
# 1. 克隆仓库
git clone <repository>
cd libuiBuilder

# 2. 安装依赖
composer install
npm install

# 3. 运行测试
composer test

# 4. 启动开发
php example/04_advanced/builder_example.php
```

## 社区资源

### 文档
- [完整架构文档](docs/ARCHITECTURE.md)
- [快速开始指南](docs/QUICKSTART_DI.md)
- [构建器使用文档](docs/BUILDER_README.md)

### 示例
- 基础示例：`example/01_basics/`
- 布局示例：`example/02_layouts/`
- 组件示例：`example/03_components/`
- 高级示例：`example/04_advanced/`

### 测试
- 单元测试：`tests/Unit/`
- 功能测试：`tests/Feature/`
- 集成测试：`tests/Integration/`
- 覆盖率报告：`coverage-report/`

## 许可证

MIT License - 详见 [LICENSE](LICENSE) 文件

## 贡献指南

1. Fork 项目仓库
2. 创建功能分支
3. 编写测试用例
4. 实现功能代码
5. 确保测试通过
6. 提交 Pull Request

## 更新日志

### 当前版本特性
- ✅ 完整的链式构建器模式
- ✅ 依赖注入容器集成
- ✅ 事件驱动架构
- ✅ 状态管理系统
- ✅ 配置管理支持
- ✅ 17+ UI 组件
- ✅ 完整测试覆盖
- ✅ 可视化设计器
- ✅ 详细文档和示例

### 技术债务
- 🔄 性能优化空间
- 🔄 更多组件类型
- 🔄 主题系统
- 🔄 国际化支持
- 🔄 插件系统

---

*最后更新: 2025-12-19*