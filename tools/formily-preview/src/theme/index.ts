/**
 * 主题管理辅助函数
 * 
 * 提供与 libuiBuilder 兼容的 API
 */

import { themeManager, useThemeManager } from './ThemeManager'

// 导出主题管理器
export { themeManager, useThemeManager }

// 兼容 libuiBuilder API
export const theme = {
  // 获取主题管理器实例
  instance: () => themeManager,
  
  // 切换主题
  switch: (themeId: string) => themeManager.switchTheme(themeId),
  
  // 切换明暗模式
  toggleDarkMode: () => themeManager.toggleDarkMode(),
  
  // 获取当前主题
  getCurrent: () => themeManager.getCurrentTheme(),
  
  // 获取所有可用主题
  getAvailable: () => themeManager.getAvailableThemes(),
  
  // 获取主题状态
  getState: () => themeManager.getThemeState(),
  
  // 创建自定义主题
  createCustom: (name: string, baseThemeId?: string, customVariables?: Record<string, string>, customStyles?: Record<string, string>) => 
    themeManager.createCustomTheme(name, baseThemeId, customVariables, customStyles),
  
  // 删除自定义主题
  deleteCustom: (themeId: string) => themeManager.deleteCustomTheme(themeId),
  
  // 导出主题配置
  export: (themeId: string) => themeManager.exportTheme(themeId),
  
  // 导入主题配置
  import: (json: string) => themeManager.importTheme(json),
  
  // CSS 变量管理
  getVariable: (name: string) => themeManager.getVariable(name),
  updateVariable: (name: string, value: string) => themeManager.updateVariable(name, value),
  updateVariables: (variables: Record<string, string>) => themeManager.updateVariables(variables),
  
  // 重置为默认主题
  reset: () => themeManager.resetToDefault()
}

// Vue 组合式 API
export function useTheme() {
  const manager = useThemeManager()
  
  return {
    // 主题管理
    switch: manager.switchTheme,
    toggleDarkMode: manager.toggleDarkMode,
    getCurrent: manager.getCurrentTheme,
    getAvailable: manager.getAvailableThemes,
    getState: manager.getThemeState,
    
    // 自定义主题
    createCustom: manager.createCustomTheme,
    deleteCustom: manager.deleteCustomTheme,
    export: manager.exportTheme,
    import: manager.importTheme,
    
    // CSS 变量管理
    getVariable: manager.getVariable,
    updateVariable: manager.updateVariable,
    updateVariables: manager.updateVariables,
    
    // 工具方法
    reset: manager.resetToDefault,
    
    // 响应式状态
    isDarkMode: manager.isDarkMode
  }
}

// 默认导出
export default {
  instance: () => themeManager,
  ...theme
}