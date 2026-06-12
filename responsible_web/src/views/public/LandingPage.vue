<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router     = useRouter()
const menuOpen   = ref(false)

const features = [
  {
    icon: 'shield',
    title: 'Sécurisé & Fiable',
    desc:  "Chaque cotisation est enregistrée avec une référence unique, une heure exacte et les coordonnées GPS de l'encaissement.",
  },
  {
    icon: 'mobile',
    title: 'Agents sur le terrain',
    desc:  "Vos agents collectent depuis leur téléphone. Plus besoin d'attendre le soir pour saisir les données.",
  },
  {
    icon: 'sms',
    title: 'Notifications SMS',
    desc:  'Chaque membre reçoit un SMS de confirmation instantané après chaque versement. Zéro litige.',
  },
  {
    icon: 'chart',
    title: 'Tableaux de bord',
    desc:  'Suivez en temps réel les collectes, les performances de vos agents et la santé financière de vos tontines.',
  },
  {
    icon: 'map',
    title: 'Carte GPS',
    desc:  "Visualisez sur une carte interactive, l'emplacement exact de chaque collecte de la journée.",
  },
  {
    icon: 'export',
    title: 'Exports PDF & Excel',
    desc:  'Générez en un clic des rapports complets pour vos cotisations.',
  },
]

const steps = [
  { num: '01', title: 'Inscrivez-vous', desc: 'Créez votre compte responsable et votre organisation en moins de 2 minutes.' },
  { num: '02', title: 'Créez vos agents', desc: 'Ajoutez vos collecteurs de terrain. Ils téléchargent l\'application mobile TontiTOGO et se connectent instantanément pour démarrer les collectes.' },
  { num: '03', title: 'Lancez vos tontines', desc: 'Configurez vos tontines, ajoutez vos membres et définissez les montants.' },
  { num: '04', title: 'Collectez & Suivez', desc: 'Vos agents encaissent sur le terrain. Vous pilotez tout depuis votre bureau.' },
]
</script>

<template>
  <div class="min-h-screen bg-white">

    <!-- ── NAVBAR ──────────────────────────────────────────────────────── -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

        <!-- Logo -->
        <RouterLink :to="{ name: 'home' }" class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="text-lg font-bold text-gray-900">TontiTOGO</span>
        </RouterLink>

        <!-- CTA desktop -->
        <div class="hidden sm:flex items-center gap-3">
          <RouterLink :to="{ name: 'login' }" class="btn-secondary text-sm">
            Se connecter
          </RouterLink>
          <RouterLink :to="{ name: 'register' }" class="btn-primary text-sm">
            S'inscrire
          </RouterLink>
        </div>

        <!-- Burger mobile -->
        <button class="sm:hidden p-2 rounded-lg hover:bg-gray-100" @click="menuOpen = !menuOpen">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Menu mobile -->
      <div v-if="menuOpen" class="sm:hidden border-t border-gray-100 px-4 py-3 space-y-2">
        <RouterLink :to="{ name: 'login' }" class="block w-full btn-secondary text-center">Connexion</RouterLink>
        <RouterLink :to="{ name: 'register' }" class="block w-full btn-primary text-center">Commencer gratuitement</RouterLink>
      </div>
    </nav>

    <!-- ── HERO ────────────────────────────────────────────────────────── -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-white py-20 sm:py-28">

      <!-- Déco background -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-100 rounded-full opacity-40 blur-3xl" />
      <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-primary-200 rounded-full opacity-30 blur-3xl" />

      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold mb-6">
          <span class="w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
          Plateforme numérique de tontine au Togo
        </span>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
          La gestion de votre tontine,
          <span class="text-primary-600"> enfin simple.</span>
        </h1>

        <p class="text-lg text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
          Pilotez vos agents, suivez chaque cotisation en temps réel, notifiez vos membres par SMS et
          exportez vos rapports. Plus besoin de registres papiers, gérez toute votre activité depuis votre navigateur.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <RouterLink :to="{ name: 'register' }" class="btn-primary px-8 py-3 text-base">
            Créer mon compte responsable
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </RouterLink>
          <RouterLink :to="{ name: 'login' }" class="btn-secondary px-8 py-3 text-base">
            Accéder à mon espace
          </RouterLink>
        </div>

        <!-- Social proof minimaliste -->
        <p class="mt-8 text-xs text-gray-400">
          Sécurisé · Technologie anti-fraude · Données protégées
        </p>
      </div>
    </section>

    <!-- ── FONCTIONNALITÉS ─────────────────────────────────────────────── -->
    <section class="py-20 bg-white">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-gray-900 mb-3">Tout ce dont vous avez besoin</h2>
          <p class="text-gray-500 max-w-xl mx-auto">Une seule plateforme pour gérer l'intégralité de votre activité de tontine.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="f in features"
            :key="f.title"
            class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-sm transition-all duration-200 group"
          >
            <!-- Icône -->
            <div class="w-11 h-11 rounded-xl bg-primary-50 group-hover:bg-primary-100 transition-colors flex items-center justify-center mb-4">
              <!-- shield -->
              <svg v-if="f.icon === 'shield'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              <!-- mobile -->
              <svg v-else-if="f.icon === 'mobile'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1"/>
              </svg>
              <!-- sms -->
              <svg v-else-if="f.icon === 'sms'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
              </svg>
              <!-- chart -->
              <svg v-else-if="f.icon === 'chart'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
              </svg>
              <!-- map -->
              <svg v-else-if="f.icon === 'map'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
              </svg>
              <!-- export -->
              <svg v-else-if="f.icon === 'export'" class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1.5">{{ f.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── COMMENT ÇA MARCHE ───────────────────────────────────────────── -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold text-gray-900 mb-3">Démarrez en 4 étapes</h2>
          <p class="text-gray-500">Votre tontine numérique opérationnelle en moins d'une heure.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="step in steps"
            :key="step.num"
            class="relative bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-sm transition-shadow"
          >
            <span class="text-4xl font-black text-primary-100 leading-none">{{ step.num }}</span>
            <h3 class="font-semibold text-gray-900 mt-2 mb-1.5">{{ step.title }}</h3>
            <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA FINAL ───────────────────────────────────────────────────── -->
    <section class="py-20 bg-primary-600">
      <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Prêt à moderniser votre tontine ?</h2>
        <p class="text-primary-100 mb-8 text-lg">Rejoignez les responsables qui pilotent leur tontine depuis leur bureau.</p>
        <RouterLink :to="{ name: 'register' }" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-colors text-base">
          Créer mon compte maintenant
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </RouterLink>
      </div>
    </section>

    <!-- ── FOOTER ──────────────────────────────────────────────────────── -->
    <footer class="bg-gray-900 text-gray-400 py-10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-primary-500 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <span class="text-white font-semibold">TontiTOGO</span>
        </div>
        <p class="text-xs text-center">© {{ new Date().getFullYear() }} TontiTOGO. La gestion numérique de votre tontine en toute simplicité.</p>
        <div class="flex gap-4 text-xs">
          <RouterLink :to="{ name: 'login' }" class="hover:text-white transition-colors">Connexion</RouterLink>
          <RouterLink :to="{ name: 'register' }" class="hover:text-white transition-colors">Inscription</RouterLink>
        </div>
      </div>
    </footer>

  </div>
</template>
