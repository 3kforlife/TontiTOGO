<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({ open: Boolean })
defineEmits(['update:open'])

const route     = useRoute()
const authStore = useAuthStore()

const navItems = [
  { name: 'Tableau de bord',    routeName: 'dashboard',     icon: 'grid' },
  { name: 'Agents',             routeName: 'agents',        icon: 'users' },
  { name: 'Membres',            routeName: 'members',       icon: 'person' },
  { name: 'Tontines',           routeName: 'tontines',      icon: 'bank' },
  { name: 'Cotisations',        routeName: 'contributions', icon: 'cash' },
  { name: 'Versements',         routeName: 'settlements',   icon: 'settlement' },
  { name: 'Carte GPS',          routeName: 'map',           icon: 'map' },
  { name: 'SMS',                routeName: 'sms',           icon: 'chat' },
  { name: 'Paramètres',         routeName: 'settings',      icon: 'settings' },
]

function isActive(routeName) {
  return route.name === routeName
}
</script>

<template>
  <!-- Sidebar desktop (fixe) + mobile (drawer) -->
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-30 flex flex-col bg-white border-r border-gray-100 transition-all duration-300 ease-in-out',
      'w-64 lg:w-64 lg:translate-x-0 lg:static lg:z-auto',
      open ? 'translate-x-0 shadow-2xl' : '-translate-x-full',
    ]"
  >
    <!-- Logo + Nom organisation -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-100">
      <!-- Avatar organisation (initial) -->
      <div class="w-9 h-9 rounded-xl bg-primary-600 flex items-center justify-center text-white font-bold text-base flex-shrink-0">
        {{ authStore.organizationName.charAt(0).toUpperCase() || 'T' }}
      </div>
      <div class="overflow-hidden">
        <p class="text-sm font-semibold text-gray-800 truncate leading-tight">
          {{ authStore.organizationName || 'Mon Organisation' }}
        </p>
        <p class="text-xs text-gray-400">Responsable</p>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
      <RouterLink
        v-for="item in navItems"
        :key="item.routeName"
        :to="{ name: item.routeName }"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150',
          isActive(item.routeName)
            ? 'bg-primary-50 text-primary-700'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
        ]"
      >
        <!-- Icônes SVG inline -->
        <span class="w-5 h-5 flex-shrink-0 opacity-80">
          <!-- grid -->
          <svg v-if="item.icon === 'grid'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
          </svg>
          <!-- users -->
          <svg v-else-if="item.icon === 'users'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M16 11c1.657 0 3-1.343 3-3s-1.343-3-3-3"/><path d="M19 21v-2c0-1.657-1.343-3-3-3h-1"/>
            <circle cx="9" cy="8" r="3"/><path d="M3 21v-2c0-1.657 1.343-3 3-3h6c1.657 0 3 1.343 3 3v2"/>
          </svg>
          <!-- person -->
          <svg v-else-if="item.icon === 'person'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/>
          </svg>
          <!-- bank -->
          <svg v-else-if="item.icon === 'bank'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M3 10h18M3 6l9-3 9 3M5 10v8m4-8v8m4-8v8m4-8v8M3 18h18"/>
          </svg>
          <!-- cash -->
          <svg v-else-if="item.icon === 'cash'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <rect x="2" y="6" width="20" height="12" rx="2"/>
            <circle cx="12" cy="12" r="3"/><path d="M2 10h2M20 10h2M2 14h2M20 14h2"/>
          </svg>
          <!-- settlement (clôture de caisse) -->
          <svg v-else-if="item.icon === 'settlement'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M9 11l3 3L22 4"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
          </svg>
          <!-- map -->
          <svg v-else-if="item.icon === 'map'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M9 3L3 6v15l6-3 6 3 6-3V3l-6 3-6-3z"/><path d="M9 3v15M15 6v15"/>
          </svg>
          <!-- chat -->
          <svg v-else-if="item.icon === 'chat'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
          </svg>
          <!-- settings -->
          <svg v-else-if="item.icon === 'settings'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
          </svg>
        </span>
        <span>{{ item.name }}</span>
      </RouterLink>
    </nav>

    <!-- Bouton Déconnexion -->
    <div class="p-3 border-t border-gray-100">
      <button
        class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium
               bg-primary-600 text-white hover:bg-red-600 transition-colors duration-200"
        @click="authStore.logout().then(() => $router.replace({ name: 'login' }))"
      >
        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Déconnexion</span>
      </button>
    </div>
  </aside>
</template>
