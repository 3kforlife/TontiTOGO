<script setup>
import { ref } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader  from '@/components/layout/AppHeader.vue'
import AppToast   from '@/components/ui/AppToast.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">
    <AppSidebar v-model:open="sidebarOpen" />
    <div v-if="sidebarOpen" class="fixed inset-0 z-20 bg-black/40 lg:hidden" @click="sidebarOpen = false" />
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="flex-1 overflow-y-auto p-6">
        <RouterView />
      </main>
    </div>
  </div>
  <AppToast />
</template>
