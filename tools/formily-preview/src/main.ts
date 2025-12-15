import { createApp } from 'vue'
import { createPinia } from 'pinia'
// import Formily from '@formily/vue'  // 暂时注释，因为导出问题
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css'

import App from './App.vue'
import router from './router'
import libuiComponents from './components'

import './assets/main.css'

const app = createApp(App)

// 安装 Pinia 状态管理
app.use(createPinia())

// 安装 Formily（暂时注释，因为导出问题）
// app.use(Formily)

// 安装 Ant Design Vue
app.use(Antd)

// 安装 libuiBuilder 自定义组件
app.use(libuiComponents)

// 安装路由
app.use(router)

app.mount('#app')