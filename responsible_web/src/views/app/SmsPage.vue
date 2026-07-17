<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useSmsStore } from '@/stores/sms'
import { useToast }    from '@/composables/useToast'
import { useDateFormatter } from '@/composables/useDateFormatter'

const store = useSmsStore()
const toast = useToast()

const filters = reactive({ status: '', type: '' })

const showDetail = ref(false)

function applyFilters() {
  store.setPage(1)
  store.fetchAll(cleanFilters())
}

function cleanFilters() {
  const p = {}
  if (filters.status) p.status = filters.status
  if (filters.type)   p.type   = filters.type
  return p
}

async function handleSendReminders() {
  try {
    const res = await store.sendReminders()
    toast.success(res.message || 'Rappels envoyés avec succès.')
    await store.fetchAll(cleanFilters())
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erreur lors de l\'envoi des rappels.')
  }
}

async function openDetail(log) {
  await store.fetchOne(log.id)
  showDetail.value = true
}

function truncate(str, len = 80) {
  if (!str) return '—'
  return str.length > len ? str.slice(0, len) + '…' : str
}

const { formatDate } = useDateFormatter()

function formatPayload(payload) {
  try {
    if (typeof payload === 'string') return JSON.stringify(JSON.parse(payload), null, 2)
    return JSON.stringify(payload, null, 2)
  } catch { return payload || '—' }
}

onMounted(() => store.fetchAll())
</script>

<template>
  <div class="space-y-6">

    <!-- Titre -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900">SMS</h1>
        <p class="text-sm text-gray-400 mt-0.5">Journal des messages envoyés</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="flex flex-wrap gap-3">
      <div class="card p-4 flex items-center gap-3 flex-1 min-w-[140px]">
        <span class="badge-green text-base font-bold px-3 py-1">{{ store.stats?.sent ?? 0 }}</span>
        <p class="text-sm text-gray-500">Envoyés</p>
      </div>
      <div class="card p-4 flex items-center gap-3 flex-1 min-w-[140px]">
        <span class="badge-red text-base font-bold px-3 py-1">{{ store.stats?.failed ?? 0 }}</span>
        <p class="text-sm text-gray-500">Échoués</p>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card p-4 flex flex-wrap gap-3">
      <select v-model="filters.status" @change="applyFilters" class="form-input w-auto">
        <option value="">Tous les statuts</option>
        <option value="sent">Envoyé</option>
        <option value="failed">Échoué</option>
      </select>
      <select v-model="filters.type" @change="applyFilters" class="form-input w-auto">
        <option value="">Tous les types</option>
        <option value="confirmation">Confirmation</option>
        <option value="reminder">Rappel</option>
      </select>
      <button class="btn-secondary cursor-pointer" @click="filters.status = ''; filters.type = ''; applyFilters()">Réinitialiser</button>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 6" :key="i" class="h-12 bg-gray-50 rounded-lg animate-pulse" />
      </div>

      <div v-else-if="!store.logs.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        <p class="text-sm">Aucun SMS trouvé</p>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-xs text-gray-400 uppercase">
            <th class="px-4 py-3 text-left font-medium">Destinataire</th>
            <th class="px-4 py-3 text-center font-medium hidden sm:table-cell">Type</th>
            <th class="px-4 py-3 text-center font-medium">Statut</th>
            <th class="px-4 py-3 text-left font-medium hidden md:table-cell">Message</th>
            <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr
            v-for="log in store.logs" :key="log.id"
            class="hover:bg-gray-50/50 transition-colors cursor-pointer"
            @click="openDetail(log)"
          >
            <td class="px-4 py-3 text-gray-700 font-medium">{{ log.recipient }}</td>
            <td class="px-4 py-3 text-center hidden sm:table-cell">
              <span :class="log.type === 'confirmation' ? 'badge-blue' : 'badge-gray'">
                {{ log.type_label || (log.type === 'confirmation' ? 'Confirmation' : 'Rappel') }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="log.status === 'sent' ? 'badge-green' : 'badge-red'">
                {{ log.status_label || (log.status === 'sent' ? 'Envoyé' : 'Échoué') }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">{{ truncate(log.message) }}</td>
            <td class="px-4 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ formatDate(log.created_at, true) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400">Page {{ store.currentPage }} / {{ store.lastPage }}</p>
        <div class="flex gap-1">
          <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40 cursor-pointer"
            @click="store.setPage(store.currentPage - 1); store.fetchAll(cleanFilters())">←</button>
          <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40 cursor-pointer"
            @click="store.setPage(store.currentPage + 1); store.fetchAll(cleanFilters())">→</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL DÉTAIL SMS ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDetail = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Détail du SMS</h2>
            <button @click="showDetail = false" class="p-1 cursor-pointer hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="flex items-center gap-3">
              <span :class="store.selected?.status === 'sent' ? 'badge-green' : 'badge-red'">
                {{ store.selected?.status_label || store.selected?.status }}
              </span>
              <span :class="store.selected?.type === 'confirmation' ? 'badge-blue' : 'badge-gray'">
                {{ store.selected?.type_label || store.selected?.type }}
              </span>
              <span class="text-xs text-gray-400 ml-auto">{{ formatDate(store.selected?.created_at, true) }}</span>
            </div>
            <div>
              <p class="text-xs text-gray-400 mb-1">Destinataire</p>
              <p class="text-sm font-medium text-gray-900">{{ store.selected?.recipient }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 mb-1">Message</p>
              <div class="bg-gray-50 rounded-xl p-3 text-sm text-gray-700 whitespace-pre-wrap">{{ store.selected?.message }}</div>
            </div>
            <div v-if="store.selected?.response_payload">
              <p class="text-xs text-gray-400 mb-1">Réponse API</p>
              <pre class="bg-gray-900 text-green-400 rounded-xl p-3 text-xs overflow-x-auto max-h-40">{{ formatPayload(store.selected.response_payload) }}</pre>
            </div>
          </div>
          <div class="p-4 border-t border-gray-100">
            <button class="btn-secondary w-full cursor-pointer" @click="showDetail = false">Fermer</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
