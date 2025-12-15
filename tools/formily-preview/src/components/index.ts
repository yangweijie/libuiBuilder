/**
 * libuiBuilder 组件库
 */

import type { App } from 'vue'
import LibuiForm from './LibuiForm.vue'
import LibuiGrid from './LibuiGrid.vue'
import LibuiGridItem from './LibuiGridItem.vue'
import LibuiBox from './LibuiBox.vue'
import LibuiInput from './LibuiInput.vue'

// 导出所有组件
export {
  LibuiForm,
  LibuiGrid,
  LibuiGridItem,
  LibuiBox,
  LibuiInput
}

// 组件类型
export type { 
  LibuiForm as Form,
  LibuiGrid as Grid,
  LibuiGridItem as GridItem,
  LibuiBox as Box,
  LibuiInput as Input
}

// 安装函数
export function installLibuiComponents(app: App) {
  app.component('LibuiForm', LibuiForm)
  app.component('LibuiGrid', LibuiGrid)
  app.component('LibuiGridItem', LibuiGridItem)
  app.component('LibuiBox', LibuiBox)
  app.component('LibuiInput', LibuiInput)
}

// 默认导出安装函数
export default {
  install: installLibuiComponents
}