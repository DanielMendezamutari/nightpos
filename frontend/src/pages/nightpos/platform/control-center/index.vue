<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchPlatformOperationsDashboard } from '@/api/platformOperations'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permissions: ['platform.operations.view', 'admin.tenants.list'],
  },
})

const { notify } = useNightPosNotify()
const dashboard = ref(null)
const loading = ref(true)

const STATUS_COLORS = {
  ONLINE: 'success',
  WARNING: 'warning',
  DEGRADED: 'orange',
  OFFLINE: 'secondary',
  CRITICAL: 'error',
}

onMounted(async () => {
  try {
    dashboard.value = await fetchPlatformOperationsDashboard()
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
    { title: 'Clientes activos', color: 'primary', icon: 'ri-building-4-line', stats: String(c.active_tenants ?? 0), subtitle: 'Estado comercial active' },
    { title: 'Online', color: 'success', icon: 'ri-wifi-line', stats: String(c.online_tenants ?? 0), subtitle: 'Operación normal' },
    { title: 'Warning', color: 'warning', icon: 'ri-alert-line', stats: String(c.warning_tenants ?? 0), subtitle: 'Revisar pronto' },
    { title: 'Offline', color: 'secondary', icon: 'ri-cloud-off-line', stats: String(c.offline_tenants ?? 0), subtitle: 'Sin actividad' },
    { title: 'Impresoras online', color: 'success', icon: 'ri-printer-line', stats: String(c.print_devices_online ?? 0), subtitle: 'Agentes conectados' },
    { title: 'Impresoras offline', color: 'error', icon: 'ri-printer-cloud-line', stats: String(c.print_devices_offline ?? 0), subtitle: 'Sin heartbeat' },
    { title: 'Ventas hoy', color: 'info', icon: 'ri-money-dollar-circle-line', stats: `${formatMoney(c.sales_today ?? 0)} BOB`, subtitle: 'Plataforma' },
    { title: 'Tickets impresos', color: 'primary', icon: 'ri-file-list-3-line', stats: String(c.print_jobs_today ?? 0), subtitle: 'Print jobs hoy' },
    { title: 'Comandas hoy', color: 'warning', icon: 'ri-restaurant-line', stats: String(c.orders_today ?? 0), subtitle: 'Órdenes creadas' },
    { title: 'Errores críticos', color: 'error', icon: 'ri-error-warning-line', stats: String(c.critical_errors ?? 0), subtitle: 'Tenants críticos' },
  ]
})

const problemHeaders = [
  { title: 'Cliente', key: 'tenant_name' },
  { title: 'Estado', key: 'operational_status' },
  { title: 'Health', key: 'health_score' },
  { title: 'Problema', key: 'main_issue' },
  { title: 'Acción', key: 'actions', sortable: false },
]
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Ribersoft Control Center"
      subtitle="Monitoreo operativo de clientes NightPOS."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Control Center', disabled: true },
      ]"
    >
      <template #actions>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-control-center-tenants' }"
        >
          Ver clientes
        </VBtn>
        <VBtn
          variant="tonal"
          :to="{ name: 'nightpos-platform-control-center-agents' }"
        >
          Agentes impresión
        </VBtn>
      </template>
    </NightPosPageHeader>

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else>
      <VRow>
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

      <VAlert
        v-if="dashboard?.backups_status"
        type="success"
        variant="tonal"
        class="mt-4"
        title="Backups"
        :text="`Estado: ${dashboard.backups_status}`"
      />

      <VCard class="mt-4">
        <VCardItem>
          <VCardTitle>Clientes con problemas</VCardTitle>
        </VCardItem>
        <VCardText>
          <VDataTable
            :headers="problemHeaders"
            :items="dashboard?.problem_tenants ?? []"
            item-value="tenant_id"
            no-data-text="Sin clientes con problemas detectados."
          >
            <template #item.operational_status="{ item }">
              <VChip
                size="small"
                :color="STATUS_COLORS[item.operational_status] ?? 'default'"
              >
                {{ item.operational_status }}
              </VChip>
            </template>
            <template #item.health_score="{ item }">
              {{ item.health_score }}%
            </template>
            <template #item.actions="{ item }">
              <VBtn
                size="small"
                variant="text"
                :to="{ name: 'nightpos-platform-control-center-tenants-id', params: { id: item.tenant_id } }"
              >
                Ver detalle
              </VBtn>
            </template>
          </VDataTable>
        </VCardText>
      </VCard>

      <VCard
        v-if="dashboard?.versions"
        class="mt-4"
      >
        <VCardItem>
          <VCardTitle>Versiones</VCardTitle>
        </VCardItem>
        <VCardText class="d-flex flex-wrap gap-4">
          <div>
            <div class="text-caption text-medium-emphasis">
              Backend
            </div>
            <div>{{ dashboard.versions.backend_version }}</div>
          </div>
          <div>
            <div class="text-caption text-medium-emphasis">
              Frontend
            </div>
            <div>{{ dashboard.versions.frontend_version }}</div>
          </div>
        </VCardText>
      </VCard>
    </template>
  </div>
</template>
