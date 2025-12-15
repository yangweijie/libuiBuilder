<template>
  <div class="designer-view">
    <a-page-header
      title="可视化设计器"
      sub-title="拖拽式界面设计工具"
      @back="goBack"
    >
      <template #extra>
        <a-space>
          <a-button @click="saveDesign">
            <template #icon><SaveOutlined /></template>
            保存
          </a-button>
          <a-button type="primary" @click="exportCode">
            <template #icon><ExportOutlined /></template>
            导出代码
          </a-button>
          <a-button @click="resetDesign">
            <template #icon><RedoOutlined /></template>
            重置
          </a-button>
        </a-space>
      </template>
    </a-page-header>

    <a-divider />

    <div class="designer-container">
      <a-layout>
        <!-- 左侧组件面板 -->
        <a-layout-sider width="280" theme="light" class="components-panel">
          <div class="panel-header">
            <h3>📦 组件库</h3>
            <a-input-search placeholder="搜索组件..." />
          </div>
          
          <a-tabs v-model:activeKey="activeComponentTab">
            <a-tab-pane key="containers" tab="容器">
              <div class="component-list">
                <div class="component-item" draggable="true" data-component="window">
                  <div class="component-icon">🪟</div>
                  <div class="component-info">
                    <div class="component-name">窗口</div>
                    <div class="component-desc">主窗口容器</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="grid">
                  <div class="component-icon">📐</div>
                  <div class="component-info">
                    <div class="component-name">网格</div>
                    <div class="component-desc">二维网格布局</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="vbox">
                  <div class="component-icon">📦</div>
                  <div class="component-info">
                    <div class="component-name">垂直盒子</div>
                    <div class="component-desc">垂直排列容器</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="hbox">
                  <div class="component-icon">📦</div>
                  <div class="component-info">
                    <div class="component-name">水平盒子</div>
                    <div class="component-desc">水平排列容器</div>
                  </div>
                </div>
              </div>
            </a-tab-pane>
            
            <a-tab-pane key="inputs" tab="输入控件">
              <div class="component-list">
                <div class="component-item" draggable="true" data-component="input">
                  <div class="component-icon">📝</div>
                  <div class="component-info">
                    <div class="component-name">输入框</div>
                    <div class="component-desc">单行文本输入</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="textarea">
                  <div class="component-icon">📄</div>
                  <div class="component-info">
                    <div class="component-name">多行文本</div>
                    <div class="component-desc">多行文本输入</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="button">
                  <div class="component-icon">🔘</div>
                  <div class="component-info">
                    <div class="component-name">按钮</div>
                    <div class="component-desc">可点击按钮</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="checkbox">
                  <div class="component-icon">☑️</div>
                  <div class="component-info">
                    <div class="component-name">复选框</div>
                    <div class="component-desc">多选项选择</div>
                  </div>
                </div>
              </div>
            </a-tab-pane>
            
            <a-tab-pane key="display" tab="显示控件">
              <div class="component-list">
                <div class="component-item" draggable="true" data-component="label">
                  <div class="component-icon">🏷️</div>
                  <div class="component-info">
                    <div class="component-name">标签</div>
                    <div class="component-desc">文本标签</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="progressbar">
                  <div class="component-icon">📊</div>
                  <div class="component-info">
                    <div class="component-name">进度条</div>
                    <div class="component-desc">进度显示</div>
                  </div>
                </div>
                <div class="component-item" draggable="true" data-component="separator">
                  <div class="component-icon">➖</div>
                  <div class="component-info">
                    <div class="component-name">分隔线</div>
                    <div class="component-desc">水平或垂直分隔</div>
                  </div>
                </div>
              </div>
            </a-tab-pane>
          </a-tabs>
        </a-layout-sider>

        <!-- 中间设计画布 -->
        <a-layout-content class="design-canvas">
          <div class="canvas-header">
            <a-space>
              <span>设计画布</span>
              <a-tag color="blue">实时预览</a-tag>
              <a-select v-model:value="currentTheme" style="width: 120px">
                <a-select-option value="default">默认主题</a-select-option>
                <a-select-option value="dark">暗色主题</a-select-option>
                <a-select-option value="macos">macOS 风格</a-select-option>
                <a-select-option value="windows">Windows 风格</a-select-option>
              </a-select>
            </a-space>
          </div>
          
          <div class="canvas-content" id="designCanvas">
            <div class="empty-canvas" v-if="!hasComponents">
              <div class="empty-content">
                <div class="empty-icon">🎨</div>
                <h3>开始设计</h3>
                <p>从左侧拖拽组件到此处开始设计界面</p>
                <p class="hint">提示：支持拖拽、调整大小、属性编辑</p>
              </div>
            </div>
            
            <!-- 这里将放置动态生成的组件 -->
            <div class="components-container" v-else>
              <!-- 组件将通过 Formily 动态渲染 -->
            </div>
          </div>
        </a-layout-content>

        <!-- 右侧属性面板 -->
        <a-layout-sider width="320" theme="light" class="properties-panel">
          <div class="panel-header">
            <h3>⚙️ 属性面板</h3>
            <a-tag v-if="selectedComponent" color="green">
              {{ selectedComponent.type }}
            </a-tag>
          </div>
          
          <div class="properties-content" v-if="selectedComponent">
            <!-- 组件属性表单将通过 Formily 动态生成 -->
            <div class="no-properties" v-if="!selectedComponent.properties">
              <p>该组件暂无属性可配置</p>
            </div>
          </div>
          
          <div class="no-selection" v-else>
            <div class="empty-state">
              <div class="empty-icon">👆</div>
              <h4>未选择组件</h4>
              <p>点击设计画布中的组件以编辑其属性</p>
            </div>
          </div>
        </a-layout-sider>
      </a-layout>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { SaveOutlined, ExportOutlined, RedoOutlined } from '@ant-design/icons-vue'

