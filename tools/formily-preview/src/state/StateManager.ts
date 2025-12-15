/**
 * 状态管理器
 * 
 * 兼容现有 libuiBuilder StateManager API 的 Vue 3 reactive 实现
 */

import { reactive, computed, watch } from 'vue'
import type { StateBinding } from '@/types'

export class StateManager {
  private static instance: StateManager
  private state = reactive<Record<string, any>>({})
  private watchers = new Map<string, Array<(value: any, oldValue: any, key: string) => void>>()
  private componentRefs = new Map<string, any>()

  /**
   * 获取单例实例
   */
  static getInstance(): StateManager {
    if (!StateManager.instance) {
      StateManager.instance = new StateManager()
    }
    return StateManager.instance
  }

  /**
   * 设置状态值
   */
  set(key: string, value: any): void {
    const oldValue = this.state[key]
    
    // 如果值相同，不触发更新
    if (oldValue === value) {
      return
    }
    
    this.state[key] = value
    
    // 触发监听器
    const callbacks = this.watchers.get(key)
    if (callbacks) {
      callbacks.forEach(callback => {
        try {
          callback(value, oldValue, key)
        } catch (error) {
          console.error(`状态监听器执行失败: ${key}`, error)
        }
      })
    }
    
    // 触发全局监听器
    const globalCallbacks = this.watchers.get('*')
    if (globalCallbacks) {
      globalCallbacks.forEach(callback => {
        try {
          callback(value, oldValue, key)
        } catch (error) {
          console.error(`全局状态监听器执行失败: ${key}`, error)
        }
      })
    }
  }

  /**
   * 获取状态值
   */
  get(key: string, defaultValue?: any): any {
    const value = this.state[key]
    return value !== undefined ? value : defaultValue
  }

  /**
   * 监听状态变化
   */
  watch(key: string, callback: (value: any, oldValue: any, key: string) => void): void {
    if (!this.watchers.has(key)) {
      this.watchers.set(key, [])
    }
    this.watchers.get(key)!.push(callback)
  }

  /**
   * 监听所有状态变化
   */
  watchAll(callback: (value: any, oldValue: any, key: string) => void): void {
    this.watch('*', callback)
  }

  /**
   * 取消监听状态变化
   */
  unwatch(key: string, callback?: (value: any, oldValue: any, key: string) => void): void {
    const callbacks = this.watchers.get(key)
    if (!callbacks) return
    
    if (callback) {
      const index = callbacks.indexOf(callback)
      if (index > -1) {
        callbacks.splice(index, 1)
      }
    } else {
      this.watchers.delete(key)
    }
  }

  /**
   * 批量更新状态
   */
  update(updates: Record<string, any>): void {
    Object.entries(updates).forEach(([key, value]) => {
      this.set(key, value)
    })
  }

  /**
   * 注册组件引用
   */
  registerComponent(id: string, component: any): void {
    this.componentRefs.set(id, component)
  }

  /**
   * 获取组件引用
   */
  getComponent(id: string): any {
    return this.componentRefs.get(id)
  }

  /**
   * 移除组件引用
   */
  removeComponent(id: string): void {
    this.componentRefs.delete(id)
  }

  /**
   * 获取所有状态（调试用）
   */
  dump(): Record<string, any> {
    return { ...this.state }
  }

  /**
   * 清空所有状态
   */
  clear(): void {
    const keys = Object.keys(this.state)
    keys.forEach(key => {
      delete this.state[key]
    })
    this.watchers.clear()
    this.componentRefs.clear()
  }

  /**
   * 检查状态是否存在
   */
  has(key: string): boolean {
    return key in this.state
  }

  /**
   * 删除状态
   */
  delete(key: string): boolean {
    const existed = this.has(key)
    if (existed) {
      const oldValue = this.state[key]
      delete this.state[key]
      
      // 触发监听器
      const callbacks = this.watchers.get(key)
      if (callbacks) {
        callbacks.forEach(callback => {
          try {
            callback(undefined, oldValue, key)
          } catch (error) {
            console.error(`状态删除监听器执行失败: ${key}`, error)
          }
        })
      }
    }
    return existed
  }

  /**
   * 获取所有状态键
   */
  keys(): string[] {
    return Object.keys(this.state)
  }

  /**
   * 获取状态数量
   */
  size(): number {
    return this.keys().length
  }

  /**
   * 创建 Vue 计算属性
   */
  createComputed(key: string) {
    return computed(() => this.get(key))
  }

  /**
   * 创建 Vue watch
   */
  createWatch(key: string, callback: (value: any, oldValue: any) => void) {
    return watch(
      () => this.get(key),
      (newValue, oldValue) => {
        callback(newValue, oldValue)
      },
      { deep: true }
    )
  }

  /**
   * 为 Formily 创建响应式状态
   */
  createFormilyReactions(): Record<string, any> {
    const reactions: Record<string, any> = {}
    
    Object.keys(this.state).forEach(key => {
      reactions[key] = computed(() => this.get(key))
    })
    
    return reactions
  }

  /**
   * 初始化状态绑定
   */
  initializeBindings(bindings: StateBinding[]): void {
    bindings.forEach(binding => {
      if (!this.has(binding.key)) {
        this.set(binding.key, binding.defaultValue)
      }
      
      // 注册监听器
      if (binding.watchers) {
        binding.watchers.forEach(watcher => {
          this.watch(binding.key, watcher)
        })
      }
    })
  }

  /**
   * 导出状态为 JSON
   */
  toJSON(): string {
    return JSON.stringify(this.state, null, 2)
  }

  /**
   * 从 JSON 导入状态
   */
  fromJSON(json: string): void {
    try {
      const data = JSON.parse(json)
      this.update(data)
    } catch (error) {
      console.error('状态导入失败:', error)
      throw new Error('无效的 JSON 格式')
    }
  }
}

// 导出单例实例
export const stateManager = StateManager.getInstance()

// Vue 组合式 API 封装
export function useStateManager() {
  const instance = StateManager.getInstance()
  
  return {
    // 状态操作
    set: instance.set.bind(instance),
    get: instance.get.bind(instance),
    has: instance.has.bind(instance),
    delete: instance.delete.bind(instance),
    update: instance.update.bind(instance),
    clear: instance.clear.bind(instance),
    
    // 监听器
    watch: instance.watch.bind(instance),
    watchAll: instance.watchAll.bind(instance),
    unwatch: instance.unwatch.bind(instance),
    
    // 组件引用
    registerComponent: instance.registerComponent.bind(instance),
    getComponent: instance.getComponent.bind(instance),
    removeComponent: instance.removeComponent.bind(instance),
    
    // 工具方法
    dump: instance.dump.bind(instance),
    keys: instance.keys.bind(instance),
    size: instance.size.bind(instance),
    toJSON: instance.toJSON.bind(instance),
    fromJSON: instance.fromJSON.bind(instance),
    
    // Vue 集成
    createComputed: instance.createComputed.bind(instance),
    createWatch: instance.createWatch.bind(instance),
    createFormilyReactions: instance.createFormilyReactions.bind(instance),
    initializeBindings: instance.initializeBindings.bind(instance),
    
    // 响应式状态（用于模板）
    state: instance['state']
  }
}