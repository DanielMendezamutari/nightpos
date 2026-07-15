<script setup>
import SettlementsCashBanner from '@/components/nightpos/settlements/SettlementsCashBanner.vue'
import SettlementPayDialog from '@/components/nightpos/settlements/SettlementPayDialog.vue'
import StaffFineDialog from '@/components/nightpos/settlements/StaffFineDialog.vue'
import SettlementListRowActions from '@/components/nightpos/settlements/SettlementListRowActions.vue'
import QuickOpenCashDialog from '@/components/nightpos/cash/QuickOpenCashDialog.vue'
import NightPosPageHeader from '@/components/nightpos/layout/NightPosPageHeader.vue'
import NightPosSectionTabs from '@/components/nightpos/layout/NightPosSectionTabs.vue'
import { updateSettlementManualCompensation } from '@/api/settlements'
import { useCurrentShiftSettlements } from '@/composables/useCurrentShiftSettlements'
import { useNightPosNotify } from '@/composables/useNightPosNotify'
import { useFilteredSettlementTabs } from '@/composables/useSettlementSectionTabs'
import { useSettlementPayment } from '@/composables/useSettlementPayment'
import { useNightPosPermissions } from '@/composables/useNightPosPermissions'
import { useOperationalEvents } from '@/composables/useOperationalEvents'
import { getApiErrorMessage } from '@/services/http'
import { useDisplay } from 'vuetify'

definePage({ meta: { permission: 'settlements.access' } })

const settlementTabs = useFilteredSettlementTabs()
const router = useRouter()
const { can, canManageSettlementFines } = useNightPosPermissions()
const { notify } = useNightPosNotify()
const { loading, shift, context, waiters, reload } = useCurrentShiftSettlements()
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
const payingItem = ref(null)
const finePrefill = ref(null)
const payDialogRef = ref(null)
const showManualDialog = ref(false)
const manualLoading = ref(false)
const manualItem = ref(null)
const manualForm = ref({ amount: '', notes: '' })
const { smAndDown } = useDisplay()

