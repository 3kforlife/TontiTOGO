<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

defineEmits(['toggle-sidebar'])

const authStore = useAuthStore()
const router    = useRouter()
</script>

<template>
  <header class="flex items-center justify-between h-[68px] px-4 sm:px-6 lg:px-8 bg-white/80 border-b border-gray-200/70 flex-shrink-0 backdrop-blur-xl">

    <!-- Burger menu mobile -->
    <button
      class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors lg:hidden"
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
      class="flex items-center gap-3 rounded-xl px-2 py-1.5 cursor-pointer hover:bg-gray-100/80 transition-colors group"
      @click="router.push({ name: 'profile' })"
    >
      <span class="text-sm font-semibold text-gray-700 group-hover:text-primary-700 transition-colors hidden sm:block">
        {{ authStore.fullName }}
      </span>

      <!-- Avatar -->
      <div class="relative">
        <img
          v-if="authStore.avatarUrl"
          :src="authStore.avatarUrl"
          :alt="authStore.fullName"
          class="w-9 h-9 rounded-xl object-cover ring-1 ring-gray-200 shadow-sm group-hover:ring-primary-300 transition"
        />
        <div
          v-else
          class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 font-bold text-sm flex items-center justify-center ring-1 ring-primary-200 shadow-sm group-hover:bg-primary-600 group-hover:text-white transition"
        >
          {{ authStore.fullName.charAt(0).toUpperCase() }}
        </div>
      </div>
    </button>
  </header>
</template>
