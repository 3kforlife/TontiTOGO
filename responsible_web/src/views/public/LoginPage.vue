<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const authStore = useAuthStore()

const form    = reactive({ login: '', password: '' })
const loading = ref(false)
const errors  = ref({})
const serverError = ref('')

async function submit() {
  loading.value = true
  errors.value  = {}
  serverError.value = ''

  try {
    await authStore.login(form)
    router.replace({ name: 'dashboard' })
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

      <!-- Header -->
      <div class="text-center mb-8">
        <RouterLink :to="{ name: 'home' }" class="inline-flex items-center gap-2 mb-6">
          <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="text-xl font-bold text-gray-900">TontiTOGO</span>
        </RouterLink>
        <h1 class="text-2xl font-bold text-gray-900">Connexion</h1>
        <p class="text-gray-500 text-sm mt-1">Accédez à votre espace responsable</p>
      </div>

      <div class="card">
        <!-- Erreur globale -->
        <div v-if="serverError" class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">
          {{ serverError }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email -->
          <div>
            <label class="form-label">Adresse e-mail <span class="text-red-500">*</span></label>
            <input v-model="form.login" type="text" placeholder="votre email" class="form-input" required />
            <p v-if="errors.login" class="text-red-500 text-xs mt-1">{{ errors.login[0] }}</p>
          </div>

          <!-- Mot de passe -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="form-label mb-0">Mot de passe <span class="text-red-500">*</span></label>
              <RouterLink :to="{ name: 'forgot-password' }" class="text-xs text-primary-600 hover:underline">
                Mot de passe oublié ?
              </RouterLink>
            </div>
            <input v-model="form.password" type="password" placeholder="••••••••" class="form-input" required />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5">
            <svg v-if="loading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-500">
          Pas encore de compte ?
          <RouterLink :to="{ name: 'register' }" class="text-primary-600 font-medium hover:underline">
            Créer un compte
          </RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
