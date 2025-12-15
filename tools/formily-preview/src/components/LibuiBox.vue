<template>
  <div :class="boxClass" :style="boxStyle">
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  direction?: 'horizontal' | 'vertical'
  padded?: boolean
  spacing?: number | string
  align?: 'start' | 'center' | 'end' | 'stretch'
  justify?: 'start' | 'center' | 'end' | 'space-between' | 'space-around'
  wrap?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  direction: 'vertical',
  padded: false,
  spacing: 8,
  align: 'start',
  justify: 'start',
  wrap: false
})

// 计算盒子类名
const boxClass = computed(() => ({
  'libui-box': true,
  [`libui-box--${props.direction}`]: true,
  'libui-box--padded': props.padded,
  'libui-box--wrap': props.wrap
}))

// 计算盒子样式
const boxStyle = computed(() => {
  const style: Record<string, any> = {
    display: 'flex'
  }
  
  // 方向
  style.flexDirection = props.direction === 'vertical' ? 'column' : 'row'
  
  // 间距
  if (props.spacing) {
    const gap = typeof props.spacing === 'number' ? `${props.spacing}px` : props.spacing
    style.gap = gap
  }
  
  // 对齐方式
  if (props.direction === 'vertical') {
    style.alignItems = props.align
    style.justifyContent = props.justify
  } else {
    style.alignItems = props.justify
    style.justifyContent = props.align
  }
  
  // 换行
  if (props.wrap) {
    style.flexWrap = 'wrap'
  }
  
  // 内边距
  if (props.padded) {
    style.padding = '16px'
  }
  
  return style
})
</script>

<style scoped>
.libui-box {
  width: 100%;
  height: 100%;
}

.libui-box--horizontal {
  flex-direction: row;
}

.libui-box--vertical {
  flex-direction: column;
}

.libui-box--padded {
  padding: 16px;
}

.libui-box--wrap {
  flex-wrap: wrap;
}
</style>