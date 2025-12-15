/**
 * 性能优化器
 * 
 * 提供性能监控、优化建议和性能分析工具
 */

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

export class PerformanceOptimizer {
  private static instance: PerformanceOptimizer
  private metrics: PerformanceMetric[] = []
  private config: OptimizationConfig = {
    enableMonitoring: true,
    enableThrottling: true,
    enableDebouncing: true,
    enableLazyLoading: true,
    enableCaching: true,
    monitoringInterval: 5000,
    maxMetrics: 1000
  }
  private monitoringIntervalId: number | null = null

  /**
   * 获取单例实例
   */
  static getInstance(): PerformanceOptimizer {
    if (!PerformanceOptimizer.instance) {
      PerformanceOptimizer.instance = new PerformanceOptimizer()
    }
    return PerformanceOptimizer.instance
  }

  /**
   * 配置性能优化器
   */
  configure(config: Partial<OptimizationConfig>): void {
    this.config = { ...this.config, ...config }
    
    // 重启监控
    if (this.config.enableMonitoring) {
      this.startMonitoring()
    } else {
      this.stopMonitoring()
    }
  }

  /**
   * 开始性能监控
   */
  startMonitoring(): void {
    if (this.monitoringIntervalId || !this.config.enableMonitoring) return
    
    // 只在浏览器环境中启动监控
    if (typeof window !== 'undefined') {
      this.monitoringIntervalId = window.setInterval(() => {
        this.collectPerformanceMetrics()
      }, this.config.monitoringInterval)
      
      console.log('📊 性能监控已启动')
    }
  }

  /**
   * 停止性能监控
   */
  stopMonitoring(): void {
    if (this.monitoringIntervalId) {
      clearInterval(this.monitoringIntervalId)
      this.monitoringIntervalId = null
      console.log('📊 性能监控已停止')
    }
  }

  /**
   * 收集性能指标
   */
  private collectPerformanceMetrics(): void {
    // 只在浏览器环境中收集性能指标
    if (typeof window === 'undefined' || !window.performance) return

    const metrics: PerformanceMetric[] = []

    // 内存使用情况
    if ((performance as any).memory) {
      const memory = (performance as any).memory
      metrics.push(
        this.createMetric('memory.used', memory.usedJSHeapSize, 'bytes'),
        this.createMetric('memory.total', memory.totalJSHeapSize, 'bytes'),
        this.createMetric('memory.limit', memory.jsHeapSizeLimit, 'bytes')
      )
    }

    // 页面性能
    const timing = performance.timing
    if (timing) {
      const loadTime = timing.loadEventEnd - timing.navigationStart
      const domReadyTime = timing.domContentLoadedEventEnd - timing.navigationStart
      const firstPaint = (performance.getEntriesByType('paint') as any[]).find(
        entry => entry.name === 'first-paint'
      )
      
      metrics.push(
        this.createMetric('page.load', loadTime, 'ms'),
        this.createMetric('page.domReady', domReadyTime, 'ms')
      )
      
      if (firstPaint) {
        metrics.push(this.createMetric('page.firstPaint', firstPaint.startTime, 'ms'))
      }
    }

    // 资源加载性能
    const resources = performance.getEntriesByType('resource')
    const resourceMetrics = resources.map(resource => 
      this.createMetric(`resource.${resource.name}`, resource.duration, 'ms', {
        type: resource.initiatorType,
        size: (resource as any).transferSize || 0
      })
    )
    
    metrics.push(...resourceMetrics.slice(0, 10)) // 限制数量

    // 添加到指标列表
    this.addMetrics(metrics)
  }

  /**
   * 创建性能指标
   */
  createMetric(
    name: string,
    value: number,
    unit: string,
    context?: Record<string, any>
  ): PerformanceMetric {
    return {
      name,
      value,
      unit,
      timestamp: Date.now(),
      context
    }
  }

  /**
   * 记录自定义指标
   */
  recordMetric(
    name: string,
    value: number,
    unit: string = 'ms',
    context?: Record<string, any>
  ): void {
    const metric = this.createMetric(name, value, unit, context)
    this.addMetric(metric)
  }

