<template>
  <div class="component-renderer">
    <!-- 窗口组件 -->
    <div 
      v-if="component.type === 'window'" 
      class="window-renderer"
      :style="windowStyle"
      @click="handleClick"
    >
      <div class="window-title">{{ component.properties.title || '窗口' }}</div>
      <div 
        class="window-content"
        :style="windowContentStyle"
      >
        <slot name="children">
          <div v-if="!component.children || component.children.length === 0" class="window-placeholder">
            窗口内容区域
          </div>
          <div v-else>
            <component-preview 
              v-for="child in component.children"
              :key="child.id"
              :component="child"
              :selected="globalSelectedComponent?.id === child.id"
              @select="onSelectChild"
              @delete="onDeleteChild"
            />
          </div>
        </slot>
      </div>
    </div>
    
    <!-- 网格组件 -->
    <div v-else-if="component.type === 'grid'" class="grid-renderer" @click="handleClick">
      <div v-if="!component.children || component.children.length === 0" class="grid-placeholder">
        Grid 布局容器
      </div>
      <div v-else class="grid-content" :style="gridStyle" @dragover.prevent @drop="handleGridDrop">
        <component-preview 
          v-for="child in component.children"
          :key="child.id"
          :component="child"
          :selected="globalSelectedComponent?.id === child.id" @select="onSelectChild" @delete="onDeleteChild"
        />
      </div>
    </div>
    
    <!-- 垂直盒子 -->
    <div v-else-if="component.type === 'vbox'" class="vbox-renderer" @click="handleClick">
      <div v-if="!component.children || component.children.length === 0" class="vbox-placeholder">
        VBox 容器
      </div>
      <div v-else class="vbox-content">
        <component-preview 
          v-for="child in component.children"
          :key="child.id"
          :component="child"
          :selected="selectedComponent?.id === child.id"
          @select="onSelectChild"
          @delete="onDeleteChild"
        />
      </div>
    </div>
    
    <!-- 水平盒子 -->
    <div v-else-if="component.type === 'hbox'" class="hbox-renderer" @click="handleClick">
      <div v-if="!component.children || component.children.length === 0" class="hbox-placeholder">
        HBox 容器
      </div>
      <div v-else class="hbox-content">
        <component-preview 
          v-for="child in component.children"
          :key="child.id"
          :component="child"
          :selected="selectedComponent?.id === child.id"
          @select="onSelectChild"
          @delete="onDeleteChild"
        />
      </div>
    </div>

    <!-- Tab 组件 -->
    <div v-else-if="component.type === 'tab'" class="tab-renderer" @click="handleClick">
      <div v-if="!component.children || component.children.length === 0" class="tab-placeholder">
        Tab 容器
      </div>
      <div v-else class="tab-content">
        <a-tabs 
          :active-key="component.properties.activeTab || '0'"
          :size="component.properties.padded === 'true' ? 'default' : 'small'"
        >
          <a-tab-pane
            v-for="(tab, index) in getTabItems()"
            :key="index"
            :tab="tab.label"
          >
            <div class="tab-pane-content">
              <component-preview 
                v-for="child in getTabChildren(index)"
                :key="child.id"
                :component="child"
                :selected="selectedComponent?.id === child.id"
                @select="onSelectChild"
                @delete="onDeleteChild"
              />
            </div>
          </a-tab-pane>
        </a-tabs>
      </div>
    </div>
    
    <!-- 输入框 -->
    <div v-else-if="component.type === 'input'" :style="gridItemStyle" @click="handleClick">
      <AInput
        :placeholder="component.properties.placeholder || '请输入...'"
        :value="component.properties.value || ''"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '120px' }"
      />
    </div>
    
    <!-- 按钮 -->
    <div v-else-if="component.type === 'button'" :style="gridItemStyle" @click="handleClick">
      <AButton
        :type="component.properties.type || 'default'"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : 'auto' }"
      >
        {{ component.properties.text || '按钮' }}
      </AButton>
    </div>
    
    <!-- 标签 -->
    <div v-else-if="component.type === 'label'" :style="gridItemStyle" @click="handleClick">
      <span class="label-renderer">
        {{ component.properties.text || '标签文本' }}
      </span>
    </div>
    
    <!-- 复选框 -->
    <div v-else-if="component.type === 'checkbox'" :style="gridItemStyle" @click="handleClick">
      <ACheckbox
        :checked="component.properties.checked || false"
      >
        {{ component.properties.text || '复选框' }}
      </ACheckbox>
    </div>
    
    <!-- 单选框 -->
    <div v-else-if="component.type === 'radio'" :style="gridItemStyle" @click="handleClick">
      <ARadio
        :checked="component.properties.checked || false"
      >
        {{ component.properties.text || '单选框' }}
      </ARadio>
    </div>
    
    <!-- 下拉框 -->
    <div v-else-if="component.type === 'select'" :style="gridItemStyle" @click="handleClick">
      <ASelect
        :placeholder="component.properties.placeholder || '请选择...'"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '120px' }"
      >
        <ASelectOption value="option1">选项1</ASelectOption>
        <ASelectOption value="option2">选项2</ASelectOption>
      </ASelect>
    </div>
    
    <!-- 多行输入 -->
    <div v-else-if="component.type === 'textarea'" :style="gridItemStyle" @click="handleClick">
      <ATextarea
        :placeholder="component.properties.placeholder || '请输入...'"
        :rows="component.properties.rows || 3"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '120px' }"
      />
    </div>
    
    <!-- 进度条 -->
    <div v-else-if="component.type === 'progressbar'" :style="gridItemStyle" @click="handleClick">
      <AProgress
        :percent="component.properties.value || 50"
        :stroke-width="8"
        :show-info="false"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '120px' }"
      />
    </div>
    
    <!-- 分隔符 -->
    <div v-else-if="component.type === 'separator'" :style="gridItemStyle" @click="handleClick">
      <ADivider
        :type="component.properties.orientation === 'vertical' ? 'vertical' : 'horizontal'"
        :style="{ 
          width: component.properties.orientation === 'vertical' ? '1px' : 
                 (component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '120px')
        }"
      />
    </div>
    
    <!-- 表格 -->
    <div v-else-if="component.type === 'table'" class="table-renderer" :style="gridItemStyle" @click="handleClick">
      <ATable
        :columns="getTableColumns()"
        :data-source="getTableData()"
        size="small"
        :pagination="false"
        :style="{ width: component.properties.stretchy === 'true' || component.properties.stretchy === true ? '100%' : '200px' }"
      />
    </div>
    
    <!-- 默认：显示组件类型 -->
    <div v-else class="unknown-component" @click="handleClick">
      {{ component.type }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineOptions, computed, inject } from 'vue'
