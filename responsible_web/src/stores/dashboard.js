import { defineStore } from 'pinia'
import { ref } from 'vue'
import { dashboardService } from '@/services/dashboardService'

export const useDashboardStore = defineStore('dashboard', () => {
  const kpis           = ref(null)
  const charts         = ref(null)
  const recentActivity = ref([])
  const loading        = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const res     = await dashboardService.getStats()
      kpis.value           = res.data.data.kpis
      charts.value         = res.data.data.charts
      recentActivity.value = res.data.data.recent_activity
    } finally {
      loading.value = false
    }
  }

  return { kpis, charts, recentActivity, loading, fetch }
})
