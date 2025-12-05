# libuiBuilder 设计器 Bug 测试指南

## 问题描述

用户报告了一个bug：在libuiBuilder设计器中，当切换操作系统平台或修改Window组件属性时，子组件会从视觉显示中消失。

## 测试脚本说明

本目录包含三个测试脚本，用于重现和验证这个bug：

1. **`test_designer_bug.js`** - 完整功能的测试脚本，包含详细的步骤和截图
2. **`test_designer_bug_simple.js`** - 简化版测试脚本，专注于核心测试逻辑
3. **`test_designer_bug_headless.js`** - 无头模式测试脚本，适用于CI/CD环境

## 安装依赖

```bash
# 安装 Node.js 依赖（Puppeteer）
npm install
```

## 运行测试

### 方法1：运行完整测试（推荐）

```bash
# 这将打开浏览器窗口，模拟用户操作
node test_designer_bug_simple.js
```

### 方法2：运行无头模式测试

```bash
# 无头模式，不打开浏览器窗口
node test_designer_bug_headless.js
```

### 方法3：使用 npm 脚本

```bash
# 运行完整测试
npm test

# 运行无头模式测试
npm run test:headless
```

## 测试步骤

测试脚本会自动执行以下步骤：

1. **启动设计器界面** - 打开 `tools/designer.html`
2. **添加Window组件** - 拖拽Window组件到画布
3. **添加HBox组件** - 在Window中添加HBox容器
4. **添加Input组件** - 在HBox中添加输入框
5. **修改Window属性** - 将边距属性从true改为false
6. **切换操作系统平台** - 从Windows切换到macOS
7. **验证子组件可见性** - 检查每个步骤后子组件是否仍然可见

## 测试输出

测试运行时会生成：

1. **控制台输出** - 显示每个步骤的结果和状态
2. **截图文件** - 保存在 `test_screenshots/` 目录中
3. **测试报告** - 如果发现bug，会生成JSON格式的详细报告

## 预期结果

### 正常情况（无bug）
- 所有步骤都显示 ✅ 通过
- 控制台输出显示子组件在所有操作后都保持可见
- 最终显示 "✅ 测试通过！未发现子组件消失的问题。"

### 发现bug的情况
- 相关步骤显示 ❌ 失败
- 控制台输出显示子组件在特定操作后消失
- 生成详细的bug报告和调试信息
- 最终显示发现的bug列表和问题分析

## 问题诊断

如果测试发现bug，脚本会提供以下诊断信息：

1. **bug类型** - 指出是在修改属性还是切换平台时出现问题
2. **组件状态** - 显示不可见组件的详细信息
3. **代码位置** - 指出可能有问题的方法和文件
4. **建议检查** - 提供具体的代码位置和修复建议

## 手动验证

如果自动化测试通过但手动测试仍然发现问题，可以：

1. 增加测试脚本中的延迟时间
2. 检查浏览器控制台是否有错误信息
3. 验证拖拽操作的准确性
4. 检查组件的选中状态

## 文件说明

- `package.json` - Node.js项目配置和依赖
- `test_designer_bug.js` - 原始完整测试脚本
- `test_designer_bug_simple.js` - 简化版测试脚本（推荐使用）
- `test_designer_bug_headless.js` - 无头模式测试脚本
- `test_screenshots/` - 测试截图目录（自动创建）
- `test_report_*.json` - 详细的测试报告（发现bug时生成）

## 调试建议

如果测试失败，建议检查以下代码位置：

1. **`tools/designer.js` 中的 `refreshComponent` 方法**（约第850行）
   - 检查组件重新渲染时子组件是否被正确处理
   - 验证子组件的父组件关系是否保持

2. **`tools/designer.js` 中的 `updateComponentStyles` 方法**（约第1350行）
   - 检查平台切换时所有组件是否被正确更新
   - 验证子组件的样式更新逻辑

3. **`tools/designer.js` 中的 `renderComponent` 方法**（约第400行）
   - 检查容器组件的子组件渲染逻辑
   - 验证DOM结构是否正确重建

## 注意事项

1. 首次运行测试时，Puppeteer会自动下载Chromium浏览器（约200MB）
2. 测试需要访问本地文件系统，确保有相应权限
3. 如果测试速度太快导致问题，可以调整脚本中的延迟时间
4. 截图功能可以帮助可视化测试过程，便于调试