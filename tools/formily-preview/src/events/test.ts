/**
 * 事件管理器测试
 */

import { eventManager, useEventManager } from './EventManager'
import { stateManager } from '@/state'

console.log('🧪 开始事件管理器测试...\n')

// 测试 1: 注册事件处理器
console.log('📝 测试 1: 注册事件处理器')
const loginHandler = (event: Event, state: any) => {
  console.log('🔓 登录事件触发')
  console.log('📊 当前状态:', state)
  console.log('🎯 事件类型:', event.type)
}

const logoutHandler = (event: Event, state: any) => {
  console.log('🚪 登出事件触发')
  console.log('📊 当前状态:', state)
}

eventManager.registerHandler('handleLogin', loginHandler)
eventManager.registerHandler('handleLogout', logoutHandler)

console.log('✅ 事件处理器注册成功\n')

// 测试 2: 绑定事件到组件
console.log('📝 测试 2: 绑定事件到组件')
eventManager.bindEvent('loginButton', 'click', 'handleLogin')
eventManager.bindEvent('logoutButton', 'click', 'handleLogout')

console.log('✅ 事件绑定成功\n')

// 测试 3: 设置状态
console.log('📝 测试 3: 设置状态')
stateManager.set('username', 'testuser')
stateManager.set('isLoggedIn', false)

console.log('✅ 状态设置成功\n')

// 测试 4: 触发事件
console.log('📝 测试 4: 触发事件')
const mockEvent = new Event('click')
eventManager.triggerEvent('loginButton', 'click', mockEvent)

console.log('✅ 事件触发成功\n')

// 测试 5: 获取组件绑定
console.log('📝 测试 5: 获取组件绑定')
const loginBindings = eventManager.getComponentBindings('loginButton')
console.log('🔗 登录按钮绑定:', loginBindings)

const logoutBindings = eventManager.getComponentBindings('logoutButton')
console.log('🔗 登出按钮绑定:', logoutBindings)

console.log('✅ 绑定查询成功\n')

// 测试 6: 验证绑定
console.log('📝 测试 6: 验证绑定')
const validation = eventManager.validateBindings()
console.log('🔍 验证结果:', validation)

console.log('✅ 绑定验证成功\n')

// 测试 7: 使用组合式 API
console.log('📝 测试 7: 使用组合式 API')
const eventManagerApi = useEventManager()

// 注册新处理器
const testHandler = (event: Event, state: any) => {
  console.log('🧪 测试事件触发')
}

eventManagerApi.registerHandler('handleTest', testHandler)
eventManagerApi.bindEvent('testButton', 'click', 'handleTest')

// 触发测试事件
const testEvent = new Event('click')
eventManagerApi.triggerEvent('testButton', 'click', testEvent)

console.log('✅ 组合式 API 测试成功\n')

// 测试 8: 创建 Formily 事件处理器
console.log('📝 测试 8: 创建 Formily 事件处理器')
const formilyHandler = eventManager.createFormilyHandler('formButton', 'click')
console.log('🎯 Formily 处理器:', typeof formilyHandler)

// 测试 9: 创建 Formily 事件属性
console.log('📝 测试 9: 创建 Formily 事件属性')
const formilyEventProps = eventManager.createFormilyEventProps('loginButton')
console.log('🎯 Formily 事件属性:', formilyEventProps)

console.log('✅ Formily 集成测试成功\n')

// 测试 10: 导出和导入配置
console.log('📝 测试 10: 导出和导入配置')
const jsonConfig = eventManager.toJSON()
console.log('📄 JSON 配置:', jsonConfig)

// 清空并重新导入
eventManager.clear()
console.log('🧹 已清空事件管理器')

// 重新注册处理器
eventManager.registerHandler('handleLogin', loginHandler)
eventManager.registerHandler('handleLogout', logoutHandler)

// 从 JSON 导入绑定
eventManager.fromJSON(jsonConfig)

// 验证导入
const importedBindings = eventManager.getAllBindings()
console.log('📥 导入的绑定:', importedBindings)

console.log('✅ 配置导出导入测试成功\n')

// 测试 11: 移除组件绑定
console.log('📝 测试 11: 移除组件绑定')
eventManager.removeComponentBindings('loginButton')
const remainingBindings = eventManager.getAllBindings()
console.log('🗑️ 移除后剩余绑定:', remainingBindings)

console.log('✅ 组件绑定移除测试成功\n')

// 测试 12: 移除事件处理器
console.log('📝 测试 12: 移除事件处理器')
const removed = eventManager.removeHandler('handleLogin')
console.log('🗑️ 处理器移除结果:', removed)

const remainingHandlers = eventManager.getAllHandlers()
console.log('🗑️ 移除后剩余处理器:', remainingHandlers)

console.log('✅ 事件处理器移除测试成功\n')

// 最终状态
console.log('📊 最终状态:')
console.log('🔗 事件绑定数量:', eventManager.getAllBindings().length)
console.log('🎯 事件处理器数量:', eventManager.getAllHandlers().length)
console.log('📄 配置 JSON:', eventManager.toJSON())

console.log('\n🎉 所有事件管理器测试完成！')
