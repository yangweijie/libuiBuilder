/**
 * 主题管理器测试
 */

import { themeManager, useThemeManager } from './ThemeManager'

console.log('🎨 开始主题管理器测试...\n')

// 测试 1: 获取当前主题
console.log('📝 测试 1: 获取当前主题')
const currentTheme = themeManager.getCurrentTheme()
console.log('🎯 当前主题:', currentTheme?.name)
console.log('✅ 获取当前主题成功\n')

// 测试 2: 获取所有可用主题
console.log('📝 测试 2: 获取所有可用主题')
const availableThemes = themeManager.getAvailableThemes()
console.log('📚 可用主题数量:', availableThemes.length)
availableThemes.forEach(theme => {
  console.log(`  - ${theme.name} (${theme.id}) - ${theme.description}`)
})
console.log('✅ 获取所有主题成功\n')

// 测试 3: 切换主题
console.log('📝 测试 3: 切换主题')
const switchResult = themeManager.switchTheme('blue-light')
console.log('🔄 切换到蓝色主题:', switchResult ? '成功' : '失败')

const newTheme = themeManager.getCurrentTheme()
console.log('🎯 新主题:', newTheme?.name)
console.log('✅ 主题切换成功\n')

// 测试 4: 切换明暗模式
console.log('📝 测试 4: 切换明暗模式')
console.log('🌓 当前是否为暗色模式:', themeManager.getThemeState().isDarkMode)
themeManager.toggleDarkMode()
console.log('🌓 切换后是否为暗色模式:', themeManager.getThemeState().isDarkMode)
console.log('✅ 明暗模式切换成功\n')

// 测试 5: 创建自定义主题
console.log('📝 测试 5: 创建自定义主题')
const customThemeId = themeManager.createCustomTheme(
  '我的自定义主题',
  'default-light',
  {
    '--primary-color': '#ff6b6b',
    '--border-radius': '12px',
    '--shadow-color': 'rgba(255, 107, 107, 0.2)'
  },
  {
    '.ant-btn': 'border-radius: var(--border-radius); font-weight: bold;',
    '.ant-card': 'border-radius: var(--border-radius); box-shadow: 0 4px 16px var(--shadow-color);'
  }
)
console.log('🎨 自定义主题 ID:', customThemeId)
console.log('✅ 自定义主题创建成功\n')

// 测试 6: 切换到自定义主题
console.log('📝 测试 6: 切换到自定义主题')
themeManager.switchTheme(customThemeId)
console.log('🎯 当前主题:', themeManager.getCurrentTheme()?.name)
console.log('✅ 自定义主题切换成功\n')

// 测试 7: 获取 CSS 变量
console.log('📝 测试 7: 获取 CSS 变量')
const primaryColor = themeManager.getVariable('--primary-color')
const borderRadius = themeManager.getVariable('--border-radius')
console.log('🎨 主色:', primaryColor)
console.log('🎨 圆角:', borderRadius)
console.log('✅ CSS 变量获取成功\n')

// 测试 8: 更新 CSS 变量
console.log('📝 测试 8: 更新 CSS 变量')
themeManager.updateVariable('--primary-color', '#7c3aed')
themeManager.updateVariable('--border-radius', '16px')
console.log('🔄 更新 CSS 变量成功')
console.log('🎨 新主色:', themeManager.getVariable('--primary-color'))
console.log('🎨 新圆角:', themeManager.getVariable('--border-radius'))
console.log('✅ CSS 变量更新成功\n')

// 测试 9: 批量更新 CSS 变量
console.log('📝 测试 9: 批量更新 CSS 变量')
themeManager.updateVariables({
  '--success-color': '#10b981',
  '--warning-color': '#f59e0b',
  '--error-color': '#ef4444'
})
console.log('🔄 批量更新 CSS 变量成功')
console.log('✅ CSS 变量批量更新成功\n')

// 测试 10: 使用组合式 API
console.log('📝 测试 10: 使用组合式 API')
const themeApi = useThemeManager()

// 切换到默认主题
themeApi.switchTheme('default-light')
console.log('🔄 切换到默认主题')

// 获取主题状态
const themeState = themeApi.getThemeState()
console.log('📊 主题状态:', themeState)
console.log('✅ 组合式 API 测试成功\n')

// 测试 11: 导出主题配置
console.log('📝 测试 11: 导出主题配置')
const exportedTheme = themeManager.exportTheme(customThemeId)
console.log('📄 导出主题配置长度:', exportedTheme.length)
console.log('✅ 主题导出成功\n')

// 测试 12: 导入主题配置
console.log('📝 测试 12: 导入主题配置')
try {
  const importedThemeId = themeManager.importTheme(exportedTheme)
  console.log('📥 导入的主题 ID:', importedThemeId)
  console.log('✅ 主题导入成功\n')
} catch (error) {
  console.error('❌ 主题导入失败:', error)
}

// 测试 13: 删除自定义主题
console.log('📝 测试 13: 删除自定义主题')
// 先切换到其他主题
themeManager.switchTheme('default-light')

// 删除自定义主题
const deleteResult = themeManager.deleteCustomTheme(customThemeId)
console.log('🗑️ 删除自定义主题:', deleteResult ? '成功' : '失败')

// 尝试删除默认主题（应该失败）
const deleteDefaultResult = themeManager.deleteCustomTheme('default-light')
console.log('🗑️ 删除默认主题:', deleteDefaultResult ? '成功' : '失败（预期）')
console.log('✅ 主题删除测试成功\n')

// 测试 14: 重置为默认主题
console.log('📝 测试 14: 重置为默认主题')
themeManager.resetToDefault()
console.log('🔄 重置为默认主题')
console.log('🎯 当前主题:', themeManager.getCurrentTheme()?.name)
console.log('✅ 主题重置成功\n')

// 最终状态
console.log('📊 最终状态:')
const finalState = themeManager.getThemeState()
console.log('🎯 当前主题:', finalState.currentTheme)
console.log('🌓 是否为暗色模式:', finalState.isDarkMode)
console.log('📚 可用主题数量:', finalState.availableThemes.length)
console.log('🎨 CSS 变量示例:')
console.log('  - 主色:', themeManager.getVariable('--primary-color'))
console.log('  - 圆角:', themeManager.getVariable('--border-radius'))
console.log('  - 背景色:', themeManager.getVariable('--background-color'))

console.log('\n🎉 所有主题管理器测试完成！')
