<template>
  <div class="preview-view">
    <a-page-header
      title="模板预览"
      sub-title="预览和调试 .ui.html 模板文件"
      @back="goBack"
    >
      <template #extra>
        <a-space>
          <a-button @click="loadExample">
            <template #icon><FileTextOutlined /></template>
            加载示例
          </a-button>
          <a-button type="primary" @click="parseHtml">
            <template #icon><PlayCircleOutlined /></template>
            解析预览
          </a-button>
          <a-button @click="clearPreview">
            <template #icon><ClearOutlined /></template>
            清空
          </a-button>
        </a-space>
      </template>
    </a-page-header>

    <a-divider />

    <div class="preview-container">
      <a-layout>
        <!-- 左侧文件面板 -->
        <a-layout-sider width="300" theme="light" class="file-panel">
          <div class="panel-header">
            <h3>📁 文件管理</h3>
            <a-upload
              :before-upload="handleFileUpload"
              :show-upload-list="false"
              accept=".html,.ui.html"
            >
              <a-button type="dashed" block>
                <template #icon><UploadOutlined /></template>
                上传 .ui.html 文件
              </a-button>
            </a-upload>
          </div>

          <div class="file-list">
            <a-list :data-source="fileList" :locale="{ emptyText: '暂无文件' }">
              <template #renderItem="{ item }">
                <a-list-item
                  :class="{ 'file-item': true, 'active': activeFile === item.id }"
                  @click="selectFile(item)"
                >
                  <template #actions>
                    <a-button type="link" size="small" @click.stop="removeFile(item.id)">
                      删除
                    </a-button>
                  </template>
                  <a-list-item-meta>
                    <template #title>
                      <div class="file-name">{{ item.name }}</div>
                    </template>
                    <template #description>
                      <div class="file-info">
                        <span>{{ formatFileSize(item.size) }}</span>
                        <span>·</span>
                        <span>{{ item.modified }}</span>
                      </div>
                    </template>
                  </a-list-item-meta>
                </a-list-item>
              </template>
            </a-list>
          </div>

          <a-divider />

          <div class="state-panel">
            <h3>📊 状态绑定</h3>
            <div class="state-list" v-if="stateBindings.length > 0">
              <a-list :data-source="stateBindings" size="small">
                <template #renderItem="{ item }">
                  <a-list-item>
                    <a-list-item-meta>
                      <template #title>
                        <code>{{ item.key }}</code>
                      </template>
                      <template #description>
                        <span class="state-value">{{ formatStateValue(item.value) }}</span>
                      </template>
                    </a-list-item-meta>
                  </a-list-item>
                </template>
              </a-list>
            </div>
            <div class="no-state" v-else>
              <p>暂无状态绑定</p>
            </div>
          </div>
        </a-layout-sider>

        <!-- 中间预览区域 -->
        <a-layout-content class="preview-content">
          <div class="preview-header">
            <a-space>
              <span>预览区域</span>
              <a-tag color="blue">实时渲染</a-tag>
              <a-select v-model:value="previewScale" style="width: 100px">
                <a-select-option value="0.75">75%</a-select-option>
                <a-select-option value="1.0">100%</a-select-option>
                <a-select-option value="1.25">125%</a-select-option>
                <a-select-option value="1.5">150%</a-select-option>
              </a-select>
              <a-switch v-model:checked="showGrid" checked-children="网格" un-checked-children="网格" />
              <a-switch v-model:checked="showBindings" checked-children="绑定" un-checked-children="绑定" />
            </a-space>
          </div>

          <div class="preview-wrapper" :style="{ transform: `scale(${previewScale})` }">
            <div class="preview-area" id="previewArea">
              <div class="no-preview" v-if="!currentHtml">
                <div class="empty-content">
                  <div class="empty-icon">👁️</div>
                  <h3>等待预览</h3>
                  <p>请上传或选择 .ui.html 文件开始预览</p>
                  <p class="hint">支持拖拽上传，自动解析和渲染</p>
                </div>
              </div>

              <div class="error-preview" v-else-if="parseError">
                <div class="error-content">
                  <div class="error-icon">❌</div>
                  <h3>解析错误</h3>
                  <p>{{ parseError }}</p>
                  <a-button type="primary" @click="retryParse">重试</a-button>
                </div>
              </div>

              <!-- Formily 预览渲染区域 -->
              <div class="formily-preview" v-else-if="formilySchema">
                <!-- Formily 表单将通过动态 Schema 渲染 -->
                <div class="preview-loading" v-if="isParsing">
                  <a-spin tip="正在解析和渲染..." />
                </div>
              </div>

              <!-- 原始 HTML 预览（备用） -->
              <div class="raw-preview" v-else-if="currentHtml && !formilySchema">
                <div class="html-content" v-html="currentHtml"></div>
              </div>
            </div>
          </div>
        </a-layout-content>

        <!-- 右侧信息面板 -->
        <a-layout-sider width="320" theme="light" class="info-panel">
          <div class="panel-header">
            <h3>ℹ️ 预览信息</h3>
            <a-tag v-if="currentFile" color="blue">
              {{ currentFile.name }}
            </a-tag>
          </div>

          <a-tabs v-model:activeKey="activeInfoTab">
            <a-tab-pane key="properties" tab="属性">
              <div class="properties-info" v-if="componentProperties.length > 0">
                <a-descriptions :column="1" size="small" bordered>
                  <a-descriptions-item
                    v-for="prop in componentProperties"
                    :key="prop.name"
                    :label="prop.name"
                  >
                    {{ prop.value }}
                  </a-descriptions-item>
                </a-descriptions>
              </div>
              <div class="no-properties" v-else>
                <p>暂无属性信息</p>
              </div>
            </a-tab-pane>

            <a-tab-pane key="events" tab="事件">
              <div class="events-info" v-if="eventHandlers.length > 0">
                <a-list :data-source="eventHandlers" size="small">
                  <template #renderItem="{ item }">
                    <a-list-item>
                      <a-list-item-meta>
                        <template #title>
                          <code>{{ item.name }}</code>
                        </template>
                        <template #description>
                          <span class="event-type">{{ item.type }}</span>
                        </template>
                      </a-list-item-meta>
                    </a-list-item>
                  </template>
                </a-list>
              </div>
              <div class="no-events" v-else>
                <p>暂无事件处理器</p>
              </div>
            </a-tab-pane>

            <a-tab-pane key="layout" tab="布局">
              <div class="layout-info" v-if="layoutInfo">
                <a-descriptions :column="1" size="small" bordered>
                  <a-descriptions-item label="根元素">
                    {{ layoutInfo.rootElement }}
                  </a-descriptions-item>
                  <a-descriptions-item label="组件数量">
                    {{ layoutInfo.componentCount }}
                  </a-descriptions-item>
                  <a-descriptions-item label="布局类型">
                    {{ layoutInfo.layoutType }}
                  </a-descriptions-item>
                  <a-descriptions-item label="状态绑定数">
                    {{ layoutInfo.stateBindingCount }}
                  </a-descriptions-item>
                  <a-descriptions-item label="事件处理器数">
                    {{ layoutInfo.eventHandlerCount }}
                  </a-descriptions-item>
                </a-descriptions>
              </div>
              <div class="no-layout" v-else>
                <p>暂无布局信息</p>
              </div>
            </a-tab-pane>
          </a-tabs>
        </a-layout-sider>
      </a-layout>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  FileTextOutlined, 
  PlayCircleOutlined, 
  ClearOutlined,
  UploadOutlined 
} from '@ant-design/icons-vue'
import type { UploadProps } from 'ant-design-vue'

