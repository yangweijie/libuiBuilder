/**
 * 主题管理器
 * 
 * 支持多主题切换和 CSS 变量管理的主题系统
 */

export interface ThemeConfig {
  id: string
  name: string
  type: 'light' | 'dark' | 'custom'
  variables: Record<string, string>
  styles: Record<string, string>
  description?: string
  icon?: string
}

export interface ThemeState {
  currentTheme: string
  availableThemes: ThemeConfig[]
  isDarkMode: boolean
}

export class ThemeManager {
  private static instance: ThemeManager
  private themes = new Map<string, ThemeConfig>()
  private currentThemeId: string = 'default-light'
  private isDarkMode: boolean = false
  private styleElement: HTMLStyleElement | null = null

  /**
   * 获取单例实例
   */
  static getInstance(): ThemeManager {
    if (!ThemeManager.instance) {
      ThemeManager.instance = new ThemeManager()
      ThemeManager.instance.initialize()
    }
    return ThemeManager.instance
  }

  /**
   * 初始化主题管理器
   */
  private initialize(): void {
    // 只在浏览器环境中创建样式元素
    if (typeof document !== 'undefined') {
      this.styleElement = document.createElement('style')
      this.styleElement.id = 'theme-styles'
      document.head.appendChild(this.styleElement)
    }

    // 注册默认主题
    this.registerDefaultThemes()

    // 从本地存储加载主题设置
    this.loadFromStorage()

    // 应用当前主题
    this.applyCurrentTheme()
  }

