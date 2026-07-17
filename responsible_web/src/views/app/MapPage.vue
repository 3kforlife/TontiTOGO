<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useMapStore }  from '@/stores/map'
import { agentService } from '@/services/agentService'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import L from 'leaflet'

// Create custom blue pin icon
const customPinIcon = L.divIcon({
  className: 'custom-pin-icon',
  html: `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 0C7.16344 0 0 7.16344 0 16C0 28 16 40 16 40C16 40 32 28 32 16C32 7.16344 24.8366 0 16 0Z" fill="#2563EB"/>
    <circle cx="16" cy="16" r="8" fill="white"/>
    <circle cx="16" cy="16" r="4" fill="#2563EB"/>
  </svg>`,
  iconSize: [32, 40],
  iconAnchor: [16, 40],
  popupAnchor: [0, -40],
})
import 'leaflet/dist/leaflet.css'

const store  = useMapStore()
const agents = ref([])

const filters = reactive({ date: '', agent_id: '' })
const zoom    = ref(7)
const center  = ref([8.6, 1.0])

const togoMaxBounds = [
  [5.9, -0.2],
  [11.2, 1.9],
]

async function loadAgents() {
  try {
    const res  = await agentService.list({ per_page: 100 })
    agents.value = res.data.data.data || []
  } catch {}
}

function applyFilters() {
  store.setFilters({ date: filters.date, agent_id: filters.agent_id })
  store.fetchMarkers()
}

function resetFilters() {
  filters.date     = ''
  filters.agent_id = ''
  store.setFilters({ date: '', agent_id: '' })
  store.fetchMarkers()
}

function formatAmount(v) {
  return new Intl.NumberFormat('fr-FR').format(v || 0) + ' F'
}

function formatDate(d) {
  if (!d) return '—'
  // La date vient déjà formatée depuis Laravel (d/m/Y H:i)
  return d
}

onMounted(async () => {
  await loadAgents()
  store.fetchMarkers()
})
</script>

<template>
  <div class="space-y-6">

    <!-- Titre -->
    <div>
      <h1 class="text-xl font-bold text-gray-900">Carte des collectes</h1>
      <p class="text-sm text-gray-400 mt-0.5">{{ store.count }} point{{ store.count > 1 ? 's' : '' }} de collecte</p>
    </div>

    <!-- Filtres -->
    <div class="card p-4 flex flex-wrap gap-3 items-end">
      <div class="flex flex-col gap-1">
        <label class="form-label">Date</label>
        <input type="date" v-model="filters.date" class="form-input w-auto" />
      </div>
      <div class="flex flex-col gap-1 min-w-[180px]">
        <label class="form-label">Agent</label>
        <select v-model="filters.agent_id" class="form-input">
          <option value="">Tous les agents</option>
          <option v-for="a in agents" :key="a.id" :value="a.id">
            {{ a.full_name }}
          </option>
        </select>
      </div>
      <div class="flex gap-2">
        <button class="btn-primary flex items-center gap-2 cursor-pointer" @click="applyFilters" :disabled="store.loading">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Actualiser
        </button>
        <button class="btn-secondary" @click="resetFilters">Réinitialiser</button>
      </div>
    </div>

    <!-- Carte -->
    <div class="card p-0 overflow-hidden" style="height: 520px;">
      <!-- Loading overlay -->
      <div v-if="store.loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70">
        <div class="text-center">
          <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
          <p class="text-sm text-gray-400">Chargement des données...</p>
        </div>
      </div>

      <LMap
        v-model:zoom="zoom"
        :center="center"
        :min-zoom="6"
        :max-zoom="16"
        :max-bounds="togoMaxBounds"
        :max-bounds-viscosity="1.0"
        style="height: 100%; width: 100%;"
        :use-global-leaflet="false"
      >
        <LTileLayer
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
          layer-type="base"
          name="OpenStreetMap"
        />
        <LMarker
          v-for="marker in store.markers"
          :key="marker.id"
          :lat-lng="[marker.latitude, marker.longitude]"
          :icon="customPinIcon"
        >
          <LPopup>
            <div class="text-xs space-y-2 min-w-[220px]">
              <!-- Agent Name -->
              <div class="flex items-center gap-2">
                <span class="text-gray-400">👤</span>
                <p class="text-gray-500">
                  <span class="font-medium text-gray-700">Agent :</span> {{ marker.agent?.full_name || '—' }}
                </p>
              </div>

              <!-- Member Name (if available) -->
              <div v-if="marker.member?.full_name" class="flex items-center gap-2">
                <span class="text-gray-400">👤</span>
                <p class="text-gray-500">
                  <span class="font-medium text-gray-700">Membre :</span> {{ marker.member.full_name }}
                </p>
              </div>

              <!-- Amount -->
              <div class="flex items-center gap-2">
                <span class="text-gray-400">💰</span>
                <p class="text-gray-500">
                  <span class="font-medium text-green-700">Montant :</span>
                  <span class="font-semibold text-green-700">{{ formatAmount(marker.amount) }}</span>
                </p>
              </div>

               <!-- Date -->
               <div class="flex items-center gap-2">
                 <span class="text-gray-400">📅</span>
                 <p class="text-gray-500">
                   <span class="font-medium text-gray-700">Date :</span> {{ formatDate(marker.created_at) }}
                 </p>
               </div>

              <!-- Coordinates (clickable) -->
              <div class="flex items-center gap-2">
                <span class="text-gray-400">📍</span>
                <a
                  :href="`https://www.google.com/maps?q=${marker.latitude},${marker.longitude}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-primary-600 hover:text-primary-700 hover:underline font-medium"
                >
                  {{ marker.latitude }}, {{ marker.longitude }}
                </a>
              </div>

              <!-- Google Maps Button -->
              <a
                :href="`https://www.google.com/maps?q=${marker.latitude},${marker.longitude}`"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 mt-2 w-full btn-primary text-xs justify-center py-1.5"
              >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                Voir sur Google Maps
              </a>
            </div>
          </LPopup>
        </LMarker>
      </LMap>
    </div>

    <!-- Aucun point -->
    <div v-if="!store.loading && store.markers.length === 0" class="text-center py-6 text-gray-300">
      <svg class="w-10 h-10 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <p class="text-sm">Aucun point de collecte pour ces critères</p>
    </div>

  </div>
</template>

<style scoped>
.custom-pin-icon {
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}
</style>
