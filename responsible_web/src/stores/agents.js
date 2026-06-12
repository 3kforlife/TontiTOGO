import { defineStore } from 'pinia'
import { ref } from 'vue'
import { agentService } from '@/services/agentService'

export const useAgentsStore = defineStore('agents', () => {
  const agents      = ref([])
  const total       = ref(0)
  const currentPage = ref(1)
  const lastPage    = ref(1)
  const loading     = ref(false)
  const selected    = ref(null)
  const stats       = ref(null)

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const res     = await agentService.list({ page: currentPage.value, ...params })
      agents.value  = res.data.data.data
      total.value   = res.data.data.total
      lastPage.value= res.data.data.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const res   = await agentService.show(id)
      selected.value = res.data.data.agent
      stats.value    = res.data.data.stats
    } finally {
      loading.value = false
    }
  }

  async function create(data) {
    const res = await agentService.create(data)
    return res.data
  }

  async function update(id, data) {
    const res = await agentService.update(id, data)
    return res.data
  }

  async function toggleStatus(id) {
    const res = await agentService.toggleStatus(id)
    const idx = agents.value.findIndex(a => a.id === id)
    if (idx !== -1) agents.value[idx] = res.data.data
    return res.data
  }

  async function destroy(id) {
    await agentService.destroy(id)
    agents.value = agents.value.filter(a => a.id !== id)
    total.value--
  }

  function setPage(page) {
    currentPage.value = page
  }

  return { agents, total, currentPage, lastPage, loading, selected, stats,
           fetchAll, fetchOne, create, update, toggleStatus, destroy, setPage }
})
