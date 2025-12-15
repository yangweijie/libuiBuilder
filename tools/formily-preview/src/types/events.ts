/**
 * 事件系统类型定义
 */

export interface EventHandler {
  name: string
  handler: (event: Event, state: any) => void
  componentId?: string
}

export interface EventBinding {
  eventName: string
  handlerName: string
  componentId: string
}

export interface EventConfig {
  handlers: Array<{
    key: string
    name: string
    componentId?: string
  }>
  bindings: EventBinding[]
}

export interface FormilyEventProps {
  [eventName: string]: (event: Event) => void
}

export interface EventManagerOptions {
  debug?: boolean
  strictMode?: boolean
  autoValidate?: boolean
}

// 标准事件类型
export type LibuiEventType = 
  | 'click'
  | 'change'
  | 'select'
  | 'toggle'
  | 'focus'
  | 'blur'
  | 'input'
  | 'submit'
  | 'keydown'
  | 'keyup'
  | 'mouseenter'
  | 'mouseleave'

// 事件映射
export const eventTypeMapping: Record<string, LibuiEventType> = {
  'onclick': 'click',
  'onchange': 'change',
  'onselected': 'select',
  'ontoggled': 'toggle',
  'onfocus': 'focus',
  'onblur': 'blur',
  'oninput': 'input',
  'onsubmit': 'submit',
  'onkeydown': 'keydown',
  'onkeyup': 'keyup',
  'onmouseenter': 'mouseenter',
  'onmouseleave': 'mouseleave'
}

// 组件支持的事件类型
export const componentEventSupport: Record<string, LibuiEventType[]> = {
  'Button': ['click', 'focus', 'blur', 'mouseenter', 'mouseleave'],
  'Input': ['change', 'input', 'focus', 'blur', 'keydown', 'keyup'],
  'Input.TextArea': ['change', 'input', 'focus', 'blur', 'keydown', 'keyup'],
  'Select': ['change', 'focus', 'blur'],
  'Checkbox': ['change', 'toggle', 'focus', 'blur'],
  'Radio.Group': ['change', 'focus', 'blur'],
  'Table': ['select', 'click'],
  'Progress': ['click'],
  'Divider': ['click']
}

// 事件处理器工厂
export interface EventHandlerFactory {
  createHandler(name: string, handler: (event: Event, state: any) => void): EventHandler
  createBinding(componentId: string, eventName: string, handlerName: string): EventBinding
  createFormilyProps(componentId: string, bindings: EventBinding[]): FormilyEventProps
}

// 事件验证结果
export interface EventValidationResult {
  valid: boolean
  errors: string[]
  warnings: string[]
  suggestions: string[]
}