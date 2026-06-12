import { defineStore } from 'pinia'
import { ref } from 'vue'
import { settingService } from '@/services/settingService'

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref({
    organization_name:        '',
    sms_template_confirmation:'',
    sms_template_reminder:    '',
    sms_reminder_time:        '17:30',
  })
  const loading = ref(false)
  const saving  = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const res    = await settingService.get()
      settings.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  async function save(data) {
    saving.value = true
    try {
      const res    = await settingService.update(data)
      settings.value = { ...settings.value, ...res.data.data }
      return res.data
    } finally {
      saving.value = false
    }
  }

  return { settings, loading, saving, fetch, save }
})
