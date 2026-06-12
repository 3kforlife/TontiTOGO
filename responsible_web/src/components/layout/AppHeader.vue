<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const router    = useRouter()
</script>

<template>
  <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-100 flex-shrink-0">

    <!-- Burger menu mobile -->
    <button
      class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden"
      @click="$emit('toggle-sidebar')"
    >
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <!-- Titre page courant (desktop) -->
    <div class="hidden lg:block" />

    <!-- Profil à droite -->
    <button
      class="flex items-center gap-2.5 group"
      @click="router.push({ name: 'profile' })"
    >
      <span class="text-sm font-medium text-gray-700 group-hover:text-primary-600 transition-colors hidden sm:block">
        {{ authStore.fullName }}
      </span>

      <!-- Avatar -->
      <div class="relative">
        <img
          v-if="authStore.avatarUrl"
          :src="authStore.avatarUrl"
          :alt="authStore.fullName"
          class="w-9 h-9 rounded-full object-cover ring-2 ring-gray-100 group-hover:ring-primary-200 transition"
        />
        <div
          v-else
          class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 font-semibold text-sm flex items-center justify-center ring-2 ring-gray-100 group-hover:ring-primary-200 transition"
        >
          {{ authStore.fullName.charAt(0).toUpperCase() }}
        </div>
      </div>
    </button>
  </header>
</template>
