import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import PublicLayout from '../layouts/PublicLayout.vue'
import AppLayout from '../layouts/AppLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
    path: '/',
      component: PublicLayout,
      children: [
    // Landing page
    {
      path: '/',
      name: 'home',
      component: () => import('../views/public/LandingPage.vue')
    },

        {
          path: 'login',
          name: 'login',
          component: () => import('../views/public/LoginPage.vue')
        },
        {
          path: 'register',
          name: 'register',
          component: () => import('../views/public/RegisterPage.vue')
        },
        {
          path: 'forgot-password',
          name: 'forgot-password',
          component: () => import('../views/public/ForgotPasswordPage.vue')
        },
        {
          path: 'reset-password',
          name: 'reset-password',
          component: () => import('../views/public/ResetPasswordPage.vue')
        },
        {
          path: 'verify-email',
          name: 'verify-email',
          component: () => import('../views/public/EmailVerifyPage.vue')
        }
      ]
    },

    // Responsible routes with AppLayout
    {
      path: '/responsible',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/responsible/dashboard'
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('../views/app/DashboardPage.vue')
        },
        {
          path: 'agents',
          name: 'agents',
          component: () => import('../views/app/AgentsPage.vue')
        },
        {
          path: 'members',
          name: 'members',
          component: () => import('../views/app/MembersPage.vue')
        },
        {
          path: 'tontines',
          name: 'tontines',
          component: () => import('../views/app/TontinesPage.vue')
        },
        {
          path: 'contributions',
          name: 'contributions',
          component: () => import('../views/app/ContributionsPage.vue')
        },
        {
          path: 'settlements',
          name: 'settlements',
          component: () => import('../views/app/SettlementsPage.vue')
        },
        {
          path: 'map',
          name: 'map',
          component: () => import('../views/app/MapPage.vue')
        },
        {
          path: 'sms',
          name: 'sms',
          component: () => import('../views/app/SmsPage.vue')
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('../views/app/ProfilePage.vue')
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('../views/app/SettingsPage.vue')
        }
      ]
    },

    // Catch-all
    {
      path: '/:pathMatch(.*)*',
      redirect: '/'
    }
  ]
})

// Navigation Guard
router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return '/responsible/dashboard'
  }

  return true
})

export default router