<template>
  <a-form
    v-bind="formProps"
    :style="formStyle"
    @finish="handleFinish"
    @finish-failed="handleFinishFailed"
  >
    <slot></slot>
  </a-form>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { FormProps } from 'ant-design-vue'

interface Props {
  title?: string
  size?: { width: number; height: number } | string
  layout?: 'horizontal' | 'vertical' | 'inline'
  centered?: boolean
  margined?: boolean
  model?: Record<string, any>
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Window',
  size: () => ({ width: 400, height: 300 }),
  layout: 'vertical',
  centered: false,
  margined: false,
  model: () => ({})
})

// 计算表单样式
const formStyle = computed(() => {
  const style: Record<string, any> = {}
  
  // 处理尺寸
  if (typeof props.size === 'string') {
    const [width, height] = props.size.split(',').map(v => parseInt(v.trim()))
    style.width = `${width}px`
    style.height = `${height}px`
  } else if (props.size) {
    style.width = `${props.size.width}px`
    style.height = `${props.size.height}px`
  }
  
  // 居中显示
  if (props.centered) {
    style.margin = '0 auto'
  }
  
  // 边距
  if (props.margined) {
    style.padding = '20px'
  }
  
  return style
})

// 计算表单属性
const formProps = computed<FormProps>(() => ({
  layout: props.layout,
  model: props.model,
  labelCol: { span: 6 },
  wrapperCol: { span: 18 }
}))

// 表单提交事件
const handleFinish = (values: any) => {
  console.log('表单提交成功:', values)
  emit('submit', values)
}

// 表单提交失败事件
const handleFinishFailed = (errors: any) => {
  console.log('表单提交失败:', errors)
  emit('submitFailed', errors)
}

const emit = defineEmits<{
  submit: [values: any]
  submitFailed: [errors: any]
}>()
</script>

<style scoped>
.libui-form {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
</style>