<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchAuditLogs } from '@/api/auditLogs'
import { SETTINGS_SECTION_TABS } from '@/composables/useSettingsSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'audits.list' } })

const ACTION_LABELS = {
  'product_price.replaced': 'Precio actualizado',
  'cash_session.closed': 'Caja cerrada',
  'cash_session.force_closed': 'Cierre administrativo de caja',
  'official_shift.closed': 'Turno cerrado',
  'sale.charged': 'Venta cobrada',
}

const { notify } = useNightPosNotify()
const loading = ref(false)
const logs = ref([])

const headers = [
  { title: 'Fecha', key: 'created_at' },
  { title: 'Acción', key: 'action' },
  { title: 'Usuario', key: 'user' },
  { title: 'Detalle', key: 'metadata' },
]

const load = async () => {
  loading.value = true
  try {
    logs.value = await fetchAuditLogs()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const actionLabel = action => ACTION_LABELS[action] ?? action

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Bitácora de auditoría"
      subtitle="Acciones sensibles: precios, cobros, cierres de caja y turno."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Configuración', disabled: true },
        { title: 'Auditoría', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="SETTINGS_SECTION_TABS" />
    <VCard>
      <VDataTable
        :headers="headers"
        :items="logs"
        :loading="loading"
        item-value="id"
      >
        <template #item.created_at="{ item }">
          {{ item.created_at ? new Date(item.created_at).toLocaleString() : '—' }}
        </template>
        <template #item.action="{ item }">
          {{ actionLabel(item.action) }}
        </template>
        <template #item.user="{ item }">
          {{ item.user?.name ?? '—' }}
        </template>
        <template #item.metadata="{ item }">
          <span class="text-caption">{{ JSON.stringify(item.metadata ?? {}) }}</span>
        </template>
      </VDataTable>
    </VCard>
</div>
</template>
