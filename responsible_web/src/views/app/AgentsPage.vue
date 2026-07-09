<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useAgentsStore } from '@/stores/agents'
import { useToast }        from '@/composables/useToast'

const store = useAgentsStore()
const toast = useToast()

// ── Modales ──────────────────────────────────────────────────────────────
const showCreate        = ref(false)
const showEdit          = ref(false)
const showPerformance   = ref(false)
const showDeleteConfirm = ref(false)
const showCredentials   = ref(false)   
const targetAgent       = ref(null)
const newCredentials    = ref(null)    
const submitting        = ref(false)
const copied            = ref(false)

// ── Formulaire ───────────────────────────────────────────────────────────
const form   = reactive({ firstname: '', lastname: '', phone: '', email: '' })
const avatar = ref(null)
const errors = ref({})

function resetForm() {
  Object.assign(form, { firstname: '', lastname: '', phone: '', email: '' })
  avatar.value = null
  errors.value = {}
}

function openCreate() { resetForm(); showCreate.value = true }

function openEdit(agent) {
  targetAgent.value = agent
  Object.assign(form, { firstname: agent.firstname, lastname: agent.lastname, phone: agent.phone, email: agent.email || '' })
  avatar.value = null
  errors.value = {}
  showEdit.value = true
}

async function openPerformance(agent) {
  await store.fetchOne(agent.id)
  showPerformance.value = true
}

function openDelete(agent) {
  targetAgent.value = agent
  showDeleteConfirm.value = true
}

// ── Actions ──────────────────────────────────────────────────────────────
async function submitCreate() {
  submitting.value = true
  errors.value = {}
  try {
    const fd = new FormData()
    Object.entries(form).forEach(([k, v]) => v && fd.append(k, v))
    if (avatar.value) fd.append('avatar', avatar.value)
    const res = await store.create(fd)
    showCreate.value   = false
    newCredentials.value = res.data?.credentials || null
    copied.value       = false
    showCredentials.value = true
    store.setPage(1)
    await store.fetchAll()
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la création.')
  } finally { submitting.value = false }
}

function copyCredentials() {
  if (!newCredentials.value) return
  const c = newCredentials.value
  const text = `Nom : ${c.full_name}\nEmail : ${c.email}\nTéléphone : ${c.phone}\nMot de passe : ${c.temp_password}`
  navigator.clipboard.writeText(text).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 2500)
  })
}

async function submitEdit() {
  submitting.value = true
  errors.value = {}
  try {
    const fd = new FormData()
    fd.append('_method', 'PUT')
    Object.entries(form).forEach(([k, v]) => v && fd.append(k, v))
    if (avatar.value) fd.append('avatar', avatar.value)
    const res = await store.update(targetAgent.value.id, fd)
    toast.success(res.message || 'Agent mis à jour.')
    showEdit.value = false
    await store.fetchAll()
  } catch (err) {
    if (err.response?.status === 422) errors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la mise à jour.')
  } finally { submitting.value = false }
}

async function handleToggle(agent) {
  try {
    const res = await store.toggleStatus(agent.id)
    toast.success(res.message)
  } catch { toast.error('Impossible de changer le statut.') }
}

async function confirmDelete() {
  try {
    await store.destroy(targetAgent.value.id)
    toast.success('Agent supprimé.')
    showDeleteConfirm.value = false
  } catch { toast.error('Impossible de supprimer cet agent.') }
}

function formatAmount(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' F'
}

onMounted(() => store.fetchAll())
</script>