  /**
   * 注册默认主题
   */
  private registerDefaultThemes(): void {
    // 默认浅色主题
    this.registerTheme({
      id: 'default-light',
      name: '默认浅色',
      type: 'light',
      description: '默认的浅色主题，简洁明亮',
      icon: '🌞',
      variables: {
        '--primary-color': '#1890ff',
        '--success-color': '#52c41a',
        '--warning-color': '#faad14',
        '--error-color': '#ff4d4f',
        '--info-color': '#1890ff',
        '--text-color': '#333333',
        '--text-color-secondary': '#666666',
        '--border-color': '#d9d9d9',
        '--border-color-light': '#f0f0f0',
        '--background-color': '#ffffff',
        '--background-color-light': '#f5f5f5',
        '--background-color-dark': '#f0f0f0',
        '--component-background': '#ffffff',
        '--disabled-color': '#bfbfbf',
        '--disabled-bg': '#f5f5f5',
        '--shadow-color': 'rgba(0, 0, 0, 0.15)',
        '--border-radius': '6px',
        '--font-family': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        '--font-size-base': '14px',
        '--line-height-base': '1.5715'
      },
      styles: {
        'body': 'background-color: var(--background-color); color: var(--text-color);',
        '.ant-btn': 'border-radius: var(--border-radius);',
        '.ant-input': 'border-radius: var(--border-radius);',
        '.ant-card': 'border-radius: var(--border-radius); box-shadow: 0 2px 8px var(--shadow-color);',
        '.ant-modal-content': 'border-radius: var(--border-radius);'
      }
    })

    // 默认深色主题
    this.registerTheme({
      id: 'default-dark',
      name: '默认深色',
      type: 'dark',
      description: '默认的深色主题，护眼舒适',
      icon: '🌙',
      variables: {
        '--primary-color': '#177ddc',
        '--success-color': '#49aa19',
        '--warning-color': '#d89614',
        '--error-color': '#a61d24',
        '--info-color': '#177ddc',
        '--text-color': '#ffffff',
        '--text-color-secondary': '#a6a6a6',
        '--border-color': '#434343',
        '--border-color-light': '#303030',
        '--background-color': '#141414',
        '--background-color-light': '#1f1f1f',
        '--background-color-dark': '#0a0a0a',
        '--component-background': '#1f1f1f',
        '--disabled-color': '#595959',
        '--disabled-bg': '#262626',
        '--shadow-color': 'rgba(0, 0, 0, 0.45)',
        '--border-radius': '6px',
        '--font-family': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        '--font-size-base': '14px',
        '--line-height-base': '1.5715'
      },
      styles: {
        'body': 'background-color: var(--background-color); color: var(--text-color);',
        '.ant-btn': 'border-radius: var(--border-radius);',
        '.ant-input': 'border-radius: var(--border-radius);',
        '.ant-card': 'border-radius: var(--border-radius); box-shadow: 0 2px 8px var(--shadow-color);',
        '.ant-modal-content': 'border-radius: var(--border-radius);'
      }
    })

    // 蓝色主题
    this.registerTheme({
      id: 'blue-light',
      name: '蓝色主题',
      type: 'light',
      description: '以蓝色为主的浅色主题',
      icon: '🔵',
      variables: {
        '--primary-color': '#1890ff',
        '--success-color': '#52c41a',
        '--warning-color': '#faad14',
        '--error-color': '#ff4d4f',
        '--info-color': '#1890ff',
        '--text-color': '#262626',
        '--text-color-secondary': '#595959',
        '--border-color': '#d9d9d9',
        '--border-color-light': '#f0f0f0',
        '--background-color': '#f0f8ff',
        '--background-color-light': '#e6f7ff',
        '--background-color-dark': '#bae7ff',
        '--component-background': '#ffffff',
        '--disabled-color': '#bfbfbf',
        '--disabled-bg': '#f5f5f5',
        '--shadow-color': 'rgba(24, 144, 255, 0.1)',
        '--border-radius': '8px',
        '--font-family': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        '--font-size-base': '14px',
        '--line-height-base': '1.5715'
      },
      styles: {
        'body': 'background-color: var(--background-color); color: var(--text-color);',
        '.ant-btn': 'border-radius: var(--border-radius);',
        '.ant-input': 'border-radius: var(--border-radius); border-color: var(--primary-color);',
        '.ant-card': 'border-radius: var(--border-radius); box-shadow: 0 2px 12px var(--shadow-color); border: 1px solid var(--border-color-light);',
        '.ant-modal-content': 'border-radius: var(--border-radius);'
      }
    })

    // 绿色主题
    this.registerTheme({
      id: 'green-light',
      name: '绿色主题',
      type: 'light',
      description: '以绿色为主的浅色主题',
      icon: '🟢',
      variables: {
        '--primary-color': '#52c41a',
        '--success-color': '#52c41a',
        '--warning-color': '#faad14',
        '--error-color': '#ff4d4f',
        '--info-color': '#1890ff',
        '--text-color': '#262626',
        '--text-color-secondary': '#595959',
        '--border-color': '#d9d9d9',
        '--border-color-light': '#f0f0f0',
        '--background-color': '#f6ffed',
        '--background-color-light': '#d9f7be',
        '--background-color-dark': '#b7eb8f',
        '--component-background': '#ffffff',
        '--disabled-color': '#bfbfbf',
        '--disabled-bg': '#f5f5f5',
        '--shadow-color': 'rgba(82, 196, 26, 0.1)',
        '--border-radius': '6px',
        '--font-family': '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
        '--font-size-base': '14px',
        '--line-height-base': '1.5715'
      },
      styles: {
        'body': 'background-color: var(--background-color); color: var(--text-color);',
        '.ant-btn': 'border-radius: var(--border-radius);',
        '.ant-input': 'border-radius: var(--border-radius);',
        '.ant-card': 'border-radius: var(--border-radius); box-shadow: 0 2px 8px var(--shadow-color);',
        '.ant-modal-content': 'border-radius: var(--border-radius);'
      }
    })
  }

  /**
   * 注册新主题
   */
  registerTheme(theme: ThemeConfig): void {
    this.themes.set(theme.id, theme)
    console.log(`✅ 注册主题: ${theme.name} (${theme.id})`)
  }

  /**
   * 切换主题
   */
  switchTheme(themeId: string): boolean {
    const theme = this.themes.get(themeId)
    if (!theme) {
      console.warn(`⚠️ 主题不存在: ${themeId}`)
      return false
    }

    this.currentThemeId = themeId
    this.isDarkMode = theme.type === 'dark'
    
    this.applyCurrentTheme()
    this.saveToStorage()
    
    console.log(`🎨 切换主题: ${theme.name}`)
    return true
  }

