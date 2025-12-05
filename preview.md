# html
~~~ html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>libui-ng 完整控件库</title>
    <link rel="stylesheet" href="libui-ng-complete.css">
</head>
<body class="windows">
    <div class="platform-selector">
        <button onclick="switchPlatform('windows')">Windows</button>
        <button onclick="switchPlatform('macos')">macOS</button>
        <button onclick="switchPlatform('linux')">Linux</button>
    </div>
 
    <div class="ui-tab">
        <div class="ui-tab-header">
            <button class="ui-tab-button active">输入控件</button>
            <button class="ui-tab-button">按钮控件</button>
            <button class="ui-tab-button">数据显示</button>
            <button class="ui-tab-button">选择器</button>
        </div>
        
        <div class="ui-tab-content">
            <div class="ui-form padded">
                <div class="form-row">
                    <label class="form-label">输入框:</label>
                    <input type="text" class="ui-entry" placeholder="请输入文本">
                </div>
                
                <div class="form-row">
                    <label class="form-label">多行输入:</label>
                    <textarea class="ui-multiline-entry" rows="3" placeholder="多行文本输入..."></textarea>
                </div>
                
                <div class="form-row">
                    <label class="form-label">下拉框:</label>
                    <select class="ui-combobox">
                        <option>选项 1</option>
                        <option>选项 2</option>
                        <option>选项 3</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label class="form-label">数字输入:</label>
                    <div class="ui-spinbox">
                        <input type="number" value="0" min="0" max="100">
                        <button>-</button>
                        <button>+</button>
                    </div>
                </div>
                
                <div class="form-row">
                    <label class="form-label">进度条:</label>
                    <div class="ui-progress-bar" style="width: 200px;">
                        <div class="fill" style="width: 60%;"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <label class="form-label">滑块:</label>
                    <div class="ui-slider">
                        <input type="range" min="0" max="100" value="50">
                        <span class="tooltip">50</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
~~~

# libui-ng-complete.css

~~~ css
/* 通用Box容器样式 */
.ui-box {
    display: flex;
    gap: 8px;
}
 
.ui-box.vertical {
    flex-direction: column;
}
 
.ui-box.horizontal {
    flex-direction: row;
}
 
.ui-box.padded {
    padding: 8px;
}
 
.ui-box .stretchy {
    flex: 1;
    min-width: 0;
    min-height: 0;
}
 
.ui-box .fixed {
    flex: 0;
}
 
/* 平台差异 */
.windows .ui-box {
    gap: 6px;
}
 
.macos .ui-box {
    gap: 10px;
}
 
.linux .ui-box {
    gap: 8px;
}

.ui-grid {
    display: grid;
    gap: 8px;
    align-items: start;
}
 
.ui-grid.padded {
    padding: 10px;
}
 
/* 模拟libui-ng的网格布局逻辑 */
.ui-grid .form-label {
    grid-column: 1;
    align-self: center;
    margin-right: 8px;
}
 
.ui-grid .form-control {
    grid-column: 2;
    min-width: 200px;
}
 
.windows .ui-grid {
    gap: 6px;
}
 
.macos .ui-grid {
    gap: 10px;
}
 
.linux .ui-grid {
    gap: 8px;
}

.ui-form {
    display: grid;
    gap: 8px;
    align-items: center;
}
 
.ui-form.padded {
    padding: 12px;
}
 
.ui-form .form-row {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 8px;
    align-items: center;
}
 
.ui-form .form-label {
    font-weight: 500;
    min-width: 80px;
}
 
.ui-form .form-control {
    min-width: 200px;
}
 
/* 平台特定样式 */
.windows .ui-form .form-label {
    font-size: 14px;
    color: #323130;
}
 
.macos .ui-form .form-label {
    font-size: 13px;
    color: #1d1d1f;
    font-weight: 400;
}
 
.linux .ui-form .form-label {
    font-size: 14px;
    color: #2e3436;
}

