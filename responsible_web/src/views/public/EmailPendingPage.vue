<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { authService }  from '@/services/authService'
import { useRouter }    from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()
const sending   = ref(false)
const sent      = ref(false)
const error     = ref('')

async function resend() {
  sending.value = true
  error.value   = ''
  try {
    await authService.sendVerificationEmail()
    sent.value = true
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de l\'envoi.'
  } finally {
    sending.value = false
  }
}

function logout() {
  authStore.logout()
  router.replace({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="card w-full max-w-md text-center p-8 space-y-6">

      <!-- Icône -->
      <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto">
        <svg class="w-8 h-8 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
      </div>

      <!-- Titre -->
      <div>
        <h1 class="text-xl font-bold text-gray-900">Vérifiez votre adresse e-mail</h1>
        <p class="text-sm text-gray-500 mt-2">
          Un e-mail de vérification a été envoyé à
          <span class="font-semibold text-gray-700">{{ authStore.user?.email }}</span>.
          Cliquez sur le lien dans l'e-mail pour activer votre compte.
        </p>
      </div>

      <!-- Alerte succès renvoi -->
      <div v-if="sent" class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        E-mail renvoyé avec succès. Vérifiez votre boîte de réception.
      </div>

      <!-- Erreur -->
      <div v-if="error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
        {{ error }}
      </div>

      <!-- Actions -->
      <div class="space-y-3">
        <button
          class="btn-primary w-full"
          :disabled="sending || sent"
          @click="resend"
        >
          <svg v-if="sending" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ sending ? 'Envoi...' : sent ? 'E-mail envoyé ✓' : 'Renvoyer l\'e-mail de vérification' }}
        </button>

        <button class="btn-secondary w-full text-sm" @click="logout">
          Se déconnecter
        </button>
      </div>

      <p class="text-xs text-gray-400">
        Vérifiez aussi vos spams si vous ne trouvez pas l'e-mail.
      </p>
    </div>
  </div>
</template>
