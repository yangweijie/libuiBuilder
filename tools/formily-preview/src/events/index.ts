/**
 * 事件管理辅助函数
 * 
 * 提供与 libuiBuilder 兼容的 API
 */

import { eventManager, useEventManager } from './EventManager'

// 导出事件管理器
export { eventManager, useEventManager }

// 兼容 libuiBuilder API
export const events = {
  // 获取事件管理器实例
  instance: () => eventManager,
  
  // 注册事件处理器
  register: (name: string, handler: (event: Event, state: any) => void, componentId?: string) => 
    eventManager.registerHandler(name, handler, componentId),
  
  // 注册多个事件处理器
  registerAll: (handlers: Record<string, (event: Event, state: any) => void>) => 
    eventManager.registerHandlers(handlers),
  
  // 绑定事件到组件
  bind: (componentId: string, eventName: string, handlerName: string) => 
    eventManager.bindEvent(componentId, eventName, handlerName),
  
  // 触发事件
  trigger: (componentId: string, eventName: string, event: Event) => 
    eventManager.triggerEvent(componentId, eventName, event),
  
  // 获取事件处理器
  getHandler: (name: string, componentId?: string) => 
    eventManager.getHandler(name, componentId),
  
  // 移除事件处理器
  removeHandler: (name: string, componentId?: string) => 
    eventManager.removeHandler(name, componentId),
  
  // 移除组件的事件绑定
  removeComponentBindings: (componentId: string) => 
    eventManager.removeComponentBindings(componentId),
  
  // 获取所有事件处理器
  getAllHandlers: () => eventManager.getAllHandlers(),
  
  // 获取所有事件绑定
  getAllBindings: () => eventManager.getAllBindings(),
  
  // 清空所有事件处理器和绑定
  clear: () => eventManager.clear(),
  
  // 验证事件绑定
  validate: () => eventManager.validateBindings(),
  
  // 导出事件配置为 JSON
  toJSON: () => eventManager.toJSON(),
  
  // 从 JSON 导入事件配置
  fromJSON: (json: string) => eventManager.fromJSON(json)
}

// Vue 组合式 API
export function useEvents() {
  const manager = useEventManager()
  
  return {
    // 处理器管理
    register: manager.registerHandler,
    registerAll: manager.registerHandlers,
    getHandler: manager.getHandler,
    removeHandler: manager.removeHandler,
    
    // 事件绑定
    bind: manager.bindEvent,
    trigger: manager.triggerEvent,
    getComponentBindings: manager.getComponentBindings,
    removeComponentBindings: manager.removeComponentBindings,
    
    // 工具方法
    getAllHandlers: manager.getAllHandlers,
    getAllBindings: manager.getAllBindings,
    clear: manager.clear,
    validate: manager.validateBindings,
    toJSON: manager.toJSON,
    fromJSON: manager.fromJSON,
    
    // Formily 集成
    createFormilyHandler: manager.createFormilyHandler,
    createFormilyEventProps: manager.createFormilyEventProps,
    createBindingsFromHtml: manager.createBindingsFromHtml
  }
}

// 默认导出
export default {
  instance: () => eventManager,
  ...events
}