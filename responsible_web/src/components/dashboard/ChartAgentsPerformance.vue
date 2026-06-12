<script setup>
import { ref, onMounted, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { agentService } from '@/services/agentService'

defineProps({ loading: Boolean })

const agents = ref([])

onMounted(async () => {
  try {
    const res = await agentService.list({ per_page: 10 })
    // Trier par nombre de cotisations décroissant
    agents.value = (res.data.data.data || [])
      .filter(a => (a.total_contributions || 0) > 0)
      .sort((a, b) => (b.total_contributions || 0) - (a.total_contributions || 0))
      .slice(0, 6)
  } catch {}
})

const series = computed(() => [{
  name: 'Cotisations',
  data: agents.value.map(a => a.total_contributions || 0),
}])

const options = computed(() => ({
  chart:  { type: 'bar', toolbar: { show: false }, animations: { speed: 500 } },
  plotOptions: {
    bar: { horizontal: true, borderRadius: 6, barHeight: '60%' },
  },
  colors:     ['#16a34a'],
  dataLabels: { enabled: false },
  xaxis: {
    categories: agents.value.map(a => a.full_name),
    labels: { style: { fontSize: '11px', colors: '#9ca3af' } },
    axisBorder: { show: false },
    axisTicks:  { show: false },
  },
  yaxis: {
    labels: { style: { fontSize: '11px', colors: '#9ca3af' } },
  },
  tooltip: { y: { formatter: (v) => v + ' cotisations' } },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
}))
</script>

<template>
  <div class="card">
    <div class="mb-1">
      <h3 class="text-sm font-semibold text-gray-900">Performance comparative des agents</h3>
      <p class="text-xs text-gray-400 mt-0.5">Classement par nombre de cotisations — motiver & réaffecter</p>
    </div>

    <div v-if="loading || !agents.length" class="h-48 bg-gray-50 rounded-xl animate-pulse mt-4" />
    <VueApexCharts
      v-else
      type="bar"
      height="190"
      :options="options"
      :series="series"
    />
  </div>
</template>