const router = useRouter()

const activeComponentTab = ref('containers')
const currentTheme = ref('default')
const hasComponents = ref(false)
const selectedComponent = ref<any>(null)

const goBack = () => {
  router.push('/')
}

const saveDesign = () => {
  console.log('保存设计')
  // TODO: 实现保存逻辑
}

const exportCode = () => {
  console.log('导出代码')
  // TODO: 实现导出逻辑
}

const resetDesign = () => {
  console.log('重置设计')
  // TODO: 实现重置逻辑
}
</script>

<style scoped>
.designer-view {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.designer-container {
  flex: 1;
  overflow: hidden;
}

.components-panel,
.properties-panel {
  border-right: 1px solid #f0f0f0;
  overflow-y: auto;
}

.panel-header {
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.panel-header h3 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
}

.component-list {
  padding: 8px;
}

.component-item {
  display: flex;
  align-items: center;
  padding: 12px;
  margin-bottom: 8px;
  border: 1px solid #f0f0f0;
  border-radius: 6px;
  cursor: grab;
  transition: all 0.2s;
  background: white;
}

.component-item:hover {
  border-color: #1890ff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.component-icon {
  font-size: 24px;
  margin-right: 12px;
}

.component-info {
  flex: 1;
}

.component-name {
  font-weight: 500;
  margin-bottom: 4px;
}

.component-desc {
  font-size: 12px;
  color: #666;
}

.design-canvas {
  padding: 16px;
  background: #f5f5f5;
  overflow: auto;
}

.canvas-header {
  margin-bottom: 16px;
  padding: 12px 16px;
  background: white;
  border-radius: 6px;
  border: 1px solid #f0f0f0;
}

.canvas-content {
  background: white;
  border-radius: 8px;
  border: 2px dashed #f0f0f0;
  min-height: 600px;
  position: relative;
}

.empty-canvas {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 600px;
}

.empty-content {
  text-align: center;
  color: #999;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.hint {
  font-size: 12px;
  margin-top: 8px;
  color: #ccc;
}

.properties-content {
  padding: 16px;
}

.no-properties,
.no-selection {
  padding: 40px 20px;
  text-align: center;
  color: #999;
}

.empty-state {
  text-align: center;
}

.empty-state .empty-icon {
  font-size: 32px;
  margin-bottom: 12px;
}
</style>