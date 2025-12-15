<template>
  <div 
    class="component-node"
    :class="{ selected, 'has-children': hasChildren }"
    @click.stop="$emit('select', component)"
  >
    <div class="node-header">
      <div class="node-info">
        <div class="node-icon">{{ getComponentIcon(component.type) }}</div>
        <div class="node-name">{{ getComponentName(component.type) }}</div>
      </div>
      
      <div class="node-actions">
        <a-button 
          size="small" 
          type="text"
          @click.stop="$emit('delete', component)"
        >
          删除
        </a-button>
      </div>
    </div>
    
    <!-- 简化属性显示，仅显示关键属性 -->
    <div v-if="showSimplifiedProperties" class="node-properties-simple">
      <template v-for="(value, key) in component.properties" :key="key">
        <span v-if="isImportantProperty(key, value)" class="property-simple">
          {{ formatPropertySimple(value) }}
        </span>
      </template>
    </div>
    
    <div v-if="hasChildren" class="node-children-count">
      <span class="children-icon">📁</span>
      <span class="children-text">{{ component.children?.length }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, computed } from 'vue'
import type { ComponentConfig } from '@/types'

const props = defineProps<{
  component: ComponentConfig
  selected: boolean
}>()

defineEmits<{
  select: [component: ComponentConfig]
  update: [component: ComponentConfig]
  delete: [component: ComponentConfig]
}>()

const hasChildren = computed(() => {
  return props.component.children && props.component.children.length > 0
})

const showSimplifiedProperties = computed(() => {
  // 只显示重要的属性
  const importantProps = Object.keys(props.component.properties).filter(key => 
    isImportantProperty(key, props.component.properties[key])
  )
  return importantProps.length > 0
})

const getComponentIcon = (type: string): string => {
  const icons: Record<string, string> = {
    window: '🪟',
    grid: '📊',
    vbox: '📦',
    hbox: '📦',
    tab: '📑',
    input: '📝',
    textarea: '📄',
    button: '🔘',
    checkbox: '☑️',
    radio: '🔘',
    select: '📋',
    label: '🏷️',
    progressbar: '📊',
    separator: '➖',
    table: '📋'
  }
  return icons[type] || '📦'
}

const getComponentName = (type: string): string => {
  const names: Record<string, string> = {
    window: '窗口',
    grid: '网格',
    vbox: '垂直盒子',
    hbox: '水平盒子',
    tab: '标签页',
    input: '输入框',
    textarea: '多行输入',
    button: '按钮',
    checkbox: '复选框',
    radio: '单选框',
    select: '下拉框',
    label: '标签',
    progressbar: '进度条',
    separator: '分隔符',
    table: '表格'
  }
  return names[type] || '未知组件'
}

// 判断是否为重要属性（需要显示的属性）
const isImportantProperty = (key: string, value: any): boolean => {
  const importantKeys = ['text', 'title', 'placeholder', 'value', 'checked']
  return importantKeys.includes(key) && value !== undefined && value !== null && value !== ''
}

// 简化属性显示
const formatPropertySimple = (value: any): string => {
  if (value === null || value === undefined) {
    return ''
  }
  
  if (typeof value === 'boolean') {
    return value ? '✓' : '✗'
  }
  
  if (typeof value === 'string') {
    if (value.length > 10) {
      return value.substring(0, 8) + '...'
    }
    return value
  }
  
  if (typeof value === 'number') {
    return String(value)
  }
  
  return ''
}
</script>

<style scoped>
.component-node {
  padding: 8px 12px;
  background: white;
  border: 1px solid #e8e8e8;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.component-node:hover {
  border-color: #1890ff;
  background: #f0f8ff;
}

.component-node.selected {
  border-color: #1890ff;
  background: #e6f7ff;
}

.component-node.has-children {
  border-left: 2px solid #52c41a;
}

.node-header {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.node-info {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.node-icon {
  font-size: 16px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  border-radius: 3px;
  flex-shrink: 0;
}

.node-name {
  font-weight: 500;
  color: #333;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.node-actions {
  opacity: 0;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.component-node:hover .node-actions {
  opacity: 1;
}

.node-properties-simple {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.property-simple {
  font-size: 11px;
  color: #666;
  background: #f0f0f0;
  padding: 1px 4px;
  border-radius: 2px;
  white-space: nowrap;
}

.node-children-count {
  font-size: 11px;
  color: #52c41a;
  background: #f6ffed;
  padding: 1px 6px;
  border-radius: 10px;
  border: 1px solid #b7eb8f;
  flex-shrink: 0;
}

.children-icon {
  font-size: 12px;
  margin-right: 2px;
}
</style>