const desktopHeaders = [
  { title: 'Garzón', key: 'staff_name' },
  { title: 'Corte', key: 'cut_label' },
  { title: 'Modo', key: 'compensation_mode' },
  { title: '%', key: 'commission_percent' },
  { title: 'Cant. ventas', key: 'sales_count' },
  { title: 'Total vendido', key: 'sales_total_amount' },
  { title: 'Monto manual', key: 'manual_amount_input' },
  { title: 'Comisión', key: 'total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Generado', key: 'created_at' },
  { title: 'Pagado', key: 'paid_at' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const mobileHeaders = [
  { title: 'Garzón', key: 'staff_name' },
  { title: 'Total vendido', key: 'sales_total_amount' },
  { title: 'Comisión', key: 'total_amount' },
  { title: 'Estado', key: 'status' },
  { title: 'Acciones', key: 'actions', sortable: false },
  { title: 'Cant. ventas', key: 'sales_count' },
  { title: 'Corte', key: 'cut_label' },
  { title: 'Modo', key: 'compensation_mode' },
  { title: '%', key: 'commission_percent' },
  { title: 'Monto manual', key: 'manual_amount_input' },
  { title: 'Generado', key: 'created_at' },
  { title: 'Pagado', key: 'paid_at' },
]

const headers = computed(() => (smAndDown.value ? mobileHeaders : desktopHeaders))

const statusColor = status => ({
  PENDING: 'warning',
  PAID: 'success',
  CANCELLED: 'secondary',
}[status] || 'default')

const compensationModeLabel = mode => ({
  AUTO_PERCENT: 'Auto %',
  MANUAL: 'Manual',
}[mode] || mode || 'N/A')

const canAssignManual = item => item.compensation_mode === 'MANUAL'
const requiresManualAmount = item => item.requires_manual_amount === true
const payDisabledReason = item => requiresManualAmount(item) ? 'Asigne monto manual antes de pagar.' : ''

const formatBobAmount = amount => {
  if (amount === null || amount === undefined || amount === '')
    return '—'

  const numeric = Number(amount)

  if (Number.isNaN(numeric))
    return `${amount} BOB`

  return `${new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(numeric)} BOB`
}

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

const openManualDialog = item => {
  manualItem.value = item
  manualForm.value = {
    amount: item.manual_amount_input ?? '',
    notes: item.compensation_notes ?? '',
  }
  showManualDialog.value = true
}

const submitManualCompensation = async () => {
  if (!manualItem.value)
    return

  const amount = Number(manualForm.value.amount)

  if (Number.isNaN(amount) || amount < 0) {
    notify('Ingrese un monto manual valido (>= 0).', 'warning')

    return
  }

  manualLoading.value = true

  try {
    await updateSettlementManualCompensation(manualItem.value.id, {
      amount,
      notes: manualForm.value.notes || null,
    })

    notify('Monto manual guardado.', 'success')
    showManualDialog.value = false
    manualItem.value = null
    await reload()
  }
  catch (error) {
    notify(getApiErrorMessage(error), 'error')
  }
  finally {
    manualLoading.value = false
  }
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
      title="Liquidaciones — Garzones"
      subtitle="Comisiones del turno oficial por garzón."
      :breadcrumbs="[
        { title: 'NightPOS', disabled: true },
        { title: 'Finanzas', disabled: true },
        { title: 'Liquidaciones', to: { name: 'nightpos-settlements' } },
        { title: 'Garzones', disabled: true },
      ]"
    />
    <NightPosSectionTabs :tabs="settlementTabs" />

    <SettlementsCashBanner emphasize-pay-requirement />

    <VAlert
      v-if="canManageSettlementFines && !loading && waiters.length"
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
      <div class="waiters-table-wrap">
        <VDataTable
          :headers="headers"
          :items="waiters"
          :items-per-page="15"
          class="text-no-wrap waiters-table"
        >
          <template #item.status="{ item }">
            <VChip
              size="small"
              :color="statusColor(item.status)"
              variant="tonal"
            >
              {{ item.status === 'PENDING' ? 'Pendiente' : item.status === 'PAID' ? 'Pagado' : item.status }}
            </VChip>
          </template>
          <template #item.compensation_mode="{ item }">
            <VChip
              size="small"
              :color="item.compensation_mode === 'MANUAL' ? 'info' : 'primary'"
              variant="tonal"
            >
              {{ compensationModeLabel(item.compensation_mode) }}
            </VChip>
          </template>
          <template #item.sales_count="{ item }">
            {{ Number(item.sales_count ?? 0) }}
          </template>
          <template #item.sales_total_amount="{ item }">
            {{ formatBobAmount(item.sales_total_amount ?? item.waiter_sales_total ?? '0.00') }}
          </template>
          <template #item.total_amount="{ item }">
            {{ formatBobAmount(item.total_amount) }}
          </template>
          <template #item.manual_amount_input="{ item }">
            {{ item.manual_amount_input ?? 'Pendiente' }}
          </template>
          <template #item.actions="{ item }">
            <SettlementListRowActions
              :item="item"
              :can-pay="canPay"
              :can-multar="canManageSettlementFines"
              :can-assign-manual="canAssignManual(item)"
              :pay-disabled="requiresManualAmount(item)"
              :pay-disabled-reason="payDisabledReason(item)"
              @pay="openPayDialog"
              @multar="openFineDialog"
              @assign-manual="openManualDialog"
              @detail="item => router.push({ name: 'nightpos-settlements-id', params: { id: item.id } })"
            />
          </template>
        </VDataTable>
      </div>
    </VCard>

    <SettlementPayDialog
      ref="payDialogRef"
      v-model="showPayDialog"
      :settlement="payingItem"
      title="Confirmar pago garzón"
      type-label="Garzón"
      :loading="paying"
      @confirm="confirmPay"
      @register-fine="openFineFromPay"
    />

    <StaffFineDialog
      v-model="showFineDialog"
      :staff-user-id="finePrefill?.staff_user_id"
      staff-role="WAITER"
      :staff-name="finePrefill?.staff_name"
      @created="onFineCreated"
    />

    <QuickOpenCashDialog v-model="showOpenCash" @opened="refreshCashSession" />

    <VDialog
      v-model="showManualDialog"
      max-width="520"
    >
      <VCard>
        <VCardTitle>Asignar compensacion manual</VCardTitle>
        <VCardText>
          <div class="mb-3 text-body-2">
            Garzon: <strong>{{ manualItem?.staff_name ?? '-' }}</strong>
          </div>
          <VTextField
            v-model="manualForm.amount"
            label="Monto manual (BOB)"
            type="number"
            min="0"
            step="0.01"
            density="comfortable"
          />
          <VTextarea
            v-model="manualForm.notes"
            label="Notas"
            rows="3"
            density="comfortable"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            :disabled="manualLoading"
            @click="showManualDialog = false"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            :loading="manualLoading"
            @click="submitManualCompensation"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.waiters-table-wrap {
  overflow-x: auto;
}

.waiters-table {
  min-width: 1180px;
}
</style>
