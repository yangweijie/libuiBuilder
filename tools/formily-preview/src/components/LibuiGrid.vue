<template>
  <div :class="gridClass" :style="gridStyle">
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  padded?: boolean
  gap?: number
  columns?: number
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  padded: false,
  gap: 8,
  columns: 12,
  rows: undefined
})

// 计算网格类名
const gridClass = computed(() => ({
  'libui-grid': true,
  'libui-grid--padded': props.padded
}))

// 计算网格样式
const gridStyle = computed(() => {
  const style: Record<string, any> = {
    display: 'grid',
    gap: `${props.gap}px`
  }
  
  // 设置网格列
  if (props.columns) {
    style.gridTemplateColumns = `repeat(${props.columns}, 1fr)`
  }
  
  // 设置网格行（如果有）
  if (props.rows) {
    style.gridTemplateRows = `repeat(${props.rows}, auto)`
  }
  
  return style
})
</script>

<style scoped>
.libui-grid {
  width: 100%;
  height: 100%;
}

.libui-grid--padded {
  padding: 16px;
}
</style>