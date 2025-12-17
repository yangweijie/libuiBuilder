class LibuiBuilderDesigner {
    constructor() {
        this.platform = 'windows';
        this.components = [];
        this.selectedComponent = null;
        this.componentIdCounter = 0;
        this.draggedComponent = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
        this.updateCodePreview();
        this.setupDebugShortcuts();
    }
    setupDebugShortcuts() {
        document.addEventListener('keydown', (e) => {
            // 按 Ctrl+Shift+D 来调试Grid布局
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.debugGridLayout();
            }
        });
    }
    
    setupEventListeners() {
        // 平台切换
        document.querySelectorAll('.platform-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.platform-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.platform = e.target.dataset.platform;
                document.body.className = this.platform;
                
                // 更新所有组件的样式
                this.updateComponentStyles();
                this.updateCodePreview();
            });
        });
        
        // 工具栏按钮
        document.getElementById('saveBtn').addEventListener('click', () => this.save());
        document.getElementById('exportBtn').addEventListener('click', () => this.showExportModal());
        document.getElementById('previewBtn').addEventListener('click', () => this.preview());
        document.getElementById('copyCodeBtn').addEventListener('click', () => this.copyCode());
        document.getElementById('toggleCodeBtn').addEventListener('click', () => this.toggleCodePreview());
        
        // 模态对话框
        document.getElementById('closeModal').addEventListener('click', () => this.hideExportModal());
        document.getElementById('cancelExport').addEventListener('click', () => this.hideExportModal());
        document.getElementById('confirmExport').addEventListener('click', () => this.exportFile());
    }
    
    setupDragAndDrop() {
        // 组件拖拽开始
        document.querySelectorAll('.component-item').forEach(item => {
            item.addEventListener('dragstart', (e) => {
                this.draggedComponent = e.target.dataset.component;
                e.dataTransfer.effectAllowed = 'copy';
                e.target.style.opacity = '0.5';
            });
            
            item.addEventListener('dragend', (e) => {
                e.target.style.opacity = '1';
                this.draggedComponent = null;
            });
        });
        
        // 设计画布拖拽事件
        const canvas = document.getElementById('designCanvas');
        
        canvas.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            
            if (this.draggedComponent) {
                // 检查是否悬停在容器组件上
                const targetComponent = this.getComponentAtPosition(e.clientX, e.clientY);
                if (targetComponent && this.isContainerComponent(targetComponent.type)) {
                    this.highlightContainer(targetComponent);
                } else {
                    canvas.classList.add('drag-over');
                    this.clearContainerHighlights();
                }
            }
        });
        
        canvas.addEventListener('dragleave', (e) => {
            if (e.target === canvas) {
                canvas.classList.remove('drag-over');
                this.clearContainerHighlights();
            }
        });
        
        canvas.addEventListener('drop', (e) => {
            e.preventDefault();
            canvas.classList.remove('drag-over');
            this.clearContainerHighlights();
            
            if (this.draggedComponent) {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // 检查是否放置在容器组件内
                const targetComponent = this.getComponentAtPosition(e.clientX, e.clientY);
                if (targetComponent && this.isContainerComponent(targetComponent.type)) {
                    this.addComponentToContainer(this.draggedComponent, targetComponent, x, y);
                } else {
                    this.addComponent(this.draggedComponent, x, y);
                }
            }
        });
    }
    
    addComponent(type, x, y, parentComponent = null) {
        const component = {
            id: `component_${++this.componentIdCounter}`,
            type: type,
            x: x,
            y: y,
            props: this.getDefaultProps(type),
            children: [],
            parent: parentComponent ? parentComponent.id : null
        };
        
        // 如果是窗口组件且没有父容器，自动居中
        if (type === 'window' && !parentComponent) {
            const canvas = document.getElementById('designCanvas');
            const canvasRect = canvas.getBoundingClientRect();
            
            // 解析默认尺寸
            let width = 400;
            let height = 300;
            if (component.props.size) {
                const [w, h] = component.props.size.split(',').map(s => parseInt(s.trim()));
                if (!isNaN(w)) width = w;
                if (!isNaN(h)) height = h;
            }
            
            // 计算居中位置
            component.x = Math.max(50, (canvasRect.width - width) / 2);
            component.y = Math.max(50, (canvasRect.height - height) / 2);
        }
        
        if (parentComponent) {
            // 添加到父容器的children数组
            parentComponent.children.push(component);
        } else {
            // 添加到根级别
            this.components.push(component);
        }
        
        // 清除占位符
        const placeholder = document.querySelector('.canvas-placeholder');
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        this.renderComponent(component, parentComponent);
        this.updateCodePreview();
    }
    
    addComponentToContainer(type, containerComponent, x, y) {
        this.addComponent(type, x, y, containerComponent);
    }
    
    isContainerComponent(type) {
        return ['window', 'grid', 'hbox', 'vbox', 'tab'].includes(type);
    }
    
    getComponentAtPosition(clientX, clientY) {
        const elements = document.elementsFromPoint(clientX, clientY);
        for (let element of elements) {
            const componentEl = element.closest('.designer-component');
            if (componentEl) {
                const componentId = componentEl.dataset.componentId;
                return this.findComponentById(componentId);
            }
        }
        return null;
    }
    
    findComponentById(id) {
        // 在根级别查找
        let component = this.components.find(c => c.id === id);
        if (component) return component;
        
        // 递归在子组件中查找
        for (let rootComponent of this.components) {
            component = this.findInChildren(rootComponent, id);
            if (component) return component;
        }
        
        return null;
    }
    
    findInChildren(parent, id) {
        for (let child of parent.children) {
            if (child.id === id) return child;
            
            const found = this.findInChildren(child, id);
            if (found) return found;
        }
        return null;
    }
    
    highlightContainer(container) {
        this.clearContainerHighlights();
        const element = document.querySelector(`[data-component-id="${container.id}"]`);
        if (element) {
            element.classList.add('drag-target');
            // 拖拽时隐藏占位符文字
            const placeholder = element.querySelector('.container-placeholder');
            if (placeholder) {
                placeholder.classList.add('drag-target-placeholder');
            }
        }
    }
    
    clearContainerHighlights() {
        document.querySelectorAll('.drag-target').forEach(el => {
            el.classList.remove('drag-target');
        });
        document.querySelectorAll('.drag-target-placeholder').forEach(el => {
            el.classList.remove('drag-target-placeholder');
        });
    }
    
    getDefaultProps(type) {
        const defaults = {
            window: {
                title: '窗口',
                size: '400,300',
                centered: 'true',
                margined: 'true'
            },
            grid: {
                padded: 'true'
            },
            hbox: {
                padded: 'false'
            },
            vbox: {
                padded: 'false'
            },
            input: {
                type: 'text',
                placeholder: '请输入文本',
                expand: 'horizontal'
            },
            textarea: {
                placeholder: '请输入多行文本',
                rows: '3',
                expand: 'horizontal'
            },
            password: {
                type: 'password',
                placeholder: '请输入密码',
                expand: 'horizontal'
            },
            button: {
                text: '按钮'
            },
            label: {
                text: '标签文本'
            },
            checkbox: {
                text: '复选框',
                checked: 'false'
            },
            combobox: {
                selected: '0'
            },
            spinbox: {
                min: '0',
                max: '100',
                value: '0'
            },
            slider: {
                min: '0',
                max: '100',
                value: '50'
            },
            progressbar: {
                value: '60'
            },
            separator: {
                orientation: 'horizontal'
            },
            table: {
                columns: '列1,列2',
                data: '数据1,数据2'
            }
        };
        
        return defaults[type] || {};
    }
    
    renderComponent(component, parentComponent = null) {
        let targetElement;
        
        if (parentComponent) {
            // 对于容器组件，需要找到其内部的容器区域
            const parentEl = document.querySelector(`[data-component-id="${parentComponent.id}"]`);
            if (parentEl) {
                // 对于不同类型的容器，找到对应的容器区域
                if (parentComponent.type === 'window') {
                    // Window 组件使用 .window-content
                    targetElement = parentEl.querySelector('.window-content');
                    // 如果找不到window-content，尝试使用component-content
                    if (!targetElement) {
                        targetElement = parentEl.querySelector('.component-content');
                    }
                } else {
                    // 其他容器使用 .container-content
                    targetElement = parentEl.querySelector('.container-content');
                    if (!targetElement) {
                        // 如果没有找到，使用 component-content
                        targetElement = parentEl.querySelector('.component-content');
                    }
                }
            }
        } else {
            targetElement = document.getElementById('designCanvas');
        }
            
        if (!targetElement) {
            console.warn('无法找到目标元素', component, parentComponent);
            return;
        }
        
        // 检查组件是否已经存在于DOM中，如果是则先移除
        const existingElement = document.querySelector(`[data-component-id="${component.id}"]`);
        if (existingElement) {
            existingElement.remove();
        }
        
        const element = this.createComponentElement(component);
        targetElement.appendChild(element);
        
        // 添加子组件后，隐藏父容器的占位符文字
        if (parentComponent) {
            this.updateContainerPlaceholder(parentComponent);
        }
    }
    
    updateContainerPlaceholder(containerComponent) {
        const parentEl = document.querySelector(`[data-component-id="${containerComponent.id}"]`);
        if (parentEl) {
            const placeholder = parentEl.querySelector('.container-placeholder');
            if (placeholder) {
                const hasChildren = containerComponent.children.length > 0;
                placeholder.style.display = hasChildren ? 'none' : 'block';
                
                // 确保占位符在正确的位置（对于某些容器可能需要调整z-index）
                if (hasChildren) {
                    placeholder.style.zIndex = '1';
                } else {
                    placeholder.style.zIndex = '1';
                }
            }
        }
    }
    
    // 调试Grid布局的函数
    debugGridLayout() {
        console.log('=== Grid Layout Debug ===');
        
        // 查找所有Grid组件
        this.components.forEach(component => {
            if (component.type === 'grid') {
                console.log(`Grid Component: ${component.id}`);
                console.log(`- Children count: ${component.children.length}`);
                
                const gridElement = document.querySelector(`[data-component-id="${component.id}"]`);
                if (gridElement) {
                    const gridRect = gridElement.getBoundingClientRect();
                    console.log(`- Grid total width: ${gridRect.width}px`);
                    
                    // 检查Grid的CSS Grid列数
                    const gridContainer = gridElement.querySelector('.ui-grid');
                    if (gridContainer) {
                        const computedStyle = window.getComputedStyle(gridContainer);
                        console.log(`- Grid template columns: ${computedStyle.gridTemplateColumns}`);
                        
                        // 检查每个子组件的grid-column属性
                        component.children.forEach((child, idx) => {
                            const childElement = document.querySelector(`[data-component-id="${child.id}"]`);
                            if (childElement) {
                                const childRect = childElement.getBoundingClientRect();
                                const childComputedStyle = window.getComputedStyle(childElement);
                                
                                console.log(`  Child ${idx} (${child.type}):`);
                                console.log(`  - ID: ${child.id}`);
                                console.log(`  - Layout: row=${child.layout?.row}, col=${child.layout?.col}, rowspan=${child.layout?.rowspan}, colspan=${child.layout?.colspan}`);
                                console.log(`  - Width: ${childRect.width}px`);
                                console.log(`  - Grid column: ${childComputedStyle.gridColumn}`);
                                console.log(`  - Grid row: ${childComputedStyle.gridRow}`);
                            }
                        });
                    }
                }
            }
        });
        
        // 输出所有组件信息
        console.log('=== All Components ===');
        this.components.forEach(component => {
            console.log(`${component.type} (${component.id}): parent=${component.parent || 'none'}`);
            if (component.layout) {
                console.log(`  Layout: row=${component.layout.row}, col=${component.layout.col}, rowspan=${component.layout.rowspan}, colspan=${component.layout.colspan}`);
            }
            if (component.children && component.children.length > 0) {
                console.log(`  Children: ${component.children.length}`);
            }
        });
    }
    
    createComponentElement(component) {
        const div = document.createElement('div');
        div.className = 'designer-component';
        div.dataset.componentId = component.id;
        div.dataset.componentType = component.type;
        if (component.parent) {
            div.dataset.parent = component.parent;
        }
        
        // 只有根级别组件才使用绝对定位
        if (!component.parent) {
            div.style.position = 'absolute';
            div.style.left = component.x + 'px';
            div.style.top = component.y + 'px';
        } else {
            // 获取父组件
            const parentComponent = this.findComponentById(component.parent);
            
            // 如果父组件是Grid，使用Grid定位
            if (parentComponent && parentComponent.type === 'grid' && component.layout) {
                div.style.position = 'relative';
                div.style.margin = '0';
                div.style.display = 'block';
                div.style.border = '1px solid #ddd';
                div.style.borderRadius = '4px';
                div.style.padding = '4px';
                div.style.background = 'white';
                div.style.zIndex = '20';
                div.style.minWidth = '0';
                div.style.maxWidth = '100%';
                div.style.boxSizing = 'border-box';
                
                // 设置Grid定位
                const row = component.layout.row || 0;
                const col = component.layout.col || 0;
                const rowspan = component.layout.rowspan || 1;
                const colspan = component.layout.colspan || 1;
                
                div.style.gridColumn = `${col + 1} / ${col + 1 + colspan}`;
                div.style.gridRow = `${row + 1} / ${row + 1 + rowspan}`;
                
                // 设置对齐方式
                if (component.layout.align) {
                    if (component.layout.align.includes(',')) {
                        // 分离的对齐值 (水平,垂直)
                        const [hAlign, vAlign] = component.layout.align.split(',');
                        div.style.justifySelf = hAlign.trim() || 'stretch';
                        div.style.alignSelf = vAlign.trim() || 'stretch';
                    } else {
                        // 单个值，水平和垂直相同
                        div.style.justifySelf = component.layout.align;
                        div.style.alignSelf = component.layout.align;
                    }
                } else {
                    div.style.justifySelf = 'stretch';
                    div.style.alignSelf = 'stretch';
                }
            } else {
                // 非Grid子组件使用相对定位
                div.style.position = 'relative';
                div.style.margin = '0';
                div.style.display = 'block';
                div.style.flex = 'none';
                div.style.border = '1px solid #ddd';
                div.style.borderRadius = '4px';
                div.style.padding = '4px';
                div.style.background = 'white';
                div.style.zIndex = '20';
                div.style.minWidth = '0';
                div.style.maxWidth = '100%';
                div.style.boxSizing = 'border-box';
            }
        }
        
        // 创建组件内容
        const content = this.createComponentContent(component);
        div.appendChild(content);
        
        // 创建控制按钮
        const controls = this.createComponentControls(component);
        div.appendChild(controls);
        
        // 添加事件监听
        div.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectComponent(component);
        });
        
        return div;
    }
    
    createComponentContent(component) {
        const content = document.createElement('div');
        content.className = 'component-content';
        
        switch (component.type) {
            case 'window':
                // 解析尺寸属性
                let width = 400;
                let height = 300;
                if (component.props.size) {
                    const [w, h] = component.props.size.split(',').map(s => parseInt(s.trim()));
                    if (!isNaN(w)) width = w;
                    if (!isNaN(h)) height = h;
                }
                
                // 根据margined属性设置内边距
                const padding = component.props.margined === 'true' ? '16px' : '8px';
                
                content.innerHTML = `
                    <div class="ui-window" style="width: ${width}px; height: ${height}px; border: 1px solid #ccc; background: white; max-width: 100%; overflow: hidden; position: relative;">
                        <div style="padding: 8px; border-bottom: 1px solid #ccc; background: #f8f9fa;">
                            ${component.props.title || '窗口'}
                        </div>
                        <div style="padding: ${padding}; min-height: ${height - 60}px; width: 100%; box-sizing: border-box;" class="window-content">
                            <!-- 占位符文字，只在无子元素时显示 -->
                            <div class="container-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #999; font-weight: 500; font-size: 14px; pointer-events: none; z-index: 1; ${component.children.length > 0 ? 'display: none;' : ''}">
                                窗口内容区域
                            </div>
                            <!-- 子组件将在这里动态添加 -->
                        </div>
                    </div>
                `;
                break;
                
            case 'grid':
                // 计算网格的行列数
                let maxRow = 1;
                let maxCol = 1; // 默认至少1列
                
                if (component.children && component.children.length > 0) {
                    component.children.forEach(child => {
                        if (child.layout) {
                            const row = child.layout.row || 0;
                            const col = child.layout.col || 0;
                            const rowspan = child.layout.rowspan || 1;
                            const colspan = child.layout.colspan || 1;
                            
                            maxRow = Math.max(maxRow, row + rowspan);
                            maxCol = Math.max(maxCol, col + colspan);
                        }
                    });
                }
                
                // 创建列定义数组，每列宽度设为1fr
                const columnDefinitions = Array(maxCol).fill('1fr').join(' ');
                
                content.innerHTML = `
                    <div class="ui-grid ${component.props.padded === 'true' ? 'padded' : ''}" style="min-width: 600px; width: 100%; border: 2px dashed #0078d4; background: rgba(0, 120, 212, 0.05); display: grid; grid-template-columns: ${columnDefinitions}; grid-template-rows: repeat(${maxRow}, minmax(60px, auto)); gap: 8px; padding: 8px; position: relative;">
                        <!-- 占位符文字，只在无子元素时显示 -->
                        <div class="container-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #0078d4; font-weight: 500; font-size: 14px; pointer-events: none; z-index: 1; grid-column: 1 / -1; ${component.children.length > 0 ? 'display: none;' : ''}">
                            Grid 布局容器
                        </div>
                        <!-- Grid子组件将直接附加到Grid容器，而不是嵌套容器 -->
                        <div class="container-content" style="display: contents;">
                            <!-- Grid子组件将被添加到这里，但会成为Grid的直接子项 -->
                        </div>
                    </div>
                `;
                break;
                
            case 'hbox':
                const hboxPadding = component.props.padded === 'true' ? '8px' : '0px';
                content.innerHTML = `
                    <div class="ui-box horizontal" style="min-width: 200px; min-height: 60px; border: 2px dashed #0078d4; background: rgba(0, 120, 212, 0.05); display: flex; gap: 8px; padding: ${hboxPadding}; align-items: center; width: 100%; position: relative; box-sizing: border-box;">
                        <!-- 占位符文字，只在无子元素时显示 -->
                        <div class="container-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #0078d4; font-weight: 500; font-size: 14px; pointer-events: none; z-index: 1; ${component.children.length > 0 ? 'display: none;' : ''}">
                            HBox 容器
                        </div>
                        <!-- 子组件容器 -->
                        <div class="container-content" style="display: flex; gap: 8px; width: 100%; z-index: 2; align-items: center;">
                            <!-- 子组件将在这里动态添加 -->
                        </div>
                    </div>
                `;
                break;
                
            case 'vbox':
                const vPadding = component.props.padded === 'true' ? '8px' : '0px';
                content.innerHTML = `
                    <div class="ui-box vertical" style="min-width: 200px; min-height: 120px; border: 2px dashed #0078d4; background: rgba(0, 120, 212, 0.05); display: flex; flex-direction: column; gap: 8px; padding: ${vPadding}; width: 100%; position: relative; box-sizing: border-box;">
                        <!-- 占位符文字，只在无子元素时显示 -->
                        <div class="container-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #0078d4; font-weight: 500; font-size: 14px; pointer-events: none; z-index: 1; ${component.children.length > 0 ? 'display: none;' : ''}">
                            VBox 容器
                        </div>
                        <!-- 子组件容器 -->
                        <div class="container-content" style="display: flex; flex-direction: column; gap: 8px; width: 100%; z-index: 2;">
                            <!-- 子组件将在这里动态添加 -->
                        </div>
                    </div>
                `;
                break;
                
            case 'input':
                // 根据stretchy属性决定宽度
                let widthStyle = 'width: 200px; min-width: 0; max-width: 100%; box-sizing: border-box;';
                if (component.props.stretchy === 'true' || component.props.stretchy === 'horizontal') {
                    widthStyle = 'flex: 1; min-width: 80px; max-width: 100%; box-sizing: border-box;';
                }
                
                content.innerHTML = `
                    <input type="text" class="ui-entry" placeholder="${component.props.placeholder || ''}" 
                           style="${widthStyle}">
                `;
                break;
                
            case 'textarea':
                let textareaStyle = 'width: 200px; min-width: 0; max-width: 100%; box-sizing: border-box;';
                if (component.props.stretchy === 'true' || component.props.stretchy === 'horizontal') {
                    textareaStyle = 'flex: 1; min-width: 80px; max-width: 100%; box-sizing: border-box;';
                }
                content.innerHTML = `
                    <textarea class="ui-multiline-entry" placeholder="${component.props.placeholder || ''}" 
                              rows="${component.props.rows || '3'}" style="${textareaStyle}"></textarea>
                `;
                break;
                
            case 'password':
                let passwordStyle = 'width: 200px; min-width: 0; max-width: 100%; box-sizing: border-box;';
                if (component.props.stretchy === 'true' || component.props.stretchy === 'horizontal') {
                    passwordStyle = 'flex: 1; min-width: 80px; max-width: 100%; box-sizing: border-box;';
                }
                content.innerHTML = `
                    <input type="password" class="ui-entry" placeholder="${component.props.placeholder || ''}" 
                           style="${passwordStyle}">
                `;
                break;
                
            case 'button':
                content.innerHTML = `
                    <button class="ui-button">${component.props.text || '按钮'}</button>
                `;
                break;
                
            case 'label':
                content.innerHTML = `
                    <span class="ui-label">${component.props.text || '标签文本'}</span>
                `;
                break;
                
            case 'checkbox':
                content.innerHTML = `
                    <label class="ui-checkbox">
                        <input type="checkbox" ${component.props.checked === 'true' ? 'checked' : ''}>
                        <span>${component.props.text || '复选框'}</span>
                    </label>
                `;
                break;
                
            case 'combobox':
                let comboboxStyle = 'width: 150px; min-width: 0; max-width: 100%; box-sizing: border-box;';
                if (component.props.stretchy === 'true' || component.props.stretchy === 'horizontal') {
                    comboboxStyle = 'flex: 1; min-width: 80px; max-width: 100%; box-sizing: border-box;';
                }
                content.innerHTML = `
                    <select class="ui-combobox" style="${comboboxStyle}">
                        <option>选项 1</option>
                        <option>选项 2</option>
                        <option>选项 3</option>
                    </select>
                `;
                break;
                
            case 'spinbox':
                content.innerHTML = `
                    <div class="ui-spinbox">
                        <input type="number" value="${component.props.value || '0'}" 
                               min="${component.props.min || '0'}" max="${component.props.max || '100'}" readonly>
                        <button disabled>-</button>
                        <button disabled>+</button>
                    </div>
                `;
                break;
                
            case 'slider':
                content.innerHTML = `
                    <div class="ui-slider">
                        <input type="range" value="${component.props.value || '50'}" 
                               min="${component.props.min || '0'}" max="${component.props.max || '100'}" readonly>
                        <span>${component.props.value || '50'}</span>
                    </div>
                `;
                break;
                
            case 'progressbar':
                content.innerHTML = `
                    <div class="ui-progress-bar" style="width: 200px;">
                        <div class="fill" style="width: ${component.props.value || '60'}%;"></div>
                    </div>
                `;
                break;
                
            case 'separator':
                if (component.props.orientation === 'vertical') {
                    content.innerHTML = `<hr class="ui-separator vertical" style="height: 50px;">`;
                } else {
                    content.innerHTML = `<hr class="ui-separator horizontal" style="width: 200px;">`;
                }
                break;
                
            case 'table':
                content.innerHTML = `
                    <table class="mini-table" style="width: 200px;">
                        <tr>
                            <th>列1</th>
                            <th>列2</th>
                        </tr>
                        <tr>
                            <td>数据1</td>
                            <td>数据2</td>
                        </tr>
                    </table>
                `;
                break;
                
            default:
                content.innerHTML = `<div style="padding: 8px; border: 1px solid #ccc;">${component.type}</div>`;
        }
        
        return content;
    }
    
    createComponentControls(component) {
        const controls = document.createElement('div');
        controls.className = 'component-controls';
        
        // 删除按钮
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'control-btn delete';
        deleteBtn.innerHTML = '×';
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteComponent(component);
        });
        
        controls.appendChild(deleteBtn);
        
        return controls;
    }
    
    selectComponent(component) {
        // 清除之前的选择
        document.querySelectorAll('.designer-component').forEach(el => {
            el.classList.remove('selected');
        });
        
        // 选中当前组件
        const element = document.querySelector(`[data-component-id="${component.id}"]`);
        if (element) {
            element.classList.add('selected');
        }
        
        this.selectedComponent = component;
        this.showProperties(component);
    }
    
    showProperties(component) {
        const propertiesContent = document.getElementById('propertiesContent');
        
        let html = `
            <div class="property-group">
                <h5>基本属性</h5>
                <div class="property-row">
                    <label class="property-label">组件类型:</label>
                    <span class="property-input" style="background: #f8f9fa;">${component.type}</span>
                </div>
                <div class="property-row">
                    <label class="property-label">ID:</label>
                    <span class="property-input" style="background: #f8f9fa;">${component.id}</span>
                </div>
            </div>
        `;
        
        // 添加特定组件的属性
        html += this.getComponentProperties(component);
        
        propertiesContent.innerHTML = html;
        
        // 绑定属性编辑事件
        this.bindPropertyEvents(component);
    }
    
    getComponentProperties(component) {
        let html = '';
        
        switch (component.type) {
            case 'window':
                html += `
                    <div class="property-group">
                        <h5>窗口属性</h5>
                        <div class="property-row">
                            <label class="property-label">标题:</label>
                            <input type="text" class="property-input" data-prop="title" value="${component.props.title || ''}">
                        </div>
                        <div class="property-row">
                            <label class="property-label">尺寸:</label>
                            <input type="text" class="property-input" data-prop="size" value="${component.props.size || ''}">
                        </div>
                        <div class="property-row">
                            <label class="property-label">居中:</label>
                            <select class="property-input" data-prop="centered">
                                <option value="true" ${component.props.centered === 'true' ? 'selected' : ''}>是</option>
                                <option value="false" ${component.props.centered === 'false' ? 'selected' : ''}>否</option>
                            </select>
                        </div>
                        <div class="property-row">
                            <label class="property-label">边距:</label>
                            <select class="property-input" data-prop="margined">
                                <option value="true" ${component.props.margined === 'true' ? 'selected' : ''}>是</option>
                                <option value="false" ${component.props.margined === 'false' ? 'selected' : ''}>否</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'hbox':
            case 'vbox':
                html += `
                    <div class="property-group">
                        <h5>容器属性</h5>
                        <div class="property-row">
                            <label class="property-label">内边距:</label>
                            <select class="property-input" data-prop="padded">
                                <option value="true" ${component.props.padded === 'true' ? 'selected' : ''}>是</option>
                                <option value="false" ${component.props.padded === 'false' ? 'selected' : ''}>否</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'input':
            case 'password':
            case 'textarea':
                html += `
                    <div class="property-group">
                        <h5>输入属性</h5>
                        <div class="property-row">
                            <label class="property-label">占位符:</label>
                            <input type="text" class="property-input" data-prop="placeholder" value="${component.props.placeholder || ''}">
                        </div>
                        ${component.type === 'textarea' ? `
                        <div class="property-row">
                            <label class="property-label">行数:</label>
                            <input type="number" class="property-input" data-prop="rows" value="${component.props.rows || '3'}">
                        </div>
                        ` : ''}
                        <div class="property-row">
                            <label class="property-label">拉伸:</label>
                            <select class="property-input" data-prop="stretchy">
                                <option value="">不拉伸</option>
                                <option value="true" ${component.props.stretchy === 'true' ? 'selected' : ''}>全部拉伸</option>
                                <option value="horizontal" ${component.props.stretchy === 'horizontal' ? 'selected' : ''}>水平拉伸</option>
                                <option value="vertical" ${component.props.stretchy === 'vertical' ? 'selected' : ''}>垂直拉伸</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'button':
            case 'label':
                html += `
                    <div class="property-group">
                        <h5>文本属性</h5>
                        <div class="property-row">
                            <label class="property-label">文本:</label>
                            <input type="text" class="property-input" data-prop="text" value="${component.props.text || ''}">
                        </div>
                    </div>
                `;
                break;
                
            case 'checkbox':
                html += `
                    <div class="property-group">
                        <h5>复选框属性</h5>
                        <div class="property-row">
                            <label class="property-label">文本:</label>
                            <input type="text" class="property-input" data-prop="text" value="${component.props.text || ''}">
                        </div>
                        <div class="property-row">
                            <label class="property-label">选中:</label>
                            <select class="property-input" data-prop="checked">
                                <option value="true" ${component.props.checked === 'true' ? 'selected' : ''}>是</option>
                                <option value="false" ${component.props.checked === 'false' ? 'selected' : ''}>否</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'spinbox':
            case 'slider':
                html += `
                    <div class="property-group">
                        <h5>数值属性</h5>
                        <div class="property-row">
                            <label class="property-label">最小值:</label>
                            <input type="number" class="property-input" data-prop="min" value="${component.props.min || '0'}">
                        </div>
                        <div class="property-row">
                            <label class="property-label">最大值:</label>
                            <input type="number" class="property-input" data-prop="max" value="${component.props.max || '100'}">
                        </div>
                        <div class="property-row">
                            <label class="property-label">当前值:</label>
                            <input type="number" class="property-input" data-prop="value" value="${component.props.value || '50'}">
                        </div>
                    </div>
                `;
                break;
                
            case 'progressbar':
                html += `
                    <div class="property-group">
                        <h5>进度条属性</h5>
                        <div class="property-row">
                            <label class="property-label">进度:</label>
                            <input type="number" class="property-input" data-prop="value" value="${component.props.value || '0'}" min="0" max="100">
                        </div>
                    </div>
                `;
                break;
                
            case 'combobox':
                html += `
                    <div class="property-group">
                        <h5>下拉框属性</h5>
                        <div class="property-row">
                            <label class="property-label">选中项:</label>
                            <input type="number" class="property-input" data-prop="selected" value="${component.props.selected || '0'}" min="0">
                        </div>
                        <div class="property-row">
                            <label class="property-label">拉伸:</label>
                            <select class="property-input" data-prop="stretchy">
                                <option value="">不拉伸</option>
                                <option value="true" ${component.props.stretchy === 'true' ? 'selected' : ''}>全部拉伸</option>
                                <option value="horizontal" ${component.props.stretchy === 'horizontal' ? 'selected' : ''}>水平拉伸</option>
                                <option value="vertical" ${component.props.stretchy === 'vertical' ? 'selected' : ''}>垂直拉伸</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
                
            case 'separator':
                html += `
                    <div class="property-group">
                        <h5>分隔符属性</h5>
                        <div class="property-row">
                            <label class="property-label">方向:</label>
                            <select class="property-input" data-prop="orientation">
                                <option value="horizontal" ${component.props.orientation === 'horizontal' ? 'selected' : ''}>水平</option>
                                <option value="vertical" ${component.props.orientation === 'vertical' ? 'selected' : ''}>垂直</option>
                            </select>
                        </div>
                    </div>
                `;
                break;
        }
        
        return html;
    }
    
    bindPropertyEvents(component) {
        const propertiesContent = document.getElementById('propertiesContent');
        
        propertiesContent.querySelectorAll('.property-input').forEach(input => {
            input.addEventListener('input', (e) => {
                const prop = e.target.dataset.prop;
                const value = e.target.value;
                
                // 更新组件属性
                component.props[prop] = value;
                
                // 重新渲染组件
                this.refreshComponent(component);
                
                // 更新代码预览
                this.updateCodePreview();
            });
        });
    }
    
    refreshComponent(component) {
        const element = document.querySelector(`[data-component-id="${component.id}"]`);
        if (element) {
            console.log(`🔄 刷新组件: ${component.type} (${component.id})`);
            console.log(`   子组件数量: ${component.children.length}`);
            
            // 保存当前的子组件（深拷贝以避免引用问题）
            const currentChildren = JSON.parse(JSON.stringify(component.children));
            
            // 只更新组件的内容，而不是整个元素
            const newContent = this.createComponentContent(component);
            const oldContent = element.querySelector('.component-content');
            
            if (oldContent) {
                // 保存当前的选择状态
                const wasSelected = element.classList.contains('selected');
                
                // 替换内容
                oldContent.parentNode.replaceChild(newContent, oldContent);
                
                // 重新渲染子组件
                if (currentChildren.length > 0) {
                    console.log(`   重新渲染 ${currentChildren.length} 个子组件`);
                    
                    // 找到容器内容区域
                    let containerContent = element.querySelector('.container-content') || 
                                         element.querySelector('.window-content') ||
                                         element.querySelector('.component-content');
                    
                    if (containerContent) {
                        // 清空容器内容
                        containerContent.innerHTML = '';
                        
                        // 重新渲染所有子组件
                        currentChildren.forEach(childData => {
                            // 创建新的子组件对象
                            const child = {
                                id: childData.id,
                                type: childData.type,
                                x: childData.x,
                                y: childData.y,
                                props: { ...childData.props },
                                children: JSON.parse(JSON.stringify(childData.children || [])),
                                parent: component.id
                            };
                            
                            // 渲染子组件
                            this.renderComponent(child, component);
                            
                            // 递归处理嵌套子组件
                            if (child.children && child.children.length > 0) {
                                this.refreshNestedChildren(child);
                            }
                        });
                    }
                }
                
                // 恢复选择状态
                if (wasSelected) {
                    element.classList.add('selected');
                }
                
                // 更新容器占位符
                if (this.isContainerComponent(component.type)) {
                    this.updateContainerPlaceholder(component);
                }
                
                console.log(`✅ 组件刷新完成`);
            }
        }
    }
    
    refreshNestedChildren(parentComponent) {
        if (parentComponent.children && parentComponent.children.length > 0) {
            parentComponent.children.forEach(child => {
                const childElement = document.querySelector(`[data-component-id="${child.id}"]`);
                if (childElement) {
                    // 更新子组件内容
                    const newContent = this.createComponentContent(child);
                    const oldContent = childElement.querySelector('.component-content');
                    
                    if (oldContent) {
                        oldContent.parentNode.replaceChild(newContent, oldContent);
                    }
                    
                    // 递归处理
                    if (child.children && child.children.length > 0) {
                        this.refreshNestedChildren(child);
                    }
                }
            });
        }
    }
    
    centerWindowInCanvas(component, element) {
        const canvas = document.getElementById('designCanvas');
        const canvasRect = canvas.getBoundingClientRect();
        
        // 解析窗口尺寸
        let width = 400;
        let height = 300;
        if (component.props.size) {
            const [w, h] = component.props.size.split(',').map(s => parseInt(s.trim()));
            if (!isNaN(w)) width = w;
            if (!isNaN(h)) height = h;
        }
        
        // 计算居中位置
        const centerX = Math.max(50, (canvasRect.width - width) / 2);
        const centerY = Math.max(50, (canvasRect.height - height) / 2);
        
        // 更新组件位置
        component.x = centerX;
        component.y = centerY;
        element.style.left = centerX + 'px';
        element.style.top = centerY + 'px';
    }
    
    deleteComponent(component) {
        // 找到父组件
        const parentComponent = this.findParentComponent(component);
        
        // 从组件列表中移除
        const index = this.components.findIndex(c => c.id === component.id);
        if (index > -1) {
            this.components.splice(index, 1);
        }
        
        // 从父组件的children中移除
        if (parentComponent) {
            const childIndex = parentComponent.children.findIndex(c => c.id === component.id);
            if (childIndex > -1) {
                parentComponent.children.splice(childIndex, 1);
            }
            // 更新父容器的占位符显示
            this.updateContainerPlaceholder(parentComponent);
        }
        
        // 从 DOM 中移除
        const element = document.querySelector(`[data-component-id="${component.id}"]`);
        if (element) {
            element.remove();
        }
        
        // 清除属性面板
        document.getElementById('propertiesContent').innerHTML = `
            <div class="no-selection">
                <p>选择一个组件以编辑属性</p>
            </div>
        `;
        
        // 如果没有组件了，显示占位符
        if (this.components.length === 0) {
            const placeholder = document.querySelector('.canvas-placeholder');
            if (placeholder) {
                placeholder.style.display = 'block';
            }
        }
        
        // 更新代码预览
        this.updateCodePreview();
    }
    
    findParentComponent(component) {
        // 在根级别查找父组件
        for (let rootComponent of this.components) {
            const parent = this.findParentInChildren(rootComponent, component);
            if (parent) return parent;
        }
        return null;
    }
    
    findParentInChildren(parent, targetChild) {
        for (let child of parent.children) {
            if (child.id === targetChild.id) {
                return parent;
            }
            const found = this.findParentInChildren(child, targetChild);
            if (found) return found;
        }
        return null;
    }
    
    updateCodePreview() {
        const htmlCode = this.generateHTML();
        document.getElementById('htmlCode').textContent = htmlCode;
    }
    
    generateHTML() {
        if (this.components.length === 0) {
            return '<!-- 拖拽组件后将在此处生成 HTML 代码 -->';
        }
        
        // 找到窗口组件作为根，如果没有窗口则使用第一个组件
        const rootComponent = this.components.find(c => c.type === 'window') || this.components[0];
        
        if (!rootComponent) {
            return '<!-- 请添加一个组件作为根元素 -->';
        }
        
        let html = `<!DOCTYPE html>
<ui version="1.0">
`;
        html += this.generateComponentHTML(rootComponent, 1);
        html += '</ui>';
        
        return html;
    }
    
    generateComponentHTML(component, indent = 0) {
        const spaces = '  '.repeat(indent);
        let html = '';
        
        switch (component.type) {
            case 'window':
                html += `${spaces}<window title="${component.props.title || '窗口'}"`;
                if (component.props.size) html += ` size="${component.props.size}"`;
                if (component.props.centered) html += ` centered="${component.props.centered}"`;
                if (component.props.margined) html += ` margined="${component.props.margined}"`;
                html += '>\n';
                
                // 生成子组件
                if (component.children.length > 0) {
                    component.children.forEach(child => {
                        html += this.generateComponentHTML(child, indent + 1);
                    });
                } else {
                    html += `${spaces}  <!-- 窗口内容 -->\n`;
                }
                
                html += `${spaces}</window>\n`;
                break;
                
            case 'grid':
                html += `${spaces}<grid`;
                if (component.props.padded === 'true') html += ` padded="true"`;
                html += '>\n';
                html += `${spaces}  <!-- Grid 布局内容 -->\n`;
                html += `${spaces}</grid>\n`;
                break;
                
            case 'hbox':
                html += `${spaces}<hbox`;
                if (component.props.padded === 'true') html += ` padded="true"`;
                html += '>\n';
                
                // 生成子组件
                if (component.children.length > 0) {
                    component.children.forEach(child => {
                        html += this.generateComponentHTML(child, indent + 1);
                    });
                } else {
                    html += `${spaces}  <!-- HBox 内容 -->\n`;
                }
                
                html += `${spaces}</hbox>\n`;
                break;
                
            case 'vbox':
                html += `${spaces}<vbox`;
                if (component.props.padded === 'true') html += ` padded="true"`;
                html += '>\n';
                
                // 生成子组件
                if (component.children.length > 0) {
                    component.children.forEach(child => {
                        html += this.generateComponentHTML(child, indent + 1);
                    });
                } else {
                    html += `${spaces}  <!-- VBox 内容 -->\n`;
                }
                
                html += `${spaces}</vbox>\n`;
                break;
                
            case 'input':
            case 'password':
                html += `${spaces}<input`;
                if (component.props.type) html += ` type="${component.props.type}"`;
                if (component.props.placeholder) html += ` placeholder="${component.props.placeholder}"`;
                if (component.props.expand) html += ` expand="${component.props.expand}"`;
                if (component.props.stretchy) html += ` stretchy="${component.props.stretchy}"`;
                html += ' />\n';
                break;
                
            case 'textarea':
                html += `${spaces}<textarea`;
                if (component.props.placeholder) html += ` placeholder="${component.props.placeholder}"`;
                if (component.props.rows) html += ` rows="${component.props.rows}"`;
                if (component.props.expand) html += ` expand="${component.props.expand}"`;
                if (component.props.stretchy) html += ` stretchy="${component.props.stretchy}"`;
                html += '></textarea>\n';
                break;
                
            case 'button':
                html += `${spaces}<button>${component.props.text || '按钮'}</button>\n`;
                break;
                
            case 'label':
                html += `${spaces}<label>${component.props.text || '标签文本'}</label>\n`;
                break;
                
            case 'checkbox':
                html += `${spaces}<checkbox`;
                if (component.props.checked === 'true') html += ` checked="true"`;
                html += `>${component.props.text || '复选框'}</checkbox>\n`;
                break;
                
            case 'combobox':
                html += `${spaces}<combobox`;
                if (component.props.selected) html += ` selected="${component.props.selected}"`;
                if (component.props.stretchy) html += ` stretchy="${component.props.stretchy}"`;
                html += '>\n';
                html += `${spaces}  <option>选项 1</option>\n`;
                html += `${spaces}  <option>选项 2</option>\n`;
                html += `${spaces}  <option>选项 3</option>\n`;
                html += `${spaces}</combobox>\n`;
                break;
                
            case 'spinbox':
                html += `${spaces}<spinbox`;
                if (component.props.min) html += ` min="${component.props.min}"`;
                if (component.props.max) html += ` max="${component.props.max}"`;
                if (component.props.value) html += ` value="${component.props.value}"`;
                html += ' />\n';
                break;
                
            case 'slider':
                html += `${spaces}<slider`;
                if (component.props.min) html += ` min="${component.props.min}"`;
                if (component.props.max) html += ` max="${component.props.max}"`;
                if (component.props.value) html += ` value="${component.props.value}"`;
                html += ' />\n';
                break;
                
            case 'progressbar':
                html += `${spaces}<progressbar`;
                if (component.props.value) html += ` value="${component.props.value}"`;
                html += ' />\n';
                break;
                
            case 'separator':
                html += `${spaces}<separator`;
                if (component.props.orientation) html += ` orientation="${component.props.orientation}"`;
                html += ' />\n';
                break;
                
            case 'table':
                html += `${spaces}<table>\n`;
                html += `${spaces}  <thead>\n`;
                html += `${spaces}    <tr>\n`;
                html += `${spaces}      <th>列1</th>\n`;
                html += `${spaces}      <th>列2</th>\n`;
                html += `${spaces}    </tr>\n`;
                html += `${spaces}  </thead>\n`;
                html += `${spaces}  <tbody>\n`;
                html += `${spaces}    <tr>\n`;
                html += `${spaces}      <td>数据1</td>\n`;
                html += `${spaces}      <td>数据2</td>\n`;
                html += `${spaces}    </tr>\n`;
                html += `${spaces}  </tbody>\n`;
                html += `${spaces}</table>\n`;
                break;
        }
        
        return html;
    }
    
    copyCode() {
        const code = document.getElementById('htmlCode').textContent;
        navigator.clipboard.writeText(code).then(() => {
            // 显示复制成功提示
            const btn = document.getElementById('copyCodeBtn');
            const originalText = btn.textContent;
            btn.textContent = '已复制!';
            btn.style.background = '#28a745';
            btn.style.color = 'white';
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    }
    
    toggleCodePreview() {
        const previewContent = document.querySelector('.preview-content');
        const btn = document.getElementById('toggleCodeBtn');
        
        if (previewContent.style.display === 'none') {
            previewContent.style.display = 'block';
            btn.textContent = '收起';
        } else {
            previewContent.style.display = 'none';
            btn.textContent = '展开';
        }
    }
    
    showExportModal() {
        const modal = document.getElementById('exportModal');
        const content = document.getElementById('exportContent');
        
        content.value = this.generateHTML();
        modal.classList.add('show');
    }
    
    hideExportModal() {
        const modal = document.getElementById('exportModal');
        modal.classList.remove('show');
    }
    
    exportFile() {
        const filename = document.getElementById('exportFilename').value;
        const content = document.getElementById('exportContent').value;
        
        const blob = new Blob([content], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        this.hideExportModal();
    }
    
    save() {
        // 保存到本地存储或发送到服务器
        const data = {
            platform: this.platform,
            components: this.components,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('libuiBuilder_design', JSON.stringify(data));
        
        // 显示保存成功提示
        const btn = document.getElementById('saveBtn');
        const originalText = btn.textContent;
        btn.textContent = '已保存!';
        btn.style.background = '#28a745';
        btn.style.color = 'white';
        
        setTimeout(() => {
            btn.textContent = originalText;
            btn.style.background = '';
            btn.style.color = '';
        }, 2000);
    }
    
    preview() {
        const html = this.generateHTML();
        const newWindow = window.open('', '_blank');
        
        if (newWindow) {
            newWindow.document.write(`
                <!DOCTYPE html>
                <html lang="zh-CN">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>预览</title>
                    <link rel="stylesheet" href="libui-ng-complete.css">
                </head>
                <body class="${this.platform}">
                    ${html}
                </body>
                </html>
            `);
            newWindow.document.close();
        }
    }
    
    load() {
        // 从本地存储加载
        const saved = localStorage.getItem('libuiBuilder_design');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                this.platform = data.platform || 'windows';
                this.components = data.components || [];
                
                // 重新渲染组件
                this.components.forEach(component => {
                    this.renderComponent(component);
                });
                
                // 更新界面
                document.body.className = this.platform;
                document.querySelectorAll('.platform-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.platform === this.platform);
                });
                
                // 更新组件样式
                this.updateComponentStyles();
                this.updateCodePreview();
                
                // 隐藏占位符
                if (this.components.length > 0) {
                    const placeholder = document.querySelector('.canvas-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
            } catch (e) {
                console.error('加载设计失败:', e);
            }
        }
    }
    
    updateComponentStyles() {
        // 强制重新渲染所有组件以应用新平台样式
        // 重新渲染所有根组件，子组件会自动重新渲染
        this.components.forEach(component => {
            this.refreshComponent(component);
        });
    }
    
    getAllComponents() {
        const allComponents = [];
        
        // 递归收集所有组件
        const collectComponents = (component) => {
            allComponents.push(component);
            if (component.children && component.children.length > 0) {
                component.children.forEach(child => collectComponents(child));
            }
        };
        
        this.components.forEach(component => collectComponents(component));
        return allComponents;
    }
}

// 初始化设计器
document.addEventListener('DOMContentLoaded', () => {
    const designer = new LibuiBuilderDesigner();
    designer.load();
});