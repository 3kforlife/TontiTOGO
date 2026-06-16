<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter }    from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { authService }  from '@/services/authService'
import { useToast }     from '@/composables/useToast'
import PasswordInput    from '../../components/PasswordInput.vue'

const authStore = useAuthStore()
const router    = useRouter()
const toast     = useToast()

// ── Section 1 — Informations personnelles ────────────────────────────────
const profileForm = reactive({
  firstname: '',
  lastname:  '',
  email:     '',
  phone:     '',
})
const avatarFile    = ref(null)
const avatarPreview = ref(null)
const profileErrors = ref({})
const savingProfile = ref(false)

// ── Section 2 — Mot de passe ──────────────────────────────────────────────
const pwForm = reactive({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})
const pwErrors      = ref({})
const savingPw      = ref(false)
const pwSuccess     = ref(false)

// ── Section 3 — Zone de danger ────────────────────────────────────────────
const showDeleteModal   = ref(false)
const deletePassword    = ref('')
const deletingAccount   = ref(false)
const deleteError       = ref('')

// ── Initiales avatar ──────────────────────────────────────────────────────
const initials = computed(() => {
  const user = authStore.user
  if (!user) return '?'
  const f = user.firstname?.charAt(0) || ''
  const l = user.lastname?.charAt(0)  || ''
  return (f + l).toUpperCase() || '?'
})

function syncProfileForm() {
  const user = authStore.user
  if (!user) return
  Object.assign(profileForm, {
    firstname: user.firstname || '',
    lastname:  user.lastname  || '',
    email:     user.email     || '',
    phone:     user.phone     || '',
  })
}

function onAvatarChange(e) {
  const file = e.target.files[0]
  if (!file) return
  avatarFile.value    = file
  avatarPreview.value = URL.createObjectURL(file)
}

async function saveProfile() {
  savingProfile.value = true
  profileErrors.value = {}
  try {
    const fd = new FormData()
    Object.entries(profileForm).forEach(([k, v]) => { if (v !== null && v !== undefined) fd.append(k, v) })
    if (avatarFile.value) fd.append('avatar', avatarFile.value)
    await authService.updateProfile(fd)
    await authStore.fetchMe()
    syncProfileForm()
    toast.success('Profil mis à jour avec succès.')
  } catch (err) {
    if (err.response?.status === 422) profileErrors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors de la mise à jour du profil.')
  } finally { savingProfile.value = false }
}

async function savePassword() {
  savingPw.value = true
  pwErrors.value = {}
  pwSuccess.value = false
  try {
    await authService.changePassword({ ...pwForm })
    Object.assign(pwForm, { current_password: '', password: '', password_confirmation: '' })
    pwSuccess.value = true
    toast.success('Mot de passe changé avec succès.')
  } catch (err) {
    if (err.response?.status === 422) pwErrors.value = err.response.data.errors || {}
    else toast.error(err.response?.data?.message || 'Erreur lors du changement de mot de passe.')
  } finally { savingPw.value = false }
}

async function confirmDeleteAccount() {
  deletingAccount.value = true
  deleteError.value     = ''
  try {
    await authService.deleteAccount({ password: deletePassword.value })
    authStore.clearSession()
    router.push('/')
  } catch (err) {
    deleteError.value = err.response?.data?.message || 'Mot de passe incorrect ou erreur serveur.'
  } finally { deletingAccount.value = false }
}

onMounted(() => syncProfileForm())
</script>

