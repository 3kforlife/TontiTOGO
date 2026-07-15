import axios from 'axios'
import router from '@/router'

const api = axios.create({
  // En production (Vercel) : VITE_API_URL pointe vers l'API Render
  // En développement : le proxy Vite redirige /api → localhost:8000
  baseURL: import.meta.env.VITE_API_URL
    ? import.meta.env.VITE_API_URL + '/api/responsible'
    : '/api/responsible',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: false,
})

// ── Interceptor requête : injecte le token Bearer ──────────────────────
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ── Interceptor réponse : gestion des erreurs globales ─────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const action = error.response?.data?.action

    // 401 → token expiré → retour connexion
    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.replace({ name: 'login' })
    }

    // 403 email_not_verified → rediriger vers page de vérification email
    if (status === 403 && action === 'email_not_verified') {
      router.replace({ name: 'email-pending' })
    }

    return Promise.reject(error)
  },
)

export default api