const router = useRouter()

// 状态管理
const fileList = ref<any[]>([])
const activeFile = ref<string>('')
const currentFile = ref<any>(null)
const currentHtml = ref<string>('')
const formilySchema = ref<any>(null)
const parseError = ref<string>('')
const isParsing = ref<boolean>(false)

// 预览设置
const previewScale = ref<number>(1.0)
const showGrid = ref<boolean>(false)
const showBindings = ref<boolean>(true)

// 信息面板
const activeInfoTab = ref<string>('properties')
const stateBindings = ref<any[]>([])
const componentProperties = ref<any[]>([])
const eventHandlers = ref<any[]>([])
const layoutInfo = ref<any>(null)

const goBack = () => {
  router.push('/')
}

const loadExample = () => {
  console.log('加载示例文件')
  // TODO: 实现示例加载逻辑
}

const parseHtml = () => {
  if (!currentHtml.value) {
    return
  }
  
  isParsing.value = true
  parseError.value = ''
  
  // TODO: 调用 HTML 解析器
  setTimeout(() => {
    isParsing.value = false
    // 模拟解析结果
    formilySchema.value = {
      type: 'object',
      properties: {
        preview: {
          type: 'void',
          'x-component': 'PreviewContainer',
          properties: {}
        }
      }
    }
    
    // 模拟状态绑定
    stateBindings.value = [
      { key: 'username', value: '' },
      { key: 'password', value: '' },
      { key: 'rememberMe', value: false }
    ]
    
    // 模拟事件处理器
    eventHandlers.value = [
      { name: 'handleLogin', type: 'onclick' },
      { name: 'handleReset', type: 'onclick' }
    ]
    
    // 模拟布局信息
    layoutInfo.value = {
      rootElement: 'window',
      componentCount: 8,
      layoutType: 'grid',
      stateBindingCount: 3,
      eventHandlerCount: 2
    }
  }, 1000)
}

