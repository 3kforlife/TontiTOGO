<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import TontiTogoLogo from '../../components/TontiTogoLogo.vue'
import PasswordInput from '../../components/PasswordInput.vue'

const router    = useRouter()
const authStore = useAuthStore()

const form = reactive({
  organization_name: '',
  firstname: '', lastname: '',
  phone: '', email: '',
  password: '', password_confirmation: '',
})
const loading     = ref(false)
const errors      = ref({})
const serverError = ref('')
const success     = ref(false)

async function submit() {
  loading.value = true
  errors.value  = {}
  serverError.value = ''

  try {
    await authStore.register(form)
    success.value = true
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      serverError.value = err.response.data.message || ''
    } else {
      serverError.value = err.response?.data?.message || 'Une erreur est survenue.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

      <div class="text-center mb-8">
        <RouterLink :to="{ name: 'home' }" class="inline-flex items-center gap-2 mb-6">
          <TontiTogoLogo />
          <span class="text-xl font-bold">
            <span class="text-gray-900">Tonti</span><span class="text-primary-600">TOGO</span>
          </span>
        </RouterLink>
        <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
        <p class="text-gray-500 text-sm mt-1">Commencez à gérer votre tontine dès aujourd'hui</p>
      </div>

      <!-- Message succès -->
      <div v-if="success" class="card text-center">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Compte créé avec succès !</h2>
        <p class="text-sm text-gray-500 mb-4">Un email de vérification vous a été envoyé. Vérifiez votre boîte de réception.</p>
        <RouterLink :to="{ name: 'login' }" class="btn-primary w-full">
          Aller à la connexion
        </RouterLink>
      </div>

      <div v-else class="card">
        <div v-if="serverError" class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">
          {{ serverError }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="form-label">Nom de l'organisation <span class="text-red-500">*</span></label>
            <input v-model="form.organization_name" type="text" placeholder="Le nom de votre organisation" class="form-input" required />
            <p v-if="errors.organization_name" class="text-red-500 text-xs mt-1">{{ errors.organization_name[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="form-label">Prénom <span class="text-red-500">*</span></label>
              <input v-model="form.firstname" type="text" placeholder="votre prénom" class="form-input" required />
              <p v-if="errors.firstname" class="text-red-500 text-xs mt-1">{{ errors.firstname[0] }}</p>
            </div>
            <div>
              <label class="form-label">Nom <span class="text-red-500">*</span></label>
              <input v-model="form.lastname" type="text" placeholder="votre nom de famille" class="form-input" required />
              <p v-if="errors.lastname" class="text-red-500 text-xs mt-1">{{ errors.lastname[0] }}</p>
            </div>
          </div>

          <div>
            <label class="form-label">Téléphone <span class="text-red-500">*</span></label>
            <input v-model="form.phone" type="text" placeholder="votre numero de téléphone" class="form-input" required />
            <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
          </div>

          <div>
            <label class="form-label">Adresse email <span class="text-red-500">*</span></label>
            <input v-model="form.email" type="email" placeholder="votre email" class="form-input" required />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
          </div>

          <div>
            <PasswordInput
              v-model="form.password"
              label="Mot de passe"
              placeholder="••••••••"
              :required="true"
              :error="errors.password?.[0]"
            />
            <p class="text-xs text-gray-400 mt-1">Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial</p>
          </div>

          <div>
            <PasswordInput
              v-model="form.password_confirmation"
              label="Confirmer le mot de passe"
              placeholder="••••••••"
              :required="true"
              :error="errors.password_confirmation?.[0]"
            />
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5 cursor-pointer">
            <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ loading ? 'Création...' : 'Créer mon compte' }}
          </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-500">
          Déjà un compte ?
          <RouterLink :to="{ name: 'login' }" class="text-primary-600 font-medium hover:underline">
            Se connecter
          </RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
