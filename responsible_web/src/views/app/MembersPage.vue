<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useMembersStore } from '@/stores/members'
import { useToast }        from '@/composables/useToast'
import { useDateFormatter } from '@/composables/useDateFormatter'

const store = useMembersStore()
const toast = useToast()

// ── Filtres ───────────────────────────────────────────────────────────────
const search       = ref('')
const statusFilter = ref('all')

function applyFilters() {
  store.setPage(1)
  const params = {}
  if (search.value)                    params.search = search.value
  if (statusFilter.value !== 'all')    params.status = statusFilter.value
  store.fetchAll(params)
}

function currentParams() {
  const params = {}
  if (search.value)                    params.search = search.value
  if (statusFilter.value !== 'all')    params.status = statusFilter.value
  return params
}

// ── Modales ───────────────────────────────────────────────────────────────
const showCreate        = ref(false)
const showEdit          = ref(false)
const showDetail        = ref(false)
const showDeleteConfirm = ref(false)
const targetMember      = ref(null)
const submitting        = ref(false)

const form   = reactive({ notebook_number: '', firstname: '', lastname: '', phone: '', gender: 'M', address: '' })
const errors = ref({})

function resetForm() {
  Object.assign(form, { notebook_number: '', firstname: '', lastname: '', phone: '', gender: 'M', address: '' })
  errors.value = {}
}

function openCreate() { resetForm(); showCreate.value = true }

function openEdit(member) {
  targetMember.value = member
  Object.assign(form, {
    notebook_number: member.notebook_number || '',
    firstname:       member.firstname       || '',
    lastname:        member.lastname        || '',
    phone:           member.phone           || '',
    gender:          member.gender          || 'M',
    address:         member.address         || '',
  })
  errors.value = {}
  showEdit.value = true
}

async function openDetail(member) {
  targetMember.value = member
  await store.fetchOne(member.id)
  showDetail.value = true
}

function openDelete(member) {
  targetMember.value = member
  showDeleteConfirm.value = true
}

// ── Actions ───────────────────────────────────────────────────────────────
async function submitCreate() {
  submitting.value = true
  errors.value = {}
  try {
    const res = await store.create({ ...form })
    toast.success(res.message || 'Membre créé avec succès.')
    showCreate.value = false
    store.setPage(1)
    await store.fetchAll(currentParams())
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la création.')
  } finally { submitting.value = false }
}

async function submitEdit() {
  submitting.value = true
  errors.value = {}
  try {
    const res = await store.update(targetMember.value.id, { ...form })
    toast.success(res.message || 'Membre mis à jour.')
    showEdit.value = false
    await store.fetchAll(currentParams())
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la mise à jour.')
  } finally { submitting.value = false }
}

async function handleToggle(member) {
  try {
    const res = await store.toggleStatus(member.id)
    toast.success(res.message)
  } catch { toast.error('Impossible de changer le statut.') }
}

async function confirmDelete() {
  try {
    await store.destroy(targetMember.value.id)
    toast.success('Membre supprimé.')
    showDeleteConfirm.value = false
  } catch { toast.error('Impossible de supprimer ce membre.') }
}

const { formatDate } = useDateFormatter()

onMounted(() => store.fetchAll())
</script>

