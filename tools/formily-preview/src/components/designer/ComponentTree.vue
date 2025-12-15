<template>
  <div class="component-tree">
    <div 
      v-for="component in components"
      :key="component.id"
      class="tree-node"
    >
      <component-node
        :component="component"
        :selected="selectedComponent?.id === component.id"
        @select="$emit('select', component)"
        @update="$emit('update', $event)"
        @delete="$emit('delete', component)"
      />
      
      <!-- 递归渲染子组件 -->
      <div 
        v-if="component.children && component.children.length > 0"
        class="tree-children"
      >
        <component-tree
          :components="component.children"
          :selected-component="selectedComponent"
          @select="$emit('select', $event)"
          @update="$emit('update', $event)"
          @delete="$emit('delete', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'
import ComponentNode from './ComponentNode.vue'
import type { ComponentConfig } from '@/types'

defineProps<{
  components: ComponentConfig[]
  selectedComponent?: ComponentConfig | null
}>()

defineEmits<{
  select: [component: ComponentConfig]
  update: [component: ComponentConfig]
  delete: [component: ComponentConfig]
}>()
</script>

<style scoped>
.component-tree {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tree-children {
  margin-left: 24px;
  padding-left: 16px;
  border-left: 2px dashed #e8e8e8;
}
</style>