  /**
   * 测量函数执行时间
   */
  measureExecution<T>(
    name: string,
    fn: () => T,
    context?: Record<string, any>
  ): T {
    const startTime = performance.now()
    try {
      const result = fn()
      const endTime = performance.now()
      const duration = endTime - startTime
      
      this.recordMetric(`execution.${name}`, duration, 'ms', context)
      
      // 如果执行时间过长，记录警告
      if (duration > 100) {
        console.warn(`⚠️ 函数执行时间过长: ${name} (${duration.toFixed(2)}ms)`)
      }
      
      return result
    } catch (error) {
      const endTime = performance.now()
      const duration = endTime - startTime
      this.recordMetric(`execution.${name}.error`, duration, 'ms', {
        ...context,
        error: String(error)
      })
      throw error
    }
  }

  /**
   * 测量异步函数执行时间
   */
  async measureExecutionAsync<T>(
    name: string,
    fn: () => Promise<T>,
    context?: Record<string, any>
  ): Promise<T> {
    const startTime = performance.now()
    try {
      const result = await fn()
      const endTime = performance.now()
      const duration = endTime - startTime
      
      this.recordMetric(`execution.async.${name}`, duration, 'ms', context)
      
      // 如果执行时间过长，记录警告
      if (duration > 500) {
        console.warn(`⚠️ 异步函数执行时间过长: ${name} (${duration.toFixed(2)}ms)`)
      }
      
      return result
    } catch (error) {
      const endTime = performance.now()
      const duration = endTime - startTime
      this.recordMetric(`execution.async.${name}.error`, duration, 'ms', {
        ...context,
        error: String(error)
      })
      throw error
    }
  }

  /**
   * 节流函数
   */
  throttle<T extends (...args: any[]) => any>(
    fn: T,
    delay: number = 300
  ): T {
    if (!this.config.enableThrottling) return fn
    
    let lastCallTime = 0
    let timeoutId: number | null = null
    
    return ((...args: Parameters<T>) => {
      const now = Date.now()
      const timeSinceLastCall = now - lastCallTime
      
      if (timeSinceLastCall >= delay) {
        lastCallTime = now
        return fn(...args)
      }
      
      if (timeoutId) {
        clearTimeout(timeoutId)
      }
      
      // 只在浏览器环境中使用 setTimeout
      if (typeof window !== 'undefined') {
        timeoutId = window.setTimeout(() => {
          lastCallTime = Date.now()
          fn(...args)
        }, delay - timeSinceLastCall)
      }
    }) as T
  }

  /**
   * 防抖函数
   */
  debounce<T extends (...args: any[]) => any>(
    fn: T,
    delay: number = 300
  ): T {
    if (!this.config.enableDebouncing) return fn
    
    let timeoutId: number | null = null
    
    return ((...args: Parameters<T>) => {
      if (timeoutId) {
        clearTimeout(timeoutId)
      }
      
      // 只在浏览器环境中使用 setTimeout
      if (typeof window !== 'undefined') {
        timeoutId = window.setTimeout(() => {
          fn(...args)
        }, delay)
      }
    }) as T
  }

  /**
   * 懒加载函数
   */
  lazyLoad<T>(
    loader: () => Promise<T>,
    key: string
  ): () => Promise<T> {
    if (!this.config.enableLazyLoading) {
      return loader
    }
    
    let cache: T | null = null
    let loadingPromise: Promise<T> | null = null
    
    return async (): Promise<T> => {
      if (cache !== null) {
        this.recordMetric(`cache.hit.${key}`, 1, 'count')
        return cache
      }
      
      if (loadingPromise) {
        return loadingPromise
      }
      
      this.recordMetric(`cache.miss.${key}`, 1, 'count')
      loadingPromise = loader().then(result => {
        cache = result
        loadingPromise = null
        return result
      })
      
      return loadingPromise
    }
  }