.ui-tab {
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    border-radius: var(--border-radius, 6px);
    background: white;
}
 
.ui-tab-header {
    display: flex;
    border-bottom: 1px solid #ccc;
    background: #f8f9fa;
}
 
.ui-tab-button {
    padding: 8px 16px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}
 
.ui-tab-button.active {
    border-bottom-color: var(--accent-color, #0078d4);
    background: white;
}
 
.ui-tab-content {
    flex: 1;
    padding: 16px;
    min-height: 200px;
}
 
/* 平台差异 */
.windows .ui-tab {
    border-radius: 4px;
}
 
.windows .ui-tab-header {
    background: #f3f2f1;
}
 
.windows .ui-tab-button.active {
    border-bottom-color: #0078d4;
}
 
.macos .ui-tab {
    border-radius: 8px;
    border-color: #d2d2d7;
}
 
.macos .ui-tab-header {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
}
 
.macos .ui-tab-button.active {
    border-bottom-color: #007aff;
}
 
.linux .ui-tab {
    border-radius: 6px;
    border-color: #cdc7c2;
}
 
.linux .ui-tab-header {
    background: #f6f5f4;
}
 
.linux .ui-tab-button.active {
    border-bottom-color: #3584e4;
}

.ui-group {
    border: 1px solid #ccc;
    border-radius: var(--border-radius, 6px);
    background: white;
}
 
.ui-group-title {
    padding: 8px 12px;
    font-weight: 500;
    background: #f8f9fa;
    border-bottom: 1px solid #ccc;
}
 
.ui-group-content {
    padding: 12px;
}
 
.ui-group.margined .ui-group-content {
    padding: 16px;
}
 
/* 平台差异 */
.windows .ui-group {
    border-radius: 4px;
    border-color: #d1d1d1;
}
 
.windows .ui-group-title {
    font-size: 14px;
    background: #f3f2f1;
    font-weight: 600;
}
 
.macos .ui-group {
    border-radius: 8px;
    border-color: #d2d2d7;
}
 
.macos .ui-group-title {
    font-size: 13px;
    background: rgba(255, 255, 255, 0.8);
    font-weight: 500;
    backdrop-filter: blur(20px);
}
 
.linux .ui-group {
    border-radius: 6px;
    border-color: #cdc7c2;
}
 
.linux .ui-group-title {
    font-size: 14px;
    background: #f6f5f4;
    font-weight: 500;
}

.ui-scrollview {
    border: 1px solid #ccc;
    border-radius: var(--border-radius, 6px);
    overflow: auto;
    background: white;
}
 
.ui-scrollview-content {
    padding: 8px;
}
 
/* 自定义滚动条 */
.ui-scrollview::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
 
.ui-scrollview::-webkit-scrollbar-track {
    background: #f1f1f1;
}
 
.ui-scrollview::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}
 
.ui-scrollview::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
 
/* 平台差异 */
.windows .ui-scrollview {
    border-radius: 4px;
    border-color: #d1d1d1;
}
 
.windows .ui-scrollview::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}
 
.macos .ui-scrollview {
    border-radius: 8px;
    border-color: #d2d2d7;
}
 
.macos .ui-scrollview::-webkit-scrollbar {
    width: 10px;
    height: 10px;
    background: transparent;
}
 
.macos .ui-scrollview::-webkit-scrollbar-track {
    background: transparent;
}
 
.macos .ui-scrollview::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}
 
