/**
 * HTML 到 Formily Schema 解析器
 * 
 * 将 .ui.html 模板文件转换为 Formily JSON Schema
 */

import type { FormilySchema, HtmlParseOptions, StateBinding, EventHandler } from '@/types'
import { 
  getComponentMapping, 
  supportsLayout, 
  supportsEvents, 
  supportsBinding,
  layoutAttributes,
  eventAttributes,
  bindingAttributes
} from './componentMappings'

// 动态导入 jsdom，避免在浏览器环境中使用
let JSDOM: any = null
let DOMParser: any = null

async function loadJsdom() {
  if (typeof window === 'undefined') {
    // Node.js 环境
    const jsdomModule = await import('jsdom')
    const { JSDOM: jsdom } = jsdomModule
    JSDOM = jsdom
    DOMParser = new jsdom('').window.DOMParser
  } else {
    // 浏览器环境
    DOMParser = window.DOMParser
  }
}

export class HtmlParser {
  private dom: Document | null = null
  private stateBindings: Map<string, StateBinding> = new Map()
  private eventHandlers: Map<string, EventHandler> = new Map()
  private variables: Map<string, any> = new Map()
  private options: HtmlParseOptions

  constructor(options: HtmlParseOptions = {}) {
    this.options = {
      preserveComments: false,
      validateSchema: true,
      strictMode: false,
      ...options
    }
  }

  /**
   * 解析 HTML 字符串为 Formily Schema
   */
  async parse(html: string): Promise<{
    schema: FormilySchema
    stateBindings: StateBinding[]
    eventHandlers: EventHandler[]
  }> {
    // 重置状态
    this.reset()

    try {
      // 1. 加载 DOM 解析器
      await loadJsdom()

      // 2. 解析 HTML
      this.parseHtml(html)

      // 3. 查找根元素
      const rootElement = this.findRootElement()
      if (!rootElement) {
        throw new Error('未找到有效的根元素（window、grid、vbox、hbox 或 tab）')
      }

      // 4. 转换根元素
      const schema = this.convertElement(rootElement)

      // 5. 验证 Schema（如果启用）
      if (this.options.validateSchema) {
        this.validateSchema(schema)
      }

      return {
        schema,
        stateBindings: Array.from(this.stateBindings.values()),
        eventHandlers: Array.from(this.eventHandlers.values())
      }
    } catch (error) {
      throw new Error(`HTML 解析失败: ${error instanceof Error ? error.message : String(error)}`)
    }
  }

  /**
   * 解析 HTML 字符串为 DOM
   */
  private parseHtml(html: string): void {
    // 替换模板变量
    const processedHtml = this.replaceTemplateVariables(html)

    // 预处理 HTML：将自闭合标签转换为标准格式
    const preprocessedHtml = this.preprocessHtml(processedHtml)

    if (!DOMParser) {
      throw new Error('DOM 解析器未初始化')
    }
    
    // 创建 DOM 解析器
    const parser = new DOMParser()
    
    // 添加 XML 声明确保 UTF-8 编码
    const xmlHtml = '<?xml encoding="UTF-8">' + preprocessedHtml
    
    // 解析 HTML
    this.dom = parser.parseFromString(xmlHtml, 'text/html')
    
    // 检查解析错误
    const parserErrors = this.dom.querySelectorAll('parsererror')
    if (parserErrors.length > 0) {
      throw new Error('HTML 语法错误: ' + parserErrors[0].textContent)
    }
  }

  /**
   * 预处理 HTML：将自闭合标签转换为标准格式
   */
  private preprocessHtml(html: string): string {
    // 定义自闭合标签列表
    const selfClosingTags = [
      'input', 'img', 'br', 'hr', 'meta', 'link', 'base',
      'area', 'col', 'command', 'embed', 'keygen', 'param',
      'source', 'track', 'wbr'
    ]
    
    // 将自闭合标签转换为标准格式
    let processed = html
    
    // 处理自闭合标签：将 <tag /> 转换为 <tag></tag>
    for (const tag of selfClosingTags) {
      const regex = new RegExp(`<${tag}([^>]*)/>`, 'gi')
      processed = processed.replace(regex, `<${tag}$1></${tag}>`)
    }
    
    // 处理 libuiBuilder 特定的自闭合标签
    const libuiTags = ['separator', 'progressbar', 'progress', 'hr']
    for (const tag of libuiTags) {
      const regex = new RegExp(`<${tag}([^>]*)/>`, 'gi')
      processed = processed.replace(regex, `<${tag}$1></${tag}>`)
    }
    
    return processed
  }

