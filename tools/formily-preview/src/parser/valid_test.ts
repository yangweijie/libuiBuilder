/**
 * 有效 HTML 解析器测试
 */

import { HtmlParser } from './HtmlParser'
import { readFileSync } from 'fs'
import { join, dirname } from 'path'
import { fileURLToPath } from 'url'

// 获取当前文件的目录路径
const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// 设置调试环境变量
process.env.DEBUG = 'true'

async function runValidTest() {
  // 读取有效的测试 HTML 文件
  const testHtmlPath = join(__dirname, 'valid_test.html')
  const htmlContent = readFileSync(testHtmlPath, 'utf-8')

  console.log('🔧 有效 HTML 解析器测试开始...')
  console.log('📄 测试文件内容:')
  console.log(htmlContent)
  console.log('\n---\n')

  try {
    // 创建解析器实例
    const parser = new HtmlParser({
      preserveComments: true,
      validateSchema: false,
      strictMode: false
    })

    console.log('🔄 开始解析 HTML...')
    const startTime = Date.now()
    
    const result = await parser.parse(htmlContent)
    
    const endTime = Date.now()
    console.log(`✅ 解析完成，耗时: ${endTime - startTime}ms`)
    
    // 输出详细的解析结果
    console.log('\n📊 完整的解析结果:')
    console.log(JSON.stringify(result.schema, null, 2))
    
    console.log('\n✅ 有效测试完成！')
    
  } catch (error) {
    console.error('❌ 有效测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runValidTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})