  /**
   * 切换明暗模式
   */
  toggleDarkMode(): void {
    const currentTheme = this.getCurrentTheme()
    if (!currentTheme) return

    // 查找对应的明暗主题
    const targetThemeId = currentTheme.type === 'light' 
      ? currentTheme.id.replace('light', 'dark') 
      : currentTheme.id.replace('dark', 'light')
    
    const targetTheme = this.themes.get(targetThemeId)
    if (targetTheme) {
      this.switchTheme(targetThemeId)
    } else {
      // 如果没有对应的主题，切换到默认的明暗主题
      const defaultThemeId = this.isDarkMode ? 'default-light' : 'default-dark'
      this.switchTheme(defaultThemeId)
    }
  }

  /**
   * 应用当前主题
   */
  private applyCurrentTheme(): void {
    const theme = this.getCurrentTheme()
    if (!theme) return

    // 只在浏览器环境中应用样式
    if (typeof document !== 'undefined') {
      // 构建 CSS 变量
      let css = ':root {\n'
      for (const [key, value] of Object.entries(theme.variables)) {
        css += `  ${key}: ${value};\n`
      }
      css += '}\n\n'

      // 添加样式规则
      for (const [selector, rules] of Object.entries(theme.styles)) {
        css += `${selector} {\n`
        const ruleLines = rules.split(';').filter(line => line.trim())
        for (const line of ruleLines) {
          css += `  ${line.trim()};\n`
        }
        css += '}\n\n'
      }

      // 更新样式元素
      if (this.styleElement) {
        this.styleElement.textContent = css
      } else {
        // 如果样式元素不存在，创建一个
        this.styleElement = document.createElement('style')
        this.styleElement.id = 'theme-styles'
        this.styleElement.textContent = css
        document.head.appendChild(this.styleElement)
      }

      // 更新文档类名
      document.documentElement.classList.toggle('dark-theme', this.isDarkMode)
      document.documentElement.classList.toggle('light-theme', !this.isDarkMode)
      document.documentElement.setAttribute('data-theme', theme.id)
    }
  }

  /**
   * 获取当前主题
   */
  getCurrentTheme(): ThemeConfig | undefined {
    return this.themes.get(this.currentThemeId)
  }

  /**
   * 获取所有可用主题
   */
  getAvailableThemes(): ThemeConfig[] {
    return Array.from(this.themes.values())
  }

  /**
   * 获取主题状态
   */
  getThemeState(): ThemeState {
    return {
      currentTheme: this.currentThemeId,
      availableThemes: this.getAvailableThemes(),
      isDarkMode: this.isDarkMode
    }
  }

  /**
   * 创建自定义主题
   */
  createCustomTheme(
    name: string,
    baseThemeId: string = 'default-light',
    customVariables: Record<string, string> = {},
    customStyles: Record<string, string> = {}
  ): string {
    const baseTheme = this.themes.get(baseThemeId)
    if (!baseTheme) {
      throw new Error(`基础主题不存在: ${baseThemeId}`)
    }

    const themeId = `custom-${Date.now()}`
    const theme: ThemeConfig = {
      id: themeId,
      name,
      type: baseTheme.type,
      description: `自定义主题 - 基于 ${baseTheme.name}`,
      icon: '🎨',
      variables: { ...baseTheme.variables, ...customVariables },
      styles: { ...baseTheme.styles, ...customStyles }
    }

    this.registerTheme(theme)
    return themeId
  }

  /**
   * 删除自定义主题
   */
  deleteCustomTheme(themeId: string): boolean {
    if (!themeId.startsWith('custom-')) {
      console.warn(`⚠️ 只能删除自定义主题: ${themeId}`)
      return false
    }

    if (this.currentThemeId === themeId) {
      console.warn(`⚠️ 不能删除当前正在使用的主题`)
      return false
    }

    const deleted = this.themes.delete(themeId)
    if (deleted) {
      console.log(`🗑️ 删除主题: ${themeId}`)
    }
    return deleted
  }

