<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { fetchSettlementHistory } from '@/api/settlements'
import { fetchShifts, fetchCurrentShift } from '@/api/shifts'
import { useOnContextChange } from '@/composables/useOnContextChange'
import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'settlements.history' } })

const settlementTabs = useFilteredSettlementTabs()
const { notify } = useNightPosNotify()
const router = useRouter()

const loading = ref(true)
const settlements = ref([])
const shifts = ref([])
const currentShift = ref(null)

const filters = ref({
  date_from: '',
  date_to: '',
  official_shift_id: null,
  staff_user_id: '',
  settlement_type: null,
  status: null,
})

const typeOptions = [
  { title: 'Todos', value: null },
  { title: 'Garzones', value: 'WAITER' },
  { title: 'Chicas', value: 'GIRL' },
]

const statusOptions = [
  { title: 'Todos', value: null },
  { title: 'Pendiente', value: 'PENDING' },
  { title: 'Pagado', value: 'PAID' },
  { title: 'Cancelado', value: 'CANCELLED' },
]

const headers = [
  { title: 'Fecha', key: 'created_at' },
  { title: 'Turno', key: 'shift_name' },
  { title: 'Personal', key: 'staff_name' },
  { title: 'Tipo', key: 'settlement_type' },
  { title: 'Corte', key: 'cut_label' },
  { title: 'Total', key: 'total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Pagado por', key: 'paid_by_name' },
  { title: 'Fecha pago', key: 'paid_at' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const statusColor = status => ({
  PENDING: 'warning',
  PAID: 'success',
  CANCELLED: 'secondary',
}[status] || 'default')

const typeLabel = type => ({
  WAITER: 'Garzón',
  GIRL: 'Chica',
}[type] || type)

const load = async () => {
  loading.value = true

  try {
    const params = {
      date_from: filters.value.date_from || undefined,
      date_to: filters.value.date_to || undefined,
      official_shift_id: filters.value.official_shift_id || undefined,
      staff_user_id: filters.value.staff_user_id ? Number(filters.value.staff_user_id) : undefined,
      settlement_type: filters.value.settlement_type || undefined,
      status: filters.value.status || undefined,
    }

    const data = await fetchSettlementHistory(params)

    settlements.value = data.settlements ?? []
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    official_shift_id: currentShift.value?.id ?? null,
    staff_user_id: '',
    settlement_type: null,
    status: null,
  }
  load()
}

onMounted(async () => {
  try {
    const [shiftList, shift] = await Promise.allSettled([fetchShifts(), fetchCurrentShift()])
    shifts.value = shiftList.status === 'fulfilled' ? (shiftList.value ?? []) : []
    currentShift.value = shift.status === 'fulfilled' ? (shift.value ?? null) : null
  }
  catch {
    shifts.value = []
    currentShift.value = null
  }

  if (currentShift.value?.id) {
    filters.value.official_shift_id = currentShift.value.id
  }

  await load()
})

useOnContextChange(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Historial de liquidaciones"
      subtitle="Liquidaciones filtradas por turno. Por defecto se muestra el turno activo."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Historial', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="settlementTabs" />

    <VAlert
      v-if="currentShift"
      type="info"
      variant="tonal"
      density="compact"
      class="mb-3"
      :text="`Turno activo: ${currentShift.name} — ${currentShift.shift_type === 'NIGHT' ? 'Noche' : 'Día'} · ${currentShift.business_date}. Mostrando liquidaciones de este turno por defecto. Usa los filtros para ver otros turnos.`"
    />

    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="filters.date_from"
              label="Desde"
              type="date"
              density="compact"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="filters.date_to"
              label="Hasta"
              type="date"
              density="compact"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.official_shift_id"
              :items="shifts"
              item-title="name"
              item-value="id"
              label="Turno"
              density="compact"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VTextField
              v-model="filters.staff_user_id"
              label="ID personal"
              type="number"
              density="compact"
              clearable
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.settlement_type"
              :items="typeOptions"
              item-title="title"
              item-value="value"
              label="Tipo"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="3"
          >
            <VSelect
              v-model="filters.status"
              :items="statusOptions"
              item-title="title"
              item-value="value"
              label="Estado"
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="6"
            class="d-flex align-center gap-2"
          >
            <VBtn
              color="primary"
              prepend-icon="ri-filter-line"
              :loading="loading"
              @click="load"
            >
              Filtrar
            </VBtn>
            <VBtn
              variant="tonal"
              @click="resetFilters"
            >
              Limpiar
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard>
      <VDataTable
        :headers="headers"
        :items="settlements"
        :loading="loading"
        :items-per-page="15"
        class="text-no-wrap"
      >
        <template #item.settlement_type="{ item }">
          <VChip
            size="small"
            variant="tonal"
          >
            {{ typeLabel(item.settlement_type) }}
          </VChip>
        </template>
        <template #item.status="{ item }">
          <VChip
            size="small"
            :color="statusColor(item.status)"
            variant="tonal"
          >
            {{ item.status }}
          </VChip>
        </template>
        <template #item.paid_by_name="{ item }">
          {{ item.paid_by_name || '—' }}
        </template>
        <template #item.paid_at="{ item }">
          {{ item.paid_at || '—' }}
        </template>
        <template #item.actions="{ item }">
          <VBtn
            size="small"
            variant="text"
            @click="router.push({ name: 'nightpos-settlements-id', params: { id: item.id } })"
          >
            Detalle
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