import { 
  Input as AInput,
  Button as AButton,
  Checkbox as ACheckbox,
  Radio as ARadio,
  Select as ASelect,
  SelectOption as ASelectOption,
  Textarea as ATextarea,
  Progress as AProgress,
  Divider as ADivider,
  Table as ATable,
  Tabs as ATabs,
  TabPane as ATabPane
} from 'ant-design-vue'
import ComponentPreview from './ComponentPreview.vue'
import type { ComponentConfig } from '@/types'

// 设置组件名称以便递归引用
defineOptions({
  name: 'ComponentRenderer'
})

const props = defineProps<{
  component: ComponentConfig
  parentType?: string
}>()

// 注入选择函数
const selectComponent = inject<(component: ComponentConfig) => void>('selectComponent')

// 注入删除函数
const deleteComponent = inject<(component: ComponentConfig) => void>('deleteComponent')

// 注入全局选中组件
const globalSelectedComponent = inject<ComponentConfig | null>('selectedComponent')

console.log('ComponentRenderer注入检查:', {
  componentType: props.component.type,
  componentId: props.component.id,
  hasSelectComponent: !!selectComponent,
  injectKey: 'selectComponent'
})

// 点击处理函数
const handleClick = (event: MouseEvent) => {
  console.log('组件点击:', props.component.type, props.component.id, { 
    selectComponent: !!selectComponent,
    eventTarget: event.target,
    eventCurrentTarget: event.currentTarget
  })
  event.stopPropagation()
  if (selectComponent) {
    console.log('调用selectComponent:', props.component)
    selectComponent(props.component)
  } else {
    console.warn('selectComponent注入失败，无法选择组件')
  }
}

