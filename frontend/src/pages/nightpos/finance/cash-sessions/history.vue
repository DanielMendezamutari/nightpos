<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useAdminCashSessionsList } from '@/composables/useAdminCashSessionsList'
import { useFilteredCashSessionTabs } from '@/composables/useCashSessionSectionTabs'
import { formatMoney } from '@/composables/useOrderHelpers'

definePage({ meta: { permission: 'admin.cash_sessions.list' } })

const cashSessionTabs = useFilteredCashSessionTabs()
const router = useRouter()

const {
  loading,
  sessions,
  shifts,
  cashiers,
  filters,
  loadSessions,
} = useAdminCashSessionsList(null)

const statusOptions = [
  { title: 'Todos', value: null },
  { title: 'Abierta', value: 'OPEN' },
  { title: 'Cerrada', value: 'CLOSED' },
]

const headers = [
  { title: 'Fecha', key: 'opened_at' },
  { title: 'Cajera', key: 'cashier' },
  { title: 'Sucursal', key: 'branch' },
  { title: 'Estado', key: 'status' },
  { title: 'Monto inicial', key: 'opening_amount' },
  { title: 'Contado', key: 'counted_cash' },
  { title: 'Diferencia', key: 'cash_difference' },
  { title: 'Total ventas', key: 'total_sales' },
  { title: 'Total egresos', key: 'total_manual_expense' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const formatDate = value => value ? new Date(value).toLocaleString('es-BO') : '—'

const applyFilters = () => loadSessions()

const resetFilters = () => {
  filters.value = {
    date_from: '',
    date_to: '',
    official_shift_id: null,
    cashier_user_id: null,
    status: null,
  }
  loadSessions()
}

const viewSession = id => router.push({ name: 'nightpos-finance-cash-sessions-id', params: { id } })

onMounted(() => {
  const query = router.currentRoute.value.query

  if (query.cashier_user_id) {
    filters.value.cashier_user_id = Number(query.cashier_user_id)
  }

  if (query.official_shift_id) {
    filters.value.official_shift_id = Number(query.official_shift_id)
  }
})
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Historial de cajas"
      subtitle="Sesiones abiertas y cerradas con filtros de auditoría."
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas', to: { name: 'nightpos-finance-cash-sessions' } },
        { title: 'Historial' },
      ]"
    />

    <NightPosSectionTabs :tabs="cashSessionTabs" />

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
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
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
            md="2"
          >
            <VSelect
              v-model="filters.cashier_user_id"
              :items="cashiers"
              item-title="name"
              item-value="id"
              label="Cajera"
              clearable
              density="compact"
            />
          </VCol>
          <VCol
            cols="12"
            md="2"
          >
            <VSelect
              v-model="filters.official_shift_id"
              :items="shifts"
              :item-title="s => `${s.shift_type} · ${s.business_date}`"
              item-value="id"
              label="Turno"
              clearable
              density="compact"
            />
          </VCol>
        </VRow>
        <div class="d-flex gap-2 mt-2">
          <VBtn
            color="primary"
            @click="applyFilters"
          >
            Filtrar
          </VBtn>
          <VBtn
            variant="tonal"
            @click="resetFilters"
          >
            Limpiar
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <VCard>
      <VCardText>
        <VDataTable
          :headers="headers"
          :items="sessions"
          :loading="loading"
          item-value="id"
        >
          <template #item.opened_at="{ item }">
            {{ formatDate(item.opened_at) }}
          </template>
          <template #item.cashier="{ item }">
            {{ item.cashier?.name || '—' }}
          </template>
          <template #item.branch="{ item }">
            {{ item.branch?.name || '—' }}
          </template>
          <template #item.status="{ item }">
            <div class="d-flex flex-wrap gap-1">
              <VChip
                :color="item.status === 'OPEN' ? 'success' : 'secondary'"
                size="small"
              >
                {{ item.status === 'OPEN' ? 'Abierta' : 'Cerrada' }}
              </VChip>
              <VChip
                v-if="item.is_forced_close"
                color="warning"
                size="small"
                label
              >
                Cierre administrativo
              </VChip>
            </div>
          </template>
          <template #item.opening_amount="{ item }">
            {{ formatMoney(item.opening_amount) }}
          </template>
          <template #item.counted_cash="{ item }">
            {{ item.is_forced_close ? 'Sin arqueo' : (item.counted_cash != null ? formatMoney(item.counted_cash) : '—') }}
          </template>
          <template #item.cash_difference="{ item }">
            {{ item.is_forced_close ? '—' : (item.cash_difference != null ? formatMoney(item.cash_difference) : '—') }}
          </template>
          <template #item.total_sales="{ item }">
            {{ formatMoney(item.total_sales) }}
          </template>
          <template #item.total_manual_expense="{ item }">
            {{ formatMoney(item.total_manual_expense) }}
          </template>
          <template #item.actions="{ item }">
            <VBtn
              size="small"
              variant="text"
              @click="viewSession(item.id)"
            >
              Ver
            </VBtn>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>
  </div>
</template>
