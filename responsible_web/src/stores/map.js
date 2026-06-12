import { defineStore } from 'pinia'
import { ref } from 'vue'
import { mapService } from '@/services/mapService'

export const useMapStore = defineStore('map', () => {
  const markers = ref([])
  const count   = ref(0)
  const loading = ref(false)
  const filters = ref({ date: '', agent_id: '' })

  async function fetchMarkers() {
    loading.value = true
    try {
      const params  = Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== ''))
      const res     = await mapService.markers(params)
      markers.value = res.data.data.markers
      count.value   = res.data.data.count
    } finally {
      loading.value = false
    }
  }

  function setFilters(f) { filters.value = { ...filters.value, ...f } }

  return { markers, count, loading, filters, fetchMarkers, setFilters }
})