<template>
  <div class="space-y-6 max-w-2xl">

    <!-- Titre -->
    <div>
      <h1 class="text-xl font-bold text-gray-900">Mon profil</h1>
      <p class="text-sm text-gray-400 mt-0.5">Gérez vos informations personnelles et la sécurité de votre compte</p>
    </div>

    <!-- ── Section 1 — Informations personnelles ───────────────────────── -->
    <div class="card p-5 space-y-5">
      <h2 class="font-semibold text-gray-800 text-sm">Informations personnelles</h2>

      <!-- Avatar -->
      <div class="flex items-center gap-4">
        <div class="relative">
          <img
            v-if="avatarPreview || authStore.avatarUrl"
            :src="avatarPreview || authStore.avatarUrl"
            class="w-16 h-16 rounded-full object-cover border-2 border-gray-100"
          />
          <div
            v-else
            class="w-16 h-16 rounded-full bg-primary-100 text-primary-700 font-bold text-xl flex items-center justify-center border-2 border-gray-100"
          >
            {{ initials }}
          </div>
        </div>
        <div>
          <label class="btn-secondary text-xs cursor-pointer">
            <svg class="w-4 h-4 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            Changer la photo
            <input type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
          </label>
          <p class="text-xs text-gray-400 mt-1">JPG, PNG — max. 2 Mo</p>
        </div>
      </div>

      <!-- Champs -->
      <form @submit.prevent="saveProfile" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="form-label">Prénom</label>
            <input v-model="profileForm.firstname" class="form-input" />
            <p v-if="profileErrors.firstname" class="text-red-500 text-xs mt-1">{{ profileErrors.firstname[0] }}</p>
          </div>
          <div>
            <label class="form-label">Nom</label>
            <input v-model="profileForm.lastname" class="form-input" />
            <p v-if="profileErrors.lastname" class="text-red-500 text-xs mt-1">{{ profileErrors.lastname[0] }}</p>
          </div>
        </div>
        <div>
          <label class="form-label">Email</label>
          <input v-model="profileForm.email" type="email" class="form-input" />
          <p v-if="profileErrors.email" class="text-red-500 text-xs mt-1">{{ profileErrors.email[0] }}</p>
        </div>
        <div>
          <label class="form-label">Téléphone</label>
          <input v-model="profileForm.phone" class="form-input" />
          <p v-if="profileErrors.phone" class="text-red-500 text-xs mt-1">{{ profileErrors.phone[0] }}</p>
        </div>
        <div class="flex justify-end pt-1">
          <button type="submit" class="btn-primary" :disabled="savingProfile">
            {{ savingProfile ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ── Section 2 — Changer le mot de passe ────────────────────────── -->
    <div class="card p-5 space-y-4">
      <h2 class="font-semibold text-gray-800 text-sm">Changer le mot de passe</h2>
      <p class="text-xs text-gray-400">Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial</p>

      <div v-if="pwSuccess" class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Mot de passe changé avec succès.
      </div>

      <form @submit.prevent="savePassword" class="space-y-4">
        <PasswordInput
          v-model="pwForm.current_password"
          label="Mot de passe actuel"
          placeholder=""
          :required="true"
          :error="pwErrors.current_password?.[0]"
        />
        <PasswordInput
          v-model="pwForm.password"
          label="Nouveau mot de passe"
          placeholder=""
          :required="true"
          :error="pwErrors.password?.[0]"
        />
        <PasswordInput
          v-model="pwForm.password_confirmation"
          label="Confirmer le nouveau mot de passe"
          placeholder=""
          :required="true"
          :error="pwErrors.password_confirmation?.[0]"
        />
        <div class="flex justify-end pt-1">
          <button type="submit" class="btn-primary" :disabled="savingPw">
            {{ savingPw ? 'Changement...' : 'Changer le mot de passe' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ── Section 3 — Zone de danger ────────────────────────────────────── -->
    <div class="card p-5 border border-red-200 bg-red-50 space-y-4">
      <h2 class="font-semibold text-red-700 text-sm">Zone de danger</h2>
      <p class="text-sm text-red-600">
        La suppression de votre compte est <strong>irréversible</strong>. Toutes vos données (organisation, agents, membres, tontines, cotisations) seront définitivement supprimées.
      </p>
      <div>
        <button class="btn-danger" @click="showDeleteModal = true">
          Supprimer mon compte
        </button>
      </div>
    </div>

    <!-- ── MODAL SUPPRESSION COMPTE ───────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showDeleteModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <h2 class="font-semibold text-red-700">Supprimer mon compte</h2>
            <button @click="showDeleteModal = false" class="p-1 hover:bg-gray-100 rounded-lg">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="p-5 space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
              <strong>Attention !</strong> Cette action supprimera définitivement votre compte et toutes les données associées : organisation, agents, membres, tontines, cotisations et versements. Cette opération est irréversible.
            </div>
            <PasswordInput
              v-model="deletePassword"
              label="Entrez votre mot de passe pour confirmer"
              placeholder="Votre mot de passe"
              :error="deleteError"
            />
            <div class="flex gap-3 pt-2">
              <button class="btn-secondary flex-1" @click="showDeleteModal = false; deletePassword = ''; deleteError = ''">
                Annuler
              </button>
              <button class="btn-danger flex-1" :disabled="deletingAccount || !deletePassword" @click="confirmDeleteAccount">
                {{ deletingAccount ? 'Suppression...' : 'Supprimer définitivement' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>
