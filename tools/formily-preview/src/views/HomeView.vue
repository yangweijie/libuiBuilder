<template>
  <div class="home-view">
    <a-page-header
      title="libuiBuilder Formily 预览工具"
      sub-title="高性能、现代化的 UI 预览和设计平台"
    >
      <template #extra>
        <a-space>
          <a-button type="primary" @click="goToDesigner">
            <template #icon><EditOutlined /></template>
            开始设计
          </a-button>
          <a-button @click="goToPreview">
            <template #icon><EyeOutlined /></template>
            预览模板
          </a-button>
        </a-space>
      </template>
    </a-page-header>

    <a-divider />

    <div class="content">
      <a-row :gutter="[24, 24]">
        <a-col :span="8">
          <a-card title="🎨 可视化设计器" :bordered="false">
            <template #extra>
              <a-button type="link" @click="goToDesigner">进入</a-button>
            </template>
            <p>拖拽式界面设计，实时预览，代码生成</p>
            <ul>
              <li>支持所有 libuiBuilder 组件</li>
              <li>实时属性编辑</li>
              <li>多主题切换</li>
              <li>导出 HTML 代码</li>
            </ul>
          </a-card>
        </a-col>

        <a-col :span="8">
          <a-card title="👁️ 模板预览" :bordered="false">
            <template #extra>
              <a-button type="link" @click="goToPreview">进入</a-button>
            </template>
            <p>预览现有的 .ui.html 模板文件</p>
            <ul>
              <li>支持文件上传和解析</li>
              <li>状态绑定可视化</li>
              <li>事件调试工具</li>
              <li>布局分析</li>
            </ul>
          </a-card>
        </a-col>

        <a-col :span="8">
          <a-card title="⚡ 技术特性" :bordered="false">
            <p>基于现代前端技术栈构建</p>
            <ul>
              <li>Vue 3 + Composition API</li>
              <li>Formily 2.x 高性能表单</li>
              <li>TypeScript 类型安全</li>
              <li>Ant Design Vue 组件库</li>
              <li>Vite 极速构建</li>
            </ul>
          </a-card>
        </a-col>
      </a-row>

      <a-divider />

      <a-card title="📋 快速开始" :bordered="false">
        <a-steps :current="1">
          <a-step title="选择工具">
            <template #description>
              <p>根据需求选择设计器或预览工具</p>
            </template>
          </a-step>
          <a-step title="创建界面">
            <template #description>
              <p>拖拽组件或上传模板文件</p>
            </template>
          </a-step>
          <a-step title="预览和导出">
            <template #description>
              <p>实时预览并导出代码</p>
            </template>
          </a-step>
        </a-steps>
      </a-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { EditOutlined, EyeOutlined } from '@ant-design/icons-vue'

const router = useRouter()

const goToDesigner = () => {
  router.push('/designer')
}

const goToPreview = () => {
  router.push('/preview')
}
</script>

<style scoped>
.home-view {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.content {
  margin-top: 24px;
}

ul {
  padding-left: 20px;
  margin: 12px 0;
}

li {
  margin-bottom: 8px;
  color: #666;
}
</style>