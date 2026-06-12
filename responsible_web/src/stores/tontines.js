import { defineStore } from 'pinia'
import { ref } from 'vue'
import { tontineService } from '@/services/tontineService'

export const useTontinesStore = defineStore('tontines', () => {
  const tontines    = ref([])
  const total       = ref(0)
  const currentPage = ref(1)
  const lastPage    = ref(1)
  const loading     = ref(false)
  const selected    = ref(null)

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const res       = await tontineService.list({ page: currentPage.value, ...params })
      tontines.value  = res.data.data.data
      total.value     = res.data.data.total
      lastPage.value  = res.data.data.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const res      = await tontineService.show(id)
      selected.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  async function create(data)     { return (await tontineService.create(data)).data }
  async function update(id, data) { return (await tontineService.update(id, data)).data }
  async function destroy(id)      { await tontineService.destroy(id); tontines.value = tontines.value.filter(t => t.id !== id) }
  async function addParticipant(id, data)             { return (await tontineService.addParticipant(id, data)).data }
  async function removeParticipant(tid, pid)          { return (await tontineService.removeParticipant(tid, pid)).data }

  function setPage(page) { currentPage.value = page }

  return { tontines, total, currentPage, lastPage, loading, selected,
           fetchAll, fetchOne, create, update, destroy, addParticipant, removeParticipant, setPage }
})