  /**
   * 创建缓存
   */
  createCache<T>(
    maxSize: number = 100,
    ttl: number = 5 * 60 * 1000 // 5分钟
  ) {
    const cache = new Map<string, { value: T; timestamp: number }>()
    
    return {
      get: (key: string): T | null => {
        const entry = cache.get(key)
        if (!entry) {
          this.recordMetric(`cache.get.miss`, 1, 'count', { key })
          return null
        }
        
        const age = Date.now() - entry.timestamp
        if (age > ttl) {
          cache.delete(key)
          this.recordMetric(`cache.get.expired`, 1, 'count', { key, age })
          return null
        }
        
        this.recordMetric(`cache.get.hit`, 1, 'count', { key, age })
        return entry.value
      },
      
      set: (key: string, value: T): void => {
        // 检查缓存大小
        if (cache.size >= maxSize) {
          const oldestKey = cache.keys().next().value
          if (oldestKey) {
            cache.delete(oldestKey)
            this.recordMetric(`cache.evicted`, 1, 'count', { key: oldestKey })
          }
        }
        
        cache.set(key, { value, timestamp: Date.now() })
        this.recordMetric(`cache.set`, 1, 'count', { key })
      },
      
      delete: (key: string): boolean => {
        const deleted = cache.delete(key)
        if (deleted) {
          this.recordMetric(`cache.delete`, 1, 'count', { key })
        }
        return deleted
      },
      
      clear: (): void => {
        const size = cache.size
        cache.clear()
        this.recordMetric(`cache.clear`, size, 'count')
      },
      
      size: (): number => cache.size,
      
      keys: (): string[] => Array.from(cache.keys())
    }
  }

  /**
   * 生成性能报告
   */
  generateReport(): PerformanceReport {
    const metrics = this.getMetrics()
    const recommendations = this.generateRecommendations(metrics)
    const score = this.calculatePerformanceScore(metrics)
    
    return {
      timestamp: Date.now(),
      metrics,
      recommendations,
      score
    }
  }

  /**
   * 获取所有指标
   */
  getMetrics(): PerformanceMetric[] {
    return [...this.metrics]
  }

  /**
   * 获取指标统计
   */
  getMetricStats(): Record<string, {
    count: number
    avg: number
    min: number
    max: number
    lastValue: number
  }> {
    const stats: Record<string, any> = {}
    
    for (const metric of this.metrics) {
      if (!stats[metric.name]) {
        stats[metric.name] = {
          count: 0,
          sum: 0,
          min: Infinity,
          max: -Infinity,
          values: []
        }
      }
      
      const stat = stats[metric.name]
      stat.count++
      stat.sum += metric.value
      stat.min = Math.min(stat.min, metric.value)
      stat.max = Math.max(stat.max, metric.value)
      stat.values.push(metric.value)
    }
    
    // 计算平均值
    const result: Record<string, any> = {}
    for (const [name, stat] of Object.entries(stats)) {
      result[name] = {
        count: stat.count,
        avg: stat.sum / stat.count,
        min: stat.min,
        max: stat.max,
        lastValue: stat.values[stat.values.length - 1]
      }
    }
    
    return result
  }

  /**
   * 清除指标
   */
  clearMetrics(): void {
    this.metrics = []
    console.log('🧹 已清除所有性能指标')
  }

  /**
   * 导出性能数据
   */
  exportData(): string {
    return JSON.stringify({
      timestamp: Date.now(),
      config: this.config,
      metrics: this.metrics,
      stats: this.getMetricStats(),
      report: this.generateReport()
    }, null, 2)
  }

  /**
   * 添加指标
   */
  private addMetric(metric: PerformanceMetric): void {
    this.metrics.push(metric)
    
    // 限制指标数量
    if (this.metrics.length > this.config.maxMetrics) {
      this.metrics = this.metrics.slice(-this.config.maxMetrics)
    }
  }

  /**
   * 添加多个指标
   */
  private addMetrics(metrics: PerformanceMetric[]): void {
    this.metrics.push(...metrics)
    
    // 限制指标数量
    if (this.metrics.length > this.config.maxMetrics) {
      this.metrics = this.metrics.slice(-this.config.maxMetrics)
    }
  }

