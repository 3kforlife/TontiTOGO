<script setup>
defineProps({
  label: String,
  value: [String, Number],
  icon:  String,
  color: { type: String, default: 'green' },
})

const colors = {
  green:  'bg-green-50 text-green-600',
  blue:   'bg-blue-50 text-blue-600',
  yellow: 'bg-yellow-50 text-yellow-600',
  purple: 'bg-purple-50 text-purple-600',
  indigo: 'bg-indigo-50 text-indigo-600',
  cyan:   'bg-cyan-50 text-cyan-600',
  red:    'bg-red-50 text-red-600',
  gray:   'bg-gray-100 text-gray-500',
}
</script>

<template>
  <div class="card flex items-center gap-4 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200">
    <!-- Icône -->
    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 ring-1 ring-inset ring-black/5', colors[color]]">
      <!-- cash -->
      <svg v-if="icon === 'cash'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>
      </svg>
      <!-- trending -->
      <svg v-else-if="icon === 'trending'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
      </svg>
      <!-- clock -->
      <svg v-else-if="icon === 'clock'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
      <!-- users -->
      <svg v-else-if="icon === 'users'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="8" r="3"/><path d="M3 21v-2c0-1.657 1.343-3 3-3h6c1.657 0 3 1.343 3 3v2"/>
        <path d="M16 11c1.657 0 3-1.343 3-3s-1.343-3-3-3"/><path d="M22 21v-2c-.27-1.419-1.343-2.636-3-3"/>
      </svg>
      <!-- bank -->
      <svg v-else-if="icon === 'bank'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 10h18M3 6l9-3 9 3M5 10v8m4-8v8m4-8v8m4-8v8M3 18h18"/>
      </svg>
      <!-- agent -->
      <svg v-else-if="icon === 'agent'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/>
      </svg>
      <!-- sms-failed -->
      <svg v-else-if="icon === 'sms-failed'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        <line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>
      </svg>
      <!-- default -->
      <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/>
      </svg>
    </div>

    <!-- Valeur -->
    <div class="min-w-0">
      <p class="text-xs text-gray-500 font-semibold truncate">{{ label }}</p>
      <p class="text-2xl font-bold tracking-tight text-gray-950 mt-0.5">{{ value }}</p>
    </div>
  </div>
</template>
