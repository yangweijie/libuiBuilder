<template>
  <div 
    class="component-preview"
    :class="{ selected: selected, 'is-window': component.type === 'window' }"
    @click.stop="$emit('select', component)"
  >
    <!-- 渲染实际组件 -->
    <div class="preview-content">
      <component-renderer :component="component" />
    </div>
    
    <!-- 控制按钮（仅在选中时显示） -->
    <div v-if="selected" class="preview-controls">
      <a-button 
        size="small" 
        type="text"
        class="control-btn delete"
        @click.stop="$emit('delete', component)"
      >
        删除
      </a-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import ComponentRenderer from './ComponentRenderer.vue'
import type { ComponentConfig } from '@/types'

const props = defineProps<{
  component: ComponentConfig
  selected: boolean
}>()

defineEmits<{
  select: [component: ComponentConfig]
  delete: [component: ComponentConfig]
}>()
</script>

<style scoped>
.component-preview {
  position: relative;
  padding: 8px;
  margin: 8px 4px 8px 4px; /* 增加上边距为删除按钮留出空间 */
  border: 2px solid transparent;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  box-sizing: border-box;
  width: fit-content;
  max-width: 100%;
  overflow: visible;
  min-height: 50px; /* 增加最小高度 */
}

/* 窗口组件的特殊样式 */
.component-preview.is-window {
  width: 100%;
  height: 100%;
  min-height: 600px; /* 确保有足够的高度 */
  padding: 0;
  margin: 0;
}

.component-preview:hover {
  border-color: #1890ff;
  box-shadow: 0 2px 8px rgba(24, 144, 255, 0.1);
}

.component-preview.selected {
  border-color: #1890ff;
  background: #e6f7ff;
}

.preview-content {
  /* 允许子组件接收点击事件 */
}

.preview-controls {
  position: absolute;
  top: -25px;
  right: 0;
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s;
  z-index: 9999; /* 提高z-index确保在最上层 */
  pointer-events: auto;
}

.component-preview.selected .preview-controls {
  opacity: 1;
  display: flex; /* 确保选中时显示 */
}

.control-btn.delete {
  color: #ff4d4f;
  background: white;
  border: 1px solid #ff4d4f;
  border-radius: 4px;
  font-size: 12px;
  height: 24px;
  padding: 0 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  pointer-events: auto;
  position: relative;
  font-weight: 500;
  min-width: 50px;
  transform: scale(0.9);
  transition: all 0.2s;
}

.control-btn.delete:hover {
  background: #ff4d4f;
  color: white;
  transform: scale(1);
  box-shadow: 0 3px 6px rgba(255, 77, 79, 0.3);
}

/* 窗口组件的删除按钮样式 */
.component-preview.is-window .preview-controls {
  top: 8px;
  right: 8px;
  z-index: 10000;
}

/* 容器组件（grid, vbox, hbox, tab）的删除按钮样式 */
.component-preview:not(.is-window) .preview-controls {
  top: -25px;
  right: 0;
  z-index: 10000;
}
</style>