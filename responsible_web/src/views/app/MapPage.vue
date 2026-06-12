<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useMapStore }  from '@/stores/map'
import { agentService } from '@/services/agentService'
import { LMap, LTileLayer, LCircleMarker, LPopup } from '@vue-leaflet/vue-leaflet'
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
  return new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
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
        <button class="btn-primary flex items-center gap-2" @click="applyFilters" :disabled="store.loading">
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
        <LCircleMarker
          v-for="marker in store.markers"
          :key="marker.id"
          :lat-lng="[marker.latitude, marker.longitude]"
          :radius="8"
          color="#16a34a"
          fill-color="#22c55e"
          :fill-opacity="0.8"
          :weight="2"
        >
          <LPopup>
            <div class="text-xs space-y-1 min-w-[160px]">
              <p class="font-semibold text-gray-900 text-sm">{{ marker.member?.full_name || '—' }}</p>
              <p class="text-gray-500">Code : {{ marker.member?.member_code || '—' }}</p>
              <p class="text-gray-500">Tontine : {{ marker.tontine?.name || '—' }}</p>
              <p class="font-medium text-green-700 text-sm">{{ formatAmount(marker.amount) }}</p>
              <p class="text-gray-500">Agent : {{ marker.agent?.full_name || '—' }}</p>
              <p class="text-gray-400">{{ formatDate(marker.collected_at) }}</p>
            </div>
          </LPopup>
        </LCircleMarker>
      </LMap>
    </div>

    <!-- Aucun point -->
    <div v-if="!store.loading && store.markers.length === 0" class="text-center py-6 text-gray-300">
      <svg class="w-10 h-10 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <p class="text-sm">Aucun point de collecte pour ces critères</p>
    </div>

  </div>
</template>
