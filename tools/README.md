# libuiBuilder 可视化设计器

这是一个基于 Web 的可视化界面设计器，专为 libuiBuilder 框架设计，支持拖拽式组件布局和实时 HTML 代码生成。

## 功能特性

### 🎨 核心功能
- **拖拽式设计** - 从组件面板拖拽组件到设计画布
- **实时预览** - 实时显示组件效果和生成的 HTML 代码
- **属性编辑** - 选中组件后可在右侧面板编辑属性
- **多平台支持** - 支持 Windows、macOS、Linux 三种平台样式切换
- **代码导出** - 生成符合 libuiBuilder 规范的 HTML 代码

### 🧩 组件支持

#### 容器组件
- **Window** - 主窗口容器
- **Grid** - 网格布局容器
- **HBox** - 水平布局容器
- **VBox** - 垂直布局容器
- **Tab** - 标签页容器

#### 输入控件
- **Input** - 单行文本输入框
- **Textarea** - 多行文本输入框
- **Password** - 密码输入框
- **Combobox** - 下拉选择框
- **Spinbox** - 数字输入框
- **Slider** - 滑动条

#### 按钮控件
- **Button** - 按钮
- **Checkbox** - 复选框
- **Radio** - 单选框

#### 显示控件
- **Label** - 文本标签
- **ProgressBar** - 进度条
- **Separator** - 分隔符
- **Table** - 表格

## 使用方法

### 1. 打开设计器
在浏览器中打开 `tools/designer.html`

### 2. 设计界面
1. 从左侧组件面板拖拽组件到中央设计区域
2. 点击组件进行选择，在右侧属性面板编辑属性
3. 顶部可以切换不同平台的视觉样式

### 3. 预览和导出
- **预览** - 点击顶部"预览"按钮在新窗口查看效果
- **复制代码** - 底部代码预览区域点击"复制代码"
- **导出文件** - 点击"导出 HTML"下载 .ui.html 文件
- **保存设计** - 点击"保存"将设计保存到浏览器本地存储

## 生成的代码格式

设计器生成的 HTML 代码完全兼容 libuiBuilder 的 HtmlRenderer：

```html
<!DOCTYPE html>
<ui version="1.0">
  <window title="登录窗口" size="400,300" centered="true" margined="true">
    <grid padded="true">
      <label row="0" col="0" align="end,center">用户名:</label>
      <input row="0" col="1" placeholder="请输入用户名" expand="horizontal"/>
      
      <label row="1" col="0" align="end,center">密码:</label>
      <input row="1" col="1" type="password" placeholder="请输入密码" expand="horizontal"/>
      
      <hbox row="2" col="0" colspan="2" align="center">
        <button onclick="handleLogin">登录</button>
        <button onclick="handleReset">清空</button>
      </hbox>
    </grid>
  </window>
</ui>
```

## 技术实现

### 架构设计
- **组件面板** - 左侧可拖拽的组件库
- **设计画布** - 中央拖拽设计区域
- **属性面板** - 右侧组件属性编辑
- **代码预览** - 底部实时生成的 HTML 代码
- **平台切换** - 顶部平台样式切换

### 核心类
```javascript
class LibuiBuilderDesigner {
    constructor() {
        this.platform = 'windows';
        this.components = [];
        this.selectedComponent = null;
        // ...
    }
    
    // 拖拽处理
    setupDragAndDrop() { /* ... */ }
    
    // 组件管理
    addComponent(type, x, y) { /* ... */ }
    deleteComponent(component) { /* ... */ }
    
    // 代码生成
    generateHTML() { /* ... */ }
    
    // 属性编辑
    showProperties(component) { /* ... */ }
}
```

### 样式系统
- 基于 libui-ng-complete.css 实现跨平台样式
- 响应式布局支持不同屏幕尺寸
- 拖拽交互视觉反馈

## 文件结构

```
tools/
├── designer.html          # 主页面
├── designer.css           # 样式文件
├── designer.js            # 核心逻辑
├── libui-ng-complete.css  # 跨平台样式库
└── README.md              # 说明文档
```

## 快捷操作

### 拖拽操作
- 拖拽组件到画布添加组件
- 点击组件选中进行编辑
- 点击删除按钮移除组件

### 属性编辑
- 实时编辑组件属性
- 自动更新预览效果
- 同步更新 HTML 代码

### 代码操作
- 实时生成 HTML 代码
- 一键复制代码到剪贴板
- 导出为 .ui.html 文件

## 浏览器兼容性

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 开发计划

### 即将推出的功能
- [ ] 组件对齐辅助线
- [ ] 撤销/重做操作
- [ ] 组件模板库
- [ ] 项目文件管理
- [ ] 代码语法高亮
- [ ] 组件层次结构视图
- [ ] 键盘快捷键支持

### 高级功能
- [ ] 自定义组件创建
- [ ] 数据绑定设计器
- [ ] 事件处理器配置
- [ ] 响应式布局设计
- [ ] 组件动画效果

## 贡献指南

欢迎提交 Issue 和 Pull Request 来改进设计器功能。

### 开发环境
1. 克隆项目
2. 在浏览器中打开 `tools/designer.html`
3. 开始开发调试

### 代码规范
- 使用 ES6+ 语法
- 遵循驼峰命名规范
- 添加适当的注释
- 保持代码整洁

## 许可证

MIT License