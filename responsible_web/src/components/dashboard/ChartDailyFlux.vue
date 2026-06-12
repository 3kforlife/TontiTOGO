<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  charts:  Object,
  loading: Boolean,
})

// Prend les 7 derniers jours depuis la série de 30 jours
const series = computed(() => {
  if (!props.charts?.daily_collection) return [{ name: 'Collecte', data: [] }]
  const data = props.charts.daily_collection.series[0].data.slice(-7)
  return [{ name: 'Collecte (FCFA)', data }]
})

const categories = computed(() => {
  if (!props.charts?.daily_collection) return []
  return props.charts.daily_collection.categories.slice(-7)
})

const options = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    sparkline: { enabled: false },
    animations: { enabled: true, speed: 500 },
  },
  stroke:   { curve: 'smooth', width: 2.5 },
  fill:     { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
  colors:   ['#16a34a'],
  dataLabels: { enabled: false },
  xaxis: {
    categories: categories.value,
    labels: { style: { fontSize: '11px', colors: '#9ca3af' } },
    axisBorder: { show: false },
    axisTicks:  { show: false },
  },
  yaxis: {
    labels: {
      formatter: (v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v),
      style: { fontSize: '11px', colors: '#9ca3af' },
    },
  },
  tooltip: {
    y: { formatter: (v) => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' },
  },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
}))
</script>

<template>
  <div class="card">
    <div class="flex items-start justify-between mb-1">
      <div>
        <h3 class="text-sm font-semibold text-gray-900">Flux de collectes journalières</h3>
        <p class="text-xs text-gray-400 mt-0.5">Évolution sur 7 jours — anticiper les besoins en trésorerie</p>
      </div>
    </div>

    <div v-if="loading" class="h-48 bg-gray-50 rounded-xl animate-pulse mt-4" />
    <VueApexCharts
      v-else
      type="area"
      height="190"
      :options="options"
      :series="series"
    />
  </div>
</template>
