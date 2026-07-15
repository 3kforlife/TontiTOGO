<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route     = useRoute()
const router    = useRouter()
const authStore = useAuthStore()
const status    = ref('loading')
const message   = ref('')

async function verifyEmail(backendUrl) {
  try {
    // L'URL est celle du backend Render — appel direct avec Bearer token
    const token = localStorage.getItem('token')
    const { default: axios } = await import('axios')
    await axios.get(backendUrl, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    })

    // Rafraîchir le user → email_verified_at est maintenant rempli
    await authStore.fetchMe()

    status.value  = 'success'
    message.value = 'Adresse e-mail vérifiée avec succès !'
    localStorage.removeItem('pendingEmailVerificationUrl')

    setTimeout(() => router.replace({ name: 'dashboard' }), 1500)
  } catch (err) {
    status.value  = 'error'
    message.value = err.response?.data?.message || 'Le lien est invalide ou a expiré.'
  }
}

onMounted(async () => {
  const backendUrl = route.query.url
  if (!backendUrl) {
    status.value  = 'error'
    message.value = 'Lien invalide.'
    return
  }

  if (authStore.isAuthenticated) {
    await verifyEmail(backendUrl)
  } else {
    // Stocker l'URL pour après la connexion
    localStorage.setItem('pendingEmailVerificationUrl', backendUrl)
    status.value  = 'info'
    message.value = 'Veuillez vous connecter pour finaliser la vérification.'
    setTimeout(() => router.replace({ name: 'login' }), 2500)
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

      <!-- Info (se connecter d'abord) -->
      <div v-else-if="status === 'info'">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-1">{{ message }}</p>
        <p class="text-sm text-gray-400">Redirection vers la page de connexion...</p>
      </div>

      <!-- Succès -->
      <div v-else-if="status === 'success'">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-1">{{ message }}</p>
        <p class="text-sm text-gray-400">{{ authStore.isAuthenticated ? 'Redirection vers le tableau de bord...' : 'Redirection vers la page de connexion...' }}</p>
      </div>

      <!-- Erreur -->
      <div v-else>
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <p class="font-semibold text-gray-900 mb-3">{{ message }}</p>
        <div class="space-y-2">
          <RouterLink :to="{ name: 'login' }" class="btn-secondary w-full">
            Retour à la connexion
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
