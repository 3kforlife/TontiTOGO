<script setup>
import { ref, onMounted } from 'vue'
import { useContributionsStore } from '@/stores/contributions'
import { useToast }              from '@/composables/useToast'
import { useDateFormatter }       from '@/composables/useDateFormatter'
import { agentService }          from '@/services/agentService'
import { memberService }         from '@/services/memberService'
import { tontineService }        from '@/services/tontineService'

const store = useContributionsStore()
const toast = useToast()

// Listes pour les selects
const agents   = ref([])
const members  = ref([])
const tontines = ref([])

async function loadFilterOptions() {
  const [a, m, t] = await Promise.all([
    agentService.list({ per_page: 100 }),
    memberService.list({ per_page: 100 }),
    tontineService.list({ per_page: 100, status: 'active' }),
  ])
  agents.value   = a.data.data.data  || []
  members.value  = m.data.data.data  || []
  tontines.value = t.data.data.data  || []
}

function applyFilters() {
  store.setPage(1)
  store.fetchAll()
}

function resetFilters() {
  store.resetFilters()
  store.fetchAll()
}

async function handleExport(type) {
  try {
    await store.exportFile(type)
  } catch {
    toast.error("Erreur lors de l'export.")
  }
}

const { formatDate } = useDateFormatter()

function formatAmount(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' F'
}

onMounted(async () => {
  await loadFilterOptions()
  store.fetchAll()
})
</script>

<template>
  <div class="space-y-6">

    <!-- Titre -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Cotisations</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ store.total }} cotisation{{ store.total > 1 ? 's' : '' }}</p>
      </div>
      <div class="flex gap-2">
        <button class="btn-secondary flex items-center gap-2" :disabled="store.exporting" @click="handleExport('excel')">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Excel
        </button>
        <button class="btn-secondary flex items-center gap-2" :disabled="store.exporting" @click="handleExport('pdf')">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6M9 11h3"/></svg>
          PDF
        </button>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card p-4">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="flex flex-col gap-1">
          <label class="form-label">Date</label>
          <input
            type="date"
            :value="store.filters.date"
            @change="store.setFilters({ date: $event.target.value })"
            class="form-input w-auto"
          />
        </div>

        <div class="flex flex-col gap-1 min-w-[180px]">
          <label class="form-label">Agent</label>
          <select
            :value="store.filters.agent_id"
            @change="store.setFilters({ agent_id: $event.target.value })"
            class="form-input"
          >
            <option value="">Tous les agents</option>
            <option v-for="a in agents" :key="a.id" :value="a.id">
              {{ a.full_name }}
            </option>
          </select>
        </div>

        <div class="flex flex-col gap-1 min-w-[180px]">
          <label class="form-label">Membre</label>
          <select
            :value="store.filters.member_id"
            @change="store.setFilters({ member_id: $event.target.value })"
            class="form-input"
          >
            <option value="">Tous les membres</option>
            <option v-for="m in members" :key="m.id" :value="m.id">
              {{ m.full_name }}
            </option>
          </select>
        </div>

        <div class="flex flex-col gap-1 min-w-[180px]">
          <label class="form-label">Tontine</label>
          <select
            :value="store.filters.tontine_id"
            @change="store.setFilters({ tontine_id: $event.target.value })"
            class="form-input"
          >
            <option value="">Toutes les tontines</option>
            <option v-for="t in tontines" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>
        </div>

        <div class="flex items-end gap-2">
          <button class="btn-primary" @click="applyFilters">Filtrer</button>
          <button class="btn-secondary" @click="resetFilters">Réinitialiser</button>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 6" :key="i" class="h-12 bg-gray-50 rounded-lg animate-pulse" />
      </div>

      <div v-else-if="!store.contributions.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        <p class="text-sm">Aucune cotisation trouvée</p>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-xs text-gray-400 uppercase">
            <th class="px-4 py-3 text-left font-medium">Référence</th>
            <th class="px-4 py-3 text-left font-medium hidden sm:table-cell">Membre</th>
            <th class="px-4 py-3 text-left font-medium hidden md:table-cell">Tontine</th>
            <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Agent</th>
            <th class="px-4 py-3 text-right font-medium">Montant</th>
            <th class="px-4 py-3 text-center font-medium hidden md:table-cell">Règlement</th>
            <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="c in store.contributions" :key="c.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="px-4 py-3">
              <p class="font-mono text-xs text-gray-600">{{ c.reference }}</p>
            </td>
            <td class="px-4 py-3 hidden sm:table-cell">
              <p class="font-medium text-gray-900">{{ c.member_full_name || c.member?.full_name }}</p>
              <p class="text-xs text-gray-400">{{ c.member_code || c.member?.member_code }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ c.tontine_name || c.tontine?.name }}</td>
            <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">{{ c.agent_full_name || c.agent?.full_name }}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ formatAmount(c.amount) }}</td>
            <td class="px-4 py-3 text-center hidden md:table-cell">
              <span :class="c.settlement_label === 'Validé' || c.settlement_status === 'validated' ? 'badge-green' :
                           c.settlement_label === 'Écart' || c.settlement_status === 'discrepancy' ? 'badge-red' : 'badge-yellow'">
                {{ c.settlement_label || c.settlement_status || 'En attente' }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ formatDate(c.created_at, true) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400">Page {{ store.currentPage }} / {{ store.lastPage }}</p>
        <div class="flex gap-1">
          <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
            @click="store.setPage(store.currentPage - 1); store.fetchAll()">←</button>
          <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
            @click="store.setPage(store.currentPage + 1); store.fetchAll()">→</button>
        </div>
      </div>
    </div>

  </div>
</template>
