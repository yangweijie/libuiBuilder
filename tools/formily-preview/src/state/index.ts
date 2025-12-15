/**
 * 状态管理辅助函数
 * 
 * 提供与 libuiBuilder 兼容的 API
 */

import { stateManager, useStateManager } from './StateManager'

// 导出状态管理器
export { stateManager, useStateManager }

// 兼容 libuiBuilder API
export const state = {
  // 获取状态管理器实例
  instance: () => stateManager,
  
  // 设置状态值
  set: (key: string, value: any) => stateManager.set(key, value),
  
  // 获取状态值
  get: (key: string, defaultValue?: any) => stateManager.get(key, defaultValue),
  
  // 监听状态变化
  watch: (key: string, callback: (value: any, oldValue: any, key: string) => void) => 
    stateManager.watch(key, callback),
  
  // 批量更新状态
  update: (updates: Record<string, any>) => stateManager.update(updates),
  
  // 检查状态是否存在
  has: (key: string) => stateManager.has(key),
  
  // 删除状态
  delete: (key: string) => stateManager.delete(key),
  
  // 清空所有状态
  clear: () => stateManager.clear(),
  
  // 获取所有状态键
  keys: () => stateManager.keys(),
  
  // 获取状态数量
  size: () => stateManager.size(),
  
  // 导出状态为 JSON
  toJSON: () => stateManager.toJSON(),
  
  // 从 JSON 导入状态
  fromJSON: (json: string) => stateManager.fromJSON(json),
  
  // 调试：获取所有状态
  dump: () => stateManager.dump()
}

// Vue 组合式 API
export function useState() {
  const manager = useStateManager()
  
  return {
    // 状态操作
    set: manager.set,
    get: manager.get,
    has: manager.has,
    delete: manager.delete,
    update: manager.update,
    clear: manager.clear,
    
    // 监听器
    watch: manager.watch,
    watchAll: manager.watchAll,
    unwatch: manager.unwatch,
    
    // 响应式状态（用于模板）
    state: manager.state,
    
    // 工具方法
    ...manager
  }
}

// 默认导出
export default {
  instance: () => stateManager,
  ...state
}