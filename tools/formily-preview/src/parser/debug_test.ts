/**
 * 调试 HTML 解析器测试
 */

import { HtmlParser } from './HtmlParser'
import { readFileSync } from 'fs'
import { join, dirname } from 'path'
import { fileURLToPath } from 'url'

// 获取当前文件的目录路径
const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

async function runDebugTest() {
  // 读取测试 HTML 文件
  const testHtmlPath = join(__dirname, 'test.html')
  const htmlContent = readFileSync(testHtmlPath, 'utf-8')

  console.log('🔧 调试 HTML 解析器测试开始...')
  console.log('📄 测试文件内容:')
  console.log(htmlContent)
  console.log('\n---\n')

  try {
    // 创建解析器实例，启用调试模式
    const parser = new HtmlParser({
      preserveComments: true,
      validateSchema: false, // 禁用验证以便查看原始输出
      strictMode: false
    })

    // 解析 HTML
    console.log('🔄 开始解析 HTML...')
    const startTime = Date.now()
    
    const result = await parser.parse(htmlContent)
    
    const endTime = Date.now()
    console.log(`✅ 解析完成，耗时: ${endTime - startTime}ms`)
    
    // 输出详细的解析结果
    console.log('\n📊 完整的解析结果:')
    console.log(JSON.stringify(result.schema, null, 2))
    
    // 检查 Grid 的子元素数量
    const grid = result.schema.properties?.child_0
    if (grid && grid.properties) {
      console.log(`\n🔍 Grid 包含 ${Object.keys(grid.properties).length} 个子元素:`)
      Object.entries(grid.properties).forEach(([key, child]: [string, any]) => {
        console.log(`  ${key}: ${child['x-component'] || 'unknown'}`)
      })
    }
    
    console.log('\n🔗 状态绑定:')
    console.log(result.stateBindings)
    
    console.log('\n🎯 事件处理器:')
    console.log(result.eventHandlers)
    
    console.log('\n✅ 调试测试完成！')
    
  } catch (error) {
    console.error('❌ 调试测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runDebugTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})