/**
 * 事件管理器
 * 
 * 兼容现有 libuiBuilder 事件系统 API 的 Vue 3 实现
 */

import { stateManager } from '@/state'

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

export class EventManager {
  private static instance: EventManager
  private handlers = new Map<string, EventHandler>()
  private bindings = new Map<string, EventBinding>()
  private componentEvents = new Map<string, Set<string>>()

  /**
   * 获取单例实例
   */
  static getInstance(): EventManager {
    if (!EventManager.instance) {
      EventManager.instance = new EventManager()
    }
    return EventManager.instance
  }

  /**
   * 注册事件处理器
   */
  registerHandler(name: string, handler: (event: Event, state: any) => void, componentId?: string): void {
    const handlerKey = componentId ? `${componentId}.${name}` : name
    
    this.handlers.set(handlerKey, {
      name,
      handler,
      componentId
    })
    
    console.log(`✅ 注册事件处理器: ${handlerKey}`)
  }

  /**
   * 注册多个事件处理器
   */
  registerHandlers(handlers: Record<string, (event: Event, state: any) => void>): void {
    for (const [name, handler] of Object.entries(handlers)) {
      this.registerHandler(name, handler)
    }
  }

  /**
   * 绑定事件到组件
   */
  bindEvent(componentId: string, eventName: string, handlerName: string): void {
    const bindingKey = `${componentId}.${eventName}`
    
    this.bindings.set(bindingKey, {
      eventName,
      handlerName,
      componentId
    })
    
    // 记录组件的事件
    if (!this.componentEvents.has(componentId)) {
      this.componentEvents.set(componentId, new Set())
    }
    this.componentEvents.get(componentId)!.add(eventName)
    
    console.log(`🔗 绑定事件: ${componentId}.${eventName} -> ${handlerName}`)
  }

  /**
   * 触发事件
   */
  triggerEvent(componentId: string, eventName: string, event: Event): void {
    const bindingKey = `${componentId}.${eventName}`
    const binding = this.bindings.get(bindingKey)
    
    if (!binding) {
      console.warn(`⚠️ 未找到事件绑定: ${bindingKey}`)
      return
    }

    // 查找处理器
    const handlerKey = binding.handlerName.includes('.') 
      ? binding.handlerName 
      : binding.handlerName
    
    const handler = this.handlers.get(handlerKey)
    
    if (!handler) {
      console.warn(`⚠️ 未找到事件处理器: ${handlerKey}`)
      return
    }

    try {
      // 获取当前状态
      const currentState = stateManager.dump()
      
      console.log(`🎯 触发事件: ${bindingKey} -> ${handlerKey}`)
      console.log('📊 当前状态:', currentState)
      
      // 执行处理器
      handler.handler(event, currentState)
    } catch (error) {
      console.error(`❌ 事件处理器执行失败: ${handlerKey}`, error)
    }
  }

  /**
   * 获取组件的事件绑定
   */
  getComponentBindings(componentId: string): EventBinding[] {
    const bindings: EventBinding[] = []
    
    for (const [key, binding] of this.bindings.entries()) {
      if (binding.componentId === componentId) {
        bindings.push(binding)
      }
    }
    
    return bindings
  }

  /**
   * 获取事件处理器
   */
  getHandler(name: string, componentId?: string): EventHandler | undefined {
    const handlerKey = componentId ? `${componentId}.${name}` : name
    return this.handlers.get(handlerKey)
  }

  /**
   * 移除事件处理器
   */
  removeHandler(name: string, componentId?: string): boolean {
    const handlerKey = componentId ? `${componentId}.${name}` : name
    return this.handlers.delete(handlerKey)
  }

  /**
   * 移除组件的事件绑定
   */
  removeComponentBindings(componentId: string): void {
    // 移除绑定
    for (const [key, binding] of this.bindings.entries()) {
      if (binding.componentId === componentId) {
        this.bindings.delete(key)
      }
    }
    
    // 移除组件事件记录
    this.componentEvents.delete(componentId)
    
    console.log(`🗑️ 移除组件事件绑定: ${componentId}`)
  }

  /**
   * 获取所有事件处理器
   */
  getAllHandlers(): EventHandler[] {
    return Array.from(this.handlers.values())
  }

