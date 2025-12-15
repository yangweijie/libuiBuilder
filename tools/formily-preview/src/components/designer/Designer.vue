<template>
  <div class="designer">
    <!-- 设计器头部 -->
    <div class="designer-header">
      <h2>libuiBuilder 可视化设计器</h2>
      <div class="header-actions">
        <a-button @click="loadExample">加载示例</a-button>
        <a-button @click="resetDesigner">重置</a-button>
        <a-button type="primary" @click="generateCode">生成代码</a-button>
      </div>
    </div>

    <!-- 设计器主体 -->
    <div class="designer-body">
      <!-- 左侧：组件面板 -->
      <div class="designer-sidebar left">
        <div class="sidebar-section">
          <h3>容器组件</h3>
          <div class="component-list">
            <div 
              v-for="component in containerComponents" 
              :key="component.type"
              class="component-item"
              draggable="true"
              @dragstart="onDragStart($event, component)"
            >
              <div class="component-icon">{{ component.icon }}</div>
              <div class="component-name">{{ component.name }}</div>
            </div>
          </div>
        </div>

        <div class="sidebar-section">
          <h3>输入组件</h3>
          <div class="component-list">
            <div 
              v-for="component in inputComponents" 
              :key="component.type"
              class="component-item"
              draggable="true"
              @dragstart="onDragStart($event, component)"
            >
              <div class="component-icon">{{ component.icon }}</div>
              <div class="component-name">{{ component.name }}</div>
            </div>
          </div>
        </div>

        <div class="sidebar-section">
          <h3>显示组件</h3>
          <div class="component-list">
            <div 
              v-for="component in displayComponents" 
              :key="component.type"
              class="component-item"
              draggable="true"
              @dragstart="onDragStart($event, component)"
            >
              <div class="component-icon">{{ component.icon }}</div>
              <div class="component-name">{{ component.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 中间：设计区域 -->
      <div 
        class="designer-canvas"
        @dragover.prevent
        @drop="onDrop"
      >
        <div class="canvas-header">
          <div class="canvas-title">设计区域</div>
          <div class="canvas-actions">
            <a-switch v-model:checked="showGrid" size="small">
              <template #checkedChildren>网格</template>
              <template #unCheckedChildren>网格</template>
            </a-switch>
            <a-switch v-model:checked="showBindings" size="small">
              <template #checkedChildren>绑定</template>
              <template #unCheckedChildren>绑定</template>
            </a-switch>
          </div>
        </div>

        <div 
          class="canvas-content"
          :class="{ 'show-grid': showGrid }"
        >
          <!-- 设计器网格背景 -->
          <div v-if="showGrid" class="grid-background"></div>

          <!-- 组件树预览 -->
          <div v-if="!components.length" class="empty-canvas">
            <div class="empty-message">
              <div class="empty-icon">📋</div>
              <div class="empty-text">拖拽组件到此处开始设计</div>
              <div class="empty-hint">或点击"加载示例"查看示例</div>
            </div>
          </div>

          <!-- 实际组件渲染 -->
          <div v-else class="component-preview-area">
            <component-preview 
              v-for="component in components"
              :key="component.id"
              :component="component"
              :selected="selectedComponent?.id === component.id"
              @select="onSelectComponent"
              @delete="onDeleteComponent"
              @grid-drop="onGridDrop"
            />
          </div>
        </div>
      </div>

      <!-- 右侧：属性面板 -->
      <div class="designer-sidebar right">
        <div class="sidebar-section">
          <h3>属性面板</h3>
          <div v-if="selectedComponent" class="property-panel">
            <div class="property-section">
              <h4>基本属性</h4>
              <a-form layout="vertical">
                <a-form-item label="组件类型">
                  <a-input :value="selectedComponent.type" disabled />
                </a-form-item>
                <a-form-item label="组件ID">
                  <a-input v-model:value="selectedComponent.id" />
                </a-form-item>
              </a-form>
            </div>

            <div class="property-section">
              <h4>属性设置</h4>
              <a-form layout="vertical">
                <a-form-item 
                  v-for="(value, key) in selectedComponent.properties" 
                  :key="key"
                  :label="key"
                >
                  <a-input 
                    v-if="typeof value === 'string'"
                    v-model:value="selectedComponent.properties[key]"
                  />
                  <a-input-number 
                    v-else-if="typeof value === 'number'"
                    v-model:value="selectedComponent.properties[key]"
                  />
                  <a-switch 
                    v-else-if="typeof value === 'boolean'"
                    v-model:checked="selectedComponent.properties[key]"
                  />
                  <a-input 
                    v-else
                    :value="JSON.stringify(value)"
                    disabled
                  />
                </a-form-item>
              </a-form>
            </div>

            <div class="property-section">
              <h4>布局设置</h4>
              <a-form layout="vertical" v-if="selectedComponent.layout">
                <a-form-item label="行">
                  <a-input-number 
                    v-model:value="selectedComponent.layout.row"
                    :min="0"
                    :max="20"
                  />
                </a-form-item>
                <a-form-item label="列">
                  <a-input-number 
                    v-model:value="selectedComponent.layout.col"
                    :min="0"
                    :max="20"
                  />
                </a-form-item>
                <a-form-item label="行跨度">
                  <a-input-number 
                    v-model:value="selectedComponent.layout.rowspan"
                    :min="1"
                    :max="10"
                  />
                </a-form-item>
                <a-form-item label="列跨度">
                  <a-input-number 
                    v-model:value="selectedComponent.layout.colspan"
                    :min="1"
                    :max="10"
                    @change="forceUpdateLayout"
                  />
                </a-form-item>
                <a-form-item label="水平对齐">
                  <a-select v-model:value="selectedComponent.layout.alignHorizontal" @change="updateAlignValue">
                    <a-select-option value="fill">填充</a-select-option>
                    <a-select-option value="start">起始</a-select-option>
                    <a-select-option value="center">居中</a-select-option>
                    <a-select-option value="end">末尾</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="垂直对齐">
                  <a-select v-model:value="selectedComponent.layout.alignVertical" @change="updateAlignValue">
                    <a-select-option value="fill">填充</a-select-option>
                    <a-select-option value="start">起始</a-select-option>
                    <a-select-option value="center">居中</a-select-option>
                    <a-select-option value="end">末尾</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="扩展方式">
                  <a-select v-model:value="selectedComponent.layout.expand">
                    <a-select-option value="none">不扩展</a-select-option>
                    <a-select-option value="horizontal">水平扩展</a-select-option>
                    <a-select-option value="vertical">垂直扩展</a-select-option>
                    <a-select-option value="both">双向扩展</a-select-option>
                  </a-select>
                </a-form-item>
              </a-form>
              <div v-else class="no-layout">
                该组件不支持布局设置
              </div>
            </div>

            <div class="property-section" v-if="selectedComponent.type === 'tab'">
              <h4>标签页管理</h4>
              <a-form layout="vertical">
                <a-form-item label="标签页列表">
                  <div class="tabs-list">
                    <div 
                      v-for="(tab, index) in getTabItems()" 
                      :key="index"
                      class="tab-item"
                    >
                      <a-input 
                        v-model:value="tab.label"
                        placeholder="标签页名称"
                        style="flex: 1"
                      />
                      <a-button 
                        type="text" 
                        danger 
                        size="small"
                        @click="removeTab(index)"
                      >
                        删除
                      </a-button>
                    </div>
                    <a-button 
                      type="dashed" 
                      block 
                      @click="addTab"
                    >
                      + 添加标签页
                    </a-button>
                  </div>
                </a-form-item>
                <a-form-item label="激活标签页">
                  <a-select v-model:value="selectedComponent.properties.activeTab">
                    <a-select-option 
                      v-for="(tab, index) in getTabItems()" 
                      :key="index"
                      :value="index.toString()"
                    >
                      {{ tab.label }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-form>
            </div>

            <div class="property-section" v-if="selectedComponent.type === 'table'">
              <h4>表格设置</h4>
              <a-form layout="vertical">
                <a-form-item label="列标题">
                  <a-input 
                    v-model:value="selectedComponent.properties.columns"
                    placeholder="用逗号分隔列标题"
                  />
                </a-form-item>
                <a-form-item label="列类型">
                  <div class="column-types-list">
                    <div 
                      v-for="(col, index) in getColumnTypes()" 
                      :key="index"
                      class="column-type-item"
                    >
                      <span class="column-name">{{ col.name }}</span>
                      <a-select 
                        v-model:value="col.type"
                        style="width: 120px"
                      >
                        <a-select-option value="text">文本</a-select-option>
                        <a-select-option value="image">图片</a-select-option>
                        <a-select-option value="checkbox">复选框</a-select-option>
                        <a-select-option value="progress">进度条</a-select-option>
                        <a-select-option value="button">按钮</a-select-option>
                        <a-select-option value="imageText">图片+文本</a-select-option>
                      </a-select>
                    </div>
                  </div>
                </a-form-item>
                <a-form-item label="表格数据">
                  <div class="table-data-container">
                    <a-table
                      :columns="getTableColumnsForEdit()"
                      :data-source="getTableDataForEdit()"
                      size="small"
                      :pagination="false"
                      bordered
                    >
                      <template #bodyCell="{ column, record, index }">
                        <a-input 
                          v-if="column.dataIndex !== 'actions'"
                          v-model:value="record[column.dataIndex]"
                          size="small"
                        />
                        <a-button 
                          v-else
                          type="text" 
                          danger 
                          size="small"
                          @click="removeTableRow(index)"
                        >
                          删除
                        </a-button>
                      </template>
                    </a-table>
                    <a-button 
                      class="add-row-btn"
                      type="dashed" 
                      block
                      @click="addTableRow"
                    >
                      + 添加行
                    </a-button>
                  </div>
                </a-form-item>
              </a-form>
            </div>
          </div>
          <div v-else class="no-selection">
            <div class="no-selection-icon">👆</div>
            <div class="no-selection-text">请选择一个组件进行编辑</div>
          </div>
        </div>

        <div class="sidebar-section">
          <h3>状态绑定</h3>
          <div class="binding-panel">
            <div v-if="selectedComponent && selectedComponent.properties.bind" class="binding-info">
              <div class="binding-key">
                <span class="binding-label">绑定键:</span>
                <span class="binding-value">{{ selectedComponent.properties.bind }}</span>
              </div>
              <a-button size="small" @click="editBinding">编辑绑定</a-button>
            </div>
            <div v-else class="no-binding">
              该组件未绑定状态
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 代码生成模态框 -->
    <a-modal 
      v-model:open="showCodeModal"
      title="生成的代码"
      width="800px"
      :footer="null"
    >
      <div class="code-modal">
        <div class="code-tabs">
          <a-tabs v-model:activeKey="activeCodeTab">
            <a-tab-pane key="html" tab="HTML 代码">
              <div class="code-content">
                <pre><code>{{ generatedHtml }}</code></pre>
              </div>
            </a-tab-pane>
            <a-tab-pane key="formily" tab="Formily Schema">
              <div class="code-content">
                <pre><code>{{ generatedFormily }}</code></pre>
              </div>
            </a-tab-pane>
          </a-tabs>
        </div>
        <div class="code-actions">
          <a-button @click="copyCode">复制代码</a-button>
          <a-button type="primary" @click="downloadCode">下载文件</a-button>
        </div>
      </div>
    </a-modal>

    <!-- 状态绑定模态框 -->
    <a-modal 
      v-model:open="showBindingModal"
      title="状态绑定管理"
      width="600px"
      @ok="saveBinding"
    >
      <div class="binding-modal">
        <a-form layout="vertical" v-if="selectedComponent">
          <a-form-item label="绑定键">
            <a-input 
              v-model:value="bindingKey"
              placeholder="输入状态键名（如：username）"
            />
          </a-form-item>
          <a-form-item label="默认值">
            <a-input 
              v-model="bindingDefaultValue"
              placeholder="输入默认值"
            />
          </a-form-item>
          <a-form-item label="描述">
            <a-textarea 
              v-model="bindingDescription"
              placeholder="描述此绑定的用途"
              :rows="3"
            />
          </a-form-item>
        </a-form>
        
        <div v-else class="no-selection">
          <p>请先选择一个组件</p>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, provide, watch, nextTick } from 'vue'
import { message } from 'ant-design-vue'
import ComponentPreview from './ComponentPreview.vue'
import type { ComponentConfig } from '@/types'

// 组件定义
const containerComponents = [
  { type: 'window', name: '窗口', icon: '🪟' },
  { type: 'grid', name: '网格', icon: '📊' },
  { type: 'vbox', name: '垂直盒子', icon: '📦' },
  { type: 'hbox', name: '水平盒子', icon: '📦' },
  { type: 'tab', name: '标签页', icon: '📑' }
]

const inputComponents = [
  { type: 'input', name: '输入框', icon: '📝' },
  { type: 'textarea', name: '多行输入', icon: '📄' },
  { type: 'button', name: '按钮', icon: '🔘' },
  { type: 'checkbox', name: '复选框', icon: '☑️' },
  { type: 'radio', name: '单选框', icon: '🔘' },
  { type: 'select', name: '下拉框', icon: '📋' }
]

const displayComponents = [
  { type: 'label', name: '标签', icon: '🏷️' },
  { type: 'progressbar', name: '进度条', icon: '📊' },
  { type: 'separator', name: '分隔符', icon: '➖' },
  { type: 'table', name: '表格', icon: '📋' }
]

// 设计器状态
const components = ref<ComponentConfig[]>([])
const selectedComponent = ref<ComponentConfig | null>(null)
const showGrid = ref(true)
const showBindings = ref(true)
const showCodeModal = ref(false)
const activeCodeTab = ref('html')
const generatedHtml = ref('')
const generatedFormily = ref('')
const showBindingModal = ref(false)

// 拖拽处理
const dragData = ref<any>(null)

const onDragStart = (event: DragEvent, component: any) => {
  if (event.dataTransfer) {
    event.dataTransfer.setData('text/plain', JSON.stringify(component))
    dragData.value = component
  }
}

const onDrop = (event: DragEvent) => {
  event.preventDefault()
  
  if (!dragData.value) return
  
  const newComponent: ComponentConfig = {
    id: `${dragData.value.type}_${Date.now()}`,
    type: dragData.value.type,
    properties: getDefaultProperties(dragData.value.type),
    children: []
  }
  
  // 如果是容器组件，添加默认子元素
  if (['grid', 'vbox', 'hbox', 'tab'].includes(dragData.value.type)) {
    newComponent.children = []
  }
  
  // 如果是窗口，设置为根组件
  if (dragData.value.type === 'window') {
    components.value = [newComponent]
  } else {
    let targetContainer: ComponentConfig | null = null
    
    // 添加到当前选中的容器或根组件
    if (selectedComponent.value && isContainer(selectedComponent.value.type)) {
      targetContainer = selectedComponent.value
    } else {
      // 尝试找到最近的容器组件
      targetContainer = findNearestContainer(selectedComponent.value)
      
      if (!targetContainer) {
        // 如果没有选中容器，添加到根组件
        if (components.value.length === 0) {
          // 如果没有根组件，创建一个网格作为根
          const rootGrid: ComponentConfig = {
            id: 'grid_root',
            type: 'grid',
            properties: { padded: true },
            children: [newComponent]
          }
          components.value = [rootGrid]
        } else {
          // 添加到第一个根组件
          const root = components.value[0]
          if (!root.children) {
            root.children = []
          }
          root.children.push(newComponent)
        }
      }
    }
    
    // 如果找到了目标容器，并且是网格容器，设置布局属性
    if (targetContainer && targetContainer.type === 'grid') {
      // 查找下一个可用的网格位置
      const nextPosition = findNextGridPosition(targetContainer)
      newComponent.layout = {
        row: nextPosition.row,
        col: nextPosition.col,
        rowspan: 1,
        colspan: 1,
        alignHorizontal: 'fill',
        alignVertical: 'fill',
        align: 'fill'
      }
      
      // 添加到网格容器
      if (!targetContainer.children) {
        targetContainer.children = []
      }
      targetContainer.children.push(newComponent)
    } else if (targetContainer) {
      // 非网格容器，直接添加
      if (!targetContainer.children) {
        targetContainer.children = []
      }
      targetContainer.children.push(newComponent)
    }
  }
  
  const componentName = dragData.value?.name || '组件'
  selectedComponent.value = newComponent
  dragData.value = null
  
  message.success(`添加 ${componentName} 组件`)
}

// 组件操作
const onSelectComponent = (component: ComponentConfig) => {
  selectedComponent.value = component
}

const onUpdateComponent = (component: ComponentConfig) => {
  // 更新组件逻辑
  console.log('更新组件:', component)
}

// 更新对齐值
const updateAlignValue = () => {
  if (!selectedComponent.value || !selectedComponent.value.layout) return
  
  const { alignHorizontal = 'fill', alignVertical = 'fill' } = selectedComponent.value.layout
  
  // 如果水平和垂直对齐相同，使用单个值
  if (alignHorizontal === alignVertical) {
    selectedComponent.value.layout.align = alignHorizontal
  } else {
    // 如果不同，使用逗号分隔的格式
    selectedComponent.value.layout.align = `${alignHorizontal},${alignVertical}`
  }
}

// 强制更新布局（响应式触发）
const forceUpdateLayout = () => {
  if (!selectedComponent.value || !selectedComponent.value.layout) return
  
  // 创建新的 layout 对象以触发响应式更新
  const newLayout = { ...selectedComponent.value.layout }
  selectedComponent.value.layout = newLayout
}

// 处理网格容器内的拖放
const onGridDrop = (event: CustomEvent) => {
  const { targetContainer, originalEvent } = event.detail
  
  if (!dragData.value) return
  
  const newComponent: ComponentConfig = {
    id: `${dragData.value.type}_${Date.now()}`,
    type: dragData.value.type,
    properties: getDefaultProperties(dragData.value.type),
    children: []
  }
  
  // 如果是容器组件，添加默认子元素
  if (['grid', 'vbox', 'hbox', 'tab'].includes(dragData.value.type)) {
    newComponent.children = []
  }
  
  // 为网格中的子组件设置布局属性
  if (targetContainer && targetContainer.type === 'grid') {
    // 查找下一个可用的网格位置
    const nextPosition = findNextGridPosition(targetContainer)
    newComponent.layout = {
      row: nextPosition.row,
      col: nextPosition.col,
      rowspan: 1,
      colspan: 1,
      alignHorizontal: 'fill',
      alignVertical: 'fill',
      align: 'fill'
    }
    
    // 添加到网格容器
    if (!targetContainer.children) {
      targetContainer.children = []
    }
    targetContainer.children.push(newComponent)
    
    // 保持选中网格容器，而不是新拖入的组件
    selectedComponent.value = targetContainer
  } else if (targetContainer) {
    // 非网格容器，直接添加
    if (!targetContainer.children) {
      targetContainer.children = []
    }
    targetContainer.children.push(newComponent)
    // 保持选中容器
    selectedComponent.value = targetContainer
  }
  
  const componentName = dragData.value?.name || '组件'
  dragData.value = null
  
  message.success(`添加 ${componentName} 组件`)
}

const onDeleteComponent = (component: ComponentConfig) => {
  const deleteFromTree = (tree: ComponentConfig[]): ComponentConfig[] => {
    return tree.filter(item => {
      if (item.id === component.id) {
        return false
      }
      if (item.children) {
        item.children = deleteFromTree(item.children)
      }
      return true
    })
  }
  
  components.value = deleteFromTree(components.value)
  
  if (selectedComponent.value?.id === component.id) {
    selectedComponent.value = null
  }
  
  message.success('删除组件成功')
}

// 提供选择函数给子组件
provide('selectComponent', onSelectComponent)

// 提供删除函数给子组件
provide('deleteComponent', onDeleteComponent)

// 提供当前选中组件给子组件
provide('selectedComponent', selectedComponent)



// 设计器操作
const loadExample = () => {
  const example: ComponentConfig = {
    id: 'window_example',
    type: 'window',
    properties: {
      title: '示例窗口',
      size: '400,300',
      centered: true,
      margined: true
    },
    children: [
      {
        id: 'grid_example',
        type: 'grid',
        properties: { padded: true },
        children: [
          {
            id: 'label_title',
            type: 'label',
            properties: { text: '欢迎使用 libuiBuilder' },
            layout: { row: 0, col: 0, colspan: 2, align: 'center' }
          },
          {
            id: 'separator_1',
            type: 'separator',
            properties: {},
            layout: { row: 1, col: 0, colspan: 2 }
          },
          {
            id: 'label_username',
            type: 'label',
            properties: { text: '用户名:' },
            layout: { row: 2, col: 0 }
          },
          {
            id: 'input_username',
            type: 'input',
            properties: { 
              type: 'text',
              placeholder: '请输入用户名',
              bind: 'username'
            },
            layout: { row: 2, col: 1, expand: 'horizontal' }
          },
          {
            id: 'button_login',
            type: 'button',
            properties: { 
              text: '登录',
              type: 'primary'
            },
            layout: { row: 3, col: 0, colspan: 2, align: 'center' }
          }
        ]
      }
    ]
  }
  
  components.value = [example]
  selectedComponent.value = example.children?.[0]?.children?.[0] || null
  
  message.success('加载示例成功')
}

const resetDesigner = () => {
  components.value = []
  selectedComponent.value = null
  message.success('设计器已重置')
}

const generateCode = () => {
  if (components.value.length === 0) {
    message.warning('请先添加组件')
    return
  }
  
  // 生成 HTML 代码
  generatedHtml.value = generateHtmlCode(components.value[0])
  
  // 生成 Formily Schema（简化版）
  generatedFormily.value = JSON.stringify(
    generateFormilySchema(components.value[0]),
    null, 2
  )
  
  showCodeModal.value = true
}

const copyCode = () => {
  const code = activeCodeTab.value === 'html' ? generatedHtml.value : generatedFormily.value
  navigator.clipboard.writeText(code)
    .then(() => message.success('代码已复制到剪贴板'))
    .catch(() => message.error('复制失败'))
}

const downloadCode = () => {
  const filename = activeCodeTab.value === 'html' ? 'design.ui.html' : 'schema.json'
  const content = activeCodeTab.value === 'html' ? generatedHtml.value : generatedFormily.value
  
  const blob = new Blob([content], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
  
  message.success(`文件 ${filename} 已下载`)
}

// 状态绑定相关
const bindingKey = ref('')
const bindingDefaultValue = ref('')
const bindingDescription = ref('')

const saveBinding = () => {
  if (!selectedComponent.value || !bindingKey.value.trim()) {
    message.warning('请输入有效的绑定键名')
    return
  }
  
  // 设置绑定属性
  selectedComponent.value.properties.bind = bindingKey.value
  
  // 如果有默认值，更新状态
  if (bindingDefaultValue.value) {
    try {
      // 这里应该调用状态管理器设置默认值
      console.log('设置状态:', bindingKey.value, bindingDefaultValue.value)
    } catch (e) {
      console.error('设置状态失败:', e)
    }
  }
  
  showBindingModal.value = false
  message.success('状态绑定已保存')
  bindingKey.value = ''
  bindingDefaultValue.value = ''
  bindingDescription.value = ''
}

// Tab 相关方法
const getTabItems = () => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'tab') return []
  
  const tabs = selectedComponent.value.properties.tabs || '标签页1,标签页2'
  return tabs.split(',').map((tab: string, index) => ({
    label: tab.trim(),
    index: index
  }))
}

const removeTab = (index: number) => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'tab') return
  
  const tabs = getTabItems()
  if (tabs.length <= 1) {
    message.warning('至少需要保留一个标签页')
    return
  }
  
  tabs.splice(index, 1)
  selectedComponent.value.properties.tabs = tabs.map(tab => tab.label).join(',')
  
  // 更新激活标签页索引
  if (selectedComponent.value.properties.activeTab && parseInt(selectedComponent.value.properties.activeTab) >= tabs.length) {
    selectedComponent.value.properties.activeTab = '0'
  }
  
  // 移除被删除标签页的子组件
  if (selectedComponent.value.children) {
    selectedComponent.value.children = selectedComponent.value.children.filter(
      child => !child.layout || child.layout.tabIndex !== index
    ).map(child => {
      // 调整后续标签页的索引
      if (child.layout && child.layout.tabIndex > index) {
        child.layout.tabIndex -= 1
      }
      return child
    })
  }
  
  message.success('标签页已删除')
}