  /**
   * 替换模板变量 {{variable}}
   */
  private replaceTemplateVariables(html: string): string {
    return html.replace(/\{\{(\w+)\}\}/g, (match, varName) => {
      return this.variables.get(varName) || match
    })
  }

  /**
   * 查找根元素
   */
  private findRootElement(): Element | null {
    if (!this.dom) return null

    // 优先查找 window 标签
    const windows = this.dom.querySelectorAll('window')
    if (windows.length > 0) {
      return windows[0]
    }

    // 查找其他可能的根元素
    const rootTags = ['grid', 'vbox', 'hbox', 'tab']
    for (const tag of rootTags) {
      const elements = this.dom.querySelectorAll(tag)
      if (elements.length > 0) {
        return elements[0]
      }
    }

    return null
  }

  /**
   * 转换单个元素为 Formily Schema
   */
  private convertElement(element: Element, parentIsGrid: boolean = false): FormilySchema {
    const tagName = element.tagName.toLowerCase()
    const mapping = getComponentMapping(tagName)

    if (!mapping) {
      if (this.options.strictMode) {
        throw new Error(`未知的 HTML 标签: ${tagName}`)
      }
      
      // 非严格模式下返回空 Schema
      console.warn(`未知的 HTML 标签: ${tagName}`)
      return { type: 'void' }
    }

    // 创建基础 Schema
    const schema: FormilySchema = {
      type: 'void'
    }

    // 设置 Formily 组件
    if (mapping.formilyComponent) {
      schema['x-component'] = mapping.formilyComponent
    }

    // 设置 Formily 装饰器
    if (mapping.formilyDecorator) {
      schema['x-decorator'] = mapping.formilyDecorator
    }

    // 处理组件属性
    const componentProps: Record<string, any> = { ...mapping.defaultProps }
    this.processAttributes(element, mapping, componentProps)
    
    // 处理布局属性（如果是 Grid 的子元素）
    if (parentIsGrid && supportsLayout(tagName)) {
      this.processLayoutAttributes(element, componentProps)
    }

    // 处理事件属性
    if (supportsEvents(tagName)) {
      this.processEventAttributes(element, tagName)
    }

    // 处理数据绑定属性
    if (supportsBinding(tagName)) {
      this.processBindingAttributes(element, tagName)
    }

    // 设置组件属性
    if (Object.keys(componentProps).length > 0) {
      schema['x-component-props'] = componentProps
    }

    // 处理子元素
    if (mapping.children !== 'none') {
      this.processChildren(element, schema, tagName === 'grid')
    }

    return schema
  }

  /**
   * 处理组件属性
   */
  private processAttributes(
    element: Element, 
    mapping: any, 
    props: Record<string, any>
  ): void {
    for (const attr of mapping.attributes) {
      const htmlValue = element.getAttribute(attr.htmlName)
      if (htmlValue !== null) {
        let value: any = htmlValue
        
        // 应用转换函数
        if (attr.transform) {
          try {
            value = attr.transform(htmlValue)
          } catch (error) {
            console.warn(`属性转换失败: ${attr.htmlName}=${htmlValue}`, error)
            value = attr.defaultValue
          }
        }
        
        // 设置默认值
        if (value === undefined || value === null) {
          value = attr.defaultValue
        }
        
        props[attr.formilyName] = value
      } else if (attr.defaultValue !== undefined) {
        // 使用默认值
        props[attr.formilyName] = attr.defaultValue
      }
    }

    // 处理文本内容（对于 label、button 等）
    if (element.textContent && element.textContent.trim()) {
      const textAttr = mapping.attributes.find((attr: any) => attr.formilyName === 'children')
      if (textAttr && !props.children) {
        props.children = element.textContent.trim()
      }
    }
  }

  /**
   * 处理布局属性
   */
  private processLayoutAttributes(element: Element, props: Record<string, any>): void {
    const layoutProps: Record<string, any> = {}
    
    for (const attr of layoutAttributes) {
      const htmlValue = element.getAttribute(attr.htmlName)
      if (htmlValue !== null) {
        let value: any = htmlValue
        
        if (attr.transform) {
          try {
            value = attr.transform(htmlValue)
          } catch (error) {
            console.warn(`布局属性转换失败: ${attr.htmlName}=${htmlValue}`, error)
          }
        }
        
        layoutProps[attr.formilyName] = value
      } else if (attr.defaultValue !== undefined) {
        layoutProps[attr.formilyName] = attr.defaultValue
      }
    }

    // 如果有布局属性，设置 LibuiGridItem 装饰器
    if (Object.keys(layoutProps).length > 0) {
      props['x-decorator'] = 'LibuiGridItem'
      props['x-decorator-props'] = layoutProps
    }
  }

