<script setup>
import { ref, onMounted, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { memberService } from '@/services/memberService'

defineProps({ loading: Boolean })

const counts = ref({ active: 0, suspended: 0 })

onMounted(async () => {
  try {
    const [activeRes, suspendedRes] = await Promise.all([
      memberService.list({ status: 'active',    per_page: 1 }),
      memberService.list({ status: 'suspended', per_page: 1 }),
    ])
    counts.value.active    = activeRes.data.data.total    || 0
    counts.value.suspended = suspendedRes.data.data.total || 0
  } catch {}
})

const total    = computed(() => counts.value.active + counts.value.suspended)
const series   = computed(() => [counts.value.active, counts.value.suspended])
const options  = computed(() => ({
  chart:  { type: 'donut', animations: { speed: 500 } },
  labels: ['Actifs', 'Suspendus'],
  colors: ['#16a34a', '#f87171'],
  legend: {
    position: 'bottom',
    labels:   { colors: '#6b7280' },
    fontSize: '12px',
  },
  dataLabels: {
    enabled: true,
    formatter: (val) => val.toFixed(0) + '%',
    style: { fontSize: '11px' },
  },
  plotOptions: { pie: { donut: { size: '68%' } } },
  tooltip: { y: { formatter: (v) => v + ' membres' } },
}))
</script>

<template>
  <div class="card">
    <div class="mb-1">
      <h3 class="text-sm font-semibold text-gray-900">Répartition du portefeuille membres</h3>
      <p class="text-xs text-gray-400 mt-0.5">Santé de la communauté — traiter les adhésions suspendues</p>
    </div>

    <div v-if="loading || total === 0" class="h-52 bg-gray-50 rounded-xl animate-pulse mt-4" />
    <VueApexCharts
      v-else
      type="donut"
      height="210"
      :options="options"
      :series="series"
    />
  </div>
</template>
