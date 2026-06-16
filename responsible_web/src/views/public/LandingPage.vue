<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import TontiTogoLogo from '../../components/TontiTogoLogo.vue'

const router = useRouter()
const menuOpen = ref(false)
const activeStep = ref(-1)
const lineProgress = ref(100)

// Typewriter effect
const fullText = "La gestion de votre tontine, enfin simple."
const displayedText = ref("")
const isTyping = ref(true)
let typewriterInterval = ref(null)

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

// Lancer l'effet typewriter au montage
onMounted(() => {
  let charIndex = 0
  typewriterInterval = setInterval(() => {
    if (charIndex < fullText.length) {
      displayedText.value += fullText[charIndex]
      charIndex++
    } else {
      clearInterval(typewriterInterval)
      isTyping.value = false
    }
  }, 70) // Vitesse d'écriture : 70ms par caractère
})

// Nettoyer l'intervalle au démontage
onUnmounted(() => {
  if (typewriterInterval) {
    clearInterval(typewriterInterval)
  }
})
</script>

<template>
  <div class="min-h-screen bg-white">

    <!-- ── NAVBAR ──────────────────────────────────────────────────────── -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

        <!-- Logo -->
        <RouterLink :to="{ name: 'home' }" class="flex items-center gap-2.5">
              <TontiTogoLogo />
              <span class="text-lg font-bold">
                <span class="text-gray-900">Tonti</span><span class="text-primary-600">TOGO</span>
              </span>
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

    <!-- ── HERO & STEPS (2 colonnes) ───────────────────────────────────── -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-white py-16 sm:py-24">

      <!-- Déco background -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-100 rounded-full opacity-40 blur-3xl" />
      <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-primary-200 rounded-full opacity-30 blur-3xl" />

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

          <!-- Colonne gauche: Hero -->
          <div class="pt-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold mb-6 animate-fade-in-up">
              <span class="w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
              Plateforme numérique de tontine au Togo
            </span>

            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight mb-6 animate-fade-in-up delay-100">
              <!-- Partie avant "enfin simple." -->
              <span v-if="displayedText.length >= 27">
                {{ displayedText.slice(0, 27) }}
              </span>
              <span v-else>{{ displayedText }}</span>
              
              <!-- Partie verte "enfin simple." -->
              <span v-if="displayedText.length >= 27" class="text-primary-600">
                {{ displayedText.slice(27) }}
              </span>
              
              <!-- Curseur clignotant -->
              <span v-if="isTyping" class="inline-block w-1.5 h-12 sm:h-14 bg-primary-600 ml-1 align-middle animate-pulse"></span>
            </h1>

            <p class="text-lg text-gray-600 max-w-xl mb-10 leading-relaxed animate-fade-in-up delay-200">
              Pilotez vos agents, suivez chaque cotisation en temps réel, notifiez vos membres par SMS et
              exportez vos rapports. Plus besoin de registres papiers, gérez toute votre activité depuis votre navigateur.
            </p>

            <div class="flex flex-col sm:flex-row items-start gap-4 animate-fade-in-up delay-300">
              <RouterLink :to="{ name: 'register' }" class="btn-primary px-8 py-4 text-base shadow-lg shadow-primary-500/20 hover:shadow-xl hover:shadow-primary-500/30 transition-all duration-300">
                Créer mon compte responsable
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
              </RouterLink>
            </div>

            <!-- Social proof minimaliste -->
            <p class="mt-8 text-sm text-gray-400 animate-fade-in-up delay-400">
              Sécurisé · Technologie anti-fraude · Données protégées
            </p>
          </div>

          <!-- Colonne droite: Étapes -->
          <div class="relative">
            <!-- Ligne verticale -->
            <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-gradient-to-b from-primary-600 via-primary-500 to-primary-400 hidden lg:block" />

            <h2 class="text-2xl font-bold text-gray-900 mb-8 lg:text-center lg:mb-10">
              Démarrez en 4 étapes
            </h2>

            <div class="space-y-5">
              <div
                v-for="(step, index) in steps"
                :key="step.num"
                class="relative flex gap-5 items-start p-5 bg-white rounded-2xl border border-gray-100 shadow-sm transition-all duration-300 timeline-loop-card"
                :class="{
                  'shadow-xl border-primary-300': activeStep === index,
                  'hover:shadow-lg hover:border-primary-200 hover:-translate-y-1.5': activeStep !== index,
                  '-translate-y-1.5': activeStep === index
                }"
                :style="{
                  '--delay': `${index * 0.6}s`,
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                }"
                @mouseenter="activeStep = index"
                @mouseleave="activeStep = -1"
              >
                <!-- Numéro de l'étape dans un cercle vert -->
                <div
                  class="relative z-10 flex-shrink-0 w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-primary-600/25 transition-transform duration-300 timeline-loop-circle"
                  :style="{
                    '--delay': `${index * 0.6}s`,
                    transform: activeStep === index ? 'scale(1.15)' : 'scale(1)'
                  }"
                >
                  {{ step.num }}
                </div>

                <!-- Contenu de l'étape -->
                <div class="flex-1 pt-1">
                  <h3
                    class="font-semibold mb-1 transition-colors duration-300"
                    :class="activeStep === index ? 'text-primary-700' : 'text-gray-900'"
                  >
                    {{ step.title }}
                  </h3>
                  <p class="text-sm text-gray-500 leading-relaxed">
                    {{ step.desc }}
                  </p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ── FONCTIONNALITÉS ─────────────────────────────────────────────── -->
    <section class="py-24 bg-white">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold mb-4">
            Fonctionnalités
          </span>
          <h2 class="text-3xl font-bold text-gray-900 mb-3">Tout ce dont vous avez besoin</h2>
          <p class="text-gray-500 max-w-xl mx-auto">Une seule plateforme pour gérer l'intégralité de votre activité de tontine.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="f in features"
            :key="f.title"
            class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300 group"
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

    
    <!-- ── FOOTER ──────────────────────────────────────────────────────── -->
    <footer class="bg-gray-900 text-gray-400 py-10">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <TontiTogoLogo />
          <span class="text-white font-semibold">
            <span class="text-gray-300">Tonti</span><span class="text-primary-400">TOGO</span>
          </span>
        </div>
        <p class="text-xs text-center">© {{ new Date().getFullYear() }} TontiTOGO. La gestion numérique de votre tontine en toute simplicité.</p>
        <div class="flex gap-4 text-xs">
          <p>Support@tontitogo.com</p>
        </div>
      </div>
    </footer>

  </div>
</template>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out both;
}

.animate-fade-in-up.delay-100 {
  animation-delay: 0.1s;
}

.animate-fade-in-up.delay-200 {
  animation-delay: 0.2s;
}

.animate-fade-in-up.delay-300 {
  animation-delay: 0.3s;
}

.animate-fade-in-up.delay-400 {
  animation-delay: 0.4s;
}

/* Animation pour les cards de la timeline : apparition progressive puis animation subtile continue */
@keyframes fadeInCard {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInCircle {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes gentleFloat {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-5px);
  }
}

.timeline-loop-card {
  opacity: 0;
  animation: fadeInCard 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards, gentleFloat 3s ease-in-out infinite;
  animation-delay: var(--delay, 0s), calc(var(--delay, 0s) + 2s);
}

.timeline-loop-circle {
  opacity: 0;
  animation: fadeInCircle 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
  animation-delay: var(--delay, 0s);
}
</style>
