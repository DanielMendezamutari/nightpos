<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchPlatformOperationsPrintAgents } from '@/api/platformOperations'
import { PLATFORM_SECTION_TABS } from '@/composables/useStaffSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({
  meta: {
    permissions: ['platform.operations.view', 'admin.tenants.list'],
  },
})

const { notify } = useNightPosNotify()
const items = ref([])
const loading = ref(true)

const headers = [
  { title: 'Cliente', key: 'tenant_name' },
  { title: 'Sucursal', key: 'branch_name' },
  { title: 'Dispositivo', key: 'name' },
  { title: 'Impresora', key: 'printer_name' },
  { title: 'Estado', key: 'online' },
  { title: 'Versión agente', key: 'agent_version' },
  { title: 'Último seen', key: 'last_seen_at' },
  { title: 'Error', key: 'last_error' },
]

onMounted(async () => {
  try {
    const data = await fetchPlatformOperationsPrintAgents()
    items.value = data.items ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Agentes de impresión"
      subtitle="Todos los print devices registrados en la plataforma."
      :breadcrumbs="[
        { title: 'Plataforma', disabled: true },
        { title: 'Control Center', to: { name: 'nightpos-platform-control-center' } },
        { title: 'Agentes', disabled: true },
      ]"
    />

    <NightPosSectionTabs :tabs="PLATFORM_SECTION_TABS" />

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="items"
        item-value="id"
      >
        <template #item.online="{ item }">
          <VChip
            size="small"
            :color="item.online ? 'success' : 'error'"
          >
            {{ item.online ? 'Online' : 'Offline' }}
          </VChip>
        </template>
        <template #item.last_seen_at="{ item }">
          {{ item.last_seen_at ? new Date(item.last_seen_at).toLocaleString() : '—' }}
        </template>
        <template #item.last_error="{ item }">
          {{ item.last_error ?? '—' }}
        </template>
        <template #item.agent_version="{ item }">
          {{ item.agent_version ?? '—' }}
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
