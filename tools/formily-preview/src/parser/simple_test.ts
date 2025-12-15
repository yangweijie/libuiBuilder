/**
 * 简单 HTML 解析器测试
 */

import { HtmlParser } from './HtmlParser'

async function runSimpleTest() {
  const simpleHtml = `
<!DOCTYPE html>
<ui version="1.0">
<window title="简单测试" size="300,200">
  <grid padded="true">
    <label row="0" col="0">测试标签</label>
    <input row="0" col="1" type="text" placeholder="输入文本" bind="testInput"/>
    <button row="1" col="0" colspan="2" onclick="handleTest">测试按钮</button>
  </grid>
</window>
</ui>
`

  console.log('🔧 简单 HTML 解析器测试开始...')
  console.log('📄 测试 HTML 内容:')
  console.log(simpleHtml)
  console.log('\n---\n')

  try {
    const parser = new HtmlParser({
      preserveComments: true,
      validateSchema: true,
      strictMode: false
    })

    console.log('🔄 开始解析 HTML...')
    const startTime = Date.now()
    
    const result = await parser.parse(simpleHtml)
    
    const endTime = Date.now()
    console.log(`✅ 解析完成，耗时: ${endTime - startTime}ms`)
    
    console.log('\n📊 解析结果:')
    console.log(JSON.stringify(result.schema, null, 2))
    
    console.log('\n🔗 状态绑定:')
    console.log(result.stateBindings)
    
    console.log('\n🎯 事件处理器:')
    console.log(result.eventHandlers)
    
    console.log('\n✅ 简单测试通过！')
    
  } catch (error) {
    console.error('❌ 简单测试失败:')
    console.error(error instanceof Error ? error.message : String(error))
    
    if (error instanceof Error && error.stack) {
      console.error('\n堆栈跟踪:')
      console.error(error.stack)
    }
    
    process.exit(1)
  }
}

// 运行测试
runSimpleTest().catch(error => {
  console.error('❌ 测试运行失败:', error)
  process.exit(1)
})
