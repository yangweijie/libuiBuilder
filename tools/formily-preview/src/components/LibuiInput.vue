<template>
  <a-input
    v-bind="inputProps"
    v-model:value="localValue"
    @change="handleChange"
    @focus="handleFocus"
    @blur="handleBlur"
  />
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import type { InputProps } from 'ant-design-vue'

interface Props {
  modelValue?: string
  type?: string
  placeholder?: string
  value?: string
  readonly?: boolean
  disabled?: boolean
  bind?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  type: 'text',
  placeholder: '',
  value: '',
  readonly: false,
  disabled: false,
  bind: ''
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  change: [value: string, event: Event]
  focus: [event: FocusEvent]
  blur: [event: FocusEvent]
}>()

// 本地值
const localValue = computed({
  get: () => props.modelValue || props.value || '',
  set: (value) => {
    emit('update:modelValue', value)
  }
})

// 计算输入属性
const inputProps = computed<InputProps>(() => ({
  type: props.type,
  placeholder: props.placeholder,
  readonly: props.readonly,
  disabled: props.disabled,
  allowClear: true
}))

// 监听绑定键变化
watch(() => props.bind, (newBindKey) => {
  if (newBindKey) {
    console.log(`输入框绑定到状态键: ${newBindKey}`)
    // 这里可以添加状态管理逻辑
  }
}, { immediate: true })

// 事件处理
const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value
  emit('change', value, event)
  
  // 如果有绑定键，更新状态
  if (props.bind) {
    console.log(`更新状态 ${props.bind}: ${value}`)
    // 这里可以调用状态管理器
  }
}

const handleFocus = (event: FocusEvent) => {
  emit('focus', event)
}

const handleBlur = (event: FocusEvent) => {
  emit('blur', event)
}
</script>

<style scoped>
.libui-input {
  width: 100%;
}
</style>