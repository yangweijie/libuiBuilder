/**
 * 错误处理器
 * 
 * 统一的错误处理和日志记录系统
 */

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

export class ErrorHandler {
  private static instance: ErrorHandler
  private errors: ErrorInfo[] = []
  private options: ErrorHandlerOptions = {
    maxErrors: 100,
    logToConsole: true,
    showUserNotifications: true,
    autoReport: false,
    developmentMode: process.env.NODE_ENV === 'development'
  }

  /**
   * 获取单例实例
   */
  static getInstance(): ErrorHandler {
    if (!ErrorHandler.instance) {
      ErrorHandler.instance = new ErrorHandler()
    }
    return ErrorHandler.instance
  }

  /**
   * 配置错误处理器
   */
  configure(options: Partial<ErrorHandlerOptions>): void {
    this.options = { ...this.options, ...options }
  }

  /**
   * 记录错误
   */
  error(
    message: string,
    error?: Error | unknown,
    context?: Record<string, any>,
    component?: string
  ): string {
    const errorId = this.generateErrorId()
    const errorInfo: ErrorInfo = {
      id: errorId,
      timestamp: Date.now(),
      type: 'error',
      message,
      context,
      component
    }

    // 提取错误堆栈
    if (error instanceof Error) {
      errorInfo.stack = error.stack
      errorInfo.message = `${message}: ${error.message}`
    } else if (error) {
      errorInfo.message = `${message}: ${String(error)}`
    }

    // 添加到错误列表
    this.addError(errorInfo)

    // 控制台日志
    if (this.options.logToConsole) {
      console.error(`❌ [${errorId}] ${errorInfo.message}`, {
        context,
        component,
        stack: errorInfo.stack
      })
    }

    // 用户通知
    if (this.options.showUserNotifications && typeof window !== 'undefined') {
      this.showUserNotification(errorInfo)
    }

    // 自动报告
    if (this.options.autoReport) {
      this.reportError(errorInfo)
    }

    return errorId
  }

  /**
   * 记录警告
   */
  warning(
    message: string,
    context?: Record<string, any>,
    component?: string
  ): string {
    const warningId = this.generateErrorId()
    const warningInfo: ErrorInfo = {
      id: warningId,
      timestamp: Date.now(),
      type: 'warning',
      message,
      context,
      component
    }

    // 添加到错误列表
    this.addError(warningInfo)

    // 控制台日志
    if (this.options.logToConsole) {
      console.warn(`⚠️ [${warningId}] ${message}`, { context, component })
    }

    return warningId
  }

  /**
   * 记录信息
   */
  info(
    message: string,
    context?: Record<string, any>,
    component?: string
  ): string {
    const infoId = this.generateErrorId()
    const infoInfo: ErrorInfo = {
      id: infoId,
      timestamp: Date.now(),
      type: 'info',
      message,
      context,
      component
    }

    // 添加到错误列表
    this.addError(infoInfo)

    // 控制台日志
    if (this.options.logToConsole && this.options.developmentMode) {
      console.info(`ℹ️ [${infoId}] ${message}`, { context, component })
    }

    return infoId
  }

  /**
   * 安全执行函数
   */
  safeExecute<T>(
    fn: () => T,
    errorMessage: string,
    context?: Record<string, any>,
    component?: string
  ): T | null {
    try {
      return fn()
    } catch (error) {
      this.error(errorMessage, error, context, component)
      return null
    }
  }

  /**
   * 安全执行异步函数
   */
  async safeExecuteAsync<T>(
    fn: () => Promise<T>,
    errorMessage: string,
    context?: Record<string, any>,
    component?: string
  ): Promise<T | null> {
    try {
      return await fn()
    } catch (error) {
      this.error(errorMessage, error, context, component)
      return null
    }
  }

  /**
   * 获取所有错误
   */
  getErrors(): ErrorInfo[] {
    return [...this.errors]
  }

  /**
   * 获取错误统计
   */
  getErrorStats(): {
    total: number
    errors: number
    warnings: number
    infos: number
    lastErrorTime: number | null
  } {
    const errors = this.errors.filter(e => e.type === 'error')
    const warnings = this.errors.filter(e => e.type === 'warning')
    const infos = this.errors.filter(e => e.type === 'info')
    
    const lastError = this.errors[this.errors.length - 1]
    
    return {
      total: this.errors.length,
      errors: errors.length,
      warnings: warnings.length,
      infos: infos.length,
      lastErrorTime: lastError?.timestamp || null
    }
  }

  /**
   * 清除错误
   */
  clearErrors(): void {
    this.errors = []
    console.log('🧹 已清除所有错误记录')
  }

  /**
   * 导出错误日志
   */
  exportErrors(): string {
    return JSON.stringify({
      timestamp: Date.now(),
      stats: this.getErrorStats(),
      errors: this.errors
    }, null, 2)
  }

  /**
   * 导入错误日志
   */
  importErrors(json: string): void {
    try {
      const data = JSON.parse(json)
      if (data.errors && Array.isArray(data.errors)) {
        this.errors = data.errors
        console.log(`📥 导入 ${data.errors.length} 个错误记录`)
      }
    } catch (error) {
      console.error('错误日志导入失败:', error)
    }
  }