<template>
  <div class="space-y-6">

    <!-- Titre + action -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Agents collecteurs</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ store.total }} agent{{ store.total > 1 ? 's' : '' }} enregistré{{ store.total > 1 ? 's' : '' }}</p>
      </div>
      <button class="btn-primary cursor-pointer" @click="openCreate">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel agent
      </button>
    </div>

    <!-- Cards Grid -->
    <div class="space-y-6">
      <div v-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="card p-6">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 rounded-full bg-gray-100 animate-pulse"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-100 rounded w-3/4 animate-pulse"></div>
              <div class="h-3 bg-gray-100 rounded w-1/2 animate-pulse"></div>
            </div>
          </div>
          <div class="space-y-3">
            <div class="h-3 bg-gray-100 rounded animate-pulse"></div>
            <div class="h-3 bg-gray-100 rounded w-5/6 animate-pulse"></div>
          </div>
          <div class="mt-4 h-10 bg-gray-100 rounded-lg animate-pulse"></div>
        </div>
      </div>

      <div v-else-if="!store.agents.length" class="card flex flex-col items-center justify-center py-16 text-gray-300">
        <svg class="w-16 h-16 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/></svg>
        <p class="text-base">Aucun agent enregistré</p>
        <button class="btn-primary mt-5 text-sm cursor-pointer" @click="openCreate">Ajouter un agent</button>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="agent in store.agents" :key="agent.id" class="card group hover:shadow-lg transition-shadow duration-300">
          <!-- Card Header: Avatar + Name + Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
              <img v-if="agent.avatar_url" :src="agent.avatar_url" class="w-14 h-14 rounded-full object-cover flex-shrink-0" />
              <div v-else class="w-14 h-14 rounded-full bg-primary-100 text-primary-700 text-lg font-semibold flex items-center justify-center flex-shrink-0">
                {{ agent.full_name?.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate">{{ agent.full_name }}</p>
                <p class="text-sm text-gray-500 truncate">{{ agent.email || '—' }}</p>
              </div>
            </div>
            <button
              :class="agent.status === 'active' ? 'badge-green' : 'badge-red'"
              @click="handleToggle(agent)"
              title="Cliquer pour changer"
              class="shrink-0 cursor-pointer"
            >
              {{ agent.status === 'active' ? 'Actif' : 'Suspendu' }}
            </button>
          </div>

          <!-- Card Body: Contact Info & Stats -->
          <div class="space-y-3 mb-4">
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
              <span class="text-sm">{{ agent.phone }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600">
              <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
              <span class="text-sm">
                <span class="font-semibold text-gray-900">{{ agent.total_contributions ?? '—' }}</span> cotisations
              </span>
            </div>
          </div>

          <!-- Card Actions -->
          <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
            <button class="flex-1 py-2.5 text-sm font-medium text-gray-700 cursor-pointer hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors flex items-center justify-center gap-2" title="Performance" @click="openPerformance(agent)">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
              Performance
            </button>
            <button class="py-2.5 px-3 text-sm font-medium text-gray-700 cursor-pointer hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier" @click="openEdit(agent)">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button class="py-2.5 px-3 text-sm font-medium text-gray-700 cursor-pointer hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer" @click="openDelete(agent)">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="store.lastPage > 1" class="flex items-center justify-between px-4 py-3 card">
        <p class="text-xs text-gray-400">Page {{ store.currentPage }} / {{ store.lastPage }}</p>
        <div class="flex gap-1">
          <button :disabled="store.currentPage === 1" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40" @click="store.setPage(store.currentPage - 1); store.fetchAll()">←</button>
          <button :disabled="store.currentPage === store.lastPage" class="btn-secondary text-xs px-3 py-1.5 disabled:opacity-40" @click="store.setPage(store.currentPage + 1); store.fetchAll()">→</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL CRÉATION ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showCreate = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Nouvel agent</h2>
            <button @click="showCreate = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitCreate" class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Prénom <span class="text-red-500">*</span></label>
                <input v-model="form.firstname" class="form-input" placeholder="prenom" required />
                <p v-if="errors.firstname" class="text-red-500 text-xs mt-1">{{ errors.firstname[0] }}</p>
              </div>
              <div>
                <label class="form-label">Nom <span class="text-red-500">*</span></label>
                <input v-model="form.lastname" class="form-input" placeholder="nom de famille" required />
                <p v-if="errors.lastname" class="text-red-500 text-xs mt-1">{{ errors.lastname[0] }}</p>
              </div>
            </div>
            <div>
              <label class="form-label">Téléphone <span class="text-red-500">*</span></label>
              <input v-model="form.phone" class="form-input" placeholder="numero de téléphone" required />
              <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
            </div>
            <div>
              <label class="form-label">Email <span class="text-red-500">*</span></label>
              <input v-model="form.email" type="email" class="form-input" placeholder="email" required />
              <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
            </div>
            <div>
              <label class="form-label">Photo d'identité <span class="text-red-500">*</span></label>
              <input type="file" accept="image/*" class="form-input" @change="e => avatar = e.target.files[0]" required />
              <p v-if="errors.avatar" class="text-red-500 text-xs mt-1">{{ errors.avatar[0] }}</p>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" class="btn-secondary flex-1 cursor-pointer" @click="showCreate = false">Annuler</button>
              <button type="submit" :disabled="submitting" class="btn-primary flex-1 cursor-pointer">
                {{ submitting ? 'Création...' : 'Créer l\'agent' }}
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
            <h2 class="font-semibold text-gray-900">Modifier l'agent</h2>
            <button @click="showEdit = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="submitEdit" class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">Prénom</label>
                <input v-model="form.firstname" class="form-input" />
              </div>
              <div>
                <label class="form-label">Nom</label>
                <input v-model="form.lastname" class="form-input" />
              </div>
            </div>
            <div>
              <label class="form-label">Téléphone</label>
              <input v-model="form.phone" class="form-input" />
              <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
            </div>
            <div>
              <label class="form-label">Email</label>
              <input v-model="form.email" type="email" class="form-input" />
            </div>
            <div>
              <label class="form-label">Nouvelle photo (optionnel)</label>
              <input type="file" accept="image/*" class="form-input" @change="e => avatar = e.target.files[0]" />
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

    <!-- ── MODAL PERFORMANCE ──────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showPerformance" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showPerformance = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Performance de l'agent</h2>
            <button @click="showPerformance = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div v-if="store.loading" class="p-6 space-y-3">
            <div v-for="i in 4" :key="i" class="h-10 bg-gray-50 rounded animate-pulse" />
          </div>
          <div v-else class="p-5 space-y-3">
            <div class="flex items-center gap-3 mb-4">
              <img v-if="store.selected?.avatar_url" :src="store.selected.avatar_url" class="w-12 h-12 rounded-full object-cover" />
              <div v-else class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 font-bold text-lg flex items-center justify-center">
                {{ store.selected?.full_name?.charAt(0) }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ store.selected?.full_name }}</p>
                <p class="text-xs text-gray-400">{{ store.selected?.phone }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ store.stats?.total_contributions ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Cotisations</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-2xl font-bold text-primary-600">{{ formatAmount(store.stats?.total_collected) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Total collecté</p>
              </div>
              <div class="bg-yellow-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-yellow-700">{{ formatAmount(store.stats?.pending_settlement) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">En attente</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500">Dernière activité</p>
                <p class="text-xs font-medium text-gray-700 mt-0.5">{{ store.stats?.last_activity || '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL SUPPRESSION ──────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDeleteConfirm = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <h2 class="font-semibold text-gray-900 mb-2">Supprimer l'agent ?</h2>
          <p class="text-sm text-gray-500 mb-5">Cette action est irréversible. L'agent <strong>{{ targetAgent?.full_name }}</strong> sera supprimé.</p>
          <div class="flex gap-3">
            <button class="btn-secondary flex-1 cursor-pointer" @click="showDeleteConfirm = false">Annuler</button>
            <button class="btn-danger flex-1 cursor-pointer" @click="confirmDelete">Supprimer</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ── MODAL IDENTIFIANTS ─────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showCredentials" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

          <!-- En-tête -->
          <div class="p-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div>
                <h2 class="font-semibold text-gray-900">Agent créé avec succès</h2>
                <p class="text-xs text-gray-400 mt-0.5">Communiquez ces identifiants à l'agent en main propre</p>
              </div>
            </div>
          </div>

          <!-- Corps -->
          <div class="p-5 space-y-3">

            <!-- Alerte sécurité -->
            <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
              <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              <p class="text-xs text-amber-700">Le mot de passe temporaire ne sera plus affiché après fermeture. Notez-le ou copiez-le maintenant.</p>
            </div>

            <!-- Informations -->
            <div class="bg-gray-50 rounded-xl divide-y divide-gray-100 overflow-hidden">
              <div class="flex items-center justify-between px-4 py-3">
                <span class="text-xs text-gray-400 uppercase tracking-wide font-medium">Nom</span>
                <span class="text-sm font-semibold text-gray-900">{{ newCredentials?.full_name }}</span>
              </div>
              <div class="flex items-center justify-between px-4 py-3">
                <span class="text-xs text-gray-400 uppercase tracking-wide font-medium">Email</span>
                <span class="text-sm text-gray-700">{{ newCredentials?.email }}</span>
              </div>
              <div class="flex items-center justify-between px-4 py-3">
                <span class="text-xs text-gray-400 uppercase tracking-wide font-medium">Téléphone</span>
                <span class="text-sm text-gray-700">{{ newCredentials?.phone }}</span>
              </div>
              <div class="flex items-center justify-between px-4 py-3 bg-primary-50">
                <span class="text-xs text-primary-600 uppercase tracking-wide font-medium">Mot de passe temp.</span>
                <span class="text-base font-bold text-primary-700 font-mono tracking-widest">{{ newCredentials?.temp_password }}</span>
              </div>
            </div>

            <!-- Bouton copier -->
            <button
              class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-dashed text-sm font-medium transition-colors cursor-pointer"
              :class="copied ? 'border-green-400 text-green-600 bg-green-50' : 'border-gray-200 text-gray-500 hover:border-primary-300 hover:text-primary-600'"
              @click="copyCredentials"
            >
              <svg v-if="!copied" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
              <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              {{ copied ? 'Copié !' : 'Copier tous les identifiants' }}
            </button>
          </div>

          <!-- Pied -->
          <div class="p-4 border-t border-gray-100">
            <button class="btn-primary w-full cursor-pointer" @click="showCredentials = false; newCredentials = null">
              J'ai bien noté les identifiants
            </button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
