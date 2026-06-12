import { defineStore } from 'pinia'
import { ref } from 'vue'
import { contributionService } from '@/services/contributionService'

export const useContributionsStore = defineStore('contributions', () => {
  const contributions = ref([])
  const total         = ref(0)
  const currentPage   = ref(1)
  const lastPage      = ref(1)
  const loading       = ref(false)
  const exporting     = ref(false)
  const filters       = ref({ date: '', agent_id: '', member_id: '', tontine_id: '' })

  async function fetchAll() {
    loading.value = true
    try {
      const params = { page: currentPage.value, ...cleanFilters() }
      const res    = await contributionService.list(params)
      contributions.value = res.data.data.data
      total.value         = res.data.data.total
      lastPage.value      = res.data.data.last_page
    } finally {
      loading.value = false
    }
  }

  async function exportFile(type) {
    exporting.value = true
    try {
      const params = cleanFilters()
      const res    = type === 'pdf'
        ? await contributionService.exportPdf(params)
        : await contributionService.exportExcel(params)

      const ext  = type === 'pdf' ? 'pdf' : 'xlsx'
      const mime = type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      const url  = URL.createObjectURL(new Blob([res.data], { type: mime }))
      const a    = document.createElement('a')
      a.href     = url
      a.download = `cotisations-${new Date().toISOString().slice(0,10)}.${ext}`
      a.click()
      URL.revokeObjectURL(url)
    } finally {
      exporting.value = false
    }
  }

  function cleanFilters() {
    return Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== ''))
  }

  function setPage(page)    { currentPage.value = page }
  function setFilters(f)    { filters.value = { ...filters.value, ...f } }
  function resetFilters()   { filters.value = { date: '', agent_id: '', member_id: '', tontine_id: '' }; currentPage.value = 1 }

  return { contributions, total, currentPage, lastPage, loading, exporting, filters,
           fetchAll, exportFile, setPage, setFilters, resetFilters }
})
