<script setup>
import { ref } from 'vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader  from '@/components/layout/AppHeader.vue'
import AppToast   from '@/components/ui/AppToast.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="flex h-screen bg-gradient-to-br from-gray-50 via-white to-primary-50/30 overflow-hidden">
    <AppSidebar v-model:open="sidebarOpen" />
    <div v-if="sidebarOpen" class="fixed inset-0 z-20 bg-gray-950/35 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="flex-1 overflow-y-auto px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-7">
        <RouterView />
      </main>
    </div>
  </div>
  <AppToast />
</template>
