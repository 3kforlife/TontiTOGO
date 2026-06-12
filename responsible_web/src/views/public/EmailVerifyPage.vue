<script setup>import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()
const status    = ref('loading') // loading | success | error
const message   = ref('')

onMounted(async () => {
  const backendUrl = route.query.url
  if (!backendUrl) {
    status.value  = 'error'
    message.value = 'Lien invalide.'
    return
  }

  try {
    const token = localStorage.getItem('token')
    await axios.get(backendUrl, {
      headers: { Authorization: `Bearer ${token}` },
    })

    // Rafraîchir le user en localStorage pour persister email_verified_at
    await authStore.fetchMe()

    status.value  = 'success'
    message.value = 'Adresse e-mail vérifiée avec succès !'
    setTimeout(() => router.replace({ name: 'dashboard' }), 2000)
  } catch {
    status.value  = 'error'
    message.value = 'Le lien est invalide ou a expiré.'
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="card w-full max-w-sm text-center">
      <!-- Chargement -->
      <div v-if="status === 'loading'">
        <svg class="w-10 h-10 animate-spin text-primary-500 mx-auto mb-3" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-gray-500">Vérification en cours...</p>
      </div>

      <!-- Succès -->
      <div v-else-if="status === 'success'">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-1">{{ message }}</p>
        <p class="text-sm text-gray-400">Redirection vers le tableau de bord...</p>
      </div>

      <!-- Erreur -->
      <div v-else>
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-3">{{ message }}</p>
        <button>
        <RouterLink :to="{ name: 'login' }" class="btn-secondary w-full">Retour à la connexion</RouterLink>
          {{ resending ? 'Envoi en cours...' : 'Renvoyer le lien' }}
        </button>
      </div>
    </div>
  </div>
</template>