const addTab = () => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'tab') return
  
  const tabs = getTabItems()
  const newTabIndex = tabs.length
  const newTabName = `标签页${newTabIndex + 1}`
  
  tabs.push({ label: newTabName, index: newTabIndex })
  selectedComponent.value.properties.tabs = tabs.map(tab => tab.label).join(',')
  selectedComponent.value.properties.activeTab = String(newTabIndex)
  
  message.success('标签页已添加')
}

// 表格相关方法
const getColumnTypes = () => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'table') return []
  
  const columns = selectedComponent.value.properties.columns || '列1,列2'
  const columnTypes = selectedComponent.value.properties.columnTypes || 'text,text'
  
  return columns.split(',').map((col: string, index: string) => ({
    name: col.trim(),
    type: columnTypes.split(',')[index] || 'text'
  }))
}

const getTableColumnsForEdit = () => {
  return getColumnTypes().map(col => ({
    title: col.name,
    dataIndex: `col${Math.random().toString(36).substr(2, 5)}`,
    key: col.name,
    width: 150
  }))
}

const getTableDataForEdit = () => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'table') return []
  
  const tableData = selectedComponent.value.properties.tableData || '[]'
  
  try {
    return JSON.parse(tableData)
  } catch (e) {
    // 返回默认数据
    const columns = getColumnTypes()
    return [
      columns.reduce((acc: any, col, index) => {
        acc[col.dataIndex] = `数据${index + 1}`
      return acc
    }, {})
    ]
  }
}

