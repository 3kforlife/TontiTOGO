import { defineStore } from 'pinia'
import { ref } from 'vue'
import { memberService } from '@/services/memberService'

export const useMembersStore = defineStore('members', () => {
  const members     = ref([])
  const total       = ref(0)
  const currentPage = ref(1)
  const lastPage    = ref(1)
  const loading     = ref(false)
  const selected    = ref(null)

  async function fetchAll(params = {}) {
    loading.value = true
    try {
      const res      = await memberService.list({ page: currentPage.value, ...params })
      members.value  = res.data.data.data
      total.value    = res.data.data.total
      lastPage.value = res.data.data.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const res      = await memberService.show(id)
      selected.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  async function create(data) {
    const res = await memberService.create(data)
    return res.data
  }

  async function update(id, data) {
    const res = await memberService.update(id, data)
    return res.data
  }

  async function toggleStatus(id) {
    const res = await memberService.toggleStatus(id)
    const idx = members.value.findIndex(m => m.id === id)
    if (idx !== -1) members.value[idx] = res.data.data
    return res.data
  }

  async function destroy(id) {
    await memberService.destroy(id)
    members.value = members.value.filter(m => m.id !== id)
    total.value--
  }

  function setPage(page) { currentPage.value = page }

  return { members, total, currentPage, lastPage, loading, selected,
           fetchAll, fetchOne, create, update, toggleStatus, destroy, setPage }
})
