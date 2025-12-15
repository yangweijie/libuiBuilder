# libuiBuilder 简化设计器 - 完整分析文档

## 概述

libuiBuilder 简化设计器是一个基于 HTML5、CSS3 和原生 JavaScript 的跨平台 GUI 界面设计工具。它允许用户通过拖放方式创建 libuiBuilder 兼容的界面，并实时生成 HTML 代码。该设计器采用纯前端技术实现，无需后端支持，可直接在浏览器中运行。

### 核心特性
- **拖放式设计**：通过拖拽组件到画布快速构建界面
- **实时预览**：设计过程中的所有更改即时反映在画布和代码预览区
- **双HTML生成风格**：支持 GUI 标签（libuiBuilder原生）和类HTML标签两种输出格式
- **跨平台样式**：提供 Windows、macOS、Linux 三种平台的主题样式切换
- **完整组件库**：支持所有 libuiBuilder 组件类型，包括容器组件、输入控件、按钮控件和显示控件
- **高级容器支持**：完整支持 Grid 布局、Tab 标签页、分组容器等复杂布局
- **表格组件**：支持多种列类型（文本、图片、复选框、进度条、按钮等）的复杂表格设计
- **响应式属性编辑**：属性面板支持实时编辑和保存
- **代码导出**：支持导出完整 HTML 代码，可直接用于 libuiBuilder 项目

## 文件结构

```
tools/
├── designer.html          # 主设计器文件，包含完整 HTML 结构和 JavaScript 实现
├── designer.css           # 设计器样式文件，包含所有 UI 样式和布局
├── libui-ng-complete.css  # 跨平台样式库，提供 Windows/macOS/Linux 主题
└── README.md              # 本文档
```

## 架构设计

### 核心类：`SimpleLibuiDesigner`

设计器的核心是一个名为 `SimpleLibuiDesigner` 的 JavaScript 类，采用面向对象设计，封装了所有设计器功能。

#### 类属性
- `components`：存储所有组件数据的数组（树形结构）
- `selectedComponent`：当前选中的组件引用
- `componentIdCounter`：组件 ID 计数器
- `draggedComponent`：当前拖拽中的组件
- `htmlGenerationStyle`：HTML 生成风格（'gui' 或 'html'）
- `updateCodePreviewTimeout`：代码预览更新防抖定时器
- `updatingProperties`：防止属性更新循环的标志

#### 主要方法

##### 初始化方法
- `constructor()`：初始化设计器实例，设置默认值，调用 `init()`
- `init()`：初始化 DOM 元素引用，设置事件监听器

##### 组件管理
- `setupEventListeners()`：设置全局事件监听器（拖放、点击等）
- `setupDragAndDrop()`：配置拖放功能
- `addComponent(type, x, y)`：在指定位置添加新组件
- `getDefaultProps(type)`：获取指定组件类型的默认属性
- `renderComponent(component, parentComponent)`：渲染组件到画布
- `createComponentElement(component)`：创建组件 DOM 元素
- `createComponentContent(component)`：创建组件内容区域
- `createComponentControls(component)`：创建组件控制按钮（删除等）

##### 选择和属性管理
- `selectComponent(component)`：选择组件并高亮显示
- `showProperties(component)`：显示组件属性面板
- `getComponentProperties(component)`：获取组件属性配置
- `bindPropertyEvents(component)`：绑定属性输入事件
- `saveComponentProperties(component)`：保存组件属性
- `refreshComponent(component)`：刷新组件显示
- `deleteComponent(component)`：删除组件

##### 容器和布局管理
- `isContainerComponent(type)`：检查是否为容器组件
- `isControlComponent(type)`：检查是否为控件组件
- `addComponentToContainer(type, containerComponent, x, y)`：添加组件到容器
- `calculateNextGridPosition(containerComponent)`：计算 Grid 容器的下一个可用位置
- `applyGridStyles(element, component, parentComponent)`：应用 Grid 布局样式
- `setupTabEvents(component)`：设置 Tab 组件事件
- `setupComboboxEvents(component)`：设置下拉框组件事件
- `setupTableEvents(component)`：设置表格组件事件

##### 代码生成
- `generateHTML()`：生成 HTML 代码
- `updateCodePreview()`：更新代码预览（防抖实现）
- `save()`：保存设计为 HTML 文件
- `export()`：导出代码到模态框
- `preview()`：在新窗口预览设计

