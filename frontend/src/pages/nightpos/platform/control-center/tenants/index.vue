<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchPlatformOperationsTenants } from '@/api/platformOperations'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'
import { usePlatformContext } from '@/composables/usePlatformContext'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permissions: ['platform.operations.view', 'admin.tenants.list'],
  },
})

const { notify } = useNightPosNotify()
const { applyContext } = usePlatformContext()
const router = useRouter()

const items = ref([])
const loading = ref(false)
const filters = ref({
  status: '',
  health: '',
  agent_offline: false,
  no_sales_today: false,
  open_cash_too_long: false,
  print_errors: false,
  search: '',
})

const STATUS_COLORS = {
  ONLINE: 'success',
  WARNING: 'warning',
  DEGRADED: 'orange',
  OFFLINE: 'secondary',
  CRITICAL: 'error',
}

const headers = [
  { title: 'Cliente', key: 'tenant_name' },
  { title: 'Estado', key: 'operational_status' },
  { title: 'Health', key: 'health_score' },
  { title: 'Última actividad', key: 'last_activity_at' },
  { title: 'Ventas hoy', key: 'sales_today' },
  { title: 'Impresoras', key: 'print_devices' },
  { title: 'Problema', key: 'main_issue' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const load = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.status)
      params.status = filters.value.status
    if (filters.value.health)
      params.health = filters.value.health
    if (filters.value.agent_offline)
      params.agent_offline = 1
    if (filters.value.no_sales_today)
      params.no_sales_today = 1
    if (filters.value.open_cash_too_long)
      params.open_cash_too_long = 1
    if (filters.value.print_errors)
      params.print_errors = 1
    if (filters.value.search)
      params.search = filters.value.search

    const data = await fetchPlatformOperationsTenants(params)
    items.value = data.items ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const operateAs = async item => {
  try {
    await applyContext({ tenantSlug: item.slug, branchCode: null })
    notify(`Contexto: ${item.tenant_name}`)
    await router.push({ name: 'nightpos-platform-branches' })
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
}

watch(filters, load, { deep: true })
onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Clientes operativos"
      subtitle="Estado, health score e incidencias por tenant."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Control Center', to: { name: 'nightpos-platform-control-center' } },
        { title: 'Clientes', disabled: true },
      ]"
    />

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.status"
              label="Estado operativo"
              :items="[
                { title: 'Todos', value: '' },
                { title: 'Online', value: 'ONLINE' },
                { title: 'Warning', value: 'WARNING' },
                { title: 'Degraded', value: 'DEGRADED' },
                { title: 'Offline', value: 'OFFLINE' },
                { title: 'Critical', value: 'CRITICAL' },
              ]"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.health"
              label="Health"
              :items="[
                { title: 'Todos', value: '' },
                { title: 'Alto (≥80)', value: 'high' },
                { title: 'Medio (50-79)', value: 'medium' },
                { title: 'Bajo (<50)', value: 'low' },
              ]"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="4"
          >
            <VTextField
              v-model="filters.search"
              label="Buscar cliente"
              prepend-inner-icon="ri-search-line"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
            class="d-flex flex-column gap-2"
          >
            <VCheckbox
              v-model="filters.agent_offline"
              label="Agente offline"
              density="compact"
              hide-details
            />
            <VCheckbox
              v-model="filters.no_sales_today"
              label="Sin ventas hoy"
              density="compact"
              hide-details
            />
            <VCheckbox
              v-model="filters.print_errors"
              label="Errores impresión"
              density="compact"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="items"
        item-value="tenant_id"
      >
        <template #item.tenant_name="{ item }">
          <RouterLink
            :to="{ name: 'nightpos-platform-control-center-tenants-id', params: { id: item.tenant_id } }"
            class="text-primary"
          >
            {{ item.tenant_name }}
          </RouterLink>
        </template>
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
        <template #item.last_activity_at="{ item }">
          {{ item.last_activity_at ? new Date(item.last_activity_at).toLocaleString() : '—' }}
        </template>
        <template #item.sales_today="{ item }">
          {{ formatMoney(item.sales_today ?? 0) }} BOB
        </template>
        <template #item.print_devices="{ item }">
          <span v-if="!item.print_devices_registered">Sin agente</span>
          <span v-else>{{ item.print_devices_online }}/{{ item.print_devices_registered }} online</span>
        </template>
        <template #item.main_issue="{ item }">
          {{ item.main_issue ?? '—' }}
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="text"
            :to="{ name: 'nightpos-platform-control-center-tenants-id', params: { id: item.tenant_id } }"
          >
            Detalle
          </VBtn>
          <VBtn
            size="small"
            variant="text"
            @click="operateAs(item)"
          >
            Operar
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