  /**
   * 生成性能监控
   */
  createPerformanceMonitor(name: string) {
    const startTime = performance.now()
    
    return {
      end: () => {
        const endTime = performance.now()
        const duration = endTime - startTime
        
        if (duration > 100) { // 超过100ms记录警告
          this.warning(`性能警告: ${name} 耗时 ${duration.toFixed(2)}ms`, { duration })
        }
        
        if (this.options.developmentMode) {
          console.debug(`⏱️ ${name}: ${duration.toFixed(2)}ms`)
        }
        
        return duration
      },
      
      mark: (checkpoint: string) => {
        const currentTime = performance.now()
        const elapsed = currentTime - startTime
        console.debug(`📍 ${name} - ${checkpoint}: ${elapsed.toFixed(2)}ms`)
      }
    }
  }

  /**
   * 添加错误到列表
   */
  private addError(errorInfo: ErrorInfo): void {
    this.errors.push(errorInfo)
    
    // 限制错误数量
    if (this.errors.length > this.options.maxErrors) {
      this.errors = this.errors.slice(-this.options.maxErrors)
    }
  }

  /**
   * 生成错误ID
   */
  private generateErrorId(): string {
    return `err_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
  }

  /**
   * 显示用户通知
   */
  private showUserNotification(errorInfo: ErrorInfo): void {
    if (typeof window === 'undefined') return
    
    // 使用 Ant Design 的通知组件
    if ((window as any).antd && (window as any).antd.notification) {
      const notification = (window as any).antd.notification
      
      const config = {
        message: '发生错误',
        description: errorInfo.message,
        duration: errorInfo.type === 'error' ? 0 : 4.5, // 错误不自动关闭
        type: errorInfo.type === 'error' ? 'error' : 'warning'
      }
      
      notification[config.type](config)
    } else {
      // 回退到原生 alert
      console.warn('显示用户通知:', errorInfo.message)
    }
  }

  /**
   * 报告错误到服务器
   */
  private reportError(errorInfo: ErrorInfo): void {
    // 这里可以实现错误上报到服务器的逻辑
    // 例如：发送到 Sentry、LogRocket 等
    console.debug('📡 错误上报:', errorInfo)
  }

  /**
   * 全局错误捕获
   */
  setupGlobalErrorHandling(): void {
    if (typeof window === 'undefined') return
    
    // 捕获未处理的 Promise 错误
    window.addEventListener('unhandledrejection', (event) => {
      this.error('未处理的 Promise 错误', event.reason)
      event.preventDefault()
    })
    
    // 捕获全局错误
    window.addEventListener('error', (event) => {
      this.error('全局 JavaScript 错误', event.error, {
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno
      })
      event.preventDefault()
    })
    
    // Vue 错误处理
    if ((window as any).Vue) {
      const Vue = (window as any).Vue
      Vue.config.errorHandler = (err: Error, vm: any, info: string) => {
        this.error(`Vue 错误: ${info}`, err, { component: vm?.$options?.name })
      }
    }
    
    console.log('🛡️ 全局错误处理已启用')
  }
}

// 导出单例实例
export const errorHandler = ErrorHandler.getInstance()

// Vue 组合式 API 封装
export function useErrorHandler() {
  const instance = ErrorHandler.getInstance()
  
  return {
    // 错误记录
    error: instance.error.bind(instance),
    warning: instance.warning.bind(instance),
    info: instance.info.bind(instance),
    
    // 安全执行
    safeExecute: instance.safeExecute.bind(instance),
    safeExecuteAsync: instance.safeExecuteAsync.bind(instance),
    
    // 错误管理
    getErrors: instance.getErrors.bind(instance),
    getErrorStats: instance.getErrorStats.bind(instance),
    clearErrors: instance.clearErrors.bind(instance),
    exportErrors: instance.exportErrors.bind(instance),
    importErrors: instance.importErrors.bind(instance),
    
    // 性能监控
    createPerformanceMonitor: instance.createPerformanceMonitor.bind(instance),
    
    // 配置
    configure: instance.configure.bind(instance),
    setupGlobalErrorHandling: instance.setupGlobalErrorHandling.bind(instance)
  }
}

// 便捷函数
export function handleError(
  message: string,
  error?: Error | unknown,
  context?: Record<string, any>,
  component?: string
): string {
  return errorHandler.error(message, error, context, component)
}

export function handleWarning(
  message: string,
  context?: Record<string, any>,
  component?: string
): string {
  return errorHandler.warning(message, context, component)
}

export function handleInfo(
  message: string,
  context?: Record<string, any>,
  component?: string
): string {
  return errorHandler.info(message, context, component)
}

export function safeExecute<T>(
  fn: () => T,
  errorMessage: string,
  context?: Record<string, any>,
  component?: string
): T | null {
  return errorHandler.safeExecute(fn, errorMessage, context, component)
}

export async function safeExecuteAsync<T>(
  fn: () => Promise<T>,
  errorMessage: string,
  context?: Record<string, any>,
  component?: string
): Promise<T | null> {
  return errorHandler.safeExecuteAsync(fn, errorMessage, context, component)
}