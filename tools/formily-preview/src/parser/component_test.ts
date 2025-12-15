/**
 * 组件映射测试
 */

import { HtmlParser } from './HtmlParser'

async function runComponentTest() {
  const testHtml = `
<!DOCTYPE html>
<ui version="1.0">
<window title="组件测试" size="500,400">
  <grid padded="true">
    <label row="0" col="0">标签</label>
    <button row="0" col="1">按钮</button>
    <input row="1" col="0" type="text" placeholder="输入框"/>
    <checkbox row="1" col="1">复选框</checkbox>
  </grid>
</window>
</ui>
`

  console.log('🔧 组件映射测试开始...')
  console.log('📄 测试 HTML 内容:')
  console.log(testHtml)
  console.log('\n---\n')

  try {
    const parser = new HtmlParser({
      preserveComments: true,
      validateSchema: true,
      strictMode: false
    })

    console.log('🔄 开始解析 HTML...')
    const startTime = Date.now()
    
    const result = await parser.parse(testHtml)
    
    const endTime = Date.now()
    console.log(`✅ 解析完成，耗时: ${endTime - startTime}ms`)
    
    // 检查组件映射
    console.log('\n🔍 组件映射检查:')
    
    const windowSchema = result.schema
    console.log(`1. Window → ${windowSchema['x-component']} (应为: LibuiForm)`)
    
    const gridSchema = windowSchema.properties?.child_0
    console.log(`2. Grid → ${gridSchema?.['x-component']} (应为: LibuiGrid)`)
    
    if (gridSchema?.properties) {
      const children = Object.values(gridSchema.properties)
      children.forEach((child: any, index) => {
        console.log(`   子组件 ${index + 1}: ${child['x-component']}`)
      })
    }
    
    // 检查 GridItem 装饰器
    const firstChild = gridSchema?.properties?.child_0
    if (firstChild?.['x-component-props']?.['x-decorator']) {
      console.log(`3. GridItem 装饰器: ${firstChild['x-component-props']['x-decorator']} (应为: LibuiGridItem)`)
    }
    
    console.log('\n📊 解析结果摘要:')
    console.log(JSON.stringify({
      window: windowSchema['x-component'],
      grid: gridSchema?.['x-component'],
      childrenCount: Object.keys(gridSchema?.properties || {}).length,
      hasGridItem: !!firstChild?.['x-component-props']?.['x-decorator']
    }, null, 2))
    
    console.log('\n✅ 组件映射测试通过！')
    
  } catch (error) {
    console.error('❌ 组件映射测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runComponentTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})