const removeTableRow = (index: number) => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'table') return
  
  const tableData = getTableDataForEdit()
  tableData.splice(index, 1)
  
  selectedComponent.value.properties.tableData = JSON.stringify(tableData)
  message.success('表格行已删除')
}

const addTableRow = () => {
  if (!selectedComponent.value || selectedComponent.value.type !== 'table') return
  
  const tableData = getTableDataForEdit()
  const columns = getColumnTypes()
  
  const newRow = columns.reduce((acc: any, col) => {
    acc[col.dataIndex] = ''
    return acc
  }, {})
  
  tableData.push(newRow)
  selectedComponent.value.properties.tableData = JSON.stringify(tableData)
  message.success('表格行已添加')
}

// 工具函数
const getDefaultProperties = (type: string): Record<string, any> => {
  const defaults: Record<string, Record<string, any>> = {
    window: { title: '新窗口', size: '800,600', centered: false, margined: false },
    grid: { padded: true },
    vbox: { padded: true },
    hbox: { padded: true },
    tab: {},
    input: { type: 'text', placeholder: '请输入...' },
    textarea: { placeholder: '请输入...', rows: 3 },
    button: { text: '按钮', type: 'default' },
    checkbox: { text: '选项', checked: false },
    radio: {},
    select: { placeholder: '请选择...' },
    label: { text: '标签文本' },
    progressbar: { value: 50, max: 100 },
    separator: {},
    table: { columns: '列1,列2,列3' }
  }
  
  return { ...defaults[type] || {} }
}

