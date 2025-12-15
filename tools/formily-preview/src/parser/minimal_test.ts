/**
 * 最小化 HTML 解析器测试
 */

import { HtmlParser } from './HtmlParser'

async function runMinimalTest() {
  // 测试没有注释的简单 HTML
  const minimalHtml = `
<!DOCTYPE html>
<ui version="1.0">
<window title="最小测试" size="300,200">
  <grid padded="true">
    <label row="0" col="0">标签1</label>
    <label row="0" col="1">标签2</label>
    <label row="1" col="0">标签3</label>
    <label row="1" col="1">标签4</label>
  </grid>
</window>
</ui>
`

  console.log('🔧 最小化 HTML 解析器测试开始...')
  console.log('📄 测试 HTML 内容:')
  console.log(minimalHtml)
  console.log('\n---\n')

  try {
    const parser = new HtmlParser({
      preserveComments: false,
      validateSchema: false,
      strictMode: false
    })

    console.log('🔄 开始解析 HTML...')
    const startTime = Date.now()
    
    const result = await parser.parse(minimalHtml)
    
    const endTime = Date.now()
    console.log(`✅ 解析完成，耗时: ${endTime - startTime}ms`)
    
    // 检查 Grid 的子元素数量
    const grid = result.schema.properties?.child_0
    if (grid && grid.properties) {
      console.log(`\n🔍 Grid 包含 ${Object.keys(grid.properties).length} 个子元素:`)
      Object.entries(grid.properties).forEach(([key, child]: [string, any]) => {
        console.log(`  ${key}: ${child['x-component'] || 'unknown'} - ${child['x-component-props']?.children || 'no text'}`)
      })
    }
    
    console.log('\n✅ 最小化测试完成！')
    
  } catch (error) {
    console.error('❌ 最小化测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runMinimalTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})