// 子组件选择处理
const onSelectChild = (component: ComponentConfig) => {
  if (selectComponent) {
    selectComponent(component)
  }
}

// 子组件删除处理
const onDeleteChild = (component: ComponentConfig) => {
  if (deleteComponent) {
    deleteComponent(component)
  }
}

// 计算窗口尺寸样式
const windowStyle = computed(() => {
  if (props.component.type !== 'window') return {}
  
  const style: Record<string, any> = {}
  const { size, centered } = props.component.properties
  
  console.log('窗口尺寸解析:', { size, centered, type: typeof size })
  
  // 解析尺寸
  if (size) {
    if (typeof size === 'string') {
      const [width, height] = size.split(',').map(v => parseInt(v.trim()))
      console.log('解析尺寸字符串:', { width, height, isNaNWidth: isNaN(width), isNaNHeight: isNaN(height) })
      if (!isNaN(width)) style.width = `${width}px`
      if (!isNaN(height)) style.height = `${height}px`
    } else if (typeof size === 'object' && size.width && size.height) {
      style.width = `${size.width}px`
      style.height = `${size.height}px`
    }
  }
  
  // 居中处理
  if (centered === true || centered === 'true') {
    console.log('应用居中样式')
    style.position = 'absolute'
    style.top = '50%'
    style.left = '50%'
    style.transform = 'translate(-50%, -50%)'
  }
  
  console.log('最终窗口样式:', style)
  return style
})

// 计算窗口内容样式（根据margined属性）
const windowContentStyle = computed(() => {
  if (props.component.type !== 'window') return {}
  
  const { margined } = props.component.properties
  const padding = margined === true || margined === 'true' ? '16px' : '8px'
  
  return { padding }
})

// 获取表格列配置
const getTableColumns = () => {
  const columns = props.component.properties.columns || '列1,列2'
  return columns.split(',').map((col: string, index: number) => ({
    title: col.trim(),
    dataIndex: `col${index + 1}`,
    key: `col${index + 1}`
  }))
}

// 获取表格数据
const getTableData = () => {
  const columns = getTableColumns()
  const tableData = props.component.properties.tableData || ''
  
  if (tableData) {
    try {
      return JSON.parse(tableData)
    } catch (e) {
      console.warn('表格数据解析失败:', e)
    }
  }
  
  // 返回默认数据
  return [
    columns.reduce((acc: any, col, index) => {
      acc[col.dataIndex] = `数据${index + 1}`
      acc.key = '1'
      return acc
    }, {}),
    columns.reduce((acc: any, col, index) => {
      acc[col.dataIndex] = `数据${index + 3}`
      acc.key = '2'
      return acc
    }, {})
  ]
}

// 获取Tab标签页项目
const getTabItems = () => {
  const tabs = props.component.properties.tabs || '标签页1,标签页2'
  return tabs.split(',').map((tab: string, index) => ({
    key: String(index),
    label: tab.trim()
  }))
}

// 获取指定标签页的子组件
const getTabChildren = (tabIndex: number) => {
  if (!props.component.children) return []
  
  return props.component.children.filter(child => 
    child.layout && child.layout.tabIndex === tabIndex
  )
}

