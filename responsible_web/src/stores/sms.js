import { defineStore } from 'pinia'
import { ref } from 'vue'
import { smsService } from '@/services/smsService'

export const useSmsStore = defineStore('sms', () => {
  const logs        = ref([])
  const total       = ref(0)
  const currentPage = ref(1)
  const lastPage    = ref(1)
  const loading     = ref(false)
  const sending     = ref(false)
  const stats       = ref({ sent: 0, failed: 0 })
  const selected    = ref(null)

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const res       = await smsService.list({ page: currentPage.value, ...params })
      logs.value      = res.data.data.data
      total.value     = res.data.data.total
      lastPage.value  = res.data.data.last_page
      stats.value     = res.data.data.stats
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    const res      = await smsService.show(id)
    selected.value = res.data.data
  }

  async function sendReminders() {
    sending.value = true
    try {
      const res = await smsService.sendReminders()
      return res.data
    } finally {
      sending.value = false
    }
  }

  function setPage(page) { currentPage.value = page }

  return { logs, total, currentPage, lastPage, loading, sending, stats, selected,
           fetchAll, fetchOne, sendReminders, setPage }
})
