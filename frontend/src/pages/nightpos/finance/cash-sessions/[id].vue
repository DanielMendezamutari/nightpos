<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import AdminForceCloseCashSessionDialog from '@/components/nightpos/finance/AdminForceCloseCashSessionDialog.vue'
import AdminForcedCloseSessionPanel from '@/components/nightpos/finance/AdminForcedCloseSessionPanel.vue'
import { fetchAdminCashSession } from '@/api/adminCashSessions'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useNightPosPrint } from '@/composables/useNightPosPrint'
import { formatMoney } from '@/composables/useOrderHelpers'
import { getApiErrorMessage } from '@/services/http'

definePage({ meta: { permission: 'admin.cash_sessions.view' } })

const route = useRoute('nightpos-finance-cash-sessions-id')
const router = useRouter()
const { notify } = useNightPosNotify()
const { openPrintRoute } = useNightPosPrint()
const { can } = useNightPosPermissions()

const canForceClose = computed(() => can('admin.cash_sessions.force_close'))
const showForceClose = ref(false)

const loading = ref(true)
const session = ref(null)
const summary = ref(null)
const movements = ref([])
const sales = ref([])
const settlementsPaid = ref([])

const movementHeaders = [
  { title: 'Tipo', key: 'movement_type' },
  { title: 'Descripción', key: 'description' },
  { title: 'Método', key: 'payment_method' },
  { title: 'Monto', key: 'amount' },
  { title: 'Hora', key: 'created_at' },
]

const saleHeaders = [
  { title: 'Venta', key: 'sale_number' },
  { title: 'Total', key: 'total' },
  { title: 'Modo pago', key: 'payment_mode' },
  { title: 'Fecha', key: 'paid_at' },
]

const settlementHeaders = [
  { title: 'Tipo', key: 'settlement_type' },
  { title: 'Personal', key: 'staff_name' },
  { title: 'Monto', key: 'total_amount' },
  { title: 'Pagado por', key: 'paid_by_name' },
  { title: 'Fecha', key: 'paid_at' },
]

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Efectivo esperado', value: formatMoney(s.expected_cash), color: 'primary' },
    { title: 'Efectivo ventas', value: formatMoney(s.total_cash), color: 'success' },
    { title: 'QR', value: formatMoney(s.total_qr), color: 'info' },
    { title: 'Tarjeta', value: formatMoney(s.total_card), color: 'warning' },
    { title: 'Ventas totales', value: formatMoney(s.total_sales), color: 'success' },
    { title: 'Ingresos manuales', value: formatMoney(s.total_manual_income), color: 'success' },
    { title: 'Egresos', value: formatMoney(s.total_manual_expense), color: 'error' },
    { title: 'Diferencia', value: session.value?.is_forced_close ? 'Sin arqueo — cierre administrativo' : (s.cash_difference != null ? formatMoney(s.cash_difference) : '—'), color: 'error' },
  ]
})

const formatDate = value => value ? new Date(value).toLocaleString('es-BO') : '—'