##### 辅助方法
- `findComponentById(id)`：通过 ID 查找组件
- `findParentComponent(component)`：查找父组件
- `removeComponentFromTree(targetComponent)`：从组件树中删除组件
- `getChildrenForTabPage(tabComponent, tabIndex)`：获取指定标签页的子组件
- `showSaveNotification()`：显示保存成功提示
- `clearAll()`：清空所有组件

## 功能详细说明

### 1. 拖放系统

#### 拖放流程
1. **拖拽开始**：用户在组件面板拖拽组件时，设置 `draggedComponent` 为组件类型
2. **拖拽过程**：在画布上移动时，高亮可放置的容器组件
3. **拖放结束**：释放时，根据目标位置决定添加方式：
   - 如果释放到容器组件上：将新组件添加到容器内
   - 如果释放到空白区域：在画布绝对位置添加新组件

#### 容器识别
- **容器组件**：window、grid、hbox、vbox、form、tab、group
- **控件组件**：input、textarea、password、combobox、spinbox、slider、button、checkbox、radio、label、progressbar、separator、table

### 2. 组件渲染系统

#### 组件 DOM 结构
```html
<div class="component-element" data-component-id="component_1">
    <div class="component-content">
        <!-- 组件特定内容 -->
    </div>
    <div class="component-controls">
        <button class="control-btn delete">×</button>
    </div>
</div>
```

#### 组件状态管理
- **默认状态**：普通边框
- **悬停状态**：蓝色边框
- **选中状态**：蓝色边框 + 阴影高亮
- **拖放目标**：绿色边框 + 阴影

### 3. Grid 布局系统

#### 属性支持
- `row`：行索引（从0开始）
- `col`：列索引（从0开始）
- `rowspan`：行跨度
- `colspan`：列跨度
- `align`：对齐方式（fill、start、center、end）

#### 自动位置计算
- 当组件添加到 Grid 容器时，自动计算下一个可用位置
- 使用 10x10 网格系统跟踪占用位置
- 智能寻找空闲单元格

#### CSS Grid 实现
```css
.component-element {
    grid-area: {row+1} / {col+1} / span {rowspan} / span {colspan};
    align-self: {alignment};
    justify-self: {alignment};
}
```

### 4. Tab 标签页系统

#### 数据结构
```javascript
{
    type: 'tab',
    props: {
        tabs: '标签页1,标签页2,标签页3',
        activeTab: '0',
        padded: 'true'
    },
    children: [
        // 子组件，每个组件有 tabIndex 属性指定所属标签页
    ]
}
```

#### 功能特性
- **动态标签管理**：可添加、删除、重命名标签页
- **组件分组**：子组件按标签页分组存储
- **自动激活**：添加新标签页时自动激活
- **占位符管理**：空标签页显示占位符提示

#### 事件处理
- 标签页切换时更新显示的子组件
- 删除标签页时自动重新分配子组件索引
- 标签页激活状态实时同步

### 5. 表格组件系统

#### 列类型支持
| 类型 | 说明 | 示例数据 | HTML输出 |
|------|------|----------|----------|
| text | 普通文本 | "示例文本" | `<td>示例文本</td>` |
| image | 图片 | "image.png" | `<td><img src="image.png" alt="image.png"></td>` |
| checkbox | 复选框 | "true"/"false" | `<td><input type="checkbox" checked></td>` |
| progress | 进度条 | "75" | `<td><progress value="75" max="100"></progress>` |
| button | 按钮 | "删除:delete" | `<td><button value="delete">删除</button></td>` |
| imageText | 图片+文本 | "icon.png:设置" | `<td><img src="icon.png"> 设置</td>` |

#### 按钮列格式
- **简单文本**：直接显示按钮文本，值为空
  - 示例：`删除` → `<button>删除</button>`
- **文本:值格式**：显示文本，设置value属性
  - 示例：`删除:delete` → `<button value="delete">删除</button>`

#### 数据管理
- 可视化编辑表格数据
- 支持增加/删除行
- 实时数据绑定

### 6. 属性编辑系统

#### 属性面板结构
- **分组显示**：属性按功能分组显示
- **实时更新**：部分属性实时更新组件显示
- **批量保存**：提供保存按钮统一保存属性

#### 特殊属性处理
- **Grid布局属性**：实时更新组件位置和大小
- **下拉框选项**：动态管理选项列表
- **标签页属性**：管理标签页标题和激活状态
- **表格属性**：管理列类型和数据