  /**
   * 获取所有事件绑定
   */
  getAllBindings(): EventBinding[] {
    return Array.from(this.bindings.values())
  }

  /**
   * 清空所有事件处理器和绑定
   */
  clear(): void {
    this.handlers.clear()
    this.bindings.clear()
    this.componentEvents.clear()
    
    console.log('🧹 清空所有事件处理器和绑定')
  }

  /**
   * 为 Formily 创建事件处理器
   */
  createFormilyHandler(componentId: string, eventName: string): (event: Event) => void {
    return (event: Event) => {
      this.triggerEvent(componentId, eventName, event)
    }
  }

  /**
   * 为组件创建 Formily 事件属性
   */
  createFormilyEventProps(componentId: string): Record<string, (event: Event) => void> {
    const eventProps: Record<string, (event: Event) => void> = {}
    const bindings = this.getComponentBindings(componentId)
    
    for (const binding of bindings) {
      eventProps[binding.eventName] = this.createFormilyHandler(componentId, binding.eventName)
    }
    
    return eventProps
  }

  /**
   * 从 HTML 解析结果创建事件绑定
   */
  createBindingsFromHtml(htmlBindings: Array<{
    componentId: string
    eventName: string
    handlerName: string
  }>): void {
    for (const binding of htmlBindings) {
      this.bindEvent(binding.componentId, binding.eventName, binding.handlerName)
    }
  }

  /**
   * 验证事件绑定
   */
  validateBindings(): { valid: boolean; errors: string[] } {
    const errors: string[] = []
    
    for (const [key, binding] of this.bindings.entries()) {
      const handlerKey = binding.handlerName.includes('.') 
        ? binding.handlerName 
        : binding.handlerName
      
      if (!this.handlers.has(handlerKey)) {
        errors.push(`未找到事件处理器: ${handlerKey} (绑定: ${key})`)
      }
    }
    
    return {
      valid: errors.length === 0,
      errors
    }
  }

  /**
   * 导出事件配置为 JSON
   */
  toJSON(): string {
    const config = {
      handlers: Array.from(this.handlers.entries()).map(([key, handler]) => ({
        key,
        name: handler.name,
        componentId: handler.componentId
      })),
      bindings: Array.from(this.bindings.values())
    }
    
    return JSON.stringify(config, null, 2)
  }

  /**
   * 从 JSON 导入事件配置
   */
  fromJSON(json: string): void {
    try {
      const config = JSON.parse(json)
      
      // 注意：处理器函数无法从 JSON 恢复，需要重新注册
      if (config.bindings) {
        for (const binding of config.bindings) {
          this.bindEvent(binding.componentId, binding.eventName, binding.handlerName)
        }
      }
      
      console.log('📥 从 JSON 导入事件绑定')
    } catch (error) {
      console.error('事件配置导入失败:', error)
      throw new Error('无效的 JSON 格式')
    }
  }
}

// 导出单例实例
export const eventManager = EventManager.getInstance()

// Vue 组合式 API 封装
export function useEventManager() {
  const instance = EventManager.getInstance()
  
  return {
    // 处理器管理
    registerHandler: instance.registerHandler.bind(instance),
    registerHandlers: instance.registerHandlers.bind(instance),
    getHandler: instance.getHandler.bind(instance),
    removeHandler: instance.removeHandler.bind(instance),
    
    // 事件绑定
    bindEvent: instance.bindEvent.bind(instance),
    triggerEvent: instance.triggerEvent.bind(instance),
    getComponentBindings: instance.getComponentBindings.bind(instance),
    removeComponentBindings: instance.removeComponentBindings.bind(instance),
    
    // 工具方法
    getAllHandlers: instance.getAllHandlers.bind(instance),
    getAllBindings: instance.getAllBindings.bind(instance),
    clear: instance.clear.bind(instance),
    validateBindings: instance.validateBindings.bind(instance),
    toJSON: instance.toJSON.bind(instance),
    fromJSON: instance.fromJSON.bind(instance),
    
    // Formily 集成
    createFormilyHandler: instance.createFormilyHandler.bind(instance),
    createFormilyEventProps: instance.createFormilyEventProps.bind(instance),
    createBindingsFromHtml: instance.createBindingsFromHtml.bind(instance)
  }
}