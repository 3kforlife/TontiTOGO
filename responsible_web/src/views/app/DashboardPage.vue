<script setup>
import { onMounted } from 'vue'
import { useDashboardStore }  from '@/stores/dashboard'
import KpiCard                from '@/components/dashboard/KpiCard.vue'
import ChartDailyFlux         from '@/components/dashboard/ChartDailyFlux.vue'
import ChartAgentsPerformance from '@/components/dashboard/ChartAgentsPerformance.vue'
import ChartMemberPortfolio   from '@/components/dashboard/ChartMemberPortfolio.vue'
import ChartCollectionZone    from '@/components/dashboard/ChartCollectionZone.vue'

const store = useDashboardStore()

onMounted(() => store.fetch())

function fmt(v) {
  if (v === null || v === undefined) return '—'
  return new Intl.NumberFormat('fr-FR').format(v) + ' F'
}
</script>

<template>
  <div class="space-y-6">

    <!-- Titre -->
    <div>
      <h1 class="text-xl font-bold text-gray-900">Tableau de bord</h1>
      <p class="text-sm text-gray-400 mt-0.5">Vue d'ensemble de votre activité</p>
    </div>

    <!-- Skeletons -->
    <div v-if="store.loading" class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="card h-24 animate-pulse bg-gray-50" />
    </div>

    <!-- KPIs -->
    <div v-else-if="store.kpis" class="grid grid-cols-2 lg:grid-cols-3 gap-4">

      <!-- A. Caisse totale du jour -->
      <KpiCard
        label="Caisse du jour"
        :value="fmt(store.kpis.total_collected_today)"
        icon="cash"
        color="green"
      />

      <!-- E. Volume total du mois -->
      <KpiCard
        label="Volume du mois"
        :value="fmt(store.kpis.total_collected_month)"
        icon="trending"
        color="blue"
      />

      <!-- En attente de versement -->
      <KpiCard
        label="En attente de versement"
        :value="fmt(store.kpis.pending_settlement)"
        icon="clock"
        color="yellow"
      />

      <!-- B. Agents actifs terrain aujourd'hui -->
      <div class="card flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-cyan-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.582-7 8-7s8 3 8 7"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-400 font-medium truncate">Agents terrain aujourd'hui</p>
          <p class="text-xl font-bold text-gray-900 mt-0.5">
            {{ store.kpis.agents_active_today }}
            <span class="text-sm font-normal text-gray-400">/ {{ store.kpis.total_agents }}</span>
          </p>
        </div>
      </div>

      <!-- C. Membres actifs -->
      <KpiCard
        label="Membres actifs"
        :value="store.kpis.total_active_members"
        icon="users"
        color="purple"
      />

      <!-- F. SMS échoués -->
      <div class="card flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
             :class="store.kpis.sms_failed_count > 0 ? 'bg-red-50' : 'bg-gray-50'">
          <svg class="w-5 h-5" :class="store.kpis.sms_failed_count > 0 ? 'text-red-500' : 'text-gray-400'"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-400 font-medium">Alertes SMS échoués</p>
          <p class="text-xl font-bold mt-0.5"
             :class="store.kpis.sms_failed_count > 0 ? 'text-red-600' : 'text-gray-900'">
            {{ store.kpis.sms_failed_count }}
          </p>
        </div>
      </div>

    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <ChartDailyFlux   :charts="store.charts" :loading="store.loading" />
      <ChartAgentsPerformance :loading="store.loading" />
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <ChartMemberPortfolio :loading="store.loading" />
      <ChartCollectionZone  :loading="store.loading" />
    </div>

    <!-- Activité récente -->
    <div class="card">
      <h2 class="text-sm font-semibold text-gray-900 mb-4">Dernières cotisations</h2>
      <div v-if="store.loading" class="space-y-3">
        <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 rounded-lg animate-pulse" />
      </div>
      <div v-else-if="!store.recentActivity.length" class="text-center py-8 text-gray-400 text-sm">
        Aucune cotisation enregistrée pour le moment.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs text-gray-400 uppercase border-b border-gray-50">
              <th class="pb-2 text-left font-medium">Référence</th>
              <th class="pb-2 text-left font-medium hidden sm:table-cell">Membre</th>
              <th class="pb-2 text-left font-medium hidden md:table-cell">Tontine</th>
              <th class="pb-2 text-left font-medium hidden lg:table-cell">Agent</th>
              <th class="pb-2 text-right font-medium">Montant</th>
              <th class="pb-2 text-right font-medium hidden sm:table-cell">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="c in store.recentActivity" :key="c.reference" class="hover:bg-gray-50/50">
              <td class="py-2.5 font-mono text-xs text-gray-500">{{ c.reference }}</td>
              <td class="py-2.5 text-gray-800 hidden sm:table-cell">{{ c.member }}</td>
              <td class="py-2.5 text-gray-500 hidden md:table-cell">{{ c.tontine }}</td>
              <td class="py-2.5 text-gray-500 hidden lg:table-cell">{{ c.agent }}</td>
              <td class="py-2.5 text-right font-semibold text-gray-900">
                {{ new Intl.NumberFormat('fr-FR').format(c.amount) }} F
              </td>
              <td class="py-2.5 text-right text-gray-400 text-xs hidden sm:table-cell">{{ c.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>