  /**
   * 处理事件属性
   */
  private processEventAttributes(element: Element, tagName: string): void {
    for (const [htmlEvent, formilyEvent] of Object.entries(eventAttributes)) {
      const handlerName = element.getAttribute(htmlEvent)
      if (handlerName) {
        const componentId = element.getAttribute('id') || `component_${Date.now()}`
        const eventKey = `${componentId}.${formilyEvent}`
        
        this.eventHandlers.set(eventKey, {
          name: handlerName,
          handler: (event: Event, state: any) => {
            console.log(`事件触发: ${handlerName}`, { event, state })
            // 实际的事件处理逻辑将在运行时注入
          }
        })
      }
    }
  }

  /**
   * 处理数据绑定属性
   */
  private processBindingAttributes(element: Element, tagName: string): void {
    for (const attrName of bindingAttributes) {
      const bindingKey = element.getAttribute(attrName)
      if (bindingKey) {
        const componentId = element.getAttribute('id') || `component_${Date.now()}`
        
        this.stateBindings.set(bindingKey, {
          key: bindingKey,
          defaultValue: this.getDefaultValueForBinding(tagName, element)
        })
      }
    }
  }

  /**
   * 获取数据绑定的默认值
   */
  private getDefaultValueForBinding(tagName: string, element: Element): any {
    switch (tagName) {
      case 'input':
      case 'textarea':
        return element.getAttribute('value') || ''
      case 'checkbox':
        return element.getAttribute('checked') === 'true'
      case 'radio':
      case 'select':
        return element.getAttribute('value') || ''
      default:
        return null
    }
  }

  /**
   * 处理子元素
   */
  private processChildren(
    element: Element, 
    schema: FormilySchema, 
    isGrid: boolean
  ): void {
    const children: Element[] = []
    
    // 收集有效的子元素
    for (const child of element.children) {
      // 跳过 template 和特殊标签
      if (child.tagName.toLowerCase() === 'template') {
        continue
      }
      
      // 跳过 option 和 column 等特殊子元素
      const specialTags = ['option', 'column', 'tabpage']
      if (specialTags.includes(child.tagName.toLowerCase())) {
        continue
      }
      
      children.push(child)
    }

    // 调试信息：显示处理的子元素
    if (children.length > 0 && process.env.DEBUG) {
      console.log(`🔍 处理 ${element.tagName} 的 ${children.length} 个子元素:`)
      children.forEach((child, index) => {
        console.log(`  [${index}] ${child.tagName}: ${child.textContent?.trim() || 'no text'}`)
      })
      
      // 显示所有子节点（包括文本节点）
      console.log(`🔍 ${element.tagName} 的所有子节点 (${element.childNodes.length}):`)
      for (let i = 0; i < element.childNodes.length; i++) {
        const node = element.childNodes[i]
        if (node.nodeType === 1) { // 元素节点
          const elem = node as Element
          console.log(`  [${i}] ELEMENT: ${elem.tagName}`)
        } else if (node.nodeType === 3) { // 文本节点
          const text = node.textContent?.trim()
          if (text) {
            console.log(`  [${i}] TEXT: "${text}"`)
          }
        } else if (node.nodeType === 8) { // 注释节点
          console.log(`  [${i}] COMMENT: ${node.textContent}`)
        }
      }
    }

    // 转换子元素
    if (children.length > 0) {
      schema.properties = {}
      
      children.forEach((child, index) => {
        const childSchema = this.convertElement(child, isGrid)
        const childKey = `child_${index}`
        schema.properties![childKey] = childSchema
      })
    }
  }

  /**
   * 验证生成的 Schema
   */
  private validateSchema(schema: FormilySchema): void {
    if (!schema.type) {
      throw new Error('Schema 缺少 type 属性')
    }

    // 检查必需的属性
    const requiredProps = ['x-component']
    for (const prop of requiredProps) {
      if (!schema[prop]) {
        throw new Error(`Schema 缺少必需的属性: ${prop}`)
      }
    }

    // 递归验证子属性
    if (schema.properties) {
      for (const [key, childSchema] of Object.entries(schema.properties)) {
        this.validateSchema(childSchema)
      }
    }
  }

  /**
   * 重置解析器状态
   */
  private reset(): void {
    this.dom = null
    this.stateBindings.clear()
    this.eventHandlers.clear()
    this.variables.clear()
  }

  /**
   * 设置模板变量
   */
  setVariable(name: string, value: any): void {
    this.variables.set(name, value)
  }

  /**
   * 设置多个模板变量
   */
  setVariables(variables: Record<string, any>): void {
    for (const [name, value] of Object.entries(variables)) {
      this.setVariable(name, value)
    }
  }
}