  /**
   * 生成优化建议
   */
  private generateRecommendations(metrics: PerformanceMetric[]): string[] {
    const recommendations: string[] = []
    const stats = this.getMetricStats()

    // 检查内存使用
    const memoryStats = stats['memory.used']
    if (memoryStats && memoryStats.avg > 100 * 1024 * 1024) { // 超过100MB
      recommendations.push('内存使用较高，建议优化内存管理')
    }

    // 检查页面加载时间
    const loadStats = stats['page.load']
    if (loadStats && loadStats.avg > 3000) { // 超过3秒
      recommendations.push('页面加载时间过长，建议优化资源加载')
    }

    // 检查函数执行时间
    const executionStats = Object.entries(stats).filter(([key]) => 
      key.startsWith('execution.')
    )
    
    for (const [key, stat] of executionStats) {
      if (stat.avg > 100) { // 超过100ms
        recommendations.push(`函数 ${key} 执行时间过长，建议优化`)
      }
    }

    // 检查缓存命中率
    const hitStats = stats['cache.hit']
    const missStats = stats['cache.miss']
    if (hitStats && missStats) {
      const hitRate = hitStats.count / (hitStats.count + missStats.count)
      if (hitRate < 0.5) {
        recommendations.push('缓存命中率较低，建议优化缓存策略')
      }
    }

    return recommendations.slice(0, 5) // 最多5条建议
  }

  /**
   * 计算性能分数
   */
  private calculatePerformanceScore(metrics: PerformanceMetric[]): number {
    let score = 100
    
    // 页面加载时间扣分
    const loadMetrics = metrics.filter(m => m.name === 'page.load')
    if (loadMetrics.length > 0) {
      const avgLoadTime = loadMetrics.reduce((sum, m) => sum + m.value, 0) / loadMetrics.length
      if (avgLoadTime > 1000) score -= 10
      if (avgLoadTime > 3000) score -= 20
      if (avgLoadTime > 5000) score -= 30
    }
    
    // 内存使用扣分
    const memoryMetrics = metrics.filter(m => m.name === 'memory.used')
    if (memoryMetrics.length > 0) {
      const avgMemory = memoryMetrics.reduce((sum, m) => sum + m.value, 0) / memoryMetrics.length
      if (avgMemory > 50 * 1024 * 1024) score -= 5
      if (avgMemory > 100 * 1024 * 1024) score -= 10
      if (avgMemory > 200 * 1024 * 1024) score -= 20
    }
    
    return Math.max(0, Math.min(100, score))
  }
}

// 导出单例实例
export const performanceOptimizer = PerformanceOptimizer.getInstance()

// Vue 组合式 API 封装
export function usePerformanceOptimizer() {
  const instance = PerformanceOptimizer.getInstance()
  
  return {
    // 配置
    configure: instance.configure.bind(instance),
    startMonitoring: instance.startMonitoring.bind(instance),
    stopMonitoring: instance.stopMonitoring.bind(instance),
    
    // 指标记录
    recordMetric: instance.recordMetric.bind(instance),
    measureExecution: instance.measureExecution.bind(instance),
    measureExecutionAsync: instance.measureExecutionAsync.bind(instance),
    
    // 优化工具
    throttle: instance.throttle.bind(instance),
    debounce: instance.debounce.bind(instance),
    lazyLoad: instance.lazyLoad.bind(instance),
    createCache: instance.createCache.bind(instance),
    
    // 报告和分析
    generateReport: instance.generateReport.bind(instance),
    getMetrics: instance.getMetrics.bind(instance),
    getMetricStats: instance.getMetricStats.bind(instance),
    clearMetrics: instance.clearMetrics.bind(instance),
    exportData: instance.exportData.bind(instance)
  }
}

// 便捷函数
export function measureExecution<T>(
  name: string,
  fn: () => T,
  context?: Record<string, any>
): T {
  return performanceOptimizer.measureExecution(name, fn, context)
}

export async function measureExecutionAsync<T>(
  name: string,
  fn: () => Promise<T>,
  context?: Record<string, any>
): Promise<T> {
  return performanceOptimizer.measureExecutionAsync(name, fn, context)
}

export function throttle<T extends (...args: any[]) => any>(
  fn: T,
  delay: number = 300
): T {
  return performanceOptimizer.throttle(fn, delay)
}

export function debounce<T extends (...args: any[]) => any>(
  fn: T,
  delay: number = 300
): T {
  return performanceOptimizer.debounce(fn, delay)
}