const isContainer = (type: string): boolean => {
  return ['window', 'grid', 'vbox', 'hbox', 'tab'].includes(type)
}

// 查找最近的容器组件
const findNearestContainer = (component: ComponentConfig | null): ComponentConfig | null => {
  if (!component) return null
  
  // 如果当前组件就是容器，直接返回
  if (isContainer(component.type)) {
    return component
  }
  
  // 查找父容器
  const findParent = (components: ComponentConfig[], targetId: string): ComponentConfig | null => {
    for (const comp of components) {
      if (comp.children) {
        // 检查子组件中是否有目标组件
        for (const child of comp.children) {
          if (child.id === targetId) {
            return comp
          }
        }
        // 递归查找
        const found = findParent(comp.children, targetId)
        if (found) return found
      }
    }
    return null
  }
  
  return findParent(components.value, component.id)
}

// 查找下一个可用的网格位置
const findNextGridPosition = (container: ComponentConfig): { row: number, col: number } => {
  if (!container.children) {
    return { row: 0, col: 0 }
  }
  
  // 找出所有已使用的位置
  const usedPositions = new Set<string>()
  container.children.forEach(child => {
    if (child.layout) {
      usedPositions.add(`${child.layout.row},${child.layout.col}`)
    }
  })
  
  // 从 (0,0) 开始查找第一个空闲位置
  for (let row = 0; row < 20; row++) {
    for (let col = 0; col < 20; col++) {
      if (!usedPositions.has(`${row},${col}`)) {
        return { row, col }
      }
    }
  }
  
  return { row: 0, col: 0 }
}

