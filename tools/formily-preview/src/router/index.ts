import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Designer from '../views/Designer.vue'
import PreviewView from '../views/PreviewView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: '首页 - libuiBuilder Formily 预览工具' }
    },
    {
      path: '/designer',
      name: 'designer',
      component: Designer,
      meta: { title: '可视化设计器' }
    },
    {
      path: '/preview',
      name: 'preview',
      component: PreviewView,
      meta: { title: '模板预览' }
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/'
    }
  ]
})

// 路由守卫：更新页面标题
router.beforeEach((to, from, next) => {
  const title = to.meta.title as string || 'libuiBuilder Formily 预览工具'
  document.title = title
  next()
})

export default router