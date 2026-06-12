<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useTontinesStore } from '@/stores/tontines'
import { useToast }         from '@/composables/useToast'
import { memberService }    from '@/services/memberService'

const store = useTontinesStore()
const toast = useToast()

// Liste des membres pour le select "Ajouter participant"
const members        = ref([])
const membersLoading = ref(false)

async function loadMembers() {
  membersLoading.value = true
  try {
    const res  = await memberService.list({ per_page: 200, status: 'active' })
    members.value = res.data.data.data || []
  } catch {
    toast.error('Impossible de charger la liste des membres.')
  } finally {
    membersLoading.value = false
  }
}

// ── Filtres ───────────────────────────────────────────────────────────────
const activeTab = ref('all') 

function switchTab(tab) {
  activeTab.value = tab
  store.setPage(1)
  store.fetchAll(tabParams())
}

function tabParams() {
  return activeTab.value !== 'all' ? { status: activeTab.value } : {}
}

// ── Modales ───────────────────────────────────────────────────────────────
const showCreate        = ref(false)
const showEdit          = ref(false)
const showDetail        = ref(false)
const showDeleteConfirm = ref(false)
const showAddParticipant= ref(false)
const targetTontine     = ref(null)
const submitting        = ref(false)

const form = reactive({
  name: '', minimum_amount: '', frequency: 'monthly', start_date: '', end_date: '', status: 'active'
})
const participantForm = reactive({ member_id: '', chosen_amount: '', joined_at: '' })
const errors = ref({})
const participantErrors = ref({})

function resetForm() {
  Object.assign(form, { name: '', minimum_amount: '', frequency: 'monthly', start_date: '', end_date: '', status: 'active' })
  errors.value = {}
}

function resetParticipantForm() {
  Object.assign(participantForm, { member_id: '', chosen_amount: '', joined_at: '' })
  participantErrors.value = {}
}

function openCreate() { resetForm(); showCreate.value = true }

function openEdit(tontine) {
  targetTontine.value = tontine
  Object.assign(form, {
    name:           tontine.name           || '',
    minimum_amount: tontine.minimum_amount || '',
    frequency:      tontine.frequency      || 'monthly',
    start_date:     tontine.start_date     || '',
    end_date:       tontine.end_date       || '',
    status:         tontine.status         || 'active',
  })
  errors.value = {}
  showEdit.value = true
}

async function openDetail(tontine) {
  targetTontine.value = tontine
  await store.fetchOne(tontine.id)
  showDetail.value = true
}

function openDelete(tontine) {
  targetTontine.value = tontine
  showDeleteConfirm.value = true
}

function openAddParticipant() {
  resetParticipantForm()
  if (!members.value.length) loadMembers()
  showAddParticipant.value = true
}

// ── Actions ───────────────────────────────────────────────────────────────
async function submitCreate() {
  submitting.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (!payload.end_date) delete payload.end_date
    const res = await store.create(payload)
    toast.success(res.message || 'Tontine créée avec succès.')
    showCreate.value = false
    store.setPage(1)
    await store.fetchAll(tabParams())
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la création.')
  } finally { submitting.value = false }
}

async function submitEdit() {
  submitting.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (!payload.end_date) delete payload.end_date
    const res = await store.update(targetTontine.value.id, payload)
    toast.success(res.message || 'Tontine mise à jour.')
    showEdit.value = false
    await store.fetchAll(tabParams())
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la mise à jour.')
  } finally { submitting.value = false }
}

async function confirmDelete() {
  try {
    await store.destroy(targetTontine.value.id)
    toast.success('Tontine supprimée.')
    showDeleteConfirm.value = false
  } catch { toast.error('Impossible de supprimer cette tontine.') }
}

async function submitAddParticipant() {
  submitting.value = true
  participantErrors.value = {}
  try {
    const payload = {
      ...participantForm,
      member_id: Number(participantForm.member_id)
    }
    const res = await store.addParticipant(store.selected.id, payload)
    toast.success(res.message || 'Participant ajouté.')
    showAddParticipant.value = false
    await store.fetchOne(store.selected.id)
  } catch (err) {
    if (err.response?.status === 422) participantErrors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de l\'ajout.')
  } finally { submitting.value = false }
}

async function removeParticipant(participant) {
  if (!confirm(`Retirer ${participant.member_full_name || participant.full_name} de cette tontine ?`)) return
  try {
    await store.removeParticipant(store.selected.id, participant.id)
    toast.success('Participant retiré.')
    await store.fetchOne(store.selected.id)
  } catch { toast.error('Impossible de retirer ce participant.') }
}

function formatAmount(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' F'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR')
}

const tabs = [
  { key: 'all',    label: 'Toutes' },
  { key: 'active', label: 'Active' },
  { key: 'closed', label: 'Clôturée' },
]

const frequencyLabels = { daily: 'Journalier', weekly: 'Hebdomadaire', monthly: 'Mensuel' }

onMounted(() => {
  store.fetchAll()
  loadMembers()
})
</script>