.linux .ui-scrollview {
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-entry {
    height: var(--entry-height, 22px);
    padding: 4px 8px;
    border: 1px solid #d1d1d1;
    border-radius: var(--border-radius, 4px);
    font-size: 14px;
    background: white;
    transition: border-color 0.1s;
}
 
.ui-entry:focus {
    border-color: var(--focus-color, #0078d4);
    outline: 1px solid var(--focus-color, #0078d4);
    outline-offset: -2px;
}
 
/* 平台差异 */
.macos .ui-entry {
    height: 28px;
    padding: 3px 8px;
    font-size: 13px;
    border-radius: 6px;
    border-color: #d2d2d7;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
}
 
.macos .ui-entry:focus {
    border-color: #007aff;
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
    outline: none;
}
 
.linux .ui-entry {
    height: 32px;
    padding: 5px 8px;
    border-radius: 6px;
    border-color: #cdc7c2;
}
 
.linux .ui-entry:focus {
    border-color: #3584e4;
    box-shadow: inset 0 0 0 2px #3584e4;
    outline: none;
}

.ui-multiline-entry {
    padding: 6px 8px;
    border: 1px solid #d1d1d1;
    border-radius: var(--border-radius, 4px);
    font-size: 14px;
    font-family: inherit;
    background: white;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.1s;
}
 
.macos .ui-multiline-entry {
    border-radius: 6px;
    border-color: #d2d2d7;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    font-size: 13px;
}
 
.linux .ui-multiline-entry {
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-combobox {
    height: 22px;
    padding: 0 8px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 6px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 28px;
}
 
.macos .ui-combobox {
    height: 26px;
    border-radius: 6px;
    border-color: #d2d2d7;
    font-size: 13px;
}
 
.linux .ui-combobox {
    height: 32px;
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-editable-combobox {
    display: flex;
    height: 22px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    overflow: hidden;
}
 
.ui-editable-combobox input {
    flex: 1;
    border: none;
    padding: 0 8px;
    font-size: 14px;
    outline: none;
}
 
.ui-editable-combobox button {
    width: 20px;
    border: none;
    background: #f0f0f0;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 12px;
}

.ui-spinbox {
    display: flex;
    height: 22px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    overflow: hidden;
}
 
.ui-spinbox input {
    flex: 1;
    border: none;
    padding: 0 8px;
    font-size: 14px;
    text-align: center;
    outline: none;
}
 
.ui-spinbox button {
    width: 20px;
    border: none;
    background: #f0f0f0;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
}
 
.ui-spinbox button:first-child {
    border-right: 1px solid #e0e0e0;
}
 
.macos .ui-spinbox {
    height: 26px;
    border-radius: 6px;
    border-color: #d2d2d7;
}
 
.linux .ui-spinbox {
    height: 32px;
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-button {
    height: 32px;
    padding: 0 12px;
    border: 1px solid #d1d1d1;
    background: linear-gradient(to bottom, #f3f2f1, #faf9f8);
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.1s;
}
 
.ui-button:hover {
    background: linear-gradient(to bottom, #e8e6e1, #f5f2f0);
    border-color: #c7c6c5;
}
 
.ui-button:active {
    background: linear-gradient(to bottom, #e4e2dd, #edebe9);
}
 
.macos .ui-button {
    height: 30px;
    border-radius: 6px;
    border-color: #d2d2d7;
    background: linear-gradient(to bottom, #0066cc, #0052a3);
    color: white;
    font-size: 13px;
    font-weight: 500;
}
 
.linux .ui-button {
    height: 34px;
    border-radius: 6px;
    border-color: #cdc7c2;
    background: linear-gradient(to bottom, #f6f5f4, #e0dedb);
}

.ui-checkbox {
    display: flex;
    align-items: center;
    height: 24px;
    gap: 6px;
    cursor: pointer;
}
 
.ui-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
    border: 1px solid #d1d1d1;
    border-radius: 2px;
    cursor: pointer;
    margin: 0;
}
 
.ui-checkbox input[type="checkbox"]:checked {
    background: #0078d4;
    border-color: #0078d4;
}
 
.ui-checkbox label {
    font-size: 14px;
    color: #323130;
    user-select: none;
}
 
.macos .ui-checkbox {
    height: 20px;
    gap: 8px;
}
 
.macos .ui-checkbox input[type="checkbox"] {
    border-radius: 4px;
    border-color: #d2d2d7;
}
 
.macos .ui-checkbox input[type="checkbox"]:checked {
    background: #007aff;
    border-color: #007aff;
}
 
.linux .ui-checkbox {
    height: 22px;
}
 
.linux .ui-checkbox input[type="checkbox"] {
    border-radius: 3px;
    border-color: #cdc7c2;
}

.ui-radio-buttons {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
 
.ui-radio-buttons label {
    display: flex;
    align-items: center;
    height: 24px;
    gap: 6px;
    cursor: pointer;
    font-size: 14px;
    color: #323130;
}
 
.ui-radio-buttons input[type="radio"] {
    width: 16px;
    height: 16px;
    border: 1px solid #d1d1d1;
    border-radius: 50%;
    margin: 0;
    cursor: pointer;
}
 
.ui-radio-buttons input[type="radio"]:checked {
    background: #0078d4;
    border-color: #0078d4;
}
 
.macos .ui-radio-buttons label {
    height: 20px;
    gap: 8px;
    font-size: 13px;
    color: #1d1d1f;
}
 
.macos .ui-radio-buttons input[type="radio"] {
    border-color: #d2d2d7;
}
 
.macos .ui-radio-buttons input[type="radio"]:checked {
    background: #007aff;
    border-color: #007aff;
}

.ui-label {
    font-size: 14px;
    color: #323130;
    line-height: 1.4;
    user-select: none;
}
 
.ui-label.multiline {
    white-space: pre-wrap;
    line-height: 1.6;
}
 
.macos .ui-label {
    font-size: 13px;
    color: #1d1d1f;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro', sans-serif;
}
 
.linux .ui-label {
    font-size: 14px;
    color: #2e3436;
    font-family: 'Cantarell', 'Ubuntu', system-ui, sans-serif;
}

.ui-progress-bar {
    height: 8px;
    background: #f3f2f1;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}
 
.ui-progress-bar .fill {
    height: 100%;
    background: #0078d4;
    transition: width 0.2s ease;
}
 
.ui-progress-bar.indeterminate .fill {
    width: 30%;
    background: linear-gradient(90deg, transparent, #0078d4, transparent);
    animation: progress-indeterminate 1.5s infinite;
}
 
@keyframes progress-indeterminate {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(400%); }
}
 
.macos .ui-progress-bar {
    height: 6px;
    border-radius: 3px;
    border-color: #d2d2d7;
    background: #f0f0f0;
}
 
.macos .ui-progress-bar .fill {
    background: #007aff;
}
 
.linux .ui-progress-bar {
    height: 8px;
    border-radius: 4px;
    border-color: #cdc7c2;
    background: #f6f5f4;
}
 
.linux .ui-progress-bar .fill {
    background: #3584e4;
}

.ui-slider {
    width: 200px;
    height: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}
 
.ui-slider input[type="range"] {
    flex: 1;
    height: 4px;
    background: #f3f2f1;
    border: 1px solid #d1d1d1;
    border-radius: 2px;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
}
 
.ui-slider input[type="range"]::-webkit-slider-thumb {
    width: 20px;
    height: 20px;
    background: #0078d4;
    border: 1px solid #0078d4;
    border-radius: 50%;
    cursor: pointer;
    -webkit-appearance: none;
    margin-top: -8px;
}
 
.ui-slider input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background: #0078d4;
    border: 1px solid #0078d4;
    border-radius: 50%;
    cursor: pointer;
    border: none;
}
 
.ui-slider .tooltip {
    font-size: 12px;
    color: #323130;
    min-width: 30px;
    text-align: center;
}
 
.macos .ui-slider input[type="range"]::-webkit-slider-thumb {
    background: #007aff;
    border-color: #007aff;
}
 
.linux .ui-slider input[type="range"]::-webkit-slider-thumb {
    background: #3584e4;
    border-color: #3584e4;
}


.ui-separator {
    border: none;
    background: #d1d1d1;
}
 
.ui-separator.horizontal {
    height: 1px;
    width: 100%;
    margin: 8px 0;
}
 
.ui-separator.vertical {
    width: 1px;
    height: 100%;
    margin: 0 8px;
}
 
.macos .ui-separator {
    background: #d2d2d7;
}
 
.linux .ui-separator {
    background: #cdc7c2;
}

.ui-datetime-picker {
    display: flex;
    height: 22px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    background: white;
    overflow: hidden;
}
 
.ui-datetime-picker select {
    flex: 1;
    border: none;
    padding: 0 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
}
 
.ui-datetime-picker select:not(:last-child) {
    border-right: 1px solid #e0e0e0;
}
 
.macos .ui-datetime-picker {
    height: 26px;
    border-radius: 6px;
    border-color: #d2d2d7;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
}
 
.linux .ui-datetime-picker {
    height: 32px;
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-color-button {
    display: flex;
    align-items: center;
    height: 24px;
    padding: 4px 8px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    gap: 6px;
    font-size: 14px;
}
 
.ui-color-button .color-preview {
    width: 16px;
    height: 16px;
    border: 1px solid #999;
    border-radius: 2px;
}
 
.macos .ui-color-button {
    height: 22px;
    border-radius: 6px;
    border-color: #d2d2d7;
}
 
.linux .ui-color-button {
    height: 26px;
    border-radius: 6px;
    border-color: #cdc7c2;
}

.ui-font-button {
    display: flex;
    align-items: center;
    height: 24px;
    padding: 4px 8px;
    border: 1px solid #d1d1d1;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    font-size: 14px;
}
 
.macos .ui-font-button {
    height: 22px;
    border-radius: 6px;
    border-color: #d2d2d7;
    font-size: 13px;
}
 
.linux .ui-font-button {
    height: 26px;
    border-radius: 6px;
    border-color: #cdc7c2;
}
~~~

# js
~~~ js
class LibuiNGEditor {
    constructor() {
        this.platform = 'windows';
        this.components = [];
        this.selectedComponent = null;
    }
 
    // 创建组件
    createComponent(type, props = {}) {
        const component = {
            id: Date.now(),
            type,
            props: { ...this.getDefaultProps(type), ...props },
            children: []
        };
        this.components.push(component);
        return component;
    }
 
    // 获取平台默认属性
    getDefaultProps(type) {
        const defaults = {
            entry: {
                width: this.platform === 'windows' ? 107 : 
                       this.platform === 'macos' ? 96 : 'auto',
                height: this.platform === 'windows' ? 22 : 
                        this.platform === 'macos' ? 28 : 32
            },
            button: {
                width: 'auto',
                height: this.platform === 'windows' ? 32 : 
                        this.platform === 'macos' ? 30 : 34
            },
            checkbox: {
                width: 'auto',
                height: this.platform === 'windows' ? 24 : 
                        this.platform === 'macos' ? 20 : 22
            }
        };
        return defaults[type] || {};
    }
 
    // 生成HTML
    generateHTML() {
        return this.components.map(comp => 
            this.generateComponentHTML(comp)
        ).join('');
    }
 
    generateComponentHTML(component) {
        const { type, props, children } = component;
        switch(type) {
            case 'box':
                return `<div class="ui-box ${props.orientation || 'horizontal'} ${props.padded ? 'padded' : ''}">
                    ${children.map(child => this.generateComponentHTML(child)).join('')}
                </div>`;
            case 'entry':
                return `<input type="text" class="ui-entry" 
                    style="width: ${props.width}px; height: ${props.height}px"
                    placeholder="${props.placeholder || ''}">`;
            case 'button':
                return `<button class="ui-button" 
                    style="width: ${props.width}; height: ${props.height}px">
                    ${props.text || '按钮'}
                </button>`;
            // 更多组件类型...
            default:
                return '';
        }
    }
}
~~~