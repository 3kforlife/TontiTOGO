<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authService } from '@/services/authService'
import PasswordInput from '../../components/PasswordInput.vue'

const route   = useRoute()
const router  = useRouter()

const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const errors  = ref({})
const success = ref(false)
const serverError = ref('')

onMounted(() => {
  form.token = route.query.token || ''
  form.email = route.query.email || ''
})

async function submit() {
  loading.value = true
  errors.value  = {}
  serverError.value = ''
  try {
    await authService.resetPassword(form)
    success.value = true
    setTimeout(() => router.replace({ name: 'login' }), 2000)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    }
    serverError.value = err.response?.data?.message || 'Une erreur est survenue.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Nouveau mot de passe</h1>
      </div>
      <div class="card">
        <div v-if="success" class="text-center">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <p class="text-sm text-gray-600">Mot de passe mis à jour. Redirection...</p>
        </div>
        <form v-else @submit.prevent="submit" class="space-y-4">
          <div v-if="serverError" class="p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100">{{ serverError }}</div>
          <div>
            <PasswordInput
              v-model="form.password"
              label="Nouveau mot de passe"
              placeholder="••••••••"
              :required="true"
              :error="errors.password?.[0]"
            />
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
          <button type="submit" :disabled="loading" class="btn-primary w-full py-2.5">
            {{ loading ? 'Enregistrement...' : 'Changer le mot de passe' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