const generateHtmlCode = (component: ComponentConfig, indent = 0): string => {
  const spaces = '  '.repeat(indent)
  
  if (!component) return ''
  
  const { type, properties, children, layout } = component
  
  // 构建属性字符串
  const attrs: string[] = []
  
  // 添加普通属性
  for (const [key, value] of Object.entries(properties)) {
    if (value !== undefined && value !== null && value !== '') {
      if (typeof value === 'boolean') {
        if (value) attrs.push(key)
      } else {
        // 转义特殊字符
        const escapedValue = String(value).replace(/"/g, '&quot;')
        attrs.push(`${key}="${escapedValue}"`)
      }
    }
  }
  
  // 添加布局属性
  if (layout) {
    // 处理对齐属性的特殊逻辑
    const { align, ...otherLayoutProps } = layout
    
    // 添加其他布局属性
    for (const [key, value] of Object.entries(otherLayoutProps)) {
      if (value !== undefined && value !== null) {
        attrs.push(`${key}="${value}"`)
      }
    }
    
    // 特殊处理对齐属性
    if (align !== undefined && align !== null) {
      // 当对齐方式不是默认值时才添加
      if (align !== 'fill') {
        attrs.push(`align="${align}"`)
      }
    }
  }
  
  const attrStr = attrs.length > 0 ? ' ' + attrs.join(' ') : ''
  
  // 处理子组件
  let childrenHtml = ''
  if (children && children.length > 0) {
    childrenHtml = '\n' + children.map(child => 
      generateHtmlCode(child, indent + 1)
    ).join('\n') + '\n' + spaces
  }
  
  // 生成标签
  if (children && children.length > 0) {
    return `${spaces}<${type}${attrStr}>${childrenHtml}</${type}>`
  } else {
    return `${spaces}<${type}${attrStr} />`
  }
}

const generateFormilySchema = (component: ComponentConfig): any => {
  if (!component) return {}
  
  const { type, properties, children, layout } = component
  
  const schema: any = {
    type: 'void'
  }
  
  // 设置组件类型
  const componentMapping: Record<string, string> = {
    window: 'LibuiForm',
    grid: 'LibuiGrid',
    vbox: 'LibuiBox',
    hbox: 'LibuiBox',
    input: 'LibuiInput',
    button: 'Button',
    label: 'Typography.Text',
    checkbox: 'Checkbox',
    radio: 'Radio.Group',
    select: 'Select',
    textarea: 'Input.TextArea',
    separator: 'Divider',
    progressbar: 'Progress',
    table: 'Table',
    tab: 'Tabs'
  }
  
  if (componentMapping[type]) {
    schema['x-component'] = componentMapping[type]
  }
  
  // 设置组件属性
  if (Object.keys(properties).length > 0) {
    schema['x-component-props'] = { ...properties }
    
    // 特殊处理
    if (type === 'vbox' || type === 'hbox') {
      schema['x-component-props'].direction = type === 'vbox' ? 'vertical' : 'horizontal'
    }
  }
  
  // 处理布局属性（Grid子组件）
  if (layout && type !== 'grid') {
    const layoutProps: any = {}
    
    for (const [key, value] of Object.entries(layout)) {
      layoutProps[key] = value
    }
    
    if (Object.keys(layoutProps).length > 0) {
      schema['x-decorator'] = 'LibuiGridItem'
      schema['x-decorator-props'] = layoutProps
    }
  }
  
  // 处理子组件
  if (children && children.length > 0) {
    schema.properties = {}
    
    // 如果是Tab组件，按标签页分组
    if (type === 'tab') {
      const tabItems = properties.tabs ? properties.tabs.split(',') : ['标签页1', '标签页2']
      
      tabItems.forEach((tabLabel: string, tabIndex: number) => {
        const tabChildren = children.filter(child => 
          child.layout && child.layout.tabIndex === tabIndex
        )
        
        if (tabChildren.length > 0) {
          const tabPane: any = {
            type: 'void',
            'x-component': 'TabPane',
            'x-component-props': { tab: tabLabel.trim() },
            properties: {}
          }
          
          tabChildren.forEach((child, index) => {
            tabPane.properties[`child_${index}`] = generateFormilySchema(child)
          })
          
          schema.properties[`tab_${tabIndex}`] = tabPane
        }
      })
    } else {
      // 普通子组件
      children.forEach((child, index) => {
        schema.properties[`child_${index}`] = generateFormilySchema(child)
      })
    }
  }
  
  return schema
}
</script>

<style scoped>
.designer {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f5f5f5;
}

.designer-header {
  padding: 16px 24px;
  background: white;
  border-bottom: 1px solid #e8e8e8;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.designer-header h2 {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.designer-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.designer-sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #e8e8e8;
  overflow-y: auto;
}

.designer-sidebar.right {
  border-right: none;
  border-left: 1px solid #e8e8e8;
}

.sidebar-section {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.sidebar-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
}

.component-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.component-item {
  padding: 12px;
  background: #fafafa;
  border: 1px solid #e8e8e8;
  border-radius: 4px;
  cursor: move;
  text-align: center;
  transition: all 0.2s;
}

.component-item:hover {
  background: #e6f7ff;
  border-color: #1890ff;
}

.component-icon {
  font-size: 24px;
  margin-bottom: 4px;
}

.component-name {
  font-size: 12px;
  color: #333;
}

.designer-canvas {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
  margin: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.canvas-header {
  padding: 12px 16px;
  background: #fafafa;
  border-bottom: 1px solid #e8e8e8;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.canvas-title {
  font-weight: 500;
  color: #333;
}

.canvas-actions {
  display: flex;
  gap: 8px;
}

.canvas-content {
  flex: 1;
  position: relative;
  overflow: visible;
  padding: 40px 24px 24px 24px; /* 增加上边距为删除按钮留出空间 */
  width: 100%;
  box-sizing: border-box;
  min-height: 600px; /* 增加最小高度以适应居中的窗口 */
}

.canvas-content.show-grid {
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
  background-size: 20px 20px;
}

.empty-canvas {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-message {
  text-align: center;
  color: #999;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-text {
  font-size: 16px;
  margin-bottom: 8px;
}

.empty-hint {
  font-size: 12px;
}

.component-tree {
  min-height: 100%;
}

.property-panel {
  padding: 8px 0;
}

.property-section {
  margin-bottom: 20px;
}

.property-section h4 {
  margin: 0 0 12px 0;
  font-size: 13px;
  color: #666;
  font-weight: 500;
}

.no-layout,
.no-binding,
.no-selection {
  padding: 32px 16px;
  text-align: center;
  color: #999;
}

.no-selection-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.binding-info {
  padding: 12px;
  background: #f6ffed;
  border: 1px solid #b7eb8f;
  border-radius: 4px;
}

.binding-key {
  margin-bottom: 8px;
}

.binding-label {
  font-weight: 500;
  margin-right: 8px;
}

.binding-value {
  color: #52c41a;
}

/* 组件预览区域 */
.component-preview-area {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start; /* 改为左对齐，避免居中导致的定位问题 */
  min-height: 100%;
  padding-top: 10px; /* 添加顶部内边距 */
  position: relative;
  overflow: visible;
}

/* 为居中的窗口提供定位上下文 */
.component-preview-area > .component-preview:first-child {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 600px; /* 确保有足够的高度用于居中 */
}

/* 窗口组件的预览区域样式 */
.component-preview-area > .component-preview.is-window {
  min-height: 600px !important; /* 强制设置最小高度 */
  height: calc(100vh - 200px); /* 根据视口高度动态计算 */
}

/* 确保画布内容有足够宽度用于居中 */
.canvas-content {
  flex: 1;
  position: relative;
  overflow: auto;
  padding: 24px;
  width: 100%;
  box-sizing: border-box;
}

.code-modal {
  display: flex;
  flex-direction: column;
  height: 500px;
}

.code-tabs {
  flex: 1;
  overflow: hidden;
}

.code-content {
  height: 400px;
  overflow: auto;
  background: #f6f8fa;
  border-radius: 4px;
  padding: 16px;
}

.code-content pre {
  margin: 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 12px;
  line-height: 1.5;
}

.code-actions {
  padding: 16px 0 0 0;
  text-align: right;
  border-top: 1px solid #f0f0f0;
  margin-top: 16px;
}

.code-actions button {
  margin-left: 8px;
}
</style>