### 7. 代码生成系统

#### 两种生成风格
1. **GUI标签风格**（默认）
   ```html
   <window title="我的窗口" size="400,300" centered="true">
       <grid padded="true">
           <label row="0" col="0">用户名:</label>
           <input row="0" col="1" stretchy="true" />
       </grid>
   </window>
   ```

2. **类HTML标签风格**
   ```html
   <div title="我的窗口" size="400,300" centered="true">
       <div padded="true">
           <label row="0" col="0">用户名:</label>
           <input row="0" col="1" stretchy="true" />
       </div>
   </div>
   ```

#### 代码优化
- **防抖更新**：减少频繁更新带来的性能问题
- **完整结构**：生成完整的 HTML 文档结构
- **属性转义**：正确处理属性值中的特殊字符

## 组件库详细说明

### 容器组件

#### 1. Window（窗口）
- **主要属性**：title、size、centered、margined
- **默认尺寸**：400x300
- **特殊功能**：支持居中显示、边距控制

#### 2. Grid（网格布局）
- **主要属性**：padded
- **布局特性**：支持二维网格布局，子组件可指定行列位置

#### 3. HBox（水平盒子）
- **主要属性**：padded
- **布局特性**：水平排列子组件，子组件默认拉伸

#### 4. VBox（垂直盒子）
- **主要属性**：padded
- **布局特性**：垂直排列子组件，子组件默认拉伸

#### 5. Form（表单容器）
- **主要属性**：padded
- **布局特性**：表单布局容器，子组件默认拉伸

#### 6. Tab（标签页）
- **主要属性**：tabs、activeTab、padded
- **特殊功能**：动态标签页管理，子组件按标签页分组

#### 7. Group（分组容器）
- **主要属性**：title、margined
- **特殊功能**：带标题的分组容器，视觉上分组相关控件

### 输入控件

#### 1. Input（输入框）
- **主要属性**：placeholder、value、stretchy

#### 2. Textarea（多行输入）
- **主要属性**：placeholder、rows、stretchy

#### 3. Password（密码输入）
- **主要属性**：placeholder、stretchy

#### 4. Combobox（下拉框）
- **主要属性**：options、selected、stretchy
- **特殊功能**：动态选项管理

#### 5. Spinbox（数字输入框）
- **主要属性**：min、max、value、stretchy

#### 6. Slider（滑动条）
- **主要属性**：min、max、value、stretchy

### 按钮控件

#### 1. Button（按钮）
- **主要属性**：text、stretchy

#### 2. Checkbox（复选框）
- **主要属性**：text、checked、stretchy

#### 3. Radio（单选框）
- **主要属性**：text、stretchy

### 显示控件

#### 1. Label（标签）
- **主要属性**：text、stretchy

#### 2. Progressbar（进度条）
- **主要属性**：value、stretchy

#### 3. Separator（分隔符）
- **主要属性**：orientation、stretchy

#### 4. Table（表格）
- **主要属性**：columns、columnTypes、tableData、stretchy
- **特殊功能**：支持多种列类型，可视化数据编辑

## 样式系统

### 跨平台主题
设计器集成了 `libui-ng-complete.css` 样式库，提供三种平台的主题：

1. **Windows 主题**（默认）
   - 蓝色系配色
   - 直角边框
   - 微软 Fluent Design 风格

2. **macOS 主题**
   - 灰色系配色
   - 圆角边框
   - macOS 毛玻璃效果

3. **Linux 主题**
   - 紫色系配色
   - GTK 风格控件

### 设计器专用样式
`designer.css` 包含以下主要样式模块：

#### 1. 布局样式
- 三栏布局（组件面板、设计画布、属性面板）
- 响应式高度计算
- 浮动元素定位

#### 2. 组件样式
- 组件预览样式
- 拖放效果样式
- 选中状态样式

#### 3. 属性面板样式
- 属性分组样式
- 输入控件样式
- 特殊控件（选项列表、标签页管理、表格编辑）样式

#### 4. 代码预览样式
- 深色主题代码高亮
- 可折叠面板
- 浮动控制按钮

## 事件系统

### 鼠标事件
1. **点击事件**：选择组件、激活标签页、点击按钮
2. **拖放事件**：开始拖拽、拖拽过程、拖放结束
3. **悬停事件**：组件悬停高亮、容器高亮

### 键盘事件
1. **删除键**：删除选中组件
2. **ESC键**：取消选择

