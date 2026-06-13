<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useSettlementsStore } from '@/stores/settlements'
import { useToast }            from '@/composables/useToast'
import { useDateFormatter }     from '@/composables/useDateFormatter'
import { agentService }        from '@/services/agentService'

const store = useSettlementsStore()
const toast = useToast()

const activeTab   = ref('pending')
const histFilters = reactive({ agent_id: '', status: '' })
const submitting  = ref(false)
const agents      = ref([])

async function loadAgents() {
  try {
    const res  = await agentService.list({ per_page: 100 })
    agents.value = res.data.data.data || []
  } catch {}
}

// ── Validate modal ────────────────────────────────────────────────────────
const showValidate  = ref(false)
const targetAgent   = ref(null)
const validateForm  = reactive({ received_amount: '', notes: '' })
const validateErrors= ref({})

function openValidate(agent) {
  targetAgent.value = agent
  Object.assign(validateForm, { received_amount: agent.pending_amount || '', notes: '' })
  validateErrors.value = {}
  showValidate.value = true
}

async function submitValidate() {
  submitting.value = true
  validateErrors.value = {}
  try {
    const res = await store.validate({
      agent_id:        targetAgent.value.agent_id || targetAgent.value.id,
      date_settled:    store.pendingDate,
      received_amount: validateForm.received_amount,
      notes:           validateForm.notes,
    })
    toast.success(res.message || 'Versement validé avec succès.')
    showValidate.value = false
    await store.fetchPendingSummary()
  } catch (err) {
    if (err.response?.status === 422) validateErrors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la validation.')
  } finally { submitting.value = false }
}

function onDateChange(e) {
  store.fetchPendingSummary(e.target.value)
}

function applyHistFilters() {
  store.setPage(1)
  const params = {}
  if (histFilters.agent_id) params.agent_id = histFilters.agent_id
  if (histFilters.status)   params.status   = histFilters.status
  store.fetchAll(params)
}

function formatAmount(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' F'
}

const { formatDate } = useDateFormatter()

function discrepancyClass(expected, received) {
  const diff = (received || 0) - (expected || 0)
  if (diff === 0) return 'text-green-600 font-medium'
  return 'text-red-600 font-medium'
}

function discrepancyLabel(expected, received) {
  const diff = (received || 0) - (expected || 0)
  if (diff === 0) return '0 F'
  const sign = diff > 0 ? '+' : ''
  return sign + new Intl.NumberFormat('fr-FR').format(diff) + ' F'
}

onMounted(() => {
  loadAgents()
  store.fetchPendingSummary()
})

function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'history') store.fetchAll()
}
</script>

