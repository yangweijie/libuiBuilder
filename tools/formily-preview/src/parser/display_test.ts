/**
 * 显示组件测试
 */

import { HtmlParser } from './HtmlParser'

async function runDisplayTest() {
  const testHtml = `
<!DOCTYPE html>
<ui version="1.0">
<window title="显示组件测试" size="600,500">
  <grid padded="true">
    <!-- 标签 -->
    <label row="0" col="0" colspan="2">显示组件演示</label>
    <separator row="1" col="0" colspan="2"/>
    
    <!-- 进度条 -->
    <label row="2" col="0">进度:</label>
    <progressbar row="2" col="1" value="75" max="100"/>
    
    <!-- 表格 -->
    <label row="3" col="0" colspan="2">数据表格:</label>
    <table row="4" col="0" colspan="2" columns="姓名,年龄,城市">
      <tr>
        <td>张三</td>
        <td>25</td>
        <td>北京</td>
      </tr>
      <tr>
        <td>李四</td>
        <td>30</td>
        <td>上海</td>
      </tr>
    </table>
  </grid>
</window>
</ui>
`

  console.log('🔧 显示组件测试开始...')
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
    console.log('\n🔍 显示组件映射检查:')
    
    const gridSchema = result.schema.properties?.child_0
    if (gridSchema?.properties) {
      const children = Object.values(gridSchema.properties)
      children.forEach((child: any, index) => {
        const component = child['x-component']
        const decorator = child['x-component-props']?.['x-decorator']
        console.log(`   组件 ${index + 1}: ${component} ${decorator ? `[${decorator}]` : ''}`)
      })
    }
    
    console.log('\n📊 解析结果摘要:')
    console.log(JSON.stringify({
      window: result.schema['x-component'],
      grid: gridSchema?.['x-component'],
      childrenCount: Object.keys(gridSchema?.properties || {}).length,
      components: Object.values(gridSchema?.properties || {}).map((child: any) => child['x-component'])
    }, null, 2))
    
    console.log('\n✅ 显示组件测试通过！')
    
  } catch (error) {
    console.error('❌ 显示组件测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runDisplayTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})