// 计算网格样式
const gridStyle = computed(() => {
  if (props.component.type !== 'grid') return {}
  
  const style: Record<string, any> = {
    display: 'grid',
    gap: '8px'
  }
  
  // 计算最大行列数以确定网格大小（遵循libui网格原理）
  if (props.component.children && props.component.children.length > 0) {
    let maxRow = 0
    let maxCol = 0
    
    console.log('Grid组件计算样式，子组件数量:', props.component.children.length)
    
    props.component.children.forEach((child, index) => {
      if (child.layout) {
        const row = parseInt(child.layout.row) || 0
        const col = parseInt(child.layout.col) || 0
        const rowspan = parseInt(child.layout.rowspan) || 1
        const colspan = parseInt(child.layout.colspan) || 1
        
        console.log(`子组件 ${index}:`, {
          id: child.id,
          type: child.type,
          layout: child.layout,
          row, col, rowspan, colspan
        })
        
        // 计算组件占据的结束位置
        const endRow = row + rowspan
        const endCol = col + colspan
        
        maxRow = Math.max(maxRow, endRow)
        maxCol = Math.max(maxCol, endCol)
      }
    })
    
    // 确保至少有1行1列
    maxRow = Math.max(maxRow, 1)
    maxCol = Math.max(maxCol, 1)
    
    // 设置网格模板（根据实际组件位置动态计算）
    style.gridTemplateColumns = `repeat(${maxCol}, 1fr)`
    style.gridTemplateRows = `repeat(${maxRow}, minmax(30px, auto))`
    
    console.log('Grid样式计算结果:', {
      maxRow,
      maxCol,
      gridTemplateColumns: style.gridTemplateColumns,
      gridTemplateRows: style.gridTemplateRows
    })
  }
  
  return style
})

// 计算子组件的网格位置样式
const gridItemStyle = computed(() => {
  if (props.parentType !== 'grid' || !props.component.layout) return {}
  
  const { 
    row = 0, 
    col = 0, 
    rowspan = 1, 
    colspan = 1, 
    align = 'fill',
    alignHorizontal = 'fill',
    alignVertical = 'fill',
    expand = 'none' 
  } = props.component.layout
  
  // 确保数值是整数
  const rowInt = parseInt(row) || 0
  const colInt = parseInt(col) || 0
  const rowspanInt = parseInt(rowspan) || 1
  const colspanInt = parseInt(colspan) || 1
  
  const style: Record<string, any> = {}
  
  // 设置网格位置（CSS Grid 索引从1开始）
  const startRow = rowInt + 1
  const startCol = colInt + 1
  const endRow = startRow + rowspanInt - 1
  const endCol = startCol + colspanInt - 1
  
  style.gridRow = `${startRow} / ${endRow + 1}`
  style.gridColumn = `${startCol} / ${endCol + 1}`
  
  // 解析对齐方式
  let horizontalAlign = 'fill'
  let verticalAlign = 'fill'
  
  // 处理对齐值
  if (align) {
    if (align.includes(',')) {
      // 分离水平和垂直对齐
      const [h, v] = align.split(',')
      horizontalAlign = h.trim() || 'fill'
      verticalAlign = v.trim() || 'fill'
    } else {
      // 单个值，水平和垂直相同
      horizontalAlign = align
      verticalAlign = align
    }
  }
  
  // 优先使用单独的对齐值
  if (alignHorizontal) horizontalAlign = alignHorizontal
  if (alignVertical) verticalAlign = alignVertical
  
  // 设置水平对齐 (justifySelf)
  switch (horizontalAlign) {
    case 'start':
      style.justifySelf = 'start'
      break
    case 'center':
      style.justifySelf = 'center'
      break
    case 'end':
      style.justifySelf = 'end'
      break
    case 'fill':
    default:
      style.justifySelf = 'stretch'
      break
  }
  
  // 设置垂直对齐 (alignSelf)
  switch (verticalAlign) {
    case 'start':
      style.alignSelf = 'start'
      break
    case 'center':
      style.alignSelf = 'center'
      break
    case 'end':
      style.alignSelf = 'end'
      break
    case 'fill':
    default:
      style.alignSelf = 'stretch'
      break
  }
  
  // 设置扩展方式
  switch (expand) {
    case 'horizontal':
      style.width = '100%'
      break
    case 'vertical':
      style.height = '100%'
      break
    case 'both':
      style.width = '100%'
      style.height = '100%'
      break
    case 'none':
    default:
      // 不设置宽度和高度，由内容决定
      break
  }
  
  // 处理 stretchy 属性（等同于 expand: 'both'）
  if (props.component.properties.stretchy === 'true' || props.component.properties.stretchy === true) {
    style.width = '100%'
    style.height = '100%'
    style.alignSelf = 'stretch'
    style.justifySelf = 'stretch'
  }
  
  return style
})

