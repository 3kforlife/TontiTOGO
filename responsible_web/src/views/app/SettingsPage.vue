<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useAuthStore }     from '@/stores/auth'
import { useToast }         from '@/composables/useToast'

const store     = useSettingsStore()
const authStore = useAuthStore()
const toast     = useToast()

const form = reactive({
  organization_name: '',
  sms_reminder_time: '17:30',
})

const saveStatus  = ref('') // '' | 'success' | 'error'
const saveMessage = ref('')

function syncForm() {
  form.organization_name = store.settings.organization_name || authStore.organizationName || ''
  form.sms_reminder_time = store.settings.sms_reminder_time || '17:30'
}

async function saveSettings() {
  saveStatus.value  = ''
  saveMessage.value = ''
  try {
    const res = await store.save({
      organization_name: form.organization_name,
      sms_reminder_time: form.sms_reminder_time,
    })
    saveStatus.value  = 'success'
    saveMessage.value = res.message || 'Paramètres enregistrés avec succès.'
    toast.success(saveMessage.value)
  } catch (err) {
    saveStatus.value  = 'error'
    saveMessage.value = err.response?.data?.message || "Erreur lors de l'enregistrement."
    toast.error(saveMessage.value)
  }
}

onMounted(async () => {
  await store.fetch()
  syncForm()
})
</script>

<template>
  <div class="space-y-6 max-w-2xl">

    <!-- Titre -->
    <div>
      <h1 class="text-xl font-bold text-gray-900">Paramètres</h1>
      <p class="text-sm text-gray-400 mt-0.5">Configuration de votre organisation</p>
    </div>

    <div v-if="store.loading" class="space-y-4">
      <div v-for="i in 2" :key="i" class="card p-5 h-24 animate-pulse bg-gray-50" />
    </div>

    <template v-else>

      <!-- Section 1 — Nom de l'organisation -->
      <div class="card p-5 space-y-4">
        <div>
          <h2 class="font-semibold text-gray-800 text-sm">Organisation</h2>
          <p class="text-xs text-gray-400 mt-0.5">Ce nom apparaîtra dans les SMS envoyés à vos membres.</p>
        </div>
        <div>
          <label class="form-label">Nom de l'organisation <span class="text-red-500">*</span></label>
          <input v-model="form.organization_name" class="form-input max-w-sm" placeholder="Mon Organisation" />
        </div>
      </div>

      <!-- Section 2 — SMS de confirmation (info) -->
      <div class="card p-5 space-y-3">
        <h2 class="font-semibold text-gray-800 text-sm">SMS de confirmation</h2>
        <p class="text-xs text-gray-400">Envoyé automatiquement à chaque membre après enregistrement d'une cotisation.</p>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-xs text-gray-500 font-mono leading-relaxed">
            <span class="font-semibold text-gray-700">[Nom Organisation]</span> : Bonjour
            <span class="font-semibold text-gray-700">[Nom Membre]</span>, votre cotisation de
            <span class="font-semibold text-gray-700">[Montant]</span> FCFA pour aujourd'hui a bien été
            enregistrée. Réf:
            <span class="font-semibold text-gray-700">[Référence]</span>.
          </p>
        </div>
        <p class="text-xs text-primary-600 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Message fixe — les données sont remplies automatiquement depuis votre organisation.
        </p>
      </div>

      <!-- Section 3 — SMS de rappel (info + heure) -->
      <div class="card p-5 space-y-4">
        <div>
          <h2 class="font-semibold text-gray-800 text-sm">SMS de rappel automatique</h2>
          <p class="text-xs text-gray-400 mt-0.5">Envoyé chaque jour à l'heure choisie aux membres n'ayant pas encore cotisé.</p>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
          <p class="text-xs text-gray-500 font-mono leading-relaxed">
            <span class="font-semibold text-gray-700">[Nom Organisation]</span> Bonjour
            <span class="font-semibold text-gray-700">[Nom Membre]</span>, nous vous rappelons que
            votre cotisation du jour est attendue. Merci.
          </p>
        </div>
        <div>
          <label class="form-label">Heure d'envoi des rappels <span class="text-red-500">*</span></label>
          <input v-model="form.sms_reminder_time" type="time" class="form-input w-36" />
          <p class="text-xs text-gray-400 mt-1">Les rappels seront envoyés chaque jour à cette heure.</p>
        </div>
      </div>

      <!-- Feedback -->
      <div v-if="saveStatus === 'success'" class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        {{ saveMessage }}
      </div>
      <div v-else-if="saveStatus === 'error'" class="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ saveMessage }}
      </div>

      <!-- Bouton -->
      <div class="flex justify-end">
        <button class="btn-primary px-8" :disabled="store.saving" @click="saveSettings">
          <svg v-if="store.saving" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ store.saving ? 'Enregistrement...' : 'Enregistrer les paramètres' }}
        </button>
      </div>

    </template>
  </div>
</template>