### 表单事件
1. **输入事件**：属性值变更、选项编辑、数据更新
2. **变更事件**：下拉框选择、复选框状态变更
3. **保存事件**：属性保存、代码更新

## 性能优化

### 1. 防抖机制
- 代码预览更新使用 100ms 防抖延迟
- 减少频繁 DOM 操作带来的性能开销

### 2. 事件委托
- 使用事件委托处理组件点击和拖放
- 减少事件监听器数量

### 3. 缓存策略
- 缓存 DOM 元素引用
- 减少重复查询 DOM

### 4. 批量更新
- 属性变更时批量更新组件状态
- 减少重复渲染

## 使用指南

### 基本操作流程
1. **选择平台样式**：在顶部工具栏选择目标平台（Windows/macOS/Linux）
2. **拖放组件**：从左侧组件面板拖拽组件到设计画布
3. **编辑属性**：点击组件，在右侧属性面板编辑属性
4. **保存属性**：点击"保存"按钮应用属性更改
5. **生成代码**：在底部代码预览区查看生成的 HTML 代码
6. **导出设计**：使用"导出 HTML"按钮获取完整代码

### 高级功能
1. **Grid布局**：
   - 将组件拖放到 Grid 容器中
   - 编辑 row、col、rowspan、colspan、align 属性
   - 组件自动按网格布局排列

2. **标签页设计**：
   - 添加 Tab 容器组件
   - 在属性面板管理标签页
   - 将组件拖放到特定标签页

3. **表格设计**：
   - 添加 Table 组件
   - 设置列标题和列类型
   - 编辑表格数据

### 快捷键
- `Del`：删除选中组件
- `Esc`：取消选择
- 拖拽 + `Shift`：强制添加到容器

## 代码结构示例

### 生成的 GUI 标签代码
```html
<window title="登录窗口" size="400,300" centered="true" margined="true">
    <grid padded="true">
        <label row="0" col="0">用户名:</label>
        <input row="0" col="1" placeholder="请输入用户名" stretchy="true" />
        
        <label row="1" col="0">密码:</label>
        <password row="1" col="1" placeholder="请输入密码" stretchy="true" />
        
        <button row="2" col="0" colspan="2" stretchy="true">登录</button>
    </grid>
</window>
```

### 生成的类 HTML 标签代码
```html
<div title="登录窗口" size="400,300" centered="true" margined="true">
    <div padded="true">
        <label row="0" col="0">用户名:</label>
        <input row="0" col="1" placeholder="请输入用户名" stretchy="true" />
        
        <label row="1" col="0">密码:</label>
        <input type="password" row="1" col="1" placeholder="请输入密码" stretchy="true" />
        
        <button row="2" col="0" colspan="2" stretchy="true">登录</button>
    </div>
</div>
```

## 移植注意事项

### 架构要点
1. **组件数据模型**：保持相同的组件数据结构
2. **渲染管道**：实现类似的组件渲染逻辑
3. **事件系统**：确保拖放、选择、属性编辑事件正常工作
4. **代码生成**：保持 HTML 生成逻辑的一致性

### 关键技术
1. **拖放 API**：使用 HTML5 Drag and Drop API
2. **CSS Grid**：实现 Grid 布局的核心技术
3. **事件委托**：提高事件处理效率的关键
4. **防抖机制**：优化性能的重要策略

### 兼容性考虑
1. **浏览器支持**：确保支持现代浏览器
2. **移动设备**：考虑触屏设备的手势支持
3. **可访问性**：添加适当的 ARIA 标签

## 已知限制

1. **性能限制**：组件数量过多时可能影响性能
2. **浏览器兼容性**：依赖现代浏览器特性
3. **移动设备**：触屏设备的拖放体验有待优化
4. **复杂布局**：极复杂的嵌套布局可能需要手动调整

## 扩展建议

### 功能扩展
1. **撤销/重做**：实现操作历史记录
2. **组件对齐**：添加对齐辅助线和对齐工具
3. **响应式设计**：添加不同屏幕尺寸的预览
4. **模板系统**：支持保存和加载设计模板

### 技术优化
1. **虚拟滚动**：大量组件时的性能优化
2. **Web Workers**：将代码生成移出主线程
3. **本地存储**：自动保存设计草稿
4. **协作功能**：实时协作设计

---

*本文档基于 `designer.html` 和 `designer.css` 的完整源码分析编写，涵盖所有功能和实现细节，可用于 100% 完整移植。*