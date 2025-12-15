/**
 * 工具函数索引
 */

// 错误处理
export { 
  errorHandler, 
  useErrorHandler
} from './ErrorHandler'

// 性能优化
export {
  performanceOptimizer,
  usePerformanceOptimizer
} from './PerformanceOptimizer'

// 便捷函数
import {
  handleError,
  handleWarning,
  handleInfo,
  safeExecute,
  safeExecuteAsync
} from './ErrorHandler'

import {
  measureExecution,
  measureExecutionAsync,
  throttle,
  debounce
} from './PerformanceOptimizer'

export {
  handleError,
  handleWarning,
  handleInfo,
  safeExecute,
  safeExecuteAsync,
  measureExecution,
  measureExecutionAsync,
  throttle,
  debounce
}

// 类型导出
export type {
  ErrorInfo,
  ErrorHandlerOptions,
  PerformanceMetric,
  PerformanceReport,
  OptimizationConfig
} from './types'

// 默认导出
export default {
  error: handleError,
  warning: handleWarning,
  info: handleInfo,
  safeExecute,
  safeExecuteAsync,
  measureExecution,
  measureExecutionAsync,
  throttle,
  debounce
}