const load = async () => {
  loading.value = true

  try {
    const data = await fetchAdminCashSession(route.params.id)

    session.value = data.session
    summary.value = data.summary
    movements.value = data.movements ?? []
    sales.value = data.sales ?? []
    settlementsPaid.value = data.settlements_paid ?? []
  }
  catch (error) {
    if (import.meta.env.DEV) {
      console.error('[admin/cash-sessions/:id]', error?.response?.status, error?.response?.data?.message ?? error)
    }
    notify(getApiErrorMessage(error), 'error')
    router.replace({ name: 'nightpos-finance-cash-sessions' })
  }
  finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Detalle de caja"
      :subtitle="session ? `Sesión #${session.id} · ${session.cashier?.name || 'Cajera'}` : 'Cargando...'"
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas', to: { name: 'nightpos-finance-cash-sessions' } },
        { title: 'Detalle' },
      ]"
    >
      <template #actions>
        <VBtn
          v-if="session && canForceClose && session.status === 'OPEN'"
          color="error"
          variant="tonal"
          prepend-icon="ri-shield-keyhole-line"
          class="me-2"
          @click="showForceClose = true"
        >
          Cerrar administrativamente
        </VBtn>
        <VBtn
          v-if="session"
          variant="tonal"
          prepend-icon="ri-printer-line"
          @click="openPrintRoute({ name: 'nightpos-print-cash-session-id', params: { id: session.id } })"
        >
          Ver cierre imprimible
        </VBtn>
      </template>
    </NightPosPageHeader>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <template v-else-if="session">
      <AdminForcedCloseSessionPanel
        :session="session"
        :summary="summary"
      />

      <VCard class="mb-4">
        <VCardText>
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <div class="d-flex align-center gap-2 mb-1">
                <strong>Estado:</strong>
                <span>{{ session.status === 'OPEN' ? 'Abierta' : 'Cerrada' }}</span>
                <VChip
                  v-if="session.is_forced_close"
                  color="warning"
                  size="x-small"
                  label
                >
                  Cierre administrativo
                </VChip>
              </div>
              <div><strong>Sucursal:</strong> {{ session.branch?.name }}</div>
              <div><strong>Turno:</strong>
                <span v-if="session.official_shift">{{ session.official_shift.shift_type }} · {{ session.official_shift.business_date }}</span>
                <span v-else>—</span>
              </div>
              <div><strong>Apertura:</strong> {{ formatDate(session.opened_at) }}</div>
              <div v-if="session.closed_at"><strong>Cierre:</strong> {{ formatDate(session.closed_at) }}</div>
            </VCol>
            <VCol
              cols="12"
              md="6"
            >
              <div><strong>Monto inicial:</strong> {{ formatMoney(session.opening_amount) }} BOB</div>
              <div v-if="session.counted_cash != null && !session.is_forced_close"><strong>Contado:</strong> {{ formatMoney(session.counted_cash) }} BOB</div>
              <div v-else-if="session.is_forced_close"><strong>Contado:</strong> Sin arqueo — cierre administrativo</div>
              <div v-if="session.opening_notes"><strong>Notas apertura:</strong> {{ session.opening_notes }}</div>
              <div v-if="session.closing_notes"><strong>Notas cierre:</strong> {{ session.closing_notes }}</div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <VRow class="mb-4">
        <VCol
          v-for="card in summaryCards"
          :key="card.title"
          cols="12"
          sm="6"
          md="3"
        >
          <VCard>
            <VCardText>
              <div class="text-caption text-medium-emphasis">
                {{ card.title }}
              </div>
              <div
                class="text-h6"
                :class="`text-${card.color}`"
              >
                {{ card.value }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VCard class="mb-4">
        <VCardTitle>Movimientos</VCardTitle>
        <VCardText>
          <VDataTable
            :headers="movementHeaders"
            :items="movements"
            item-value="id"
            no-data-text="Sin movimientos."
          >
            <template #item.movement_type="{ item }">
              <VChip
                :color="item.movement_type === 'INCOME' ? 'success' : 'error'"
                size="small"
              >
                {{ item.movement_type === 'INCOME' ? 'Ingreso' : 'Egreso' }}
              </VChip>
            </template>
            <template #item.amount="{ item }">
              {{ formatMoney(item.amount) }}
            </template>
            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>
          </VDataTable>
        </VCardText>
      </VCard>

      <VCard class="mb-4">
        <VCardTitle>Ventas cobradas</VCardTitle>
        <VCardText>
          <VDataTable
            :headers="saleHeaders"
            :items="sales"
            item-value="id"
            no-data-text="Sin ventas en esta sesión."
          >
            <template #item.total="{ item }">
              {{ formatMoney(item.total) }}
            </template>
            <template #item.paid_at="{ item }">
              {{ formatDate(item.paid_at) }}
            </template>
          </VDataTable>
        </VCardText>
      </VCard>

      <VCard>
        <VCardTitle>Liquidaciones pagadas</VCardTitle>
        <VCardText>
          <VDataTable
            :headers="settlementHeaders"
            :items="settlementsPaid"
            item-value="id"
            no-data-text="Sin liquidaciones pagadas desde esta caja."
          >
            <template #item.total_amount="{ item }">
              {{ formatMoney(item.total_amount) }}
            </template>
            <template #item.paid_at="{ item }">
              {{ formatDate(item.paid_at) }}
            </template>
          </VDataTable>
        </VCardText>
      </VCard>
    </template>

    <AdminForceCloseCashSessionDialog
      v-model="showForceClose"
      :session="session"
      @closed="load"
    />
  </div>
</template>