  /**
   * 导出主题配置
   */
  exportTheme(themeId: string): string {
    const theme = this.themes.get(themeId)
    if (!theme) {
      throw new Error(`主题不存在: ${themeId}`)
    }

    return JSON.stringify(theme, null, 2)
  }

  /**
   * 导入主题配置
   */
  importTheme(json: string): string {
    try {
      const theme = JSON.parse(json) as ThemeConfig
      
      // 验证主题配置
      if (!theme.id || !theme.name || !theme.type || !theme.variables) {
        throw new Error('无效的主题配置')
      }

      // 确保 ID 唯一
      if (this.themes.has(theme.id)) {
        theme.id = `${theme.id}-${Date.now()}`
      }

      this.registerTheme(theme)
      return theme.id
    } catch (error) {
      console.error('主题导入失败:', error)
      throw new Error('无效的 JSON 格式或主题配置')
    }
  }

  /**
   * 保存到本地存储
   */
  private saveToStorage(): void {
    try {
      localStorage.setItem('theme-manager', JSON.stringify({
        currentThemeId: this.currentThemeId,
        isDarkMode: this.isDarkMode
      }))
    } catch (error) {
      console.error('主题设置保存失败:', error)
    }
  }

  /**
   * 从本地存储加载
   */
  private loadFromStorage(): void {
    try {
      const saved = localStorage.getItem('theme-manager')
      if (saved) {
        const data = JSON.parse(saved)
        if (data.currentThemeId && this.themes.has(data.currentThemeId)) {
          this.currentThemeId = data.currentThemeId
          this.isDarkMode = data.isDarkMode || false
        }
      }
    } catch (error) {
      console.error('主题设置加载失败:', error)
    }
  }

  /**
   * 重置为默认主题
   */
  resetToDefault(): void {
    this.switchTheme('default-light')
  }

  /**
   * 获取 CSS 变量值
   */
  getVariable(name: string): string | null {
    const theme = this.getCurrentTheme()
    return theme?.variables[name] || null
  }

  /**
   * 更新 CSS 变量
   */
  updateVariable(name: string, value: string): void {
    const theme = this.getCurrentTheme()
    if (theme) {
      theme.variables[name] = value
      this.applyCurrentTheme()
      console.log(`🎨 更新 CSS 变量: ${name} = ${value}`)
    }
  }

  /**
   * 批量更新 CSS 变量
   */
  updateVariables(variables: Record<string, string>): void {
    const theme = this.getCurrentTheme()
    if (theme) {
      Object.assign(theme.variables, variables)
      this.applyCurrentTheme()
      console.log(`🎨 批量更新 CSS 变量:`, variables)
    }
  }
}

// 导出单例实例
export const themeManager = ThemeManager.getInstance()

// Vue 组合式 API 封装
export function useThemeManager() {
  const instance = ThemeManager.getInstance()
  
  return {
    // 主题管理
    switchTheme: instance.switchTheme.bind(instance),
    toggleDarkMode: instance.toggleDarkMode.bind(instance),
    getCurrentTheme: instance.getCurrentTheme.bind(instance),
    getAvailableThemes: instance.getAvailableThemes.bind(instance),
    getThemeState: instance.getThemeState.bind(instance),
    
    // 自定义主题
    createCustomTheme: instance.createCustomTheme.bind(instance),
    deleteCustomTheme: instance.deleteCustomTheme.bind(instance),
    exportTheme: instance.exportTheme.bind(instance),
    importTheme: instance.importTheme.bind(instance),
    
    // CSS 变量管理
    getVariable: instance.getVariable.bind(instance),
    updateVariable: instance.updateVariable.bind(instance),
    updateVariables: instance.updateVariables.bind(instance),
    
    // 工具方法
    resetToDefault: instance.resetToDefault.bind(instance),
    
    // 响应式状态
    isDarkMode: instance['isDarkMode']
  }
}