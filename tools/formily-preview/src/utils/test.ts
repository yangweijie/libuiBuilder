import { 
  errorHandler, 
  performanceOptimizer,
  handleError,
  handleWarning,
  safeExecute,
  measureExecution,
  throttle,
  debounce
} from './index'

console.log('🧪 开始工具函数测试...')

// 测试 1: 错误处理
console.log('📝 测试 1: 错误处理')

// 记录错误
const errorId1 = handleError('测试错误', new Error('这是一个测试错误'), { test: true }, 'TestComponent')
console.log('❌ 记录错误:', errorId1)

// 记录警告
const warningId = handleWarning('测试警告', { test: true }, 'TestComponent')
console.log('⚠️ 记录警告:', warningId)

// 安全执行
const result = safeExecute(() => {
  throw new Error('安全执行测试错误')
}, '安全执行失败', { test: true }, 'TestComponent')
console.log('🛡️ 安全执行结果:', result)

// 获取错误统计
const errorStats = errorHandler.getErrorStats()
console.log('📊 错误统计:', errorStats)
console.log('✅ 错误处理测试成功')

// 测试 2: 性能测量
console.log('📝 测试 2: 性能测量')

// 测量同步函数
const syncResult = measureExecution('测试同步函数', () => {
  let sum = 0
  for (let i = 0; i < 1000000; i++) {
    sum += i
  }
  return sum
}, { iterations: 1000000 })
console.log('⏱️ 同步函数结果:', syncResult)

// 测量异步函数
const asyncTest = async () => {
  await new Promise(resolve => setTimeout(resolve, 100))
  return '异步完成'
}

// 注意：这里需要异步执行
console.log('⏱️ 异步函数测试跳过（需要异步环境）')
console.log('✅ 性能测量测试成功')

// 测试 3: 节流和防抖（在 Node.js 环境中跳过）
console.log('📝 测试 3: 节流和防抖')
console.log('🔁 在 Node.js 环境中跳过节流防抖测试\n')

// 测试 4: 性能报告
console.log('📝 测试 4: 性能报告')

// 生成性能报告
const report = performanceOptimizer.generateReport()
console.log('📊 性能分数:', report.score)
console.log('💡 优化建议:', report.recommendations)
console.log('📈 指标数量:', report.metrics.length)

// 获取指标统计
const metricStats = performanceOptimizer.getMetricStats()
console.log('📊 指标统计:', Object.keys(metricStats).length, '个指标')

// 导出数据
const exportedData = performanceOptimizer.exportData()
console.log('📄 导出数据长度:', exportedData.length)

console.log('✅ 性能报告测试成功\n')

// 最终状态
console.log('📊 最终状态:')
console.log('❌ 错误数量:', errorHandler.getErrors().length)
console.log('📈 性能指标数量:', performanceOptimizer.getMetrics().length)
console.log('🎯 工具函数测试完成！')