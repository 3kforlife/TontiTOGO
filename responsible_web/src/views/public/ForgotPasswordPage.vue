<script setup>
import { ref } from 'vue'
import { authService } from '@/services/authService'

const email   = ref('')
const loading = ref(false)
const success = ref(false)
const error   = ref('')

async function submit() {
  loading.value = true
  error.value   = ''
  try {
    await authService.forgotPassword({ email: email.value })
    success.value = true
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue.'
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
          <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="text-xl font-bold text-gray-900">TontiTOGO</span>
        </RouterLink>
        <h1 class="text-2xl font-bold text-gray-900">Mot de passe oublié</h1>
        <p class="text-gray-500 text-sm mt-1">Saisissez votre email pour recevoir un lien de réinitialisation</p>
      </div>

      <div class="card">
        <div v-if="success" class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <p class="text-sm text-gray-600 mb-4">Si cette adresse est enregistrée, vous recevrez un e-mail dans quelques instants.</p>
          <RouterLink :to="{ name: 'login' }" class="btn-secondary w-full">Retour à la connexion</RouterLink>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-4">
          <div v-if="error" class="p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">{{ error }}</div>
          <div>
            <label class="form-label">Adresse e-mail</label>
            <input v-model="email" type="email" placeholder="votre email" class="form-input" required />
          </div>
          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5">
            {{ loading ? 'Envoi...' : 'Envoyer le lien' }}
          </button>
          <RouterLink :to="{ name: 'login' }" class="block text-center text-sm text-gray-500 hover:text-gray-700">
            ← Retour à la connexion
          </RouterLink>
        </form>
      </div>
    </div>
  </div>
</template>
