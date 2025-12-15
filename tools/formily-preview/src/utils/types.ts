/**
 * 工具类型定义
 */

// 错误处理类型
export interface ErrorInfo {
  id: string
  timestamp: number
  type: 'error' | 'warning' | 'info'
  message: string
  stack?: string
  context?: Record<string, any>
  component?: string
  file?: string
  line?: number
  column?: number
}

export interface ErrorHandlerOptions {
  maxErrors: number
  logToConsole: boolean
  showUserNotifications: boolean
  autoReport: boolean
  developmentMode: boolean
}

// 性能优化类型
export interface PerformanceMetric {
  name: string
  value: number
  unit: string
  timestamp: number
  context?: Record<string, any>
}

export interface PerformanceReport {
  timestamp: number
  metrics: PerformanceMetric[]
  recommendations: string[]
  score: number
}

export interface OptimizationConfig {
  enableMonitoring: boolean
  enableThrottling: boolean
  enableDebouncing: boolean
  enableLazyLoading: boolean
  enableCaching: boolean
  monitoringInterval: number
  maxMetrics: number
}

// 缓存类型
export interface CacheEntry<T> {
  value: T
  timestamp: number
}

export interface Cache<T> {
  get: (key: string) => T | null
  set: (key: string, value: T) => void
  delete: (key: string) => boolean
  clear: () => void
  size: () => number
  keys: () => string[]
}

// 监控类型
export interface MonitoringStats {
  [key: string]: {
    count: number
    avg: number
    min: number
    max: number
    lastValue: number
  }
}

// 工具函数类型
export type ThrottledFunction<T extends (...args: any[]) => any> = (...args: Parameters<T>) => void
export type DebouncedFunction<T extends (...args: any[]) => any> = (...args: Parameters<T>) => void
export type LazyLoader<T> = () => Promise<T>