<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchShifts } from '@/api/shifts'
import { useFilteredShiftTabs } from '@/composables/useShiftSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'shifts.list' } })

const shiftTabs = useFilteredShiftTabs()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const shifts = ref([])
const loading = ref(true)

const headers = [
  { title: 'Fecha operativa', key: 'business_date' },
  { title: 'Tipo', key: 'shift_type_label' },
  { title: 'Estado', key: 'status' },
  { title: 'Ventas', key: 'total_sales' },
  { title: 'Diferencia', key: 'cash_difference' },
  { title: 'Apertura', key: 'opened_by_name' },
  { title: 'Cierre', key: 'closed_by_name' },
  { title: '', key: 'actions', sortable: false },
]

onMounted(async () => {
  try {
    shifts.value = await fetchShifts()
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
      title="Historial de turnos"
      subtitle="Turnos oficiales cerrados y abiertos de la sucursal."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Turnos', to: { name: 'nightpos-shifts' } },
        { title: 'Historial', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="shiftTabs" />

    <VCard>
      <VDataTable
        :headers="headers"
        :items="shifts"
        :loading="loading"
        item-value="id"
        class="text-no-wrap"
      >
        <template #item.total_sales="{ item }">
          {{ item.total_sales ?? '—' }}
        </template>
        <template #item.cash_difference="{ item }">
          {{ item.cash_difference ?? '—' }}
        </template>
        <template #item.opened_by_name="{ item }">
          {{ item.opened_by_name || '—' }}
        </template>
        <template #item.closed_by_name="{ item }">
          {{ item.closed_by_name || '—' }}
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="tonal"
            prepend-icon="ri-printer-line"
            @click="openPrintRoute({ name: 'nightpos-print-shift-id', params: { id: item.id } })"
          >
            Ver cierre
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
