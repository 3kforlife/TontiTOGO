import { defineStore } from 'pinia'
import { ref } from 'vue'
import { settlementService } from '@/services/settlementService'

export const useSettlementsStore = defineStore('settlements', () => {
  const settlements   = ref([])
  const total         = ref(0)
  const currentPage   = ref(1)
  const lastPage      = ref(1)
  const loading       = ref(false)
  const pendingSummary = ref([])
  const pendingDate    = ref(new Date().toISOString().slice(0, 10))

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const res         = await settlementService.list({ page: currentPage.value, ...params })
      settlements.value = res.data.data.data
      total.value       = res.data.data.total
      lastPage.value    = res.data.data.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchPendingSummary(date) {
    if (date) pendingDate.value = date
    loading.value = true
    try {
      const res         = await settlementService.pendingSummary(pendingDate.value)
      pendingSummary.value = res.data.data.summary
    } finally {
      loading.value = false
    }
  }

  async function validate(data) {
    const res = await settlementService.validate(data)
    return res.data
  }

  function setPage(page) { currentPage.value = page }

  return { settlements, total, currentPage, lastPage, loading,
           pendingSummary, pendingDate,
           fetchAll, fetchPendingSummary, validate, setPage }
})
