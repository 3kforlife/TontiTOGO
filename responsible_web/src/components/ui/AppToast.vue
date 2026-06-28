<script setup>
import { useToast } from '@/composables/useToast'
const { toasts, remove } = useToast()
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2.5 w-[calc(100%-2rem)] max-w-80">
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          :class="[
            'flex items-start gap-3 p-4 rounded-2xl shadow-xl ring-1 ring-white/20 text-sm font-semibold cursor-pointer backdrop-blur-xl',
            t.type === 'success' ? 'bg-green-700/95 text-white' :
            t.type === 'error'   ? 'bg-red-700/95 text-white'   :
                                   'bg-gray-900/95 text-white',
          ]"
          @click="remove(t.id)"
        >
          <!-- icône -->
          <svg v-if="t.type === 'success'" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-else-if="t.type === 'error'" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          <svg v-else class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12" y2="16"/></svg>
          <span>{{ t.message }}</span>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active  { transition: all .25s ease; }
.toast-leave-active  { transition: all .2s ease; }
.toast-enter-from    { opacity: 0; transform: translateX(1rem); }
.toast-leave-to      { opacity: 0; transform: translateX(1rem); }
</style>