// 网格拖放处理
const handleGridDrop = (event: DragEvent) => {
  event.preventDefault()
  event.stopPropagation()
  
  // 触发父级的拖放事件
  const dropEvent = new CustomEvent('grid-drop', {
    detail: {
      targetContainer: props.component,
      originalEvent: event
    }
  })
  event.target?.dispatchEvent(dropEvent)
}
</script>

<style scoped>
.component-renderer {
  width: 100%;
  height: 100%;
}

/* 窗口样式 */
.window-renderer {
  border: 1px solid #ccc;
  border-radius: 4px;
  background: white;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  box-sizing: border-box;
  /* 尺寸由windowStyle计算属性控制 */
  z-index: 1; /* 确保窗口在其他元素之上 */
}

.window-title {
  padding: 8px 12px;
  background: #f0f0f0;
  border-bottom: 1px solid #ccc;
  font-weight: 500;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.window-content {
  position: relative;
  box-sizing: border-box;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  /* 高度由父容器控制，padding由windowContentStyle控制 */
}

.window-placeholder {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #999;
  font-size: 14px;
}

/* 网格样式 */
.grid-renderer {
  border: 2px dashed #0078d4;
  background: rgba(0, 120, 212, 0.05);
  min-width: 200px;
  min-height: 200px;
  border-radius: 4px;
  padding: 8px;
  position: relative;
  overflow: visible;
}

.grid-placeholder {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #0078d4;
  font-weight: 500;
  font-size: 14px;
}

.grid-content {
  display: grid;
  gap: 8px;
  min-height: 150px;
  overflow: visible;
}

/* VBox样式 */
.vbox-renderer {
  border: 2px dashed #28a745;
  background: rgba(40, 167, 69, 0.05);
  min-width: 150px;
  min-height: 100px;
  width: 100%;
  height: auto;
  border-radius: 4px;
  padding: 8px;
  position: relative;
  overflow: visible;
  box-sizing: border-box;
}

.vbox-placeholder {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #28a745;
  font-weight: 500;
  font-size: 14px;
}

.vbox-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 80px;
  width: 100%;
  height: 100%;
  overflow: visible;
  box-sizing: border-box;
}

/* HBox样式 */
.hbox-renderer {
  border: 2px dashed #ffc107;
  background: rgba(255, 193, 7, 0.05);
  min-width: 200px;
  min-height: 50px;
  width: 100%;
  height: auto;
  border-radius: 4px;
  padding: 8px;
  position: relative;
  overflow: visible;
  box-sizing: border-box;
}

.hbox-placeholder {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #ffc107;
  font-weight: 500;
  font-size: 14px;
}

.hbox-content {
  display: flex;
  gap: 8px;
  align-items: center;
  min-height: 30px;
  width: 100%;
  height: 100%;
  overflow: visible;
  box-sizing: border-box;
  flex-wrap: wrap;
}

/* 标签样式 */
.label-renderer {
  font-size: 14px;
  color: #333;
  padding: 4px 8px;
}

/* 未知组件 */
.unknown-component {
  padding: 8px 12px;
  border: 1px solid #dc3545;
  background: rgba(220, 53, 69, 0.1);
  border-radius: 4px;
  color: #dc3545;
  font-size: 12px;
}
</style>