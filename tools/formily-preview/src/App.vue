<template>
  <a-config-provider :theme="themeConfig">
    <a-layout class="app-layout">
      <!-- 顶部导航栏 -->
      <a-layout-header class="app-header">
        <div class="header-content">
          <div class="logo">
            <span class="logo-icon">🎨</span>
            <span class="logo-text">libuiBuilder Formily</span>
          </div>
          
          <a-menu
            v-model:selectedKeys="selectedKeys"
            mode="horizontal"
            theme="dark"
            class="nav-menu"
          >
            <a-menu-item key="home" @click="goToHome">
              <template #icon><HomeOutlined /></template>
              首页
            </a-menu-item>
            <a-menu-item key="designer" @click="goToDesigner">
              <template #icon><EditOutlined /></template>
              设计器
            </a-menu-item>
            <a-menu-item key="preview" @click="goToPreview">
              <template #icon><EyeOutlined /></template>
              预览
            </a-menu-item>
          </a-menu>
          
          <div class="header-actions">
            <a-space>
              <a-tooltip title="切换主题">
                <a-switch
                  v-model:checked="isDarkTheme"
                  checked-children="🌙"
                  un-checked-children="🌞"
                  @change="toggleTheme"
                />
              </a-tooltip>
              <a-tooltip title="GitHub">
                <a-button type="text" @click="openGitHub">
                  <template #icon><GithubOutlined /></template>
                </a-button>
              </a-tooltip>
              <a-tooltip title="文档">
                <a-button type="text" @click="openDocs">
                  <template #icon><BookOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-space>
          </div>
        </div>
      </a-layout-header>

      <!-- 主内容区域 -->
      <a-layout-content class="app-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </a-layout-content>

      <!-- 底部信息栏 -->
      <a-layout-footer class="app-footer">
        <div class="footer-content">
          <div class="footer-left">
            <span>libuiBuilder Formily 预览工具 v1.0.0</span>
            <a-divider type="vertical" />
            <span>基于 Vue 3 + Formily 构建</span>
          </div>
          <div class="footer-right">
            <a-space>
              <a href="#" @click.prevent="showAbout">关于</a>
              <a-divider type="vertical" />
              <a href="#" @click.prevent="showHelp">帮助</a>
              <a-divider type="vertical" />
              <a href="#" @click.prevent="showFeedback">反馈</a>
            </a-space>
          </div>
        </div>
      </a-layout-footer>
    </a-layout>

    <!-- 关于对话框 -->
    <a-modal
      v-model:open="showAboutModal"
      title="关于 libuiBuilder Formily"
      :footer="null"
      width="500px"
    >
      <div class="about-content">
        <div class="about-header">
          <div class="about-icon">🎨</div>
          <h3>libuiBuilder Formily 预览工具</h3>
          <p>高性能、现代化的 UI 预览和设计平台</p>
        </div>
        
        <a-divider />
        
        <a-descriptions :column="1" bordered size="small">
          <a-descriptions-item label="版本">v1.0.0</a-descriptions-item>
          <a-descriptions-item label="技术栈">
            Vue 3 + Formily 2.x + TypeScript + Ant Design Vue
          </a-descriptions-item>
          <a-descriptions-item label="构建工具">Vite</a-descriptions-item>
          <a-descriptions-item label="许可证">MIT</a-descriptions-item>
          <a-descriptions-item label="GitHub">
            <a href="https://github.com/yangweijie/libuiBuilder" target="_blank">
              yangweijie/libuiBuilder
            </a>
          </a-descriptions-item>
        </a-descriptions>
        
        <div class="about-features">
          <h4>主要特性：</h4>
          <ul>
            <li>支持 .ui.html 模板文件预览</li>
            <li>拖拽式可视化设计器</li>
            <li>实时状态绑定和事件调试</li>
            <li>多主题切换支持</li>
            <li>高性能 Formily 表单渲染</li>
          </ul>
        </div>
      </div>
    </a-modal>
  </a-config-provider>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { theme } from 'ant-design-vue'
import {
  HomeOutlined,
  EditOutlined,
  EyeOutlined,
  GithubOutlined,
  BookOutlined
} from '@ant-design/icons-vue'

const router = useRouter()
const route = useRoute()

// 主题管理
const isDarkTheme = ref(false)
const showAboutModal = ref(false)

// 根据路由更新选中的菜单项
const selectedKeys = ref<string[]>(['home'])

watch(
  () => route.name,
  (name) => {
    if (name === 'home') selectedKeys.value = ['home']
    else if (name === 'designer') selectedKeys.value = ['designer']
    else if (name === 'preview') selectedKeys.value = ['preview']
  },
  { immediate: true }
)

// 主题配置
const themeConfig = computed(() => {
  const algorithm = isDarkTheme.value ? theme.darkAlgorithm : theme.defaultAlgorithm
  return {
    algorithm,
    token: {
      colorPrimary: '#1890ff',
      borderRadius: 6,
      colorBgContainer: isDarkTheme.value ? '#1f1f1f' : '#ffffff'
    }
  }
})

// 导航方法
const goToHome = () => {
  router.push('/')
}

const goToDesigner = () => {
  router.push('/designer')
}

const goToPreview = () => {
  router.push('/preview')
}

// 主题切换
const toggleTheme = (checked: boolean) => {
  isDarkTheme.value = checked
  localStorage.setItem('theme', checked ? 'dark' : 'light')
}

// 初始化主题
const initTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  isDarkTheme.value = savedTheme === 'dark'
}

// 其他操作
const openGitHub = () => {
  window.open('https://github.com/yangweijie/libuiBuilder', '_blank')
}

const openDocs = () => {
  // TODO: 打开文档链接
  console.log('打开文档')
}

const showAbout = () => {
  showAboutModal.value = true
}

const showHelp = () => {
  // TODO: 显示帮助信息
  console.log('显示帮助')
}

const showFeedback = () => {
  // TODO: 显示反馈表单
  console.log('显示反馈')
}

// 初始化
initTheme()
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
}

.app-header {
  padding: 0;
  background: #001529;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 64px;
}

.logo {
  display: flex;
  align-items: center;
  color: white;
  font-size: 18px;
  font-weight: 600;
}

.logo-icon {
  font-size: 24px;
  margin-right: 12px;
}

.nav-menu {
  flex: 1;
  justify-content: center;
  border-bottom: none;
  background: transparent;
}

.header-actions {
  display: flex;
  align-items: center;
}

.app-content {
  padding: 24px;
  background: #f0f2f5;
  min-height: calc(100vh - 128px);
}

.app-footer {
  padding: 16px 24px;
  background: white;
  border-top: 1px solid #f0f0f0;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-left {
  color: #666;
  font-size: 14px;
}

.footer-right a {
  color: #666;
  text-decoration: none;
}

.footer-right a:hover {
  color: #1890ff;
}

/* 过渡动画 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* 关于对话框样式 */
.about-content {
  text-align: center;
}

.about-header {
  margin-bottom: 24px;
}

.about-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.about-header h3 {
  margin: 0 0 8px 0;
  font-size: 20px;
}

.about-header p {
  margin: 0;
  color: #666;
}

.about-features {
  margin-top: 24px;
  text-align: left;
}

.about-features h4 {
  margin-bottom: 12px;
  font-size: 16px;
}

.about-features ul {
  padding-left: 20px;
  margin: 0;
}

.about-features li {
  margin-bottom: 8px;
  color: #666;
}
</style>