<script setup>
import NightPosContextCards from '@/components/nightpos/layout/NightPosContextCards.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchPlatformDashboard } from '@/api/platform'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.tenants.list' } })

const { notify } = useNightPosNotify()
const dashboard = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    dashboard.value = await fetchPlatformDashboard()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
})

const cards = computed(() => {
  const c = dashboard.value?.cards ?? {}

  return [
    { title: 'Empresas activas', color: 'success', icon: 'ri-checkbox-circle-line', stats: String(c.active_tenants ?? 0), change: 0, subtitle: 'Estado active' },
    { title: 'Suspendidas', color: 'warning', icon: 'ri-pause-circle-line', stats: String(c.suspended_tenants ?? 0), change: 0, subtitle: 'Requieren revisión' },
    { title: 'Vencidas', color: 'error', icon: 'ri-time-line', stats: String(c.expired_tenants ?? 0), change: 0, subtitle: 'Suscripción expirada' },
    { title: 'Trial', color: 'info', icon: 'ri-flask-line', stats: String(c.trial_tenants ?? 0), change: 0, subtitle: 'Con vigencia futura' },
    { title: 'Total empresas', color: 'primary', icon: 'ri-building-4-line', stats: String(c.total_tenants ?? 0), change: 0, subtitle: 'Tenants en plataforma' },
  ]
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Dashboard SaaS"
      subtitle="Vista global de la plataforma NightPOS."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Dashboard', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />
    <NightPosContextCards />
    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />
    <VRow v-else>
      <VCol
        v-for="card in cards"
        :key="card.title"
        cols="12"
        sm="6"
        md="4"
        lg="2"
      >
        <CardStatisticsVertical v-bind="card" />
      </VCol>
    </VRow>

    <VCard
      v-if="dashboard?.top_plans?.length"
      class="mt-4"
    >
      <VCardItem>
        <VCardTitle>Planes más usados</VCardTitle>
      </VCardItem>
      <VCardText>
        <VList density="compact">
          <VListItem
            v-for="plan in dashboard.top_plans"
            :key="plan.id"
            :title="plan.name"
            :subtitle="plan.code"
          >
            <template #append>
              <VChip size="small">
                {{ plan.tenants_count }} empresas
              </VChip>
            </template>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>

    <VCard class="mt-4">
      <VCardItem>
        <VCardTitle>Accesos rápidos</VCardTitle>
      </VCardItem>
      <VCardText class="d-flex flex-wrap gap-2">
        <VBtn :to="{ name: 'nightpos-platform-tenants' }">
          Empresas
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-tenants-create' }"
        >
          Crear empresa
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-plans' }"
        >
          Planes
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-branches' }"
        >
          Sucursales
        </VBtn>
      </VCardText>
    </VCard>
  </div>
</template>