<template>
  <div class="space-y-6">

    <!-- Titre -->
    <div>
      <h1 class="text-xl font-bold text-gray-900">Versements</h1>
      <p class="text-sm text-gray-400 mt-0.5">Gestion des clôtures journalières</p>
    </div>

    <!-- Onglets -->
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
      <button
        @click="switchTab('pending')"
        :class="['px-4 py-1.5 rounded-lg text-sm font-medium transition-colors',
          activeTab === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
      >
        Clôture du jour
      </button>
      <button
        @click="switchTab('history')"
        :class="['px-4 py-1.5 rounded-lg text-sm font-medium transition-colors',
          activeTab === 'history' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
      >
        Historique
      </button>
    </div>

    <!-- ── TAB: CLÔTURE DU JOUR ────────────────────────────────────────── -->
    <template v-if="activeTab === 'pending'">
      <!-- Sélecteur de date -->
      <div class="card p-4 flex items-center gap-4">
        <label class="form-label mb-0">Date de clôture</label>
        <input type="date" :value="store.pendingDate" @change="onDateChange" class="form-input w-auto" />
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="card p-4 h-28 animate-pulse bg-gray-50" />
      </div>

      <!-- Aucun agent -->
      <div v-else-if="!store.pendingSummary?.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <p class="text-sm">Aucun versement en attente pour cette date</p>
      </div>

      <!-- Cartes agents -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="agent in store.pendingSummary" :key="agent.agent_id || agent.id" class="card p-4 space-y-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-sm">
              {{ (agent.agent_full_name || agent.full_name)?.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 truncate">{{ agent.agent_full_name || agent.full_name }}</p>
              <p class="text-xs text-gray-400">{{ agent.pending_count }} cotisation{{ agent.pending_count > 1 ? 's' : '' }}</p>
            </div>
            <span v-if="agent.already_settled" class="badge-green text-xs">Versé</span>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <p class="text-xl font-bold text-gray-900">{{ formatAmount(agent.pending_amount) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Montant attendu</p>
          </div>
          <button
            v-if="!agent.already_settled"
            class="btn-primary w-full text-sm"
            @click="openValidate(agent)"
          >
            Valider le versement
          </button>
          <div v-else class="text-center text-xs text-gray-400 py-1">Déjà clôturé</div>
        </div>
      </div>
    </template>

    <!-- ── TAB: HISTORIQUE ────────────────────────────────────────────── -->
    <template v-if="activeTab === 'history'">
      <!-- Filtres -->
      <div class="card p-4 flex flex-wrap gap-3">
        <select v-model="histFilters.agent_id" class="form-input min-w-[180px]">
          <option value="">Tous les agents</option>
          <option v-for="a in agents" :key="a.id" :value="a.id">
            {{ a.full_name }}
          </option>
        </select>
        <select v-model="histFilters.status" class="form-input w-auto">
          <option value="">Tous les statuts</option>
          <option value="validated">Validé</option>
          <option value="discrepancy">Écart</option>
        </select>
        <button class="btn-primary" @click="applyHistFilters">Filtrer</button>
      </div>

      <!-- Tableau historique -->
      <div class="card p-0 overflow-hidden">
        <div v-if="store.loading" class="p-6 space-y-3">
          <div v-for="i in 6" :key="i" class="h-12 bg-gray-50 rounded-lg animate-pulse" />
        </div>

        <div v-else-if="!store.settlements.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
          <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
          <p class="text-sm">Aucun versement dans l'historique</p>
        </div>

        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr class="text-xs text-gray-400 uppercase">
              <th class="px-4 py-3 text-left font-medium">Date</th>
              <th class="px-4 py-3 text-left font-medium hidden sm:table-cell">Agent</th>
              <th class="px-4 py-3 text-right font-medium hidden md:table-cell">Attendu</th>
              <th class="px-4 py-3 text-right font-medium hidden md:table-cell">Reçu</th>
              <th class="px-4 py-3 text-right font-medium hidden lg:table-cell">Écart</th>
              <th class="px-4 py-3 text-center font-medium">Statut</th>
              <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Validé par</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="s in store.settlements" :key="s.id" class="hover:bg-gray-50/50 transition-colors">
              <td class="px-4 py-3 text-gray-600">{{ formatDate(s.date_settled) }}</td>
              <td class="px-4 py-3 text-gray-900 hidden sm:table-cell">{{ s.agent_full_name || s.agent?.full_name }}</td>
              <td class="px-4 py-3 text-right text-gray-600 hidden md:table-cell">{{ formatAmount(s.expected_amount) }}</td>
              <td class="px-4 py-3 text-right font-medium text-gray-900 hidden md:table-cell">{{ formatAmount(s.received_amount) }}</td>
              <td class="px-4 py-3 text-right hidden lg:table-cell">
                <span :class="discrepancyClass(s.expected_amount, s.received_amount)">
                  {{ discrepancyLabel(s.expected_amount, s.received_amount) }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span :class="s.status === 'validated' ? 'badge-green' : 'badge-red'">
                  {{ s.status === 'validated' ? 'Validé' : 'Écart' }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ s.validated_by || '—' }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="store.lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
          <p class="text-xs text-gray-400">Page {{ store.currentPage }} / {{ store.lastPage }}</p>
          <div class="flex gap-1">
            <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
              @click="store.setPage(store.currentPage - 1); applyHistFilters()">←</button>
            <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
              @click="store.setPage(store.currentPage + 1); applyHistFilters()">→</button>
          </div>
        </div>
      </div>
    </template>

    <!-- ── MODAL VALIDATION ───────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showValidate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showValidate = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Valider le versement</h2>
            <button @click="showValidate = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <!-- Résumé agent -->
            <div class="bg-gray-50 rounded-xl p-4">
              <p class="text-sm font-semibold text-gray-900">{{ targetAgent?.agent_full_name || targetAgent?.full_name }}</p>
              <p class="text-xs text-gray-400 mt-1">Montant attendu :
                <span class="font-semibold text-gray-700">{{ formatAmount(targetAgent?.pending_amount) }}</span>
              </p>
            </div>
            <form @submit.prevent="submitValidate" class="space-y-4">
              <div>
                <label class="form-label">Montant reçu <span class="text-red-500">*</span></label>
                <input v-model="validateForm.received_amount" type="number" class="form-input" placeholder="0" required />
                <p v-if="validateErrors.received_amount" class="text-red-500 text-xs mt-1">{{ validateErrors.received_amount[0] }}</p>
              </div>
              <div>
                <label class="form-label">Notes</label>
                <textarea v-model="validateForm.notes" class="form-input min-h-[80px] resize-none" placeholder="Observations éventuelles..." />
              </div>
              <div class="flex gap-3 pt-2">
                <button type="button" class="btn-secondary flex-1" @click="showValidate = false">Annuler</button>
                <button type="submit" :disabled="submitting" class="btn-primary flex-1">
                  {{ submitting ? 'Validation...' : 'Confirmer' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