<template>
  <div class="space-y-6">

    <!-- Titre + action -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Tontines</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ store.total }} tontine{{ store.total > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary" @click="openCreate">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle tontine
      </button>
    </div>

    <!-- Onglets statut -->
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
      <button
        v-for="tab in tabs" :key="tab.key"
        @click="switchTab(tab.key)"
        :class="['px-4 py-1.5 rounded-lg text-sm font-medium transition-colors',
          activeTab === tab.key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 6" :key="i" class="h-12 bg-gray-50 rounded-lg animate-pulse" />
      </div>

      <div v-else-if="!store.tontines.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        <p class="text-sm">Aucune tontine enregistrée</p>
        <button class="btn-primary mt-4 text-xs" @click="openCreate">Créer une tontine</button>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-xs text-gray-400 uppercase">
            <th class="px-4 py-3 text-left font-medium">Nom</th>
            <th class="px-4 py-3 text-left font-medium hidden sm:table-cell">Fréquence</th>
            <th class="px-4 py-3 text-right font-medium hidden md:table-cell">Montant min.</th>
            <th class="px-4 py-3 text-center font-medium hidden md:table-cell">Participants</th>
            <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Début</th>
            <th class="px-4 py-3 text-center font-medium">Statut</th>
            <th class="px-4 py-3 text-right font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="t in store.tontines" :key="t.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="px-4 py-3 font-medium text-gray-900">{{ t.name }}</td>
            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ t.frequency_label || frequencyLabels[t.frequency] }}</td>
            <td class="px-4 py-3 text-right text-gray-600 hidden md:table-cell">{{ formatAmount(t.minimum_amount) }}</td>
            <td class="px-4 py-3 text-center hidden md:table-cell">
              <span class="text-gray-700 font-medium">{{ t.active_participants_count ?? 0 }}</span>
              <span class="text-gray-400"> / {{ t.participants_count ?? 0 }}</span>
            </td>
            <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(t.start_date) }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="t.status === 'active' ? 'badge-green' : t.status === 'closed' ? 'badge-red' : 'badge-gray'">
                {{ t.status === 'active' ? 'Active' : t.status === 'closed' ? 'Clôturée' : 'Archivée' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700" title="Détails" @click="openDetail(t)">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </button>
                <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-600" title="Modifier" @click="openEdit(t)">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-red-600" title="Supprimer" @click="openDelete(t)">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400">Page {{ store.currentPage }} / {{ store.lastPage }}</p>
        <div class="flex gap-1">
          <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
            @click="store.setPage(store.currentPage - 1); store.fetchAll(tabParams())">←</button>
          <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40"
            @click="store.setPage(store.currentPage + 1); store.fetchAll(tabParams())">→</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL CRÉATION ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCreate = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Nouvelle tontine</h2>
            <button @click="showCreate = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-5 space-y-4">
            <div>
              <label class="form-label">Nom <span class="text-red-500">*</span></label>
              <input v-model="form.name" class="form-input" placeholder="nom de la tontine" required />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
            </div>
            <div>
              <label class="form-label">Montant minimum <span class="text-red-500">*</span></label>
              <input v-model="form.minimum_amount" type="number" class="form-input" placeholder="montant minimum" required />
              <p v-if="errors.minimum_amount" class="text-red-500 text-xs mt-1">{{ errors.minimum_amount[0] }}</p>
            </div>
            <div>
              <label class="form-label">Fréquence <span class="text-red-500">*</span></label>
              <select v-model="form.frequency" class="form-input">
                <option value="daily">Journalier</option>
                <option value="weekly">Hebdomadaire</option>
                <option value="monthly">Mensuel</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Date de début <span class="text-red-500">*</span></label>
                <input v-model="form.start_date" type="date" class="form-input" required />
                <p v-if="errors.start_date" class="text-red-500 text-xs mt-1">{{ errors.start_date[0] }}</p>
              </div>
              <div>
                <label class="form-label">Date de fin</label>
                <input v-model="form.end_date" type="date" class="form-input" />
              </div>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1" @click="showCreate = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1">
                {{ submitting ? 'Création...' : 'Créer la tontine' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL ÉDITION ──────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showEdit = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Modifier la tontine</h2>
            <button @click="showEdit = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitEdit" class="p-5 space-y-4">
            <div>
              <label class="form-label">Nom</label>
              <input v-model="form.name" class="form-input" />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
            </div>
            <div>
              <label class="form-label">Montant minimum </label>
              <input v-model="form.minimum_amount" type="number" class="form-input" />
              <p v-if="errors.minimum_amount" class="text-red-500 text-xs mt-1">{{ errors.minimum_amount[0] }}</p>
            </div>
            <div>
              <label class="form-label">Fréquence</label>
              <select v-model="form.frequency" class="form-input">
                <option value="daily">Journalier</option>
                <option value="weekly">Hebdomadaire</option>
                <option value="monthly">Mensuel</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Date de début</label>
                <input v-model="form.start_date" type="date" class="form-input" />
              </div>
              <div>
                <label class="form-label">Date de fin</label>
                <input v-model="form.end_date" type="date" class="form-input" />
              </div>
            </div>
            <div>
              <label class="form-label">Statut</label>
              <select v-model="form.status" class="form-input">
                <option value="active">Active</option>
                <option value="closed">Clôturée</option>
                <option value="archived">Archivée</option>
              </select>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1" @click="showEdit = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1">
                {{ submitting ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL DÉTAIL ───────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDetail = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between p-5 border-b border-gray-100 flex-shrink-0">
            <h2 class="font-semibold text-gray-900">{{ store.selected?.name }}</h2>
            <button @click="showDetail = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div v-if="store.loading" class="p-6 space-y-3">
            <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 rounded animate-pulse" />
          </div>
          <div v-else class="overflow-y-auto p-5 space-y-5">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-gray-900">{{ formatAmount(store.selected?.total_collected) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Total collecté</p>
              </div>
              <div class="bg-green-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-green-700">{{ store.selected?.active_members ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Membres actifs</p>
              </div>
              <div class="bg-yellow-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-yellow-700">{{ formatAmount(store.selected?.pending_settlement) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">En attente</p>
              </div>
            </div>

            <!-- Participants -->
            <div>
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Participants</h3>
                <button class="btn-primary text-xs" @click="openAddParticipant">
                  <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  Ajouter participant
                </button>
              </div>
              <div v-if="!store.selected?.participants?.length" class="text-center py-6 text-gray-300 text-sm">
                Aucun participant
              </div>
              <table v-else class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr class="text-gray-400 uppercase">
                    <th class="px-3 py-2 text-left font-medium">Membre</th>
                    <th class="px-3 py-2 text-left font-medium">Téléphone</th>
                    <th class="px-3 py-2 text-right font-medium">Montant</th>
                    <th class="px-3 py-2 text-center font-medium">Statut</th>
                    <th class="px-3 py-2 text-right font-medium">Total payé</th>
                    <th class="px-3 py-2"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="p in store.selected.participants" :key="p.id" class="hover:bg-gray-50/50">
                    <td class="px-3 py-2">
                      <p class="font-medium text-gray-800">{{ p.member_full_name || p.full_name }}</p>
                      <p class="text-gray-400">{{ formatDate(p.joined_at) }}</p>
                    </td>
                    <td class="px-3 py-2 text-gray-500">{{ p.phone || '—' }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">{{ formatAmount(p.chosen_amount) }}</td>
                    <td class="px-3 py-2 text-center">
                      <span :class="p.status === 'active' ? 'badge-green' : p.status === 'suspended' ? 'badge-red' : 'badge-yellow'">
                        {{ p.status }}
                      </span>
                    </td>
                    <td class="px-3 py-2 text-right font-medium text-gray-900">{{ formatAmount(p.total_paid) }}</td>
                    <td class="px-3 py-2 text-right">
                      <button class="p-1 hover:bg-red-50 text-gray-300 hover:text-red-500 rounded" title="Retirer" @click="removeParticipant(p)">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="p-4 border-t border-gray-100 flex-shrink-0">
            <button class="btn-secondary w-full" @click="showDetail = false">Fermer</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL AJOUTER PARTICIPANT ──────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showAddParticipant" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" @click.self="showAddParticipant = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Ajouter un participant</h2>
            <button @click="showAddParticipant = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitAddParticipant" class="p-5 space-y-4">
            <div>
              <label class="form-label">Membre <span class="text-red-500">*</span></label>
              <select v-model="participantForm.member_id" class="form-input" :disabled="membersLoading">
                <option value="">
                  {{ membersLoading ? 'Chargement...' : 'Sélectionner un membre' }}
                </option>
                <option v-for="m in members" :key="m.id" :value="m.id">
                  {{ m.full_name }} — {{ m.member_code }}
                </option>
              </select>
              <p v-if="participantErrors.member_id" class="text-red-500 text-xs mt-1">{{ participantErrors.member_id[0] }}</p>
            </div>
            <div>
              <label class="form-label">Montant choisi <span class="text-red-500">*</span></label>
              <input v-model="participantForm.chosen_amount" type="number" class="form-input" placeholder="5000" required />
              <p v-if="participantErrors.chosen_amount" class="text-red-500 text-xs mt-1">{{ participantErrors.chosen_amount[0] }}</p>
            </div>
            <div>
              <label class="form-label">Date d'adhésion <span class="text-red-500">*</span></label>
              <input v-model="participantForm.joined_at" type="date" class="form-input" required />
              <p v-if="participantErrors.joined_at" class="text-red-500 text-xs mt-1">{{ participantErrors.joined_at[0] }}</p>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1" @click="showAddParticipant = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1">
                {{ submitting ? 'Ajout...' : 'Ajouter' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL SUPPRESSION ──────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDeleteConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <h2 class="font-semibold text-gray-900 mb-2">Supprimer la tontine ?</h2>
          <p class="text-sm text-gray-500 mb-5">Cette action est irréversible. La tontine <strong>{{ targetTontine?.name }}</strong> sera supprimée.</p>
          <div class="flex gap-3">
            <button class="btn-secondary flex-1" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-danger flex-1" @click="confirmDelete">Supprimer</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
