<script setup>
import { ref, onMounted, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { tontineService } from '@/services/tontineService'

defineProps({ loading: Boolean })

// Répartition par tontine (proxy pour la "zone" — corrélée à la tontine actuelle)
const tontines = ref([])

onMounted(async () => {
  try {
    const res = await tontineService.list({ per_page: 6, status: 'active' })
    tontines.value = (res.data.data.data || []).slice(0, 6)
  } catch {}
})

const series  = computed(() => tontines.value.map(t => t.active_participants_count || 0))
const options = computed(() => ({
  chart:  { type: 'pie', animations: { speed: 500 } },
  labels: tontines.value.map(t => t.name),
  colors: ['#16a34a', '#22d3ee', '#6366f1', '#f59e0b', '#f87171', '#a78bfa'],
  legend: {
    position: 'bottom',
    labels:   { colors: '#6b7280' },
    fontSize: '11px',
  },
  dataLabels: {
    enabled: true,
    formatter: (val) => val.toFixed(0) + '%',
    style: { fontSize: '11px' },
  },
  tooltip: { y: { formatter: (v) => v + ' participants actifs' } },
}))
</script>

<template>
  <div class="card">
    <div class="mb-1">
      <h3 class="text-sm font-semibold text-gray-900">Répartition par tontine active</h3>
      <p class="text-xs text-gray-400 mt-0.5">Origine géographique de la trésorerie par groupe</p>
    </div>

    <div v-if="loading || !tontines.length" class="h-52 bg-gray-50 rounded-xl animate-pulse mt-4" />
    <VueApexCharts
      v-else
      type="pie"
      height="210"
      :options="options"
      :series="series"
    />
  </div>
</template>
