/**
 * HTML 标签到 Formily 组件的映射配置
 */

import type { ComponentAttribute } from '@/types'

export interface ComponentMapping {
  formilyComponent: string
  formilyDecorator?: string
  attributes: ComponentAttribute[]
  children?: 'single' | 'multiple' | 'none'
  defaultProps?: Record<string, any>
}

// HTML 标签到 Formily 组件的映射
export const componentMappings: Record<string, ComponentMapping> = {
  // 容器组件
  window: {
    formilyComponent: 'LibuiForm',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'title', formilyName: 'title', type: 'string', defaultValue: 'Window' },
      { htmlName: 'size', formilyName: 'size', type: 'string', transform: (value) => {
        const [width, height] = value.split(',').map(v => parseInt(v.trim()))
        return { width, height }
      }},
      { htmlName: 'centered', formilyName: 'centered', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'margined', formilyName: 'margined', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'single',
    defaultProps: {
      layout: 'vertical'
    }
  },

  grid: {
    formilyComponent: 'LibuiGrid',
    attributes: [
      { htmlName: 'padded', formilyName: 'padded', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'gap', formilyName: 'gap', type: 'number', transform: (value) => parseInt(value), defaultValue: 8 }
    ],
    children: 'multiple',
    defaultProps: {
      gap: 8,
      columns: 12
    }
  },

  vbox: {
    formilyComponent: 'LibuiBox',
    attributes: [
      { htmlName: 'padded', formilyName: 'padded', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'multiple',
    defaultProps: {
      direction: 'vertical',
      spacing: 8
    }
  },

  hbox: {
    formilyComponent: 'LibuiBox',
    attributes: [
      { htmlName: 'padded', formilyName: 'padded', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'multiple',
    defaultProps: {
      direction: 'horizontal',
      spacing: 8
    }
  },

  // 输入组件
  input: {
    formilyComponent: 'LibuiInput',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'type', formilyName: 'type', type: 'string', defaultValue: 'text' },
      { htmlName: 'placeholder', formilyName: 'placeholder', type: 'string' },
      { htmlName: 'value', formilyName: 'value', type: 'string' },
      { htmlName: 'readonly', formilyName: 'readOnly', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'bind', formilyName: 'bind', type: 'string' },
      { htmlName: 'stretchy', formilyName: 'stretchy', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  textarea: {
    formilyComponent: 'Input.TextArea',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'placeholder', formilyName: 'placeholder', type: 'string' },
      { htmlName: 'value', formilyName: 'value', type: 'string' },
      { htmlName: 'readonly', formilyName: 'readOnly', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'rows', formilyName: 'rows', type: 'number', transform: (value) => parseInt(value) },
      { htmlName: 'stretchy', formilyName: 'stretchy', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  button: {
    formilyComponent: 'Button',
    attributes: [
      { htmlName: 'text', formilyName: 'children', type: 'string' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'type', formilyName: 'type', type: 'string', defaultValue: 'default' },
      { htmlName: 'stretchy', formilyName: 'stretchy', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  checkbox: {
    formilyComponent: 'Checkbox',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'text', formilyName: 'children', type: 'string' },
      { htmlName: 'checked', formilyName: 'checked', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  radio: {
    formilyComponent: 'Radio.Group',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'value', formilyName: 'value', type: 'string' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'multiple'
  },

  select: {
    formilyComponent: 'Select',
    formilyDecorator: 'FormItem',
    attributes: [
      { htmlName: 'value', formilyName: 'value', type: 'string' },
      { htmlName: 'disabled', formilyName: 'disabled', type: 'boolean', transform: (value) => value === 'true' },
      { htmlName: 'placeholder', formilyName: 'placeholder', type: 'string' }
    ],
    children: 'multiple'
  },

  // 显示组件
  label: {
    formilyComponent: 'Typography.Text',
    attributes: [
      { htmlName: 'text', formilyName: 'children', type: 'string' },
      { htmlName: 'stretchy', formilyName: 'stretchy', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  progressbar: {
    formilyComponent: 'Progress',
    attributes: [
      { htmlName: 'value', formilyName: 'percent', type: 'number', transform: (value) => parseInt(value) },
      { htmlName: 'max', formilyName: 'max', type: 'number', transform: (value) => parseInt(value), defaultValue: 100 }
    ],
    children: 'none'
  },

  separator: {
    formilyComponent: 'Divider',
    attributes: [
      { htmlName: 'orientation', formilyName: 'type', type: 'string', defaultValue: 'horizontal' }
    ],
    children: 'none'
  },

  table: {
    formilyComponent: 'Table',
    attributes: [
      { htmlName: 'columns', formilyName: 'columns', type: 'string', transform: (value) => 
        value.split(',').map((col: string) => ({ title: col.trim(), dataIndex: col.trim().toLowerCase() }))
      },
      { htmlName: 'columnTypes', formilyName: 'columnTypes', type: 'string' },
      { htmlName: 'tableData', formilyName: 'tableData', type: 'string' },
      { htmlName: 'stretchy', formilyName: 'stretchy', type: 'boolean', transform: (value) => value === 'true' }
    ],
    children: 'none'
  },

  // Tab 组件
  tab: {
    formilyComponent: 'Tabs',
    attributes: [
      { htmlName: 'tabs', formilyName: 'items', type: 'string', transform: (value) => 
        value.split(',').map((tab: string, index) => ({ 
          key: String(index), 
          label: tab.trim(),
          forceRender: true
        }))
      },
      { htmlName: 'activeTab', formilyName: 'activeKey', type: 'string', defaultValue: '0' },
      { htmlName: 'padded', formilyName: 'size', type: 'string', transform: (value) => value === 'true' ? 'default' : 'small' }
    ],
    children: 'multiple'
  }
}

// HTML 标签别名映射
export const tagAliases: Record<string, string> = {
  // 输入控件别名
  'entry': 'input',
  'password': 'input',
  'multiline': 'textarea',
  'number': 'input',
  'range': 'input',
  
  // 选择控件别名
  'combobox': 'select',
  'spinbox': 'input',
  'slider': 'input',
  
  // 显示控件别名
  'progress': 'progressbar',
  'hr': 'separator',
  
  // 容器别名
  'group': 'vbox',
  'tab': 'vbox'
}

// 布局属性映射
export const layoutAttributes: ComponentAttribute[] = [
  { htmlName: 'row', formilyName: 'gridRowStart', type: 'number', transform: (value) => parseInt(value) + 1 },
  { htmlName: 'col', formilyName: 'gridColumnStart', type: 'number', transform: (value) => parseInt(value) + 1 },
  { htmlName: 'rowspan', formilyName: 'gridRowEnd', type: 'number', transform: (value) => {
    const row = parseInt(value) || 1
    return `span ${row}`
  }},
  { htmlName: 'colspan', formilyName: 'gridColumnEnd', type: 'number', transform: (value) => {
    const col = parseInt(value) || 1
    return `span ${col}`
  }},
  { htmlName: 'align', formilyName: 'align', type: 'string', defaultValue: 'fill' },
  { htmlName: 'expand', formilyName: 'expand', type: 'string', defaultValue: 'none' }
]

// 事件属性映射
export const eventAttributes: Record<string, string> = {
  'onclick': 'onClick',
  'onchange': 'onChange',
  'onselected': 'onSelect',
  'ontoggled': 'onToggle',
  'onfocus': 'onFocus',
  'onblur': 'onBlur'
}

// 数据绑定属性
export const bindingAttributes = ['bind', 'ref']

/**
 * 获取组件的映射配置
 */
export function getComponentMapping(tagName: string): ComponentMapping | null {
  // 首先检查别名
  const actualTagName = tagAliases[tagName] || tagName
  
  // 返回映射配置
  return componentMappings[actualTagName] || null
}

/**
 * 检查标签是否支持布局属性
 */
export function supportsLayout(tagName: string): boolean {
  const mapping = getComponentMapping(tagName)
  if (!mapping) return false
  
  // Grid 的子元素支持布局属性
  return mapping.formilyComponent !== 'Grid'
}

/**
 * 检查标签是否支持事件
 */
export function supportsEvents(tagName: string): boolean {
  const mapping = getComponentMapping(tagName)
  if (!mapping) return false
  
  // 按钮和输入控件支持事件
  const eventComponents = ['Button', 'Input', 'Input.TextArea', 'Select', 'Checkbox', 'Radio.Group']
  return eventComponents.includes(mapping.formilyComponent)
}

/**
 * 检查标签是否支持数据绑定
 */
export function supportsBinding(tagName: string): boolean {
  const mapping = getComponentMapping(tagName)
  if (!mapping) return false
  
  // 表单控件支持数据绑定
  const bindingComponents = ['Input', 'Input.TextArea', 'Select', 'Checkbox', 'Radio.Group']
  return bindingComponents.includes(mapping.formilyComponent)
}