const clearPreview = () => {
  currentHtml.value = ''
  formilySchema.value = null
  parseError.value = ''
  stateBindings.value = []
  componentProperties.value = []
  eventHandlers.value = []
  layoutInfo.value = null
}

const handleFileUpload: UploadProps['beforeUpload'] = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    currentHtml.value = e.target?.result as string
    currentFile.value = {
      id: Date.now().toString(),
      name: file.name,
      size: file.size,
      modified: new Date().toLocaleString()
    }
    
    // 自动添加到文件列表
    if (!fileList.value.find(f => f.name === file.name)) {
      fileList.value.push(currentFile.value)
    }
    
    activeFile.value = currentFile.value.id
    parseHtml()
  }
  reader.readAsText(file)
  
  return false // 阻止自动上传
}

const selectFile = (file: any) => {
  activeFile.value = file.id
  currentFile.value = file
  // TODO: 加载文件内容
}

const removeFile = (id: string) => {
  fileList.value = fileList.value.filter(f => f.id !== id)
  if (activeFile.value === id) {
    activeFile.value = ''
    currentFile.value = null
    clearPreview()
  }
}

const retryParse = () => {
  parseError.value = ''
  parseHtml()
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatStateValue = (value: any): string => {
  if (value === null || value === undefined) return 'null'
  if (typeof value === 'boolean') return value ? 'true' : 'false'
  if (typeof value === 'object') return JSON.stringify(value)
  return String(value)
}
</script>

<style scoped>
.preview-view {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.preview-container {
  flex: 1;
  overflow: hidden;
}

.file-panel,
.info-panel {
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

.file-list {
  padding: 8px;
}

.file-item {
  padding: 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.file-item:hover {
  background: #f5f5f5;
}

.file-item.active {
  background: #e6f7ff;
  border: 1px solid #91d5ff;
}

.file-name {
  font-weight: 500;
}

.file-info {
  font-size: 12px;
  color: #999;
}

.state-panel {
  padding: 16px;
}

.state-list {
  margin-top: 12px;
}

.state-value {
  font-family: monospace;
  color: #1890ff;
}

.preview-content {
  padding: 16px;
  background: #f5f5f5;
  overflow: auto;
}

.preview-header {
  margin-bottom: 16px;
  padding: 12px 16px;
  background: white;
  border-radius: 6px;
  border: 1px solid #f0f0f0;
}

.preview-wrapper {
  transform-origin: top center;
  transition: transform 0.2s;
}

.preview-area {
  background: white;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
  min-height: 600px;
  position: relative;
}

.no-preview,
.error-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 600px;
}

.empty-content,
.error-content {
  text-align: center;
  color: #999;
}

.empty-icon,
.error-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.error-icon {
  color: #ff4d4f;
}

.hint {
  font-size: 12px;
  margin-top: 8px;
  color: #ccc;
}

.preview-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 600px;
}

.properties-info,
.events-info,
.layout-info {
  padding: 16px;
}

.no-properties,
.no-events,
.no-layout,
.no-state {
  padding: 40px 20px;
  text-align: center;
  color: #999;
}

.event-type {
  font-size: 12px;
  color: #666;
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 3px;
}
</style>