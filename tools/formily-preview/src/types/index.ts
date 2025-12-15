/**
 * 类型定义文件
 */

// 导入事件类型
export type { EventHandler, EventBinding } from './events'

// Formily Schema 类型
export interface FormilySchema {
  type: 'object' | 'void' | 'string' | 'number' | 'boolean' | 'array'
  title?: string
  description?: string
  'x-component'?: string
  'x-decorator'?: string
  'x-component-props'?: Record<string, any>
  'x-decorator-props'?: Record<string, any>
  'x-reactions'?: any
  properties?: Record<string, FormilySchema>
  items?: FormilySchema
  default?: any
  enum?: any[]
  required?: boolean
  [key: string]: any
}

// HTML 解析选项
export interface HtmlParseOptions {
  preserveComments?: boolean
  validateSchema?: boolean
  strictMode?: boolean
  [key: string]: any
}

// 状态绑定
export interface StateBinding {
  key: string
  defaultValue: any
  watchers?: Array<(value: any, oldValue: any, key: string) => void>
}

// 事件处理器
export interface EventHandler {
  name: string
  handler: (event: Event, state: any) => void
}

// 组件属性
export interface ComponentAttribute {
  htmlName: string
  formilyName: string
  type: 'string' | 'number' | 'boolean' | 'object' | 'array'
  defaultValue?: any
  transform?: (value: string) => any
}

// 主题配置
export interface ThemeConfig {
  name: string
  variables: Record<string, string>
  styles: Record<string, string>
}

// 组件配置
export interface ComponentConfig {
  id: string
  type: string
  properties: Record<string, any>
  children?: ComponentConfig[]
  layout?: {
    row?: number
    col?: number
    rowspan?: number
    colspan?: number
    align?: string
    expand?: string
  }
}

// 设计器状态
export interface DesignerState {
  components: ComponentConfig[]
  selectedComponent: ComponentConfig | null
  currentTheme: string
  showGrid: boolean
  showBindings: boolean
}

// 预览状态
export interface PreviewState {
  currentHtml: string
  formilySchema: FormilySchema | null
  parseError: string | null
  isParsing: boolean
  previewScale: number
  showGrid: boolean
  showBindings: boolean
  stateBindings: StateBinding[]
  eventHandlers: EventHandler[]
  componentProperties: Array<{ name: string; value: any }>
  layoutInfo: {
    rootElement: string
    componentCount: number
    layoutType: string
    stateBindingCount: number
    eventHandlerCount: number
  } | null
}

// 文件信息
export interface FileInfo {
  id: string
  name: string
  size: number
  modified: string
  content?: string
}

// 解析结果
export interface ParseResult {
  schema: FormilySchema
  stateBindings: StateBinding[]
  eventHandlers: EventHandler[]
  warnings: string[]
  errors: string[]
}