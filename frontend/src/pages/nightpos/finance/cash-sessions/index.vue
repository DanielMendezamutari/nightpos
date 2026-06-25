<script setup>
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import AdminForceCloseCashSessionDialog from '@/components/nightpos/finance/AdminForceCloseCashSessionDialog.vue'
import { useAdminCashSessionsList } from '@/composables/useAdminCashSessionsList'
import { useFilteredCashSessionTabs } from '@/composables/useCashSessionSectionTabs'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { formatMoney } from '@/composables/useOrderHelpers'

definePage({ meta: { permission: 'admin.cash_sessions.list' } })

const cashSessionTabs = useFilteredCashSessionTabs()
const router = useRouter()
const { can } = useNightPosPermissions()

const canForceClose = computed(() => can('admin.cash_sessions.force_close'))

const forceCloseSession = ref(null)
const showForceClose = ref(false)

const {
  loading,
  summaryLoading,
  sessions,
  summary,
  reload,
} = useAdminCashSessionsList('OPEN')

const summaryCards = computed(() => {
  const s = summary.value || {}

  return [
    { title: 'Cajas abiertas', color: 'success', icon: 'ri-safe-2-line', stats: String(s.total_open_sessions ?? 0), subtitle: 'En sucursal' },
    { title: 'Efectivo esperado', color: 'primary', icon: 'ri-money-dollar-circle-line', stats: `${formatMoney(s.expected_cash_total)} BOB`, subtitle: 'Acumulado' },
    { title: 'QR acumulado', color: 'info', icon: 'ri-qr-code-line', stats: `${formatMoney(s.total_qr)} BOB`, subtitle: 'Pagos QR' },
    { title: 'Ventas acumuladas', color: 'warning', icon: 'ri-shopping-bag-line', stats: `${formatMoney(s.total_sales)} BOB`, subtitle: 'Total ventas' },
  ]
})

const headers = [
  { title: 'Cajera', key: 'cashier' },
  { title: 'Sucursal', key: 'branch' },
  { title: 'Turno', key: 'shift' },
  { title: 'Apertura', key: 'opened_at' },
  { title: 'Monto inicial', key: 'opening_amount' },
  { title: 'Efectivo esperado', key: 'expected_cash' },
  { title: 'QR', key: 'total_qr' },
  { title: 'Tarjeta', key: 'total_card' },
  { title: 'Total ventas', key: 'total_sales' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const formatDate = value => value ? new Date(value).toLocaleString('es-BO') : '—'

const viewSession = id => router.push({ name: 'nightpos-finance-cash-sessions-id', params: { id } })

const openForceClose = session => {
  forceCloseSession.value = session
  showForceClose.value = true
}

const onForceClosed = () => reload()
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Fiscalización de cajas"
      subtitle="Cajas abiertas de la sucursal — control y auditoría (no operación de cobro)."
      :breadcrumbs="[
        { title: 'Finanzas' },
        { title: 'Fiscalización de cajas' },
      ]"
    />

    <NightPosSectionTabs :tabs="cashSessionTabs" />

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
            <div class="d-flex align-center gap-3">
              <VAvatar
                :color="card.color"
                variant="tonal"
                size="42"
              >
                <VIcon :icon="card.icon" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">
                  {{ card.title }}
                </div>
                <div class="text-h6">
                  {{ card.stats }}
                </div>
                <div class="text-caption">
                  {{ card.subtitle }}
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard>
      <VCardText>
        <div class="d-flex justify-end mb-4">
          <VBtn
            variant="tonal"
            :loading="loading || summaryLoading"
            @click="reload"
          >
            Actualizar
          </VBtn>
        </div>

        <VDataTable
          :headers="headers"
          :items="sessions"
          :loading="loading"
          item-value="id"
          no-data-text="No hay cajas abiertas en esta sucursal."
        >
          <template #item.cashier="{ item }">
            {{ item.cashier?.name || '—' }}
          </template>
          <template #item.branch="{ item }">
            {{ item.branch?.name || '—' }}
          </template>
          <template #item.shift="{ item }">
            <span v-if="item.official_shift">
              {{ item.official_shift.shift_type }} · {{ item.official_shift.business_date }}
            </span>
            <span v-else>—</span>
          </template>
          <template #item.opened_at="{ item }">
            {{ formatDate(item.opened_at) }}
          </template>
          <template #item.opening_amount="{ item }">
            {{ formatMoney(item.opening_amount) }}
          </template>
          <template #item.expected_cash="{ item }">
            {{ formatMoney(item.expected_cash) }}
          </template>
          <template #item.total_qr="{ item }">
            {{ formatMoney(item.total_qr) }}
          </template>
          <template #item.total_card="{ item }">
            {{ formatMoney(item.total_card) }}
          </template>
          <template #item.total_sales="{ item }">
            {{ formatMoney(item.total_sales) }}
          </template>
          <template #item.actions="{ item }">
            <VBtn
              size="small"
              variant="text"
              @click="viewSession(item.id)"
            >
              Ver
            </VBtn>
            <VBtn
              v-if="canForceClose && item.status === 'OPEN'"
              size="small"
              variant="text"
              color="error"
              @click="openForceClose(item)"
            >
              Cerrar administrativamente
            </VBtn>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <AdminForceCloseCashSessionDialog
      v-model="showForceClose"
      :session="forceCloseSession"
      @closed="onForceClosed"
    />
  </div>
</template>
