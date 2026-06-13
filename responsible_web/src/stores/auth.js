import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))

  const isAuthenticated    = computed(() => !!token.value)
  const isEmailVerified    = computed(() => !!user.value?.email_verified_at)
  const organizationName   = computed(() => user.value?.organization?.name || '')
  const fullName           = computed(() => user.value?.full_name || '')
  const avatarUrl          = computed(() => user.value?.avatar_url || null)

  function setSession(data) {
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
  }

  function clearSession() {
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function login(credentials) {
    const res = await authService.login(credentials)
    setSession(res.data.data)

    // Vérifie s'il y a une vérification d'email en attente
    const pendingVerificationUrl = localStorage.getItem('pendingEmailVerificationUrl')
    if (pendingVerificationUrl) {
      try {
        await axios.get(pendingVerificationUrl)
        await fetchMe()
        localStorage.removeItem('pendingEmailVerificationUrl')
      } catch (err) {
        console.error('Erreur lors de la vérification de l\'email:', err)
        localStorage.removeItem('pendingEmailVerificationUrl')
      }
    }

    return res.data
  }

  async function register(formData) {
    const res = await authService.register(formData)
    setSession(res.data.data)
    return res.data
  }

  async function logout() {
    try { await authService.logout() } catch {}
    clearSession()
  }

  async function fetchMe() {
    const res = await authService.me()
    user.value = res.data.data
    localStorage.setItem('user', JSON.stringify(user.value))
    return user.value
  }

  return {
    token, user,
    isAuthenticated, isEmailVerified,
    organizationName, fullName, avatarUrl,
    login, register, logout, fetchMe, setSession, clearSession,
  }
})
