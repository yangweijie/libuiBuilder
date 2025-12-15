<template>
  <div :class="itemClass" :style="itemStyle">
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  gridRowStart?: number | string
  gridColumnStart?: number | string
  gridRowEnd?: number | string
  gridColumnEnd?: number | string
  align?: 'fill' | 'start' | 'center' | 'end'
  expand?: 'none' | 'horizontal' | 'vertical' | 'both'
}

const props = withDefaults(defineProps<Props>(), {
  gridRowStart: undefined,
  gridColumnStart: undefined,
  gridRowEnd: undefined,
  gridColumnEnd: undefined,
  align: 'fill',
  expand: 'none'
})

// 计算项目类名
const itemClass = computed(() => ({
  'libui-grid-item': true,
  [`libui-grid-item--align-${props.align}`]: true,
  [`libui-grid-item--expand-${props.expand}`]: true
}))

// 计算项目样式
const itemStyle = computed(() => {
  const style: Record<string, any> = {}
  
  // 网格位置
  if (props.gridRowStart !== undefined) {
    style.gridRowStart = props.gridRowStart
  }
  
  if (props.gridColumnStart !== undefined) {
    style.gridColumnStart = props.gridColumnStart
  }
  
  if (props.gridRowEnd !== undefined) {
    style.gridRowEnd = props.gridRowEnd
  }
  
  if (props.gridColumnEnd !== undefined) {
    style.gridColumnEnd = props.gridColumnEnd
  }
  
  // 对齐方式
  switch (props.align) {
    case 'start':
      style.alignSelf = 'start'
      style.justifySelf = 'start'
      break
    case 'center':
      style.alignSelf = 'center'
      style.justifySelf = 'center'
      break
    case 'end':
      style.alignSelf = 'end'
      style.justifySelf = 'end'
      break
    case 'fill':
    default:
      style.alignSelf = 'stretch'
      style.justifySelf = 'stretch'
      break
  }
  
  // 扩展方式
  switch (props.expand) {
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
  
  return style
})
</script>

<style scoped>
.libui-grid-item {
  display: flex;
  box-sizing: border-box;
}

.libui-grid-item--align-start {
  align-items: flex-start;
  justify-content: flex-start;
}

.libui-grid-item--align-center {
  align-items: center;
  justify-content: center;
}

.libui-grid-item--align-end {
  align-items: flex-end;
  justify-content: flex-end;
}

.libui-grid-item--align-fill {
  align-items: stretch;
  justify-content: stretch;
}
</style>