<template>
  <div class="space-y-6">

    <!-- Titre + action -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Membres</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ store.total }} membre{{ store.total > 1 ? 's' : '' }} enregistré{{ store.total > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary cursor-pointer" @click="openCreate">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau membre
      </button>
    </div>

    <!-- Filtres -->
    <div class="card p-4 flex flex-wrap gap-3">
      <input
        v-model="search"
        @keyup.enter="applyFilters"
        class="form-input flex-1 min-w-[200px]"
        placeholder="Rechercher par nom, téléphone, carnet..."
      />
      <select v-model="statusFilter" @change="applyFilters" class="form-input w-auto">
        <option value="all">Tous les statuts</option>
        <option value="active">Actif</option>
        <option value="suspended">Suspendu</option>
      </select>
      <button class="btn-secondary cursor-pointer" @click="applyFilters">Filtrer</button>
    </div>

    <!-- Tableau -->
    <div class="card p-0 overflow-hidden">
      <div v-if="store.loading" class="p-6 space-y-3">
        <div v-for="i in 6" :key="i" class="h-12 bg-gray-50 rounded-lg animate-pulse" />
      </div>

      <div v-else-if="!store.members.length" class="flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M21 21v-2a4 4 0 00-3-3.87"/></svg>
        <p class="text-sm">Aucun membre enregistré</p>
        <button class="btn-primary mt-4 text-xs cursor-pointer" @click="openCreate">Ajouter un membre</button>
      </div>

      <table v-else class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr class="text-xs text-gray-400 uppercase">
            <th class="px-4 py-3 text-left font-medium">Code / Nom</th>
            <th class="px-4 py-3 text-left font-medium hidden sm:table-cell">Téléphone</th>
            <th class="px-4 py-3 text-center font-medium hidden md:table-cell">Carnet</th>
            <th class="px-4 py-3 text-center font-medium hidden lg:table-cell">Genre</th>
            <th class="px-4 py-3 text-center font-medium">Statut</th>
            <th class="px-4 py-3 text-left font-medium hidden lg:table-cell">Inscription</th>
            <th class="px-4 py-3 text-left font-medium hidden xl:table-cell">Créé par</th>
            <th class="px-4 py-3 text-right font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="member in store.members" :key="member.id" class="hover:bg-gray-50/50 transition-colors">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-900">{{ member.full_name }}</p>
              <p class="text-xs text-gray-400">{{ member.member_code }}</p>
            </td>
            <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ member.phone }}</td>
            <td class="px-4 py-3 text-center text-gray-500 hidden md:table-cell">{{ member.notebook_number || '—' }}</td>
            <td class="px-4 py-3 text-center hidden lg:table-cell">
              <span class="badge-blue">{{ member.gender_label || member.gender }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <button
                :class="member.status === 'active' ? 'badge-green cursor-pointer' : 'badge-red cursor-pointer' "
                @click="handleToggle(member)"
                title="Cliquer pour changer le statut"
              >
                {{ member.status === 'active' ? 'Actif' : 'Suspendu' }}
              </button>
            </td>
            <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">{{ formatDate(member.created_at) }}</td>
            <td class="px-4 py-3 text-gray-500 text-sm hidden xl:table-cell">
              {{ member.created_by_agent || 'Responsable' }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1">
                <button class="p-1.5 rounded-lg cursor-pointer hover:bg-gray-100 text-gray-400 hover:text-gray-700" title="Détails" @click="openDetail(member)">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </button>
                <button class="p-1.5 rounded-lg cursor-pointer hover:bg-gray-100 text-gray-400 hover:text-blue-600" title="Modifier" @click="openEdit(member)">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="p-1.5 rounded-lg cursor-pointer hover:bg-gray-100 text-gray-400 hover:text-red-600" title="Supprimer" @click="openDelete(member)">
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
          <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40 cursor-pointer"
            @click="store.setPage(store.currentPage - 1); store.fetchAll(currentParams())">←</button>
          <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40 cursor-pointer"
            @click="store.setPage(store.currentPage + 1); store.fetchAll(currentParams())">→</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL CRÉATION ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCreate = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Nouveau membre</h2>
            <button @click="showCreate = false" class="p-1 cursor-pointer hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-5 space-y-4">
            <div>
              <label class="form-label">Numéro de carnet <span class="text-red-500">*</span></label>
              <input v-model="form.notebook_number" class="form-input" placeholder="le numero de carnet du membre" required />
              <p v-if="errors.notebook_number" class="text-red-500 text-xs mt-1">{{ errors.notebook_number[0] }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Prénom <span class="text-red-500">*</span></label>
                <input v-model="form.firstname" class="form-input" placeholder="prenom" required />
                <p v-if="errors.firstname" class="text-red-500 text-xs mt-1">{{ errors.firstname[0] }}</p>
              </div>
              <div>
                <label class="form-label">Nom <span class="text-red-500">*</span></label>
                <input v-model="form.lastname" class="form-input" placeholder="nom" required />
                <p v-if="errors.lastname" class="text-red-500 text-xs mt-1">{{ errors.lastname[0] }}</p>
              </div>
            </div>
            <div>
              <label class="form-label">Téléphone <span class="text-red-500">*</span></label>
              <input v-model="form.phone" class="form-input" placeholder="numero de téléphone" required />
              <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
            </div>
            <div>
              <label class="form-label">Genre <span class="text-red-500">*</span></label>
              <select v-model="form.gender" class="form-input">
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
              </select>
            </div>
            <div>
              <label class="form-label">Adresse <span class="text-red-500">*</span></label>
              <input v-model="form.address" class="form-input" placeholder="adresse" required />
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1 cursor-pointer" @click="showCreate = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1 cursor-pointer">
                {{ submitting ? 'Création...' : 'Créer le membre' }}
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
            <h2 class="font-semibold text-gray-900">Modifier le membre</h2>
            <button @click="showEdit = false" class="p-1 cursor-pointer hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitEdit" class="p-5 space-y-4">
            <div>
              <label class="form-label">Numéro de carnet</label>
              <input v-model="form.notebook_number" class="form-input" />
              <p v-if="errors.notebook_number" class="text-red-500 text-xs mt-1">{{ errors.notebook_number[0] }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Prénom</label>
                <input v-model="form.firstname" class="form-input" />
                <p v-if="errors.firstname" class="text-red-500 text-xs mt-1">{{ errors.firstname[0] }}</p>
              </div>
              <div>
                <label class="form-label">Nom</label>
                <input v-model="form.lastname" class="form-input" />
                <p v-if="errors.lastname" class="text-red-500 text-xs mt-1">{{ errors.lastname[0] }}</p>
              </div>
            </div>
            <div>
              <label class="form-label">Téléphone</label>
              <input v-model="form.phone" class="form-input" />
              <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
            </div>
            <div>
              <label class="form-label">Genre</label>
              <select v-model="form.gender" class="form-input">
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
              </select>
            </div>
            <div>
              <label class="form-label">Adresse</label>
              <input v-model="form.address" class="form-input" />
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1 cursor-pointer" @click="showEdit = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1 cursor-pointer">
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
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between p-5 border-b border-gray-100 flex-shrink-0">
            <h2 class="font-semibold text-gray-900">Détails du membre</h2>
            <button @click="showDetail = false" class="p-1 cursor-pointer hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div v-if="store.loading" class="p-6 space-y-3">
            <div v-for="i in 4" :key="i" class="h-10 bg-gray-50 rounded animate-pulse" />
          </div>
          <div v-else class="overflow-y-auto p-5 space-y-5">
            <!-- Infos membre -->
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-0.5">Code membre</p>
                <p class="font-medium text-gray-900">{{ store.selected?.member_code }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-0.5">Nom complet</p>
                <p class="font-medium text-gray-900">{{ store.selected?.full_name }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-0.5">Téléphone</p>
                <p class="font-medium text-gray-900">{{ store.selected?.phone }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-400 mb-0.5">Genre</p>
                <p class="font-medium text-gray-900">{{ store.selected?.gender_label || store.selected?.gender }}</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                <p class="text-xs text-gray-400 mb-0.5">Adresse</p>
                <p class="font-medium text-gray-900">{{ store.selected?.address || '—' }}</p>
              </div>
            </div>

            <!-- Participations -->
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Participations aux tontines</h3>
              <div v-if="!store.selected?.participations?.length" class="text-center py-6 text-gray-300 text-sm">
                Aucune participation enregistrée
              </div>
              <table v-else class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr class="text-gray-400 uppercase">
                    <th class="px-3 py-2 text-left font-medium">Tontine</th>
                    <th class="px-3 py-2 text-right font-medium">Montant</th>
                    <th class="px-3 py-2 text-center font-medium">Statut</th>
                    <th class="px-3 py-2 text-right font-medium">Total payé</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="p in store.selected.participations" :key="p.id">
                    <td class="px-3 py-2 text-gray-700">{{ p.tontine_name || p.tontine?.name }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">{{ new Intl.NumberFormat('fr-FR').format(p.chosen_amount) }} F</td>
                    <td class="px-3 py-2 text-center">
                      <span :class="p.status === 'active' ? 'badge-green' : p.status === 'suspended' ? 'badge-red' : 'badge-yellow'">
                        {{ p.status }}
                      </span>
                    </td>
                    <td class="px-3 py-2 text-right font-medium text-gray-900">{{ new Intl.NumberFormat('fr-FR').format(p.total_paid || 0) }} F</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="p-4 border-t border-gray-100 flex-shrink-0">
            <button class="btn-secondary w-full cursor-pointer" @click="showDetail = false">Fermer</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL SUPPRESSION ──────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDeleteConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <h2 class="font-semibold text-gray-900 mb-2">Supprimer le membre ?</h2>
          <p class="text-sm text-gray-500 mb-5">Cette action est irréversible. Le membre <strong>{{ targetMember?.full_name }}</strong> sera supprimé.</p>
          <div class="flex gap-3">
            <button class="btn-secondary flex-1 cursor-pointer" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-danger flex-1 cursor-pointer" @click="confirmDelete">Supprimer</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
