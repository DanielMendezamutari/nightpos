<script setup>
import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'
import SettlementPayDialog from '@/components/nightpos/settlements/SettlementPayDialog.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import StaffFineDialog from '@/components/nightpos/settlements/StaffFineDialog.vue'
import SettlementListRowActions from '@/components/nightpos/settlements/SettlementListRowActions.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { useCurrentShiftSettlements } from '@/composables/useCurrentShiftSettlements'
import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'
import { useSettlementPayment } from '@/composables/useSettlementPayment'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'

definePage({ meta: { permission: 'settlements.access' } })

const settlementTabs = useFilteredSettlementTabs()
const router = useRouter()
const { can, canManageSettlementFines } = useNightPosPermissions()
const { loading, shift, context, girls, reload } = useCurrentShiftSettlements()
const { paySettlement, showOpenCash, refreshCashSession } = useSettlementPayment({ onPaid: reload })

const { on, start: startSse, stop: stopSse } = useOperationalEvents()

let settlementDebounce = null
const debouncedReload = () => {
  clearTimeout(settlementDebounce)
  settlementDebounce = setTimeout(reload, 600)
}

on('settlement.generated', debouncedReload)
on('settlement.paid', debouncedReload)

onMounted(() => { startSse() })
onUnmounted(() => { stopSse() })

const canPay = computed(() => can('settlements.pay'))
const paying = ref(false)
const showPayDialog = ref(false)
const showFineDialog = ref(false)
const showDetailDialog = ref(false)
const payingItem = ref(null)
const finePrefill = ref(null)
const detailItem = ref(null)
const payDialogRef = ref(null)

const headers = [
  { title: 'Chica', key: 'staff_name' },
  { title: 'Consumos cobrados', key: 'consumption_total' },
  { title: 'Piezas', key: 'pieces_total' },
  { title: 'Manillas / reparto', key: 'bracelets_total' },
  { title: 'Show', key: 'shows_total' },
  { title: 'Ajustes', key: 'adjustments_total' },
  { title: 'Total pagable', key: 'total_amount' },
  { title: 'Provisional sin cobrar', key: 'provisional_total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const statusColor = status => ({
  PENDING: 'warning',
  PAID: 'success',
  CANCELLED: 'secondary',
  PROVISIONAL: 'info',
  MIXED: 'primary',
  INFORMATIVE: 'secondary',
}[status] || 'default')

const statusLabel = status => ({
  PENDING: 'Confirmado',
  PAID: 'Pagado',
  PROVISIONAL: 'Pendiente de cobro',
  MIXED: 'Confirmado + provisional',
  INFORMATIVE: 'Informativo',
  CANCELLED: 'Cancelado',
}[status] || status || '—')

const scopeLabel = computed(() => {
  if (context.value?.scope === 'my_cash_session') {
    const ids = context.value?.settlement_official_shift_ids ?? []
    const idsLabel = ids.length ? ` (turnos incluidos: ${ids.join(', ')})` : ''

    return `Alcance operativo: mi caja actual${idsLabel}`
  }

  if (context.value?.scope === 'shift') {
    return 'Alcance operativo: turno oficial'
  }

  return null
})

const openPayDialog = async item => {
  await refreshCashSession()
  payingItem.value = item
  showPayDialog.value = true
}

const confirmPay = async ({ payment_method, notes, applied_fine_ids }) => {
  if (!payingItem.value)
    return
  paying.value = true
  try {
    const result = await paySettlement(payingItem.value.id, { payment_method, notes, applied_fine_ids })
    if (result.ok) {
      showPayDialog.value = false
      payingItem.value = null
    }
  }
  finally {
    paying.value = false
  }
}

const openFineDialog = item => {
  finePrefill.value = item
  showFineDialog.value = true
}

const openDetailDialog = item => {
  detailItem.value = item
  showDetailDialog.value = true
}

const openFineFromPay = () => {
  if (!payingItem.value)
    return
  openFineDialog(payingItem.value)
}

const onFineCreated = async () => {
  await reload()
  await payDialogRef.value?.reloadPreview?.()
}
</script>

<template>
  <div>
    <NightPosPageHeader
      title="Liquidaciones — Chicas"
      subtitle="Liquidación del turno por chica."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Chicas', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="settlementTabs" />

    <SettlementsCashBanner emphasize-pay-requirement />

    <VAlert
      v-if="canManageSettlementFines && !loading && girls.length"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Use <strong>Multar</strong> en cada fila para registrar una multa antes de pagar.
    </VAlert>

    <VAlert
      v-if="scopeLabel"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      {{ scopeLabel }}
    </VAlert>

    <VAlert
      v-if="!loading && !shift"
      type="info"
      variant="tonal"
      class="mb-4"
    >
      Sin turno clasificado. Genere liquidaciones desde el resumen cuando haya ventas cobradas.
    </VAlert>

    <VProgressLinear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <VCard v-else>
      <VDataTable
        :headers="headers"
        :items="girls"
        :items-per-page="15"
        class="text-no-wrap"
      >
        <template #item.status="{ item }">
          <VChip
            size="small"
            :color="statusColor(item.status)"
            variant="tonal"
          >
            {{ statusLabel(item.status) }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <SettlementListRowActions
            :item="item"
            :can-pay="canPay"
            :can-multar="canManageSettlementFines"
            @pay="openPayDialog"
            @multar="openFineDialog"
            @detail="openDetailDialog"
          />
        </template>
      </VDataTable>
    </VCard>

    <VDialog v-model="showDetailDialog" max-width="900">
      <VCard>
        <VCardTitle>Detalle operativo</VCardTitle>
        <VCardText>
          <div class="mb-3"><strong>{{ detailItem?.staff_name }}</strong></div>
          <div class="mb-4">
            Confirmado: {{ detailItem?.total_amount ?? '0.00' }} BOB
            <br>
            Provisional: {{ detailItem?.provisional_total_amount ?? '0.00' }} BOB
          </div>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Fuente</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Tipo</th>
                <th>Turno origen</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="detail in detailItem?.details ?? []" :key="`${detail.source_type}-${detail.source_id}`">
                <td>{{ detail.description || detail.source_type }}</td>
                <td>{{ detail.amount }} BOB</td>
                <td>{{ detail.status }}</td>
                <td>{{ detail.provisional ? 'Provisional' : 'Confirmado' }}</td>
                <td>{{ detail.official_shift_id ?? '—' }}</td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="showDetailDialog = false">Cerrar</VBtn>
          <VBtn v-if="detailItem?.id" color="primary" variant="tonal" @click="router.push({ name: 'nightpos-settlements-id', params: { id: detailItem.id } })">Ver settlement</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <SettlementPayDialog
      ref="payDialogRef"
      v-model="showPayDialog"
      :settlement="payingItem"
      title="Confirmar pago chica"
      type-label="Chica"
      :loading="paying"
      @confirm="confirmPay"
      @register-fine="openFineFromPay"
    />

    <StaffFineDialog
      v-model="showFineDialog"
      :staff-user-id="finePrefill?.staff_user_id"
      staff-role="GIRL"
      :staff-name="finePrefill?.staff_name"
      @created="onFineCreated"
    />

    <QuickOpenCashDialog v-model="showOpenCash" @opened="refreshCashSession" />
  </div>
</template>
